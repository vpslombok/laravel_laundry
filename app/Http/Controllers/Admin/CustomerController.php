<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class CustomerController extends Controller
{
  public function index()
  {
    $customer = User::where('auth', 'Customer')->get();
    return view('modul_admin.customer.index', compact('customer'));
  }

  public function show($id)
  {
    $customer = User::with('transaksiCustomer')->where('id', $id)->first();
    return view('modul_admin.customer.infoCustomer', compact('customer'));
  }

  // Tambahkan method edit untuk menampilkan data di modal
  public function edit($id)
  {
    $customer = User::findOrFail($id);
    return response()->json($customer);
  }

  // Method untuk handle update data
  public function update(Request $request, $id)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email|unique:users,email,' . $id,
      'no_telp' => 'required|string|max:15',
      'alamat' => 'required|string'
    ]);

    $customer = User::findOrFail($id);
    $customer->update([
      'name' => $request->name,
      'email' => $request->email,
      'no_telp' => $request->no_telp,
      'alamat' => $request->alamat
    ]);

    return response()->json(['success' => 'Data customer berhasil diperbarui']);
  }
}
