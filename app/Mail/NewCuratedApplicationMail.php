<?php

namespace App\Mail;

use App\Models\CuratedApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewCuratedApplicationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $reviewUrl;

    public function __construct(
        public CuratedApplication $application,
    ) {
        $this->reviewUrl = url("/admin/curated-onboarding/{$this->application->id}?ref=email");
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New Curated Application to review [#'.$this->application->id.']');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.curated.new-application');
    }
}
