<!DOCTYPE html>
<html>

<head>
  <title>Selamat Datang di {{$data['nama_laundry']}}</title>
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
      font-family: 'Ubuntu', Whitney, Helvetica Neue, Helvetica, Arial, sans-serif;
    }

    .header-text {
      color: white;
      font-size: 36px;
      font-weight: 600;
      text-align: center;
    }

    .content-title {
      font-weight: 500;
      font-size: 20px;
      color: #4F545C;
      letter-spacing: 0.27px;
    }

    .content-text {
      color: #737F8D;
      font-size: 16px;
      line-height: 24px;
    }

    .login-button {
      background-color: #7289DA;
      color: white;
      padding: 15px 25px;
      text-decoration: none;
      border-radius: 3px;
      display: inline-block;
      font-weight: bold;
    }

    .footer-text {
      color: #99AAB5;
      font-size: 12px;
      line-height: 24px;
    }

    .credentials {
      background-color: #f5f5f5;
      padding: 15px;
      border-radius: 4px;
      margin: 20px 0;
    }
  </style>
</head>

<body style="background: #F9F9F9; margin: 0; padding: 0;">
  <!-- Main Container -->
  <div style="max-width:640px; margin:0 auto; box-shadow:0px 1px 5px rgba(0,0,0,0.1); border-radius:4px; overflow:hidden;">

    <!-- Header Section -->
    <div style="background:#7289DA;">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td style="text-align:center; padding:57px;">
            <div class="header-text">Selamat Datang!</div>
          </td>
        </tr>
      </table>
    </div>

    <!-- Content Section -->
    <div style="background:#ffffff;">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td style="padding:40px 70px;">
            <div class="content-text">
              <h2 class="content-title">Halo, Selamat Datang Kak {{$data['name']}}.</h2>
              <p>Terima kasih sudah menjadi member <strong>{{$data['nama_laundry']}}</strong>.</p>

              <p>Berikut ini adalah detail akun kakak:</p>

              <div class="credentials">
                <p><strong>Email:</strong> {{$data['email']}}</p>
                <p><strong>Password:</strong>
                  <span style="background-color: #fff; padding: 2px 5px; border-radius: 3px;">
                    {{$data['password']}}
                  </span>
                </p>
                <p style="font-size: 14px; color: #ff5850;">
                  <strong>Harap ganti password setelah login pertama kali!</strong>
                </p>
              </div>

              <p style="text-align: center; margin: 30px 0;">
                <a href="{{$data['url_login']}}" class="login-button" target="_blank">Login Sekarang</a>
              </p>

              <p>Jika mengalami kesulitan, jangan ragu untuk menghubungi kami.</p>
            </div>
          </td>
        </tr>
      </table>
    </div>

    <!-- Footer Section -->
    <div style="background:transparent; padding:20px 0;">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td style="text-align:center;">
            <div class="footer-text">
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