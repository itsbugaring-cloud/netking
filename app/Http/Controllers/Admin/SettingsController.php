<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        $token = (string) ($settings['telegram_config_bot_token'] ?? '');
        $secret = (string) ($settings['telegram_config_bot_secret'] ?? '');
        $webhookUrl = ($token !== '' && $secret !== '')
            ? url('/api/telegram/config/webhook/' . $secret)
            : null;

        $telegram = [
            'has_token' => $token !== '',
            'masked_token' => $this->maskToken($token),
            'webhook_url' => $webhookUrl,
        ];

        return view('admin.settings', compact('settings', 'telegram'));
    }

    public function update(Request $request)
    {
        $group = $request->input('group', 'general');

        // Whitelist allowed settings keys per group
        $allowedKeys = [
            'general' => ['company_name', 'company_address', 'company_phone', 'company_email', 'company_logo', 'timezone', 'currency', 'language'],
            'billing' => [
                'billing_day',
                'due_days',
                'invoice_prefix',
                'tax_rate',
                'grace_period_days',
                'auto_generate_invoices',
                'payment_bank_1_name',
                'payment_bank_1_number',
                'payment_bank_1_holder',
                'payment_bank_2_name',
                'payment_bank_2_number',
                'payment_bank_2_holder',
                'payment_qris_label',
                'payment_qris_image_url',
                'payment_qris_notes',
                'manual_payment_notes',
                'late_fee_percent',
            ],
            'mikrotik' => ['mikrotik_host', 'mikrotik_port', 'mikrotik_username'],
            'notification' => ['notify_overdue', 'notify_new_customer', 'notify_payment'],
            'notifications' => ['notif_email', 'notif_sms'],
            'telegram_bot' => [
                'telegram_config_bot_token',
                'telegram_config_bot_secret',
                'telegram_config_admin_chat_id',
                'telegram_config_allowed_ids',
                'telegram_config_mode',
            ],
        ];

        $keys = $allowedKeys[$group] ?? [];
        $data = $request->only($keys);

        // Basic sanitization: strip tags from all string values
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = strip_tags(trim($value));
            }
        }

        // Do not overwrite sensitive fields when left blank in UI
        if ($group === 'telegram_bot') {
            foreach (['telegram_config_bot_token', 'telegram_config_bot_secret'] as $sensitiveKey) {
                if (($data[$sensitiveKey] ?? '') === '') {
                    unset($data[$sensitiveKey]);
                }
            }
        }

        Setting::setMany($data, $group);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Settings saved successfully']);
        }

        return back()->with('success', 'Settings saved successfully');
    }

    public function telegramTestToken()
    {
        $token = (string) Setting::get('telegram_config_bot_token', '');
        if ($token === '') {
            return response()->json([
                'success' => false,
                'message' => 'Token Telegram belum diisi.',
            ], 422);
        }

        try {
            $res = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getMe");
            $json = $res->json();
            if (!$res->successful() || !($json['ok'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid atau API Telegram tidak merespons.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Token valid.',
                'bot' => [
                    'id' => data_get($json, 'result.id'),
                    'username' => data_get($json, 'result.username'),
                    'name' => data_get($json, 'result.first_name'),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal konek ke Telegram API: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function telegramSetWebhook()
    {
        $token = (string) Setting::get('telegram_config_bot_token', '');
        $secret = (string) Setting::get('telegram_config_bot_secret', '');
        if ($token === '' || $secret === '') {
            return response()->json([
                'success' => false,
                'message' => 'Token/Secret Telegram belum lengkap.',
            ], 422);
        }

        $url = url('/api/telegram/config/webhook/' . $secret);

        try {
            $res = Http::asForm()->timeout(10)->post("https://api.telegram.org/bot{$token}/setWebhook", [
                'url' => $url,
                'allowed_updates' => json_encode(['message', 'callback_query']),
            ]);
            $json = $res->json();

            if (!$res->successful() || !($json['ok'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal set webhook.',
                    'detail' => $json,
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Webhook berhasil diset.',
                'webhook_url' => $url,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal konek ke Telegram API: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function telegramWebhookInfo()
    {
        $token = (string) Setting::get('telegram_config_bot_token', '');
        if ($token === '') {
            return response()->json([
                'success' => false,
                'message' => 'Token Telegram belum diisi.',
            ], 422);
        }

        try {
            $res = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getWebhookInfo");
            $json = $res->json();
            if (!$res->successful() || !($json['ok'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil status webhook.',
                ], 422);
            }

            $info = (array) ($json['result'] ?? []);

            return response()->json([
                'success' => true,
                'message' => 'Status webhook berhasil diambil.',
                'info' => [
                    'url' => (string) ($info['url'] ?? ''),
                    'pending_update_count' => (int) ($info['pending_update_count'] ?? 0),
                    'last_error_message' => (string) ($info['last_error_message'] ?? ''),
                    'last_error_date' => (int) ($info['last_error_date'] ?? 0),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal konek ke Telegram API: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function telegramSendTestMessage()
    {
        $token = (string) Setting::get('telegram_config_bot_token', '');
        $chatId = (string) Setting::get('telegram_config_admin_chat_id', '');
        if ($token === '' || $chatId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Token atau Admin Chat ID belum diisi.',
            ], 422);
        }

        try {
            $text = "✅ Test pesan dari panel NETKING\nWaktu: " . now()->format('Y-m-d H:i:s');
            $res = Http::asJson()->timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
            ]);
            $json = $res->json();
            if (!$res->successful() || !($json['ok'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal kirim test message ke chat admin.',
                    'detail' => $json,
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Test message berhasil dikirim ke admin chat.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal konek ke Telegram API: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function maskToken(string $token): string
    {
        if ($token === '') {
            return '-';
        }
        if (strlen($token) <= 10) {
            return str_repeat('*', max(0, strlen($token) - 2)) . substr($token, -2);
        }
        return substr($token, 0, 8) . str_repeat('*', max(0, strlen($token) - 12)) . substr($token, -4);
    }
}
