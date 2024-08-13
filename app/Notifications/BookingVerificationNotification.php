<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingVerificationNotification extends Notification
{
    use Queueable;

    public $place;

    public $details;

    public $time;

    public $status;

    /**
     * Create a new notification instance.
     */
    public function __construct($place, $details, $time, $status = 'done')
    {
        $this->place = $place;

        $this->details = $details;

        $this->time = $time;

        $this->status = $status;
        
    }

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
       $mail =  (new MailMessage)
        ->line('Your booking to ' . $this->place . ' is ' . $this->status . ' successfully')
        ->line('details are:');

        foreach($this->details as $detail)
           $mail->line($detail);

        $mail->line('You can cancel the reservation or part of it ' . $this->time . ' before the reservation');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
