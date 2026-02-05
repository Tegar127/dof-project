@extends('layouts.app')

@section('title', 'Editor - DOF')

@section('content')
<div class="flex flex-col lg:flex-row h-screen overflow-hidden bg-gray-100" x-data="editorApp()" x-init="init()">
    
    <!-- History Modal -->
    <div x-show="showHistoryModal" x-cloak class="fixed inset-0 z-[80] bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showHistoryModal = false" class="bg-white rounded-xl shadow-lg max-w-2xl w-full flex flex-col max-h-[90vh]">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-800">Riwayat Dokumen</h3>
                <button @click="showHistoryModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            
                                    <!-- Tabs -->
            
                                    <div class="flex border-b border-gray-100 px-4">
            
                                        <button @click="activeHistoryTab = 'status'" :class="activeHistoryTab === 'status' ? 'text-indigo-600 border-indigo-600' : 'text-slate-500 border-transparent hover:text-slate-700'" class="px-4 py-3 font-medium text-sm border-b-2 transition-colors">
            
                                            Riwayat Status
            
                                        </button>
            
                                        <button @click="activeHistoryTab = 'work'" :class="activeHistoryTab === 'work' ? 'text-indigo-600 border-indigo-600' : 'text-slate-500 border-transparent hover:text-slate-700'" class="px-4 py-3 font-medium text-sm border-b-2 transition-colors">
            
                                            Log Pengerjaan
            
                                        </button>
            
                                    </div>
            
                        
            
                                    <!-- Content -->
            
                                    <div class="p-4 overflow-y-auto flex-1 bg-slate-50">
            
                                        
            
                                        <!-- Tab: Status -->
            
                                        <div x-show="activeHistoryTab === 'status'" class="space-y-4">
            
                                             <template x-if="loadingLogs">
            
                                                <div class="text-center py-4 text-slate-500">Memuat data...</div>
            
                                            </template>
            
                                            <template x-if="!loadingLogs && logs.length === 0">
            
                                                <div class="text-center py-4 text-slate-500">Belum ada riwayat status.</div>
            
                                            </template>
            
                                            <div class="relative pl-4 border-l-2 border-indigo-100 space-y-6">
            
                                                <template x-for="(log, index) in logs" :key="index">
            
                                                    <div class="relative">
            
                                                        <div class="absolute -left-[21px] top-1 w-4 h-4 rounded-full border-2 border-white" 
            
                                                             :class="index === 0 ? 'bg-indigo-500 ring-4 ring-indigo-50' : 'bg-slate-300'"></div>
            
                                                        
            
                                                        <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-sm">
            
                                                            <div class="flex justify-between items-start mb-1">
            
                                                                <span class="text-xs font-bold text-slate-700" x-text="log.action.toUpperCase().replace('_', ' ')"></span>
            
                                                                <span class="text-[10px] text-slate-400" x-text="formatDeadlineDisplay(log.created_at)"></span>
            
                                                            </div>
            
                                                                                                <p class="text-sm text-slate-600 mb-1" x-text="log.notes"></p>
            
                                                                                                
            
                                                                                                <!-- Changes Detail -->
            
                                                                                                <template x-if="log.changes && log.changes !== 'Penyimpanan otomatis.'">
            
                                                                <div class="mt-2 p-3 bg-blue-50/50 border border-blue-100 rounded-lg">
            
                                                                    <div class="text-xs font-bold text-blue-700 mb-1 flex items-center gap-1">
            
                                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            
                                                                        Detail Perubahan
            
                                                                    </div>
            
                                                                    <p class="text-xs text-slate-600 whitespace-pre-line leading-relaxed italic" x-text="log.changes"></p>
            
                                                                </div>
            
                                                            </template>
            
                        
            
                                                            <div class="flex items-center gap-2 mt-2 pt-2 border-t border-slate-50">
            
                                                                <div class="w-5 h-5 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500">
            
                                                                    <span x-text="log.user_name ? log.user_name.charAt(0) : 'S'"></span>
            
                                                                </div>
            
                                                                <span class="text-xs text-slate-500" x-text="log.user_name"></span>
            
                                                            </div>
            
                                                        </div>
            
                                                    </div>
            
                                                </template>
            
                                            </div>
            
                                        </div>
            
                        
            
                                        <!-- Tab: Work Logs -->                <div x-show="activeHistoryTab === 'work'" class="space-y-4">
                     <template x-if="loadingWorkLogs">
                        <div class="text-center py-4 text-slate-500">Memuat data...</div>
                    </template>
                    <template x-if="!loadingWorkLogs && workLogs.length === 0">
                        <div class="text-center py-4 text-slate-500">Belum ada log pengerjaan.</div>
                    </template>
                    
                    <div class="space-y-3">
                         <template x-for="log in workLogs" :key="log.id">
                            <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-sm flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between mb-1">
                                        <span class="text-xs font-bold text-slate-700" x-text="log.user ? log.user.name : 'User'"></span>
                                        <span class="text-xs font-mono bg-slate-100 px-1.5 py-0.5 rounded" x-text="log.duration_minutes + ' mnt'"></span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 flex gap-2">
                                        <span x-text="formatDeadlineDisplay(log.start_time)"></span>
                                        <span>-</span>
                                        <span x-text="formatDeadlineDisplay(log.end_time).split(' ').slice(-1)[0]"></span>
                                    </div>
                                </div>
                            </div>
                         </template>
                    </div>
                    
                    <!-- Total Duration Summary -->
                     <template x-if="workLogs.length > 0">
                        <div class="mt-4 p-3 bg-indigo-50 rounded-lg border border-indigo-100 flex justify-between items-center">
                            <span class="text-sm font-bold text-indigo-800">Total Waktu Pengerjaan</span>
                            <span class="text-sm font-bold text-indigo-600" x-text="workLogs.reduce((acc, curr) => acc + curr.duration_minutes, 0) + ' Menit'"></span>
                        </div>
                    </template>
                </div>

            </div>
        </div>
    </div>

    <!-- Signature Modal -->
    <div x-show="showSignatureModal" x-cloak class="fixed inset-0 z-[80] bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showSignatureModal = false" class="bg-white rounded-xl shadow-lg max-w-lg w-full p-6">
            <h3 class="text-xl font-bold mb-1 text-slate-800">Tanda Tangan Digital</h3>
            <p class="text-xs text-indigo-600 font-bold mb-4 uppercase tracking-wider" x-text="'Untuk: ' + activeSignatureLabel"></p>

            <!-- Tabs -->
            <div class="flex border-b border-gray-200 mb-4">
                <button 
                    @click="signatureTab = 'draw'"
                    :class="{'border-b-2 border-indigo-600 text-indigo-600': signatureTab === 'draw', 'text-gray-500 hover:text-gray-700': signatureTab !== 'draw'}"
                    class="flex-1 py-2 text-sm font-medium transition-colors focus:outline-none"
                >
                    Gambar Manual
                </button>
                <button 
                    @click="signatureTab = 'upload'"
                    :class="{'border-b-2 border-indigo-600 text-indigo-600': signatureTab === 'upload', 'text-gray-500 hover:text-gray-700': signatureTab !== 'upload'}"
                    class="flex-1 py-2 text-sm font-medium transition-colors focus:outline-none"
                >
                    Upload Gambar
                </button>
            </div>
            
            <div x-show="signatureTab === 'draw'" class="border-2 border-dashed border-gray-300 rounded-lg bg-gray-50 relative h-64 w-full mb-4 overflow-hidden touch-none">
                <canvas id="signature-canvas" class="absolute inset-0 w-full h-full cursor-crosshair"></canvas>
                <div class="absolute bottom-2 right-2 text-xs text-gray-400 pointer-events-none">Area Tanda Tangan</div>
            </div>

            <div x-show="signatureTab === 'upload'" class="border-2 border-dashed border-gray-300 rounded-lg bg-gray-50 relative h-64 w-full mb-4 flex flex-col items-center justify-center p-4">
                <template x-if="!uploadedSignatureData">
                    <div class="text-center w-full">
                        <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <p class="text-sm text-gray-500 mb-4">Upload gambar tanda tangan (PNG/JPG)</p>
                        <input type="file" id="signature-upload-input" @change="handleSignatureUpload($event)" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer text-center mx-auto">
                    </div>
                </template>
                <template x-if="uploadedSignatureData">
                    <div class="relative w-full h-full flex items-center justify-center group">
                         <img :src="uploadedSignatureData" class="max-w-full max-h-full object-contain">
                         <div class="absolute inset-0 bg-black/10 hidden group-hover:flex items-center justify-center">
                             <button @click="uploadedSignatureData = null; document.getElementById('signature-upload-input').value = ''" class="bg-white text-red-500 p-2 rounded-full shadow-lg hover:bg-red-50 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                             </button>
                         </div>
                    </div>
                </template>
            </div>

            <div class="flex gap-3">
                <button 
                    @click="clearSignature()" 
                    class="px-4 py-2.5 text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 rounded-lg font-medium transition-colors"
                >
                    Hapus
                </button>
                <div class="flex-1"></div>
                <button 
                    @click="showSignatureModal = false" 
                    class="px-4 py-2.5 text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors"
                >
                    Batal
                </button>
                <button 
                    @click="saveSignature()" 
                    class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors shadow-sm"
                >
                    Simpan Tanda Tangan
                </button>
            </div>
        </div>
    </div>

    <!-- Read Only Modal -->
    <div x-show="showReadOnlyModal" x-cloak class="fixed inset-0 z-[60] bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showReadOnlyModal = false" class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6 text-center">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 text-blue-600">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2 text-slate-800">Mode Baca-Saja</h3>
            <p class="text-gray-500 text-sm mb-6">Dokumen ini sedang diproses atau telah disetujui. Anda tidak dapat melakukan perubahan pada konten saat ini.</p>
            <button 
                @click="showReadOnlyModal = false" 
                class="w-full py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors shadow-sm"
            >
                Mengerti
            </button>
        </div>
    </div>

    <!-- Generic Success/Alert Modal -->
    <div x-show="showSuccessModal" x-cloak class="fixed inset-0 z-[70] bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showSuccessModal = false" class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6 text-center">
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

    <!-- Generic Confirmation Modal -->
    <div x-show="showConfirmModal" x-cloak class="fixed inset-0 z-[70] bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showConfirmModal = false" class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6 text-center">
            <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4 text-amber-600">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2 text-slate-800" x-text="confirmTitle || 'Konfirmasi'"></h3>
            <p class="text-gray-500 text-sm mb-6" x-text="confirmMessage"></p>
            <div class="flex gap-3">
                <button 
                    @click="showConfirmModal = false" 
                    class="flex-1 py-2.5 text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors"
                >
                    Batal
                </button>
                <button 
                    @click="confirmCallback()" 
                    class="flex-1 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors shadow-sm"
                >
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>

    <!-- Send Document Modal -->
    <div x-show="showSendModal" x-cloak class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showSendModal = false" class="bg-white rounded-xl shadow-lg max-w-md w-full p-6">
            <h3 class="text-xl font-bold mb-2 text-slate-800">Kirim Dokumen</h3>
            <p class="text-gray-500 text-sm mb-6">Pilih tujuan pengiriman dokumen ini.</p>

            <div class="space-y-4">
                <div class="space-y-2">
                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors" :class="document.target_role === 'group' ? 'border-indigo-500 bg-indigo-50/50' : ''">
                        <input type="radio" name="target" value="group" x-model="document.target_role" class="text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Group / Divisi</span>
                            <span class="block text-xs text-slate-500">Kirim ke divisi terkait untuk diproses.</span>
                        </div>
                    </label>
                    
                    <div x-show="document.target_role === 'group'" class="pl-8">
                        <select x-model="document.target_value" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Pilih Group --</option>
                            <template x-for="group in groups" :key="group.id">
                                <option :value="group.name" x-text="group.name"></option>
                            </template>
                        </select>
                    </div>

                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors" :class="document.target_role === 'dispo' ? 'border-indigo-500 bg-indigo-50/50' : ''">
                        <input type="radio" name="target" value="dispo" x-model="document.target_role" class="text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Disposisi (Reviewer)</span>
                            <span class="block text-xs text-slate-500">Kirim ke reviewer untuk diperiksa.</span>
                        </div>
                    </label>
                </div>

                <div class="flex gap-3 pt-4">
                    <button
                        @click="showSendModal = false"
                        class="flex-1 py-2.5 text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        @click="confirmSend()"
                        class="flex-1 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors shadow-sm flex justify-center items-center gap-2"
                    >
                        <span>Kirim Sekarang</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .form-input-styled {
            width: 100%;
            padding: 0.5rem 1rem;
            background-color: #f8fafc; /* bg-slate-50 */
            border: 1px solid #e2e8f0; /* border-slate-200 */
            border-radius: 0.5rem;
            font-size: 0.875rem;
            color: #1e293b; /* text-slate-800 */
            transition: all 0.2s;
        }
        .form-input-styled:focus {
            background-color: #ffffff;
            border-color: #6366f1; /* indigo-500 */
            outline: none;
            box-shadow: 0 0 0 3px rgba(199, 210, 254, 0.5); /* ring-indigo-200 */
        }
        .form-textarea-styled {
            width: 100%;
            padding: 0.5rem 1rem;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            color: #1e293b;
            transition: all 0.2s;
        }
        .form-textarea-styled:focus {
            background-color: #ffffff;
            border-color: #6366f1;
            outline: none;
            box-shadow: 0 0 0 3px rgba(199, 210, 254, 0.5);
        }
    </style>
    <!-- Left Sidebar: Input Form -->
    <div class="w-full lg:w-[400px] xl:w-[450px] bg-white flex flex-col border-r border-gray-200 shadow-xl z-10 h-full flex-shrink-0 font-sans">
        
        <!-- Toolbar -->
        <div class="px-6 py-4 bg-white border-b border-gray-100 flex justify-between items-center sticky top-0 z-20">
            <a href="/dashboard" class="flex items-center text-slate-500 hover:text-slate-800 text-sm font-medium gap-2 transition-colors">
                <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </div>
                <span>Kembali</span>
            </a>
            
            <div class="flex items-center gap-3">
                <button 
                    @click="openHistoryModal()" 
                    class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                    title="Riwayat & Versi"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>

                <span x-show="saving" class="text-xs text-slate-400">Menyimpan...</span>
                <button 
                    @click="saveDocument()" 
                    :disabled="saving || !isEditable()"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 active:bg-indigo-800 transition-all shadow-sm hover:shadow flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    <span>Simpan</span>
                </button>
            </div>
        </div>

        <!-- Document Info Header -->
        <div class="px-6 py-5 bg-slate-50/50 border-b border-gray-100 space-y-3">
             <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider border" 
                      :class="{
                        'bg-indigo-50 text-indigo-600 border-indigo-100': document.type === 'nota',
                        'bg-emerald-50 text-emerald-600 border-emerald-100': document.type === 'sppd',
                        'bg-amber-50 text-amber-600 border-amber-100': document.type === 'perj'
                      }"
                      x-text="document.type === 'nota' ? 'Nota Dinas' : (document.type === 'sppd' ? 'SPPD' : 'Perjanjian')"></span>
                
                <div class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-500 border border-slate-200" 
                     x-text="getStatusLabel(document.status)"></div>

                <div class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-600 border border-blue-100" 
                     x-text="'v' + (document.version || '1.0')"></div>
            </div>

            <div class="relative group">
                <label for="doc-title" class="sr-only">Judul Dokumen</label>
                <input 
                    id="doc-title"
                    type="text" 
                    x-model="document.title" 
                    :disabled="!isEditable()" 
                    class="w-full bg-transparent border-0 border-b-2 border-transparent hover:border-gray-200 focus:border-indigo-500 p-0 py-1 text-lg font-bold text-slate-800 placeholder-slate-300 focus:ring-0 transition-all disabled:text-slate-500" 
                    placeholder="Judul Dokumen (Klik untuk edit)"
                >
                <div class="absolute right-0 top-1/2 -translate-y-1/2 text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Scrollable Form Area -->
        <div class="flex-1 overflow-y-auto custom-scrollbar">
            <div class="p-6 space-y-6">

                <!-- Notifications / Alerts -->
                <template x-if="document.status === 'needs_revision' && currentUser?.role === 'user' && document.author_id == currentUser.id">
                    <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 flex gap-4 shadow-sm">
                        <div class="text-amber-500 shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-amber-900">Perlu Revisi</h4>
                            <p class="text-xs text-amber-700 mt-1 leading-relaxed" x-text="document.feedback || 'Mohon periksa kembali dokumen Anda sesuai arahan.'"></p>
                        </div>
                    </div>
                </template>

                 <template x-if="!isEditable() && currentUser?.role === 'user'">
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex gap-4">
                        <div class="text-slate-400 shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                         <div>
                            <h4 class="text-sm font-bold text-slate-700">Mode Baca-Saja</h4>
                            <p class="text-xs text-slate-500 mt-1">Dokumen sedang diproses. Anda tidak dapat mengedit saat ini.</p>
                        </div>
                    </div>
                </template>

                <!-- Main Form Fields -->
                <fieldset :disabled="!isEditable()" class="space-y-6">
                    
                    <!-- Common Field -->
                    <div class="group">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2 pl-1">Nomor Dokumen</label>
                        <input type="text" x-model="document.content_data.docNumber" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all placeholder:text-slate-300" placeholder="Contoh: 001/ND/I/2026">
                    </div>

                    <div class="w-full h-px bg-slate-100 my-6"></div>

                    <!-- NOTA DINAS Fields -->
                    <template x-if="document.type === 'nota'">
                        <div class="space-y-6">
                            
                            <!-- Section: Header Info -->
                            <div class="space-y-4">
                                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                    <span class="w-1 h-4 bg-indigo-500 rounded-full"></span>
                                    Informasi Surat
                                </h3>
                                
                                <div class="grid gap-4">
                                    <div class="space-y-1">
                                        <label class="text-xs text-slate-500 font-medium ml-1">Kepada</label>
                                        <input type="text" x-model="document.content_data.to" class="form-input-styled" placeholder="Yth. ...">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs text-slate-500 font-medium ml-1">Dari</label>
                                        <input type="text" x-model="document.content_data.from" class="form-input-styled" placeholder="Nama Pengirim">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs text-slate-500 font-medium ml-1">Lampiran</label>
                                        <input type="text" x-model="document.content_data.attachment" class="form-input-styled" placeholder="-">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs text-slate-500 font-medium ml-1">Perihal</label>
                                        <textarea x-model="document.content_data.subject" rows="2" class="form-textarea-styled" placeholder="Isi perihal surat..."></textarea>
                                    </div>
                                </div>
                            </div>

                             <!-- Section: Body -->
                            <div class="space-y-4 pt-2">
                                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                    <span class="w-1 h-4 bg-indigo-500 rounded-full"></span>
                                    Isi Dokumen
                                </h3>

                                <!-- Dynamic List: Basis -->
                                <div class="space-y-2">
                                    <label class="text-xs text-slate-500 font-medium ml-1">Dasar / Basis (Poin-poin)</label>
                                    <div class="space-y-2">
                                        <template x-for="(item, index) in document.content_data.basis" :key="index">
                                            <div class="flex gap-2 group">
                                                <div class="relative w-full">
                                                     <span class="absolute left-3 top-2.5 text-xs text-slate-400 font-mono" x-text="index + 1 + '.'"></span>
                                                    <input type="text" x-model="document.content_data.basis[index]" class="form-input-styled pl-8" placeholder="Isi poin...">
                                                </div>
                                                <button @click="removeListItem('basis', index)" class="text-slate-300 hover:text-red-500 p-2 hover:bg-red-50 rounded-lg transition-colors">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                    <button @click="addListItem('basis')" class="w-full py-2 border border-dashed border-indigo-200 text-indigo-600 text-xs font-bold rounded-lg hover:bg-indigo-50 hover:border-indigo-300 transition-all flex items-center justify-center gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        Tambah Poin Dasar
                                    </button>
                                </div>

                                <div class="space-y-1">
                                    <label class="text-xs text-slate-500 font-medium ml-1">Paragraf Isi</label>
                                    <textarea x-model="document.content_data.content" rows="8" class="form-textarea-styled leading-relaxed" placeholder="Ketik isi surat disini..."></textarea>
                                </div>
                            </div>
                            
                            <!-- Section: Footer / Signature -->
                            <div class="space-y-6 pt-2">
                                <!-- BD-MLI Signature Section (MUST BE FIRST) -->
                                <div class="space-y-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                        <span class="w-1 h-4 bg-emerald-500 rounded-full"></span>
                                        Paraf BD-MLI (Wajib diisi pertama)
                                    </h3>
                                    
                                    <div class="pt-2">
                                        <template x-if="!document.content_data.bdMliSignature">
                                            <button @click="initSignaturePad('bdMliSignature')" class="w-full py-2 border border-dashed border-emerald-300 text-emerald-600 text-xs font-bold rounded-lg hover:bg-emerald-50 transition-all flex items-center justify-center gap-2">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                BUAT PARAF BD-MLI
                                            </button>
                                        </template>
                                        <template x-if="document.content_data.bdMliSignature">
                                            <div class="relative group border border-emerald-200 rounded-lg p-2 bg-white text-center">
                                                <img :src="document.content_data.bdMliSignature" alt="Signature" class="h-16 mx-auto object-contain">
                                                <button @click="removeSignature('bdMliSignature')" class="absolute top-1 right-1 bg-white text-red-500 rounded-full p-1 shadow hover:bg-red-50 opacity-0 group-hover:opacity-100 transition-all">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </button>
                                                <p class="text-[10px] text-emerald-400 mt-1">Klik hapus untuk ulang</p>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Main Signature Section (Locked until BD-MLI is done) -->
                                <div class="space-y-4 pt-2 transition-all duration-300" :class="!document.content_data.bdMliSignature ? 'opacity-40 grayscale pointer-events-none select-none' : ''">
                                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                        <span class="w-1 h-4 bg-indigo-500 rounded-full"></span>
                                        Penutup & Tanda Tangan
                                    </h3>

                                    <template x-if="!document.content_data.bdMliSignature">
                                        <div class="text-[10px] text-amber-600 font-bold bg-amber-50 p-2 rounded border border-amber-100 flex items-center gap-2">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                            Lengkapi Paraf BD-MLI di atas untuk membuka bagian ini.
                                        </div>
                                    </template>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <label class="text-xs text-slate-500 font-medium ml-1">Lokasi</label>
                                            <input type="text" x-model="document.content_data.location" class="form-input-styled" placeholder="Jakarta" :disabled="!document.content_data.bdMliSignature">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-xs text-slate-500 font-medium ml-1">Tanggal</label>
                                            <input type="date" x-model="document.content_data.date" class="form-input-styled" :disabled="!document.content_data.bdMliSignature">
                                        </div>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="text-xs text-slate-500 font-medium ml-1">Jabatan Penandatangan</label>
                                        <input type="text" x-model="document.content_data.signerPosition" class="form-input-styled" placeholder="Contoh: KEPALA DIVISI..." :disabled="!document.content_data.bdMliSignature">
                                    </div>
                                    
                                    <div class="space-y-1">
                                        <label class="text-xs text-slate-500 font-medium ml-1">Nama Divisi</label>
                                        <input type="text" x-model="document.content_data.division" class="form-input-styled" placeholder="Contoh: DIVISI TEKNOLOGI..." :disabled="!document.content_data.bdMliSignature">
                                    </div>

                                    <div class="space-y-1">
                                        <label class="text-xs text-slate-500 font-medium ml-1">Nama Lengkap</label>
                                        <input type="text" x-model="document.content_data.signerName" class="form-input-styled font-bold" placeholder="Nama Penandatangan" :disabled="!document.content_data.bdMliSignature">
                                    </div>
                                    
                                    <div class="pt-2">
                                        <template x-if="!document.content_data.signature">
                                            <button @click="initSignaturePad('signature')" :disabled="!document.content_data.bdMliSignature" class="w-full py-2 border border-dashed border-indigo-300 text-indigo-600 text-xs font-bold rounded-lg hover:bg-indigo-50 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                BUAT TANDA TANGAN
                                            </button>
                                        </template>
                                        <template x-if="document.content_data.signature">
                                            <div class="relative group border border-slate-200 rounded-lg p-2 bg-slate-50 text-center">
                                                <img :src="document.content_data.signature" alt="Signature" class="h-16 mx-auto object-contain">
                                                <button @click="removeSignature('signature')" class="absolute top-1 right-1 bg-white text-red-500 rounded-full p-1 shadow hover:bg-red-50 opacity-0 group-hover:opacity-100 transition-all">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </button>
                                                <p class="text-[10px] text-slate-400 mt-1">Klik hapus untuk ulang</p>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- SPPD Fields -->
                    <template x-if="document.type === 'sppd'">
                         <div class="space-y-6">
                            
                            <!-- Section: Considerations -->
                            <div class="space-y-4">
                                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                    <span class="w-1 h-4 bg-emerald-500 rounded-full"></span>
                                    Dasar Pertimbangan
                                </h3>

                                <div class="space-y-1">
                                    <label class="text-xs text-slate-500 font-medium ml-1">Menimbang</label>
                                    <textarea x-model="document.content_data.weigh" rows="3" class="form-textarea-styled" placeholder="Bahwa dalam rangka..."></textarea>
                                </div>

                                <!-- Dynamic List: Mengingat -->
                                <div class="space-y-2">
                                    <label class="text-xs text-slate-500 font-medium ml-1">Mengingat (Daftar Peraturan)</label>
                                    <div class="space-y-2">
                                        <template x-for="(item, index) in document.content_data.remembers" :key="index">
                                            <div class="flex gap-2 group">
                                                <div class="relative w-full">
                                                    <span class="absolute left-3 top-2.5 text-xs text-slate-400 font-mono" x-text="index + 1 + '.'"></span>
                                                    <input type="text" x-model="document.content_data.remembers[index]" class="form-input-styled pl-8" placeholder="Peraturan...">
                                                </div>
                                                <button @click="removeListItem('remembers', index)" class="text-slate-300 hover:text-red-500 p-2 hover:bg-red-50 rounded-lg transition-colors">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                    <button @click="addListItem('remembers')" class="w-full py-2 border border-dashed border-emerald-200 text-emerald-600 text-xs font-bold rounded-lg hover:bg-emerald-50 hover:border-emerald-300 transition-all flex items-center justify-center gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        Tambah Poin Mengingat
                                    </button>
                                </div>
                            </div>

                             <!-- Section: Assignments -->
                            <div class="space-y-4 pt-2">
                                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                    <span class="w-1 h-4 bg-emerald-500 rounded-full"></span>
                                    Penugasan
                                </h3>
                                
                                <div class="space-y-1">
                                    <label class="text-xs text-slate-500 font-medium ml-1">Kepada (Penerima Tugas)</label>
                                    <input type="text" x-model="document.content_data.to" class="form-input-styled" placeholder="Nama & Jabatan">
                                </div>

                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-4">
                                    <p class="text-xs font-bold text-slate-400 uppercase">Detail Perintah (Untuk)</p>
                                    
                                    <div class="space-y-1">
                                        <label class="text-xs text-slate-500 ml-1">1. Kegiatan Utama</label>
                                        <input type="text" x-model="document.content_data.task" class="form-input-styled" placeholder="Melaksanakan kegiatan...">
                                    </div>

                                    <div class="space-y-1">
                                        <label class="text-xs text-slate-500 ml-1">2. Detail Perjalanan</label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <input type="text" x-model="document.content_data.destination" class="form-input-styled" placeholder="Tujuan">
                                            <input type="text" x-model="document.content_data.transport" class="form-input-styled" placeholder="Transportasi">
                                        </div>
                                        <div class="grid grid-cols-2 gap-3 mt-2">
                                            <div class="space-y-1">
                                                <span class="text-[10px] text-slate-400 ml-1">Tgl Berangkat</span>
                                                <input type="date" x-model="document.content_data.dateGo" class="form-input-styled">
                                            </div>
                                            <div class="space-y-1">
                                                 <span class="text-[10px] text-slate-400 ml-1">Tgl Kembali</span>
                                                <input type="date" x-model="document.content_data.dateBack" class="form-input-styled">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-3 pt-2">
                                        <div class="space-y-1">
                                            <label class="text-xs text-slate-500 ml-1">3. Pembebanan Biaya</label>
                                            <textarea x-model="document.content_data.funding" rows="2" class="form-textarea-styled text-sm"></textarea>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-xs text-slate-500 ml-1">4. Pelaporan</label>
                                            <textarea x-model="document.content_data.report" rows="2" class="form-textarea-styled text-sm"></textarea>
                                        </div>
                                         <div class="space-y-1">
                                            <label class="text-xs text-slate-500 ml-1">5. Penutup</label>
                                            <textarea x-model="document.content_data.closing" rows="2" class="form-textarea-styled text-sm"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                             <!-- Section: Signature & Tembusan -->
                             <div class="space-y-4 pt-2">
                                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                    <span class="w-1 h-4 bg-emerald-500 rounded-full"></span>
                                    Validasi
                                </h3>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="text-xs text-slate-500 font-medium ml-1">Dikeluarkan di</label>
                                        <input type="text" x-model="document.content_data.location" class="form-input-styled">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs text-slate-500 font-medium ml-1">Pada Tanggal</label>
                                        <input type="date" x-model="document.content_data.signDate" class="form-input-styled">
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <label class="text-xs text-slate-500 font-medium ml-1">Jabatan (Direksi)</label>
                                    <input type="text" x-model="document.content_data.signerPosition" class="form-input-styled" placeholder="DIREKTUR...">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs text-slate-500 font-medium ml-1">Nama Penandatangan</label>
                                    <input type="text" x-model="document.content_data.signerName" class="form-input-styled font-bold">
                                </div>
                                
                                <div class="pt-2">
                                    <template x-if="!document.content_data.signature">
                                        <button @click="initSignaturePad()" class="w-full py-2 border border-dashed border-indigo-300 text-indigo-600 text-xs font-bold rounded-lg hover:bg-indigo-50 transition-all flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            BUAT TANDA TANGAN
                                        </button>
                                    </template>
                                    <template x-if="document.content_data.signature">
                                        <div class="relative group border border-slate-200 rounded-lg p-2 bg-slate-50 text-center">
                                            <img :src="document.content_data.signature" alt="Signature" class="h-16 mx-auto object-contain">
                                            <button @click="removeSignature()" class="absolute top-1 right-1 bg-white text-red-500 rounded-full p-1 shadow hover:bg-red-50 opacity-0 group-hover:opacity-100 transition-all">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                            <p class="text-[10px] text-slate-400 mt-1">Klik hapus untuk ulang</p>
                                        </div>
                                    </template>
                                </div>

                                <!-- Dynamic List: Tembusan -->
                                <div class="space-y-2 pt-2 border-t border-dashed border-slate-200">
                                    <label class="text-xs text-slate-500 font-medium ml-1">Tembusan</label>
                                     <div class="space-y-2">
                                        <template x-for="(item, index) in document.content_data.ccs" :key="index">
                                            <div class="flex gap-2 group">
                                                 <div class="relative w-full">
                                                    <span class="absolute left-3 top-2.5 text-xs text-slate-400 font-mono" x-text="index + 1 + '.'"></span>
                                                    <input type="text" x-model="document.content_data.ccs[index]" class="form-input-styled pl-8" placeholder="Nama / Jabatan...">
                                                </div>
                                                <button @click="removeListItem('ccs', index)" class="text-slate-300 hover:text-red-500 p-2 hover:bg-red-50 rounded-lg transition-colors">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                    <button @click="addListItem('ccs')" class="text-xs text-slate-500 hover:text-emerald-600 underline decoration-dashed underline-offset-4 decoration-slate-300 hover:decoration-emerald-400 transition-all">
                                        + Tambah Tembusan
                                    </button>
                                </div>
                            </div>
                                                </div>
                                            </template>
                        
                                            <!-- PERJANJIAN Fields -->
                                            <template x-if="document.type === 'perj'">
                                                <div class="space-y-6">
                                                    
                                                    <!-- Section: Header Info -->
                                                    <div class="space-y-4">
                                                        <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                                            <span class="w-1 h-4 bg-amber-500 rounded-full"></span>
                                                            Tentang & Waktu
                                                        </h3>
                                                        
                                                        <div class="grid gap-4">
                                                            <div class="space-y-1">
                                                                <label class="text-xs text-slate-500 font-medium ml-1">Tentang</label>
                                                                <textarea x-model="document.content_data.about" rows="2" class="form-textarea-styled" placeholder="Isi tentang perjanjian..."></textarea>
                                                            </div>
                                                            <div class="grid grid-cols-2 gap-3">
                                                                <div class="space-y-1">
                                                                    <label class="text-xs text-slate-500 font-medium ml-1">Hari</label>
                                                                    <input type="text" x-model="document.content_data.day" class="form-input-styled" placeholder="Senin">
                                                                </div>
                                                                <div class="space-y-1">
                                                                    <label class="text-xs text-slate-500 font-medium ml-1">Lokasi</label>
                                                                    <input type="text" x-model="document.content_data.location" class="form-input-styled" placeholder="Jakarta">
                                                                </div>
                                                            </div>
                                                            <div class="space-y-1">
                                                                <label class="text-xs text-slate-500 font-medium ml-1">Tanggal (Teks)</label>
                                                                <input type="text" x-model="document.content_data.dateWritten" class="form-input-styled" placeholder="sembilan belas Januari dua ribu dua puluh enam">
                                                            </div>
                                                        </div>
                                                    </div>
                        
                                                    <!-- Section: Parties -->
                                                    <div class="space-y-4 pt-2">
                                                        <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                                            <span class="w-1 h-4 bg-amber-500 rounded-full"></span>
                                                            Para Pihak
                                                        </h3>
                        
                                                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                                                            <p class="text-[10px] font-bold text-slate-400 uppercase">Pihak Kesatu (ASABRI)</p>
                                                            <input type="text" x-model="document.content_data.party1Name" class="form-input-styled" placeholder="Nama Penandatangan">
                                                            <input type="text" x-model="document.content_data.party1Pos" class="form-input-styled" placeholder="Jabatan">
                                                            <textarea x-model="document.content_data.party1Auth" rows="3" class="form-textarea-styled text-xs" placeholder="Dasar hukum / Surat Kuasa..."></textarea>
                                                        </div>
                        
                                                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                                                            <p class="text-[10px] font-bold text-slate-400 uppercase">Pihak Kedua</p>
                                                            <input type="text" x-model="document.content_data.party2Name" class="form-input-styled" placeholder="Nama Pihak Kedua">
                                                            <textarea x-model="document.content_data.party2Info" rows="3" class="form-textarea-styled text-xs" placeholder="Detail Pihak Kedua (Lahir, Alamat, NIK...)"></textarea>
                                                        </div>
                                                    </div>
                        
                                                     <!-- Section: Points -->
                                                     <div class="space-y-4 pt-2">
                                                        <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                                            <span class="w-1 h-4 bg-amber-500 rounded-full"></span>
                                                            Poin-poin Perjanjian
                                                        </h3>
                        
                                                        <div class="space-y-2">
                                                            <template x-for="(item, index) in document.content_data.points" :key="index">
                                                                <div class="flex gap-2 group">
                                                                    <div class="relative w-full">
                                                                        <span class="absolute left-3 top-2.5 text-xs text-slate-400 font-bold" x-text="String.fromCharCode(65 + index) + '.'"></span>
                                                                        <textarea x-model="document.content_data.points[index]" class="form-textarea-styled pl-8 text-xs" rows="2" placeholder="Isi poin..."></textarea>
                                                                    </div>
                                                                    <button @click="removeListItem('points', index)" class="text-slate-300 hover:text-red-500 p-2 hover:bg-red-50 rounded-lg transition-colors">
                                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                                    </button>
                                                                </div>
                                                            </template>
                                                        </div>
                                                        <button @click="addListItem('points')" class="w-full py-2 border border-dashed border-amber-200 text-amber-600 text-xs font-bold rounded-lg hover:bg-amber-50 transition-all flex items-center justify-center gap-1">
                                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                                            Tambah Poin Perjanjian
                                                        </button>
                                                    </div>
                        
                                                                                                            <!-- Section: Paraf (Dinamis) -->
                        
                                                                                                            <div class="space-y-4 pt-2">
                        
                                                                                                                <div class="flex items-center justify-between">
                        
                                                                                                                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        
                                                                                                                        <span class="w-1 h-4 bg-amber-500 rounded-full"></span>
                        
                                                                                                                        Tabel Paraf
                        
                                                                                                                    </h3>
                        
                                                                                                                    <button @click="document.content_data.paraf.push({code: '', name: '', signature: ''})" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-wider">
                        
                                                                                                                        + Tambah Baris
                        
                                                                                                                    </button>
                        
                                                                                                                </div>
                        
                                                                                
                        
                                                                                                                <div class="space-y-3">
                        
                                                                                                                    <template x-for="(item, index) in document.content_data.paraf" :key="index">
                        
                                                                                                                        <div x-show="canSignParaf(index)" 
                        
                                                                                                                             @mouseenter="highlightParaf(index, true)"
                        
                                                                                                                             @mouseleave="highlightParaf(index, false)"
                        
                                                                                                                             x-transition:enter="transition ease-out duration-300"
                        
                                                                                                                             class="bg-slate-50 p-3 rounded-lg border border-slate-200 relative group">
                        
                                                                                                                            
                        
                                                                                                                            <button @click="document.content_data.paraf.splice(index, 1)" class="absolute -right-2 -top-2 bg-white text-red-500 rounded-full shadow border border-red-100 p-1 opacity-0 group-hover:opacity-100 transition-all z-10">
                        
                                                                                                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        
                                                                                                                            </button>
                        
                                                                                
                        
                                                                                                                            <div class="grid grid-cols-2 gap-2 mb-3">
                        
                                                                                                                                <div class="space-y-1">
                        
                                                                                                                                    <label class="text-[9px] font-bold text-slate-400 uppercase ml-1">Kode</label>
                        
                                                                                                                                    <input type="text" x-model="document.content_data.paraf[index].code" class="form-input-styled text-[10px] py-1" placeholder="DV-...">
                        
                                                                                                                                </div>
                        
                                                                                                                                <div class="space-y-1">
                        
                                                                                                                                    <label class="text-[9px] font-bold text-slate-400 uppercase ml-1">Nama</label>
                        
                                                                                                                                    <input type="text" x-model="document.content_data.paraf[index].name" class="form-input-styled text-[10px] py-1" placeholder="Nama...">
                        
                                                                                                                                </div>
                        
                                                                                                                            </div>
                        
                                                                                
                        
                                                                                                                            <!-- Paraf TTD -->
                        
                                                                                                                            <div>
                        
                                                                                                                                <template x-if="!item.signature">
                        
                                                                                                                                    <button @click="initSignaturePad('paraf.' + index, (item.code || 'Paraf ' + (index+1)) + ' - ' + (item.name || ''))" class="w-full py-1.5 border border-dashed border-indigo-200 text-indigo-500 text-[10px] font-bold rounded hover:bg-white transition-all">
                        
                                                                                                                                        ISI Tanda Tangan <span x-text="item.code"></span>
                        
                                                                                                                                    </button>
                        
                                                                                                                                </template>
                        
                                                                                                                                <template x-if="item.signature">
                        
                                                                                                                                    <div class="relative bg-white border border-indigo-100 rounded p-1 flex items-center justify-center group/sig">
                        
                                                                                                                                        <img :src="item.signature" class="h-8 object-contain">
                        
                                                                                                                                        <button @click="removeSignature('paraf.' + index)" class="absolute inset-0 bg-black/5 flex items-center justify-center opacity-0 group-hover/sig:opacity-100 transition-opacity">
                        
                                                                                                                                            <span class="bg-white text-red-500 text-[8px] px-1 rounded shadow font-bold">HAPUS TTD</span>
                        
                                                                                                                                        </button>
                        
                                                                                                                                    </div>
                        
                                                                                                                                </template>
                        
                                                                                                                            </div>
                        
                                                                                                                        </div>
                        
                                                                                                                    </template>
                        
                                                                                                                </div>
                        
                                                                                                            </div>
                                                </div>
                                            </template>
                                        </fieldset>
            </div>
            
            <!-- Bottom Actions -->
            <div class="p-6 bg-white border-t border-gray-100 pb-20 lg:pb-6">
                
                <!-- Reviewer Actions Block -->
                 <template x-if="currentUser?.role === 'reviewer' && document.id">
                    <div class="space-y-4 mb-6 bg-amber-50 p-5 rounded-xl border border-amber-100 shadow-sm">
                        <div class="flex items-center gap-2 text-amber-800 mb-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <h4 class="text-sm font-bold">Aksi Reviewer</h4>
                        </div>
                        <div>
                            <textarea
                                x-model="document.feedback"
                                rows="3"
                                class="w-full px-4 py-3 bg-white border border-amber-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm placeholder-amber-300"
                                placeholder="Tulis catatan revisi atau persetujuan disini..."
                            ></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                             <button
                                @click="updateStatus('needs_revision')"
                                class="w-full py-2.5 bg-white text-amber-600 border border-amber-200 rounded-lg hover:bg-amber-50 hover:border-amber-300 transition-all text-sm font-bold shadow-sm"
                            >
                                Minta Revisi
                            </button>
                            <button
                                @click="updateStatus('approved')"
                                class="w-full py-2.5 bg-emerald-600 text-white border border-transparent rounded-lg hover:bg-emerald-700 transition-all text-sm font-bold shadow-sm"
                            >
                                Setujui (Approve)
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Deadline Setting (for all users) -->
                <template x-if="currentUser?.role === 'user' && (document.status === 'draft' || document.status === 'needs_revision')">
                    <div class="mb-4 bg-gradient-to-br from-indigo-50 to-blue-50 p-5 rounded-xl border border-indigo-100 shadow-sm">
                        <div class="flex items-center gap-2 text-indigo-800 mb-3">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h4 class="text-sm font-bold">Batas Waktu (Opsional)</h4>
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs text-indigo-600 font-medium ml-1">Set Deadline Dokumen</label>
                            <input 
                                type="datetime-local" 
                                x-model="document.deadline"
                                class="w-full px-4 py-3 bg-white border border-indigo-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                :min="new Date().toISOString().slice(0, 16)"
                            >
                            <p class="text-xs text-indigo-600 ml-1">
                                <span x-show="!document.deadline">Tidak ada deadline yang diatur</span>
                                <span x-show="document.deadline" x-text="'Deadline: ' + formatDeadlineDisplay(document.deadline)"></span>
                            </p>
                        </div>
                    </div>
                </template>

                 <!-- Paraf Dinamis Setting -->
                <template x-if="currentUser?.role === 'user' && (document.status === 'draft' || document.status === 'needs_revision')">
                    <div class="mb-4 bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2 text-slate-800">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                                <h4 class="text-sm font-bold">Paraf Berjenjang</h4>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest" x-text="document.approvals?.length + ' PARAF'"></span>
                        </div>
                        
                        <div class="space-y-3">
                            <template x-for="(aprv, index) in document.approvals" :key="index">
                                <div class="flex gap-2 items-center bg-slate-50 p-2 rounded-lg border border-slate-100">
                                    <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-[10px] font-bold" x-text="index + 1"></div>
                                    <select x-model="aprv.approver_position" class="flex-1 text-xs p-1.5 bg-white border border-slate-200 rounded">
                                        <option value="staff">Staff</option>
                                        <option value="kabid">Kabid</option>
                                        <option value="kadiv">Kadiv</option>
                                        <option value="direksi">Direksi</option>
                                    </select>
                                    <button @click="document.approvals.splice(index, 1)" class="text-slate-300 hover:text-red-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </template>
                            
                            <button @click="document.approvals.push({sequence: document.approvals.length + 1, approver_position: 'kabid', status: 'pending'})" class="w-full py-2 border border-dashed border-slate-300 text-slate-500 text-[10px] font-bold rounded-lg hover:bg-slate-50 transition-all">
                                + TAMBAH JENJANG PARAF
                            </button>
                        </div>
                    </div>
                </template>

                <!-- User Send Button -->
                <template x-if="currentUser?.role === 'user' && (document.status === 'draft' || document.status === 'needs_revision')">
                    <div class="mb-4">
                        <button 
                            @click="showSendModal = true" 
                            class="w-full bg-indigo-600 text-white py-3 rounded-lg font-bold hover:bg-indigo-700 transition-colors flex justify-center items-center gap-2"
                        >
                            <span>KIRIM DOKUMEN</span>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </div>
                </template>

                <!-- Forward Button for Received/Sent Documents (Re-route) -->
                <template x-if="currentUser?.role === 'user' && (document.status === 'received' || document.status === 'sent')">
                    <div>
                        <div class="mb-4">
                            <button 
                                @click="showSendModal = true" 
                                class="w-full bg-emerald-600 text-white py-3 rounded-lg font-bold hover:bg-emerald-700 transition-colors flex justify-center items-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>TERUSKAN DOKUMEN</span>
                            </button>
                            <p class="text-xs text-slate-500 text-center mt-2">Kirim dokumen ini ke group/divisi lain</p>
                        </div>

                        <div class="mb-4">
                            <button 
                                @click="finishDocument()" 
                                class="w-full bg-slate-800 text-white py-3 rounded-lg font-bold hover:bg-slate-900 transition-colors flex justify-center items-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>SELESAI / ACC</span>
                            </button>
                            <p class="text-xs text-slate-500 text-center mt-2">Tandai dokumen selesai (tidak bisa diedit lagi)</p>
                        </div>
                    </div>
                </template>

                <!-- Download Button -->
                <button 
                    @click="downloadPDF()" 
                    class="w-full bg-white text-slate-700 border border-slate-200 py-3 rounded-xl font-bold hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900 transition-all flex justify-center items-center gap-2 shadow-sm"
                >
                    <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Download PDF</span>
                </button>
            </div>
        </div>

    </div>

    <style>
        /* CSS for Print / Save as PDF */
        @media print {
            /* Hide everything by default */
            body * {
                visibility: hidden;
            }
            
            /* Hide the main scrollbars */
            html, body {
                height: auto;
                overflow: visible !important;
                background: white !important;
            }

            /* Specifically hide fixed elements that might persist */
            .fixed, header, nav, aside, button, .sidebar-container {
                display: none !important;
            }

            /* Make the paper content visible and positioned correctly */
            #paperContent, #paperContent * {
                visibility: visible;
            }

            #paperContent {
                position: absolute;
                left: 0;
                top: 0;
                width: 100% !important;
                margin: 0 !important;
                padding: 20mm !important; /* Ensure proper print padding */
                box-shadow: none !important;
                background-color: white !important;
                color: black !important;
                min-height: auto !important;
                overflow: visible !important;
            }

            /* Ensure images are visible */
            img {
                display: inline-block !important;
                opacity: 1 !important;
                max-width: 100% !important;
            }
            
            /* Reset specific backgrounds for print to be clean */
            .bg-slate-50, .bg-gray-100, .bg-fuchsia-50 {
                background-color: transparent !important;
                border: 1px solid #ddd !important; /* Add border for visibility if needed */
            }
            
            /* Force table layouts */
            table {
                width: 100% !important;
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
    <!-- Right Content: Paper Preview -->
    <div class="flex-1 overflow-y-auto p-8 flex justify-center relative print:p-0 print:overflow-visible" style="background-color: #e2e8f0;">
        <div id="paperContent" class="paper relative min-h-[297mm]" style="color: #000000 !important; background-color: #ffffff !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
            
            <div class="flex items-center mb-2">
                <!-- Using local asset if available. Removed external fallback to prevent CORS/Taint errors during PDF generation. -->
                <img src="/images/logo_asa.png" alt="ASABRI Logo" class="h-16 mb-2" style="height: 4rem; margin-bottom: 0.5rem;">
            </div>

            <!-- PREVIEW: NOTA DINAS -->
            <template x-if="document.type === 'nota'">
                <div>
                    <div class="paper-header">
                        <h1 class="font-bold text-lg uppercase tracking-wide">NOTA DINAS</h1>
                        <p>NOMOR <span x-text="document.content_data.docNumber || '...'"></span></p>
                    </div>

                    <table class="info-table w-full mb-6">
                        <tr><td width="100">Kepada</td><td width="20">:</td><td>Yth. <span x-text="document.content_data.to || '...'"></span></td></tr>
                        <tr><td>Dari</td><td>:</td><td><span x-text="document.content_data.from || '...'"></span></td></tr>
                        <tr><td>Lampiran</td><td>:</td><td><span x-text="document.content_data.attachment || '...'"></span></td></tr>
                        <tr><td>Hal</td><td>:</td><td class="font-bold"><span x-text="document.content_data.subject || '...'"></span></td></tr>
                    </table>

                    <div class="mb-4">
                        <p class="mb-2">Berdasarkan:</p>
                        <ol class="list-numbered text-justify">
                            <template x-for="item in document.content_data.basis">
                                <li x-text="item" class="mb-1 pl-1"></li>
                            </template>
                            <li x-show="!document.content_data.basis?.length" style="list-style: none">...</li>
                        </ol>
                    </div>

                    <div class="mb-8 text-justify leading-relaxed">
                        <p style="white-space: pre-wrap;" x-text="document.content_data.content || '...'"></p>
                    </div>

                    <p class="mb-8">Demikian disampaikan dan untuk dijadikan periksa.</p>

                    <div class="signature-section">
                        <p class="mb-1"><span x-text="document.content_data.location || '...'"></span>, <span x-text="formatDate(document.content_data.date)"></span></p>
                        <p class="font-bold uppercase mb-0"><span x-text="document.content_data.signerPosition || '...'"></span></p>
                        <p class="font-bold uppercase mb-0"><span x-text="document.content_data.division || '...'"></span></p>
                        
                        <!-- Signature Image Placeholder -->
                        <div class="h-24 w-full flex items-center justify-center">
                            <template x-if="document.content_data.signature">
                                <img :src="document.content_data.signature" class="h-24 object-contain" alt="Tanda Tangan">
                            </template>
                            <template x-if="!document.content_data.signature">
                                <div class="h-24 w-full"></div>
                            </template>
                        </div>
                        
                        <p class="font-bold uppercase underline"><span x-text="document.content_data.signerName || '...'"></span></p>
                    </div>

                    <div style="clear: both;"></div>
                    <table class="paraf-box">
                        <tr><td colspan="2" class="text-center font-bold" style="background-color: #f3f4f6 !important;">BD-MLI</td></tr>
                        <tr><td rowspan="2" class="text-center align-middle" width="50%">Paraf</td><td class="text-center">Staff</td></tr>
                        <tr><td class="text-center h-12 align-middle">
                            <template x-if="document.content_data.bdMliSignature">
                                <img :src="document.content_data.bdMliSignature" style="max-height: 40px; margin: 0 auto; display: block;">
                            </template>
                        </td></tr>
                    </table>
                </div>
            </template>

            <!-- PREVIEW: SPPD -->
            <template x-if="document.type === 'sppd'">
                <div>
                    <div class="paper-header" style="margin-bottom: 30px;">
                        <h1 class="font-bold text-lg uppercase tracking-wide">SURAT PERINTAH PERJALANAN DINAS</h1>
                        <p>NOMOR <span x-text="document.content_data.docNumber || '...'"></span></p>
                    </div>

                    <table class="sppd-table">
                        <tr>
                            <td class="sppd-label">Menimbang</td>
                            <td class="sppd-colon">:</td>
                            <td><span x-text="document.content_data.weigh || '...'"></span></td>
                        </tr>
                    </table>

                    <table class="sppd-table">
                        <tr>
                            <td class="sppd-label">Mengingat</td>
                            <td class="sppd-colon">:</td>
                            <td>
                                <ol class="list-numbered" style="margin-top: 0; margin-bottom: 0; padding-left: 15px;">
                                    <template x-for="item in document.content_data.remembers">
                                        <li x-text="item" class="mb-1"></li>
                                    </template>
                                    <li x-show="!document.content_data.remembers?.length" style="list-style: none">...</li>
                                </ol>
                            </td>
                        </tr>
                    </table>

                    <div class="text-center font-bold my-6">Memberi Perintah</div>

                    <table class="sppd-table">
                        <tr>
                            <td class="sppd-label">Kepada</td>
                            <td class="sppd-colon"></td>
                            <td class="font-bold"><span x-text="document.content_data.to || '...'"></span></td>
                        </tr>
                    </table>

                    <table class="sppd-table">
                        <tr>
                            <td class="sppd-label">Untuk</td>
                            <td class="sppd-colon">:</td>
                            <td>
                                <ol class="list-numbered" style="margin-top: 0; padding-left: 15px;">
                                    <li class="mb-2"><span x-text="document.content_data.task || '...'"></span></li>
                                    
                                    <li class="mb-2">
                                        Perjalanan dinas dilaksanakan, sebagai berikut:
                                        <table class="sub-table w-full mt-1">
                                            <tr><td width="100">Tujuan</td><td width="10">:</td><td><span x-text="document.content_data.destination || '...'"></span></td></tr>
                                            <tr><td>Berangkat</td><td>:</td><td><span x-text="formatDate(document.content_data.dateGo)"></span></td></tr>
                                            <tr><td>Kembali</td><td>:</td><td><span x-text="formatDate(document.content_data.dateBack)"></span></td></tr>
                                            <tr><td>Transportasi</td><td>:</td><td><span x-text="document.content_data.transport || '...'"></span></td></tr>
                                        </table>
                                    </li>

                                    <li class="mb-2 text-justify"><span x-text="document.content_data.funding || '...'"></span></li>
                                    <li class="mb-2 text-justify"><span x-text="document.content_data.report || '...'"></span></li>
                                    <li class="mb-2 text-justify"><span x-text="document.content_data.closing || '...'"></span></li>
                                </ol>
                            </td>
                        </tr>
                    </table>

                    <div class="signature-section">
                        <p class="mb-1">Dikeluarkan di <span x-text="document.content_data.location || '...'"></span></p>
                        <p class="mb-1">pada tanggal <span x-text="formatDate(document.content_data.signDate)"></span></p>
                        <p class="font-bold uppercase mb-0">DIREKSI,</p>
                        <p class="font-bold uppercase mb-0"><span x-text="document.content_data.signerPosition || '...'"></span></p>

                        <!-- Signature Image Placeholder -->
                        <div class="h-24 w-full flex items-center justify-center">
                            <template x-if="document.content_data.signature">
                                <img :src="document.content_data.signature" class="h-24 object-contain" alt="Tanda Tangan">
                            </template>
                            <template x-if="!document.content_data.signature">
                                <div class="h-24 w-full"></div>
                            </template>
                        </div>

                        <p class="font-bold uppercase underline"><span x-text="document.content_data.signerName || '...'"></span></p>
                    </div>

                    <div style="clear: both;"></div>
                    
                                        <div class="mt-8 text-sm">
                    
                                            <p class="font-bold underline mb-1">Tembusan:</p>
                    
                                            <ol class="list-numbered" style="margin-left: 15px;">
                    
                                                <template x-for="item in document.content_data.ccs">
                    
                                                    <li x-text="item" class="mb-1"></li>
                    
                                                </template>
                    
                                                <li x-show="!document.content_data.ccs?.length" style="list-style: none">...</li>
                    
                                            </ol>
                    
                                        </div>
                    
                                    </div>
                    
                                </template>
                    
                    
                    
                                <!-- PREVIEW: PERJANJIAN -->
                    
                                <template x-if="document.type === 'perj'">
                    
                                    <div class="text-justify leading-normal" style="font-family: 'Times New Roman', serif; font-size: 11pt;">
                    
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
                    
                                                <span x-text="document.content_data.party1Name || '...'" class="font-bold"></span> 
                    
                                                dalam jabatannya selaku <span x-text="document.content_data.party1Pos || '...'" class="font-bold"></span> 
                    
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
                    
                    
                    
                                        <div class="space-y-4">
                    
                                            <template x-for="(point, index) in document.content_data.points">
                    
                                                <div class="flex items-start">
                    
                                                    <div class="w-8 flex-shrink-0 font-bold" x-text="String.fromCharCode(65 + index) + '.'"></div>
                    
                                                    <div class="flex-grow text-justify" x-text="point"></div>
                    
                                                </div>
                    
                                            </template>
                    
                                        </div>
                    
                    
                    
                                                                                                                        <!-- Paraf Table (Dinamis) -->
                    
                    
                    
                                                                                                                        <div class="paraf-container">
                    
                    
                    
                                                                                                                            <table class="paraf-table">
                    
                    
                    
                                                                                                                                <tr>
                    
                    
                    
                                                                                                                                    <td rowspan="3" class="col-paraf-label">Paraf</td>
                    
                    
                    
                                                                                                                                    <template x-for="(p, index) in document.content_data.paraf" :key="'code-'+index">
                    
                    
                    
                                                                                                                                        <td class="cell-width" x-text="p.code || '...'"></td>
                    
                    
                    
                                                                                                                                    </template>
                    
                    
                    
                                                                                                                                </tr>
                    
                    
                    
                                                                                                                                <tr class="row-name">
                    
                    
                    
                                                                                                                                    <template x-for="(p, index) in document.content_data.paraf" :key="'name-'+index">
                    
                    
                    
                                                                                                                                        <td x-text="p.name || '...'"></td>
                    
                    
                    
                                                                                                                                    </template>
                    
                    
                    
                                                                                                                                </tr>
                    
                    
                    
                                                                                                                                <tr class="row-signature">
                    
                    
                    
                                                                                                                                    <template x-for="(p, index) in document.content_data.paraf" :key="'sig-'+index">
                    
                    
                    
                                                                                                                                        <td :id="'paraf-cell-' + index" class="align-middle h-[65px] transition-all duration-300">
                    
                    
                    
                                                                                                                                            <template x-if="p.signature">
                    
                    
                    
                                                                                                                                                <img :src="p.signature" style="max-height: 60px; margin: 0 auto; display: block;">
                    
                    
                    
                                                                                                                                            </template>
                    
                    
                    
                                                                                                                                        </td>
                    
                    
                    
                                                                                                                                    </template>
                    
                    
                    
                                                                                                                                </tr>
                    
                    
                    
                                                                                                                            </table>
                    
                    
                    
                                                                                                                        </div>
                    
                                    </div>
                    
                                </template>
                    
                            </div>
                    
                        </div>
</div>


@endsection