@extends('layouts.app')

@section('title', 'Dashboard - DOF')

@section('content')
<div class="min-h-screen bg-slate-50/50 font-sans text-slate-900" x-data="dashboardApp()" x-init="init()">
    <!-- Success Modal -->
    <div x-show="showSuccessModal" x-cloak x-transition class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showSuccessModal = false" class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6 text-center">
            <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4 text-emerald-600">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2 text-slate-800">Berhasil!</h3>
            <p class="text-gray-500 text-sm mb-6" x-text="alertMessage"></p>
            <button 
                @click="showSuccessModal = false" 
                class="w-full py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium transition-colors shadow-sm"
            >
                Tutup
            </button>
        </div>
    </div>

    <!-- Create Document Modal -->
    <div x-show="showCreateModal" x-cloak x-transition class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showCreateModal = false" class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <h3 class="text-xl font-bold mb-2 text-slate-800" x-text="'Buat ' + (documentType === 'nota' ? 'Nota Dinas' : 'SPPD') + ' Baru'"></h3>
            <p class="text-gray-500 text-sm mb-6">Masukkan nama dokumen untuk melanjutkan</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Dokumen</label>
                    <input
                        type="text"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                        :placeholder="documentType === 'nota' ? 'Contoh: Nota Dinas Rapat Koordinasi' : 'Contoh: SPPD Jakarta Mei 2026'"
                        x-model="documentName"
                        @keyup.enter="confirmCreate()"
                    />
                </div>

                <div class="flex gap-3 pt-2">
                    <button
                        @click="showCreateModal = false; documentName = ''; documentType = null"
                        class="flex-1 py-2.5 text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        @click="confirmCreate()"
                        class="flex-1 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors shadow-sm"
                    >
                        Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" x-cloak x-transition class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showDeleteModal = false" class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6">
            <div class="text-center">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4 text-red-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2 text-slate-800">Hapus Dokumen?</h3>
                <p class="text-gray-500 text-sm mb-6">
                    Apakah anda yakin ingin menghapus dokumen <strong x-text="'\"' + docToDelete?.title + '\"'"></strong>? Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="flex gap-3">
                    <button
                        @click="showDeleteModal = false; docToDelete = null"
                        class="flex-1 py-2.5 text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        @click="confirmDelete()"
                        class="flex-1 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition-colors shadow-sm"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Decoration -->
    <div class="h-64 bg-slate-900 absolute top-0 left-0 right-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 to-blue-800 opacity-80"></div>
        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20"></div>
    </div>

    <div class="relative z-10 max-w-6xl mx-auto px-6 py-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
            <div class="text-white">
                <div class="flex items-center gap-3 mb-2 opacity-90">
                    <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center font-bold text-sm border border-white/10" x-text="currentUser?.name?.charAt(0)"></div>
                    <span class="font-medium tracking-wide text-sm" x-text="currentUser?.role === 'reviewer' ? 'Reviewer Panel' : 'Staff Workspace'"></span>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold tracking-tight">Halo, <span x-text="currentUser?.name?.split(' ')[0]"></span> 👋</h1>
                <p class="text-blue-100 mt-2 text-lg">Kelola dokumen dinas anda dengan mudah dan cepat.</p>
            </div>

            <button
                @click="handleLogout()"
                class="bg-white/10 hover:bg-white/20 text-white backdrop-blur-md px-4 py-2.5 rounded-xl text-sm font-medium transition-all border border-white/10 flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Sign Out
            </button>
        </div>

        <!-- Quick Actions & Stats -->
        <div class="mb-12">
            <div class="flex items-center gap-3 mb-6">
                <div class="h-8 w-1.5 bg-indigo-500 rounded-full"></div>
                <h2 class="text-white font-bold text-xl tracking-tight">Buat Dokumen Baru</h2>
                <!-- Debug Role (temporary, can be removed later) -->
                <!-- <span class="text-xs text-white/30 font-mono" x-text="'(Role: ' + (currentUser?.role || '-') + ')'"></span> -->
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- User Cards -->
                <!-- Card 1: Nota Dinas -->
                <button 
                    x-show="currentUser?.role === 'user'" 
                    @click="handleCreate('nota')" 
                    class="group relative bg-white p-8 rounded-3xl shadow-sm border border-slate-200 hover:border-indigo-500/50 transition-all hover:shadow-2xl hover:shadow-indigo-500/10 hover:-translate-y-1 overflow-hidden text-left"
                >
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM13 9V3.5L18.5 9H13z"/>
                        </svg>
                    </div>
                    
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 mb-6 group-hover:scale-110 transition-transform group-hover:bg-indigo-600 group-hover:text-white shadow-sm shadow-indigo-100">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2">Nota Dinas Baru</h3>
                        <p class="text-slate-500 text-sm leading-relaxed mb-6">Buat draf nota dinas resmi dengan format standar secara otomatis.</p>
                        <div class="flex items-center text-indigo-600 font-semibold text-sm group-hover:gap-2 transition-all">
                            Mulai Buat 
                            <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </div>
                    </div>
                </button>

                <!-- Card 2: SPPD -->
                <button 
                    x-show="currentUser?.role === 'user'" 
                    @click="handleCreate('sppd')" 
                    class="group relative bg-white p-8 rounded-3xl shadow-sm border border-slate-200 hover:border-emerald-500/50 transition-all hover:shadow-2xl hover:shadow-emerald-500/10 hover:-translate-y-1 overflow-hidden text-left"
                >
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L4.5 20.29l.71.71L12 18l6.79 3 .71-.71z"/>
                        </svg>
                    </div>
                    
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 mb-6 group-hover:scale-110 transition-transform group-hover:bg-emerald-600 group-hover:text-white shadow-sm shadow-emerald-100">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2">SPPD Baru</h3>
                        <p class="text-slate-500 text-sm leading-relaxed mb-6">Terbitkan Surat Perintah Perjalanan Dinas dengan data personil lengkap.</p>
                        <div class="flex items-center text-emerald-600 font-semibold text-sm group-hover:gap-2 transition-all">
                            Mulai Buat 
                            <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </div>
                    </div>
                </button>

                <!-- Card 3: Stats -->
                <div 
                    x-show="currentUser?.role === 'user'"
                    class="bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 p-8 rounded-3xl shadow-xl shadow-slate-900/20 text-white relative overflow-hidden"
                >
                    <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-indigo-500/10 rounded-full blur-3xl"></div>
                    <div class="relative z-10 h-full flex flex-col justify-between">
                        <div>
                            <div class="text-slate-400 text-sm font-bold uppercase tracking-widest mb-1">Total Dokumen</div>
                            <div class="text-5xl font-black tracking-tight" x-text="filteredDocs.length"></div>
                        </div>
                        <div class="mt-8 flex flex-col gap-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-400">Telah Disetujui</span>
                                <span class="font-bold text-emerald-400" x-text="filteredDocs.filter(d => d.status === 'approved').length"></span>
                            </div>
                            <div class="w-full bg-white/5 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-emerald-400 h-full rounded-full transition-all duration-500" :style="'width: ' + (filteredDocs.length > 0 ? (filteredDocs.filter(d => d.status === 'approved').length / filteredDocs.length * 100) : 0) + '%'"></div>
                            </div>
                            <div class="flex items-center justify-between text-sm mt-1">
                                <span class="text-slate-400">Dalam Proses</span>
                                <span class="font-bold text-amber-400" x-text="filteredDocs.filter(d => d.status === 'pending_review').length"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reviewer Cards -->
                <template x-if="currentUser?.role === 'reviewer'">
                    <div class="col-span-3 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-slate-800" x-text="filteredDocs.filter(d => d.status === 'pending_review').length"></div>
                                <div class="text-slate-500 text-sm">Menunggu Review</div>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-slate-800" x-text="filteredDocs.filter(d => d.status === 'approved').length"></div>
                                <div class="text-slate-500 text-sm">Telah Disetujui</div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Documents Table -->
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
            <!-- Toolbar -->
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4 bg-white">
                <h2 class="font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Daftar Dokumen
                </h2>
                <div class="relative w-full sm:w-64">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="text"
                        placeholder="Cari dokumen..."
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                        x-model="searchTerm"
                    />
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <th class="px-6 py-4">Dokumen</th>
                            <th class="px-6 py-4">Tipe</th>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Deadline</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-if="filteredDocs.length === 0">
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <svg class="w-12 h-12 mb-3 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p>Tidak ada dokumen ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <template x-for="doc in filteredDocs" :key="doc.id">
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div :class="doc.type === 'nota' ? 'bg-indigo-50 text-indigo-600' : 'bg-emerald-50 text-emerald-600'" class="w-10 h-10 rounded-lg flex items-center justify-center text-lg font-bold flex-shrink-0">
                                            <span x-text="doc.type === 'nota' ? 'N' : 'S'"></span>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-slate-900 group-hover:text-indigo-600 transition-colors" x-text="doc.title"></div>
                                            <div class="text-xs text-slate-500 font-mono mt-0.5" x-text="doc.content_data?.docNumber || doc.data?.docNumber || 'No Ref'"></div>
                                            
                                            <!-- Sender Info for Receiver -->
                                            <template x-if="doc.author_id !== currentUser?.id">
                                                <div class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                    <span x-text="'Dari: ' + (doc.author?.group_name || doc.author_name || 'Unknown')"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-slate-600 font-medium capitalize bg-slate-100 px-2 py-1 rounded-md" x-text="doc.type"></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-700 font-medium" x-text="formatDate(doc.created_at).d"></div>
                                    <div class="text-[10px] text-slate-400 flex items-center gap-1 mt-0.5">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span x-text="formatDate(doc.created_at).t"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div x-show="doc.deadline" class="flex items-center gap-2">
                                        <svg class="w-4 h-4" :class="getDeadlineColor(getDeadlineStatus(doc.deadline))" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-sm font-medium" :class="getDeadlineColor(getDeadlineStatus(doc.deadline))" x-text="formatDeadline(doc.deadline)"></span>
                                    </div>
                                    <div x-show="!doc.deadline" class="text-xs text-slate-400">-</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="getStatusClass(doc.status)" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span x-text="getStatusLabel(doc)"></span>
                                        
                                        <!-- Read Receipt for Sender -->
                                        <template x-if="doc.status === 'received' && doc.author_id === currentUser.id">
                                            <svg class="w-4 h-4 text-violet-600 ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" title="Dibaca oleh penerima">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7M5 13l4 4L19 7" /> <!-- Double check style -->
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
                                            </svg>
                                        </template>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a
                                            :href="'/documents/' + doc.id"
                                            class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm"
                                        >
                                            <span>Detail</span>
                                        </a>
                                        <a
                                            :href="'/editor/' + doc.id"
                                            class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-all shadow-sm"
                                        >
                                            <span x-text="currentUser?.role === 'reviewer' ? 'Review' : 'Edit'"></span>
                                        </a>
                                        <template x-if="currentUser?.role === 'user'">
                                            <button
                                                @click="handleDelete(doc.id, doc.title)"
                                                class="inline-flex items-center justify-center px-3 py-2 rounded-lg bg-white border border-slate-200 text-red-500 hover:text-red-700 hover:bg-red-50 hover:border-red-200 transition-all shadow-sm"
                                                title="Hapus Dokumen"
                                            >
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <template x-if="filteredDocs.length > 0">
                <div class="p-4 border-t border-slate-100 bg-slate-50/30 text-xs text-slate-400 flex justify-between items-center">
                    <span x-text="'Menampilkan ' + filteredDocs.length + ' dokumen'"></span>
                </div>
            </template>
        </div>
    </div>
</div>


@endsection
