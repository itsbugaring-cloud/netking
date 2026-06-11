<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Setting;
use App\Models\User;
use App\Services\TelegramConfigDraftValidator;
use App\Services\MikroTikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class TelegramConfigBotController extends Controller
{
    private const BOT_DIR = 'telegram-config-bot';
    private const DEFAULT_PPPOE_PASSWORD = 'netking';

    private const FLOW_FIELDS = [
        'area_id',
        'nama',
        'no_hp',
        'address',
        'coordinates',
        'sn_ont',
        'pppoe_user',
        'paket_id',
    ];

    private const STATUS_DITERIMA = 'diterima';
    private const STATUS_MENUNGGU_PUSH_OLT = 'menunggu_push_olt';
    private const STATUS_MENUNGGU_PPPOE_UP = 'menunggu_pppoe_up';
    private const STATUS_ONLINE = 'online';
    private const STATUS_REJECTED = 'rejected';
    private const STATUS_FAILED_MIKROTIK = 'failed_mikrotik';

    private function draftValidator(): TelegramConfigDraftValidator
    {
        return app(TelegramConfigDraftValidator::class);
    }

    public function handle(Request $request, string $secret): JsonResponse
    {
        if (!$this->isSecretValid($secret)) {
            return response()->json(['ok' => false], 404);
        }

        $update = $request->all();

        try {
            if (isset($update['callback_query']) && is_array($update['callback_query'])) {
                $this->handleCallbackQuery($update['callback_query']);
            } elseif (isset($update['message']) && is_array($update['message'])) {
                $this->handleMessage($update['message']);
            }
        } catch (\Throwable $e) {
            Log::error('telegram_config_bot_error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'update' => $update,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    private function handleMessage(array $message): void
    {
        $chatId = (string) data_get($message, 'chat.id', '');
        if ($chatId === '') {
            return;
        }
        $incomingMessageId = (int) data_get($message, 'message_id', 0);

        $from = (array) data_get($message, 'from', []);
        if (!$this->isAllowed($from)) {
            $this->sendMessage($chatId, "⛔ Maaf, akun Telegram ini belum diizinkan ya.\nMinta admin aktifkan dulu.");
            return;
        }

        $this->syncTelegramIdentity($from, $chatId);

        if (isset($message['photo']) && is_array($message['photo'])) {
            $this->handlePhotoUpload($chatId, $message['photo'], (string) data_get($message, 'caption', ''), $incomingMessageId);
            return;
        }

        if (isset($message['location']) && is_array($message['location'])) {
            $this->handleLocationInput($chatId, $from, (array) $message['location'], $incomingMessageId);
            return;
        }

        $text = trim((string) data_get($message, 'text', ''));
        if ($text === '') {
            return;
        }

        $textLc = mb_strtolower($text);
        $this->sendChatAction($chatId, 'typing');

        if (in_array($textLc, ['/start', '/menu', 'menu'], true)) {
            $this->cleanupTransientMessages($chatId);
            $this->clearPromptMessage($chatId);
            $this->clearLoadingMessage($chatId);
            $this->resetInputState($chatId, false);
            $this->sendGreeting($chatId, $from);
            return;
        }

        if (in_array($textLc, ['/cancel', 'cancel'], true)) {
            $state = $this->getState($chatId);
            $state['collecting'] = false;
            unset($state['edit_field']);
            $this->saveState($chatId, $state);
            $this->sendMenuAttachedMessage($chatId, "👌 Oke, input saya hentikan dulu ya.");
            return;
        }

        if (in_array($textLc, ['/bersih', 'bersih', '/clean', 'clean'], true)) {
            $this->purgeRecentMessages($chatId, (int) data_get($message, 'message_id', 0), 80);
            $tmpId = $this->sendMessage($chatId, "🧹 Poof! Chat bot yang terbaru sudah saya rapihin.", ['no_track' => true]);
            if ($tmpId !== null) {
                $this->deleteMessage($chatId, $tmpId);
            }
            return;
        }

        if (in_array($textLc, ['/reset', '♻️ reset draft', '♻️ reset', 'reset'], true)) {
            $this->cleanupTransientMessages($chatId);
            $this->clearPromptMessage($chatId);
            $this->clearLoadingMessage($chatId);
            $this->resetInputState($chatId, true);
            $this->sendMenuAttachedMessage($chatId, "♻️ Siap, draftnya udah saya kosongin. Kita mulai lagi kapan aja.");
            return;
        }

        if (in_array($textLc, ['/draft', '📋 lihat draft', '📋 draft', 'draft'], true) || str_contains($textLc, 'lihat draft')) {
            $this->sendDraftSummary($chatId, true);
            return;
        }

        if (in_array($textLc, ['/history', '🗂 history saya', '🗂 history', 'history'], true) || str_contains($textLc, 'history saya')) {
            $this->sendMyHistory($chatId);
            return;
        }

        if (in_array($textLc, ['/status', '📡 cek status', '📡 status'], true) || str_contains($textLc, 'cek status')) {
            $this->sendLatestRequestStatus($chatId);
            return;
        }

        if (in_array($textLc, ['/template', '🧾 template draft', '🧾 template'], true) || str_contains($textLc, 'template draft')) {
            $this->sendDraftTemplate($chatId);
            return;
        }

        if (in_array($textLc, ['/guide', '📚 panduan mikrotik pppoe', '📚 guide', 'guide'], true) || str_contains($textLc, 'panduan mikrotik pppoe')) {
            $this->sendMessage($chatId, "Menu ini saya nonaktifkan dulu ya. Langsung klik *Input* buat mulai.", ['parse_mode' => 'Markdown']);
            return;
        }

        if (in_array($textLc, ['📝 input', 'input', '📝 input data pelanggan'], true) || str_contains($textLc, 'input data pelanggan')) {
            $this->startFlow($chatId, $from);
            return;
        }

        if (in_array($textLc, ['📷 kirim foto sn', '📷 foto sn'], true) || str_contains($textLc, 'kirim foto sn')) {
            $this->sendMessage($chatId, "📷 Kirim foto label SN ONT yang jelas.\nBot akan baca serial langsung dari gambar, jadi nggak perlu caption lagi.");
            return;
        }

        if ($textLc === '✅ submit' || $textLc === 'submit') {
            $this->showFuturisticLoading($chatId, 'Validasi submit');
            $this->submitDraft($chatId, $from);
            return;
        }

        if ($textLc === '❌ batal input') {
            $state = $this->getState($chatId);
            $state['collecting'] = false;
            $this->saveState($chatId, $state);
            $this->sendMenuAttachedMessage($chatId, "❌ Oke, input saya batalkan dulu.");
            return;
        }

        $state = $this->getState($chatId);
        if (($state['collecting'] ?? false) === true) {
            $this->handleFlowInput($chatId, $from, $text, $state);
            if ($incomingMessageId > 0) {
                $this->deleteMessage($chatId, $incomingMessageId);
            }
            return;
        }

        // Hindari spam "menu" untuk pesan yang tidak dikenali.
        // Keyboard sudah dipasang pada greeting dan aksi utama.
        return;
    }

    private function handleCallbackQuery(array $callback): void
    {
        $data = (string) ($callback['data'] ?? '');
        $from = (array) ($callback['from'] ?? []);
        $chatId = (string) data_get($callback, 'message.chat.id', '');
        $callbackId = (string) ($callback['id'] ?? '');

        if ($chatId === '') {
            return;
        }

        if (!$this->isAllowed($from)) {
            $this->answerCallbackQuery($callbackId, 'Akses ditolak.');
            return;
        }

        $this->syncTelegramIdentity($from, $chatId);

        if ($data === '') {
            $this->answerCallbackQuery($callbackId);
            return;
        }

        $parts = explode(':', $data);
        $action = $parts[0] ?? '';

        if ($action !== 'cfg') {
            $this->answerCallbackQuery($callbackId);
            return;
        }

        $type = $parts[1] ?? '';
        $value = $parts[2] ?? '';

        $this->answerCallbackQuery($callbackId);
        $this->sendChatAction($chatId, 'typing');

        if ($type === 'input') {
            $this->startFlow($chatId, $from);
            return;
        }

        if ($type === 'draft') {
            $this->sendDraftSummary($chatId, true);
            return;
        }

        if ($type === 'template') {
            $this->sendDraftTemplate($chatId);
            return;
        }
        
        if ($type === 'guide') {
            $this->sendMessage($chatId, "Guide saya nonaktifkan dulu ya. Langsung pakai menu *Input* aja.", ['parse_mode' => 'Markdown']);
            return;
        }

        if ($type === 'garea') {
            $this->sendMessage($chatId, "Guide area juga saya nonaktifkan dulu. Klik *Input* buat lanjut kerja.", ['parse_mode' => 'Markdown']);
            return;
        }

        if ($type === 'reset') {
            $this->resetInputState($chatId, true);
            $this->sendMenuAttachedMessage($chatId, "♻️ Siap, draftnya udah saya kosongin. Kita mulai lagi kapan aja.");
            return;
        }

        if ($type === 'submit') {
            $this->showFuturisticLoading($chatId, 'Validasi submit');
            $this->submitDraft($chatId, $from);
            return;
        }

        if ($type === 'status') {
            $this->sendLatestRequestStatus($chatId);
            return;
        }

        if ($type === 'edit') {
            $this->beginEditField($chatId, $value);
            return;
        }

        if ($type === 'area') {
            $this->selectArea($chatId, $from, (int) $value);
            return;
        }

        if ($type === 'pkg') {
            $this->selectPackage($chatId, $from, (int) $value);
            return;
        }

        if ($type === 'aok') {
            $this->sendMessage($chatId, "ℹ️ Mode approve dimatikan. Sekarang submit langsung push ke MikroTik.");
            return;
        }

        if ($type === 'apush') {
            $this->adminActionMarkOltPushed($chatId, $value, $from);
            return;
        }

        if ($type === 'aup') {
            $this->adminActionMarkPppoeUp($chatId, $value, $from);
            return;
        }

        if ($type === 'arej') {
            $this->adminActionReject($chatId, $value, $from);
            return;
        }
    }

    private function startFlow(string $chatId, array $from): void
    {
        $state = $this->getState($chatId);
        $state['collecting'] = true;
        $state['field_index'] = 0;
        $state['draft'] = $state['draft'] ?? [];
        $state['draft']['pppoe_pass'] = self::DEFAULT_PPPOE_PASSWORD;
        $state['draft']['tanggal_pasang'] = now()->toDateString();
        $state['draft']['requested_by'] = [
            'telegram_id' => (string) ($from['id'] ?? ''),
            'telegram_username' => (string) ($from['username'] ?? ''),
            'name' => (string) ($from['first_name'] ?? ''),
        ];
        $state['updated_at'] = now()->toDateTimeString();
        $this->saveState($chatId, $state);

        $this->sendMessage(
            $chatId,
            "┌ Input pelanggan\n" .
            "└ Pilih area dulu ya.",
            [
                'reply_markup' => [
                    'remove_keyboard' => true,
                ],
            ]
        );

        $this->promptCurrentField($chatId, $state);
    }

    private function selectArea(string $chatId, array $from, int $areaId): void
    {
        $state = $this->getState($chatId);
        if (($state['collecting'] ?? false) !== true) {
            $state['collecting'] = true;
            $state['field_index'] = $this->fieldIndex('area_id');
        }

        $field = self::FLOW_FIELDS[(int) ($state['field_index'] ?? 0)] ?? null;
        if ($field !== 'area_id' && ($state['edit_field'] ?? '') !== 'area_id') {
            $this->sendMessage($chatId, "⚠️ Pilihan router belum bisa dipakai di step ini. Klik *Input* dulu ya.", ['parse_mode' => 'Markdown']);
            return;
        }

        $area = Area::query()->find($areaId);
        if (!$area) {
            $this->sendMessage($chatId, "⚠️ Area tidak ditemukan.");
            $this->promptCurrentField($chatId, $state);
            return;
        }

        $areaLabel = $this->areaDisplayName($area);
        $state['draft']['area_id'] = $area->id;
        $state['draft']['area_name'] = $area->name;
        $state['draft']['area_label'] = $areaLabel;
        $state['draft']['area_vlan_pppoe'] = $area->vlan_pppoe;

        if (($state['edit_field'] ?? '') === 'area_id') {
            unset($state['draft']['paket_id'], $state['draft']['paket_kode'], $state['draft']['paket_name'], $state['draft']['harga'], $state['draft']['mikrotik_profile']);
            $state['edit_field'] = 'paket_id';
            $state['field_index'] = $this->fieldIndex('paket_id');
            $state['updated_at'] = now()->toDateTimeString();
            $this->saveState($chatId, $state);
            $areaVlan = trim((string) ($area->vlan_pppoe ?? ''));
            $msg = "✅ {$areaLabel}";
            if ($areaVlan !== '') {
                $msg .= "\nVLAN PPPoE: {$areaVlan}";
            }
            $msg .= "\nLanjut pilih paket.";
            $this->sendMessage($chatId, $msg);
            $this->promptCurrentField($chatId, $state);
            return;
        }

        $state['field_index'] = (int) $state['field_index'] + 1;
        $state['updated_at'] = now()->toDateTimeString();
        $state['draft']['requested_by'] = [
            'telegram_id' => (string) ($from['id'] ?? ''),
            'telegram_username' => (string) ($from['username'] ?? ''),
            'name' => (string) ($from['first_name'] ?? ''),
        ];
        $this->saveState($chatId, $state);

        $areaVlan = trim((string) ($area->vlan_pppoe ?? ''));
        $msg = "✅ {$areaLabel}";
        if ($areaVlan !== '') {
            $msg .= "\nVLAN PPPoE: {$areaVlan}";
        }
        $this->sendMessage($chatId, $msg);

        $this->promptCurrentField($chatId, $state);
    }

    private function selectPackage(string $chatId, array $from, int $packageId): void
    {
        $state = $this->getState($chatId);
        if (($state['collecting'] ?? false) !== true) {
            $this->sendMessage($chatId, "⚠️ Session input belum aktif. Klik *Input* dulu ya.", ['parse_mode' => 'Markdown']);
            return;
        }

        $field = self::FLOW_FIELDS[(int) ($state['field_index'] ?? 0)] ?? null;
        if ($field !== 'paket_id' && ($state['edit_field'] ?? '') !== 'paket_id') {
            $this->sendMessage($chatId, "⚠️ Pilihan paket belum bisa dipakai di step ini.");
            return;
        }

        $areaId = (int) ($state['draft']['area_id'] ?? 0);
        $package = Package::query()
            ->where('id', $packageId)
            ->where('area_id', $areaId)
            ->where('is_active', true)
            ->first();

        if (!$package) {
            $this->sendMessage($chatId, "⚠️ Paketnya nggak cocok untuk area ini. Coba pilih lagi ya.");
            $this->promptCurrentField($chatId, $state);
            return;
        }

        $state['draft']['paket_id'] = $package->id;
        $state['draft']['paket_kode'] = (string) $package->code;
        $state['draft']['paket_name'] = (string) $package->name;
        $state['draft']['harga'] = (int) $package->price;
        $state['draft']['mikrotik_profile'] = (string) ($package->mikrotik_profile ?: $package->code);

        if (($state['edit_field'] ?? '') === 'paket_id') {
            unset($state['edit_field']);
            $state['collecting'] = false;
            $state['updated_at'] = now()->toDateTimeString();
            $this->saveState($chatId, $state);
            $this->sendMessage($chatId, "✅ Paket berhasil diperbarui.");
            $this->sendDraftSummary($chatId, true);
            return;
        }

        $state['field_index'] = (int) $state['field_index'] + 1;
        $state['updated_at'] = now()->toDateTimeString();
        $state['draft']['requested_by'] = [
            'telegram_id' => (string) ($from['id'] ?? ''),
            'telegram_username' => (string) ($from['username'] ?? ''),
            'name' => (string) ($from['first_name'] ?? ''),
        ];
        $this->saveState($chatId, $state);

        $this->promptCurrentField($chatId, $state);
    }

    private function handleFlowInput(string $chatId, array $from, string $input, array $state): void
    {
        $editingField = (string) ($state['edit_field'] ?? '');
        if ($editingField !== '') {
            $value = $this->normalizeFieldValue($editingField, $input);
            $error = $this->validateFieldValue($editingField, $value, $state);
            if ($error !== null) {
                $this->sendMessage($chatId, "⚠️ {$error}");
                $this->promptCurrentField($chatId, $state);
                return;
            }

            if ($editingField === 'pppoe_user') {
                $duplicate = $this->findDuplicatePppoe(
                    (int) ($state['draft']['area_id'] ?? 0),
                    (string) $value
                );
                if ($duplicate !== null) {
                    $this->sendMessage(
                        $chatId,
                        "⚠️ PPPoE ini sudah kepakai di area yang sama: {$duplicate['name']} ({$duplicate['pppoe_user']})."
                    );
                    $this->promptCurrentField($chatId, $state);
                    return;
                }
            }

            $state['draft'][$editingField] = $value;
            if ($editingField === 'coordinates') {
                $parsed = $this->parseCoordinates($value);
                $state['draft']['latitude'] = $parsed['latitude'] ?? null;
                $state['draft']['longitude'] = $parsed['longitude'] ?? null;
            }
            if ($editingField === 'sn_ont') {
                $state['draft']['photo_sn_matched'] = false;
                $state['draft']['photo_sn_verified'] = false;
                $state['draft']['photo_sn_ont'] = null;
                $state['draft']['photo_sn_ocr'] = null;
                $state['draft']['photo_ocr_text'] = null;
                $state['draft']['photo_ocr_error'] = null;
            }

            unset($state['edit_field']);
            $state['collecting'] = false;
            $state['updated_at'] = now()->toDateTimeString();
            $this->saveState($chatId, $state);

            $msg = "✅ Field {$editingField} diperbarui.";
            if ($editingField === 'sn_ont') {
                $msg .= "\n📷 Karena SN berubah, kirim ulang foto SN yang jelas supaya saya cek ulang dari gambar.";
            }
            $this->sendMessage($chatId, $msg);
            $this->sendDraftSummary($chatId, true);
            return;
        }

        $fieldIndex = (int) ($state['field_index'] ?? 0);
        $field = self::FLOW_FIELDS[$fieldIndex] ?? null;
        if ($field === null) {
            $state['collecting'] = false;
            $this->saveState($chatId, $state);
            $this->sendDraftSummary($chatId, true);
            return;
        }

        if (in_array($field, ['area_id', 'paket_id'], true)) {
            $this->promptCurrentField($chatId, $state);
            return;
        }

        $value = $this->normalizeFieldValue($field, $input);
        $error = $this->validateFieldValue($field, $value, $state);
        if ($error !== null) {
            $this->sendMessage($chatId, "⚠️ {$error}");
            $this->promptCurrentField($chatId, $state);
            return;
        }

        $state['draft'][$field] = $value;
        if ($field === 'coordinates') {
            $parsed = $this->parseCoordinates($value);
            $state['draft']['latitude'] = $parsed['latitude'] ?? null;
            $state['draft']['longitude'] = $parsed['longitude'] ?? null;
        }

        if ($field === 'pppoe_user') {
            $duplicate = $this->findDuplicatePppoe(
                (int) ($state['draft']['area_id'] ?? 0),
                (string) ($state['draft']['pppoe_user'] ?? '')
            );
            if ($duplicate !== null) {
                $this->sendMessage(
                    $chatId,
                    "⚠️ PPPoE ini sudah kepakai di area yang sama: {$duplicate['name']} ({$duplicate['pppoe_user']}).\nCoba pakai username lain ya."
                );
                $this->promptCurrentField($chatId, $state);
                return;
            }
        }

        $state['field_index'] = $fieldIndex + 1;
        $state['updated_at'] = now()->toDateTimeString();
        $state['draft']['requested_by'] = [
            'telegram_id' => (string) ($from['id'] ?? ''),
            'telegram_username' => (string) ($from['username'] ?? ''),
            'name' => (string) ($from['first_name'] ?? ''),
        ];
        $this->saveState($chatId, $state);

        if ((int) $state['field_index'] >= count(self::FLOW_FIELDS)) {
            $state['collecting'] = false;
            $this->saveState($chatId, $state);
            $this->sendDraftSummary($chatId, true);
            return;
        }

        $this->promptCurrentField($chatId, $state);
    }

    private function areaDisplayName(?Area $area): string
    {
        if (!$area) {
            return '-';
        }

        $routerIdentity = trim((string) ($area->router_identity ?? ''));
        if ($routerIdentity !== '') {
            return $routerIdentity;
        }

        return (string) $area->name;
    }

    private function areaButtonLabel(?Area $area): string
    {
        if (!$area) {
            return 'Router';
        }

        $label = $this->areaDisplayName($area);
        $label = preg_replace('/\s+/', ' ', trim($label)) ?? $label;
        $label = Str::limit($label, 24, '');

        return "◈ {$label}";
    }

    private function promptCurrentField(string $chatId, array $state): void
    {
        $this->clearPromptMessage($chatId);

        $fieldIndex = (int) ($state['field_index'] ?? 0);
        $field = self::FLOW_FIELDS[$fieldIndex] ?? null;
        if ($field === null) {
            $this->sendDraftSummary($chatId, true);
            return;
        }

        $progress = $this->renderProgress($fieldIndex, count(self::FLOW_FIELDS));

        if ($field === 'area_id') {
            $areas = Area::query()
                ->orderBy('name')
                ->get(['id', 'name', 'router_identity', 'vlan_pppoe']);

            $buttons = [];
            $row = [];
            foreach ($areas as $area) {
                $row[] = ['text' => $this->areaButtonLabel($area), 'callback_data' => 'cfg:area:' . $area->id];
                if (count($row) === 2) {
                    $buttons[] = $row;
                    $row = [];
                }
            }
            if (!empty($row)) {
                $buttons[] = $row;
            }

            $msgId = $this->sendMessage(
                $chatId,
                "⚡ PILIH MIKROTIK\n{$progress}\nPilih router tujuan dulu, habis itu lanjut isi data.",
                ['reply_markup' => ['inline_keyboard' => $buttons]]
            );
            $this->rememberPromptMessage($chatId, $msgId);
            return;
        }

        if ($field === 'paket_id') {
            $areaId = (int) ($state['draft']['area_id'] ?? 0);
            $packages = Package::query()
                ->where('area_id', $areaId)
                ->where('is_active', true)
                ->orderBy('speed_down')
                ->get(['id', 'name', 'code', 'speed_down', 'speed_up', 'price', 'mikrotik_profile']);

            $buttons = [];
            foreach ($packages as $p) {
                $price = number_format((float) $p->price, 0, ',', '.');
                $profile = $p->mikrotik_profile ?: $p->code;
                $label = "{$profile} ({$p->speed_down}M) • Rp{$price}";
                $buttons[] = [['text' => $label, 'callback_data' => 'cfg:pkg:' . $p->id]];
            }

            if (empty($buttons)) {
                $this->sendMessage($chatId, "⚠️ Paket di area ini belum diset. Pilih area lain dulu ya 🙏");
                return;
            }

            $msgId = $this->sendMessage(
                $chatId,
                "📦 PILIH PROFILE\n{$progress}\nPilih profile yang aktif di router ini.",
                ['reply_markup' => ['inline_keyboard' => $buttons]]
            );
            $this->rememberPromptMessage($chatId, $msgId);
            return;
        }

        $labels = [
            'nama' => '🧾 Masukkan NAMA pelanggan:',
            'no_hp' => '📱 Masukkan nomor HP:',
            'address' => '📍 Masukkan alamat pelanggan:',
            'coordinates' => "🛰️ Kirim titik koordinat rumah pelanggan:\nBisa share location Telegram atau ketik format: -6.123456, 107.123456",
            'sn_ont' => '🔌 Masukkan SN ONT:',
            'pppoe_user' => '🌐 Masukkan PPPoE User:',
        ];

        if ($field === 'pppoe_user') {
            $lastPppoe = $this->getLastPppoeByArea((int) ($state['draft']['area_id'] ?? 0));
            $areaName = (string) ($state['draft']['area_label'] ?? $state['draft']['area_name'] ?? '-');
            $areaVlan = trim((string) ($state['draft']['area_vlan_pppoe'] ?? ''));
            $hint = $lastPppoe !== null
                ? "\nArea: {$areaName}\nHint terakhir: {$lastPppoe['pppoe_user']} ({$lastPppoe['name']})"
                : "\nArea: {$areaName}\nHint terakhir: belum ada.";
            if ($areaVlan !== '') {
                $hint .= "\nVLAN PPPoE: {$areaVlan}";
            }
            $hint .= "\nPassword default: " . self::DEFAULT_PPPOE_PASSWORD;
            $labels['pppoe_user'] .= $hint;
        }

        $msgId = $this->sendMessage(
            $chatId,
            "{$progress}\n\n" . ($labels[$field] ?? "Masukkan {$field}:"),
            [
                'reply_markup' => [
                    'keyboard' => [['❌ Batal Input']],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => false,
                ],
            ]
        );
        $this->rememberPromptMessage($chatId, $msgId);
    }

    private function handlePhotoUpload(string $chatId, array $photos, string $caption = '', int $incomingMessageId = 0): void
    {
        $largest = end($photos);
        if (!is_array($largest) || empty($largest['file_id'])) {
            $this->sendMessage($chatId, "⚠️ Foto tidak terbaca. Kirim ulang foto SN yang jelas.");
            return;
        }

        $state = $this->getState($chatId);
        $state['draft'] = $state['draft'] ?? [];
        $typedSn = strtoupper(trim((string) ($state['draft']['sn_ont'] ?? '')));

        if ($typedSn === '') {
            $this->sendMessage($chatId, "⚠️ SN ONT teks belum diisi. Isi SN dulu, lalu kirim foto lagi.");
            return;
        }

        $state['draft']['photo_file_id'] = (string) $largest['file_id'];
        $state['draft']['photo_uploaded_at'] = now()->toDateTimeString();
        $state['draft']['photo_sn_matched'] = false;
        $state['draft']['photo_sn_verified'] = false;
        $state['draft']['photo_sn_ont'] = null;
        $state['draft']['photo_ocr_error'] = null;
        $state['draft']['photo_ocr_text'] = null;
        $state['draft']['photo_sn_ocr'] = null;

        $ocr = $this->extractSnFromTelegramPhoto((string) $largest['file_id']);
        if (($ocr['success'] ?? false) !== true) {
            $state['draft']['photo_ocr_error'] = (string) ($ocr['error'] ?? 'OCR gagal membaca foto');
            $state['updated_at'] = now()->toDateTimeString();
            $this->saveState($chatId, $state);
            $this->sendMessage(
                $chatId,
                "⚠️ Foto SN ditolak.\nSaya belum bisa baca serial dari gambar.\nAlasan: " . $state['draft']['photo_ocr_error'] . "\nKirim ulang foto yang lebih fokus dan dekat ke label SN."
            );
            return;
        }

        $ocrSn = strtoupper(trim((string) ($ocr['sn'] ?? '')));
        $state['draft']['photo_ocr_text'] = (string) ($ocr['raw_text'] ?? '');
        $state['draft']['photo_sn_ocr'] = $ocrSn !== '' ? $ocrSn : null;
        $state['draft']['photo_sn_ont'] = $ocrSn !== '' ? $ocrSn : null;
        $state['draft']['photo_sn_matched'] = $typedSn === $ocrSn;
        $state['draft']['photo_sn_verified'] = $typedSn === $ocrSn;

        $state['updated_at'] = now()->toDateTimeString();
        $this->saveState($chatId, $state);

        if (($state['draft']['photo_sn_matched'] ?? false) !== true) {
            $this->sendMessage(
                $chatId,
                "⚠️ SN di foto tidak cocok.\nSN teks: {$typedSn}\nHasil baca foto: " . ($ocrSn !== '' ? $ocrSn : 'tidak terbaca') . "\nKirim ulang foto yang lebih jelas."
            );
            return;
        }

        if ($incomingMessageId > 0) {
            $this->deleteMessage($chatId, $incomingMessageId);
        }

        $this->sendDraftSummary($chatId, true);
    }

    private function handleLocationInput(string $chatId, array $from, array $location, int $incomingMessageId = 0): void
    {
        $state = $this->getState($chatId);
        if (($state['collecting'] ?? false) !== true) {
            $this->sendMessage($chatId, "⚠️ Mode input belum aktif. Klik *Input* dulu ya.", ['parse_mode' => 'Markdown']);
            return;
        }

        $fieldIndex = (int) ($state['field_index'] ?? 0);
        $field = self::FLOW_FIELDS[$fieldIndex] ?? null;
        $editingField = (string) ($state['edit_field'] ?? '');

        if ($field !== 'coordinates' && $editingField !== 'coordinates') {
            $this->sendMessage($chatId, "⚠️ Koordinat belum dibutuhkan di step ini.");
            return;
        }

        $latitude = isset($location['latitude']) ? (float) $location['latitude'] : null;
        $longitude = isset($location['longitude']) ? (float) $location['longitude'] : null;
        if ($latitude === null || $longitude === null) {
            $this->sendMessage($chatId, "⚠️ Titik lokasi tidak terbaca. Coba share location lagi.");
            return;
        }

        $coordinateText = $this->formatCoordinates($latitude, $longitude);
        $state['draft']['coordinates'] = $coordinateText;
        $state['draft']['latitude'] = $latitude;
        $state['draft']['longitude'] = $longitude;
        $state['draft']['requested_by'] = [
            'telegram_id' => (string) ($from['id'] ?? ''),
            'telegram_username' => (string) ($from['username'] ?? ''),
            'name' => (string) ($from['first_name'] ?? ''),
        ];
        $state['updated_at'] = now()->toDateTimeString();

        if ($editingField === 'coordinates') {
            unset($state['edit_field']);
            $state['collecting'] = false;
            $this->saveState($chatId, $state);
            $this->sendMessage($chatId, "✅ Koordinat berhasil diperbarui: {$coordinateText}");
            $this->sendDraftSummary($chatId, true);
            return;
        }

        $state['field_index'] = $fieldIndex + 1;
        $this->saveState($chatId, $state);

        if ($incomingMessageId > 0) {
            $this->deleteMessage($chatId, $incomingMessageId);
        }

        if ((int) $state['field_index'] >= count(self::FLOW_FIELDS)) {
            $state['collecting'] = false;
            $this->saveState($chatId, $state);
            $this->sendDraftSummary($chatId, true);
            return;
        }

        $this->sendMessage($chatId, "✅ Koordinat masuk: {$coordinateText}");
        $this->promptCurrentField($chatId, $state);
    }

    private function submitDraft(string $chatId, array $from): void
    {
        $state = $this->getState($chatId);
        $draft = (array) ($state['draft'] ?? []);
        if (empty($draft['tanggal_pasang'])) {
            $draft['tanggal_pasang'] = now()->toDateString();
        }

        $missing = [];
        foreach (self::FLOW_FIELDS as $field) {
            if (($draft[$field] ?? '') === '') {
                $missing[] = strtoupper($field);
            }
        }

        if (!empty($missing)) {
            $this->sendMessage($chatId, '⚠️ Datanya belum lengkap nih: ' . implode(', ', $missing));
            return;
        }

        if (empty($draft['photo_file_id'])) {
            $this->sendMessage($chatId, "⚠️ Foto SN belum ada. Kirim dulu lewat tombol 📷 ya.");
            return;
        }

        $typedSn = strtoupper(trim((string) ($draft['sn_ont'] ?? '')));
        $ocrSn = strtoupper(trim((string) ($draft['photo_sn_ocr'] ?? $draft['photo_sn_ont'] ?? '')));
        if (($draft['photo_sn_verified'] ?? false) !== true || $typedSn === '' || $ocrSn === '' || $typedSn !== $ocrSn) {
            $this->sendMessage(
                $chatId,
                "⚠️ Validasi SN foto belum lolos.\nSN teks: {$typedSn}\nHasil baca foto: " . ($ocrSn !== '' ? $ocrSn : 'tidak terbaca') . "\nKirim ulang foto SN yang lebih jelas."
            );
            return;
        }

        $templateErrors = $this->validateDraftAgainstTemplate($draft);
        if (!empty($templateErrors)) {
            $this->sendMessage(
                $chatId,
                "⚠️ Format belum sesuai template final:\n- " . implode("\n- ", $templateErrors)
            );
            return;
        }

        $duplicate = $this->findDuplicatePppoe((int) ($draft['area_id'] ?? 0), (string) ($draft['pppoe_user'] ?? ''));
        if ($duplicate !== null) {
            $this->sendMessage(
                $chatId,
                "⚠️ Submit ditahan dulu. PPPoE bentrok sama pelanggan aktif: {$duplicate['name']} ({$duplicate['pppoe_user']})."
            );
            return;
        }

        $ref = 'REQ-' . $chatId . '-' . now()->format('YmdHis');
        $token = $this->requestToken();
        $area = Area::query()->find((int) ($draft['area_id'] ?? 0));

        $payload = [
            'ref' => $ref,
            'token' => $token,
            'mode' => $this->cfg('telegram_config_mode', 'TELEGRAM_CONFIG_MODE', 'test'),
            'submitted_at' => now()->toDateTimeString(),
            'chat_id' => $chatId,
            'from' => [
                'id' => (string) ($from['id'] ?? ''),
                'name' => (string) ($from['first_name'] ?? ''),
                'username' => (string) ($from['username'] ?? ''),
            ],
            'draft' => $draft,
            'status' => self::STATUS_DITERIMA,
            'history' => [
                ['at' => now()->toDateTimeString(), 'status' => self::STATUS_DITERIMA, 'by' => 'bot', 'note' => 'Request diterima'],
            ],
            'pipeline' => [
                'diterima' => now()->toDateTimeString(),
                'menunggu_push_olt' => null,
                'menunggu_pppoe_up' => null,
                'online' => null,
            ],
            'resolved_router' => [
                'area_name' => $draft['area_label'] ?? $area?->router_identity ?? $area?->name,
                'router_ip' => $area?->router_ip,
                'router_user' => $area?->router_user,
                'mikrotik_profile' => $draft['mikrotik_profile'] ?? null,
            ],
        ];

        $this->saveRequest($ref, $payload);
        $this->saveRequestIndex($token, $ref);

        // Auto push to MikroTik (tanpa approve manual).
        $push = $this->pushSecretToMikrotik($payload);
        if (($push['success'] ?? false) === true) {
            $payload['status'] = self::STATUS_MENUNGGU_PPPOE_UP;
            $payload['pipeline']['menunggu_push_olt'] = now()->toDateTimeString();
            $payload['pipeline']['menunggu_pppoe_up'] = now()->toDateTimeString();
            $payload['history'][] = [
                'at' => now()->toDateTimeString(),
                'status' => self::STATUS_MENUNGGU_PPPOE_UP,
                'by' => 'bot',
                'note' => 'PPPoE secret berhasil masuk ke MikroTik (auto)',
            ];

            // Auto-create customer in database
            $customer = $this->autoCreateCustomer($payload);
            if ($customer) {
                $payload['customer_id'] = $customer->id;
                $payload['customer_created_at'] = now()->toDateTimeString();
                $payload['history'][] = [
                    'at' => now()->toDateTimeString(),
                    'status' => self::STATUS_MENUNGGU_PPPOE_UP,
                    'by' => 'bot',
                    'note' => "Customer auto-created (ID {$customer->id})",
                ];
            }
        } else {
            $payload['status'] = self::STATUS_FAILED_MIKROTIK;
            $payload['history'][] = [
                'at' => now()->toDateTimeString(),
                'status' => self::STATUS_FAILED_MIKROTIK,
                'by' => 'bot',
                'note' => 'Auto push MikroTik gagal: ' . (string) ($push['error'] ?? 'unknown'),
            ];
        }
        $this->saveRequest($ref, $payload);

        // Notify admin via Telegram
        $adminChatId = config('services.telegram_config.admin_chat_id', env('TELEGRAM_CONFIG_ADMIN_CHAT_ID'));
        if ($adminChatId) {
            $areaName = $draft['area_label'] ?? $draft['area_name'] ?? '-';
            $customerName = $draft['nama'] ?? '-';
            $pppoeUser = $draft['pppoe_user'] ?? '-';
            $paketName = $draft['paket_name'] ?? ($draft['paket_kode'] ?? '-');
            $harga = number_format((float) ($draft['harga'] ?? 0), 0, ',', '.');
            $fromName = $from['first_name'] ?? 'Unknown';
            $fromUsername = $from['username'] ?? '';
            $pushStatus = (($push['success'] ?? false) === true) ? '✅ Secret masuk MikroTik' : '❌ Push gagal: ' . ($push['error'] ?? 'unknown');

            $notifText = "🆕 *Request Baru*\n"
                . "━━━━━━━━━━━━━━━\n"
                . "👷 PIC: {$fromName}" . ($fromUsername ? " (@{$fromUsername})" : '') . "\n"
                . "📍 Area: {$areaName}\n"
                . "👤 Nama: {$customerName}\n"
                . "🏠 Alamat: " . ($draft['address'] ?? '-') . "\n"
                . "🛰️ Koordinat: " . ($draft['coordinates'] ?? '-') . "\n"
                . "🌐 PPPoE: `{$pppoeUser}`\n"
                . "📦 Paket: {$paketName} (Rp {$harga})\n"
                . "━━━━━━━━━━━━━━━\n"
                . "Status: {$pushStatus}\n"
                . "Ref: `{$ref}`";

            $this->sendMessage($adminChatId, $notifText, ['parse_mode' => 'Markdown']);
        }

        $state['collecting'] = false;
        $state['last_submitted_ref'] = $ref;
        $state['last_submitted_token'] = $token;
        $this->saveState($chatId, $state);

        // "Poof" old draft/action bubbles so chat stays clean.
        $this->cleanupTransientMessages($chatId);

        $submitMsg = (($push['success'] ?? false) === true)
            ? "✅ Konfig PPPoE berhasil masuk ke MikroTik.\nPassword default PPPoE: `" . ($draft['pppoe_pass'] ?? self::DEFAULT_PPPOE_PASSWORD) . "`\nTinggal lanjut konfigurasi di ONT.\n\nStatus active connection MikroTik ditampilkan di bawah ini."
            : "⚠️ Data sudah tersimpan, tapi push ke router gagal.\n\nKlik *Status* buat lihat detailnya.";

        $this->sendMessage(
            $chatId,
            $submitMsg,
            [
                'parse_mode' => 'Markdown',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => '📡 Cek Status', 'callback_data' => 'cfg:status:last'],
                        ],
                        [
                            ['text' => '📋 Buka Draft', 'callback_data' => 'cfg:draft:open'],
                            ['text' => '📝 Input Lagi', 'callback_data' => 'cfg:input:start'],
                        ],
                    ],
                ],
            ]
        );

        if (($push['success'] ?? false) === true) {
            $this->sendLatestRequestStatus($chatId);
        }

        $this->notifyAdmin($payload);
    }

    private function validateDraftAgainstTemplate(array $draft): array
    {
        return $this->draftValidator()->validateDraftAgainstTemplate($draft);
    }

    private function adminActionApprove(string $adminChatId, string $token, array $from): void
    {
        $request = $this->getRequestByToken($token);
        if ($request === null) {
            $this->sendMessage($adminChatId, "⚠️ Request tidak ditemukan.");
            return;
        }

        if (($request['status'] ?? '') === self::STATUS_REJECTED || ($request['status'] ?? '') === self::STATUS_ONLINE) {
            $this->sendMessage($adminChatId, "ℹ️ Status request sudah final.");
            return;
        }

        $request['status'] = self::STATUS_MENUNGGU_PUSH_OLT;
        $request['pipeline']['menunggu_push_olt'] = now()->toDateTimeString();
        $request['history'][] = [
            'at' => now()->toDateTimeString(),
            'status' => self::STATUS_MENUNGGU_PUSH_OLT,
            'by' => $this->actorLabel($from),
            'note' => 'Admin approve (menunggu eksekusi dari website)',
        ];

        $this->saveRequest((string) $request['ref'], $request);
        $this->sendMessage($adminChatId, "✅ Approve tersimpan.\nStatus: Menunggu push OLT.\nLanjutkan proses dari panel website.");
        $this->notifyRequesterStatus($request, "✅ Request sudah di-approve.\nStatus: menunggu push dari admin website.");
    }

    private function adminActionMarkOltPushed(string $adminChatId, string $token, array $from): void
    {
        $request = $this->getRequestByToken($token);
        if ($request === null) {
            $this->sendMessage($adminChatId, "⚠️ Request tidak ditemukan.");
            return;
        }

        $request['status'] = self::STATUS_MENUNGGU_PPPOE_UP;
        $request['pipeline']['menunggu_pppoe_up'] = now()->toDateTimeString();
        $request['history'][] = [
            'at' => now()->toDateTimeString(),
            'status' => self::STATUS_MENUNGGU_PPPOE_UP,
            'by' => $this->actorLabel($from),
            'note' => 'OLT sudah dipush',
        ];
        $this->saveRequest((string) $request['ref'], $request);

        $this->sendMessage($adminChatId, "📌 OLT sudah dipush. Lanjut cek PPPoE sampai aktif.");
        $this->notifyRequesterStatus($request, "📌 OLT sudah dipush admin.");
    }

    private function adminActionMarkPppoeUp(string $adminChatId, string $token, array $from): void
    {
        $request = $this->getRequestByToken($token);
        if ($request === null) {
            $this->sendMessage($adminChatId, "⚠️ Request tidak ditemukan.");
            return;
        }

        $request['status'] = self::STATUS_ONLINE;
        $request['pipeline']['online'] = now()->toDateTimeString();
        $request['history'][] = [
            'at' => now()->toDateTimeString(),
            'status' => self::STATUS_ONLINE,
            'by' => $this->actorLabel($from),
            'note' => 'PPPoE sudah up/online',
        ];
        $this->saveRequest((string) $request['ref'], $request);

        $this->sendMessage($adminChatId, "🟢 Sudah ditandai ONLINE.");
        $this->notifyRequesterStatus($request, "🟢 Internet pelanggan sudah ONLINE.");
    }

    private function adminActionReject(string $adminChatId, string $token, array $from): void
    {
        $request = $this->getRequestByToken($token);
        if ($request === null) {
            $this->sendMessage($adminChatId, "⚠️ Request tidak ditemukan.");
            return;
        }

        $request['status'] = self::STATUS_REJECTED;
        $request['history'][] = [
            'at' => now()->toDateTimeString(),
            'status' => self::STATUS_REJECTED,
            'by' => $this->actorLabel($from),
            'note' => 'Request ditolak',
        ];
        $this->saveRequest((string) $request['ref'], $request);

        $this->sendMessage($adminChatId, "❌ Request ditolak.");
        $this->notifyRequesterStatus($request, "❌ Request ditolak admin. Silakan revisi dan submit ulang.");
    }

    private function autoCreateCustomer(array $payload): ?Customer
    {
        try {
            $draft = (array) ($payload['draft'] ?? []);
            $areaId = (int) ($draft['area_id'] ?? 0);
            $name = trim((string) ($draft['nama'] ?? ''));
            $pppoeUser = trim((string) ($draft['pppoe_user'] ?? ''));
            $pppoePass = trim((string) ($draft['pppoe_pass'] ?? 'netking'));
            $phone = trim((string) ($draft['no_hp'] ?? ''));
            $address = trim((string) ($draft['address'] ?? $draft['lokasi'] ?? ''));
            $latitude = isset($draft['latitude']) ? (float) $draft['latitude'] : null;
            $longitude = isset($draft['longitude']) ? (float) $draft['longitude'] : null;
            $sn = strtoupper(str_replace('-', '', trim((string) ($draft['sn_ont'] ?? ''))));
            $packageId = (int) ($draft['paket_id'] ?? 0);
            $packagePrice = (float) ($draft['harga'] ?? 0);
            $billingStart = (string) ($draft['tanggal_pasang'] ?? now()->toDateString());

            if ($areaId <= 0 || $name === '' || $pppoeUser === '') {
                return null;
            }

            // Check duplicate
            if (Customer::forAreaPppoe($areaId, $pppoeUser)->exists()) {
                return null;
            }

            // Resolve partner
            $partnerId = null;
            $fromUsername = trim((string) data_get($payload, 'from.username', ''));
            if ($fromUsername !== '') {
                $partner = User::where('role', 'partner')
                    ->whereRaw('LOWER(telegram_username) = ?', [mb_strtolower($fromUsername)])
                    ->first();
                if ($partner) $partnerId = $partner->id;
            }
            if (!$partnerId) {
                $single = User::where('role', 'partner')->where('area_id', $areaId)->count();
                if ($single === 1) {
                    $partnerId = User::where('role', 'partner')->where('area_id', $areaId)->value('id');
                }
            }

            $portalRaw = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($portalRaw) < 6) $portalRaw = '12345678';

            $customer = Customer::create([
                'partner_id' => $partnerId,
                'area_id' => $areaId,
                'package_id' => $packageId > 0 ? $packageId : null,
                'name' => $name,
                'pppoe_user' => $pppoeUser,
                'pppoe_pass' => $pppoePass,
                'portal_password' => \Illuminate\Support\Facades\Hash::make($portalRaw),
                'ont_sn' => $sn !== '' ? $sn : null,
                'package_price' => $packagePrice,
                'billing_start_date' => $billingStart,
                'phone' => $phone !== '' ? $phone : null,
                'address' => $address !== '' ? $address : null,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'status' => 'active',
            ]);

            // Link ONT if SN matches
            if ($sn !== '') {
                $ont = \App\Models\Ont::where('serial_number', 'LIKE', '%' . $sn . '%')
                    ->first();
                if ($ont) {
                    $ont->update(['customer_id' => $customer->id]);
                    $customer->update(['ont_sn' => $ont->serial_number]);
                }
            }

            return $customer;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('autoCreateCustomer failed', ['error' => $e->getMessage()]);
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
            $customerName = trim((string) ($draft['nama'] ?? ''));
            if ($pppoeUser === '') {
                return ['success' => false, 'error' => 'PPPoE User kosong'];
            }

            // Guard duplicate in MikroTik (fast path)
            $exists = $service->secretExists($pppoeUser);
            if (($exists['success'] ?? false) !== true) {
                return ['success' => false, 'error' => (string) ($exists['error'] ?? 'Gagal cek duplikasi PPPoE di MikroTik')];
            }
            if (($exists['exists'] ?? false) === true) {
                return ['success' => false, 'error' => 'PPPoE User sudah ada di MikroTik area'];
            }

            $result = $service->createSecret(
                username: $pppoeUser,
                password: $pppoePass,
                service: 'pppoe',
                profile: $profile,
                remoteAddress: null,
                localAddress: null,
                comment: $customerName !== '' ? $customerName : ('TELEGRAM:' . ($request['ref'] ?? ''))
            );

            if (($result['success'] ?? false) !== true) {
                return ['success' => false, 'error' => (string) ($result['error'] ?? 'Gagal create secret')];
            }

            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendDraftSummary(string $chatId, bool $withActions): void
    {
        $state = $this->getState($chatId);
        $draft = (array) ($state['draft'] ?? []);
        $price = isset($draft['harga']) ? number_format((int) $draft['harga'], 0, ',', '.') : '-';
        $fotoOk = !empty($draft['photo_file_id']) ? '✅ Ada' : '❌ Belum';
        $snOk = (($draft['photo_sn_verified'] ?? false) ? '✅ Cocok' : '❌ Belum cocok');
        $ocrSn = trim((string) ($draft['photo_sn_ocr'] ?? $draft['photo_sn_ont'] ?? ''));
        $ocrErr = trim((string) ($draft['photo_ocr_error'] ?? ''));
        $areaVlan = trim((string) ($draft['area_vlan_pppoe'] ?? ''));

        $lines = [
            "📋 DRAFT SIAP CEK",
            '',
            '├ Area: ' . ($draft['area_label'] ?? $draft['area_name'] ?? '-'),
            '├ VLAN PPPoE: ' . ($areaVlan !== '' ? $areaVlan : '-'),
            '├ Nama: ' . ($draft['nama'] ?? '-'),
            '├ No HP: ' . ($draft['no_hp'] ?? '-'),
            '├ Alamat: ' . ($draft['address'] ?? '-'),
            '├ Koordinat: ' . ($draft['coordinates'] ?? '-'),
            '├ SN ONT: ' . ($draft['sn_ont'] ?? '-'),
            '├ PPPoE User: ' . ($draft['pppoe_user'] ?? '-'),
            '├ PPPoE Pass: ' . ($draft['pppoe_pass'] ?? self::DEFAULT_PPPOE_PASSWORD),
            '├ Paket: ' . ($draft['paket_kode'] ?? '-') . ' (' . ($draft['paket_name'] ?? '-') . ')',
            '├ Harga: Rp ' . $price,
            '└ Tanggal Pasang: ' . $this->formatDateDisplay((string) ($draft['tanggal_pasang'] ?? now()->toDateString())),
            '',
            'Operator:',
            '• Nama: ' . (data_get($draft, 'requested_by.name', '-') ?: '-'),
            '• Username: @' . (data_get($draft, 'requested_by.telegram_username', '-') ?: '-'),
            '',
            'Foto SN: ' . $fotoOk,
            'OCR SN: ' . ($ocrSn !== '' ? $ocrSn : '-'),
            'Validasi SN: ' . $snOk,
        ];

        if ($ocrErr !== '') {
            $lines[] = 'Catatan OCR: ' . $ocrErr;
        }

        if (!empty($draft['photo_file_id'])) {
            $photoMsgId = $this->sendPhoto(
                $chatId,
                (string) $draft['photo_file_id'],
                implode("\n", $lines)
            );
            $this->rememberTransientMessage($chatId, $photoMsgId);
        } else {
            $summaryMsgId = $this->sendMessage($chatId, implode("\n", $lines));
            $this->rememberTransientMessage($chatId, $summaryMsgId);
        }

        if ($withActions) {
            $actionMsgId = $this->sendMessage(
                $chatId,
                "Pilih aksi:",
                [
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                ['text' => '✏️ Nama', 'callback_data' => 'cfg:edit:nama'],
                                ['text' => '✏️ No HP', 'callback_data' => 'cfg:edit:no_hp'],
                            ],
                            [
                                ['text' => '✏️ Alamat', 'callback_data' => 'cfg:edit:address'],
                                ['text' => '✏️ SN ONT', 'callback_data' => 'cfg:edit:sn_ont'],
                            ],
                            [
                                ['text' => '✏️ Koordinat', 'callback_data' => 'cfg:edit:coordinates'],
                                ['text' => '✏️ PPPoE', 'callback_data' => 'cfg:edit:pppoe_user'],
                            ],
                            [
                                ['text' => '🔁 Area', 'callback_data' => 'cfg:edit:area_id'],
                                ['text' => '📦 Paket', 'callback_data' => 'cfg:edit:paket_id'],
                            ],
                            [
                                ['text' => '📋 Draft', 'callback_data' => 'cfg:draft:open'],
                                ['text' => '✅ Submit', 'callback_data' => 'cfg:submit:go'],
                            ],
                            [
                                ['text' => '♻️ Reset', 'callback_data' => 'cfg:reset:all'],
                            ],
                        ],
                    ],
                ]
            );
            $this->rememberTransientMessage($chatId, $actionMsgId);
        }
    }

    private function sendGreeting(string $chatId, array $from): void
    {
        $name = (string) ($from['first_name'] ?? 'Partner');

        $text = "NETKING-SENUT siap bantu\n\n" .
            "Halo {$name} 👋\n" .
            "Langsung pilih menu di bawah buat mulai.";

        $this->sendMessage(
            $chatId,
            $text,
            [
                'reply_markup' => [
                    'keyboard' => $this->mainKeyboardRows(),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => false,
                ],
            ]
        );
    }

    private function sendMainKeyboard(string $chatId): void
    {
        $this->sendMessage(
            $chatId,
            "👇",
            [
                'reply_markup' => [
                    'keyboard' => $this->mainKeyboardRows(),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => false,
                ],
            ]
        );
    }

    private function mainKeyboardRows(): array
    {
        return [
            ['📝 Input', '📷 Foto SN', '📋 Draft'],
            ['🗂 History', '📡 Status', '✅ Submit'],
            ['🧾 Template', '♻️ Reset'],
        ];
    }

    private function sendMenuAttachedMessage(string $chatId, string $text): void
    {
        $this->sendMessage(
            $chatId,
            $text,
            [
                'reply_markup' => [
                    'keyboard' => $this->mainKeyboardRows(),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => false,
                ],
            ]
        );
    }

    private function sendLatestRequestStatus(string $chatId): void
    {
        $state = $this->getState($chatId);
        $token = (string) ($state['last_submitted_token'] ?? '');
        if ($token === '') {
            $this->sendMessage($chatId, "Belum ada request terakhir. Klik *Input* dulu ya.", ['parse_mode' => 'Markdown']);
            return;
        }

        $request = $this->getRequestByToken($token);
        if ($request === null) {
            $this->sendMessage($chatId, "Request terakhir tidak ketemu. Coba submit ulang ya.");
            return;
        }

        $status = (string) ($request['status'] ?? self::STATUS_DITERIMA);
        $pppoeUser = (string) data_get($request, 'draft.pppoe_user', '');
        $runtime = $this->checkPppoeRuntime($request);

        if (($runtime['active'] ?? false) === true && !in_array($status, [self::STATUS_ONLINE, self::STATUS_REJECTED], true)) {
            $status = self::STATUS_ONLINE;
            $request['status'] = self::STATUS_ONLINE;
            $request['pipeline']['online'] = now()->toDateTimeString();
            $request['history'][] = [
                'at' => now()->toDateTimeString(),
                'status' => self::STATUS_ONLINE,
                'by' => 'bot',
                'note' => 'Auto-detected PPPoE active di MikroTik',
            ];
            $this->saveRequest((string) ($request['ref'] ?? ''), $request);
        }

        $pppoeLive = match (($runtime['state'] ?? 'unknown')) {
            'active' => "🟢 Aktif di MikroTik",
            'inactive' => "⚪ Belum aktif di MikroTik",
            'unknown' => "🟠 Belum bisa cek (router belum terjangkau)",
            default => "🟨 {$runtime['state']}",
        };

        $historyRows = is_array($request['history'] ?? null) ? $request['history'] : [];
        $lastHistory = !empty($historyRows) ? (array) end($historyRows) : [];
        $lastBy = (string) ($lastHistory['by'] ?? '-');
        $lastAt = (string) ($lastHistory['at'] ?? '-');
        $runtimeInfo = '';
        if (($runtime['state'] ?? '') === 'active') {
            $uptime = (string) ($runtime['uptime'] ?? '-');
            $address = (string) ($runtime['address'] ?? '-');
            $runtimeInfo = "\nUptime sesi: {$uptime}\nIP sesi: {$address}";
        } elseif (($runtime['state'] ?? '') === 'unknown' && !empty($runtime['error'])) {
            $runtimeInfo = "\nCatatan cek: " . (string) $runtime['error'];
        }

        $text =
            "📡 Active Connection\n" .
            "━━━━━━━━━━━━━━━\n" .
            "PPPoE: " . ($pppoeUser !== '' ? $pppoeUser : '-') . "\n" .
            "Status: {$pppoeLive}\n" .
            "Update terakhir: {$lastAt} • {$lastBy}{$runtimeInfo}";

        $statusMsgId = $this->sendMessage($chatId, $text);
        $this->rememberTransientMessage($chatId, $statusMsgId);
    }

    private function checkPppoeRuntime(array $request): array
    {
        $draft = (array) ($request['draft'] ?? []);
        $pppoeUser = trim((string) ($draft['pppoe_user'] ?? ''));
        $areaId = (int) ($draft['area_id'] ?? 0);
        if ($pppoeUser === '' || $areaId <= 0) {
            return ['state' => 'unknown', 'active' => false, 'error' => 'Data area/PPPoE belum lengkap'];
        }

        try {
            $area = Area::query()->find($areaId);
            if (!$area) {
                return ['state' => 'unknown', 'active' => false, 'error' => 'Area tidak ditemukan'];
            }

            $service = MikroTikService::forArea($area);
            if (!$service->isConnected()) {
                return ['state' => 'unknown', 'active' => false, 'error' => 'Koneksi router area gagal'];
            }

            $sessions = $service->getActiveSessions($pppoeUser);
            if (($sessions['success'] ?? false) !== true) {
                return [
                    'state' => 'unknown',
                    'active' => false,
                    'error' => (string) ($sessions['error'] ?? 'Gagal baca active session'),
                ];
            }

            $rows = is_array($sessions['data'] ?? null) ? $sessions['data'] : [];
            if (count($rows) > 0) {
                $row = (array) $rows[0];
                return [
                    'state' => 'active',
                    'active' => true,
                    'uptime' => (string) ($row['uptime'] ?? '-'),
                    'address' => (string) ($row['address'] ?? '-'),
                ];
            }

            return ['state' => 'inactive', 'active' => false];
        } catch (\Throwable $e) {
            return ['state' => 'unknown', 'active' => false, 'error' => $e->getMessage()];
        }
    }

    private function notifyAdmin(array $payload): void
    {
        $adminChatId = trim($this->cfg('telegram_config_admin_chat_id', 'TELEGRAM_CONFIG_ADMIN_CHAT_ID', ''));
        if ($adminChatId === '') {
            return;
        }

        $draft = (array) ($payload['draft'] ?? []);
        $price = number_format((int) ($draft['harga'] ?? 0), 0, ',', '.');
        $text = "📥 REQUEST BARU\n" .
            "Area: " . ($draft['area_label'] ?? $draft['area_name'] ?? '-') . "\n" .
            "Nama: " . ($draft['nama'] ?? '-') . "\n" .
            "PPPoE: " . ($draft['pppoe_user'] ?? '-') . "\n" .
            "SN: " . ($draft['sn_ont'] ?? '-') . "\n" .
            "No HP: " . ($draft['no_hp'] ?? '-') . "\n" .
            "Paket: " . ($draft['paket_kode'] ?? '-') . " / Rp {$price}\n" .
            "Profile: " . ($draft['mikrotik_profile'] ?? '-') . "\n" .
            "Tgl Pasang: " . $this->formatDateDisplay((string) ($draft['tanggal_pasang'] ?? '-'));

        $buttons = [[
            ['text' => '❌ Reject', 'callback_data' => 'cfg:arej:' . $payload['token']],
            ['text' => '🟢 PPPoE Up', 'callback_data' => 'cfg:aup:' . $payload['token']],
        ]];

        $this->sendMessage($adminChatId, $text, ['reply_markup' => ['inline_keyboard' => $buttons]]);
        if (!empty($draft['photo_file_id'])) {
            $this->sendPhoto($adminChatId, (string) $draft['photo_file_id'], "📷 Foto SN pelanggan");
        }
    }

    private function notifyRequesterStatus(array $request, string $message): void
    {
        $chatId = (string) ($request['chat_id'] ?? '');
        if ($chatId === '') {
            return;
        }
        $this->sendMessage($chatId, $message);
    }

    private function sendGuideMenu(string $chatId): void
    {
        $this->sendChatAction($chatId, 'typing');

        $areas = Area::query()
            ->orderBy('name')
            ->get(['id', 'name', 'router_ip']);

        if ($areas->isEmpty()) {
            $this->sendMessage($chatId, "⚠️ Data area belum tersedia.");
            return;
        }

        $rows = [];
        $chunk = [];
        foreach ($areas as $area) {
            $chunk[] = [
                'text' => $area->name,
                'callback_data' => 'cfg:garea:' . $area->id,
            ];

            if (count($chunk) === 2) {
                $rows[] = $chunk;
                $chunk = [];
            }
        }
        if (!empty($chunk)) {
            $rows[] = $chunk;
        }

        $this->sendMessage(
            $chatId,
            "📚 *Panduan MikroTik PPPoE*\nPilih area dulu ya. Nanti saya tampilkan router, PPPoE terakhir, dan paketnya.",
            [
                'parse_mode' => 'Markdown',
                'reply_markup' => ['inline_keyboard' => $rows],
            ]
        );
    }

    private function sendGuideArea(string $chatId, int $areaId): void
    {
        $this->sendChatAction($chatId, 'typing');

        $area = Area::query()->find($areaId, ['id', 'name', 'router_ip']);
        if (!$area) {
            $this->sendMessage($chatId, "⚠️ Area tidak ditemukan.");
            return;
        }

        $latest = Customer::query()
            ->where('area_id', $area->id)
            ->whereNotNull('pppoe_user')
            ->orderByDesc('id')
            ->first(['pppoe_user', 'name']);

        $packages = Package::query()
            ->where('area_id', $area->id)
            ->where('is_active', true)
            ->orderBy('speed_down')
            ->get(['code', 'speed_down', 'speed_up', 'price', 'mikrotik_profile']);

        $lines = [
            "📍 *{$area->name}*",
            "Router: `{$area->router_ip}`",
            "PPPoE terakhir: `" . ($latest?->pppoe_user ?: '-') . "`",
            "Nama pelanggan: *" . ($latest?->name ?: '-') . "*",
            "",
        ];

        if ($packages->isEmpty()) {
            $lines[] = "⚠️ Paket aktif belum ada.";
        } else {
            $lines[] = "📦 *Paket Aktif*";
            foreach ($packages as $p) {
                $price = number_format((int) $p->price, 0, ',', '.');
                $profile = $p->mikrotik_profile ?: $p->code;
                $lines[] = "• `{$p->code}` • {$p->speed_down}M/{$p->speed_up}M • Rp{$price}";
                $lines[] = "  profile: `{$profile}`";
            }
        }

        $this->sendMessage(
            $chatId,
            implode("\n", $lines),
            [
                'parse_mode' => 'Markdown',
                'reply_markup' => [
                    'inline_keyboard' => [[
                        ['text' => '🔙 Kembali ke Daftar Area', 'callback_data' => 'cfg:guide:menu'],
                    ]],
                ],
            ]
        );
    }

    private function sendDraftTemplate(string $chatId): void
    {
        $template = "🧾 TEMPLATE DRAFT (copy-paste)\n\n" .
            "AREA: [pilih area]\n" .
            "NAMA: [nama pelanggan]\n" .
            "No HP: [08xxxxxxxxxx]\n" .
            "Alamat: [alamat pelanggan]\n" .
            "Koordinat: [-6.123456, 107.123456]\n" .
            "SN ONT: [contoh: CDTCAF1234AB]\n" .
            "PPPoE User: [contoh: N-010]\n" .
            "PPPoE Pass: netking\n" .
            "Paket: [6M/8M/10M]\n" .
            "HARGA: [angka saja]\n\n" .
            "Tips:\n" .
            "- PPPoE otomatis dicek duplikat per area.\n" .
            "- Tanggal pasang otomatis pakai tanggal hari ini.\n" .
            "- Koordinat bisa share location atau ketik format lat,lng.\n" .
            "- Foto SN tetap wajib kirim lewat tombol 📷.\n" .
            "- Foto SN harus terbaca OCR dari gambar, jadi kirim yang fokus dan dekat.";

        $this->sendMessage(
            $chatId,
            $template,
            [
                'reply_markup' => [
                    'inline_keyboard' => [[
                        ['text' => '📝 Mulai Input Sekarang', 'callback_data' => 'cfg:input:start'],
                    ]],
                ],
            ]
        );
    }

    private function beginEditField(string $chatId, string $field): void
    {
        $allowed = ['nama', 'no_hp', 'address', 'coordinates', 'sn_ont', 'pppoe_user', 'area_id', 'paket_id'];
        if (!in_array($field, $allowed, true)) {
            $this->sendMessage($chatId, "⚠️ Field edit tidak valid.");
            return;
        }

        $state = $this->getState($chatId);
        $state['collecting'] = true;
        $state['edit_field'] = $field;
        $state['field_index'] = $this->fieldIndex($field);
        $state['updated_at'] = now()->toDateTimeString();
        $this->saveState($chatId, $state);

        $this->promptCurrentField($chatId, $state);
    }

    private function normalizeFieldValue(string $field, string $input): string
    {
        $value = trim($input);
        if ($field === 'no_hp') {
            $digits = preg_replace('/\D+/', '', $value) ?? '';
            if (str_starts_with($digits, '62')) {
                return '0' . substr($digits, 2);
            }
            return $digits;
        }

        if ($field === 'sn_ont') {
            return strtoupper(preg_replace('/\s+/', '', $value) ?? '');
        }

        if ($field === 'coordinates') {
            $parsed = $this->parseCoordinates($value);
            return $parsed ? $this->formatCoordinates($parsed['latitude'], $parsed['longitude']) : $value;
        }

        if ($field === 'pppoe_user') {
            return strtoupper($value);
        }

        if ($field === 'tanggal_pasang') {
            $normalized = $this->normalizeDateValue($value);
            return $normalized ?? $value;
        }

        return $value;
    }

    private function parseCoordinates(string $value): ?array
    {
        $raw = trim($value);
        if ($raw === '') {
            return null;
        }

        $raw = preg_replace('/\s+/', ' ', $raw) ?? $raw;
        if (!preg_match('/^\s*(-?\d{1,3}(?:\.\d+)?)\s*,\s*(-?\d{1,3}(?:\.\d+)?)\s*$/', $raw, $matches)) {
            return null;
        }

        $latitude = (float) $matches[1];
        $longitude = (float) $matches[2];
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return null;
        }

        return [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }

    private function formatCoordinates(float $latitude, float $longitude): string
    {
        return number_format($latitude, 6, '.', '') . ', ' . number_format($longitude, 6, '.', '');
    }

    private function extractSnFromTelegramPhoto(string $fileId): array
    {
        $fileUrl = $this->resolveTelegramFileUrl($fileId);
        if ($fileUrl === null) {
            return ['success' => false, 'error' => 'File foto Telegram tidak bisa diambil'];
        }

        $tmpDir = storage_path('app/private/telegram-config-bot/tmp');
        if (!is_dir($tmpDir) && !@mkdir($tmpDir, 0775, true) && !is_dir($tmpDir)) {
            return ['success' => false, 'error' => 'Folder OCR sementara gagal dibuat'];
        }

        $tmpBase = tempnam($tmpDir, 'sn_');
        if ($tmpBase === false) {
            return ['success' => false, 'error' => 'Gagal membuat file sementara OCR'];
        }

        $tmpImage = $tmpBase . '.jpg';
        @rename($tmpBase, $tmpImage);

        try {
            $download = Http::timeout(15)->get($fileUrl);
            if (!$download->successful()) {
                return ['success' => false, 'error' => 'Download foto Telegram gagal'];
            }

            file_put_contents($tmpImage, $download->body());

            $attempts = [
                ['--psm', '6'],
                ['--psm', '7'],
                ['--psm', '11'],
            ];

            $bestRaw = '';
            foreach ($attempts as $args) {
                $process = new Process([
                    'tesseract',
                    $tmpImage,
                    'stdout',
                    ...$args,
                    '-c',
                    'tessedit_char_whitelist=ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-',
                ]);
                $process->setTimeout(20);
                $process->run();

                if (!$process->isSuccessful()) {
                    $error = trim($process->getErrorOutput());
                    if (str_contains(strtolower($error), 'not recognized') || str_contains(strtolower($error), 'not found')) {
                        return ['success' => false, 'error' => 'Tesseract OCR belum terpasang di server'];
                    }
                    continue;
                }

                $rawText = strtoupper(trim($process->getOutput()));
                if ($rawText === '') {
                    continue;
                }

                $bestRaw = $rawText;
                $sn = $this->extractSnFromText($rawText);
                if ($sn !== null) {
                    return [
                        'success' => true,
                        'raw_text' => $rawText,
                        'sn' => strtoupper($sn),
                    ];
                }
            }

            return [
                'success' => false,
                'error' => $bestRaw !== '' ? 'OCR belum menemukan serial SN yang valid' : 'OCR tidak membaca teks dari foto',
                'raw_text' => $bestRaw,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        } finally {
            if (is_file($tmpImage)) {
                @unlink($tmpImage);
            }
            if (is_file($tmpBase)) {
                @unlink($tmpBase);
            }
        }
    }

    private function resolveTelegramFileUrl(string $fileId): ?string
    {
        $fileId = trim($fileId);
        if ($fileId === '') {
            return null;
        }

        $token = trim($this->cfg('telegram_config_bot_token', 'TELEGRAM_CONFIG_BOT_TOKEN', ''));
        if ($token === '') {
            return null;
        }

        $response = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getFile", [
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
    }

    private function validateFieldValue(string $field, string $value, array $state): ?string
    {
        return $this->draftValidator()->validateFieldValue($field, $value);
    }

    private function findDuplicatePppoe(int $areaId, string $pppoeUser): ?array
    {
        if ($areaId <= 0 || trim($pppoeUser) === '') {
            return null;
        }

        $customer = Customer::query()
            ->select(['id', 'name', 'pppoe_user'])
            ->where('area_id', $areaId)
            ->whereRaw('LOWER(TRIM(pppoe_user)) = ?', [mb_strtolower(trim($pppoeUser))])
            ->first();

        if (!$customer) {
            return null;
        }

        return [
            'id' => $customer->id,
            'name' => (string) $customer->name,
            'pppoe_user' => (string) $customer->pppoe_user,
        ];
    }

    private function getLastPppoeByArea(int $areaId): ?array
    {
        if ($areaId <= 0) {
            return null;
        }

        $dbLatest = Customer::query()
            ->select(['name', 'pppoe_user'])
            ->where('area_id', $areaId)
            ->whereNotNull('pppoe_user')
            ->where('pppoe_user', '!=', '')
            ->orderByDesc('id')
            ->first();

        $expectedPrefix = $this->detectAreaPppoePrefix($areaId, $dbLatest?->pppoe_user);

        // Prioritaskan data live dari MikroTik supaya hint "secret terakhir" akurat.
        $area = Area::query()->find($areaId);
        if ($area) {
            try {
                $service = MikroTikService::forArea($area);
                if ($service->isConnected()) {
                    $all = $service->getAllSecrets();
                    if (($all['success'] ?? false) === true) {
                        $rows = is_array($all['data'] ?? null) ? $all['data'] : [];
                        $best = $this->pickLatestRouterSecret($rows, $expectedPrefix);

                        if (is_array($best)) {
                            return [
                                'name' => trim((string) ($best['comment'] ?? '')) ?: '-',
                                'pppoe_user' => trim((string) ($best['name'] ?? '')),
                                'source' => 'router',
                            ];
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Fallback ke DB jika router tidak bisa dibaca.
            }
        }

        if (!$dbLatest) {
            return null;
        }

        return [
            'name' => (string) $dbLatest->name,
            'pppoe_user' => (string) $dbLatest->pppoe_user,
            'source' => 'db',
        ];
    }

    private function pickLatestRouterSecret(array $rows, ?string $expectedPrefix = null): ?array
    {
        $prefixed = [];
        $fallback = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $service = mb_strtolower(trim((string) ($row['service'] ?? 'pppoe')));
            if ($service !== '' && $service !== 'pppoe') {
                continue;
            }

            if (preg_match('/-\d+$/', $name) !== 1) {
                continue;
            }

            $fallback[] = $row;
            if ($expectedPrefix !== null && $this->pppoeMatchesPrefix($name, $expectedPrefix)) {
                $prefixed[] = $row;
            }
        }

        $candidates = !empty($prefixed) ? $prefixed : $fallback;
        if (empty($candidates)) {
            return null;
        }

        $best = null;
        $bestScore = -1;
        foreach ($candidates as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $score = $this->routerSecretOrderScore($row, $name, $expectedPrefix);
            if ($score >= $bestScore) {
                $bestScore = $score;
                $best = $row;
            }
        }

        return $best;
    }

    private function detectAreaPppoePrefix(int $areaId, ?string $latestDbUser = null): ?string
    {
        $latestDbUser = trim((string) $latestDbUser);
        if ($latestDbUser !== '' && preg_match('/^([A-Z0-9]+)-\d+$/i', $latestDbUser, $m)) {
            return strtoupper($m[1]);
        }

        $area = Area::query()->find($areaId, ['name', 'router_identity']);
        if (!$area) {
            return null;
        }

        $routerIdentity = trim((string) ($area->router_identity ?? ''));
        if ($routerIdentity !== '' && preg_match('/^([A-Z0-9]+)-/i', $routerIdentity, $m)) {
            return strtoupper($m[1]);
        }

        $areaName = trim((string) ($area->name ?? ''));
        if ($areaName !== '' && preg_match('/^([A-Z0-9]+)[\s-]/i', $areaName, $m)) {
            return strtoupper($m[1]);
        }

        return null;
    }

    private function pppoeMatchesPrefix(string $name, ?string $expectedPrefix): bool
    {
        if ($expectedPrefix === null || $expectedPrefix === '') {
            return true;
        }

        return preg_match('/^' . preg_quote($expectedPrefix, '/') . '-\d+$/i', $name) === 1;
    }

    private function routerSecretOrderScore(array $row, string $name, ?string $expectedPrefix = null): int
    {
        if ($expectedPrefix !== null && preg_match('/^' . preg_quote($expectedPrefix, '/') . '-(\d+)$/i', $name, $m)) {
            return (int) $m[1];
        }

        if (preg_match('/-(\d+)$/', $name, $m)) {
            return (int) $m[1];
        }

        $idRaw = (string) ($row['.id'] ?? '');
        if ($idRaw !== '' && preg_match('/\*([0-9A-Fa-f]+)/', $idRaw, $m)) {
            $hex = $m[1];
            $idVal = intval(base_convert($hex, 16, 10));
            if ($idVal > 0) {
                return $idVal;
            }
        }

        return 0;
    }

    private function extractSnFromText(string $text): ?string
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        if (preg_match('/SN(?:_ONT)?\s*[:=]\s*([A-Z0-9-]{6,24})/i', $text, $m)) {
            return strtoupper(str_replace('-', '', trim($m[1])));
        }

        if (preg_match('/\b([A-Z0-9]{8,20})\b/i', $text, $m)) {
            return strtoupper(trim($m[1]));
        }

        return null;
    }

    private function sendMyHistory(string $chatId): void
    {
        $files = Storage::disk('local')->files(self::BOT_DIR . '/requests');
        $items = [];
        foreach ($files as $file) {
            if (!str_ends_with($file, '.json')) {
                continue;
            }
            $raw = Storage::disk('local')->get($file);
            $data = json_decode($raw, true);
            if (!is_array($data)) {
                continue;
            }
            if ((string) ($data['chat_id'] ?? '') !== $chatId) {
                continue;
            }

            $items[] = [
                'status' => (string) ($data['status'] ?? '-'),
                'submitted_at' => (string) ($data['submitted_at'] ?? ''),
                'pppoe_user' => (string) data_get($data, 'draft.pppoe_user', '-'),
                'area_name' => (string) (data_get($data, 'draft.area_label') ?: data_get($data, 'draft.area_name', '-')),
            ];
        }

        if (empty($items)) {
            $this->sendMessage($chatId, "🗂 Belum ada history request dari akun ini.");
            return;
        }

        usort($items, fn ($a, $b) => strcmp($b['submitted_at'], $a['submitted_at']));
        $items = array_slice($items, 0, 10);

        $lines = ["🗂 HISTORY REQUEST (10 terakhir)", ""];
        foreach ($items as $it) {
            $statusLabel = $this->humanStatusLabel((string) ($it['status'] ?? ''));
            $lines[] = "• {$it['area_name']} | {$it['pppoe_user']}";
            $lines[] = "  status: {$statusLabel} | {$it['submitted_at']}";
        }

        $this->sendMessage($chatId, implode("\n", $lines));
    }

    private function isValidDate(string $value): bool
    {
        return $this->draftValidator()->isValidDate($value);
    }

    private function normalizeDateValue(string $value): ?string
    {
        return $this->draftValidator()->normalizeDateValue($value);
    }

    private function formatDateDisplay(string $value): string
    {
        return $this->draftValidator()->formatDateDisplay($value);
    }

    private function humanStatusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_DITERIMA => 'Diterima',
            self::STATUS_MENUNGGU_PUSH_OLT => 'Menunggu Push OLT',
            self::STATUS_MENUNGGU_PPPOE_UP => 'Menunggu PPPoE Aktif',
            self::STATUS_ONLINE => 'Online',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_FAILED_MIKROTIK => 'Gagal Push MikroTik',
            default => trim(str_replace('_', ' ', $status)) !== '' ? trim(str_replace('_', ' ', $status)) : '-',
        };
    }

    private function renderProgress(int $currentIndex, int $total): string
    {
        $current = max(1, min($total, $currentIndex + 1));
        $filled = str_repeat('●', $current);
        $empty = str_repeat('○', max(0, $total - $current));
        return $filled . $empty;
    }

    private function fieldIndex(string $field): int
    {
        $idx = array_search($field, self::FLOW_FIELDS, true);
        return $idx === false ? 0 : (int) $idx;
    }

    private function getState(string $chatId): array
    {
        $path = self::BOT_DIR . '/states/' . $chatId . '.json';
        if (!Storage::disk('local')->exists($path)) {
            return [
                'collecting' => false,
                'field_index' => 0,
                'draft' => ['pppoe_pass' => 'netking'],
            ];
        }

        $raw = Storage::disk('local')->get($path);
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return [
                'collecting' => false,
                'field_index' => 0,
                'draft' => ['pppoe_pass' => 'netking'],
            ];
        }

        return $data;
    }

    private function saveState(string $chatId, array $state): void
    {
        $path = self::BOT_DIR . '/states/' . $chatId . '.json';
        Storage::disk('local')->put(
            $path,
            json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    private function resetInputState(string $chatId, bool $clearDraft): void
    {
        $state = $this->getState($chatId);
        $state['collecting'] = false;
        $state['field_index'] = 0;
        if ($clearDraft) {
            $state['draft'] = ['pppoe_pass' => 'netking'];
        }
        $this->saveState($chatId, $state);
    }

    private function requestToken(): string
    {
        return Str::upper(Str::random(10));
    }

    private function saveRequest(string $ref, array $payload): void
    {
        Storage::disk('local')->put(
            self::BOT_DIR . '/requests/' . $ref . '.json',
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    private function loadRequest(string $ref): ?array
    {
        $path = self::BOT_DIR . '/requests/' . $ref . '.json';
        if (!Storage::disk('local')->exists($path)) {
            return null;
        }
        $raw = Storage::disk('local')->get($path);
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    private function saveRequestIndex(string $token, string $ref): void
    {
        $path = self::BOT_DIR . '/requests/index.json';
        $map = [];
        if (Storage::disk('local')->exists($path)) {
            $raw = Storage::disk('local')->get($path);
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $map = $decoded;
            }
        }
        $map[$token] = $ref;
        Storage::disk('local')->put(
            $path,
            json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    private function getRequestByToken(string $token): ?array
    {
        $path = self::BOT_DIR . '/requests/index.json';
        if (!Storage::disk('local')->exists($path)) {
            return null;
        }
        $raw = Storage::disk('local')->get($path);
        $map = json_decode($raw, true);
        if (!is_array($map)) {
            return null;
        }
        $ref = (string) ($map[$token] ?? '');
        if ($ref === '') {
            return null;
        }
        return $this->loadRequest($ref);
    }

    private function actorLabel(array $from): string
    {
        $u = (string) ($from['username'] ?? '');
        if ($u !== '') {
            return '@' . $u;
        }
        $name = (string) ($from['first_name'] ?? '');
        return $name !== '' ? $name : 'admin';
    }

    private function syncTelegramIdentity(array $from, string $chatId): void
    {
        $username = trim((string) ($from['username'] ?? ''));
        if ($username === '') {
            return;
        }

        $user = User::query()
            ->whereRaw('LOWER(telegram_username) = ?', [mb_strtolower($username)])
            ->first();

        if (!$user) {
            return;
        }

        if ((string) ($user->telegram_chat_id ?? '') !== $chatId) {
            $user->telegram_chat_id = $chatId;
            $user->save();
        }
    }

    private function isAllowed(array $from): bool
    {
        $allowedIds = $this->allowedIds();
        $id = (string) ($from['id'] ?? '');
        if (!empty($allowedIds) && in_array($id, $allowedIds, true)) {
            return true;
        }

        $usernameRaw = trim((string) ($from['username'] ?? ''));
        $username = ltrim($usernameRaw, '@');

        // Jika tidak ada allow-list ID, minimal username terdaftar di user table juga diizinkan.
        // Jika ada allow-list ID, ini jadi fallback aman untuk akun yang sudah didaftarkan admin di website.
        if ($username !== '') {
            $user = User::query()
                ->whereRaw('LOWER(telegram_username) = ?', [mb_strtolower($username)])
                ->first();

            if ($user) {
                return true;
            }
        }

        // Fallback terakhir: jika chat_id sudah pernah tertaut ke user di website.
        if ($id !== '') {
            $userByChatId = User::query()
                ->where('telegram_chat_id', $id)
                ->first();
            if ($userByChatId) {
                return true;
            }
        }

        // Default lama tetap dipertahankan: jika allow-list kosong, bot tetap bisa dipakai.
        return empty($allowedIds);
    }

    private function allowedIds(): array
    {
        $value = trim($this->cfg('telegram_config_allowed_ids', 'TELEGRAM_CONFIG_ALLOWED_IDS', ''));
        if ($value === '') {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    private function isSecretValid(string $secret): bool
    {
        $expected = $this->cfg('telegram_config_bot_secret', 'TELEGRAM_CONFIG_BOT_SECRET', '');
        if ($expected === '') {
            return false;
        }
        return hash_equals($expected, $secret);
    }

    private function sendChatAction(string $chatId, string $action = 'typing'): void
    {
        // Keep webhook response fast: skip optional chat-action calls.
        return;
    }

    private function sendMessage(string $chatId, string $text, array $extra = []): ?int
    {
        $track = (bool) ($extra['no_track'] ?? false) === false;
        unset($extra['no_track']);

        $payload = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
        ], $extra);

        $response = $this->callTelegram('sendMessage', $payload);
        $messageId = data_get($response, 'result.message_id');
        $id = is_numeric($messageId) ? (int) $messageId : null;
        if ($track) {
            $this->rememberTransientMessage($chatId, $id);
        }
        return $id;
    }

    private function sendPhoto(string $chatId, string $photoFileId, string $caption = ''): ?int
    {
        $payload = [
            'chat_id' => $chatId,
            'photo' => $photoFileId,
        ];
        if ($caption !== '') {
            $payload['caption'] = mb_substr($caption, 0, 1020);
        }

        $response = $this->callTelegram('sendPhoto', $payload);
        $messageId = data_get($response, 'result.message_id');
        $id = is_numeric($messageId) ? (int) $messageId : null;
        $this->rememberTransientMessage($chatId, $id);
        return $id;
    }

    private function answerCallbackQuery(string $callbackId, string $text = ''): void
    {
        if ($callbackId === '') {
            return;
        }

        $payload = ['callback_query_id' => $callbackId];
        if ($text !== '') {
            $payload['text'] = mb_substr($text, 0, 180);
            $payload['show_alert'] = false;
        }

        $this->callTelegram('answerCallbackQuery', $payload);
    }

    private function showFuturisticLoading(string $chatId, string $label = 'Memproses'): void
    {
        $this->clearLoadingMessage($chatId);
        $title = $this->prettyLoadingLabel($label);

        $msgId = $this->sendMessage(
            $chatId,
            "⏳ {$title}\n██░░░░ 30%",
            ['no_track' => true]
        );
        if ($msgId === null || $msgId <= 0) {
            return;
        }

        $this->rememberLoadingMessage($chatId, $msgId);

        usleep(10000);
        $this->editMessageText($chatId, $msgId, "✅ {$title}\n██████ 100%");
    }

    private function prettyLoadingLabel(string $label): string
    {
        $key = mb_strtolower(trim($label));
        return match ($key) {
            'booting' => 'Menyiapkan menu',
            'siapkan form' => 'Menyiapkan form',
            'validasi submit' => 'Mengecek data',
            'muat draft' => 'Membuka draft',
            'muat template' => 'Membuka template',
            'muat guide' => 'Membuka panduan',
            'muat area' => 'Memuat area',
            'muat detail' => 'Memuat detail',
            'cek status' => 'Mengecek status live',
            'sinkron area' => 'Menyimpan area',
            'sinkron paket' => 'Menyimpan paket',
            'approve' => 'Menyimpan approval',
            'status olt' => 'Update status OLT',
            'status pppoe' => 'Update status PPPoE',
            'reject' => 'Menyimpan penolakan',
            default => $label,
        };
    }

    private function deleteMessage(string $chatId, int $messageId): void
    {
        $this->callTelegram('deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
    }

    private function editMessageText(string $chatId, int $messageId, string $text): void
    {
        if ($messageId <= 0) {
            return;
        }

        $this->callTelegram('editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
        ]);
    }

    private function callTelegram(string $method, array $payload): ?array
    {
        $token = trim($this->cfg('telegram_config_bot_token', 'TELEGRAM_CONFIG_BOT_TOKEN', ''));
        if ($token === '') {
            Log::warning('telegram_config_bot_missing_token', ['method' => $method]);
            return null;
        }

        $url = "https://api.telegram.org/bot{$token}/{$method}";

        try {
            $res = Http::asJson()
                ->connectTimeout(1)
                ->timeout(2)
                ->post($url, $payload);

            if (!$res->successful()) {
                Log::warning('telegram_config_bot_telegram_error', [
                    'method' => $method,
                    'status' => $res->status(),
                    'body' => $res->body(),
                ]);
                return null;
            }

            $json = $res->json();
            return is_array($json) ? $json : null;
        } catch (\Throwable $e) {
            Log::warning('telegram_config_bot_http_exception', [
                'method' => $method,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function rememberTransientMessage(string $chatId, ?int $messageId): void
    {
        if ($messageId === null || $messageId <= 0) {
            return;
        }

        $state = $this->getState($chatId);
        $ids = array_values(array_filter(array_map('intval', (array) ($state['transient_message_ids'] ?? []))));
        $ids[] = $messageId;
        $state['transient_message_ids'] = array_slice(array_unique($ids), -15);
        $this->saveState($chatId, $state);
    }

    private function rememberPromptMessage(string $chatId, ?int $messageId): void
    {
        if ($messageId === null || $messageId <= 0) {
            return;
        }

        $state = $this->getState($chatId);
        $state['last_prompt_message_id'] = (int) $messageId;
        $this->saveState($chatId, $state);
    }

    private function clearPromptMessage(string $chatId): void
    {
        $state = $this->getState($chatId);
        $lastPromptId = (int) ($state['last_prompt_message_id'] ?? 0);
        if ($lastPromptId > 0) {
            $this->deleteMessage($chatId, $lastPromptId);
        }

        $state['last_prompt_message_id'] = 0;
        $this->saveState($chatId, $state);
    }

    private function rememberLoadingMessage(string $chatId, int $messageId): void
    {
        if ($messageId <= 0) {
            return;
        }

        $state = $this->getState($chatId);
        $state['last_loading_message_id'] = $messageId;
        $this->saveState($chatId, $state);
    }

    private function clearLoadingMessage(string $chatId): void
    {
        $state = $this->getState($chatId);
        $lastLoadingId = (int) ($state['last_loading_message_id'] ?? 0);
        if ($lastLoadingId > 0) {
            $this->deleteMessage($chatId, $lastLoadingId);
        }

        $state['last_loading_message_id'] = 0;
        $this->saveState($chatId, $state);
    }

    private function cleanupTransientMessages(string $chatId, array $keep = []): void
    {
        $state = $this->getState($chatId);
        $ids = array_values(array_filter(array_map('intval', (array) ($state['transient_message_ids'] ?? []))));
        if (empty($ids)) {
            return;
        }

        $keepMap = array_flip(array_values(array_filter(array_map('intval', $keep))));
        foreach ($ids as $id) {
            if (isset($keepMap[$id])) {
                continue;
            }
            $this->deleteMessage($chatId, $id);
        }

        $state['transient_message_ids'] = [];
        $this->saveState($chatId, $state);
    }

    private function purgeRecentMessages(string $chatId, int $currentMessageId, int $window = 80): void
    {
        if ($currentMessageId <= 0) {
            return;
        }

        $from = max(1, $currentMessageId - max(5, $window));
        for ($id = $currentMessageId; $id >= $from; $id--) {
            $this->deleteMessage($chatId, $id);
        }
    }

    private function cfg(string $settingKey, string $envKey, string $default = ''): string
    {
        $value = trim((string) Setting::get($settingKey, ''));
        if ($value !== '') {
            return $value;
        }

        return trim((string) env($envKey, $default));
    }
}
