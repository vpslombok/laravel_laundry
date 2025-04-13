<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{transaksi, customers, LaundrySetting, User, harga, DataBank, Pengeluaran};
use App\Http\Requests\HargaRequest;
use DB;
use Auth;
use Session;
use Carbon\carbon;
use App\Exports\LabaExport;
use Maatwebsite\Excel\Facades\Excel;

class FinanceController extends Controller
{
  // Finance
  public function index()
  {
    $chartMonthSalary = DB::table('transaksis')
      ->selectRaw('MONTH(created_at) as bulan, SUM(harga_akhir) AS jml')
      ->whereYear('created_at', date('Y'))
      ->whereMonth('created_at', '>=', date('m', strtotime('-1 month')))
      ->groupByRaw('MONTH(created_at)')
      ->orderByRaw('MONTH(created_at)')
      ->get();

    $bulans = '';
    $batas =  12;
    $chartMonth = '';
    for ($_i = 1; $_i <= $batas; $_i++) {
      $bulans = $bulans . (string)$_i . ',';
      $_check = false;
      foreach ($chartMonthSalary as $_data) {
        if ((int)@$_data->bulan === $_i) {
          $chartMonth = $chartMonth . (string)$_data->jml . ',';
          $_check = true;
        }
      }
      if (!$_check) {
        $chartMonth = $chartMonth . '0,';
      }
    }

    $incomeAll = transaksi::where('status_payment', 'Success')->sum('harga_akhir');
    $incomeY = transaksi::where('status_payment', 'Success')->where('tahun', date('Y'))
      ->sum('harga_akhir');

    $incomeM = transaksi::where('status_payment', 'Success')->where('tahun', date('Y'))
      ->where('bulan', ltrim(date('m'), '0'))->sum('harga_akhir');

    $incomeYOld = transaksi::where('status_payment', 'Success')->where('tahun', date("Y", strtotime("-1 year")))
      ->sum('harga_akhir');

    $incomeD = transaksi::where('status_payment', 'Success')->where('tahun', date('Y'))
      ->where('bulan', ltrim(date('m'), '0'))->where('tgl', ltrim(date('d'), '0'))->sum('harga_akhir');

    $incomeDOld = transaksi::where('status_payment', 'Success')->where('tahun', date('Y'))
      ->where('bulan', ltrim(date('m'), '0'))->where('tgl', ltrim(date("d", strtotime("-1 day")), '0'))->sum('harga_akhir');

    $kgDay = transaksi::where('tahun', date('Y'))->where('bulan', ltrim(date('m'), '0'))->where('tgl', ltrim(date('d'), '0'))->sum('kg');
    $kgMonth = transaksi::where('tahun', date('Y'))->where('bulan', ltrim(date('m'), '0'))->sum('kg');
    $kgYear = transaksi::where('tahun', date('Y'))->sum('kg');

    $getCabang = User::whereHas('transaksi', function ($a) {
      $a->where('tahun', date('Y'))
        ->where('bulan', ltrim(date('m'), '0'));
    })
      ->get();

    $target = LaundrySetting::first() ?? new LaundrySetting();

    return view('modul_admin.finance.index', \compact(
      'chartMonth',
      'incomeY',
      'incomeM',
      'incomeYOld',
      'incomeD',
      'incomeDOld',
      'target',
      'incomeAll',
      'getCabang',
      'kgDay',
      'kgMonth',
      'kgYear'
    ));
  }


  // Tambah dan Data Harga
  public function dataharga()
  {
    // Ambil data harga
    $harga = harga::with('harga_user')->orderBy('id', 'DESC')->get();
    // Cek Apakah sudah ada karyawan atau belum
    $karyawan = User::where('auth', 'Karyawan')->first();
    // Ambil list cabang
    $getcabang = User::where('auth', 'Karyawan')->where('status', 'Active')->get();

    // Get Data Bank
    $getBank = DataBank::where('user_id', Auth::id())->count();

    return view('modul_admin.laundri.harga', compact('harga', 'karyawan', 'getcabang', 'getBank'));
  }

  // Proses Simpan Harga
  public function hargastore(HargaRequest $request)
  {
    $addharga = new harga();
    $addharga->user_id = $request->user_id;
    $addharga->jenis = $request->jenis;
    $addharga->kg = 1000; // satuan gram
    $addharga->harga = preg_replace('/[^A-Za-z0-9\-]/', '', $request->harga); // Remove special caracter
    $addharga->hari = $request->hari;
    $addharga->status = 1; //aktif
    $addharga->save();

    Session::flash('success', 'Tambah Data Harga Berhasil');
    return redirect('data-harga');
  }

  // Proses edit harga
  public function hargaedit(Request $request)
  {
    $editharga = harga::find($request->id_harga);
    $editharga->update([
      'jenis' => $request->jenis,
      'kg'    => $request->kg,
      'harga' => $request->harga,
      'hari' => $request->hari,
      'status' => $request->status,
    ]);
    Session::flash('success', 'Edit Data Harga Berhasil');
    return $editharga;
  }

  public function pengeluaran()
  {
    // Get authenticated user ID
    $userId = Auth::id();

    // Eager load relationships and order by latest
    $pengeluaran = Pengeluaran::with(['cabang' => function ($query) {
      $query->where('auth', 'Karyawan')->select('id', 'nama_cabang');
    }])
      ->orderByDesc('tanggal')
      ->get();

    // Check if any karyawan exists (simplified query)
    $karyawanExists = User::where('auth', 'Karyawan')->exists();

    // Get active cabang list with only needed columns
    $cabangList = User::where('auth', 'Karyawan')
      ->where('status', 'Active')
      ->select('id', 'nama_cabang', 'name')
      ->get();

    // Count bank data (simplified)
    $bankCount = DataBank::where('user_id', $userId)->count();

    // Calculate statistics
    $statistics = [
      'hari_ini' => Pengeluaran::whereDate('tanggal', today())->sum('jumlah'),
      'minggu_ini' => Pengeluaran::whereBetween('tanggal', [now()->startOfWeek(), now()->endOfWeek()])->sum('jumlah'),
      'bulan_ini' => Pengeluaran::whereMonth('tanggal', now()->month)->sum('jumlah'),
      'total' => Pengeluaran::sum('jumlah')
    ];

    return view('modul_admin.pengeluaran.index', [
      'pengeluaran' => $pengeluaran,
      'karyawanExists' => $karyawanExists,
      'cabangList' => $cabangList,
      'getBank' => $bankCount,
      'statistik' => $statistics,
      'totalPengeluaran' => $statistics['total']
    ]);
  }
  public function pengeluaranstore(Request $request)
  {
    $pengeluaran = new Pengeluaran();
    $pengeluaran->user_id = $request->cabang_id;
    $pengeluaran->tanggal = $request->tanggal;
    $pengeluaran->jenis = $request->jenis;
    $pengeluaran->keterangan = $request->keterangan;
    $pengeluaran->jumlah = $request->jumlah;
    $pengeluaran->save();
    Session::flash('success', 'Tambah Data Pengeluaran Berhasil');
    return redirect()->route('pengeluaran.index');
  }
  public function update(Request $request, $id)
  {
    $request->validate([
      'tanggal' => 'required|date',
      'jenis' => 'required|string|max:50',
      'keterangan' => 'required|string|max:255',
      'jumlah' => 'required|numeric|min:0',
      'cabang_id' => 'required|exists:users,id'
    ]);

    try {
      $pengeluaran = Pengeluaran::findOrFail($id);

      $pengeluaran->update([
        'tanggal' => $request->tanggal,
        'jenis' => $request->jenis,
        'keterangan' => $request->keterangan,
        'jumlah' => $request->jumlah,
        'cabang_id' => $request->cabang_id,
      ]);

      Session::flash('success', 'Data Pengeluaran Berhasil Diubah');
      return redirect()->route('pengeluaran.index');
    } catch (\Exception $e) {
      Session::flash('error', 'Gagal mengupdate data: ' . $e->getMessage());
      return back()->withInput();
    }
  }

  public function destroy($id)
  {
    try {
      $pengeluaran = Pengeluaran::findOrFail($id);
      $pengeluaran->delete();

      return response()->json([
        'success' => true,
        'message' => 'Data pengeluaran berhasil dihapus'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal menghapus data: ' . $e->getMessage()
      ], 500);
    }
  }

  // halaman report laba
  public function laba(Request $request)
  {
    $tanggal_awal = $request->get('tanggal_awal') ?? Carbon::now()->startOfMonth()->format('Y-m-d');
    $tanggal_akhir = $request->get('tanggal_akhir') ?? Carbon::now()->format('Y-m-d');

    if ($tanggal_akhir > Carbon::now()->format('Y-m-d')) {
      Session::flash('error', 'Tanggal akhir tidak boleh melebihi tanggal yang berjalan');
      return back()->withInput();
    }

    if ($tanggal_awal > $tanggal_akhir) {
      Session::flash('error', 'Tanggal awal tidak boleh lebih besar dari tanggal akhir');
      return back()->withInput();
    }

    // Get all dates between the range
    $dates = [];
    $currentDate = Carbon::parse($tanggal_awal);
    $endDate = Carbon::parse($tanggal_akhir);

    while ($currentDate <= $endDate) {
      $dates[] = $currentDate->format('Y-m-d');
      $currentDate->addDay();
    }

    $reportData = [];

    foreach ($dates as $date) {
      // Get successful transactions for the day
      $pemasukan = transaksi::where('status_payment', 'Success')
        ->whereDate('created_at', $date)
        ->sum('harga_akhir');
      $total_kg = transaksi::where('status_payment', 'Success')
        ->whereDate('created_at', $date)
        ->sum('kg');

      // Get expenses for the day
      $pengeluaran = pengeluaran::whereDate('tanggal', $date)
        ->sum('jumlah');

      $laba = $pemasukan - $pengeluaran;

      if ($pemasukan > 0 || $pengeluaran > 0) {
        $reportData[] = [
          'tanggal' => $date,
          'pemasukan' => $pemasukan,
          'total_kg' => $total_kg,
          'pengeluaran' => $pengeluaran,
          'laba' => $laba,
          'transaksi' => Transaksi::where('status_payment', 'Success')
            ->whereDate('created_at', $date)
            ->with('customers')
            ->get()
        ];
      }
    }

    return view('modul_admin.report_laba.index', [
      'laporan_laba' => $reportData,
      'tanggal_awal' => $tanggal_awal,
      'tanggal_akhir' => $tanggal_akhir
    ]);
  }

  public function labaDetail(Request $request)
  {
    $tanggal = $request->tanggal;

    $transaksi = transaksi::where('status_payment', 'Success')
      ->whereDate('created_at', $tanggal)
      ->with('customers')
      ->get();

    $pengeluaran = pengeluaran::whereDate('tanggal', $tanggal)
      ->get();

    return response()->json([
      'transaksi' => $transaksi,
      'pengeluaran' => $pengeluaran,
      'total_pemasukan' => $transaksi->sum('harga_akhir'),
      'total_pengeluaran' => $pengeluaran->sum('jumlah')
    ]);
  }

  public function labaExport(Request $request)
  {
    $tanggal_awal = $request->tanggal_awal ?? now()->subMonth()->format('Y-m-d');
    $tanggal_akhir = $request->tanggal_akhir ?? now()->format('Y-m-d');

    $filename = 'laporan-laba-' . Carbon::parse($tanggal_awal)->format('d-m-Y') . '-sd-' . Carbon::parse($tanggal_akhir)->format('d-m-Y') . '.xlsx';

    return Excel::download(new LabaExport($tanggal_awal, $tanggal_akhir), $filename);
  }
}
