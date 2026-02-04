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
                <h1 class="text-2xl font-bold text-slate-800" x-text="activeTab === 'users' ? 'User Management' : 'Group Management'"></h1>
                <p class="text-slate-500 mt-1">Manage your organization structure and access.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-500 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                    {{ now()->format('l, d M Y') }}
                </span>
            </div>
        </div>

        <!-- Stats -->
        @include('admin.partials.stats')

        <!-- Users Tab -->
        <div x-show="activeTab === 'users'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <!-- Form View -->
            <div x-show="showForm" class="mb-8">
                @include('admin.partials.user-modal')
            </div>
            
            <!-- Table View -->
            <div x-show="!showForm">
                @include('admin.partials.users-table')
            </div>
        </div>

        <!-- Groups Tab -->
        <div x-show="activeTab === 'groups'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
            <!-- Form View -->
            <div x-show="showForm" class="mb-8">
                @include('admin.partials.group-modal')
            </div>

            <!-- List/Details View -->
            <div x-show="!showForm">
                @include('admin.partials.groups-list')
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal (Keeping this as modal as it's a critical confirmation) -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-[60] bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showDeleteModal = false" class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6">
            <h3 class="text-xl font-bold mb-2 text-slate-800">Delete Document?</h3>
            <p class="text-gray-500 text-sm mb-4">
                You are about to delete <strong x-text="docToDelete?.title"></strong>. This will notify the author.
            </p>
            
            <div class="mb-6">
                <label class="block text-xs font-bold text-gray-700 mb-1">Reason for Deletion</label>
                <textarea 
                    x-model="deleteReason" 
                    class="w-full p-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none"
                    rows="3"
                    placeholder="e.g., Duplicate document, Incorrect format..."
                ></textarea>
            </div>

            <div class="flex gap-3">
                <button
                    @click="showDeleteModal = false; docToDelete = null"
                    class="flex-1 py-2.5 text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors"
                >
                    Cancel
                </button>
                <button
                    @click="confirmDelete()"
                    class="flex-1 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition-colors shadow-sm"
                >
                    Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div x-show="notification.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         class="fixed bottom-4 right-4 z-50 flex items-center w-full max-w-xs p-4 rounded-lg shadow-lg border"
         :class="{
            'bg-green-50 text-green-800 border-green-200': notification.type === 'success',
            'bg-red-50 text-red-800 border-red-200': notification.type === 'error'
         }"
         role="alert">
        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg"
             :class="{
                'bg-green-100 text-green-500': notification.type === 'success',
                'bg-red-100 text-red-500': notification.type === 'error'
             }">
            <template x-if="notification.type === 'success'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </template>
            <template x-if="notification.type === 'error'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </template>
        </div>
        <div class="ml-3 text-sm font-medium" x-text="notification.message"></div>
        <button type="button" @click="notification.show = false" class="ml-auto -mx-1.5 -my-1.5 rounded-lg focus:ring-2 p-1.5 inline-flex h-8 w-8"
                :class="{
                    'bg-green-50 text-green-500 hover:bg-green-200 focus:ring-green-400': notification.type === 'success',
                    'bg-red-50 text-red-500 hover:bg-red-200 focus:ring-red-400': notification.type === 'error'
                }">
            <span class="sr-only">Close</span>
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
        </button>
    </div>
    
</div>
@vite('resources/js/admin/index.js')
@endsection
