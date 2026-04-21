<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class AdminMessageMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $recipient,
        public string $subjectLine,
        public string $body,
        public ?string $templateId = null,
        public ?int $sentByAdminId = null,
    ) {}

    public function envelope(): Envelope
    {
        $keys = Cache::get('settings:admin', []);

        $supportEmail = ($keys['general.supportEmail'] ?? null)
            ?: config('loops.support_email')
            ?: config('mail.from.address');

        $instanceName = ($keys['general.instanceName'] ?? null)
            ?: config('mail.from.name')
            ?: config('app.name');

        return new Envelope(
            subject: $this->subjectLine,
            from: new Address($supportEmail, $instanceName),
            replyTo: [new Address($supportEmail)],
            metadata: ['template' => $this->templateId ?? 'custom'],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin.message',
            with: [
                'recipient' => $this->recipient,
                'body' => $this->body,
                'appName' => config('app.name'),
            ],
        );
    }
}
