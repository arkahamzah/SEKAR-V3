<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Konsultasi;

class KonsultasiNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $konsultasi;
    public $actionType;

    /**
     * Create a new message instance.
     */
    public function __construct(Konsultasi $konsultasi, string $actionType = 'new')
    {
        $this->konsultasi = $konsultasi;
        $this->actionType = $actionType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match($this->actionType) {
            'new' => '[SEKAR] ' . ucfirst($this->konsultasi->JENIS) . ' Baru - ' . $this->konsultasi->JUDUL,
            'comment' => '[SEKAR] Komentar Baru - ' . $this->konsultasi->JUDUL,
            'escalate' => '[SEKAR] Eskalasi ' . ucfirst($this->konsultasi->JENIS) . ' - ' . $this->konsultasi->JUDUL,
            'closed' => '[SEKAR] ' . ucfirst($this->konsultasi->JENIS) . ' Selesai - ' . $this->konsultasi->JUDUL,
            default => '[SEKAR] Update ' . ucfirst($this->konsultasi->JENIS) . ' - ' . $this->konsultasi->JUDUL,
        };

        return new Envelope(
            subject: $subject,
            from: env('MAIL_FROM_ADDRESS', 'noreply@sekar.telkom.co.id'),
            replyTo: env('MAIL_FROM_ADDRESS', 'noreply@sekar.telkom.co.id'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.konsultasi-notification',
            with: [
                'konsultasi' => $this->konsultasi,
                'actionType' => $this->actionType,
                'actionText' => $this->getActionText(),
                'actionColor' => $this->getActionColor(),
                'karyawan' => $this->konsultasi->karyawan,
                'viewUrl' => route('konsultasi.show', $this->konsultasi->ID),
            ]
        );
    }

    /**
     * Get action text based on type
     */
    private function getActionText(): string
    {
        return match($this->actionType) {
            'new' => 'Pengajuan Baru',
            'comment' => 'Komentar Baru',
            'escalate' => 'Eskalasi',
            'closed' => 'Ditutup',
            default => 'Update',
        };
    }

    /**
     * Get action color for styling
     */
    private function getActionColor(): string
    {
        return match($this->actionType) {
            'new' => '#3B82F6', // blue
            'comment' => '#10B981', // green
            'escalate' => '#F59E0B', // amber
            'closed' => '#6B7280', // gray
            default => '#6366F1', // indigo
        };
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

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.konsultasi-notification')
                    ->subject($this->envelope()->subject)
                    ->with([
                        'konsultasi' => $this->konsultasi,
                        'actionType' => $this->actionType,
                        'actionText' => $this->getActionText(),
                        'actionColor' => $this->getActionColor(),
                        'karyawan' => $this->konsultasi->karyawan,
                        'viewUrl' => route('konsultasi.show', $this->konsultasi->ID),
                    ]);
    }
}