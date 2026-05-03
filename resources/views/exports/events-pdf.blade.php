<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Event MCC - {{ $period }}</title>
    <style>
        @page { 
            margin: 1cm 0.8cm; 
            size: A4 landscape;
        }
        
        body { 
            font-family: 'DejaVu Sans', 'Arial', sans-serif; 
            font-size: 8px; 
            color: #1f2937; 
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        
        .header-container { 
            margin-bottom: 12px; 
            border-bottom: 2px solid #136EF0; 
            padding: 10px;
            display: table;
            width: 100%;
        }
        
        .logo-section {
            display: table-cell;
            width: 70px;
            vertical-align: middle;
        }
        
        .logo { 
            width: 60px; 
            height: auto;
        }
        
        .header-text { 
            display: table-cell;
            text-align: center; 
            vertical-align: middle;
            padding-left: 10px;
        }
        
        .header-text h1 { 
            margin: 0 0 3px 0; 
            font-size: 16px; 
            font-weight: 900; 
            color: #136EF0; 
            text-transform: uppercase; 
        }
        
        .header-text h2 { 
            margin: 0 0 3px 0; 
            font-size: 11px; 
            font-weight: 700; 
            color: #0c4a6e; 
        }
        
        .header-text p { 
            margin: 0; 
            font-size: 8px; 
            color: #475569; 
            font-weight: 600; 
        }
        
        .info-line {
            text-align: center;
            padding: 6px;
            margin-bottom: 10px;
            font-size: 8px;
            color: #0c4a6e;
            font-weight: 600;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            border: 1px solid #94a3b8;
        }
        
        th { 
            background: #136EF0;
            color: white; 
            font-weight: 700; 
            text-transform: uppercase; 
            font-size: 7px; 
            padding: 6px 3px; 
            border: 1px solid #0c5bb5;
            text-align: center;
        }
        
        td { 
            padding: 4px 3px; 
            border: 1px solid #cbd5e1; 
            vertical-align: middle;
            font-size: 7px;
        }
        
        tbody tr:nth-child(odd) { 
            background-color: #ffffff; 
        }
        
        tbody tr:nth-child(even) { 
            background-color: #f8fafc; 
        }
        
        .footer { 
            margin-top: 10px; 
            padding: 6px; 
            border-top: 2px solid #136EF0; 
            font-size: 6.5px; 
            color: #64748b;
            text-align: center;
        }
        
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        
        .status-badge { 
            font-weight: 700;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 6px;
            display: inline-block;
        }
        
        .status-valid { 
            color: #059669; 
            background-color: #d1fae5;
        }
        
        .status-review { 
            color: #d97706; 
            background-color: #fef3c7;
        }
        
        .status-duplikat { 
            color: #dc2626; 
            background-color: #fee2e2;
        }
    </style>
</head>
<body>
    <div class="header-container">
        <div class="logo-section">
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
        
        <div class="header-text">
            <h1>LAPORAN REKAPITULASI EVENT</h1>
            <h2>MALANG CREATIVE CENTER (MCC)</h2>
            <p>Jl. A. Yani No. 53, Blimbing, Malang, Jawa Timur 65118</p>
        </div>
    </div>

    <div class="info-line">
        Periode: <strong>{{ $period }}</strong> | 
        Total Event: <strong>{{ count($data) }}</strong> | 
        Total Peserta: <strong>{{ number_format(array_sum(array_column($data, 'jumlah_peserta'))) }}</strong> | 
        Dicetak: <strong>{{ date('d/m/Y H:i') }} WIB</strong>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 2%">NO</th>
                <th style="width: 13%">JUDUL KEGIATAN</th>
                <th style="width: 11%">PENYELENGGARA</th>
                <th style="width: 7%">BIDANG</th>
                <th style="width: 9%">SUB SEKTOR</th>
                <th style="width: 9%">LOKASI</th>
                <th style="width: 6%">TANGGAL</th>
                <th style="width: 3%">LT</th>
                <th style="width: 5%">PESERTA</th>
                <th style="width: 7%">KOMERSIAL</th>
                <th style="width: 9%">PIC</th>
                <th style="width: 9%">KONTAK</th>
                <th style="width: 5%">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-left font-bold">{{ $row['judul_kegiatan'] ?? '-' }}</td>
                <td class="text-left">{{ $row['nama_organisasi'] ?? '-' }}</td>
                <td class="text-center">{{ $row['bidang'] ?? '-' }}</td>
                <td class="text-left">{{ $row['sub_sektor'] ?? '-' }}</td>
                <td class="text-left">{{ $row['lokasi'] ?? '-' }}</td>
                <td class="text-center">
                    @if(isset($row['tanggal']))
                        {{ date('d/m/Y', strtotime($row['tanggal'])) }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">{{ $row['lantai'] ?? '-' }}</td>
                <td class="text-right font-bold">
                    @if(isset($row['jumlah_peserta']) && is_numeric($row['jumlah_peserta']))
                        {{ number_format($row['jumlah_peserta'], 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">{{ $row['komersial'] ?? '-' }}</td>
                <td class="text-left">{{ $row['pic'] ?? '-' }}</td>
                <td class="text-left">
                    {{ $row['no_telepon'] ?? '-' }}
                    @if(isset($row['email']) && $row['email'])
                        <br>{{ $row['email'] }}
                    @endif
                </td>
                <td class="text-center">
                    @php
                        $status = $row['status_data'] ?? 'Valid';
                        $statusClass = match($status) {
                            'Valid' => 'status-valid',
                            'Review' => 'status-review',
                            'Duplikat' => 'status-duplikat',
                            default => 'status-valid'
                        };
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ $status }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="13" class="text-center" style="padding: 15px;">
                    Tidak ada data untuk periode ini
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <strong>SiREKAP MCC</strong> - Sistem Rekapitulasi Malang Creative Center | 
        Jl. A. Yani No. 53, Blimbing, Malang, Jawa Timur 65118 | 
        Dokumen digenerate pada {{ date('d/m/Y H:i:s') }} WIB
    </div>
</body>
</html>
