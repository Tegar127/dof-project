<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentWorkLog;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Display a listing of groups.
     */
    public function index()
    {
        $groups = Group::all()->map(function($group) {
             $group->total_minutes = DocumentWorkLog::where('group_name', $group->name)->sum('duration_minutes');
             return $group;
        });
        return response()->json($groups);
    }

    /**
     * Store a newly created group.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:groups|max:255',
        ]);

        $group = Group::create($validated);

        return response()->json([
            'success' => true,
            'group' => $group,
        ], 201);
    }

    /**
     * Get detailed stats for a group.
     */
    public function showStats($id)
    {
        $group = Group::findOrFail($id);
        
        // Calculate total minutes
        $group->total_minutes = DocumentWorkLog::where('group_name', $group->name)->sum('duration_minutes');
        
        // Get documents worked on by this group
        // We aggregate logs by document_id
        $logs = DocumentWorkLog::where('group_name', $group->name)
            ->with('document')
            ->get()
            ->groupBy('document_id');
            
        $documents = [];
        foreach ($logs as $docId => $docLogs) {
            $doc = $docLogs->first()->document;
            if ($doc) {
                $documents[] = [
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'type' => $doc->type,
                    'status' => $doc->status,
                    'total_minutes' => $docLogs->sum('duration_minutes'),
                    'last_worked' => $docLogs->sortByDesc('end_time')->first()->end_time,
                ];
            }
        }
        
        return response()->json([
            'group' => $group,
            'documents' => $documents
        ]);
    }
}
