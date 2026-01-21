<?php

namespace App\Services;

use App\Models\SLAPolicy;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketSLATracking;
use App\Models\Holiday;
use Carbon\Carbon;

class SLAService
{
    public function getSLAPolicyForPriority(string $priority, TicketCategory $category): SLAPolicy
    {
        if ($category->default_sla_id) {
            $sla = SLAPolicy::find($category->default_sla_id);
            if ($sla && $sla->is_active) {
                return $sla;
            }
        }

        $sla = SLAPolicy::where('priority', $priority)
            ->where('is_active', true)
            ->first();

        if (!$sla) {
            $sla = SLAPolicy::where('priority', 'MEDIUM')
                ->where('is_active', true)
                ->firstOrFail();
        }

        return $sla;
    }

    public function calculateDueDate(SLAPolicy $slaPolicy): Carbon
    {
        $now = now();
        $targetMinutes = $slaPolicy->resolution_target;

        if ($slaPolicy->business_hours_only) {
            return $this->addBusinessMinutes($now, $targetMinutes, $slaPolicy);
        }

        return $now->copy()->addMinutes($targetMinutes);
    }

    protected function addBusinessMinutes(Carbon $start, int $minutes, SLAPolicy $slaPolicy): Carbon
    {
        $current = $start->copy();
        $remainingMinutes = $minutes;
        $businessDays = $slaPolicy->business_days ?? [1, 2, 3, 4, 5];
        $startTime = $slaPolicy->business_hours_start ?? '09:00';
        $endTime = $slaPolicy->business_hours_end ?? '17:00';

        while ($remainingMinutes > 0) {
            if (!in_array($current->dayOfWeek, $businessDays)) {
                $current->addDay()->setTimeFromTimeString($startTime);
                continue;
            }

            if (Holiday::whereDate('holiday_date', $current->toDateString())->exists()) {
                $current->addDay()->setTimeFromTimeString($startTime);
                continue;
            }

            $dayStart = $current->copy()->setTimeFromTimeString($startTime);
            $dayEnd = $current->copy()->setTimeFromTimeString($endTime);

            if ($current->lt($dayStart)) {
                $current = $dayStart;
            }

            if ($current->gte($dayEnd)) {
                $current->addDay()->setTimeFromTimeString($startTime);
                continue;
            }

            $minutesUntilEnd = $current->diffInMinutes($dayEnd);

            if ($remainingMinutes <= $minutesUntilEnd) {
                $current->addMinutes($remainingMinutes);
                $remainingMinutes = 0;
            } else {
                $remainingMinutes -= $minutesUntilEnd;
                $current->addDay()->setTimeFromTimeString($startTime);
            }
        }

        return $current;
    }

    public function createSLATracking(Ticket $ticket, SLAPolicy $slaPolicy): TicketSLATracking
    {
        $firstResponseTarget = $this->calculateFirstResponseTarget($ticket, $slaPolicy);
        $resolutionTarget = $this->calculateDueDate($slaPolicy);

        return TicketSLATracking::create([
            'ticket_id' => $ticket->id,
            'sla_policy_id' => $slaPolicy->id,
            'first_response_target_at' => $firstResponseTarget,
            'resolution_target_at' => $resolutionTarget,
            'overall_sla_status' => 'ON_TIME',
        ]);
    }

    protected function calculateFirstResponseTarget(Ticket $ticket, SLAPolicy $slaPolicy): Carbon
    {
        $now = now();
        $targetMinutes = $slaPolicy->first_response_target;

        if ($slaPolicy->business_hours_only) {
            return $this->addBusinessMinutes($now, $targetMinutes, $slaPolicy);
        }

        return $now->copy()->addMinutes($targetMinutes);
    }

    public function updateFirstResponse(Ticket $ticket): void
    {
        $slaTracking = $ticket->slaTracking;
        if (!$slaTracking) {
            return;
        }

        $slaTracking->update([
            'first_response_actual_at' => now(),
        ]);

        if ($slaTracking->first_response_target_at && 
            now()->gt($slaTracking->first_response_target_at)) {
            $slaTracking->update([
                'first_response_breached' => true,
                'overall_sla_status' => 'BREACHED',
            ]);

            $ticket->update(['is_sla_breached' => true]);
        } else {
            $slaTracking->update([
                'overall_sla_status' => 'ON_TIME',
            ]);
        }
    }

    public function updateResolution(Ticket $ticket): void
    {
        $slaTracking = $ticket->slaTracking;
        if (!$slaTracking) {
            return;
        }

        $slaTracking->update([
            'resolution_actual_at' => now(),
        ]);

        if ($slaTracking->resolution_target_at && 
            now()->gt($slaTracking->resolution_target_at)) {
            $slaTracking->update([
                'resolution_breached' => true,
                'overall_sla_status' => 'BREACHED',
            ]);

            $ticket->update(['is_sla_breached' => true]);
        }
    }
}
