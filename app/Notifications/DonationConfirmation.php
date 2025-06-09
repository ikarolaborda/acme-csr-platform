<?php

namespace App\Notifications;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DonationConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Donation $donation
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $campaign = $this->donation->campaign;
        
        return (new MailMessage)
            ->subject('Thank you for your donation!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Thank you for your generous donation to "' . $campaign->title . '".')
            ->line('Donation Details:')
            ->line('Amount: $' . number_format($this->donation->amount, 2))
            ->line('Campaign: ' . $campaign->title)
            ->line('Donation Number: ' . $this->donation->donation_number)
            ->line('Date: ' . $this->donation->created_at->format('F j, Y g:i A'))
            ->when(!$this->donation->is_anonymous, function ($message) {
                return $message->line('Your name will be displayed as a supporter of this campaign.');
            })
            ->when($this->donation->is_anonymous, function ($message) {
                return $message->line('Your donation will be displayed as anonymous.');
            })
            ->when($this->donation->message, function ($message) {
                return $message->line('Your message: ' . $this->donation->message);
            })
            ->action('View Campaign', url('/campaigns/' . $campaign->slug))
            ->line('Your support makes a real difference!')
            ->salutation('Best regards, The ACME CSR Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'donation_id' => $this->donation->id,
            'amount' => $this->donation->amount,
            'campaign_id' => $this->donation->campaign_id,
            'campaign_title' => $this->donation->campaign->title,
        ];
    }
} 