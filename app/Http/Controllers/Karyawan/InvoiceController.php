<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{transaksi, DataBank, PageSettings};
use Auth;
use PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InvoiceController extends Controller
{
  // Invoice
  public function invoicekar(Request $request)
  {
    $invoice = transaksi::with('price')
      ->where('user_id', Auth::id())
      ->where('id', $request->id)
      ->get();

    $data = transaksi::with('customers', 'user')
      ->where('user_id', Auth::id())
      ->where('id', $request->id)
      ->first();

    $bank = DataBank::all();
    return view('karyawan.laporan.invoice', compact('invoice', 'data', 'bank'));
  }

  public function cetakinvoice(Request $request)
  {
    $invoice = Transaksi::with('price')
      ->where('user_id', Auth::id())
      ->where('id', $request->id)
      ->get();

    $data = Transaksi::with('customers', 'user')
      ->where('user_id', Auth::id())
      ->where('id', $request->id)
      ->first();

    $nama_laundry = PageSettings::where('id', 1)->first()->judul;
    $bank = DataBank::get();

    // Generate QR Code
    $qrCode = base64_encode(QrCode::format('png')
      ->size(120)
      ->errorCorrection('H')
      ->generate($data->invoice));

    $pdf = PDF::loadView('karyawan.laporan.cetak', compact('invoice', 'data', 'bank', 'nama_laundry', 'qrCode'))
      ->setPaper([0, 0, 226.77, 841.89], 'portrait'); // 80mm width (226.77pt) x unlimited height

    return $pdf->stream('invoice-' . $data->invoice . '.pdf');
  }
}
