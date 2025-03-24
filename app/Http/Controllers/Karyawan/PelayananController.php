<?php

namespace App\Http\Controllers\Karyawan;

use carbon\carbon;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AddOrderRequest;
use Illuminate\Support\Facades\Session;
use App\Models\{transaksi, User, harga, DataBank, Notification, notifications_setting, PageSettings};
use App\Jobs\DoneCustomerJob;
use App\Jobs\OrderCustomerJob;
use App\Notifications\{OrderMasuk, OrderSelesai};
use GuzzleHttp\Client;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PelayananController extends Controller

{

  // Halaman list order masuk
  public function index()
  {
    $order = transaksi::with('price')
      ->where('user_id', Auth::user()->id)
      ->where('status_order', '!=', 'DiTerima')
      ->orderBy('id', 'DESC')
      ->get();

    return view('karyawan.transaksi.order', compact('order'));
  }

  private function generateNotaImage($order)
  {
    $font = realpath(public_path('storage/fonts/Poppins-Italic.ttf'));
    if (!$font) {
      die("Path font tidak ditemukan!");
    }

    $companyName = PageSettings::first()->judul;

    $lineSpacing = 25;
    $margin = 20;
    $maxWidth = 0;
    $posY = 50;
    $texts = [
      "TANGGAL   : " . date('d/m/Y H:i', strtotime($order->tgl_transaksi)),
      "NO RESI   : " . $order->invoice,
      "NAMA      : " . $order->customers->name,
      "LAYANAN   : " . harga::where('id', $order->harga_id)->first()->jenis,
      "HARGA/KG  : Rp " . number_format(harga::where('id', $order->harga_id)->first()->harga, 0, ',', '.'),
      "BERAT     : " . $order->kg . " Kg",
      "TOTAL     : Rp " . number_format($order->harga_akhir, 0, ',', '.'),
      "ESTIMASI HARI : " . $order->hari . " Hari",
    ];

    if ($order->status_payment == 'Pending') {
      $texts[] = "STATUS PEMBAYARAN : BELUM LUNAS";
    } else if ($order->status_payment == 'Success') {
      $texts[] = "STATUS PEMBAYARAN : LUNAS";
    }

    $hari = $order->hari;
    $created_at = $order->created_at;
    $estimasi = $created_at->addDays($hari);
    $texts[] = "ESTIMASI SELESAI  : " . $estimasi->format('d/m/Y') . "\n";

    $footerText = "** Terima Kasih Sudah Menggunakan Layanan Kami **\n";
    $texts[] = $footerText;

    foreach ($texts as $text) {
      $bbox = imagettfbbox(12, 0, $font, $text);
      $textWidth = $bbox[2] - $bbox[0];
      $maxWidth = max($maxWidth, $textWidth);
    }

    // Hitung ukuran nama perusahaan agar bisa ditaruh di tengah
    $bboxCompany = imagettfbbox(14, 0, $font, $companyName);
    $companyWidth = $bboxCompany[2] - $bboxCompany[0];
    $maxWidth = max($maxWidth, $companyWidth);

    // Ukuran QR Code Dinamis (maksimal 150x150)
    $qrCodeSize = min(150, $maxWidth - 40);

    $imgWidth = $maxWidth + ($margin * 2);
    $imgHeight = $posY + (count($texts) * $lineSpacing) + $qrCodeSize + 60; // Tambah ruang untuk QR Code

    $img = imagecreatetruecolor($imgWidth, $imgHeight);
    $white = imagecolorallocate($img, 255, 255, 255);
    $black = imagecolorallocate($img, 0, 0, 0);
    $gray = imagecolorallocate($img, 200, 200, 200);

    imagefilledrectangle($img, 0, 0, $imgWidth, $imgHeight, $white);

    // Tambahkan nama perusahaan di tengah atas
    $companyX = ($imgWidth - $companyWidth) / 2;
    imagettftext($img, 14, 0, $companyX, 30, $black, $font, $companyName);

    $posY = 60; // Geser ke bawah untuk memberi ruang nama perusahaan
    foreach ($texts as $text) {
      imagettftext($img, 12, 0, $margin, $posY, $black, $font, $text);
      $posY += $lineSpacing;
    }

    imageline($img, $margin, $posY, $imgWidth - $margin, $posY, $gray);

    // ** Tambahkan QR Code langsung ke gambar tanpa menyimpan file **
    $qrCode = QrCode::format('png')->size($qrCodeSize)->generate($order->invoice);
    $qrCodeImage = imagecreatefromstring($qrCode);

    if ($qrCodeImage) {
      $qrCodeWidth = imagesx($qrCodeImage);
      $qrCodeHeight = imagesy($qrCodeImage);

      // Tempatkan QR code di bagian bawah tengah
      $qrCodeX = ($imgWidth - $qrCodeWidth) / 2;
      $qrCodeY = $posY + 20;

      imagecopy($img, $qrCodeImage, $qrCodeX, $qrCodeY, 0, 0, $qrCodeWidth, $qrCodeHeight);
      imagedestroy($qrCodeImage);
    }

    $fileName = 'nota_' . $order->invoice . '.png';
    $filePath = storage_path('app/public/' . $fileName);
    imagepng($img, $filePath);
    imagedestroy($img);

    return asset('storage/' . $fileName);
  }

  public function histori()
  {
    $order = transaksi::with('price')
      ->where('user_id', Auth::user()->id)
      ->where('status_order', '=', 'DiTerima')
      ->orderBy('id', 'DESC')
      ->get();

    return view('karyawan.transaksi.history', compact('order'));
  }

  public function updateHistory(Request $request)
  {
    $order = transaksi::find($request->id);
    if (!$order) {
      abort(404);
    }

    $order->status_order = $request->status_order;
    $order->tgl_ambil = null;
    $order->save();

    Session::flash('success', 'Status laundry berhasil diperbarui dan tanggal ambil telah diupdate.');
    return redirect()->route('history');
  }


  // Proses simpan order
  public function store(AddOrderRequest $request)
  {
    try {
      DB::beginTransaction();
      $order = new transaksi();
      $order->invoice         = $request->invoice;
      $order->tgl_transaksi   = Carbon::now()->parse($order->tgl_transaksi)->format('d-m-Y H:i:s');
      $order->status_payment  = $request->status_payment;
      $order->harga_id        = $request->harga_id;
      $order->customer_id     = $request->customer_id;
      $order->user_id         = Auth::user()->id;
      $order->customer        = namaCustomer($order->customer_id);
      $order->email_customer  = email_customer($order->customer_id);
      $order->hari            = $request->hari;
      $order->kg = (int) $request->kg;
      $order->harga = (int) str_replace(['Rp.', '.', ',', ' '], '', $request->harga);
      $order->disc            = $request->disc;
      $hitung                 = $order->kg * $order->harga;
      if ($request->disc != NULL) {
        $disc                = ($hitung * $order->disc) / 100;
        $total               = $hitung - $disc;
        $order->harga_akhir  = $total;
      } else {
        $order->harga_akhir    = $hitung;
      }
      $order->jenis_pembayaran  = $request->jenis_pembayaran;
      $order->tgl               = Carbon::now()->day;
      $order->bulan             = Carbon::now()->month;
      $order->tahun             = Carbon::now()->year;
      $order->save();

      if ($order) {
        // Notification Telegram
        if (setNotificationTelegramIn(1) == 1) {
          $order->notify(new OrderMasuk());
        }

        // Notification email
        if (setNotificationEmail(1) == 1) {
          // Menyiapkan data Email
          $bank = DataBank::get();
          $jenisPakaian = harga::where('id', $order->harga_id)->first();
          $data = array(
            'email'         => $order->email_customer,
            'invoice'       => $order->invoice,
            'customer'      => $order->customer,
            'tgl_transaksi' => $order->tgl_transaksi,
            'pakaian'       => $jenisPakaian->jenis,
            'berat'         => $order->kg,
            'harga'         => $order->harga,
            'harga_disc'    => ($hitung * $order->disc) / 100,
            'disc'          => $order->disc,
            'total'         => $order->kg * $order->harga,
            'harga_akhir'   => $order->harga_akhir,
            'laundry_name'  => Auth::user()->nama_cabang,
            'bank'          => $bank
          );

          // Kirim Email
          dispatch(new OrderCustomerJob($data));
        }

        // Kirim notifikasi via WhatsApp menggunakan API
        try {
          if (setNotificationWhatsappOrderSelesai(1) == 1) {
            $waApiUrl = notifications_setting::where('id', 1)->first()->wa_api_url . '/send-media'; // URL API WhatsApp untuk mengirim media
            $fileUrl = $this->generateNotaImage($order);

            $data = [
              'number' =>  $order->customers->no_telp,
              'message' => "Halo Kak *{$order->customers->name}*, berikut nota transaksi laundry Anda dengan No Resi *{$order->invoice}*. Anda dapat cek status laundry Anda dari website kami di " . url('/') . " maupun WhatsApp Bot kami.",
              'file_url' => $fileUrl // URL gambar nota
            ];

            $ch = curl_init($waApiUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            if ($response === false) {
              return response()->json(['error' => 'Gagal menghubungi server WhatsApp. Silakan coba lagi nanti.'], 500);
            } else {
              $responseData = json_decode($response, true);
              if (!isset($responseData['status']) || $responseData['status'] !== 'success') {
                Session::flash('error', 'Respon API : ' . json_encode($responseData));
              }
            }
          }

          DB::commit();
          Session::flash('success', 'Order Berhasil Ditambah!');
          return redirect('pelayanan');
        } catch (Exception $e) {
          DB::rollBack();
          Session::flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
          return redirect()->back();
        };
      }
    } catch (ErrorException $e) {
      DB::rollback();
      throw new ErrorException($e->getMessage());
    }
  }

  // Tambah Order
  public function addorders()
  {
    $customer = User::where('karyawan_id', Auth::user()->id)->get();
    $jenisPakaian = harga::where('user_id', Auth::id())->where('status', '1')->get();

    $y = date('Y');
    $number = mt_rand(1000, 9999);
    // Nomor Form otomatis
    $newID = $number . Auth::user()->id . '' . $y;
    $tgl = date('d-m-Y');

    $cek_harga = harga::where('user_id', Auth::user()->id)->where('status', 1)->first();
    $cek_customer = User::select('id', 'karyawan_id')->where('karyawan_id', Auth::id())->count();
    return view('karyawan.transaksi.addorder', compact('customer', 'newID', 'cek_harga', 'cek_customer', 'jenisPakaian'));
  }

  public function listharga(Request $request)
  {
    $list_harga = Harga::select('id', 'harga') // Gunakan "Harga" dengan huruf besar jika ini model
      ->where('user_id', Auth::id())
      ->where('id', $request->id)
      ->first(); // Menggunakan first() karena ID unik

    if (!$list_harga) {
      return response()->json(['error' => 'Harga tidak ditemukan'], 404);
    }

    return response()->json([
      'harga' => 'Rp. ' . number_format($list_harga->harga, 0, ",", ".")
    ]);
  }

  public function totalHarga(Request $request)
  {

    $harga = Harga::select('id', 'harga') // Benar
    ->where('user_id', Auth::id())
    ->where('id', $request->id)
    ->first();


    // Cek apakah harga ditemukan
    if (!$harga) {
      return response()->json(['error' => 'Harga tidak ditemukan'], 404);
    }

    // Perhitungan harga
    $kg = $request->kg ?? 1;
    $disc = $request->disc ?? 0;
    $hitung = $kg * $harga->harga;
    $diskon = ($disc > 0) ? ($hitung * $disc / 100) : 0;
    $total = $hitung - $diskon;

    return response()->json([
      'total_harga' => 'Rp. ' . number_format($total, 0, ",", ".")
    ]);
  }



  // Filter List Jumlah Hari
  public function listhari(Request $request)
  {
    $list_jenis = harga::select('id', 'hari')
      ->where('user_id', Auth::id())
      ->where('id', $request->id)
      ->first(); // Menggunakan first() karena ID unik

    if (!$list_jenis) {
      return response()->json(['error' => 'Data tidak ditemukan'], 404);
    }

    return response()->json([
      'hari' => $list_jenis->hari
    ]);
  }


  // customer name
  public function getCustomerName(Request $request)
  {
    $customer = User::select('id', 'name')->where('no_telp', $request->no_telp)->first();
    if (!$customer) {
      return response()->json(['message' => 'Nomor telepon belum terdaftar, silahkan daftar terlebih dahulu.'], 404);
    }
    return $customer;
  }


  // Update Status Laundry
  public function updateStatusLaundry(Request $request)
  {
    $transaksi = transaksi::find($request->id);
    if ($transaksi->status_payment == 'Pending') {
      $transaksi->update([
        'status_payment' => 'Success'
      ]);
    } elseif ($transaksi->status_payment == 'Success') {
      if ($transaksi->status_order == 'Process') {
        $transaksi->update([
          'status_order' => 'Done'
        ]);

        // Tambah point +1
        $points = User::where('id', $transaksi->customer_id)->firstOrFail();
        $points->point =  $points->point + 1;
        $points->update();

        // Create Notifikasi
        $id         = $transaksi->id;
        $user_id    = $transaksi->customer_id;
        $title      = 'Pakaian Selesai';
        $body       = 'Pakaian Sudah Selesai dan Sudah Bisa Diambil :)';
        $kategori   = 'info';
        sendNotification($id, $user_id, $kategori, $title, $body);

        // Cek email notif
        if (setNotificationEmail(1) == 1) {

          // Menyiapkan data
          $data = array(
            'email'           => $transaksi->email_customer,
            'invoice'         => $transaksi->invoice,
            'customer'        => $transaksi->customer,
            'nama_laundry'    => Auth::user()->nama_cabang,
            'alamat_laundry'  => Auth::user()->alamat_cabang,
          );

          // Kirim Email
          dispatch(new DoneCustomerJob($data));
        }

        // Cek status notif untuk telegram
        if (setNotificationTelegramFinish(1) == 1) {
          $transaksi->notify(new OrderSelesai());
        }

        // Notifikasi WhatsApp
        if (setNotificationWhatsappOrderSelesai(1) == 1) {
          $waApiUrl = notifications_setting::where('id', 1)->first()->wa_api_url . '/send-message';
          $createdAt = \Carbon\Carbon::parse($transaksi->created_at);
          $updatedAt = \Carbon\Carbon::parse($transaksi->updated_at);
          $lamaPengerjaan = $createdAt->diffInDays($updatedAt); // Hitung selisih hari
          $jenisLayanan = harga::where('id', $transaksi->harga_id)->first()->jenis;

          $data = [
            'number' =>  $transaksi->customers->no_telp,
            'message' => "🌟 Halo Kak *{$transaksi->customers->name}* 🌟\n\n" .
              "Kami punya kabar baik nih! 🎉 Laundry Kakak dengan No Resi *{$transaksi->invoice}* sudah selesai dan siap untuk diambil. 🧺✨\n\n" .
              "📅 *Detail Transaksi:*\n" .
              "• 🏷️ *Layanan:* {$jenisLayanan}\n" .
              "• ⚖️ *Berat:* {$transaksi->kg} Kg\n" .
              "• ⏳ *Lama Pengerjaan:* {$lamaPengerjaan} Hari\n" .
              "• 💰 *Total Biaya:* Rp " . number_format($transaksi->harga_akhir, 0, ',', '.') . "\n\n" .
              "Silakan datang ke outlet kami untuk mengambilnya. Jangan lupa, pakaian bersih dan wangi sudah menanti! 😍\n\n" .
              "Terima kasih telah menggunakan layanan kami. Semoga hari Kakak menyenangkan! 😊🙏"
          ];

          // Kirim data menggunakan cURL dengan melewati pemeriksaan SSL
          $ch = curl_init($waApiUrl);
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
          curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Melewati pemeriksaan SSL

          $response = curl_exec($ch);
          if ($response === false) {
            $error = curl_error($ch);
            \Log::error('Gagal menghubungi server WhatsApp: ' . $error);
            return response()->json(['error' => 'Gagal menghubungi server WhatsApp: ' . $error], 500);
          }
          curl_close($ch);

          $responseData = json_decode($response, true);

          // Simpan isi $data dan respon API ke log
          \Log::info('Data WhatsApp: ' . json_encode($data) . ' Respon WhatsApp: ' . json_encode($responseData));

          // Menampilkan respon API dan menghentikan eksekusi
          return response()->json($responseData);
        }
      } elseif ($transaksi->status_order == 'Done') {
        $transaksi->update([
          'status_order' => 'DiTerima',
          'tgl_ambil' => Carbon::now()->format('d-m-Y H:i:s')
        ]);
      }
    }

    if ($transaksi->status_payment == 'Success') {
      Session::flash('success', "Status Pembayaran Berhasil Diubah !");
    }
    if ($transaksi->status_order == 'Done' || $transaksi->status_order == 'DiTerima') {
      Session::flash('success', "Status Laundry Berhasil Diubah !");
    }
  }
}
