<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Models\TicketSLATracking;
use App\Jobs\ProcessEscalationJob;
use App\Jobs\SendNotificationJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MonitorSLACommand extends Command
{
    protected $signature = 'sla:monitor';
    protected $description = 'Monitor SLA compliance and trigger escalations';

    public function handle(): void
    {
        $this->info('Starting SLA monitoring...');

        $tickets = Ticket::whereNotIn('status', ['RESOLVED', 'CLOSED', 'CANCELLED'])
            ->with('slaTracking')
            ->get();

        $breachedCount = 0;
        $atRiskCount = 0;

        foreach ($tickets as $ticket) {
            $slaTracking = $ticket->slaTracking;
            
            if (!$slaTracking) {
                continue;
            }

            if ($slaTracking->first_response_target_at && 
                !$slaTracking->first_response_actual_at &&
                now()->gt($slaTracking->first_response_target_at) &&
                !$slaTracking->first_response_breached) {
                
                $this->handleFirstResponseBreach($ticket, $slaTracking);
                $breachedCount++;
            }

            if ($slaTracking->resolution_target_at &&
                $ticket->status !== 'RESOLVED' &&
                now()->gt($slaTracking->resolution_target_at) &&
                !$slaTracking->resolution_breached) {
                
                $this->handleResolutionBreach($ticket, $slaTracking);
                $breachedCount++;
            }

            if ($slaTracking->resolution_target_at &&
                $ticket->status !== 'RESOLVED') {
                
                $elapsed = now()->diffInMinutes($ticket->created_at);
                $target = $ticket->created_at->diffInMinutes($slaTracking->resolution_target_at);
                $percentage = ($elapsed / $target) * 100;

                if ($percentage >= 80 && $slaTracking->overall_sla_status !== 'AT_RISK') {
                    $slaTracking->update(['overall_sla_status' => 'AT_RISK']);
                    $atRiskCount++;
                    
                    if ($ticket->team && $ticket->team->team_lead_id) {
                        SendNotificationJob::dispatch('SLA_AT_RISK', $ticket->id, $ticket->team->team_lead_id);
                    }
                }
            }
        }

        $this->info("SLA monitoring completed. Breached: {$breachedCount}, At Risk: {$atRiskCount}");
    }

    protected function handleFirstResponseBreach(Ticket $ticket, TicketSLATracking $slaTracking): void
    {
        DB::beginTransaction();

        $slaTracking->update([
            'first_response_breached' => true,
            'overall_sla_status' => 'BREACHED',
        ]);

        $ticket->update(['is_sla_breached' => true]);

        $ticket->histories()->create([
            'action_type' => 'SLA_BREACH',
            'description' => 'First response SLA breached',
        ]);

        DB::commit();

        ProcessEscalationJob::dispatch($ticket->id, 'SLA_BREACH');

        SendNotificationJob::dispatch('SLA_BREACHED', $ticket->id, $ticket->requester_id);
        if ($ticket->assignedTo) {
            SendNotificationJob::dispatch('SLA_BREACHED', $ticket->id, $ticket->assignedTo->id);
        }
    }

    protected function handleResolutionBreach(Ticket $ticket, TicketSLATracking $slaTracking): void
    {
        DB::beginTransaction();

        $slaTracking->update([
            'resolution_breached' => true,
            'overall_sla_status' => 'BREACHED',
        ]);

        $ticket->update(['is_sla_breached' => true]);

        $ticket->histories()->create([
            'action_type' => 'SLA_BREACH',
            'description' => 'Resolution SLA breached',
        ]);

        DB::commit();

        ProcessEscalationJob::dispatch($ticket->id, 'SLA_BREACH');

        SendNotificationJob::dispatch('SLA_BREACHED', $ticket->id, $ticket->requester_id);
        if ($ticket->assignedTo) {
            SendNotificationJob::dispatch('SLA_BREACHED', $ticket->id, $ticket->assignedTo->id);
        }
        if ($ticket->team && $ticket->team->team_lead_id) {
            SendNotificationJob::dispatch('SLA_BREACHED', $ticket->id, $ticket->team->team_lead_id);
        }
    }
}
