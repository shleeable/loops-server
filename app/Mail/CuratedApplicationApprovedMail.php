<?php

namespace App\Mail;

use App\Models\CuratedApplication;
use App\Models\CuratedApplicationSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CuratedApplicationApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $greeting;

    public array $bodyLines;

    public function __construct(
        public CuratedApplication $application,
        public string $magicLink,
    ) {
        $settings = CuratedApplicationSettings::current();
        $this->greeting = $this->resolveGreeting($settings->approval_template);
        $this->bodyLines = $this->resolveBodyLines($settings->approval_template);
    }

    private function resolveGreeting(?string $template): string
    {
        if (! $template) {
            return "You're in!";
        }

        return explode("\n", $this->parseTemplate($template))[0];
    }

    private function resolveBodyLines(?string $template): array
    {
        if (! $template) {
            return [];
        }
        $lines = explode("\n", $this->parseTemplate($template));
        array_shift($lines);

        return $lines;
    }

    private function parseTemplate(string $template): string
    {
        return str_replace(
            ['{{email}}', '{{username}}'],
            [$this->application->email, $this->application->username_requested ?? ''],
            $template
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Welcome! Your application has been approved');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.curated.approved');
    }
}
