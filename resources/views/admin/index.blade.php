@extends('layouts.app')

@section('title', 'Admin Panel - DOF')

@section('content')
<div class="min-h-screen bg-slate-50 font-sans" x-data="adminApp()" x-init="init()">
    
    <!-- Sidebar -->
    @include('admin.partials.sidebar')

    <!-- Main Content -->
    <div class="ml-64 p-8 transition-all duration-300">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Dashboard Overview</h1>
                <p class="text-slate-500 mt-1">Welcome back, Admin.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-500 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                    {{ now()->format('l, d M Y') }}
                </span>
            </div>
        </div>

        <!-- Stats -->
        @include('admin.partials.stats')

        <!-- Tab Content -->
        <div x-show="activeTab === 'users'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            @include('admin.partials.users-table')
        </div>

        <div x-show="activeTab === 'groups'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
            @include('admin.partials.groups-list')
        </div>
    </div>

    <!-- Modals -->
    @include('admin.partials.user-modal')
    @include('admin.partials.group-modal')

</div>
@vite('resources/js/admin/index.js')
@endsection