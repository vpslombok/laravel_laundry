<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
  <title>Laundry Invoice</title>
  <style>
    body {
      font-family: 'Montserrat', Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f5f5f5;
      color: #333;
    }

    .container {
      max-width: 600px;
      margin: 0 auto;
      background: #ffffff;
    }

    .header {
      background-color: #4a6fa5;
      color: white;
      padding: 30px;
      text-align: center;
    }

    .content {
      padding: 30px;
    }

    .invoice-title {
      color: #ff5850;
      font-weight: 700;
      font-size: 20px;
      margin-bottom: 5px;
    }

    .invoice-table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
    }

    .invoice-table th {
      background-color: #f8f9fa;
      padding: 10px;
      text-align: left;
      border-bottom: 1px solid #ddd;
      font-size: 12px;
    }

    .invoice-table td {
      padding: 10px;
      border-bottom: 1px solid #eee;
    }

    .text-right {
      text-align: right;
    }

    .text-center {
      text-align: center;
    }

    .total-row {
      font-weight: bold;
      background-color: #f8f9fa;
    }

    .payment-methods {
      margin: 20px 0;
    }

    .footer {
      padding: 20px;
      text-align: center;
      font-size: 12px;
      color: #777;
    }

    @media (max-width: 600px) {
      .container {
        width: 100%;
      }

      .content {
        padding: 15px;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <h1 style="margin: 0; font-size: 24px;">Nota Laundry</h1>
    </div>

    <div class="content">
      <p style="font-weight: 600; font-size: 18px; margin-bottom: 5px;">Halo Kak,</p>
      <p class="invoice-title">{{$data['customer']}}</p>

      <p>Terima kasih sudah mempercayakan pakaian kakak kepada kami. Berikut detail invoice laundry kakak:</p>

      <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
        <div>
          <strong>Nomor Resi #{{$data['invoice']}}</strong>
        </div>
        <div>
          <strong>Tanggal: {{$data['tgl_transaksi']}}</strong>
        </div>
      </div>

      <table class="invoice-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Jenis Layanan</th>
            <th class="text-right">Berat</th>
            <th class="text-right">Harga</th>
            <th class="text-right">Total</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="text-center">1</td>
            <td>{{$data['pakaian']}}</td>
            <td class="text-right">{{$data['berat']}} Kg</td>
            <td class="text-right">Rp {{number_format($data['harga'],0,",",".")}} /Kg</td>
            <td class="text-right">Rp {{number_format($data['total'],0,",",".")}}</td>
          </tr>
          <tr class="total-row">
            <td colspan="4">Diskon {{$data['disc'] == null || 0 ? '0' : $data['disc']}}%</td>
            <td class="text-right">- Rp {{number_format($data['harga_disc'],0,",",".")}}</td>
          </tr>
          <tr class="total-row">
            <td colspan="4">Total Bayar</td>
            <td class="text-right">Rp {{number_format($data['harga_akhir'],0,",",".")}}</td>
          </tr>
        </tbody>
      </table>

      <div class="payment-methods">
        <h5 style="margin-bottom: 10px;">Metode Pembayaran:</h5>
        <ul style="padding-left: 20px; margin-top: 0;">
          @foreach ($data['bank'] as $banks)
          <li style="margin-bottom: 8px;">
            <strong>{{$banks->nama_bank}}</strong><br>
            {{$banks->no_rekening}} a/n {{$banks->nama_pemilik}}
          </li>
          @endforeach
        </ul>
      </div>

      <p>Untuk mengetahui status laundry terbaru, kakak bisa mengeceknya melalui halaman dashboard.</p>
      <p>Jika ada pertanyaan tentang invoice ini, silakan balas email ini atau hubungi tim kami.</p>

      <p style="margin-top: 30px;">
        Salam hangat,<br>
        <strong>{{$data['laundry_name']}} Team</strong>
      </p>
    </div>

    <div class="footer">
      <p>© {{date('Y')}} {{$data['laundry_name']}}. All rights reserved.</p>
    </div>
  </div>
</body>

</html>