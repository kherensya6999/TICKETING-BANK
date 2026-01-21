<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\AssignmentRule;
use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Support\Facades\DB;

class AssignmentService
{
    public function autoAssign(Ticket $ticket): ?int
    {
        $rules = AssignmentRule::where('is_active', true)
            ->orderBy('priority_order', 'asc')
            ->get();

        foreach ($rules as $rule) {
            if ($this->ruleMatches($rule, $ticket)) {
                if ($rule->rule_type === 'AUTO_ASSIGN') {
                    $assignedUserId = $this->assignBasedOnRule($rule, $ticket);
                    if ($assignedUserId) {
                        return $assignedUserId;
                    }
                }
            }
        }

        if ($ticket->category->default_team_id) {
            $team = Team::find($ticket->category->default_team_id);
            if ($team) {
                return $this->assignToBestTeamMember($team, $ticket);
            }
        }

        return null;
    }

    protected function ruleMatches(AssignmentRule $rule, Ticket $ticket): bool
    {
        $conditions = $rule->conditions ?? [];

        foreach ($conditions as $field => $value) {
            switch ($field) {
                case 'category_id':
                    if ($ticket->category_id != $value) {
                        return false;
                    }
                    break;
                case 'priority':
                    if ($ticket->priority !== $value) {
                        return false;
                    }
                    break;
                case 'keywords':
                    $keywords = is_array($value) ? $value : [$value];
                    $subjectDesc = strtolower($ticket->subject . ' ' . $ticket->description);
                    $matched = false;
                    foreach ($keywords as $keyword) {
                        if (str_contains($subjectDesc, strtolower($keyword))) {
                            $matched = true;
                            break;
                        }
                    }
                    if (!$matched) {
                        return false;
                    }
                    break;
            }
        }

        return true;
    }

    protected function assignBasedOnRule(AssignmentRule $rule, Ticket $ticket): ?int
    {
        if ($rule->assign_to_user_id) {
            return $rule->assign_to_user_id;
        }

        if ($rule->assign_to_team_id) {
            $team = Team::find($rule->assign_to_team_id);
            if ($team) {
                return $this->assignToBestTeamMember($team, $ticket);
            }
        }

        return null;
    }

    protected function assignToBestTeamMember(Team $team, Ticket $ticket): ?int
    {
        $member = TeamMember::where('team_id', $team->id)
            ->where('is_available', true)
            ->whereRaw('current_ticket_count < max_concurrent_tickets')
            ->with('user')
            ->orderByDesc('skill_level')
            ->orderBy('current_ticket_count')
            ->first();

        if ($member) {
            $member->increment('current_ticket_count');
            return $member->user_id;
        }

        return null;
    }
}
