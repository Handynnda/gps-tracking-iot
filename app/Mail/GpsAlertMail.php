<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GpsAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $title;
    public string $messageText;
    public ?string $latitude;
    public ?string $longitude;

    public function __construct(
        string $title,
        string $messageText,
        ?string $latitude = null,
        ?string $longitude = null
    ) {
        $this->title = $title;
        $this->messageText = $messageText;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🚨 PERINGATAN GPS: ' . $this->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.gps_alert',
        );
    }
}