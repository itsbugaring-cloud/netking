<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Ont;
use App\Models\User;
use App\Services\MikroTikService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TelegramRequestController extends Controller
{
    private const BOT_DIR = 'telegram-config-bot/requests';

    public function index(Request $request)
    {
        $status = trim((string) $request->query('status', ''));
        $q = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 25;
        $disk = Storage::disk('local');
        $files = $disk->exists(self::BOT_DIR) ? $disk->files(self::BOT_DIR) : [];

        $rows = [];
        foreach ($files as $file) {
            if (!str_ends_with($file, '.json') || str_ends_with($file, '/index.json')) {
                continue;
            }

            $decoded = json_decode((string) $disk->get($file), true);
            if (!is_array($decoded)) {
                continue;
            }

            $row = [
                'ref' => (string) ($decoded['ref'] ?? ''),
                'status' => (string) ($decoded['status'] ?? ''),
                'submitted_at' => (string) ($decoded['submitted_at'] ?? ''),
                'chat_id' => (string) ($decoded['chat_id'] ?? ''),
                'from_name' => (string) data_get($decoded, 'from.name', ''),
                'from_username' => (string) data_get($decoded, 'from.username', ''),
                'from_id' => (string) data_get($decoded, 'from.id', ''),
                'area_name' => (string) data_get($decoded, 'draft.area_name', ''),
                'customer_name' => (string) data_get($decoded, 'draft.nama', ''),
                'pppoe_user' => (string) data_get($decoded, 'draft.pppoe_user', ''),
                'sn_ont' => (string) data_get($decoded, 'draft.sn_ont', ''),
                'mode' => (string) ($decoded['mode'] ?? 'test'),
            ];

            if ($status !== '' && $row['status'] !== $status) {
                continue;
            }

            if ($q !== '') {
                $haystack = mb_strtolower(implode(' ', [
                    $row['ref'], $row['from_name'], $row['from_username'], $row['area_name'],
                    $row['customer_name'], $row['pppoe_user'], $row['sn_ont'], $row['chat_id'],
                ]));
                if (!str_contains($haystack, mb_strtolower($q))) {
                    continue;
                }
            }

            $rows[] = $row;
        }

        usort($rows, fn ($a, $b) => strcmp($b['submitted_at'], $a['submitted_at']));
        $total = count($rows);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($rows, $offset, $perPage);

        return view('admin.telegram.requests.index', [
            'items' => $items,
            'status' => $status,
            'q' => $q,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'lastPage' => $lastPage,
            'statuses' => [
                'diterima',
                'menunggu_push_olt',
                'menunggu_pppoe_up',
                'online',
                'rejected',
                'failed_mikrotik',
            ],
        ]);
    }

    public function show(string $ref)
    {
        $path = self::BOT_DIR . '/' . $ref . '.json';
        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Request bot tidak ditemukan');
        }

        $payload = json_decode((string) Storage::disk('local')->get($path), true);
        if (!is_array($payload)) {
            abort(404, 'Data request bot invalid');
        }

        return view('admin.telegram.requests.show', [
            'payload' => $payload,
            'ref' => $ref,
            'hasSnPhoto' => trim((string) data_get($payload, 'draft.photo_file_id', '')) !== '',
        ]);
    }

    public function photo(string $ref)
    {
        $payload = $this->load($ref);
        if ($payload === null) {
            abort(404, 'Request tidak ditemukan.');
        }

        $photoUrl = $this->resolveTelegramPhotoUrl((string) data_get($payload, 'draft.photo_file_id', ''));
        if ($photoUrl === null) {
            abort(404, 'Foto SN belum tersedia.');
        }

        $response = Http::timeout(10)->get($photoUrl);
        if (!$response->successful()) {
            abort(404, 'Foto SN gagal diambil.');
        }

        return response($response->body(), 200, [
            'Content-Type' => $response->header('Content-Type', 'image/jpeg'),
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    public function approve(string $ref)
    {
        $payload = $this->load($ref);
        if ($payload === null) {
            return back()->with('error', 'Request tidak ditemukan.');
        }

        $payload['status'] = 'menunggu_push_olt';
        $payload['pipeline']['menunggu_push_olt'] = now()->toDateTimeString();
        $payload['history'][] = [
            'at' => now()->toDateTimeString(),
            'status' => 'menunggu_push_olt',
            'by' => $this->actor(),
            'note' => 'Approved via website',
        ];
        $this->save($ref, $payload);

        return back()->with('success', "Request {$ref} di-approve.");
    }

    public function pushMikrotik(string $ref)
    {
        $payload = $this->load($ref);
        if ($payload === null) {
            return back()->with('error', 'Request tidak ditemukan.');
        }

        $push = $this->pushSecretToMikrotik($payload);
        if (($push['success'] ?? false) !== true) {
            $payload['status'] = 'failed_mikrotik';
            $payload['history'][] = [
                'at' => now()->toDateTimeString(),
                'status' => 'failed_mikrotik',
                'by' => $this->actor(),
                'note' => 'Push MikroTik gagal: ' . ($push['error'] ?? 'Unknown'),
            ];
            $this->save($ref, $payload);
            return back()->with('error', 'Push MikroTik gagal: ' . ($push['error'] ?? 'Unknown'));
        }

        $payload['status'] = 'menunggu_pppoe_up';
        $payload['pipeline']['menunggu_pppoe_up'] = now()->toDateTimeString();
        $payload['history'][] = [
            'at' => now()->toDateTimeString(),
            'status' => 'menunggu_pppoe_up',
            'by' => $this->actor(),
            'note' => 'Push MikroTik sukses dari website',
        ];
        $this->save($ref, $payload);

        return back()->with('success', "Push MikroTik sukses untuk {$ref}.");
    }

    public function markOnline(string $ref)
    {
        $payload = $this->load($ref);
        if ($payload === null) {
            return back()->with('error', 'Request tidak ditemukan.');
        }

        $payload['status'] = 'online';
        $payload['pipeline']['online'] = now()->toDateTimeString();
        $payload['history'][] = [
            'at' => now()->toDateTimeString(),
            'status' => 'online',
            'by' => $this->actor(),
            'note' => 'Ditandai ONLINE via website',
        ];
        $this->save($ref, $payload);

        return back()->with('success', "Request {$ref} ditandai ONLINE.");
    }

    public function reject(string $ref)
    {
        $payload = $this->load($ref);
        if ($payload === null) {
            return back()->with('error', 'Request tidak ditemukan.');
        }

        $payload['status'] = 'rejected';
        $payload['history'][] = [
            'at' => now()->toDateTimeString(),
            'status' => 'rejected',
            'by' => $this->actor(),
            'note' => 'Ditolak via website',
        ];
        $this->save($ref, $payload);

        return back()->with('success', "Request {$ref} ditolak.");
    }

    public function createCustomer(string $ref)
    {
        $payload = $this->load($ref);
        if ($payload === null) {
            return back()->with('error', 'Request tidak ditemukan.');
        }

        $draft = (array) ($payload['draft'] ?? []);
        $areaId = (int) ($draft['area_id'] ?? 0);
        $name = trim((string) ($draft['nama'] ?? ''));
        $pppoeUser = trim((string) ($draft['pppoe_user'] ?? ''));
        $pppoePass = trim((string) ($draft['pppoe_pass'] ?? 'netking'));
        $portalRaw = preg_replace('/[^0-9]/', '', (string) ($draft['no_hp'] ?? ''));
        $phone = trim((string) ($draft['no_hp'] ?? ''));
        $address = trim((string) ($draft['address'] ?? $draft['lokasi'] ?? ''));
        $latitude = isset($draft['latitude']) ? (float) $draft['latitude'] : null;
        $longitude = isset($draft['longitude']) ? (float) $draft['longitude'] : null;
        $sn = $this->normalizeSn((string) ($draft['sn_ont'] ?? ''));
        $billingStart = (string) ($draft['tanggal_pasang'] ?? now()->toDateString());
        $packageId = (int) ($draft['paket_id'] ?? 0);
        $packagePrice = (float) ($draft['harga'] ?? 0);

        if ($areaId <= 0 || $name === '' || $pppoeUser === '') {
            return back()->with('error', 'Draft belum lengkap (AREA/NAMA/PPPOE).');
        }

        $exists = Customer::forAreaPppoe($areaId, $pppoeUser)->first();
        if ($exists) {
            return back()->with('error', "PPPoE {$pppoeUser} sudah ada di area ini (Customer ID {$exists->id}).");
        }

        $partnerId = $this->resolvePartnerIdFromPayload($payload, $areaId);
        if ($portalRaw === null || strlen($portalRaw) < 6) {
            $portalRaw = '12345678';
        }

        $customer = DB::transaction(function () use (
            $areaId,
            $partnerId,
            $packageId,
            $name,
            $pppoeUser,
            $pppoePass,
            $sn,
            $packagePrice,
            $billingStart,
            $phone,
            $address,
            $portalRaw
        ) {
            $customer = Customer::create([
                'partner_id' => $partnerId,
                'area_id' => $areaId,
                'package_id' => $packageId > 0 ? $packageId : null,
                'name' => $name,
                'pppoe_user' => $pppoeUser,
                'pppoe_pass' => $pppoePass,
                'portal_password' => Hash::make($portalRaw),
                'ont_sn' => $sn !== '' ? $sn : null,
                'package_price' => $packagePrice,
                'billing_start_date' => $billingStart,
                'phone' => $phone !== '' ? $phone : null,
                'address' => $address !== '' ? $address : null,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'status' => 'provisioning',
                'pppoe_pending_enable' => true,
            ]);

            if ($sn !== '') {
                $ont = Ont::query()
                    ->where('area_id', $areaId)
                    ->whereRaw('REPLACE(UPPER(serial_number), "-", "") = ?', [$sn])
                    ->first();

                if ($ont) {
                    if ($ont->customer_id && $ont->customer_id !== $customer->id) {
                        $prev = Customer::find($ont->customer_id);
                        if ($prev && $this->normalizeSn((string) $prev->ont_sn) === $sn) {
                            $prev->update(['ont_sn' => null]);
                        }
                    }

                    Ont::where('customer_id', $customer->id)->where('id', '!=', $ont->id)->update(['customer_id' => null]);
                    $ont->update(['customer_id' => $customer->id]);
                    $customer->update(['ont_sn' => $ont->serial_number]);
                }
            }

            return $customer;
        });

        $payload['customer_id'] = $customer->id;
        $payload['customer_created_at'] = now()->toDateTimeString();
        $payload['history'][] = [
            'at' => now()->toDateTimeString(),
            'status' => (string) ($payload['status'] ?? 'diterima'),
            'by' => $this->actor(),
            'note' => "Customer dibuat via website (ID {$customer->id})" . ($sn !== '' ? " + link ONT by SN" : ''),
        ];
        $this->save($ref, $payload);

        return back()->with('success', "Customer berhasil dibuat (ID {$customer->id}) dan request di-log.");
    }

    private function load(string $ref): ?array
    {
        $path = self::BOT_DIR . '/' . $ref . '.json';
        if (!Storage::disk('local')->exists($path)) {
            return null;
        }
        $payload = json_decode((string) Storage::disk('local')->get($path), true);
        return is_array($payload) ? $payload : null;
    }

    private function save(string $ref, array $payload): void
    {
        Storage::disk('local')->put(
            self::BOT_DIR . '/' . $ref . '.json',
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    private function actor(): string
    {
        $u = auth()->user();
        if (!$u) {
            return 'admin';
        }
        return (string) ($u->name . ' (' . $u->email . ')');
    }

    private function resolvePartnerIdFromPayload(array $payload, int $areaId): ?int
    {
        $username = trim((string) data_get($payload, 'from.username', ''));
        if ($username !== '') {
            $user = User::query()
                ->where('role', 'partner')
                ->whereRaw('LOWER(telegram_username) = ?', [mb_strtolower($username)])
                ->first();
            if ($user) {
                return (int) $user->id;
            }
        }

        $single = User::query()->where('role', 'partner')->where('area_id', $areaId)->count();
        if ($single === 1) {
            return (int) User::query()->where('role', 'partner')->where('area_id', $areaId)->value('id');
        }

        return null;
    }

    private function normalizeSn(string $sn): string
    {
        $sn = strtoupper(trim($sn));
        return str_replace('-', '', $sn);
    }

    private function resolveTelegramPhotoUrl(string $fileId): ?string
    {
        $fileId = trim($fileId);
        if ($fileId === '') {
            return null;
        }

        $token = trim((string) (config('services.telegram_config.bot_token') ?: env('TELEGRAM_CONFIG_BOT_TOKEN', '')));
        if ($token === '') {
            return null;
        }

        try {
            $response = Http::timeout(5)->get("https://api.telegram.org/bot{$token}/getFile", [
                'file_id' => $fileId,
            ]);

            if (!$response->successful()) {
                return null;
            }

            $filePath = trim((string) data_get($response->json(), 'result.file_path', ''));
            if ($filePath === '') {
                return null;
            }

            return "https://api.telegram.org/file/bot{$token}/{$filePath}";
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function pushSecretToMikrotik(array $request): array
    {
        try {
            $draft = (array) ($request['draft'] ?? []);
            $areaId = (int) ($draft['area_id'] ?? 0);
            $area = Area::query()->find($areaId);
            if (!$area) {
                return ['success' => false, 'error' => 'Area tidak ditemukan'];
            }

            $service = MikroTikService::forArea($area);
            if (!$service->isConnected()) {
                return ['success' => false, 'error' => 'Tidak bisa konek MikroTik area'];
            }

            $profile = (string) ($draft['mikrotik_profile'] ?? $draft['paket_kode'] ?? 'default');
            $pppoeUser = (string) ($draft['pppoe_user'] ?? '');
            $pppoePass = (string) ($draft['pppoe_pass'] ?? 'netking');
            if ($pppoeUser === '') {
                return ['success' => false, 'error' => 'PPPOE_USER kosong'];
            }

            $all = $service->getAllSecrets();
            if (($all['success'] ?? false) === true) {
                $exists = collect($all['data'] ?? [])->contains(function ($row) use ($pppoeUser) {
                    return mb_strtolower((string) ($row['name'] ?? '')) === mb_strtolower($pppoeUser);
                });
                if ($exists) {
                    return ['success' => false, 'error' => 'PPPOE_USER sudah ada di MikroTik area'];
                }
            }

            $result = $service->createSecret(
                username: $pppoeUser,
                password: $pppoePass,
                service: 'pppoe',
                profile: $profile,
                remoteAddress: null,
                localAddress: null,
                comment: 'TELEGRAM:' . ($request['ref'] ?? '')
            );

            if (($result['success'] ?? false) !== true) {
                return ['success' => false, 'error' => (string) ($result['error'] ?? 'Gagal create secret')];
            }

            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
