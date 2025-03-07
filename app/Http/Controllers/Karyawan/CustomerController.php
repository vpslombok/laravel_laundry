<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use ErrorException;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Requests\AddCustomerRequest;
use Illuminate\Support\Facades\Hash;
use App\Jobs\RegisterCustomerJob;
use Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\notifications_setting;

class CustomerController extends Controller
{
  // index
  public function index()
  {
    $customer = User::where('karyawan_id', Auth::user()->id)
      ->where('auth', 'Customer')
      ->orderBy('id', 'DESC')->get();
    return view('karyawan.customer.index', compact('customer'));
  }

  // Detail Customer
  public function detail($id)
  {
    $customer = User::with('transaksiCustomer')
      ->where('karyawan_id', Auth::user()->id)
      ->where('id', $id)->first();
    return view('karyawan.customer.detail', compact('customer'));
  }

  // Create
  public function create()
  {
    return view('karyawan.customer.create');
  }

  // Store
  public function store(AddCustomerRequest $request)
  {

    try {
      DB::beginTransaction();

      // Validasi no_telp dan email tidak boleh sama
      $existingUser = User::where('no_telp', $request->no_telp)->orWhere('email', $request->email)->first();
      if ($existingUser) {
        return response()->json(['error' => 'Nomor telepon atau email sudah terdaftar.'], 409);
      }

      $phone_number = $request->no_telp;
      $password = str::random(8);

      $addCustomer = User::create([
        'karyawan_id' => Auth::id(),
        'name'        => $request->name,
        'email'       => $request->email,
        'auth'        => 'Customer',
        'status'      => 'Active',
        'no_telp'     => $phone_number,
        'alamat'      => $request->alamat,
        'password'    => Hash::make($password)
      ]);

      $addCustomer->assignRole($addCustomer->auth);

      if ($addCustomer) {
        // Menyiapkan data Email dan WhatsApp
        $data = array(
          'name'            => $addCustomer->name,
          'email'           => $addCustomer->email,
          'password'        => $password,
          'url_login'       => url('/login'),
          'nama_laundry'    => Auth::user()->nama_cabang,
          'alamat_laundry'  => Auth::user()->alamat_cabang,
          'no_telp'         => $addCustomer->no_telp,
        );
        // Kirim email
        if (setNotificationEmail(1) == 1) {
          dispatch(new RegisterCustomerJob($data));
        }
        // Kirim WhatsApp
        if (setNotificationWhatsappOrderSelesai(1) == 1) {
          $nameCustomer = $addCustomer->name; // get name customer
          $waApiUrl = notifications_setting::where('id', 1)->first()->wa_api_url; // URL API WhatsApp
          $apiKey = notifications_setting::where('id', 1)->first()->api_key; // get API Key dari database
          $message = "Halo Kak *$nameCustomer* 😊\n\n"
            . "Akun Anda telah berhasil dibuat. Berikut adalah informasi login Anda:\n\n"
            . "Email: $addCustomer->email\n"
            . "Password: $password\n"
            . "URL Login: " . url('/login') . "\n\n"
            . "Silakan login menggunakan informasi di atas. Terima kasih telah memilih layanan kami! Semoga hari Anda menyenangkan! 🌟";
          $data = [
            'api_key' => $apiKey,
            'number' => '62' . ltrim($addCustomer->no_telp, '0'), // Nomor penerima
            'sender' => '6285333640674', // Nomor perangkat Anda
            'message' => $message,
          ];
          $ch = curl_init($waApiUrl);
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
          curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          $response = curl_exec($ch);
          curl_close($ch);
          $response = json_decode($response, true);
        }
      }
      DB::commit();
      Session::flash('success', 'Customer Berhasil Ditambah !');
      return redirect('customers');
    } catch (ErrorException $e) {
      DB::rollback();
      throw new ErrorException($e->getMessage());
    }
  }
}
