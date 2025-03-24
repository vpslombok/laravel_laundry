<!DOCTYPE html>
<html>

<head>
  <title>Laundry Notification</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style type="text/css">
    #outlook a {
      padding: 0;
    }

    .ReadMsgBody {
      width: 100%;
    }

    .ExternalClass {
      width: 100%;
    }

    .ExternalClass * {
      line-height: 100%;
    }

    body {
      margin: 0;
      padding: 0;
      -webkit-text-size-adjust: 100%;
      -ms-text-size-adjust: 100%;
    }

    table,
    td {
      border-collapse: collapse;
      mso-table-lspace: 0pt;
      mso-table-rspace: 0pt;
    }

    img {
      border: 0;
      height: auto;
      line-height: 100%;
      outline: none;
      text-decoration: none;
      -ms-interpolation-mode: bicubic;
    }

    p {
      display: block;
      margin: 13px 0;
    }

    @media only screen and (max-width:480px) {
      @-ms-viewport {
        width: 320px;
      }

      @viewport {
        width: 320px;
      }
    }
  </style>
  <style type="text/css">
    body,
    .text {
      font-family: Whitney, Helvetica Neue, Helvetica, Arial, sans-serif;
    }

    .header {
      color: white;
      font-size: 36px;
      font-weight: 600;
    }

    .title {
      font-weight: 500;
      font-size: 20px;
      color: #4F545C;
      letter-spacing: 0.27px;
    }

    .content {
      color: #737F8D;
      font-size: 16px;
      line-height: 24px;
    }

    .footer {
      color: #99AAB5;
      font-size: 12px;
      line-height: 24px;
    }

    .bold {
      font-weight: bold;
      color: black;
    }
  </style>
</head>

<body style="background: #F9F9F9; margin: 0; padding: 0;">
  <!-- Main Container -->
  <div style="max-width:640px; margin:0 auto; box-shadow:0px 1px 5px rgba(0,0,0,0.1); border-radius:4px; overflow:hidden;">

    <!-- Header Section - Background image removed -->
    <div style="background:#7289DA;">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td style="text-align:center; padding:57px;">
            <div class="header">Laundry Selesai!</div>
          </td>
        </tr>
      </table>
    </div>

    <!-- Content Section -->
    <div style="background:#ffffff;">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td style="padding:40px 70px;">
            <div class="content">
              <h2 class="title">Halo, Kak {{$data['customer']}}.</h2>
              <p>Kami ingin memberitahu bahwa Laundry kakak dengan nomor resi <span class="bold">{{$data['invoice']}}</span> sudah selesai dan sudah bisa diambil.</p>

              <!-- Additional details -->
              <table width="100%" style="margin:20px 0; border-top:1px solid #eee; border-bottom:1px solid #eee;">
                <tr>
                  <td style="padding:10px 0;"><strong>Tanggal Selesai:</strong></td>
                  <td style="padding:10px 0; text-align:right;">{{$data['tanggal_selesai']}}</td>
                </tr>
                <tr>
                  <td style="padding:10px 0;"><strong>Total Pembayaran:</strong></td>
                  <td style="padding:10px 0; text-align:right;">{{$data['total_harga']}}</td>
                </tr>
              </table>

              <p style="margin-top:6px; margin-bottom:20px;">
                Terima Kasih,<br>
                <strong>{{$data['nama_laundry']}} Team</strong>
              </p>
            </div>
          </td>
        </tr>
      </table>
    </div>

    <!-- Footer Section -->
    <div style="background:transparent;">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td style="padding:20px 0; text-align:center;">
            <div class="footer">
              Sent by {{$data['nama_laundry']}} Teams<br>
              {{$data['alamat_laundry']}}<br>
              <a href="mailto:{{$data['email_laundry']}}" style="color:#99AAB5; text-decoration:none;">{{$data['email_laundry']}}</a>
            </div>
          </td>
        </tr>
      </table>
    </div>
  </div>
</body>

</html>