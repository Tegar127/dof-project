@extends('layouts.app')

@section('title', 'Editor - DOF')

@section('content')
<div class="min-h-screen bg-slate-50 font-sans" x-data="editorApp()" x-init="init()">
    <!-- Header -->
    <div class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="/dashboard" class="text-slate-600 hover:text-slate-900">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-900" x-text="document.title || 'New Document'"></h1>
                    <p class="text-sm text-slate-500" x-text="document.type ? (document.type === 'nota' ? 'Nota Dinas' : 'SPPD') : 'Document Editor'"></p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-500" x-text="'Status: ' + getStatusLabel(document.status)"></span>
                <button
                    @click="saveDocument()"
                    :disabled="saving"
                    class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    <span x-text="saving ? 'Saving...' : 'Save Document'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-5xl mx-auto px-6 py-8">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8">
            <!-- Document Type Selector (only for new documents) -->
            <template x-if="!documentId || documentId === 'new'">
                <div class="mb-8 pb-8 border-b border-slate-200">
                    <h3 class="text-lg font-semibold mb-4">Select Document Type</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <button
                            @click="document.type = 'nota'"
                            :class="document.type === 'nota' ? 'border-indigo-600 bg-indigo-50' : 'border-slate-200 hover:border-slate-300'"
                            class="p-6 border-2 rounded-xl transition-all text-left"
                        >
                            <div class="font-semibold text-slate-900">Nota Dinas</div>
                            <p class="text-sm text-slate-500 mt-1">Internal office memo</p>
                        </button>
                        <button
                            @click="document.type = 'sppd'"
                            :class="document.type === 'sppd' ? 'border-emerald-600 bg-emerald-50' : 'border-slate-200 hover:border-slate-300'"
                            class="p-6 border-2 rounded-xl transition-all text-left"
                        >
                            <div class="font-semibold text-slate-900">SPPD</div>
                            <p class="text-sm text-slate-500 mt-1">Official travel document</p>
                        </button>
                    </div>
                </div>
            </template>

            <!-- Document Form -->
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Document Number</label>
                        <input
                            type="text"
                            x-model="document.content_data.docNumber"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="e.g., 001/ND/2026"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Date</label>
                        <input
                            type="date"
                            x-model="document.content_data.date"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>
                </div>

                <template x-if="document.type === 'nota'">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Kepada</label>
                            <input
                                type="text"
                                x-model="document.content_data.to"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Recipient"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Dari</label>
                            <input
                                type="text"
                                x-model="document.content_data.from"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Sender"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Perihal</label>
                            <input
                                type="text"
                                x-model="document.content_data.subject"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Subject"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Isi</label>
                            <textarea
                                x-model="document.content_data.content"
                                rows="8"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Content of the memo..."
                            ></textarea>
                        </div>
                    </div>
                </template>

                <template x-if="document.type === 'sppd'">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Nama Pegawai</label>
                            <input
                                type="text"
                                x-model="document.content_data.employeeName"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Employee Name"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">NIP</label>
                            <input
                                type="text"
                                x-model="document.content_data.nip"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Identification Number"
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Tujuan</label>
                                <input
                                    type="text"
                                    x-model="document.content_data.destination"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Destination"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Lama Perjalanan</label>
                                <input
                                    type="text"
                                    x-model="document.content_data.duration"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="e.g., 3 days"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Keperluan</label>
                            <textarea
                                x-model="document.content_data.purpose"
                                rows="6"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Purpose of travel..."
                            ></textarea>
                        </div>
                    </div>
                </template>

                <!-- Target Section -->
                <div class="pt-6 border-t border-slate-200">
                    <h4 class="text-sm font-semibold text-slate-700 mb-4">Send To</h4>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-4 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                            <input
                                type="radio"
                                name="target"
                                value="group"
                                x-model="document.target_role"
                                class="text-indigo-600"
                            />
                            <div class="flex-1">
                                <div class="font-medium text-slate-900">Send to Group</div>
                                <p class="text-sm text-slate-500">Send to specific department</p>
                            </div>
                        </label>
                        <template x-if="document.target_role === 'group'">
                            <select
                                x-model="document.target_value"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 ml-8"
                            >
                                <option value="">Select Group</option>
                                <template x-for="group in groups" :key="group">
                                    <option :value="group" x-text="group"></option>
                                </template>
                            </select>
                        </template>

                        <label class="flex items-center gap-3 p-4 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                            <input
                                type="radio"
                                name="target"
                                value="dispo"
                                x-model="document.target_role"
                                class="text-indigo-600"
                            />
                            <div class="flex-1">
                                <div class="font-medium text-slate-900">Send to Disposisi (Reviewer)</div>
                                <p class="text-sm text-slate-500">Send for approval</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Reviewer Feedback Section (if reviewer) -->
                <template x-if="currentUser?.role === 'reviewer' && document.id">
                    <div class="pt-6 border-t border-slate-200 space-y-4">
                        <h4 class="text-sm font-semibold text-slate-700">Reviewer Actions</h4>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Feedback</label>
                            <textarea
                                x-model="document.feedback"
                                rows="4"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Your feedback..."
                            ></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button
                                @click="updateStatus('approved')"
                                class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors"
                            >
                                Approve
                            </button>
                            <button
                                @click="updateStatus('needs_revision')"
                                class="px-6 py-2.5 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors"
                            >
                                Request Revision
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function editorApp() {
    return {
        documentId: null,
        currentUser: null,
        document: {
            title: '',
            type: '',
            status: 'draft',
            content_data: {},
            target_role: '',
            target_value: '',
            feedback: ''
        },
        groups: [],
        saving: false,

        async init() {
            // Check auth
            const userData = localStorage.getItem('dof_user');
            if (!userData) {
                window.location.href = '/login';
                return;
            }
            this.currentUser = JSON.parse(userData);

            // Get document ID from URL
            const path = window.location.pathname;
            this.documentId = path.split('/').pop();

            // Load groups
            await this.loadGroups();

            // Load document if editing
            if (this.documentId && this.documentId !== 'new') {
                await this.loadDocument();
            } else {
                // Check for new document data
                const newDocData = localStorage.getItem('dof_new_doc');
                if (newDocData) {
                    const data = JSON.parse(newDocData);
                    this.document.type = data.type;
                    this.document.title = data.name;
                    localStorage.removeItem('dof_new_doc');
                }
            }
        },

        async loadGroups() {
            try {
                const response = await fetch('/api/groups', {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    this.groups = await response.json();
                }
            } catch (error) {
                console.error('Error loading groups:', error);
            }
        },

        async loadDocument() {
            try {
                const response = await fetch(`/api/documents/${this.documentId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    this.document = await response.json();
                }
            } catch (error) {
                console.error('Error loading document:', error);
            }
        },

        async saveDocument() {
            this.saving = true;

            try {
                const url = this.document.id ? `/api/documents/${this.document.id}` : '/api/documents';
                const method = this.document.id ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        title: this.document.title,
                        type: this.document.type,
                        status: this.document.status || 'draft',
                        content_data: this.document.content_data,
                        target: {
                            type: this.document.target_role,
                            value: this.document.target_value
                        }
                    })
                });

                if (response.ok) {
                    const result = await response.json();
                    if (!this.document.id && result.id) {
                        window.location.href = `/editor/${result.id}`;
                    } else {
                        alert('Document saved successfully!');
                    }
                }
            } catch (error) {
                console.error('Error saving document:', error);
                alert('Failed to save document');
            } finally {
                this.saving = false;
            }
        },

        async updateStatus(newStatus) {
            try {
                const response = await fetch(`/api/documents/${this.document.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        status: newStatus,
                        feedback: this.document.feedback
                    })
                });

                if (response.ok) {
                    alert('Status updated successfully!');
                    window.location.href = '/dashboard';
                }
            } catch (error) {
                console.error('Error updating status:', error);
            }
        },

        getStatusLabel(status) {
            const labels = {
                draft: 'Draft',
                pending_review: 'Pending Review',
                needs_revision: 'Needs Revision',
                approved: 'Approved',
                received: 'Received'
            };
            return labels[status] || 'Draft';
        }
    }
}
</script>
@endsection
