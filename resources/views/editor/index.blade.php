@extends('layouts.app')

@section('title', 'Editor - DOF')

@section('content')
<div class="flex flex-col lg:flex-row h-screen overflow-hidden bg-gray-100" x-data="editorApp()" x-init="init()">
    
    <!-- Read Only Modal -->
    <div x-show="showReadOnlyModal" x-cloak x-transition class="fixed inset-0 z-[60] bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showReadOnlyModal = false" class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6 text-center">
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
    <div x-show="showSendModal" x-cloak x-transition class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showSendModal = false" class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
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
                    
                    <div x-show="document.target_role === 'group'" x-transition class="pl-8">
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
    
    <!-- Left Sidebar: Input Form -->
    <div class="w-full lg:w-1/3 bg-white flex flex-col border-r border-gray-200 shadow-lg z-10 h-full">
        
        <!-- Toolbar -->
        <div class="p-4 bg-slate-50 border-b flex justify-between items-center">
            <a href="/dashboard" class="flex items-center text-slate-600 hover:text-slate-900 text-sm font-medium gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Dashboard
            </a>
            <div class="flex gap-2">
                <button 
                    @click="saveDocument()" 
                    :disabled="saving || !isEditable()"
                    class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm font-bold hover:bg-indigo-700 flex items-center gap-1 disabled:opacity-50"
                >
                    <svg x-show="!saving" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    <span x-text="saving ? 'Saving...' : 'Simpan'"></span>
                </button>
            </div>
        </div>

        <!-- Document Type Info -->
        <div class="px-6 py-3 bg-slate-100 border-b">
            <div class="flex items-center justify-between">
                <div class="w-full">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider" 
                              :class="document.type === 'nota' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700'"
                              x-text="document.type === 'nota' ? 'Nota Dinas' : 'SPPD'"></span>
                        <div class="text-[10px] px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 font-bold uppercase tracking-wider" x-text="getStatusLabel(document.status)"></div>
                    </div>
                    <input type="text" x-model="document.title" :disabled="!isEditable()" class="bg-transparent border-none p-0 text-base font-bold text-slate-800 focus:ring-0 w-full disabled:text-slate-500" placeholder="Input Nama Dokumen...">
                </div>
            </div>
        </div>

        <!-- Form Fields -->
        <div class="p-6 overflow-y-auto flex-grow space-y-4">
            
            <template x-if="!isEditable() && currentUser?.role === 'user'">
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3 shadow-sm mb-2">
                    <div class="bg-blue-100 p-2 rounded-lg text-blue-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-blue-900">Dokumen Terkunci</h4>
                        <p class="text-xs text-blue-700 mt-0.5 leading-relaxed">
                            Status: <span class="font-bold capitalize" x-text="getStatusLabel(document.status)"></span>. 
                            Konten tidak dapat diubah karena sedang dalam proses review atau sudah disetujui.
                        </p>
                    </div>
                </div>
            </template>

            <fieldset :disabled="!isEditable()" class="space-y-4 border-none p-0 m-0">
            
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Nomor Dokumen</label>
                <input type="text" x-model="document.content_data.docNumber" class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder=".../...">
            </div>

            <!-- NOTA DINAS FORM -->
            <template x-if="document.type === 'nota'">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-4">
                        <input type="text" x-model="document.content_data.to" class="w-full p-2 border border-gray-300 rounded" placeholder="Kepada (Yth...)">
                        <input type="text" x-model="document.content_data.from" class="w-full p-2 border border-gray-300 rounded" placeholder="Dari">
                        <input type="text" x-model="document.content_data.attachment" class="w-full p-2 border border-gray-300 rounded" placeholder="Lampiran">
                        <textarea x-model="document.content_data.subject" rows="2" class="w-full p-2 border border-gray-300 rounded" placeholder="Hal / Perihal"></textarea>
                    </div>

                    <hr class="border-gray-200">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Berdasarkan (Poin)</label>
                        <div class="space-y-2">
                            <template x-for="(item, index) in document.content_data.basis" :key="index">
                                <div class="flex gap-2">
                                    <input type="text" x-model="document.content_data.basis[index]" class="w-full p-2 border border-gray-300 rounded" placeholder="Poin...">
                                    <button @click="removeListItem('basis', index)" class="text-red-500 hover:bg-red-50 px-2 rounded">&times;</button>
                                </div>
                            </template>
                        </div>
                        <button @click="addListItem('basis')" class="mt-2 text-xs bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded hover:bg-indigo-100 font-medium">+ Tambah Poin</button>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mt-2">Isi Paragraf</label>
                        <textarea x-model="document.content_data.content" rows="6" class="w-full p-2 border border-gray-300 rounded" placeholder="Sehubungan dengan..."></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" x-model="document.content_data.location" class="w-full p-2 border border-gray-300 rounded" placeholder="Lokasi (Jakarta)">
                        <input type="date" x-model="document.content_data.date" class="w-full p-2 border border-gray-300 rounded">
                    </div>
                    <input type="text" x-model="document.content_data.signerPosition" class="w-full p-2 border border-gray-300 rounded" placeholder="Jabatan">
                    <input type="text" x-model="document.content_data.division" class="w-full p-2 border border-gray-300 rounded" placeholder="Divisi">
                    <input type="text" x-model="document.content_data.signerName" class="w-full p-2 border border-gray-300 rounded" placeholder="Nama Penandatangan">
                </div>
            </template>

            <!-- SPPD FORM -->
            <template x-if="document.type === 'sppd'">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Menimbang</label>
                        <textarea x-model="document.content_data.weigh" rows="3" class="w-full p-2 border border-gray-300 rounded" placeholder="bahwa dalam rangka..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mengingat (List)</label>
                        <div class="space-y-2">
                            <template x-for="(item, index) in document.content_data.remembers" :key="index">
                                <div class="flex gap-2">
                                    <input type="text" x-model="document.content_data.remembers[index]" class="w-full p-2 border border-gray-300 rounded" placeholder="Peraturan...">
                                    <button @click="removeListItem('remembers', index)" class="text-red-500 hover:bg-red-50 px-2 rounded">&times;</button>
                                </div>
                            </template>
                        </div>
                        <button @click="addListItem('remembers')" class="mt-2 text-xs bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded hover:bg-indigo-100 font-medium">+ Tambah</button>
                    </div>

                    <hr class="border-gray-200">
                    <input type="text" x-model="document.content_data.to" class="w-full p-2 border border-gray-300 rounded" placeholder="Kepada (Nama & Jabatan)">

                    <div class="bg-gray-50 p-3 rounded border border-gray-200">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Detail Perintah (Untuk)</label>
                        
                        <label class="text-xs text-gray-500">Poin 1: Kegiatan</label>
                        <input type="text" x-model="document.content_data.task" class="w-full p-2 border border-gray-300 rounded mb-2" placeholder="Melaksanakan kegiatan...">
                        
                        <label class="text-xs text-gray-500">Poin 2: Detail Perjalanan</label>
                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <input type="text" x-model="document.content_data.destination" class="p-2 border border-gray-300 rounded" placeholder="Tujuan (Denpasar)">
                            <input type="text" x-model="document.content_data.transport" class="p-2 border border-gray-300 rounded" placeholder="Pesawat Udara">
                        </div>
                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <div><span class="text-xs">Berangkat</span><input type="date" x-model="document.content_data.dateGo" class="w-full p-2 border border-gray-300 rounded"></div>
                            <div><span class="text-xs">Kembali</span><input type="date" x-model="document.content_data.dateBack" class="w-full p-2 border border-gray-300 rounded"></div>
                        </div>

                        <label class="text-xs text-gray-500">Poin 3, 4, 5 (Standar/Edit)</label>
                        <textarea x-model="document.content_data.funding" rows="2" class="w-full p-2 border border-gray-300 rounded mb-1" placeholder="Biaya dibebankan..."></textarea>
                        <textarea x-model="document.content_data.report" rows="2" class="w-full p-2 border border-gray-300 rounded mb-1" placeholder="Melaporkan pelaksanaan..."></textarea>
                        <textarea x-model="document.content_data.closing" rows="1" class="w-full p-2 border border-gray-300 rounded" placeholder="Melaksanakan dengan tanggung jawab."></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" x-model="document.content_data.location" class="w-full p-2 border border-gray-300 rounded" placeholder="Lokasi">
                        <input type="date" x-model="document.content_data.signDate" class="w-full p-2 border border-gray-300 rounded">
                    </div>
                    <input type="text" x-model="document.content_data.signerPosition" class="w-full p-2 border border-gray-300 rounded" placeholder="Jabatan Penandatangan (DIREKTUR UTAMA)">
                    <input type="text" x-model="document.content_data.signerName" class="w-full p-2 border border-gray-300 rounded" placeholder="Nama Penandatangan">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tembusan</label>
                        <div class="space-y-2">
                            <template x-for="(item, index) in document.content_data.ccs" :key="index">
                                <div class="flex gap-2">
                                    <input type="text" x-model="document.content_data.ccs[index]" class="w-full p-2 border border-gray-300 rounded" placeholder="Direksi...">
                                    <button @click="removeListItem('ccs', index)" class="text-red-500 hover:bg-red-50 px-2 rounded">&times;</button>
                                </div>
                            </template>
                        </div>
                        <button @click="addListItem('ccs')" class="mt-2 text-xs bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded hover:bg-indigo-100 font-medium">+ Tambah</button>
                    </div>
                </div>
            </template>
            </fieldset>

            <!-- Target / Status Section -->
            <div class="border-t border-gray-200 pt-4 mt-4">
                 <!-- Reviewer Feedback Section (if reviewer) -->
                 <template x-if="currentUser?.role === 'reviewer' && document.id">
                    <div class="space-y-4 mb-6 bg-amber-50 p-4 rounded-lg border border-amber-200">
                        <h4 class="text-sm font-semibold text-amber-800">Reviewer Actions</h4>
                        <div>
                            <label class="block text-sm font-medium text-amber-800 mb-2">Feedback</label>
                            <textarea
                                x-model="document.feedback"
                                rows="3"
                                class="w-full px-4 py-2 border border-amber-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                placeholder="Tulis catatan revisi atau persetujuan..."
                            ></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button
                                @click="updateStatus('approved')"
                                class="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors text-sm font-bold"
                            >
                                Approve
                            </button>
                            <button
                                @click="updateStatus('needs_revision')"
                                class="flex-1 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors text-sm font-bold"
                            >
                                Request Revision
                            </button>
                        </div>
                    </div>
                </template>

                <template x-if="currentUser?.role === 'user' && (document.status === 'draft' || document.status === 'needs_revision')">
                    <div class="space-y-3">
                        <button @click="showSendModal = true" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-bold hover:bg-indigo-700 shadow-md hover:shadow-lg transition-all text-sm flex justify-center items-center gap-2">
                            <span>KIRIM DOKUMEN</span>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                        <p class="text-center text-xs text-slate-400">Dokumen akan dikirim ke Group atau Reviewer.</p>
                    </div>
                </template>
            </div>
        </div>

        <div class="p-4 bg-white border-t flex flex-col gap-2">
            <button @click="downloadPDF()" class="w-full bg-blue-600 text-white py-3 rounded font-bold hover:bg-blue-700 shadow flex justify-center items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>DOWNLOAD PDF</span>
            </button>
        </div>
    </div>

    <!-- Right Content: Paper Preview -->
    <div class="w-full lg:w-2/3 bg-gray-500 overflow-y-auto p-8 flex justify-center">
        <div id="paperContent" class="paper relative min-h-[297mm]">
            
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
                        <tr><td colspan="2" class="text-center font-bold bg-gray-100">BD-MLI</td></tr>
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