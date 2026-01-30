<?php

namespace App\Http\Controllers;

use App\Enums\DocumentStatus;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    /**
     * Show the print view for the document.
     */
    public function print(Document $document)
    {
        return view('documents.print', ['document' => $document]);
    }

    /**
     * Display a listing of documents based on user role.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get documents filtered by user role using the scope
        $documents = Document::forUser($user)
            ->with('author')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($documents);
    }

    /**
     * Store a newly created document.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:nota,sppd',
            'status' => 'sometimes|string',
            'content_data' => 'sometimes|array',
            'history_log' => 'sometimes|array',
            'target' => 'sometimes|array',
            'folder_id' => 'nullable|exists:folders,id',
            'deadline' => 'nullable|date',
            'approval_count' => 'nullable|integer|min:0|max:10',
            'approvals' => 'nullable|array',
            'approvals.*.position' => 'nullable|in:direksi,kadiv,kabid,staff',
            'approvals.*.approver_id' => 'nullable|exists:users,id',
        ]);

        $user = Auth::user();

        $document = Document::create([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'status' => $validated['status'] ?? DocumentStatus::DRAFT,
            'author_id' => $user->id,
            'author_name' => $user->name,
            'content_data' => $validated['content_data'] ?? [],
            'history_log' => $validated['history_log'] ?? [],
            'target_role' => $validated['target']['type'] ?? null,
            'target_value' => $validated['target']['value'] ?? null,
            'folder_id' => $validated['folder_id'] ?? null,
            'version' => '1.0',
            'deadline' => $validated['deadline'] ?? null,
            'approval_count' => $validated['approval_count'] ?? 0,
        ]);

        // Create initial log entry
        $document->createLog('created', $user, 'Dokumen dibuat');

        // Create approval records if specified
        if (isset($validated['approvals']) && count($validated['approvals']) > 0) {
            foreach ($validated['approvals'] as $index => $approvalData) {
                $document->approvals()->create([
                    'sequence' => $index + 1,
                    'approver_position' => $approvalData['position'] ?? null,
                    'approver_id' => $approvalData['approver_id'] ?? null,
                    'status' => 'pending',
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'id' => $document->id,
            'document' => $document->load(['logs', 'approvals']),
        ], 201);
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document)
    {
        $user = Auth::user();

        // Mark document as read
        $document->markAsRead($user);

        // If opened by receiver group member and status is 'sent', update to 'received'
        if ($document->status === DocumentStatus::SENT && 
            $document->target_role === 'group' && 
            $document->target_value === $user->group_name) {
            $oldStatus = $document->status;
            $document->update(['status' => DocumentStatus::RECEIVED]);
            $document->createLog('received', $user, 'Dokumen diterima oleh ' . $user->group_name, $oldStatus, DocumentStatus::RECEIVED);
        }

        $document->load(['author', 'logs', 'approvals.approver', 'readReceipts.user', 'folder']);
        return response()->json($document);
    }

    /**
     * Update the specified document.
     */
    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'status' => 'sometimes|string',
            'content_data' => 'sometimes|array',
            'history_log' => 'sometimes|array',
            'feedback' => 'sometimes|string|nullable',
            'target' => 'sometimes|array',
            'folder_id' => 'nullable|exists:folders,id',
            'deadline' => 'nullable|date',
            'increment_version' => 'sometimes|boolean',
        ]);

        $user = Auth::user();
        $updateData = [];
        $oldStatus = $document->status;

        if (isset($validated['status'])) {
            $updateData['status'] = $validated['status'];
        }

        if (isset($validated['content_data'])) {
            $updateData['content_data'] = $validated['content_data'];
        }

        if (isset($validated['history_log'])) {
            $updateData['history_log'] = $validated['history_log'];
        }

        if (array_key_exists('feedback', $validated)) {
            $updateData['feedback'] = $validated['feedback'];
        }

        if (isset($validated['target'])) {
            $updateData['target_role'] = $validated['target']['type'];
            $updateData['target_value'] = $validated['target']['value'];
        }

        if (isset($validated['folder_id'])) {
            $updateData['folder_id'] = $validated['folder_id'];
        }

        if (isset($validated['deadline'])) {
            $updateData['deadline'] = $validated['deadline'];
        }

        $document->update($updateData);

        // Update approvals if provided
        if ($request->has('approvals')) {
            $document->approvals()->delete();
            foreach ($request->input('approvals') as $index => $approvalData) {
                $document->approvals()->create([
                    'sequence' => $index + 1,
                    'approver_position' => $approvalData['approver_position'] ?? null,
                    'status' => $approvalData['status'] ?? 'pending',
                ]);
            }
        }

        // Increment version if requested
        if (isset($validated['increment_version']) && $validated['increment_version']) {
            $document->incrementVersion();
        }

        // Determine action for logging
        $action = 'updated';
        $notes = 'Dokumen diperbarui';
        
        $statusChanged = isset($validated['status']) && $validated['status'] !== $oldStatus->value;
        $targetChanged = isset($validated['target']);

        if ($statusChanged || $targetChanged) {
            if (isset($validated['status']) && $validated['status'] === DocumentStatus::SENT->value) {
                if ($oldStatus === DocumentStatus::DRAFT || $oldStatus === DocumentStatus::NEEDS_REVISION) {
                    $action = 'sent';
                    $notes = 'Dokumen dikirim ke ' . ($validated['target']['value'] ?? $document->target_value);
                    
                    // Increment version if resending after revision
                    if ($oldStatus === DocumentStatus::NEEDS_REVISION) {
                        $document->incrementVersion();
                    }
                } else {
                    $action = 'sent';
                    $notes = 'Dokumen diteruskan ke ' . ($validated['target']['value'] ?? $document->target_value);
                    
                    // Increment version when forwarding
                    $document->incrementVersion(true);
                }
            } else if ($statusChanged) {
                $action = $validated['status'] === DocumentStatus::PENDING_REVIEW->value ? 'sent' : $validated['status'];
                $notes = $this->getStatusChangeNote($validated['status']);
                
                if ($validated['status'] === DocumentStatus::PENDING_REVIEW->value) {
                    $notes = 'Dokumen dikirim untuk review';
                }
            } else if ($targetChanged) {
                $notes = 'Tujuan dokumen diubah ke ' . ($validated['target']['value'] ?? $document->target_value);
            }
            
            $document->createLog($action, $user, $notes, $oldStatus, $validated['status'] ?? $oldStatus);
        } else {
            $document->createLog($action, $user, $notes);
        }

        return response()->json([
            'success' => true,
            'document' => $document->fresh(['logs', 'approvals']),
        ]);
    }

    /**
     * Get human-readable note for status change.
     */
    private function getStatusChangeNote($status)
    {
        $statusValue = $status instanceof DocumentStatus ? $status->value : $status;

        $notes = [
            DocumentStatus::DRAFT->value => 'Dokumen disimpan sebagai draft',
            DocumentStatus::PENDING_REVIEW->value => 'Dokumen dikirim untuk review',
            DocumentStatus::NEEDS_REVISION->value => 'Dokumen memerlukan revisi',
            DocumentStatus::APPROVED->value => 'Dokumen disetujui',
            DocumentStatus::SENT->value => 'Dokumen dikirim',
            DocumentStatus::RECEIVED->value => 'Dokumen diterima',
        ];
        
        return $notes[$statusValue] ?? 'Status dokumen diubah';
    }

    /**
     * Get document logs (delivery history).
     */
    public function logs($id)
    {
        $document = Document::findOrFail($id);
        $logs = $document->logs()->with('user')->get();
        
        return response()->json($logs);
    }

    /**
     * Remove the specified document.
     */
    public function destroy(Document $document)
    {
        $user = Auth::user();

        // Only allow author or admin to delete
        if ($document->author_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully',
        ]);
    }
}