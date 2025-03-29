<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AddKaryawanRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class KaryawanController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    try {
      $kry = User::where('auth', 'Karyawan')
        ->orderBy('created_at', 'desc')
        ->get();

      return view('modul_admin.pengguna.kry', compact('kry'));
    } catch (\Exception $e) {
      Log::error('Error in KaryawanController@index: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data karyawan.');
    }
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    return view('modul_admin.pengguna.addkry');
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(AddKaryawanRequest $request)
  {
    try {
      $phone_number = preg_replace('/^0/', '62', $request->no_telp);

      $karyawan = User::create([
        'name'          => $request->name,
        'email'         => $request->email,
        'nama_cabang'   => $request->nama_cabang,
        'alamat'        => $request->alamat,
        'alamat_cabang' => $request->alamat_cabang,
        'no_telp'       => $phone_number,
        'status'        => 'Active',
        'auth'          => 'Karyawan',
        'password'      => Hash::make($request->password)
      ]);

      $karyawan->assignRole('Karyawan');

      return redirect()->route('karyawan.index')
        ->with('success', 'Karyawan berhasil ditambahkan.');
    } catch (\Exception $e) {
      Log::error('Error in KaryawanController@store: ' . $e->getMessage());
      return redirect()->back()
        ->withInput()
        ->with('error', 'Gagal menambahkan karyawan. Silakan coba lagi.');
    }
  }

  /**
   * Update the status of the specified resource.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function updateStatus(Request $request)
  {
    try {
      $karyawan = User::findOrFail($request->id);

      $newStatus = $karyawan->status == 'Active' ? 'Not Active' : 'Active';
      $karyawan->update(['status' => $newStatus]);

      return response()->json([
        'success' => true,
        'message' => 'Status karyawan berhasil diupdate.',
        'new_status' => $newStatus
      ]);
    } catch (\Exception $e) {
      Log::error('Error in KaryawanController@updateStatus: ' . $e->getMessage());
      return response()->json([
        'success' => false,
        'message' => 'Gagal mengupdate status karyawan.'
      ], 500);
    }
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    try {
      $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
        'no_telp' => 'required|string|max:15',
        'nama_cabang' => 'required|string',
        'alamat_cabang' => 'required|string',
        'status' => 'required|in:Active,Not Active'
      ]);

      $karyawan = User::findOrFail($id);
      $karyawan->update($validated);

      return redirect()->route('karyawan.index')
        ->with('success', 'Data karyawan berhasil diupdate.');
    } catch (\Exception $e) {
      Log::error('Error in KaryawanController@update: ' . $e->getMessage());
      return redirect()->back()
        ->withInput()
        ->with('error', 'Gagal mengupdate data karyawan. Silakan coba lagi.');
    }
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    try {
      $karyawan = User::findOrFail($id);
      $karyawan->delete();

      return redirect()->route('karyawan.index')
        ->with('success', 'Karyawan berhasil dihapus.');
    } catch (\Exception $e) {
      Log::error('Error in KaryawanController@destroy: ' . $e->getMessage());
      return redirect()->back()
        ->with('error', 'Gagal menghapus karyawan. Silakan coba lagi.');
    }
  }
}
