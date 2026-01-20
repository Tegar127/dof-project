@extends('layouts.app')

@section('title', 'ASABRI Document Generator')

@section('content')
<div class="flex flex-col lg:flex-row h-screen overflow-hidden">
    
    <!-- Sidebar Input -->
    <div class="w-full lg:w-1/3 bg-white p-0 flex flex-col border-r border-gray-200 shadow-lg z-10 h-full">
        
        <!-- Tab Navigation -->
        <div class="flex p-4 bg-gray-50 border-b gap-2">
            <button id="tabNota" class="flex-1 py-2 px-4 rounded font-bold border transition tab-active">Nota Dinas</button>
            <button id="tabSppd" class="flex-1 py-2 px-4 rounded font-bold border transition tab-inactive">Surat Perintah</button>
        </div>

        <!-- Form Content -->
        <div class="p-6 overflow-y-auto flex-grow">
            <h2 class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">Input Data</h2>
            
            <form id="mainForm" class="space-y-4">
                
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Nomor Dokumen</label>
                    <input type="text" id="docNumber" class="w-full p-2 border border-gray-300 rounded" placeholder=".../...">
                </div>

                <!-- Nota Dinas Inputs -->
                <div id="inputsNota" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4">
                        <input type="text" id="notaTo" class="w-full p-2 border border-gray-300 rounded" placeholder="Kepada (Yth...)">
                        <input type="text" id="notaFrom" class="w-full p-2 border border-gray-300 rounded" placeholder="Dari">
                        <input type="text" id="notaAtt" class="w-full p-2 border border-gray-300 rounded" placeholder="Lampiran">
                        <textarea id="notaSubject" rows="2" class="w-full p-2 border border-gray-300 rounded" placeholder="Hal / Perihal"></textarea>
                    </div>

                    <hr class="border-gray-200">
                    <label class="block text-sm font-medium text-gray-700">Berdasarkan (Poin)</label>
                    <div id="notaBasisContainer" class="space-y-2">
                        <div class="flex gap-2 item-row"><input type="text" class="nota-basis-input w-full p-2 border border-gray-300 rounded" placeholder="Poin..."></div>
                    </div>
                    <button type="button" id="btnAddNotaBasis" class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded hover:bg-blue-100">+ Tambah Poin</button>

                    <label class="block text-sm font-medium text-gray-700 mt-2">Isi Paragraf</label>
                    <textarea id="notaContent" rows="4" class="w-full p-2 border border-gray-300 rounded" placeholder="Sehubungan dengan..."></textarea>

                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" id="notaLoc" class="w-full p-2 border border-gray-300 rounded" placeholder="Lokasi (Jakarta)">
                        <input type="date" id="notaDate" class="w-full p-2 border border-gray-300 rounded">
                    </div>
                    <input type="text" id="notaPos" class="w-full p-2 border border-gray-300 rounded" placeholder="Jabatan">
                    <input type="text" id="notaDiv" class="w-full p-2 border border-gray-300 rounded" placeholder="Divisi">
                    <input type="text" id="notaName" class="w-full p-2 border border-gray-300 rounded" placeholder="Nama Penandatangan">
                </div>

                <!-- SPPD Inputs -->
                <div id="inputsSppd" class="hidden space-y-4">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Menimbang</label>
                        <textarea id="sppdWeigh" rows="3" class="w-full p-2 border border-gray-300 rounded" placeholder="bahwa dalam rangka..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mengingat (List)</label>
                        <div id="sppdRememberContainer" class="space-y-2">
                            <div class="flex gap-2 item-row"><input type="text" class="sppd-rem-input w-full p-2 border border-gray-300 rounded" placeholder="Peraturan..."></div>
                        </div>
                        <button type="button" id="btnAddSppdRemember" class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded hover:bg-blue-100">+ Tambah</button>
                    </div>

                    <hr class="border-gray-200">
                    <input type="text" id="sppdTo" class="w-full p-2 border border-gray-300 rounded" placeholder="Kepada (Nama & Jabatan)">

                    <div class="bg-gray-50 p-3 rounded border border-gray-200">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Detail Perintah (Untuk)</label>
                        
                        <label class="text-xs text-gray-500">Poin 1: Kegiatan</label>
                        <input type="text" id="sppdTask" class="w-full p-2 border border-gray-300 rounded mb-2" placeholder="Melaksanakan kegiatan...">
                        
                        <label class="text-xs text-gray-500">Poin 2: Detail Perjalanan</label>
                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <input type="text" id="sppdDest" class="p-2 border border-gray-300 rounded" placeholder="Tujuan (Denpasar)">
                            <input type="text" id="sppdTransport" class="p-2 border border-gray-300 rounded" placeholder="Pesawat Udara">
                        </div>
                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <div><span class="text-xs">Berangkat</span><input type="date" id="sppdDateGo" class="w-full p-2 border border-gray-300 rounded"></div>
                            <div><span class="text-xs">Kembali</span><input type="date" id="sppdDateBack" class="w-full p-2 border border-gray-300 rounded"></div>
                        </div>

                        <label class="text-xs text-gray-500">Poin 3, 4, 5 (Standar/Edit)</label>
                        <textarea id="sppdFunding" rows="2" class="w-full p-2 border border-gray-300 rounded mb-1" placeholder="Biaya dibebankan..."></textarea>
                        <textarea id="sppdReport" rows="2" class="w-full p-2 border border-gray-300 rounded mb-1" placeholder="Melaporkan pelaksanaan..."></textarea>
                        <textarea id="sppdClose" rows="1" class="w-full p-2 border border-gray-300 rounded" placeholder="Melaksanakan dengan tanggung jawab."></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" id="sppdLoc" class="w-full p-2 border border-gray-300 rounded" placeholder="Lokasi">
                        <input type="date" id="sppdSignDate" class="w-full p-2 border border-gray-300 rounded">
                    </div>
                    <input type="text" id="sppdSignPos" class="w-full p-2 border border-gray-300 rounded" placeholder="DIREKTUR UTAMA">
                    <input type="text" id="sppdSignName" class="w-full p-2 border border-gray-300 rounded" placeholder="Nama Penandatangan">

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tembusan</label>
                        <div id="sppdCCContainer" class="space-y-2">
                            <div class="flex gap-2 item-row"><input type="text" class="sppd-cc-input w-full p-2 border border-gray-300 rounded" placeholder="Direksi..."></div>
                        </div>
                        <button type="button" id="btnAddSppdCC" class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded hover:bg-blue-100">+ Tambah</button>
                    </div>

                </div>
            </form>
        </div>

        <!-- Actions -->
        <div class="p-4 bg-white border-t border-gray-200 flex flex-col gap-2">
            <button id="btnDownload" class="w-full bg-blue-600 text-white py-3 rounded font-bold hover:bg-blue-700 shadow flex justify-center items-center gap-2 cursor-pointer">
                <span>DOWNLOAD PDF</span>
            </button>
            <button id="btnReset" class="w-full bg-white text-gray-700 border border-gray-300 py-2 rounded font-bold hover:bg-gray-50 flex justify-center items-center gap-2 text-sm cursor-pointer">
                <span>RESET ALL</span>
            </button>
        </div>
    </div>

    <!-- Preview Area -->
    <div class="w-full lg:w-2/3 bg-gray-500 overflow-y-auto p-8 flex justify-center">
        
        <div id="paperContent" class="paper relative">
            
            <div class="flex items-center mb-2">
                <!-- Using local asset if available, otherwise fallback to URL but note CORS issue -->
                <img src="https://pensiun.asabri.co.id/resources/img/logo_asa.png" alt="ASABRI Logo" class="h-16 mb-2" crossorigin="anonymous">
            </div>

            <!-- Preview Nota -->
            <div id="previewNota">
                <div class="paper-header">
                    <h1 class="font-bold text-lg uppercase tracking-wide">NOTA DINAS</h1>
                    <p>NOMOR <span class="prev-docNum">...</span></p>
                </div>

                <table class="info-table w-full mb-6">
                    <tr><td width="100">Kepada</td><td width="20">:</td><td>Yth. <span id="prevNotaTo">...</span></td></tr>
                    <tr><td>Dari</td><td>:</td><td><span id="prevNotaFrom">...</span></td></tr>
                    <tr><td>Lampiran</td><td>:</td><td><span id="prevNotaAtt">...</span></td></tr>
                    <tr><td>Hal</td><td>:</td><td class="font-bold"><span id="prevNotaSubject">...</span></td></tr>
                </table>

                <div class="mb-4">
                    <p class="mb-2">Berdasarkan:</p>
                    <ol id="prevNotaBasisList" class="list-numbered text-justify"></ol>
                </div>

                <div class="mb-8 text-justify leading-relaxed">
                    <p id="prevNotaContent" style="white-space: pre-wrap;">...</p>
                </div>

                <p class="mb-8">Demikian disampaikan dan untuk dijadikan periksa.</p>

                <div class="signature-section">
                    <p class="mb-1"><span id="prevNotaLoc">...</span>, <span id="prevNotaDate">...</span></p>
                    <p class="font-bold uppercase mb-0"><span id="prevNotaPos">...</span></p>
                    <p class="font-bold uppercase mb-16"><span id="prevNotaDiv">...</span></p>
                    <p class="font-bold uppercase underline"><span id="prevNotaName">...</span></p>
                </div>

                <div style="clear: both;"></div>
                <table class="paraf-box">
                    <tr><td colspan="2" class="text-center font-bold bg-gray-100">BD-MLI</td></tr>
                    <tr><td rowspan="2" class="text-center align-middle" width="50%">Paraf</td><td class="text-center">Staff</td></tr>
                    <tr><td class="text-center" height="30"> </td></tr>
                </table>
            </div>

            <!-- Preview SPPD -->
            <div id="previewSppd" class="hidden">
                <div class="paper-header" style="margin-bottom: 30px;">
                    <h1 class="font-bold text-lg uppercase tracking-wide">SURAT PERINTAH PERJALANAN DINAS</h1>
                    <p>NOMOR <span class="prev-docNum">...</span></p>
                </div>

                <table class="sppd-table">
                    <tr>
                        <td class="sppd-label">Menimbang</td>
                        <td class="sppd-colon">:</td>
                        <td><span id="prevSppdWeigh">...</span></td>
                    </tr>
                </table>

                <table class="sppd-table">
                    <tr>
                        <td class="sppd-label">Mengingat</td>
                        <td class="sppd-colon">:</td>
                        <td>
                            <ol id="prevSppdRememberList" class="list-numbered" style="margin-top: 0; margin-bottom: 0; padding-left: 15px;"></ol>
                        </td>
                    </tr>
                </table>

                <div class="text-center font-bold my-6">Memberi Perintah</div>

                <table class="sppd-table">
                    <tr>
                        <td class="sppd-label">Kepada</td>
                        <td class="sppd-colon"></td>
                        <td class="font-bold"><span id="prevSppdTo">...</span></td>
                    </tr>
                </table>

                <table class="sppd-table">
                    <tr>
                        <td class="sppd-label">Untuk</td>
                        <td class="sppd-colon">:</td>
                        <td>
                            <ol class="list-numbered" style="margin-top: 0; padding-left: 15px;">
                                <li class="mb-2"><span id="prevSppdTask">...</span></li>
                                
                                <li class="mb-2">
                                    Perjalanan dinas dilaksanakan, sebagai berikut:
                                    <table class="sub-table w-full mt-1">
                                        <tr><td width="100">Tujuan</td><td width="10">:</td><td><span id="prevSppdDest">...</span></td></tr>
                                        <tr><td>Berangkat</td><td>:</td><td><span id="prevSppdDateGo">...</span></td></tr>
                                        <tr><td>Kembali</td><td>:</td><td><span id="prevSppdDateBack">...</span></td></tr>
                                        <tr><td>Transportasi</td><td>:</td><td><span id="prevSppdTransport">...</span></td></tr>
                                    </table>
                                </li>

                                <li class="mb-2 text-justify"><span id="prevSppdFunding">...</span></li>
                                <li class="mb-2 text-justify"><span id="prevSppdReport">...</span></li>
                                <li class="mb-2 text-justify"><span id="prevSppdClose">...</span></li>
                            </ol>
                        </td>
                    </tr>
                </table>

                <div class="signature-section">
                    <p class="mb-1">Dikeluarkan di <span id="prevSppdLoc">...</span></p>
                    <p class="mb-1">pada tanggal <span id="prevSppdSignDate">...</span></p>
                    <p class="font-bold uppercase mb-0">DIREKSI,</p>
                    <p class="font-bold uppercase mb-16"><span id="prevSppdSignPos">...</span></p>
                    <p class="font-bold uppercase underline"><span id="prevSppdSignName">...</span></p>
                </div>

                <div style="clear: both;"></div>
                
                <div class="mt-8 text-sm">
                    <p class="font-bold underline mb-1">Tembusan:</p>
                    <ol id="prevSppdCCList" class="list-numbered" style="margin-left: 15px;"></ol>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
