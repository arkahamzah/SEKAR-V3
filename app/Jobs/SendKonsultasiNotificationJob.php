<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\KonsultasiNotification;
use App\Models\Konsultasi;

class SendKonsultasiNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $konsultasi;
    public $actionType;
    public $recipients;
    public $timeout = 120;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(Konsultasi $konsultasi, string $actionType, array $recipients)
    {
        $this->konsultasi = $konsultasi;
        $this->actionType = $actionType;
        $this->recipients = array_filter($recipients); // Remove empty emails
        
        // Set queue and delay based on action type
        $this->onQueue($this->getQueueName($actionType));
        
        // Add delay for non-urgent notifications
        if (in_array($actionType, ['comment', 'closed'])) {
            $this->delay(now()->addSeconds(30));
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $sentCount = 0;
            $failedCount = 0;
            $failedEmails = [];

            foreach ($this->recipients as $email) {
                try {
                    // Validate email format
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        Log::warning("Invalid email format: {$email}", [
                            'konsultasi_id' => $this->konsultasi->ID,
                            'action_type' => $this->actionType
                        ]);
                        $failedCount++;
                        $failedEmails[] = $email;
                        continue;
                    }

                    // Send email
                    Mail::to($email)->send(new KonsultasiNotification($this->konsultasi, $this->actionType));
                    $sentCount++;
                    
                    Log::info("Email notification sent successfully", [
                        'konsultasi_id' => $this->konsultasi->ID,
                        'action_type' => $this->actionType,
                        'recipient' => $email,
                        'attempt' => $this->attempts()
                    ]);

                    // Small delay between emails to avoid rate limiting
                    if (count($this->recipients) > 1) {
                        usleep(500000); // 0.5 second delay
                    }

                } catch (\Exception $e) {
                    $failedCount++;
                    $failedEmails[] = $email;
                    
                    Log::error("Failed to send email to: {$email}", [
                        'konsultasi_id' => $this->konsultasi->ID,
                        'action_type' => $this->actionType,
                        'error' => $e->getMessage(),
                        'attempt' => $this->attempts()
                    ]);
                }
            }

            // Log summary
            Log::info("Email notification job completed", [
                'konsultasi_id' => $this->konsultasi->ID,
                'action_type' => $this->actionType,
                'total_recipients' => count($this->recipients),
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'failed_emails' => $failedEmails,
                'attempt' => $this->attempts()
            ]);

            // If all emails failed, throw exception to retry job
            if ($sentCount === 0 && $failedCount > 0) {
                throw new \Exception("Failed to send all notifications");
            }

        } catch (\Exception $e) {
            Log::error("Konsultasi notification job failed", [
                'konsultasi_id' => $this->konsultasi->ID,
                'action_type' => $this->actionType,
                'error' => $e->getMessage(),
                'recipients' => $this->recipients,
                'attempt' => $this->attempts()
            ]);
            
            // Re-throw to mark job as failed for retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Konsultasi notification job permanently failed", [
            'konsultasi_id' => $this->konsultasi->ID,
            'action_type' => $this->actionType,
            'error' => $exception->getMessage(),
            'recipients' => $this->recipients,
            'attempts' => $this->attempts()
        ]);

        // Optionally send notification to admin about failed email job
        $this->notifyAdminOfFailure($exception);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [30, 60, 120]; // Retry after 30s, 60s, then 120s
    }

    /**
     * Determine if the job should be retried based on the exception.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(10); // Stop retrying after 10 minutes
    }

    /**
     * Get queue name based on action type
     */
    private function getQueueName(string $actionType): string
    {
        return match($actionType) {
            'new', 'escalate' => 'emails-urgent',
            'comment' => 'emails-normal', 
            'closed' => 'emails-low',
            default => 'emails'
        };
    }

    /**
     * Notify admin about failed email job
     */
    private function notifyAdminOfFailure(\Throwable $exception): void
    {
        try {
            // Get super admin emails
            $adminEmails = \App\Models\SekarPengurus::join('users', 't_sekar_pengurus.N_NIK', '=', 'users.nik')
                ->join('t_sekar_roles', 't_sekar_pengurus.ID_ROLES', '=', 't_sekar_roles.ID')
                ->where('t_sekar_roles.NAME', 'ADM')
                ->whereNotNull('users.email')
                ->where('users.email', '!=', '')
                ->pluck('users.email')
                ->toArray();

            if (!empty($adminEmails)) {
                $failureData = [
                    'konsultasi_id' => $this->konsultasi->ID,
                    'konsultasi_title' => $this->konsultasi->JUDUL,
                    'action_type' => $this->actionType,
                    'failed_recipients' => $this->recipients,
                    'error_message' => $exception->getMessage(),
                    'timestamp' => now()->format('Y-m-d H:i:s')
                ];

                foreach ($adminEmails as $adminEmail) {
                    Mail::raw(
                        "SEKAR Email Notification Failure Alert\n\n" .
                        "Konsultasi ID: {$failureData['konsultasi_id']}\n" .
                        "Title: {$failureData['konsultasi_title']}\n" .
                        "Action: {$failureData['action_type']}\n" .
                        "Failed Recipients: " . implode(', ', $failureData['failed_recipients']) . "\n" .
                        "Error: {$failureData['error_message']}\n" .
                        "Time: {$failureData['timestamp']}\n\n" .
                        "Please check the email configuration and recipient addresses.",
                        function ($message) use ($adminEmail, $failureData) {
                            $message->to($adminEmail)
                                   ->subject("[SEKAR] Email Notification Failure - Konsultasi ID: {$failureData['konsultasi_id']}");
                        }
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to send admin notification about email failure", [
                'original_error' => $exception->getMessage(),
                'notification_error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'konsultasi-notification',
            'konsultasi-' . $this->konsultasi->ID,
            'action-' . $this->actionType,
            'recipients-' . count($this->recipients)
        ];
    }
}