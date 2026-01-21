<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
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
        ]);

        $user = Auth::user();

        $document = Document::create([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'status' => $validated['status'] ?? 'draft',
            'author_id' => $user->id,
            'author_name' => $user->name,
            'content_data' => $validated['content_data'] ?? [],
            'history_log' => $validated['history_log'] ?? [],
            'target_role' => $validated['target']['type'] ?? null,
            'target_value' => $validated['target']['value'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'id' => $document->id,
            'document' => $document,
        ], 201);
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document)
    {
        $user = Auth::user();

        // If opened by receiver group member and status is 'sent', update to 'received'
        if ($document->status === 'sent' && 
            $document->target_role === 'group' && 
            $document->target_value === $user->group_name) {
            $document->update(['status' => 'received']);
        }

        $document->load('author');
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
        ]);

        $updateData = [];

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

        $document->update($updateData);

        return response()->json([
            'success' => true,
            'document' => $document->fresh(),
        ]);
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