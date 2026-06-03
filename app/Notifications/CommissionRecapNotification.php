<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommissionRecapNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $totalUnpaid;
    protected $topPartner;
    protected $topPartnersData;

    /**
     * Create a new notification instance.
     */
    public function __construct($totalUnpaid, $topPartner, $topPartnersData)
    {
        $this->totalUnpaid = $totalUnpaid;
        $this->topPartner = $topPartner;
        $this->topPartnersData = $topPartnersData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // You can add 'telegram' channel here when ready
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $topPartnerText = $this->topPartner 
            ? "{$this->topPartner['name']} (Rp " . number_format($this->topPartner['amount'], 0, ',', '.') . ")"
            : 'No unpaid commissions';

        $mail = (new MailMessage)
            ->subject('Monthly Commission Recap - ' . now()->format('F Y'))
            ->greeting('Hello Admin!')
            ->line('Here is your monthly commission recap for ' . now()->format('F Y') . '.')
            ->line('**Total Pending Commission to Pay:** Rp ' . number_format($this->totalUnpaid, 0, ',', '.'))
            ->line('**Top Partner:** ' . $topPartnerText);

        // Add top 5 partners list if available
        if (!empty($this->topPartnersData)) {
            $mail->line('### Top 5 Partners with Highest Unpaid Balance:');
            
            foreach ($this->topPartnersData as $index => $partner) {
                $mail->line(($index + 1) . ". {$partner['name']}: Rp " . number_format($partner['amount'], 0, ',', '.'));
            }
        }

        $mail->action('View Commission Dashboard', url('/admin/commissions'))
            ->line('Please review and process pending commission payments.')
            ->line('Thank you for using our ISP Management System!');

        return $mail;
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Monthly Commission Recap',
            'total_unpaid' => $this->totalUnpaid,
            'top_partner' => $this->topPartner,
            'top_partners' => $this->topPartnersData,
            'month' => now()->format('F Y'),
        ];
    }

    /**
     * Get the Telegram representation of the notification.
     * Uncomment and configure when Telegram integration is ready.
     */
    // public function toTelegram(object $notifiable)
    // {
    //     $topPartnerText = $this->topPartner 
    //         ? "{$this->topPartner['name']} (Rp " . number_format($this->topPartner['amount'], 0, ',', '.') . ")"
    //         : 'No unpaid commissions';
    //
    //     $message = "🔔 *Monthly Commission Recap - " . now()->format('F Y') . "*\n\n";
    //     $message .= "💰 *Total Pending Commission:* Rp " . number_format($this->totalUnpaid, 0, ',', '.') . "\n";
    //     $message .= "🏆 *Top Partner:* {$topPartnerText}\n\n";
    //     
    //     if (!empty($this->topPartnersData)) {
    //         $message .= "*Top 5 Partners:*\n";
    //         foreach ($this->topPartnersData as $index => $partner) {
    //             $message .= ($index + 1) . ". {$partner['name']}: Rp " . number_format($partner['amount'], 0, ',', '.') . "\n";
    //         }
    //     }
    //
    //     return TelegramMessage::create()
    //         ->content($message)
    //         ->button('View Dashboard', url('/admin/commissions'));
    // }
}
