@extends('layouts.app')

@section('title', 'Editor - DOF')

@section('content')
<div class="flex flex-col lg:flex-row h-screen overflow-hidden bg-gray-100" x-data="editorApp()" x-init="init()">
    
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
                            <template x-for="group in groups" :key="group">
                                <option :value="group" x-text="group"></option>
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
                      :class="document.type === 'nota' ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'"
                      x-text="document.type === 'nota' ? 'Nota Dinas' : 'SPPD'"></span>
                
                <div class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-500 border border-slate-200" 
                     x-text="getStatusLabel(document.status)"></div>
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
                            <div class="space-y-4 pt-2">
                                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                    <span class="w-1 h-4 bg-indigo-500 rounded-full"></span>
                                    Penutup & Tanda Tangan
                                </h3>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="text-xs text-slate-500 font-medium ml-1">Lokasi</label>
                                        <input type="text" x-model="document.content_data.location" class="form-input-styled" placeholder="Jakarta">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs text-slate-500 font-medium ml-1">Tanggal</label>
                                        <input type="date" x-model="document.content_data.date" class="form-input-styled">
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <label class="text-xs text-slate-500 font-medium ml-1">Jabatan Penandatangan</label>
                                    <input type="text" x-model="document.content_data.signerPosition" class="form-input-styled" placeholder="Contoh: KEPALA DIVISI...">
                                </div>
                                
                                <div class="space-y-1">
                                    <label class="text-xs text-slate-500 font-medium ml-1">Nama Divisi</label>
                                    <input type="text" x-model="document.content_data.division" class="form-input-styled" placeholder="Contoh: DIVISI TEKNOLOGI...">
                                </div>

                                <div class="space-y-1">
                                    <label class="text-xs text-slate-500 font-medium ml-1">Nama Lengkap</label>
                                    <input type="text" x-model="document.content_data.signerName" class="form-input-styled font-bold" placeholder="Nama Penandatangan">
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

                 <!-- User Send Button -->
                <template x-if="currentUser?.role === 'user' && (document.status === 'draft' || document.status === 'needs_revision' || document.status === 'received' || document.status === 'sent')">
                    <div class="mb-4">
                        <button 
                            @click="showSendModal = true" 
                            class="w-full bg-indigo-600 text-white py-3 rounded-lg font-bold hover:bg-indigo-700 transition-colors flex justify-center items-center gap-2"
                        >
                            <span x-text="(document.status === 'draft' || document.status === 'needs_revision') ? 'KIRIM DOKUMEN' : 'TERUSKAN / BALAS'"></span>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
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

    <!-- Right Content: Paper Preview -->
    <div class="flex-1 bg-slate-200 overflow-y-auto p-8 flex justify-center relative">
        <div id="paperContent" class="paper relative min-h-[297mm]" style="color: #000000 !important; background-color: #ffffff !important;">
            
            <div class="flex items-center mb-2">
                <!-- Using local asset if available, fallback to provided URL -->
                <img src="/images/logo_asa.png" onerror="this.src='https://pensiun.asabri.co.id/resources/img/logo_asa.png'" alt="ASABRI Logo" class="h-16 mb-2">
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
                        <p class="font-bold uppercase mb-16"><span x-text="document.content_data.division || '...'"></span></p>
                        <p class="font-bold uppercase underline"><span x-text="document.content_data.signerName || '...'"></span></p>
                    </div>

                    <div style="clear: both;"></div>
                    <table class="paraf-box">
                        <tr><td colspan="2" class="text-center font-bold" style="background-color: #f3f4f6;">BD-MLI</td></tr>
                        <tr><td rowspan="2" class="text-center align-middle" width="50%">Paraf</td><td class="text-center">Staff</td></tr>
                        <tr><td class="text-center" height="30"> </td></tr>
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
                        <p class="font-bold uppercase mb-16"><span x-text="document.content_data.signerPosition || '...'"></span></p>
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

        </div>
    </div>
</div>


@endsection