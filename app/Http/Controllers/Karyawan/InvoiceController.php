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

    $bank = DataBank::get();
    return view('karyawan.laporan.invoice', compact('invoice', 'data', 'bank'));
  }

  public function cetakinvoice(Request $request)
  {
    // Get transaction data with relationships
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
      ->size(150) // Smaller size for thermal printer
      ->errorCorrection('H') // High error correction
      ->generate($data->invoice));

    // Generate PDF
    $pdf = PDF::loadView('karyawan.laporan.cetak', [
      'invoice' => $invoice,
      'data' => $data,
      'bank' => $bank,
      'nama_laundry' => $nama_laundry,
      'qrCode' => $qrCode
    ])->setPaper([0, 0, 226.77, 700], 'portrait') // 80mm width (226.77 points)
      ->setOption('margin-top', 5)
      ->setOption('margin-bottom', 5)
      ->setOption('margin-left', 5)
      ->setOption('margin-right', 5);

    return $pdf->stream('invoice-' . $data->invoice . '.pdf');
  }
}
