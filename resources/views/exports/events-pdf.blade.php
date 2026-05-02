<!DOCTYPE html>
<html>
<head>
    <title>Laporan Event MCC</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; color: #4f46e5; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 6px 4px; text-align: left; word-wrap: break-word; }
        th { background-color: #f9fafb; font-weight: bold; text-transform: uppercase; font-size: 8px; color: #4b5563; }
        .footer { margin-top: 20px; text-align: right; font-size: 8px; color: #999; }
        .page-break { page-break-after: always; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN REKAPITULASI EVENT</h1>
        <p>MALANG CREATIVE CENTER (MCC)</p>
        <p>Periode: {{ $period }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%">No</th>
                <th style="width: 15%">Judul Kegiatan</th>
                <th style="width: 12%">Penyelenggara</th>
                <th style="width: 10%">Sub Sektor</th>
                <th style="width: 8%">Kategori</th>
                <th style="width: 10%">Lokasi</th>
                <th style="width: 8%">Tanggal</th>
                <th style="width: 5%">Lantai</th>
                <th style="width: 5%">Peserta</th>
                <th style="width: 8%">Komersial</th>
                <th style="width: 10%">PIC / Kontak</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td><strong>{{ $row['judul_kegiatan'] }}</strong></td>
                <td>{{ $row['nama_organisasi'] }}</td>
                <td>{{ $row['sub_sektor'] ?: '-' }}</td>
                <td>{{ $row['jenis_kegiatan'] }}</td>
                <td>{{ $row['lokasi'] }}</td>
                <td>{{ date('d/m/Y', strtotime($row['tanggal'])) }}</td>
                <td class="text-center">{{ $row['lantai'] }}</td>
                <td class="text-center">{{ number_format($row['jumlah_peserta']) }}</td>
                <td class="text-center">{{ $row['komersial'] }}</td>
                <td>
                    {{ $row['pic'] }}<br>
                    <span style="color: #666; font-size: 7px;">{{ $row['no_telepon'] }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ date('d/m/Y H:i') }} | SiREKAP MCC Digital Report
    </div>
</body>
</html>
