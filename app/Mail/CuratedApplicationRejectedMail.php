<?php

namespace App\Mail;

use App\Models\CuratedApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CuratedApplicationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $greeting;

    public array $bodyLines;

    public function __construct(
        public CuratedApplication $application,
        public ?string $reason,
        public ?string $template,
    ) {
        $this->greeting = $this->resolveGreeting();
        $this->bodyLines = $this->resolveBodyLines();
    }

    private function resolveGreeting(): string
    {
        if (! $this->template) {
            return 'Hi there,';
        }

        return explode("\n", $this->parseTemplate($this->template))[0];
    }

    private function resolveBodyLines(): array
    {
        if (! $this->template) {
            return [];
        }
        $lines = explode("\n", $this->parseTemplate($this->template));
        array_shift($lines);

        return $lines;
    }

    private function parseTemplate(string $template): string
    {
        return str_replace(
            ['{{email}}', '{{username}}'],
            [$this->application->email, $this->application->username_requested],
            $template
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.curated.rejected');
    }

    public function envelope(): Envelope
    {
        $domain = parse_url(config('app.url'), PHP_URL_HOST);

        return new Envelope(subject: $domain.' Application update');
    }
}
