<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $document->title }} - Print</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000000;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        td {
            vertical-align: top;
            padding: 2px 0;
        }
        .info-table td {
            padding-bottom: 5px;
        }
        .list-numbered {
            padding-left: 25px;
            margin: 0 0 10px 0;
        }
        .list-numbered li {
            margin-bottom: 4px;
        }
        .text-justify {
            text-align: justify;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .uppercase {
            text-transform: uppercase;
        }
        .underline {
            text-decoration: underline;
        }
        .signature-section {
            float: right;
            width: 280px;
            text-align: center;
            margin-top: 30px;
        }
        .signature-image {
            height: 96px; /* 24 * 4 approx */
            width: 100%;
            object-fit: contain;
            display: block;
            margin: 5px auto;
        }
        .paraf-box {
            margin-top: 30px;
            border: 1px solid black;
            width: 150px;
            font-size: 10pt;
            border-collapse: collapse;
            clear: both;
        }
        .paraf-box td {
            border: 1px solid black;
            padding: 2px 5px;
        }
        .bg-gray {
            background-color: #f3f4f6 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        /* SPPD Specific */
        .sppd-label { width: 100px; font-weight: bold; }
        .sppd-colon { width: 20px; text-align: center; }
        
        /* Utility to clear floats */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container clearfix">
        
        <!-- Logo -->
        <div style="margin-bottom: 10px;">
             <img src="{{ asset('images/logo_asa.png') }}" alt="Logo" style="height: 60px;">
        </div>

        @if($document->type === 'nota')
            <!-- NOTA DINAS -->
            <div class="header">
                <h1>NOTA DINAS</h1>
                <p>NOMOR {{ $document->content_data['docNumber'] ?? '...' }}</p>
            </div>

            <table class="info-table">
                <tr><td width="100">Kepada</td><td width="20">:</td><td>Yth. {{ $document->content_data['to'] ?? '...' }}</td></tr>
                <tr><td>Dari</td><td>:</td><td>{{ $document->content_data['from'] ?? '...' }}</td></tr>
                <tr><td>Lampiran</td><td>:</td><td>{{ $document->content_data['attachment'] ?? '...' }}</td></tr>
                <tr><td>Hal</td><td>:</td><td class="font-bold">{{ $document->content_data['subject'] ?? '...' }}</td></tr>
            </table>

            <div style="margin-bottom: 15px;">
                <p style="margin-bottom: 5px;">Berdasarkan:</p>
                <ol class="list-numbered text-justify">
                    @forelse($document->content_data['basis'] ?? [] as $item)
                        @if(!empty($item))
                            <li>{{ $item }}</li>
                        @endif
                    @empty
                        <li style="list-style: none">...</li>
                    @endforelse
                </ol>
            </div>

            <div class="text-justify" style="margin-bottom: 20px; white-space: pre-wrap;">{{ $document->content_data['content'] ?? '...' }}</div>

            <p style="margin-bottom: 30px;">Demikian disampaikan dan untuk dijadikan periksa.</p>

            <div class="signature-section">
                <p style="margin-bottom: 4px;">{{ $document->content_data['location'] ?? 'Jakarta' }}, {{ isset($document->content_data['date']) ? \Carbon\Carbon::parse($document->content_data['date'])->isoFormat('D MMMM Y') : '...' }}</p>
                <p class="font-bold uppercase" style="margin: 0;">{{ $document->content_data['signerPosition'] ?? '...' }}</p>
                <p class="font-bold uppercase" style="margin: 0;">{{ $document->content_data['division'] ?? '...' }}</p>
                
                <div style="height: 100px; display: flex; align-items: center; justify-content: center;">
                    @if(!empty($document->content_data['signature']))
                        <img src="{{ $document->content_data['signature'] }}" class="signature-image" alt="Tanda Tangan">
                    @endif
                </div>
                
                <p class="font-bold uppercase underline">{{ $document->content_data['signerName'] ?? '...' }}</p>
            </div>

            <div style="clear: both;"></div>
            <table class="paraf-box">
                <tr><td colspan="2" class="text-center font-bold bg-gray">BD-MLI</td></tr>
                <tr><td rowspan="2" class="text-center" width="50%" style="vertical-align: middle;">Paraf</td><td class="text-center">Staff</td></tr>
                <tr><td class="text-center" height="30"> </td></tr>
            </table>

        @elseif($document->type === 'sppd')
            <!-- SPPD -->
            <div class="header" style="margin-bottom: 30px;">
                <h1>SURAT PERINTAH PERJALANAN DINAS</h1>
                <p>NOMOR {{ $document->content_data['docNumber'] ?? '...' }}</p>
            </div>

            <table>
                <tr>
                    <td class="sppd-label">Menimbang</td>
                    <td class="sppd-colon">:</td>
                    <td class="text-justify">{{ $document->content_data['weigh'] ?? '...' }}</td>
                </tr>
            </table>

            <table>
                <tr>
                    <td class="sppd-label">Mengingat</td>
                    <td class="sppd-colon">:</td>
                    <td>
                        <ol class="list-numbered" style="margin: 0; padding-left: 20px;">
                            @forelse($document->content_data['remembers'] ?? [] as $item)
                                @if(!empty($item))
                                    <li>{{ $item }}</li>
                                @endif
                            @empty
                                <li style="list-style: none">...</li>
                            @endforelse
                        </ol>
                    </td>
                </tr>
            </table>

            <div class="text-center font-bold" style="margin: 20px 0;">Memberi Perintah</div>

            <table>
                <tr>
                    <td class="sppd-label">Kepada</td>
                    <td class="sppd-colon"></td>
                    <td class="font-bold">{{ $document->content_data['to'] ?? '...' }}</td>
                </tr>
            </table>

            <table>
                <tr>
                    <td class="sppd-label">Untuk</td>
                    <td class="sppd-colon">:</td>
                    <td>
                        <ol class="list-numbered" style="margin: 0; padding-left: 20px;">
                            <li style="margin-bottom: 10px;">{{ $document->content_data['task'] ?? '...' }}</li>
                            
                            <li style="margin-bottom: 10px;">
                                Perjalanan dinas dilaksanakan, sebagai berikut:
                                <table style="width: 100%; margin-top: 5px;">
                                    <tr><td width="100">Tujuan</td><td width="10">:</td><td>{{ $document->content_data['destination'] ?? '...' }}</td></tr>
                                    <tr><td>Berangkat</td><td>:</td><td>{{ isset($document->content_data['dateGo']) ? \Carbon\Carbon::parse($document->content_data['dateGo'])->isoFormat('D MMMM Y') : '...' }}</td></tr>
                                    <tr><td>Kembali</td><td>:</td><td>{{ isset($document->content_data['dateBack']) ? \Carbon\Carbon::parse($document->content_data['dateBack'])->isoFormat('D MMMM Y') : '...' }}</td></tr>
                                    <tr><td>Transportasi</td><td>:</td><td>{{ $document->content_data['transport'] ?? '...' }}</td></tr>
                                </table>
                            </li>

                            <li class="text-justify" style="margin-bottom: 10px;">{{ $document->content_data['funding'] ?? '...' }}</li>
                            <li class="text-justify" style="margin-bottom: 10px;">{{ $document->content_data['report'] ?? '...' }}</li>
                            <li class="text-justify" style="margin-bottom: 10px;">{{ $document->content_data['closing'] ?? '...' }}</li>
                        </ol>
                    </td>
                </tr>
            </table>

            <div class="signature-section">
                <p style="margin-bottom: 4px;">Dikeluarkan di {{ $document->content_data['location'] ?? '...' }}</p>
                <p style="margin-bottom: 4px;">pada tanggal {{ isset($document->content_data['signDate']) ? \Carbon\Carbon::parse($document->content_data['signDate'])->isoFormat('D MMMM Y') : '...' }}</p>
                <p class="font-bold uppercase" style="margin: 0;">DIREKSI,</p>
                <p class="font-bold uppercase" style="margin: 0;">{{ $document->content_data['signerPosition'] ?? '...' }}</p>

                <div style="height: 100px; display: flex; align-items: center; justify-content: center;">
                    @if(!empty($document->content_data['signature']))
                        <img src="{{ $document->content_data['signature'] }}" class="signature-image" alt="Tanda Tangan">
                    @endif
                </div>

                <p class="font-bold uppercase underline">{{ $document->content_data['signerName'] ?? '...' }}</p>
            </div>

            <div style="clear: both;"></div>
            
            <div style="margin-top: 30px; font-size: 10pt;">
                <p class="font-bold underline" style="margin-bottom: 5px;">Tembusan:</p>
                <ol class="list-numbered" style="margin-left: 20px;">
                    @forelse($document->content_data['ccs'] ?? [] as $item)
                        @if(!empty($item))
                            <li>{{ $item }}</li>
                        @endif
                    @empty
                        <li style="list-style: none">...</li>
                    @endforelse
                </ol>
            </div>
        @endif

    </div>
</body>
</html>