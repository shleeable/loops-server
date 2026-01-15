<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminInviteJoined extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public $invite;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $invite)
    {
        $this->user = $user;
        $this->invite = $invite;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Admin Invite Joined',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin.new_invite_joined',
            with: [
                'username' => $this->user->username,
                'url' => $this->user->url(),
                'invite_title' => $this->invite->title ?? 'Untitled Invite',
                'invite_admin_url' => $this->invite->getAdminLink(),
            ]
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
