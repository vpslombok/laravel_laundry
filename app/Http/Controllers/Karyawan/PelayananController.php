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
use App\Models\{transaksi, User, harga, DataBank, Notification, notifications_setting};
use App\Jobs\DoneCustomerJob;
use App\Jobs\OrderCustomerJob;
use App\Notifications\{OrderMasuk, OrderSelesai};
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

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

  private function generateNotaImage($transaksi)
  {
    // Buat canvas gambar
    $img = imagecreatetruecolor(600, 800);
    $white = imagecolorallocate($img, 255, 255, 255);
    $black = imagecolorallocate($img, 0, 0, 0);

    // Isi background putih
    imagefilledrectangle($img, 0, 0, 600, 800, $white);

    $font = realpath(public_path('storage/fonts/Poppins-BlackItalic.ttf'));

    if (!$font) {
      die("Path font tidak ditemukan!");
    }


    // Tambahkan teks ke gambar
    imagettftext($img, 20, 0, 50, 50, $black, $font, "Invoice: " . $transaksi->invoice);
    imagettftext($img, 18, 0, 50, 100, $black, $font, "Nama: " . $transaksi->customers->name);
    imagettftext($img, 18, 0, 50, 150, $black, $font, "Tanggal: " . $transaksi->tgl_transaksi);
    imagettftext($img, 18, 0, 50, 200, $black, $font, "Layanan: " . harga::where('id', $transaksi->harga_id)->first()->jenis);
    imagettftext($img, 18, 0, 50, 250, $black, $font, "Berat: " . $transaksi->kg . " Kg");
    imagettftext($img, 18, 0, 50, 300, $black, $font, "Total Biaya: Rp " . number_format($transaksi->harga_akhir, 0, ',', '.'));
    imagettftext($img, 18, 0, 50, 350, $black, $font, "Status: " . $transaksi->status_payment);

    // Simpan gambar ke storage Laravel
    $fileName = 'nota_' . $transaksi->invoice . '.png';
    $filePath = storage_path('app/public/' . $fileName);
    imagepng($img, $filePath);
    imagedestroy($img);

    // Return URL gambar yang bisa diakses
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

        // Kirim notifikasi via WhatsApp menggunakan API dengan Guzzle
        try {
          if (setNotificationWhatsappOrderSelesai(1) == 1) {
            $waApiUrl = notifications_setting::where('id', 1)->first()->wa_api_url . '/send-message'; // URL API WhatsApp
            $apikey = notifications_setting::where('id', 1)->first()->api_key; // Mendapatkan API Key dari basis data

            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', $waApiUrl, [
              'json' => [
                'api_key'  => $apikey,
                'sender'   => "6285333640674", // Nomor perangkat Anda
                'number'   => '62' . ltrim($order->customers->no_telp ?? '', '0'), // Format nomor penerima
                'message'  => "Terima kasih, " . ($order->customers->name ?? 'Pelanggan') .
                  ". Laundryan Anda sudah kami terima dengan nomor invoice " . ($order->invoice ?? 'N/A') .
                  ". Kami akan segera memproses laundryan Anda. Silakan tunggu informasi lebih lanjut. Anda dapat memantau status laundryan Anda melalui website kami di " . url('/'),
                'footer'   => "Terima kasih.."
              ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            if (isset($responseData['status']) && $responseData['status'] === false) {
              return response()->json(['error' => $responseData['msg'], 'data' => $data]);
            } else if (!isset($responseData['status']) || $responseData['status'] !== 'true') {
              Session::flash('error', 'Respon API tidak valid: ' . json_encode($responseData));
              DB::rollBack();
              return redirect()->back();
            } else {
              DB::commit();
              Session::flash('success', 'Order Berhasil Ditambah!');
              return redirect('pelayanan');
            }
          }
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

  // Filter List Harga
  public function listharga(Request $request)
  {
    $list_harga = harga::select('id', 'harga')
      ->where('user_id', Auth::user()->id)
      ->where('id', $request->id)
      ->get();
    $select = '';
    $select .= '
                    <div class="form-group has-success">
                    <label for="id" class="control-label">Harga</label>
                    <input id="harga" class="form-control" name="harga" value="';
    foreach ($list_harga as $studi) {
      $select .= 'Rp. ' . number_format($studi->harga, 0, ",", ".");
    }
    $select .= '" readonly>
                    </div>
                    </div>';
    return $select;
  }

  // Filter List Jumlah Hari
  public function listhari(Request $request)
  {
    $list_jenis = harga::select('id', 'hari')
      ->where('user_id', Auth::user()->id)
      ->where('id', $request->id)
      ->get();
    $select = '';
    $select .= '
                    <div class="form-group has-success">
                    <label for="id" class="control-label">Hari</label>
                    <input id="hari" class="form-control" name="hari" value="';
    foreach ($list_jenis as $hari) {
      $select .= $hari->hari;
    }
    $select .= '" readonly>
                    </div>
                    </div>';
    return $select;
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
          $nameCustomer = $transaksi->customers->name; // get name customer
          $waApiUrl = notifications_setting::where('id', 1)->first()->wa_api_url . '/send-message'; // URL API WhatsApp dengan tambahan /send-message
          $apiKey = notifications_setting::where('id', 1)->first()->api_key; // get API Key dari database
          $data = [
            'api_key' => $apiKey,
            'sender' => '6285333640674', // Nomor perangkat kamu
            'number' => '62' . ltrim($transaksi->customers->no_telp, '0'),
            'message' => "Halo Kak $nameCustomer 😊
Pesanan laundry Kakak dengan Invoice: $transaksi->invoice telah selesai dan sudah bisa diambil! Berikut detail pesanan:
📅 Tanggal Transaksi: $transaksi->tgl_transaksi
⚖ Berat Cucian: $transaksi->kg Kg
⏳ Lama Pengerjaan: 4 hari
💰 Total Biaya: Rp " . number_format($transaksi->harga_akhir, 0, ',', '.') . "
💳 Status Pembayaran: $transaksi->status_payment
Jika sudah melakukan pembayaran, silakan datang ke tempat kami untuk mengambil laundry. Jika belum, pembayaran bisa dilakukan saat pengambilan.

Terima kasih sudah menggunakan layanan kami! Semoga hari Kakak menyenangkan! 🌟"
          ];

          // Kirim data menggunakan cURL
          $ch = curl_init($waApiUrl);
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
          curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          $response = curl_exec($ch);
          if ($response === false) {
            $error = curl_error($ch);
            echo "cURL Error: " . $error;
          }
          curl_close($ch);

          $response = json_decode($response, true);
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
