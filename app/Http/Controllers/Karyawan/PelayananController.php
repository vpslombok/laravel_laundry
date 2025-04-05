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

  // private function generateNotaImage($order)
  // {
  //   $pdfPath = storage_path('app/public/nota_pdf/' . $order->invoice . '.pdf');

  //   SnappyPdf::loadView('karyawan.laporan.cetak', [
  //     'invoice' => Transaksi::with('price')->where('id', $order->id)->get(),
  //     'data' => $order,
  //     'nama_laundry' => PageSettings::where('id', 1)->first()->judul,
  //     'qrCode' => base64_encode(QrCode::format('png')->size(120)->generate($order->invoice))
  //   ])->setPaper('a4')->save($pdfPath);

  //   // Convert PDF to Image
  //   $imagePath = storage_path('app/public/invoices/' . $order->invoice . '.png');
  //   exec("wkhtmltoimage --quality 100 $pdfPath $imagePath");

  //   unlink($pdfPath);
  //   return asset('storage/invoices/' . $order->invoice . '.png');
  // }


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
      $order->kg = (float) $request->kg;
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
            $waApiUrl = notifications_setting::where('id', 1)->first()->wa_api_url . '/send-message';
            $nama_laundry = PageSettings::where('id', 1)->first();

            // Prepare WhatsApp message text
            "═════════════════════\n" .
              $message = "*" . $nama_laundry->judul . "*\n" .
              "*No Resi*: " . $order->invoice . "\n" .
              "═════════════════════\n" .
              "*User*: " . Auth::user()->name . "\n" .
              "*Cabang*: " . Auth::user()->nama_cabang . "\n" .
              "*Telp Cabang*: " . Auth::user()->no_telp . "\n" .
              "*Alamat*: " . Auth::user()->alamat_cabang . "\n" .
              "═════════════════════\n" .
              "*Pelanggan*: " . $order->customer . "\n" .
              "*Telp*: " . $order->customers->no_telp . "\n" .
              "═════════════════════\n" .
              "*Layanan*:\n" .
              "- " . $jenisPakaian->jenis . ": " . $order->kg . " kg × Rp " . number_format($order->harga, 0, ',', '.') . " = *Rp " . number_format(($order->kg * $order->harga), 0, ',', '.') . "*\n" .
              "═════════════════════\n" .
              "*Subtotal*: Rp " . number_format(($order->kg * $order->harga), 0, ',', '.') . "\n";

            $message .= "*Diskon (" . $order->disc . "%)*: -Rp " . number_format((($order->kg * $order->harga) * $order->disc / 100), 0, ',', '.') . "\n";

            $message .= "*TOTAL*: *Rp " . number_format($order->harga_akhir, 0, ',', '.') . "*\n\n" .
              "*Status Pembayaran*: " . ($order->status_payment == 'Success' ? 'Lunas' : 'Belum Lunas') . "\n" .
              "*Metode Bayar*: " . $order->jenis_pembayaran . "\n" .
              "*Tanggal Masuk*: " . Carbon::parse($order->tgl_transaksi)->format('d/m/Y H:i') . "\n" .
              "*Estimasi Selesai*: " . Carbon::parse($order->tgl_transaksi)->addDays($order->hari)->format('d/m/Y H:i') . "\n\n" .
              "*Terima kasih telah menggunakan layanan kami.*\n";

            $data = [
              'number' => $order->customers->no_telp,
              'message' => $message,
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

  public function updateStatusLaundry(Request $request)
  {
    DB::beginTransaction();
    try {
      $transaksi = transaksi::findOrFail($request->id);

      // Handle Process -> Done transition
      if ($transaksi->status_order == 'Process') {
        $transaksi->update([
          'status_order' => 'Done'
        ]);

        // Add point
        $points = User::where('id', $transaksi->customer_id)->firstOrFail();
        $points->increment('point');

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
            'tanggal_selesai' => $transaksi->tgl_ambil,
            'total_harga'     => $transaksi->harga_akhir,
            'email_laundry'   => Auth::user()->email_cabang,
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
          $lamaPengerjaan = $createdAt->diffForHumans($updatedAt, true); // Hitung selisih hingga jam, menit, detik
          $jenisLayanan = harga::where('id', $transaksi->harga_id)->first()->jenis;
          $nama_laundry = PageSettings::where('id', 1)->first();

          $data = [
            'number' => $transaksi->customers->no_telp,
            'message' => "*" . $nama_laundry->judul . "*\n" .
              str_repeat("=", 25) . "\n" .
              "*informasi Pengambilan*:\n" .
              "*Nama Cabang*: " . Auth::user()->nama_cabang . "\n" .
              "*Alamat Cabang*: " . Auth::user()->alamat_cabang . "\n" .
              "*No Telpon*: " . Auth::user()->no_telp . "\n\n" .
              str_repeat("=", 25) . "\n" .
              "*No. Resi*: " . $transaksi->invoice . "\n" .
              "*Pelanggan*: " . $transaksi->customers->name . "\n" .
              "*Tgl Masuk*: " . \Carbon\Carbon::parse($transaksi->created_at)->format('d/m/Y - H:i') . "\n" .
              "*Estimasi*: " . \Carbon\Carbon::parse($transaksi->estimasi_selesai)->format('d/m/Y - H:i') . "\n" .
              "*Jumlah waktu*: " . $lamaPengerjaan . "\n" .
              "*Status Laundry*: " . ($transaksi->status_order == 'Done' ? 'Siap diambil' : $transaksi->status_order) . "\n\n" .
              str_repeat("=", 25) . "\n" .
              "*Detail Layanan*\n" .
              str_repeat("=", 25) . "\n" .
              "▪ " . $jenisLayanan . "\n" .
              "▪ " . $transaksi->kg . " Kg × Rp " . number_format($transaksi->harga, 0, ',', '.') . "\n" .

              str_repeat("=", 25) . "\n" .
              "*Status*: " . ($transaksi->status_payment == 'Success' ? '✅ LUNAS' : '⌛ BELUM LUNAS') . "\n" .
              "*Metode Pembayaran*: " . $transaksi->jenis_pembayaran . "\n" .
              str_repeat("=", 25) . "\n" .
              "*RINCIAN BIAYA*\n" .
              str_repeat("=", 25) . "\n" .
              "Subtotal : Rp " . number_format($transaksi->harga * $transaksi->kg, 0, ',', '.') . "\n" .
              "Diskon   : Rp " . number_format(($transaksi->harga * $transaksi->kg * ($transaksi->diskon ?? 0)) / 100, 0, ',', '.') . "\n" .
              "TOTAL    : Rp " . number_format($transaksi->harga_akhir, 0, ',', '.') . "\n" .
              str_repeat("=", 25) . "\n\n" .

              "Terima kasih telah menggunakan layanan kami.\n" .
              "Pakaian siap diambil di outlet kami."
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
          \Log::info('Data WhatsApp: ' . json_encode($data) . ' Respon WhatsApp: ' . json_encode($responseData));
        }
        DB::commit();
        Session::flash('success', "Status Laundry Berhasil Diubah !");
        return response()->json(['success' => true]);
      }

      // Handle Done -> Payment Success transition
      if ($transaksi->status_order == 'Done' && $transaksi->status_payment == 'Pending') {
        $transaksi->update([
          'status_payment' => 'Success'
        ]);
        DB::commit();
        Session::flash('success', "Status Pembayaran Berhasil Diubah !");
        return response()->json(['success' => true]);
      }

      // Handle Done -> DiTerima transition
      if ($transaksi->status_order == 'Done') {
        $transaksi->update([
          'status_order' => 'DiTerima',
          'tgl_ambil' => Carbon::now()->format('d-m-Y H:i:s')
        ]);

        DB::commit();
        Session::flash('success', "Status Laundry Berhasil Diubah !");
        return response()->json(['success' => true]);
      }

      DB::commit();
      return response()->json(['success' => false, 'message' => 'No valid status transition']);
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error("Update status error: " . $e->getMessage());
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }
}
