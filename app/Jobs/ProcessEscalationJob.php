<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\TicketEscalation;
use App\Jobs\SendNotificationJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessEscalationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $ticketId;
    public $reason;

    public function __construct(int $ticketId, string $reason = 'SLA_BREACH')
    {
        $this->ticketId = $ticketId;
        $this->reason = $reason;
    }

    public function handle(): void
    {
        try {
            $ticket = Ticket::with(['team', 'team.teamLead'])->find($this->ticketId);
            
            if (!$ticket) {
                return;
            }

            DB::beginTransaction();

            $currentLevel = $ticket->escalation_level ?? 0;
            $newLevel = $currentLevel + 1;

            $escalatedToUserId = null;
            $escalatedToTeamId = null;

            // Escalation logic
            if ($ticket->team && $ticket->team->team_lead_id) {
                $escalatedToUserId = $ticket->team->team_lead_id;
            }

            TicketEscalation::create([
                'ticket_id' => $ticket->id,
                'escalation_level' => $newLevel,
                'escalated_from_user_id' => $ticket->assigned_to_id,
                'escalated_to_user_id' => $escalatedToUserId,
                'escalated_to_team_id' => $escalatedToTeamId,
                'escalation_reason' => $this->reason,
                'escalation_type' => 'SLA_BREACH',
                'escalated_at' => now(),
            ]);

            $ticket->update([
                'is_escalated' => true,
                'escalation_level' => $newLevel,
            ]);

            if ($escalatedToUserId) {
                $ticket->update(['assigned_to_id' => $escalatedToUserId]);
            }

            $ticket->histories()->create([
                'action_type' => 'ESCALATED',
                'description' => "Escalated to level {$newLevel}: {$this->reason}",
            ]);

            DB::commit();

            // Send notifications
            if ($escalatedToUserId) {
                SendNotificationJob::dispatch('TICKET_ESCALATED', $ticket->id, $escalatedToUserId);
            }

            Log::info("Ticket {$ticket->ticket_number} escalated to level {$newLevel}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Escalation failed for ticket {$this->ticketId}: " . $e->getMessage());
            throw $e;
        }
    }
}
