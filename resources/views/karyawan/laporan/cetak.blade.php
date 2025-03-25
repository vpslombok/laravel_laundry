<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@900&display=swap" rel="stylesheet">
    <style>
        @page {
            size: 80mm 297mm;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial Narrow', Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 5mm;
            /* Margin kecil di semua sisi */
            width: 70mm;
            /* Lebar konten lebih kecil dari kertas */
            line-height: 1.3;
        }

        .header {
            text-align: center;
            margin-bottom: 5px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }

        .title {
            font-family: 'Arial Black', 'Arial Bold', Gadget, sans-serif;
            font-weight: bolder;
            font-size: 20px;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
            /* Memberi kesan lebih tebal */
        }

        .subtitle {
            font-size: 11px;
            margin-bottom: 5px;
        }

        .info-section {
            margin-bottom: 5px;
            font-size: 11px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 25mm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 11px;
        }

        th {
            text-align: left;
            border-bottom: 1px dashed #000;
            padding: 3px 0;
        }

        td {
            padding: 2px 0;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 10px;
        }

        /* QR Code specific styles */
        .qr-container {
            text-align: center;
            margin: 8px 0;
            page-break-inside: avoid;
        }

        .qr-code {
            width: 25mm;
            /* Optimal size for thermal printers */
            height: 25mm;
            margin: 0 auto;
            display: block;
        }

        .qr-text {
            font-size: 10px;
            margin-top: 3px;
        }
    </style>
</head>

<body>
    @php
    // Hitung total sebelum digunakan di view
    $hitung = 0;
    $disc = 0;
    foreach ($invoice as $item) {
    $hitung += $item->kg * $item->harga;
    }
    $disc = ($hitung * $item->disc) / 100;
    @endphp

    <div class="header">
        <div class="title">{{$nama_laundry}}</div>
        <div>{{\Carbon\Carbon::parse($data->tgl_transaksi)->format('d/m/Y H:i')}}</div>
    </div>

    <div class="info-section">
        <div><span class="info-label">Cabang:</span> {{$data->user->nama_cabang}}</div>
        <div><span class="info-label">Telp:</span> {{$data->user->no_telp ?: '-'}}</div>
        <div><span class="info-label">Alamat:</span> {{$data->user->alamat_cabang}}</div>
    </div>

    <div class="divider"></div>

    <div class="info-section">
        <div><span class="info-label">Pelanggan:</span> {{$data->customers->name}}</div>
        <div><span class="info-label">Telp:</span> {{$data->customers->no_telp ?: '-'}}</div>
        <div><span class="info-label">Alamat:</span> {{$data->customers->alamat}}</div>
    </div>

    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th>Layanan</th>
                <th class="text-right">Berat</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice as $item)
            <tr>
                <td>{{$item->price->jenis}}</td>
                <td class="text-right">{{$item->kg}} kg</td>
                <td class="text-right">{{Rupiah::getRupiah($item->harga)}}</td>
                <td class="text-right">{{Rupiah::getRupiah($item->kg * $item->harga)}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td>Subtotal:</td>
            <td class="text-right">{{Rupiah::getRupiah($hitung)}}</td>
        </tr>
        <tr>
            <td>Diskon ({{$item->disc ?: 0}}%):</td>
            <td class="text-right">- {{Rupiah::getRupiah($disc)}}</td>
        </tr>
        <tr>
            <td><strong>TOTAL:</strong></td>
            <td class="text-right"><strong>{{Rupiah::getRupiah($item->harga_akhir)}}</strong></td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="info-section">
        <div><span class="info-label">Pembayaran:</span> {{$data->jenis_pembayaran}}</div>
        <div><span class="info-label">Tgl Masuk:</span> {{\Carbon\Carbon::parse($data->tgl_transaksi)->format('d/m/Y H:i')}}</div>
        <div><span class="info-label">Estimasi Selesai:</span> {{ \Carbon\Carbon::parse($data->tgl_transaksi)->addDays($data->hari)->format('d/m/Y H:i') }}</div>
    </div>
    @if($data->tgl_ambil)
    <div>
        <span class="info-label">Tgl Ambil:</span>
        {{ \Carbon\Carbon::parse($data->tgl_ambil)->format('d/m/Y H:i') }}
    </div>
    @endif

    <div class="divider"></div>

    <!-- QR Code Section -->
    <div class="qr-container">
        @if(isset($qrCode))
        <img class="qr-code" src="data:image/png;base64,{{ $qrCode }}" alt="QR Code Invoice">
        <div class="subtitle">No Resi #{{$data->invoice}}</div>
        @else
        <div class="barcode">*{{$data->invoice}}*</div>
        <div class="qr-text">{{$data->invoice}}</div>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        Terima kasih telah menggunakan layanan kami<br>
    </div>
</body>

</html>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Hitung tinggi dokumen dalam piksel
        let body = document.body;
        let html = document.documentElement;
        let height = Math.max(
            body.scrollHeight,
            body.offsetHeight,
            html.clientHeight,
            html.scrollHeight,
            html.offsetHeight
        );

        // Konversi ke milimeter (1px ≈ 0.264583mm)
        let heightInMM = height * 0.264583;

        // Ambil tinggi kertas A4 standar (297mm) dan hitung jumlah halaman
        const a4HeightMM = 297; // Tinggi A4 dalam mm
        const pageCount = Math.ceil(heightInMM / a4HeightMM);

        // Simpan informasi halaman ke cookie (expire 1 menit)
        let expires = new Date();
        expires.setTime(expires.getTime() + 60 * 1000);
        document.cookie = `totalPages=${pageCount}; expires=${expires.toUTCString()}; path=/`;

        console.log(`Dokumen membutuhkan ${pageCount} halaman A4`);
    });
</script>

<style>
    @media print {

        /* Reset margin untuk penggunaan kertas maksimal */
        @page {
            size: A4;
            margin: 0;
        }

        /* Tambahkan page break jika konten melebihi 1 halaman */
        .content {
            page-break-after: always;
        }

        /* Pastikan body tidak memiliki padding saat cetak */
        body {
            padding: 0;
            margin: 0;
        }
    }
</style>