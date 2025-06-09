<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignCreated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Campaign $campaign
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
        return (new MailMessage)
            ->subject('Your Campaign Has Been Created')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your campaign "' . $this->campaign->title . '" has been successfully created.')
            ->line('Campaign Details:')
            ->line('Goal Amount: $' . number_format($this->campaign->goal_amount, 2))
            ->line('Start Date: ' . $this->campaign->start_date->format('F j, Y'))
            ->line('End Date: ' . $this->campaign->end_date->format('F j, Y'))
            ->line('Status: ' . ucfirst($this->campaign->status))
            ->when($this->campaign->status === 'draft', function ($message) {
                return $message->line('Your campaign is currently in draft status. You can publish it when you\'re ready.');
            })
            ->when($this->campaign->status === 'pending', function ($message) {
                return $message->line('Your campaign is pending review. We\'ll notify you once it\'s approved.');
            })
            ->when($this->campaign->status === 'active', function ($message) {
                return $message->line('Your campaign is now active and accepting donations!');
            })
            ->action('View Campaign', url('/campaigns/' . $this->campaign->slug))
            ->line('Thank you for making a difference!')
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
            'campaign_id' => $this->campaign->id,
            'title' => $this->campaign->title,
            'status' => $this->campaign->status,
        ];
    }
} 