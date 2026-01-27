<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FolderController extends Controller
{
    /**
     * Get folder tree structure.
     */
    public function index()
    {
        $folders = Folder::with(['children', 'documents'])
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();
        
        return response()->json($folders);
    }

    /**
     * Create a new folder.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
            'type' => 'nullable|in:category,year,month,department,status,custom',
            'metadata' => 'nullable|array',
            'order' => 'nullable|integer',
        ]);

        $folder = Folder::create($validated);

        return response()->json([
            'success' => true,
            'folder' => $folder,
        ], 201);
    }

    /**
     * Get folder with documents.
     */
    public function show($id)
    {
        $folder = Folder::with(['children', 'documents.author', 'parent'])
            ->findOrFail($id);
        
        return response()->json($folder);
    }

    /**
     * Update folder.
     */
    public function update(Request $request, $id)
    {
        $folder = Folder::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
            'type' => 'nullable|in:category,year,month,department,status,custom',
            'metadata' => 'nullable|array',
            'order' => 'nullable|integer',
        ]);

        $folder->update($validated);

        return response()->json([
            'success' => true,
            'folder' => $folder->fresh(),
        ]);
    }

    /**
     * Delete folder.
     */
    public function destroy($id)
    {
        $folder = Folder::findOrFail($id);

        // Check if folder has documents
        if ($folder->documents()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Folder masih berisi dokumen. Pindahkan atau hapus dokumen terlebih dahulu.',
            ], 400);
        }

        // Check if folder has children
        if ($folder->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Folder masih memiliki subfolder. Hapus subfolder terlebih dahulu.',
            ], 400);
        }

        $folder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Folder berhasil dihapus.',
        ]);
    }

    /**
     * Move document to folder.
     */
    public function moveDocument(Request $request, $documentId)
    {
        $user = Auth::user();
        $document = Document::findOrFail($documentId);

        // Only author or admin can move documents
        if ($document->author_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk memindahkan dokumen ini.',
            ], 403);
        }

        $validated = $request->validate([
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        $document->update(['folder_id' => $validated['folder_id']]);

        // Create log entry
        $folderName = $validated['folder_id'] 
            ? Folder::find($validated['folder_id'])->name 
            : 'Root';
        $document->createLog('updated', $user, "Dokumen dipindahkan ke folder: {$folderName}");

        return response()->json([
            'success' => true,
            'document' => $document->fresh(['folder']),
        ]);
    }
}
