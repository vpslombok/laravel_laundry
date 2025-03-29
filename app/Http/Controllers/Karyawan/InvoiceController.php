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
      ->size(120) // Reduced size for single page
      ->errorCorrection('H')
      ->generate($data->invoice));

    // Calculate dynamic height based on content
    $itemCount = count($invoice);
    $baseHeight = 400; // Base height for minimal content
    $dynamicHeight = $baseHeight + ($itemCount * 15); // Add 15pt per item

    // Generate PDF
    $pdf = PDF::loadView('karyawan.laporan.cetak', compact('invoice', 'data', 'bank', 'nama_laundry', 'qrCode'))
      ->setPaper([0, 0, 226.77, $dynamicHeight], 'portrait') // Dynamic height
      ->setOptions([
        'margin-top' => 2,
        'margin-bottom' => 2,
        'margin-left' => 2,
        'margin-right' => 2,
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true
      ]);

    return $pdf->stream('invoice-' . $data->invoice . '.pdf');
  }
}
