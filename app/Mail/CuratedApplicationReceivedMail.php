<?php

namespace App\Mail;

use App\Models\CuratedApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CuratedApplicationReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $verifyUrl;

    public function __construct(
        public CuratedApplication $application,
    ) {
        $this->verifyUrl = url('/auth/curated/verify/email?'.http_build_query([
            'token' => $this->application->email_verification_token,
        ]));
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Verify your application');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.curated.received');
    }
}
