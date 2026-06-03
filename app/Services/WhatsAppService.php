<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fonnte WhatsApp Service — Official BSP (Business Solution Provider)
 * 
 * Replaces WAHA (unofficial, risky for production ISP).
 * Fonnte API docs: https://docs.fonnte.com/
 * 
 * .env config:
 *   FONNTE_API_KEY=your_fonnte_token
 */
class WhatsAppService
{
    protected ?string $apiKey;
    protected ?string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.fonnte.api_key', '');
        $this->baseUrl = config('services.fonnte.base_url', 'https://api.fonnte.com');
    }

    /**
     * Send WhatsApp message via Fonnte
     */
    public function sendMessage(string $phone, string $message): array
    {
        if (empty($this->apiKey)) {
            Log::warning('Fonnte API key not configured — message not sent', ['phone' => $phone]);
            return ['success' => false, 'error' => 'Fonnte API key not configured'];
        }

        try {
            $formattedPhone = $this->formatPhoneNumber($phone);

            $response = Http::timeout(10)->withHeaders([
                'Authorization' => $this->apiKey,
            ])->post("{$this->baseUrl}/send", [
                'target' => $formattedPhone,
                'message' => $message,
                'countryCode' => '62',
            ]);

            $result = $response->json();

            if ($response->successful() && ($result['status'] ?? false)) {
                Log::info("Fonnte: Message sent to {$formattedPhone}");
                return ['success' => true, 'data' => $result];
            }

            Log::error('Fonnte send failed', [
                'phone' => $formattedPhone,
                'status' => $response->status(),
                'response' => $result,
            ]);

            return ['success' => false, 'error' => $result['reason'] ?? $response->body()];
        } catch (\Exception $e) {
            Log::error("Fonnte exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send invoice reminder via WhatsApp
     */
    public function sendInvoiceReminder(string $customerName, string $phone, string $invoiceNumber, float $amount, string $dueDate): array
    {
        $message = "🔔 *Reminder Invoice*\n\n";
        $message .= "Halo *{$customerName}*,\n\n";
        $message .= "Invoice #{$invoiceNumber}\n";
        $message .= "Jumlah: Rp " . number_format($amount, 0, ',', '.') . "\n";
        $message .= "Jatuh Tempo: {$dueDate}\n\n";
        $message .= "Silakan lakukan pembayaran melalui portal pelanggan atau hubungi admin.\n";
        $message .= "Terima kasih! 🙏";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Send payment confirmation
     */
    public function sendPaymentConfirmation(string $phone, string $customerName, string $invoiceNumber, float $amount, string $gateway = 'Manual'): array
    {
        $message = "✅ *Pembayaran Diterima*\n\n";
        $message .= "Halo *{$customerName}*,\n\n";
        $message .= "Pembayaran invoice #{$invoiceNumber} sebesar ";
        $message .= "Rp " . number_format($amount, 0, ',', '.') . " ";
        $message .= "telah kami terima via {$gateway}.\n\n";
        $message .= "Terima kasih atas kepercayaan Anda! 🎉";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Send payment proof rejection notification
     */
    public function sendPaymentRejected(string $phone, string $customerName, string $invoiceNumber, string $reason): array
    {
        $message = "❌ *Bukti Pembayaran Ditolak*\n\n";
        $message .= "Halo *{$customerName}*,\n\n";
        $message .= "Maaf, bukti pembayaran untuk invoice *#{$invoiceNumber}* ditolak.\n\n";
        $message .= "📋 *Alasan:* {$reason}\n\n";
        $message .= "Silakan upload ulang bukti pembayaran yang benar melalui aplikasi Netking.\n";
        $message .= "Hubungi admin jika ada pertanyaan. 🙏";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Send new customer welcome message
     */
    public function sendWelcomeMessage(string $customerName, string $phone, string $username, string $password): array
    {
        $message = "🎉 *Selamat Datang di Netking ISP!*\n\n";
        $message .= "Halo *{$customerName}*,\n\n";
        $message .= "Akun internet Anda telah aktif:\n\n";
        $message .= "👤 Username: {$username}\n";
        $message .= "🔑 Password: {$password}\n\n";
        $message .= "Silakan hubungi teknisi untuk instalasi.\n";
        $message .= "Terima kasih! 🙏";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Send suspension notice
     */
    public function sendSuspensionNotice(string $phone, string $customerName, string $invoiceNumber, float $amount): array
    {
        $message = "⚠️ *Pemberitahuan Suspend*\n\n";
        $message .= "Halo *{$customerName}*,\n\n";
        $message .= "Layanan internet Anda telah di-suspend karena invoice #{$invoiceNumber} ";
        $message .= "sebesar Rp " . number_format($amount, 0, ',', '.') . " belum dibayar.\n\n";
        $message .= "Silakan lakukan pembayaran untuk mengaktifkan kembali layanan Anda.\n";
        $message .= "Hubungi admin jika ada pertanyaan. 🙏";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Format phone number to Indonesian format (628xxx)
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, replace with 62
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // If doesn't start with 62, add it
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Check Fonnte API health / device status
     */
    public function checkConnection(): bool
    {
        if (empty($this->apiKey)) {
            return false;
        }

        try {
            $response = Http::timeout(10)->withHeaders([
                'Authorization' => $this->apiKey,
            ])->post("{$this->baseUrl}/device");

            return $response->successful() && ($response->json('status') ?? false);
        } catch (\Exception $e) {
            Log::error("Fonnte health check failed: " . $e->getMessage());
            return false;
        }
    }
}
