@extends('layouts.app')

@section('title', 'Editor - DOF')

@section('content')
<div class="flex flex-col lg:flex-row h-screen overflow-hidden bg-gray-100 relative" x-data="editorApp()" x-init="init()">
    
    <!-- Floating Toggle Button (visible when sidebar is closed) -->
    <button 
        x-show="!sidebarOpen" 
        x-cloak
        @click="sidebarOpen = true"
        class="fixed top-4 left-4 z-50 p-3 bg-indigo-600 text-white rounded-xl shadow-lg hover:bg-indigo-700 transition-all flex items-center justify-center group"
    >
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
        <span class="max-w-0 overflow-hidden group-hover:max-w-xs group-hover:ml-2 transition-all duration-300 text-sm font-bold whitespace-nowrap">BUKA PANEL EDIT</span>
    </button>

    <!-- Modals -->
    <!-- History Modal -->
    <div x-show="showHistoryModal" x-cloak class="fixed inset-0 z-[100] bg-black/50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div @click.away="showHistoryModal = false" class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full flex flex-col max-h-[90vh] overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-slate-50">
                <div>
                    <h3 class="text-xl font-bold text-slate-800">Riwayat & Log</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pantau perubahan dan waktu pengerjaan dokumen</p>
                </div>
                <button @click="showHistoryModal = false" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-white rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="flex border-b border-gray-100 px-4 bg-white">
                <button @click="activeHistoryTab = 'status'" :class="activeHistoryTab === 'status' ? 'text-indigo-600 border-indigo-600' : 'text-slate-500 border-transparent hover:text-slate-700'" class="px-6 py-4 font-bold text-sm border-b-2 transition-colors">Riwayat Status</button>
                <button @click="activeHistoryTab = 'work'" :class="activeHistoryTab === 'work' ? 'text-indigo-600 border-indigo-600' : 'text-slate-500 border-transparent hover:text-slate-700'" class="px-6 py-4 font-bold text-sm border-b-2 transition-colors">Log Pengerjaan</button>
            </div>
            <div class="p-6 overflow-y-auto flex-1 bg-slate-50/50 custom-scrollbar">
                <div x-show="activeHistoryTab === 'status'" class="space-y-4">
                    <template x-if="loadingLogs"><div class="text-center py-12 text-slate-400 font-medium">Memuat data...</div></template>
                    <div class="relative pl-4 border-l-2 border-indigo-100 space-y-8">
                        <template x-for="(log, index) in logs" :key="index">
                            <div class="relative">
                                <div class="absolute -left-[21px] top-1 w-4 h-4 rounded-full border-2 border-white shadow-sm" :class="index === 0 ? 'bg-indigo-500 ring-4 ring-indigo-50' : 'bg-slate-300'"></div>
                                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-black uppercase tracking-wider" x-text="log.action.replace('_', ' ')"></span>
                                        <span class="text-[10px] font-medium text-slate-400 bg-slate-50 px-2 py-0.5 rounded-full" x-text="formatDeadlineDisplay(log.created_at)"></span>
                                    </div>
                                    <p class="text-sm text-slate-600 font-medium leading-relaxed" x-text="log.notes"></p>
                                    <template x-if="log.changes && log.changes !== 'Penyimpanan otomatis.'">
                                        <div class="mt-3 p-3 bg-blue-50/50 border border-blue-100 rounded-lg">
                                            <p class="text-xs text-blue-700 whitespace-pre-line italic leading-relaxed" x-text="log.changes"></p>
                                        </div>
                                    </template>
                                    <div class="flex items-center gap-2 mt-3 pt-3 border-t border-slate-50">
                                        <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-[10px] font-bold text-indigo-600" x-text="log.user_name?.charAt(0)"></div>
                                        <span class="text-xs font-bold text-slate-500" x-text="log.user_name"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <div x-show="activeHistoryTab === 'work'" class="space-y-4">
                    <template x-for="log in workLogs" :key="log.id">
                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-bold text-slate-800" x-text="log.user ? log.user.name : 'User'"></span>
                                    <span class="text-xs font-mono font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded" x-text="log.duration_minutes + ' menit'"></span>
                                </div>
                                <div class="text-xs text-slate-400 flex items-center gap-2">
                                    <span x-text="formatDeadlineDisplay(log.start_time)"></span>
                                    <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                    <span x-text="formatDeadlineDisplay(log.end_time).split(' ').pop()"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Other Modals: Signature, Success, Send, Confirm -->
    <div x-show="showSignatureModal" x-cloak class="fixed inset-0 z-[110] bg-black/60 flex items-center justify-center p-4 backdrop-blur-sm">
        <div @click.away="showSignatureModal = false" class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-8 overflow-hidden">
            <div class="mb-6">
                <h3 class="text-2xl font-black text-slate-800 tracking-tight">Tanda Tangan Digital</h3>
                <p class="text-sm text-indigo-600 font-bold uppercase tracking-widest mt-1" x-text="activeSignatureLabel"></p>
            </div>
            <div class="flex p-1 bg-slate-100 rounded-xl mb-6">
                <button @click="signatureTab = 'draw'" :class="signatureTab === 'draw' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-2.5 text-sm font-bold rounded-lg transition-all focus:outline-none uppercase tracking-wide">Gambar Manual</button>
                <button @click="signatureTab = 'upload'" :class="signatureTab === 'upload' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-2.5 text-sm font-bold rounded-lg transition-all focus:outline-none uppercase tracking-wide">Upload File</button>
            </div>
            <div x-show="signatureTab === 'draw'" class="border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50 h-72 relative mb-6 overflow-hidden ring-offset-4 ring-2 ring-transparent hover:ring-indigo-100 transition-all">
                <canvas id="signature-canvas" class="absolute inset-0 w-full h-full cursor-crosshair touch-none"></canvas>
                <div class="absolute bottom-4 right-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest pointer-events-none">Area Tanda Tangan</div>
            </div>
            <div x-show="signatureTab === 'upload'" class="border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50 h-72 flex flex-col items-center justify-center mb-6 p-8 text-center transition-all hover:bg-slate-100">
                <div class="w-16 h-16 rounded-full bg-white shadow-sm flex items-center justify-center mb-4 text-slate-400">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <input type="file" @change="handleSignatureUpload($event)" accept="image/*" class="text-sm font-medium text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all cursor-pointer">
            </div>
            <div class="flex gap-4">
                <button @click="clearSignature()" class="px-6 py-3 text-red-600 bg-red-50 hover:bg-red-100 rounded-xl font-bold transition-colors uppercase text-xs tracking-widest">Hapus</button>
                <div class="flex-1"></div>
                <button @click="showSignatureModal = false" class="px-6 py-3 bg-slate-100 text-slate-600 hover:bg-slate-200 rounded-xl font-bold transition-colors uppercase text-xs tracking-widest">Batal</button>
                <button @click="saveSignature()" class="px-8 py-3 bg-indigo-600 text-white hover:bg-indigo-700 rounded-xl font-bold transition-all shadow-lg shadow-indigo-200 uppercase text-xs tracking-widest">Simpan TTD</button>
            </div>
        </div>
    </div>

    <!-- LEFT SIDEBAR -->
    <div 
        x-show="sidebarOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="-translate-x-full opacity-0"
        x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="transition ease-in duration-300 transform"
        x-transition:leave-start="translate-x-0 opacity-100"
        x-transition:leave-end="-translate-x-full opacity-0"
        class="w-full lg:w-[480px] bg-white flex flex-col border-r border-gray-200 shadow-2xl z-40 h-full flex-shrink-0 relative"
    >
        <!-- Sidebar Toolbar -->
        <div class="px-6 py-4 bg-white border-b border-gray-100 flex justify-between items-center sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <a href="/dashboard" class="p-2 text-slate-400 hover:text-slate-800 hover:bg-slate-50 rounded-lg transition-all" title="Dashboard">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
                <div class="h-6 w-px bg-gray-100"></div>
                <button @click="sidebarOpen = false" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all lg:block hidden" title="Tutup Sidebar">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" /></svg>
                </button>
            </div>
            <div class="flex items-center gap-3">
                <button @click="openHistoryModal()" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Lihat Riwayat"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></button>
                <button @click="saveDocument()" :disabled="saving || !isEditable()" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm font-black flex items-center gap-2 disabled:opacity-50 transition-all hover:bg-indigo-700 shadow-lg shadow-indigo-100 active:scale-95">
                    <template x-if="saving"><svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></template>
                    <span x-text="saving ? 'MENYIMPAN...' : 'SIMPAN'"></span>
                </button>
            </div>
        </div>

        <!-- Sidebar Header Info -->
        <div class="px-8 py-6 bg-slate-50/80 border-b border-gray-100">
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <span class="px-2.5 py-1 rounded-lg bg-indigo-600 text-white text-[10px] font-black uppercase tracking-tighter" x-text="document.type"></span>
                <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-600 text-[10px] font-black uppercase tracking-tighter shadow-sm" x-text="getStatusLabel(document.status)"></span>
                <div class="flex-1"></div>
                <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-tighter border border-blue-100" x-text="'v' + (document.version || '1.0')"></span>
            </div>
            <input type="text" x-model="document.title" :disabled="!isEditable()" class="w-full bg-transparent border-0 border-b-2 border-transparent focus:border-indigo-500 p-0 text-2xl font-black text-slate-800 placeholder-slate-300 focus:ring-0 transition-all hover:border-slate-200" placeholder="Judul Dokumen...">
        </div>

        <!-- Sidebar Form Content -->
        <div class="flex-1 overflow-y-auto custom-scrollbar bg-white">
            <div class="p-8 space-y-10">
                <fieldset :disabled="!isEditable()" class="space-y-10">
                    
                    <!-- Document ID -->
                    <div class="group">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 group-focus-within:text-indigo-500 transition-colors">Nomor Dokumen</label>
                        <input type="text" x-model="document.content_data.docNumber" class="form-input-styled font-mono text-base" placeholder=".../ND/I/2026">
                    </div>

                    <!-- Header Fields -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-3">
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest flex items-center gap-2"><span class="w-1.5 h-4 bg-indigo-500 rounded-full"></span> Header Informasi</h3>
                            <div class="h-px bg-slate-100 flex-1"></div>
                        </div>
                        
                        <div class="space-y-5">
                            <!-- Recipients -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest" x-text="document.type === 'perj' ? 'Pihak-pihak' : 'Tujuan (Kepada)'"></label>
                                    <button x-show="document.type === 'nota'" @click="addListItem('to')" class="text-[10px] text-indigo-600 font-black hover:underline uppercase tracking-tighter">+ TAMBAH PENERIMA</button>
                                </div>
                                <template x-if="document.type === 'nota'">
                                    <div class="space-y-3">
                                        <template x-for="(item, index) in document.content_data.to" :key="index">
                                            <div class="flex gap-3 items-center group/item">
                                                <div class="flex-1 relative">
                                                    <input type="text" x-model="document.content_data.to[index]" class="form-input-styled" placeholder="Yth. ...">
                                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-500 rounded-l-lg opacity-0 group-focus-within/item:opacity-100 transition-opacity"></div>
                                                </div>
                                                <button x-show="document.content_data.to.length > 1" @click="removeListItem('to', index)" class="p-2 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all focus:outline-none">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="document.type !== 'nota'">
                                    <input type="text" x-model="document.content_data.to" class="form-input-styled" placeholder="Nama Penerima / Pihak Kedua">
                                </template>
                            </div>

                            <!-- PLH/PJS -->
                            <div x-show="document.type === 'nota'" class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status Jabatan</label>
                                    <select x-model="document.content_data.plh_pjs" class="form-input-styled appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20fill%3D%27none%27%20viewBox%3D%270%200%2020%2020%27%3E%3Cpath%20stroke%3D%27%236B7280%27%20stroke-linecap%3D%27round%27%20stroke-linejoin%3D%27round%27%20stroke-width%3D%271.5%27%20d%3D%27m6%208%204%204%204-4%27%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem_1.25rem] bg-[right_0.5rem_center] bg-no-repeat pr-10">
                                        <option value="">REGULER</option>
                                        <option value="PLH.">PLH (PELAKSANA HARIAN)</option>
                                        <option value="PJS.">PJS (PEJABAT SEMENTARA)</option>
                                        <option value="AN.">A.N. (ATAS NAMA)</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pengirim (Dari)</label>
                                    <input type="text" x-model="document.content_data.from" class="form-input-styled" placeholder="Nama Pengirim">
                                </div>
                            </div>

                            <div x-show="document.type === 'nota'" class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Perihal Dokumen</label>
                                <textarea x-model="document.content_data.subject" class="form-textarea-styled leading-relaxed" rows="2" placeholder="Tuliskan perihal surat..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Main Body Fields -->
                    <div class="space-y-6 pt-4">
                        <div class="flex items-center gap-3">
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest flex items-center gap-2"><span class="w-1.5 h-4 bg-indigo-500 rounded-full"></span> Isi Dokumen</h3>
                            <div class="h-px bg-slate-100 flex-1"></div>
                        </div>

                        <!-- Basis / Mengingat -->
                        <div x-show="document.type === 'nota' || document.type === 'sppd'" class="space-y-4">
                            <div class="flex justify-between items-end">
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block" x-text="document.type === 'sppd' ? 'Mengingat (Regulasi)' : 'Dasar Pelaksanaan'"></label>
                                    <select x-model="document.content_data.basisStyle" class="text-[9px] font-bold border-slate-200 rounded bg-slate-50 px-2 py-1 focus:ring-0 outline-none">
                                        <option value="1.">ANGKA (1, 2, 3)</option>
                                        <option value="a.">HURUF KECIL (a, b, c)</option>
                                        <option value="A.">HURUF BESAR (A, B, C)</option>
                                        <option value="I.">ROMAWI (I, II, III)</option>
                                    </select>
                                </div>
                                <button @click="addListItem(document.type === 'sppd' ? 'remembers' : 'basis')" class="text-[10px] text-indigo-600 font-black hover:underline tracking-tighter uppercase">+ TAMBAH POIN</button>
                            </div>
                            <div class="space-y-3">
                                <template x-for="(item, index) in (document.type === 'sppd' ? document.content_data.remembers : document.content_data.basis)" :key="index">
                                    <div class="space-y-2 p-3 bg-slate-50 rounded-xl border border-slate-100 group/basis">
                                        <div class="flex gap-3 items-center">
                                            <div class="flex-1 relative">
                                                <input type="text" x-model="item.text" class="form-input-styled text-sm py-2 bg-white" placeholder="Tulis poin dasar...">
                                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-slate-300 rounded-l-lg group-focus-within/basis:bg-indigo-500 transition-colors"></div>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <button @click="addSubItem(document.type === 'sppd' ? 'remembers' : 'basis', index)" class="p-2 text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Tambah Sub-poin">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                                </button>
                                                <button @click="removeListItem(document.type === 'sppd' ? 'remembers' : 'basis', index)" class="p-2 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </div>
                                        </div>
                                        <!-- Sub-items -->
                                        <div class="pl-8 space-y-2 border-l-2 border-slate-100 ml-2" x-show="item.sub && item.sub.length > 0">
                                            <template x-for="(subItem, subIndex) in item.sub" :key="subIndex">
                                                <div class="flex gap-2 items-center">
                                                    <span class="text-[10px] font-bold text-slate-400" x-text="String.fromCharCode(97 + subIndex) + '.'"></span>
                                                    <input type="text" x-model="item.sub[subIndex]" class="form-input-styled py-1 text-xs bg-white" placeholder="Isi sub-poin...">
                                                    <button @click="removeSubItem(document.type === 'sppd' ? 'remembers' : 'basis', index, subIndex)" class="text-slate-300 hover:text-red-400 p-1">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- CKEditor Container -->
                        <div class="space-y-2 group">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest group-focus-within:text-indigo-500 transition-colors">Konten Utama (Editor)</label>
                            <div class="ck-editor-container border border-slate-200 rounded-2xl overflow-hidden focus-within:border-indigo-500 focus-within:ring-4 focus-within:ring-indigo-50 transition-all">
                                <textarea id="ck-editor"></textarea>
                            </div>
                        </div>

                        <!-- SPPD Dynamic Fields -->
                        <div x-show="document.type === 'sppd'" class="p-6 bg-slate-50 rounded-2xl border border-slate-200 space-y-5 shadow-inner">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Parameter Penugasan</p>
                            <div class="space-y-4">
                                <div><label class="text-[9px] font-black text-slate-500 mb-1 block">KEGIATAN</label><input type="text" x-model="document.content_data.task" class="form-input-styled bg-white border-slate-200" placeholder="Melaksanakan rapat..."></div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div><label class="text-[9px] font-black text-slate-500 mb-1 block">TUJUAN</label><input type="text" x-model="document.content_data.destination" class="form-input-styled bg-white border-slate-200" placeholder="Kota / Lokasi"></div>
                                    <div><label class="text-[9px] font-black text-slate-500 mb-1 block">TRANSPORT</label><input type="text" x-model="document.content_data.transport" class="form-input-styled bg-white border-slate-200" placeholder="Darat/Udara"></div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div><label class="text-[9px] font-black text-slate-500 mb-1 block">TANGGAL PERGI</label><input type="date" x-model="document.content_data.dateGo" class="form-input-styled bg-white border-slate-200"></div>
                                    <div><label class="text-[9px] font-black text-slate-500 mb-1 block">TANGGAL PULANG</label><input type="date" x-model="document.content_data.dateBack" class="form-input-styled bg-white border-slate-200"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Perjanjian Dynamic Fields -->
                        <div x-show="document.type === 'perj'" class="p-6 bg-slate-50 rounded-2xl border border-slate-200 space-y-5 shadow-inner">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Informasi Perjanjian</p>
                            <div class="space-y-4">
                                <div><label class="text-[9px] font-black text-slate-500 mb-1 block">TENTANG</label><textarea x-model="document.content_data.about" class="form-textarea-styled bg-white border-slate-200" rows="2"></textarea></div>
                                <div><label class="text-[9px] font-black text-slate-500 mb-1 block">NAMA PIHAK KEDUA</label><input type="text" x-model="document.content_data.party2Name" class="form-input-styled bg-white border-slate-200"></div>
                                <div><label class="text-[9px] font-black text-slate-500 mb-1 block">DETIL PIHAK KEDUA (ALAMAT/NIK)</label><textarea x-model="document.content_data.party2Info" class="form-textarea-styled bg-white border-slate-200" rows="3"></textarea></div>
                            </div>
                        </div>

                        <div x-show="document.type === 'nota'" class="group">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest group-focus-within:text-indigo-500 transition-colors mb-2">Kalimat Penutup</label>
                            <textarea x-model="document.content_data.closing" class="form-textarea-styled leading-relaxed" rows="2"></textarea>
                        </div>
                    </div>

                    <!-- Validation & Signatures -->
                    <div class="space-y-8 pt-4">
                        <div class="flex items-center gap-3">
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest flex items-center gap-2"><span class="w-1.5 h-4 bg-indigo-500 rounded-full"></span> Pengesahan & Paraf</h3>
                            <div class="h-px bg-slate-100 flex-1"></div>
                        </div>

                        <!-- BD-MLI -->
                        <div x-show="document.type === 'nota' || document.type === 'sppd'" class="p-6 bg-emerald-50/50 rounded-2xl border border-emerald-100 relative group/bd shadow-sm">
                            <div class="flex justify-between items-center mb-4">
                                <label class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Paraf BD-MLI (Mandatory)</label>
                                <template x-if="document.content_data.bdMliSignature">
                                    <button @click="removeSignature('bdMliSignature')" class="text-[9px] font-bold text-red-500 hover:underline">HAPUS PARAF</button>
                                </template>
                            </div>
                            <template x-if="!document.content_data.bdMliSignature">
                                <button @click="initSignaturePad('bdMliSignature', 'PARAF BD-MLI')" class="w-full py-4 border-2 border-dashed border-emerald-200 text-emerald-600 text-[10px] font-black rounded-xl bg-white hover:bg-emerald-50 hover:border-emerald-300 transition-all uppercase tracking-widest">BUAT PARAF SEKARANG</button>
                            </template>
                            <template x-if="document.content_data.bdMliSignature">
                                <div class="h-20 flex items-center justify-center bg-white rounded-xl border border-emerald-100 shadow-inner">
                                    <img :src="document.content_data.bdMliSignature" class="h-16 object-contain">
                                </div>
                            </template>
                        </div>

                        <!-- Signature Information -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2"><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Lokasi</label><input type="text" x-model="document.content_data.location" class="form-input-styled"></div>
                            <div class="space-y-2"><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tanggal</label><input type="date" x-model="document.content_data.date" class="form-input-styled"></div>
                        </div>
                        <div class="space-y-2"><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Jabatan Penandatangan</label><input type="text" x-model="document.content_data.signerPosition" class="form-input-styled" placeholder="Kepala Divisi..."></div>
                        <div class="space-y-2"><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama Lengkap Penandatangan</label><input type="text" x-model="document.content_data.signerName" class="form-input-styled font-black text-lg"></div>

                        <!-- Main Signature -->
                        <div class="p-6 bg-indigo-50/50 rounded-2xl border border-indigo-100 relative shadow-sm">
                            <div class="flex justify-between items-center mb-4">
                                <label class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Tanda Tangan Utama</label>
                                <template x-if="document.content_data.signature">
                                    <button @click="removeSignature('signature')" class="text-[9px] font-bold text-red-500 hover:underline">HAPUS TTD</button>
                                </template>
                            </div>
                            <template x-if="!document.content_data.signature">
                                <button @click="initSignaturePad('signature', 'TANDA TANGAN UTAMA')" class="w-full py-6 border-2 border-dashed border-indigo-200 text-indigo-600 text-[10px] font-black rounded-xl bg-white hover:bg-indigo-50 hover:border-indigo-300 transition-all uppercase tracking-widest shadow-sm">BUAT TANDA TANGAN UTAMA</button>
                            </template>
                            <template x-if="document.content_data.signature">
                                <div class="h-32 flex items-center justify-center bg-white rounded-xl border border-indigo-100 shadow-inner">
                                    <img :src="document.content_data.signature" class="h-24 object-contain">
                                </div>
                            </template>
                        </div>

                        <!-- Paraf Table (Dinamis) -->
                        <div class="pt-6 border-t border-slate-100 space-y-6">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 class="text-[10px] font-black text-slate-800 uppercase tracking-widest">Tabel Paraf Tambahan</h4>
                                    <p class="text-[9px] text-slate-400 mt-0.5 uppercase font-bold tracking-tighter">Tambahkan paraf divisi terkait</p>
                                </div>
                                <button @click="document.content_data.paraf.push({code: '', name: '', signature: ''})" class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-[9px] font-black hover:bg-indigo-600 hover:text-white transition-all uppercase tracking-widest">+ TAMBAH BARIS</button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <template x-for="(item, index) in document.content_data.paraf" :key="index">
                                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 relative group/paraf shadow-sm hover:shadow-md transition-shadow">
                                        <button @click="document.content_data.paraf.splice(index, 1)" class="absolute -right-2 -top-2 w-6 h-6 bg-white text-red-500 rounded-full shadow-md border border-red-50 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all z-10 opacity-0 group-hover/paraf:opacity-100">×</button>
                                        <div class="grid grid-cols-2 gap-2 mb-3">
                                            <input type="text" x-model="item.code" placeholder="KODE" class="form-input-styled bg-white border-slate-200 py-1 text-[9px] font-black">
                                            <input type="text" x-model="item.name" placeholder="NAMA" class="form-input-styled bg-white border-slate-200 py-1 text-[9px] font-black">
                                        </div>
                                        <template x-if="!item.signature">
                                            <button @click="initSignaturePad('paraf.' + index, 'PARAF: ' + (item.code || 'BARU'))" class="w-full py-2 border-2 border-dashed border-slate-200 text-slate-400 text-[9px] font-black rounded-lg bg-white hover:border-indigo-300 hover:text-indigo-500 transition-all uppercase tracking-widest">ISI PARAF</button>
                                        </template>
                                        <template x-if="item.signature">
                                            <div class="relative h-12 flex items-center justify-center bg-white rounded-lg border border-slate-100 shadow-inner group/sig">
                                                <img :src="item.signature" class="h-8 object-contain">
                                                <button @click="removeSignature('paraf.' + index)" class="absolute inset-0 opacity-0 group-hover/sig:opacity-100 flex items-center justify-center bg-white/90 text-[8px] text-red-500 font-black tracking-widest uppercase transition-all rounded-lg">HAPUS PARAF</button>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <!-- Sidebar Bottom Actions Area (Reviewer / Send) -->
                <div class="mt-4 pt-10 border-t border-gray-100 space-y-6">
                    <!-- Reviewer Control Block -->
                    <template x-if="currentUser?.role === 'reviewer' && document.id">
                        <div class="bg-amber-50 p-6 rounded-3xl border border-amber-100 space-y-4 shadow-sm">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg></div>
                                <h4 class="text-xs font-black text-amber-800 uppercase tracking-widest">Reviewer Panel</h4>
                            </div>
                            <textarea x-model="document.feedback" rows="3" class="w-full p-4 border border-amber-200 rounded-2xl text-sm font-medium bg-white focus:ring-4 focus:ring-amber-50 focus:border-amber-400 transition-all placeholder:text-amber-200" placeholder="Tulis catatan atau arahan revisi disini..."></textarea>
                            <div class="grid grid-cols-2 gap-3">
                                <button @click="updateStatus('needs_revision')" class="py-3 bg-white text-amber-600 border border-amber-200 rounded-xl font-black text-[10px] tracking-widest uppercase hover:bg-amber-50 transition-all shadow-sm">MINTA REVISI</button>
                                <button @click="updateStatus('approved')" class="py-3 bg-emerald-600 text-white rounded-xl font-black text-[10px] tracking-widest uppercase hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100">SETUJUI (ACC)</button>
                            </div>
                        </div>
                    </template>

                    <!-- Staff Action Block -->
                    <div class="space-y-3">
                        <template x-if="currentUser?.role === 'user' && (document.status === 'draft' || document.status === 'needs_revision')">
                            <button @click="showSendModal = true" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 group flex items-center justify-center gap-3">
                                <span>KIRIM DOKUMEN</span>
                                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </button>
                        </template>

                        <template x-if="currentUser?.role === 'user' && (document.status === 'received' || document.status === 'sent')">
                            <div class="grid grid-cols-2 gap-3">
                                <button @click="showSendModal = true" class="py-3 bg-emerald-600 text-white rounded-xl font-black text-[10px] tracking-widest uppercase hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100">TERUSKAN</button>
                                <button @click="finishDocument()" class="py-3 bg-slate-800 text-white rounded-xl font-black text-[10px] tracking-widest uppercase hover:bg-slate-900 transition-all shadow-lg shadow-slate-200">SELESAI / ACC</button>
                            </div>
                        </template>

                        <button @click="downloadPDF()" class="w-full bg-white text-slate-700 border-2 border-slate-100 py-4 rounded-2xl font-black uppercase tracking-widest flex justify-center items-center gap-3 hover:bg-slate-50 transition-all hover:border-slate-200 group">
                            <svg class="w-5 h-5 text-red-500 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                            <span>Download PDF</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT PREVIEW AREA -->
    <div class="flex-1 overflow-y-auto p-12 flex justify-center bg-slate-200 print:p-0 print:bg-white custom-scrollbar">
        <div id="paperContent" class="paper shadow-lg relative min-h-[297mm]">
            <div class="flex items-center mb-2">
                <img src="/images/logo_asa.png" alt="ASABRI Logo" class="h-16 mb-2">
            </div>

            <!-- PREVIEW: NOTA DINAS -->
            <template x-if="document.type === 'nota'">
                <div>
                    <div class="paper-header">
                        <h1 class="font-bold text-lg uppercase">NOTA DINAS</h1>
                        <p>NOMOR <span x-text="document.content_data.docNumber || '...'"></span></p>
                    </div>
                    <table class="info-table w-full mb-6">
                        <tr><td width="100">Kepada</td><td width="20">:</td><td>
                            <template x-for="(to, i) in document.content_data.to" :key="i">
                                <div class="flex items-start"><span x-show="document.content_data.to.length > 1" x-text="(i+1)+'. '" class="mr-1 font-bold"></span><span x-text="to || '...'"></span></div>
                            </template>
                        </td></tr>
                        <tr><td>Dari</td><td>:</td><td><span x-show="document.content_data.plh_pjs" x-text="document.content_data.plh_pjs + ' '" class="font-bold"></span><span x-text="document.content_data.from || '...'"></span></td></tr>
                        <tr><td>Hal</td><td>:</td><td class="font-bold"><span x-text="document.content_data.subject || '...'"></span></td></tr>
                    </table>
                    <div class="mb-4">
                        <p class="mb-2">Berdasarkan:</p>
                        <ul class="list-none text-justify p-0 m-0">
                            <template x-for="(item, i) in document.content_data.basis">
                                <li class="mb-4">
                                    <div class="flex gap-2 items-start">
                                        <span class="w-8 shrink-0 font-bold text-center" x-text="formatNumbering(i, document.content_data.basisStyle || '1.')"></span>
                                        <span class="flex-1 break-words" x-text="item.text"></span>
                                    </div>
                                    <!-- Sub-poin Preview -->
                                    <ul x-show="item.sub && item.sub.length > 0" class="list-none mt-1 ml-8 space-y-1">
                                        <template x-for="(sub, si) in item.sub" :key="si">
                                            <li class="flex gap-2 items-start text-sm">
                                                <span class="w-6 shrink-0" x-text="String.fromCharCode(97 + si) + '.'"></span>
                                                <span class="flex-1 break-words text-justify" x-text="sub"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </li>
                            </template>
                        </ul>
                    </div>
                    <div class="mb-8 text-justify leading-relaxed"><div x-html="document.content_data.content || '...'"></div></div>
                    <p class="mb-8 font-medium" x-text="document.content_data.closing"></p>
                    <div class="signature-section">
                        <p class="mb-1"><span x-text="document.content_data.location"></span>, <span x-text="formatDate(document.content_data.date)"></span></p>
                        <p class="font-bold uppercase mb-0"><span x-show="document.content_data.plh_pjs" x-text="document.content_data.plh_pjs + ' '"></span><span x-text="document.content_data.signerPosition"></span></p>
                        <div class="h-24 w-full flex items-center justify-center">
                            <template x-if="document.content_data.signature"><img :src="document.content_data.signature" class="h-24 object-contain"></template>
                        </div>
                        <p class="font-bold uppercase underline"><span x-text="document.content_data.signerName"></span></p>
                    </div>
                    <div class="clear-both"></div>
                    <!-- Paraf Tables -->
                    <div class="mt-8 flex flex-col gap-4 no-break">
                        <div class="paraf-container">
                            <table class="paraf-table">
                                <tr><td rowspan="3" class="col-paraf-label">Paraf</td><template x-for="p in [...document.content_data.paraf].reverse()"><td class="cell-width" x-text="p.code"></td></template></tr>
                                <tr class="row-name"><template x-for="p in [...document.content_data.paraf].reverse()"><td x-text="p.name"></td></template></tr>
                                <tr class="row-signature"><template x-for="p in [...document.content_data.paraf].reverse()"><td class="h-16 align-middle"><img x-show="p.signature" :src="p.signature" class="max-h-12 mx-auto"></td></template></tr>
                            </table>
                        </div>
                        <table class="paraf-box">
                            <tr><td colspan="2" class="text-center font-bold bg-slate-100">BD-MLI</td></tr>
                            <tr><td rowspan="2" class="text-center" width="50%">Paraf</td><td class="text-center">Staff</td></tr>
                            <tr><td class="h-10 text-center align-middle"><img x-show="document.content_data.bdMliSignature" :src="document.content_data.bdMliSignature" class="max-h-8 mx-auto"></td></tr>
                        </table>
                    </div>
                </div>
            </template>

            <!-- PREVIEW: SPPD -->
            <template x-if="document.type === 'sppd'">
                <div>
                    <div class="paper-header"><h1 class="font-bold text-lg uppercase">SURAT PERINTAH PERJALANAN DINAS</h1><p>NOMOR <span x-text="document.content_data.docNumber || '...'"></span></p></div>
                    <table class="sppd-table">
                        <tr><td class="sppd-label">Menimbang</td><td class="sppd-colon">:</td><td x-text="document.content_data.weigh"></td></tr>
                        <tr><td class="sppd-label">Mengingat</td><td class="sppd-colon">:</td><td>
                            <ul class="list-none p-0 m-0">
                                <template x-for="(item, i) in document.content_data.remembers">
                                    <li class="mb-4">
                                        <div class="flex gap-2 items-start">
                                            <span class="w-8 shrink-0 font-bold" x-text="formatNumbering(i, document.content_data.basisStyle || '1.')"></span>
                                            <span class="flex-1 break-words text-justify" x-text="item.text"></span>
                                        </div>
                                        <ul x-show="item.sub && item.sub.length > 0" class="list-none mt-1 ml-8 space-y-1">
                                            <template x-for="(sub, si) in item.sub" :key="si">
                                                <li class="flex gap-2 items-start text-sm">
                                                    <span class="w-6 shrink-0" x-text="String.fromCharCode(97 + si) + '.'"></span>
                                                    <span class="flex-1 break-words text-justify" x-text="sub"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </li>
                                </template>
                            </ul>
                        </td></tr>
                    </table>
                    <div class="text-center font-bold my-6 uppercase">Memberi Perintah</div>
                    <table class="sppd-table"><tr><td class="sppd-label">Kepada</td><td class="sppd-colon"></td><td class="font-bold" x-text="document.content_data.to"></td></tr></table>
                    <div class="mt-4">
                        <table class="sppd-table">
                            <tr>
                                <td class="sppd-label">Untuk</td>
                                <td class="sppd-colon">:</td>
                                <td>
                                    <ol class="list-decimal pl-5 space-y-2">
                                        <li><span x-text="document.content_data.task"></span></li>
                                        <li>
                                            Perjalanan dinas dilaksanakan, sebagai berikut:
                                            <table class="w-full mt-1 border-none">
                                                <tr><td width="100">Tujuan</td><td width="10">:</td><td x-text="document.content_data.destination"></td></tr>
                                                <tr><td>Berangkat</td><td>:</td><td x-text="formatDate(document.content_data.dateGo)"></td></tr>
                                                <tr><td>Kembali</td><td>:</td><td x-text="formatDate(document.content_data.dateBack)"></td></tr>
                                                <tr><td>Transport</td><td>:</td><td x-text="document.content_data.transport"></td></tr>
                                            </table>
                                        </li>
                                        <li><div x-html="document.content_data.content"></div></li>
                                    </ol>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="signature-section">
                        <p class="mb-1">Dikeluarkan di <span x-text="document.content_data.location"></span> pada <span x-text="formatDate(document.content_data.date)"></span></p>
                        <p class="font-bold uppercase mb-0" x-text="document.content_data.signerPosition"></p>
                        <div class="h-24"><template x-if="document.content_data.signature"><img :src="document.content_data.signature" class="h-24 mx-auto"></template></div>
                        <p class="font-bold underline uppercase" x-text="document.content_data.signerName"></p>
                    </div>
                </div>
            </template>

            <!-- PREVIEW: PERJANJIAN -->
            <template x-if="document.type === 'perj'">
                <div class="text-justify font-serif text-[11pt] leading-normal">
                    <div class="text-center font-bold mb-8 uppercase leading-tight">
                        <p class="m-0">PERJANJIAN KERJA SAMA</p>
                        <p class="m-0">ANTARA</p>
                        <p class="m-0">PT ASABRI (PERSERO)</p>
                        <p class="m-0">DENGAN</p>
                        <p x-text="document.content_data.party2Name || '...'" class="m-0"></p>
                        <p class="m-0">TENTANG</p>
                        <p x-text="document.content_data.about || '...'" class="m-0"></p>
                        <p class="m-0">NOMOR: <span x-text="document.content_data.docNumber || '...'"></span></p>
                    </div>

                    <p class="mb-4">
                        Pada hari ini <span x-text="document.content_data.day || '...'" class="font-bold"></span>, 
                        tanggal <span x-text="document.content_data.dateWritten || '...'" class="font-bold"></span> 
                        bertempat di <span x-text="document.content_data.location || '...'"></span>, 
                        kami yang bertanda tangan di bawah ini:
                    </p>

                    <div class="flex mb-4 items-start">
                        <div class="w-8 flex-shrink-0 font-bold">1.</div>
                        <div class="flex-grow">
                            <span class="font-bold">PT ASABRI (Persero)</span>, 
                            suatu Perseroan Terbatas yang didirikan berdasarkan Hukum Negara Republik Indonesia, 
                            yang berkedudukan di Jalan Mayjen Sutoyo Nomor 11 Jakarta Timur, dalam hal ini diwakili oleh 
                            <span x-text="document.content_data.signerName || '...'" class="font-bold"></span> 
                            dalam jabatannya selaku <span x-text="document.content_data.signerPosition || '...'" class="font-bold"></span> 
                            <span x-text="document.content_data.party1Auth || '...'"></span>, 
                            untuk selanjutnya disebut <span class="font-bold">"Pihak Kesatu"</span>; dan
                        </div>
                    </div>

                    <div class="flex mb-4 items-start">
                        <div class="w-8 flex-shrink-0 font-bold">2.</div>
                        <div class="flex-grow">
                            <span x-text="document.content_data.party2Name || '...'" class="font-bold"></span>, 
                            <span x-text="document.content_data.party2Info || '...'"></span>, 
                            dan untuk selanjutnya disebut <span class="font-bold">"Pihak Kedua"</span>.
                        </div>
                    </div>

                    <p class="mb-4">Pihak Kesatu dan Pihak Kedua selanjutnya secara bersama-sama disebut sebagai <span class="font-bold">"Para Pihak"</span> dan masing-masing disebut <span class="font-bold">"Pihak"</span>, serta dalam kedudukannya sebagaimana tersebut di atas, terlebih dulu menerangkan hal-hal sebagai berikut:</p>

                    <div class="leading-relaxed mb-8" x-html="document.content_data.content"></div>

                    <div class="mt-12 flex justify-between text-center gap-8">
                        <div class="flex-1">
                            <p class="font-bold uppercase">PIHAK KESATU</p>
                            <div class="h-24 w-full flex items-center justify-center">
                                <template x-if="document.content_data.signature"><img :src="document.content_data.signature" class="h-24 object-contain"></template>
                            </div>
                            <p class="font-bold underline uppercase" x-text="document.content_data.signerName"></p>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold uppercase">PIHAK KEDUA</p>
                            <div class="h-24"></div>
                            <p class="font-bold underline uppercase" x-text="document.content_data.party2Name"></p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    .form-input-styled { 
        width: 100%; 
        padding: 0.75rem 1rem; 
        background: #f8fafc; 
        border: 2px solid transparent; 
        border-radius: 1rem; 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-weight: 600;
        color: #1e293b;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    .form-input-styled:focus { 
        background: #fff; 
        border-color: #6366f1; 
        outline: none; 
        box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.1), 0 4px 6px -2px rgba(99, 102, 241, 0.05);
        transform: translateY(-1px);
    }
    .form-textarea-styled { 
        width: 100%; 
        padding: 1rem; 
        background: #f8fafc; 
        border: 2px solid transparent; 
        border-radius: 1rem; 
        font-size: 0.875rem; 
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .form-textarea-styled:focus {
        background: #fff;
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.1);
    }
    .ck-editor__editable_inline { min-height: 300px; padding: 0 1.5rem !important; font-family: inherit; font-size: 0.95rem; }
    .ck.ck-editor__main>.ck-editor__editable:not(.ck-focused) { border-color: transparent; }
    .ck.ck-editor__top .ck-sticky-panel .ck-toolbar { border-radius: 1rem 1rem 0 0; border: none; background: #f1f5f9; padding: 0.5rem; }
    
    .fade-in { animation: fadeIn 0.5s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
@endpush
