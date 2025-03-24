<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{transaksi,PageSettings};

class FrontController extends Controller
{

  //Index
  public function index()
  {
    $setpage = PageSettings::first();
    $transaksi = transaksi::first();

    return view('frontend.index', compact('setpage', 'transaksi'));
  }

  //Search
  public function search(Request $request)
  {
      $search = transaksi::where('invoice', $request->search_status);
      if ($search->count() == 0) {
          $return = 0;
      } else {
          $data = $search->first();
          $return = [
              'user' => namaCustomer($data->user_id),
              'customer' => $data->customer,
              'tgl_transaksi' => date('d-m-Y H:i', strtotime($data->created_at)),
              'status_order' => $data->status_order == 'Process' ? 'Proses Pencucian' : ($data->status_order == 'Done' ? 'Siap Diambil' : $data->status_order),
              'jenis_laundry' => $data->jenis_laundry,
              'invoice' => $data->invoice,
              'estimasi_selesai' => date('d-m-Y H:i', strtotime($data->created_at . ' + ' . $data->hari . ' days')),
              'tgl_ambil' => $data->tgl_ambil ? date('d-m-Y H:i', strtotime($data->tgl_ambil)) : '',
          ];
      }
      return $return;
  }
}
