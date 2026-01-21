<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketAttachment;
use App\Models\SecurityIncident;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TicketService
{
    public function generateTicketNumber(): string
    {
        $date = now()->format('Ymd');
        $lastTicket = Ticket::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastTicket ? (int) substr($lastTicket->ticket_number, -4) + 1 : 1;
        $sequenceNumber = str_pad($sequence, 4, '0', STR_PAD_LEFT);

        return "TKT-{$date}-{$sequenceNumber}";
    }

    public function addHistory(
        Ticket $ticket,
        string $actionType,
        ?int $userId = null,
        ?string $fieldName = null,
        $oldValue = null,
        $newValue = null,
        ?string $description = null
    ): TicketHistory {
        return TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => $userId,
            'action_type' => $actionType,
            'field_name' => $fieldName,
            'old_value' => $oldValue ? (is_array($oldValue) ? json_encode($oldValue) : $oldValue) : null,
            'new_value' => $newValue ? (is_array($newValue) ? json_encode($newValue) : $newValue) : null,
            'description' => $description ?? $actionType,
        ]);
    }

    public function uploadAttachment(
        Ticket $ticket,
        UploadedFile $file,
        int $uploadedBy,
        ?int $commentId = null
    ): TicketAttachment {
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $filePath = "tickets/{$ticket->id}/" . $fileName;
        
        Storage::disk('public')->put($filePath, file_get_contents($file->getRealPath()));
        
        $fullPath = Storage::disk('public')->path($filePath);
        $fileHash = hash_file('sha256', $fullPath);

        return TicketAttachment::create([
            'ticket_id' => $ticket->id,
            'comment_id' => $commentId,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'file_hash' => $fileHash,
            'virus_scan_status' => 'PENDING',
            'uploaded_by' => $uploadedBy,
        ]);
    }

    public function createSecurityIncident(Ticket $ticket): SecurityIncident
    {
        $incidentNumber = $this->generateIncidentNumber();

        return SecurityIncident::create([
            'ticket_id' => $ticket->id,
            'incident_number' => $incidentNumber,
            'incident_classification' => 'OTHER',
            'attack_vector' => 'OTHER',
            'detected_at' => $ticket->created_at,
            'investigation_status' => 'NOT_STARTED',
        ]);
    }

    public function generateIncidentNumber(): string
    {
        $date = now()->format('Ymd');
        $lastIncident = SecurityIncident::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastIncident ? (int) substr($lastIncident->incident_number, -4) + 1 : 1;
        $sequenceNumber = str_pad($sequence, 4, '0', STR_PAD_LEFT);

        return "SEC-{$date}-{$sequenceNumber}";
    }

    public function parseMentions(string $text): array
    {
        preg_match_all('/@(\w+)/', $text, $matches);
        $usernames = $matches[1] ?? [];

        if (empty($usernames)) {
            return [];
        }

        $users = User::whereIn('username', $usernames)
            ->orWhereIn('employee_id', $usernames)
            ->pluck('id')
            ->toArray();

        return $users;
    }
}
