<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Display a listing of groups.
     */
    public function index()
    {
        $groups = Group::all()->pluck('name');
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
}
