<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Analitik Ekraf MCC - {{ $period }}</title>
    <style>
        @page { 
            margin: 1.2cm; 
            size: A4 portrait;
        }
        
        body { 
            font-family: 'DejaVu Sans', 'Arial', sans-serif; 
            font-size: 9px; 
            color: #1f2937; 
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #136EF0;
            padding: 12px;
            margin-bottom: 18px;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .logo {
            width: 75px;
            height: auto;
        }
        
        .header h1 {
            margin: 6px 0 5px 0;
            font-size: 18px;
            font-weight: 900;
            color: #136EF0;
            text-transform: uppercase;
        }
        
        .header h2 {
            margin: 0 0 5px 0;
            font-size: 12px;
            font-weight: 700;
            color: #0c4a6e;
        }
        
        .header p {
            margin: 0;
            font-size: 9px;
            color: #475569;
            font-weight: 600;
        }
        
        .summary-box {
            border: 2px solid #136EF0;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 16px;
            text-align: center;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
        }
        
        .summary-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 8px;
            border-right: 1px solid #cbd5e1;
        }
        
        .summary-item:last-child {
            border-right: none;
        }
        
        .summary-label {
            font-size: 8px;
            color: #475569;
            text-transform: uppercase;
            font-weight: 700;
            display: block;
            margin-bottom: 5px;
        }
        
        .summary-value {
            font-size: 22px;
            font-weight: 900;
            color: #136EF0;
            display: block;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #136EF0;
            margin: 20px 0 10px 0;
            padding: 8px 10px;
            border-left: 4px solid #136EF0;
            background: #f8fafc;
            text-transform: uppercase;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            border: 1px solid #94a3b8;
        }
        
        th {
            background: #136EF0;
            color: white;
            font-weight: 700;
            font-size: 8px;
            padding: 8px 6px;
            text-align: left;
            text-transform: uppercase;
            border: 1px solid #0c5bb5;
        }
        
        td {
            padding: 6px;
            border: 1px solid #cbd5e1;
            font-size: 8px;
            vertical-align: middle;
        }
        
        tr:nth-child(even) {
            background: #f8fafc;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        
        .footer {
            margin-top: 20px;
            padding: 10px;
            border-top: 2px solid #136EF0;
            text-align: center;
            font-size: 7.5px;
            color: #64748b;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .highlight-row {
            background: #dbeafe !important;
            font-weight: 700;
            color: #0c4a6e;
        }
        
        .rank-badge {
            display: inline-block;
            min-width: 20px;
            height: 20px;
            background: #136EF0;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 20px;
            font-weight: 900;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-container">
            @php
                $logoPath = 'D:/Kuliah/Magang/SPK/mcc-rekap-backend/public/images/logo.png';
                $logoData = '';
                if (file_exists($logoPath)) {
                    $logoData = base64_encode(file_get_contents($logoPath));
                }
            @endphp
            @if($logoData)
                <img src="data:image/png;base64,{{ $logoData }}" class="logo" alt="MCC Logo">
            @endif
        </div>
        <h1>LAPORAN ANALITIK EKONOMI KREATIF</h1>
        <h2>MALANG CREATIVE CENTER (MCC)</h2>
        <p>Jl. A. Yani No. 53, Blimbing, Malang, Jawa Timur 65118</p>
        <p>Periode: {{ $period }} | Dicetak: {{ date('d/m/Y H:i') }} WIB</p>
    </div>

    {{-- Summary Statistics --}}
    <div class="summary-box">
        <div class="summary-grid">
            <div class="summary-item">
                <span class="summary-label">Total Event</span>
                <span class="summary-value">{{ number_format($data['totalEvents']) }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total Pengunjung</span>
                <span class="summary-value">{{ number_format($data['totalPengunjung']) }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Pelaku Unik</span>
                <span class="summary-value">{{ number_format($data['totalPelaku']) }}</span>
            </div>
        </div>
    </div>

    {{-- Sub Sektor --}}
    <div class="section-title">Distribusi Sub Sektor Ekonomi Kreatif</div>
    <table>
        <thead>
            <tr>
                <th style="width: 8%" class="text-center">RANK</th>
                <th style="width: 52%">SUB SEKTOR</th>
                <th style="width: 20%" class="text-center">JUMLAH EVENT</th>
                <th style="width: 20%" class="text-center">PERSENTASE</th>
            </tr>
        </thead>
        <tbody>
            @php $totalSubSektor = $data['kategoriEkraf']->sum('total'); @endphp
            @foreach($data['kategoriEkraf']->take(10) as $index => $item)
            <tr>
                <td class="text-center">
                    <span class="rank-badge">{{ $index + 1 }}</span>
                </td>
                <td class="font-bold">{{ $item->label }}</td>
                <td class="text-center font-bold">{{ number_format($item->total) }}</td>
                <td class="text-center font-bold">
                    {{ $totalSubSektor > 0 ? number_format(($item->total / $totalSubSektor) * 100, 1) : 0 }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Jenis Kegiatan --}}
    <div class="section-title">Jenis Kegiatan</div>
    <table>
        <thead>
            <tr>
                <th style="width: 8%" class="text-center">RANK</th>
                <th style="width: 52%">JENIS KEGIATAN</th>
                <th style="width: 20%" class="text-center">JUMLAH EVENT</th>
                <th style="width: 20%" class="text-center">PERSENTASE</th>
            </tr>
        </thead>
        <tbody>
            @php $totalKegiatan = $data['kategoriKegiatan']->sum('total'); @endphp
            @foreach($data['kategoriKegiatan']->take(10) as $index => $item)
            <tr>
                <td class="text-center">
                    <span class="rank-badge">{{ $index + 1 }}</span>
                </td>
                <td class="font-bold">{{ $item->label }}</td>
                <td class="text-center font-bold">{{ number_format($item->total) }}</td>
                <td class="text-center font-bold">
                    {{ $totalKegiatan > 0 ? number_format(($item->total / $totalKegiatan) * 100, 1) : 0 }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    {{-- Bidang Pelaku --}}
    <div class="section-title">Bidang Pelaku</div>
    <table>
        <thead>
            <tr>
                <th style="width: 8%" class="text-center">RANK</th>
                <th style="width: 52%">BIDANG</th>
                <th style="width: 20%" class="text-center">JUMLAH EVENT</th>
                <th style="width: 20%" class="text-center">PERSENTASE</th>
            </tr>
        </thead>
        <tbody>
            @php $totalPelaku = $data['kategoriPelaku']->sum('total'); @endphp
            @foreach($data['kategoriPelaku'] as $index => $item)
            <tr>
                <td class="text-center">
                    <span class="rank-badge">{{ $index + 1 }}</span>
                </td>
                <td class="font-bold">{{ $item->label }}</td>
                <td class="text-center font-bold">{{ number_format($item->total) }}</td>
                <td class="text-center font-bold">
                    {{ $totalPelaku > 0 ? number_format(($item->total / $totalPelaku) * 100, 1) : 0 }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Rekap Bulanan --}}
    @foreach($data['bulanan'] as $year => $yearGroup)
    @php 
        $monthlyData = $yearGroup['data'];
        $totals = $yearGroup['totals'];
    @endphp
    <div class="section-title">Rekapitulasi Bulanan Tahun {{ $year }}</div>
    <table>
        <thead>
            <tr>
                <th style="width: 16%">BULAN</th>
                <th style="width: 14%" class="text-center">EVENT</th>
                <th style="width: 18%" class="text-center">PENGUNJUNG</th>
                <th style="width: 16%" class="text-center">KOMERSIAL</th>
                <th style="width: 18%" class="text-center">NON KOMERSIAL</th>
                <th style="width: 18%" class="text-center">PELAKU</th>
            </tr>
        </thead>
        <tbody>
            @foreach($monthlyData as $month)
            <tr>
                <td class="font-bold">{{ $month->bulan }}</td>
                <td class="text-center font-bold">{{ number_format($month->total_event) }}</td>
                <td class="text-center">{{ number_format($month->total_pengunjung) }}</td>
                <td class="text-center">{{ number_format($month->total_komersil) }}</td>
                <td class="text-center">{{ number_format($month->total_non_komersil) }}</td>
                <td class="text-center">{{ number_format($month->total_pelaku) }}</td>
            </tr>
            @endforeach
            <tr class="highlight-row">
                <td class="font-bold">TOTAL {{ $year }}</td>
                <td class="text-center font-bold">{{ number_format($totals['event']) }}</td>
                <td class="text-center font-bold">{{ number_format($totals['pengunjung']) }}</td>
                <td class="text-center font-bold">{{ number_format($monthlyData->sum('total_komersil')) }}</td>
                <td class="text-center font-bold">{{ number_format($monthlyData->sum('total_non_komersil')) }}</td>
                <td class="text-center font-bold">{{ number_format($totals['pelaku']) }}</td>
            </tr>
        </tbody>
    </table>
    @endforeach

    <div class="footer">
        <strong>SiREKAP MCC</strong> - Sistem Rekapitulasi Malang Creative Center<br>
        Jl. A. Yani No. 53, Blimbing, Malang, Jawa Timur 65118<br>
        Dokumen digenerate pada {{ date('d/m/Y H:i:s') }} WIB
    </div>
</body>
</html>
