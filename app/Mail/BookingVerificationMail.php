<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;

class BookingVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $place;

    public $details;

    public $time;

    public $status;

    /**
     * Create a new message instance.
     */
    public function __construct($place, $details, $time, $status = 'done')
    {
        $this->place = $place;

        $this->details = $details;

        $this->time = $time;

        $this->status = $status;
        
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Verification Mail',
        );
    }

    /**
     * Get the message content definition.
     */

    public function content(): Content
{
    return new Content(
        markdown: 'mail.verification',
    );
}

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
