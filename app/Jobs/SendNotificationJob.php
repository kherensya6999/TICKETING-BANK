<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\Notification;
use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $notificationType;
    public $ticketId;
    public $userId;
    public $metadata;

    public function __construct(string $notificationType, int $ticketId, ?int $userId = null, array $metadata = [])
    {
        $this->notificationType = $notificationType;
        $this->ticketId = $ticketId;
        $this->userId = $userId;
        $this->metadata = $metadata;
    }

    public function handle(): void
    {
        try {
            $ticket = Ticket::with(['requester', 'assignedTo', 'category'])->find($this->ticketId);
            
            if (!$ticket) {
                return;
            }

            $recipients = $this->getRecipients($ticket);

            foreach ($recipients as $recipient) {
                $this->createInAppNotification($ticket, $recipient);
                $this->sendEmailNotification($ticket, $recipient);
            }

        } catch (\Exception $e) {
            Log::error("Notification failed: " . $e->getMessage());
        }
    }

    protected function getRecipients(Ticket $ticket): array
    {
        $recipients = [];

        if ($this->userId) {
            $user = User::find($this->userId);
            if ($user) {
                $recipients[] = $user;
            }
        }

        foreach ($ticket->watchers as $watcher) {
            if (!in_array($watcher->id, array_column($recipients, 'id'))) {
                $recipients[] = $watcher;
            }
        }

        if ($ticket->requester && !in_array($ticket->requester->id, array_column($recipients, 'id'))) {
            $recipients[] = $ticket->requester;
        }

        if ($ticket->assignedTo && !in_array($ticket->assignedTo->id, array_column($recipients, 'id'))) {
            $recipients[] = $ticket->assignedTo;
        }

        return $recipients;
    }

    protected function createInAppNotification(Ticket $ticket, User $user): void
    {
        $title = $this->getNotificationTitle();
        $message = $this->getNotificationMessage($ticket);

        Notification::create([
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
            'notification_type' => $this->notificationType,
            'title' => $title,
            'message' => $message,
            'data' => $this->metadata,
        ]);
    }

    protected function sendEmailNotification(Ticket $ticket, User $user): void
    {
        try {
            $subject = "[{$ticket->ticket_number}] " . $this->getNotificationTitle();
            $body = $this->getEmailBody($ticket);

            Mail::raw($body, function ($message) use ($user, $subject) {
                $message->to($user->email)
                    ->subject($subject);
            });

            EmailLog::create([
                'recipient_email' => $user->email,
                'subject' => $subject,
                'body' => $body,
                'status' => 'SENT',
                'sent_at' => now(),
            ]);

        } catch (\Exception $e) {
            EmailLog::create([
                'recipient_email' => $user->email,
                'subject' => $subject ?? 'Notification',
                'body' => '',
                'status' => 'FAILED',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    protected function getNotificationTitle(): string
    {
        return match($this->notificationType) {
            'TICKET_CREATED' => 'New Ticket Created',
            'TICKET_UPDATED' => 'Ticket Updated',
            'TICKET_ASSIGNED' => 'Ticket Assigned to You',
            'TICKET_RESOLVED' => 'Ticket Resolved',
            'COMMENT_ADDED' => 'New Comment Added',
            'MENTIONED_IN_COMMENT' => 'You Were Mentioned',
            default => 'Ticket Notification',
        };
    }

    protected function getNotificationMessage(Ticket $ticket): string
    {
        $baseMessage = "Ticket {$ticket->ticket_number}: {$ticket->subject}";

        return match($this->notificationType) {
            'TICKET_CREATED' => "A new ticket has been created. {$baseMessage}",
            'TICKET_UPDATED' => "Ticket has been updated. {$baseMessage}",
            'TICKET_ASSIGNED' => "You have been assigned to ticket. {$baseMessage}",
            'TICKET_RESOLVED' => "Ticket has been resolved. {$baseMessage}",
            'COMMENT_ADDED' => "A new comment has been added. {$baseMessage}",
            'MENTIONED_IN_COMMENT' => "You were mentioned in a comment. {$baseMessage}",
            default => $baseMessage,
        };
    }

    protected function getEmailBody(Ticket $ticket): string
    {
        $message = $this->getNotificationMessage($ticket);
        $url = config('app.frontend_url') . "/tickets/{$ticket->id}";

        return "
{$message}

Priority: {$ticket->priority}
Status: {$ticket->status}
Category: {$ticket->category->category_name}

View Ticket: {$url}

---
This is an automated notification from IT Security Ticketing System.
        ";
    }
}
