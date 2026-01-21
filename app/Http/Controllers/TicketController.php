<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Services\TicketService;
use App\Services\SLAService;
use App\Jobs\AutoAssignTicketJob;
use App\Jobs\SendNotificationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    protected $ticketService;
    protected $slaService;

    public function __construct(TicketService $ticketService, SLAService $slaService)
    {
        $this->ticketService = $ticketService;
        $this->slaService = $slaService;
    }

    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            $query = Ticket::with([
                'category',
                'subcategory',
                'requester:id,employee_id,first_name,last_name',
                'assignedTo:id,employee_id,first_name,last_name',
                'team:id,team_name',
                'slaTracking'
            ]);

            // SECURITY HARDENING: Data Isolation
            // Jika user bukan Admin dan tidak punya izin view all, 
            // PAKSA filter hanya tiket milik user tersebut.
            if ($user->role->role_name === 'USER' && !$user->hasPermission('TICKET_VIEW_ALL')) {
                $query->where('requester_id', $user->id);
            }

            // Filter standar (tetap berjalan, tapi di atas isolation layer)
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            if ($request->has('priority')) {
                $query->where('priority', $request->priority);
            }
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }
            
            // Filter assigned_to_id (Hanya relevan untuk Admin/Staff)
            if ($request->has('assigned_to_id')) {
                // User biasa tidak boleh mengintip tiket orang lain via filter ini
                if ($user->role->role_name !== 'USER') {
                    $query->where('assigned_to_id', $request->assigned_to_id);
                }
            }

            // Filter requester_id (Admin bisa cari user tertentu, User tetap terkunci ke ID sendiri)
            if ($request->has('requester_id')) {
                if ($user->role->role_name !== 'USER') {
                    $query->where('requester_id', $request->requester_id);
                }
            }

            if ($request->has('is_security_incident')) {
                $query->where('is_security_incident', $request->boolean('is_security_incident'));
            }
            
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('ticket_number', 'like', "%{$search}%")
                      ->orWhere('subject', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 15);
            $tickets = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $tickets
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tickets: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|exists:ticket_categories,id',
                'subcategory_id' => 'nullable|exists:ticket_subcategories,id',
                'priority' => 'required|in:LOW,MEDIUM,HIGH,URGENT,CRITICAL',
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();

            DB::beginTransaction();

            $ticketNumber = $this->ticketService->generateTicketNumber();
            $category = TicketCategory::findOrFail($request->category_id);
            
            // SECURITY: Memastikan flag security incident valid
            $isSecurityIncident = $category->is_security_related || 
                                 $request->priority === 'CRITICAL' ||
                                 ($request->boolean('is_security_incident') && $user->role->role_name !== 'USER'); // User biasa tidak bisa manual set flag ini tanpa trigger logic

            $priority = $isSecurityIncident && $request->priority !== 'CRITICAL' 
                ? 'CRITICAL' 
                : $request->priority;
            
            $slaPolicy = $this->slaService->getSLAPolicyForPriority($priority, $category);
            $dueDate = $this->slaService->calculateDueDate($slaPolicy);

            $ticket = Ticket::create([
                'ticket_number' => $ticketNumber,
                'ticket_type' => $request->get('ticket_type', 'INCIDENT'),
                'category_id' => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'requester_id' => $user->id,
                'status' => 'NEW', // SECURITY: Force status NEW saat pembuatan
                'priority' => $priority,
                'sla_id' => $slaPolicy->id,
                'due_date' => $dueDate,
                'subject' => $request->subject,
                'description' => $request->description,
                'is_security_incident' => $isSecurityIncident,
            ]);

            $this->ticketService->addHistory($ticket, 'CREATED', $user->id, null, null, 'Ticket created');
            $ticket->watchers()->attach($user->id, [
                'notify_on_update' => true,
                'notify_on_comment' => true,
                'notify_on_status_change' => true,
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->ticketService->uploadAttachment($ticket, $file, $user->id);
                }
            }

            $this->slaService->createSLATracking($ticket, $slaPolicy);

            if ($isSecurityIncident) {
                $this->ticketService->createSecurityIncident($ticket);
            }

            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'action_type' => 'TICKET_CREATED',
                'entity_type' => 'Ticket',
                'entity_id' => $ticket->id,
                'description' => "Created ticket: {$ticket->ticket_number}",
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            AutoAssignTicketJob::dispatch($ticket->id);
            SendNotificationJob::dispatch('TICKET_CREATED', $ticket->id, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Ticket created successfully',
                'data' => [
                    'ticket_id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'status' => $ticket->status,
                    'due_date' => $ticket->due_date,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $ticket = Ticket::with([
                'category',
                'subcategory',
                'requester',
                'assignedTo',
                'team',
                'sla',
                'slaTracking',
                'histories.user',
                'comments.user',
                'attachments.uploadedBy',
                'watchers',
                'escalations',
                'securityIncident'
            ])->findOrFail($id);

            // SECURITY HARDENING: Access Control View
            $user = request()->user();
            if ($user->role->role_name === 'USER' && 
                $ticket->requester_id !== $user->id && 
                !$ticket->watchers->contains($user->id)) {
                
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this ticket'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $ticket
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found or access denied'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $ticket = Ticket::findOrFail($id);
            $user = $request->user();

            // Cek permission dasar
            if (!$this->canModifyTicket($user, $ticket)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to modify this ticket'
                ], 403);
            }

            // SECURITY HARDENING: Strict Input Validation berdasarkan Role
            $isAdminOrStaff = $user->role->role_name === 'ADMIN' || $user->hasPermission('TICKET_MANAGE_ALL');
            
            $allowedFields = [];
            
            if ($isAdminOrStaff) {
                // Admin/Staff boleh update hampir semua
                $allowedFields = ['status', 'priority', 'assigned_to_id', 'team_id', 'subject', 'description'];
            } else {
                // User biasa HANYA boleh update subject/desc (jika belum diproses) atau Cancel
                if ($ticket->status === 'NEW') {
                    $allowedFields = ['subject', 'description', 'status']; // status restricted to CANCELLED below
                } else {
                    $allowedFields = ['status']; // Only allowing status change for cancellation/reopen logic
                }
            }

            // Filter input hanya field yang diizinkan
            $input = $request->only($allowedFields);

            // Validasi tambahan untuk User biasa
            if (!$isAdminOrStaff && isset($input['status'])) {
                // User hanya boleh Cancel ticket
                if ($input['status'] !== 'CANCELLED' && $input['status'] !== 'CLOSED') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Users can only Cancel or Close their tickets.'
                    ], 403);
                }
            }

            $validator = Validator::make($input, [
                'status' => 'sometimes|in:NEW,ASSIGNED,IN_PROGRESS,PENDING,RESOLVED,CLOSED,CANCELLED',
                'priority' => 'sometimes|in:LOW,MEDIUM,HIGH,URGENT,CRITICAL',
                'assigned_to_id' => 'sometimes|exists:users,id',
                'team_id' => 'sometimes|exists:teams,id',
                'subject' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $oldStatus = $ticket->status;
            $oldPriority = $ticket->priority;
            $oldAssignedTo = $ticket->assigned_to_id;

            $ticket->fill($input);

            if ($request->has('status') && $request->status !== $oldStatus) {
                $this->ticketService->addHistory($ticket, 'STATUS_CHANGED', $user->id, 'status', $oldStatus, $request->status);
                
                if ($request->status === 'IN_PROGRESS' && !$ticket->first_response_at) {
                    $ticket->first_response_at = now();
                    $this->slaService->updateFirstResponse($ticket);
                }
            }

            if ($request->has('priority') && $request->priority !== $oldPriority) {
                $this->ticketService->addHistory($ticket, 'PRIORITY_CHANGED', $user->id, 'priority', $oldPriority, $request->priority);
            }

            if ($request->has('assigned_to_id') && $request->assigned_to_id !== $oldAssignedTo) {
                $this->ticketService->addHistory($ticket, 'ASSIGNED', $user->id, 'assigned_to_id', $oldAssignedTo, $request->assigned_to_id);
                
                if ($request->assigned_to_id) {
                    $ticket->watchers()->syncWithoutDetaching([$request->assigned_to_id => [
                        'notify_on_update' => true,
                        'notify_on_comment' => true,
                        'notify_on_status_change' => true,
                    ]]);
                }
            }

            $ticket->save();

            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'action_type' => 'TICKET_UPDATED',
                'entity_type' => 'Ticket',
                'entity_id' => $ticket->id,
                'description' => "Updated ticket: {$ticket->ticket_number}",
                'old_values' => $request->only(array_keys($input)), // Log only what was actually allowed to change
                'new_values' => $ticket->only(array_keys($input)),
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            SendNotificationJob::dispatch('TICKET_UPDATED', $ticket->id, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Ticket updated successfully',
                'data' => $ticket->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resolve(Request $request, $id)
    {
        try {
            $ticket = Ticket::findOrFail($id);
            $user = $request->user();

            // SECURITY HARDENING: Authorization
            // Hanya Admin atau Assignee yang boleh resolve. 
            // Requester tidak boleh resolve technical issue, mereka hanya boleh confirm close.
            $isAuthorized = $user->hasPermission('TICKET_RESOLVE') || 
                            $ticket->assigned_to_id === $user->id ||
                            $user->role->role_name === 'ADMIN';

            if (!$isAuthorized) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only assigned staff or admin can resolve tickets.'
                ], 403);
            }

            if ($ticket->status === 'RESOLVED' || $ticket->status === 'CLOSED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket is already resolved'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'resolution_status' => 'required|in:RESOLVED,WORKAROUND,CANNOT_REPRODUCE,DUPLICATE',
                'resolution_summary' => 'required|string',
                'root_cause' => 'nullable|string',
                'actions_taken' => 'nullable|string',
                'preventive_measures' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $ticket->update([
                'status' => 'RESOLVED',
                'resolution_status' => $request->resolution_status,
                'resolution_summary' => $request->resolution_summary,
                'root_cause' => $request->root_cause,
                'actions_taken' => $request->actions_taken,
                'preventive_measures' => $request->preventive_measures,
                'resolved_at' => now(),
                'resolution_duration' => $ticket->created_at->diffInMinutes(now()),
            ]);

            $this->slaService->updateResolution($ticket);
            $this->ticketService->addHistory($ticket, 'RESOLVED', $user->id, 'status', 'IN_PROGRESS', 'RESOLVED', 'Ticket resolved');

            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'action_type' => 'TICKET_RESOLVED',
                'entity_type' => 'Ticket',
                'entity_id' => $ticket->id,
                'description' => "Resolved ticket: {$ticket->ticket_number}",
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            SendNotificationJob::dispatch('TICKET_RESOLVED', $ticket->id, $ticket->requester_id);

            return response()->json([
                'success' => true,
                'message' => 'Ticket resolved successfully',
                'data' => $ticket->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addComment(Request $request, $id)
    {
        try {
            $ticket = Ticket::findOrFail($id);
            $user = $request->user();
            
            // Cek akses view sebelum comment
            if ($user->role->role_name === 'USER' && $ticket->requester_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validator = Validator::make($request->all(), [
                'comment_text' => 'required|string',
                'comment_type' => 'sometimes|in:PUBLIC,INTERNAL',
                'time_spent' => 'nullable|integer|min:0',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // SECURITY: User biasa tidak boleh buat INTERNAL comment
            if ($user->role->role_name === 'USER' && $request->comment_type === 'INTERNAL') {
                return response()->json([
                    'success' => false, 
                    'message' => 'Users cannot create internal comments'
                ], 403);
            }

            DB::beginTransaction();

            $comment = $ticket->comments()->create([
                'user_id' => $user->id,
                'comment_text' => $request->comment_text,
                'comment_type' => $request->get('comment_type', 'PUBLIC'),
                'time_spent' => $request->time_spent,
                'is_visible_to_requester' => $request->get('comment_type', 'PUBLIC') === 'PUBLIC',
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->ticketService->uploadAttachment($ticket, $file, $user->id, $comment->id);
                }
            }

            $mentionedUserIds = $this->ticketService->parseMentions($request->comment_text);
            if (!empty($mentionedUserIds)) {
                $comment->update(['mentioned_user_ids' => $mentionedUserIds]);
                
                foreach ($mentionedUserIds as $mentionedUserId) {
                    SendNotificationJob::dispatch('MENTIONED_IN_COMMENT', $ticket->id, $mentionedUserId, [
                        'comment_id' => $comment->id,
                        'mentioned_by' => $user->id,
                    ]);
                }
            }

            // Update first response if it's the assignee responding
            if (!$ticket->first_response_at && $ticket->assigned_to_id === $user->id) {
                $ticket->update(['first_response_at' => now()]);
                $this->slaService->updateFirstResponse($ticket);
            }

            $this->ticketService->addHistory($ticket, 'COMMENT_ADDED', $user->id, null, null, 'Comment added');

            DB::commit();

            SendNotificationJob::dispatch('COMMENT_ADDED', $ticket->id, $user->id, [
                'comment_id' => $comment->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully',
                'data' => $comment->load('user', 'attachments')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add comment: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function canModifyTicket($user, $ticket): bool
    {
        if ($user->hasPermission('TICKET_MODIFY_ALL') || $user->role->role_name === 'ADMIN') {
            return true;
        }

        if ($ticket->assigned_to_id === $user->id) {
            return true;
        }

        // Requester can modify (subject to strict validation in update method)
        if ($ticket->requester_id === $user->id) {
            return true;
        }

        return false;
    }
}