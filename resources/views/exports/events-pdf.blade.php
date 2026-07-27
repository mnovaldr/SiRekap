<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Event MCC - {{ $period }}</title>
    <style>
        @page { 
            margin: 1.2cm 1cm; 
            size: A4 landscape;
        }
        
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 7.5px; 
            color: #1f2937; 
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        
        .header-container { 
            margin-bottom: 15px; 
            border-bottom: 2px solid #136EF0; 
            padding-bottom: 10px;
            width: 100%;
        }

        table.header-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
        }
        
        table.header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        
        .logo { 
            width: 65px; 
            height: auto;
        }
        
        .header-text { 
            text-align: center; 
        }
        
        .header-text h1 { 
            margin: 0 0 4px 0; 
            font-size: 16px; 
            font-weight: bold; 
            color: #136EF0; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
        }
        
        .header-text h2 { 
            margin: 0 0 3px 0; 
            font-size: 11px; 
            font-weight: bold; 
            color: #0f172a; 
            text-transform: uppercase;
        }
        
        .header-text p { 
            margin: 0; 
            font-size: 8px; 
            color: #475569; 
        }
        
        .info-line {
            text-align: center;
            padding: 8px;
            margin-bottom: 12px;
            font-size: 8px;
            color: #0f172a;
            font-weight: bold;
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        
        table.data-table { 
            width: 100%; 
            border-collapse: collapse; 
            border: 1px solid #cbd5e1;
            table-layout: fixed;
            word-wrap: break-word;
        }
        
        table.data-table th { 
            background: #136EF0;
            color: white; 
            font-weight: bold; 
            text-transform: uppercase; 
            font-size: 7px; 
            padding: 8px 4px; 
            border: 1px solid #0c5bb5;
            text-align: center;
            vertical-align: middle;
        }
        
        table.data-table td { 
            padding: 6px 4px; 
            border: 1px solid #cbd5e1; 
            vertical-align: top;
            font-size: 7.5px;
            color: #334155;
        }
        
        table.data-table tbody tr:nth-child(even) { 
            background-color: #f8fafc; 
        }
        
        .footer { 
            margin-top: 15px; 
            padding-top: 10px; 
            border-top: 1px solid #cbd5e1; 
            font-size: 7px; 
            color: #64748b;
            text-align: center;
        }
        
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; color: #0f172a; }
        
        .sub-text {
            color: #64748b;
            font-size: 6.5px;
            margin-top: 2px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="header-container">
        <table class="header-table">
            <tr>
                <td style="width: 80px; text-align: left;">
                    <img src="{{ public_path('images/logo.png') }}" class="logo" alt="MCC Logo">
                </td>
                <td class="header-text">
                    <h1>Laporan Rekapitulasi Event</h1>
                    <h2>Malang Creative Center (MCC)</h2>
                    <p>Jl. A. Yani No. 53, Blimbing, Kota Malang, Jawa Timur 65118</p>
                </td>
                <td style="width: 80px;"></td> <!-- Spacer to center the text -->
            </tr>
        </table>
    </div>

    <div class="info-line">
        Periode: {{ $period }} &nbsp;|&nbsp; 
        Total Event: {{ number_format(count($data), 0, ',', '.') }} &nbsp;|&nbsp; 
        Total Peserta: {{ number_format(array_sum(array_column($data, 'jumlah_peserta')), 0, ',', '.') }} &nbsp;|&nbsp; 
        Dicetak: {{ date('d/m/Y H:i') }} WIB
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%">NO</th>
                <th style="width: 17%">JUDUL KEGIATAN</th>
                <th style="width: 15%">PENYELENGGARA</th>
                <th style="width: 14%">BIDANG & SUB SEKTOR</th>
                <th style="width: 11%">LOKASI</th>
                <th style="width: 8%">TANGGAL</th>
                <th style="width: 6%">PESERTA</th>
                <th style="width: 13%">PROGRAM SDG</th>
                <th style="width: 13%">PIC & KONTAK</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-left font-bold">{{ $row['judul_kegiatan'] ?? '-' }}</td>
                <td class="text-left">
                    {{ $row['nama_organisasi'] ?? '-' }}
                    @if(isset($row['komersial']) && $row['komersial'] !== '-')
                        <span class="sub-text">({{ $row['komersial'] }})</span>
                    @endif
                </td>
                <td class="text-left">
                    <span class="font-bold">{{ $row['bidang'] ?? '-' }}</span>
                    @if(isset($row['sub_sektor']) && $row['sub_sektor'] !== '-')
                        <span class="sub-text">{{ $row['sub_sektor'] }}</span>
                    @endif
                </td>
                <td class="text-left">
                    {{ $row['lokasi'] ?? '-' }}
                    @if(isset($row['lantai']) && $row['lantai'] !== '-')
                        <span class="sub-text">Lantai {{ $row['lantai'] }}</span>
                    @endif
                </td>
                <td class="text-center">
                    @if(isset($row['tanggal']))
                        <span class="font-bold">{{ date('d/m/Y', strtotime($row['tanggal'])) }}</span>
                    @else
                        -
                    @endif
                </td>
                <td class="text-center font-bold" style="color: #136EF0;">
                    @if(isset($row['jumlah_peserta']) && is_numeric($row['jumlah_peserta']))
                        {{ number_format($row['jumlah_peserta'], 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-left sub-text" style="font-size: 6.5px; line-height: 1.5;">{{ $row['program_sdgs'] ?? '-' }}</td>
                <td class="text-left">
                    <span class="font-bold">{{ $row['pic'] ?? '-' }}</span>
                    @if(isset($row['no_telepon']) && $row['no_telepon'] !== '-')
                        <span class="sub-text">WA: {{ $row['no_telepon'] }}</span>
                    @endif
                    @if(isset($row['email']) && $row['email'] !== '-')
                        <span class="sub-text">Email: {{ $row['email'] }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: 20px;">
                    Tidak ada data event untuk periode ini
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <strong>SiREKAP MCC</strong> - Sistem Rekapitulasi Malang Creative Center<br>
        Dokumen ini dihasilkan secara otomatis oleh sistem. Dicetak pada {{ date('d F Y, H:i:s') }} WIB
    </div>
</body>
</html>
