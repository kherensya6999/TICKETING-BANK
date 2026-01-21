<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\AssignmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoAssignTicketJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $ticketId;
    public $tries = 3;

    public function __construct(int $ticketId)
    {
        $this->ticketId = $ticketId;
    }

    public function handle(AssignmentService $assignmentService): void
    {
        try {
            $ticket = Ticket::find($this->ticketId);
            
            if (!$ticket) {
                Log::warning("Ticket {$this->ticketId} not found for auto-assignment");
                return;
            }

            if ($ticket->assigned_to_id) {
                return;
            }

            if (!in_array($ticket->status, ['NEW', 'ASSIGNED'])) {
                return;
            }

            DB::beginTransaction();

            $assignedUserId = $assignmentService->autoAssign($ticket);

            if ($assignedUserId) {
                $ticket->update([
                    'assigned_to_id' => $assignedUserId,
                    'status' => 'ASSIGNED',
                ]);

                $ticket->watchers()->syncWithoutDetaching([$assignedUserId => [
                    'notify_on_update' => true,
                    'notify_on_comment' => true,
                    'notify_on_status_change' => true,
                ]]);

                $ticket->histories()->create([
                    'action_type' => 'ASSIGNED',
                    'description' => 'Auto-assigned by system',
                ]);

                DB::commit();

                SendNotificationJob::dispatch('TICKET_ASSIGNED', $ticket->id, $assignedUserId);

                Log::info("Ticket {$ticket->ticket_number} auto-assigned to user {$assignedUserId}");
            } else {
                DB::rollBack();
                Log::info("No suitable assignee found for ticket {$ticket->ticket_number}");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Auto-assignment failed for ticket {$this->ticketId}: " . $e->getMessage());
            throw $e;
        }
    }
}
