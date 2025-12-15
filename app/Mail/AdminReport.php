<?php

namespace App\Mail;

use App\Models\Report;
use App\Services\AccountService;
use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminReport extends Mailable
{
    use Queueable, SerializesModels;

    public $report;

    /**
     * Create a new message instance.
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Report #'.$this->report->id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $reporter = AccountService::compact($this->report->reporter_profile_id);
        $reporterUsername = data_get($reporter, 'username', 'a user');
        $reportedEntityType = $this->report->reportEntityType();

        return new Content(
            markdown: 'emails.admin.report',
            with: [
                'id' => $this->report->id,
                'entity' => $reportedEntityType,
                'reporterUsername' => $reporterUsername,
                'reportType' => ReportService::getById($this->report->report_type),
                'url' => $this->report->adminUrl(),
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
