<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', 'FrontController@index');

// Frontend
Route::get('pencarian-laundry', 'FrontController@search');

Auth::routes([
  'register' => false,
]);

Route::middleware('auth')->group(function () {
  Route::get('/home', 'HomeController@index')->name('home');

  Route::get('read-notifikasi', 'HomeController@readNotifikasi');
  // Modul Admin
  Route::prefix('/')->middleware('role:Admin')->group(function () {
    Route::resource('admin', 'Admin\AdminController');

    // Pengguna/karyawan
    Route::resource('karyawan', 'Admin\KaryawanController')->except(['show']);
    Route::get('update-status-karyawan', 'Admin\KaryawanController@updateKaryawan')->name('karyawan.update-status');
    Route::put('update-karyawan/{id}', 'Admin\KaryawanController@update')->name('karyawan.update-data');

    // Customer
    Route::resource('customer', 'Admin\CustomerController');

    // Data Transaksi
    Route::resource('transaksi', 'Admin\TransaksiController');
    Route::get('filter-transaksi', 'Admin\TransaksiController@filtertransaksi'); // filter data transaksi by karyawan
    Route::get('invoice-customer/{invoice}', 'Admin\TransaksiController@invoice'); // lihat invoice
    Route::get('hapus-transaksi/{id}', 'Admin\TransaksiController@hapusTransaksi');
    Route::post('hapus-transaksi-terpilih', 'Admin\TransaksiController@hapusTransaksiTerpilih');

    // admin akses
    Route::get('data-harga', 'Admin\FinanceController@dataharga');
    Route::post('harga-store', 'Admin\FinanceController@hargastore');
    Route::get('edit-harga', 'Admin\FinanceController@hargaedit');

    // route pengeluaran
    Route::get('pengeluaran', 'Admin\FinanceController@pengeluaran')->name('pengeluaran.index');
    Route::post('pengeluaran-store', 'Admin\FinanceController@pengeluaranstore')->name('pengeluaran.store');
    Route::get('edit-pengeluaran/{id}', 'Admin\FinanceController@pengeluaranedit')->name('pengeluaran.edit');
    Route::put('/pengeluaran/{id}', 'Admin\FinanceController@update')->name('pengeluaran.update');
    Route::delete('/pengeluaran/{id}', 'Admin\FinanceController@destroy')->name('pengeluaran.destroy');
    Route::get('pengeluaran-filter', 'Admin\FinanceController@filterPengeluaran')->name('pengeluaran.filter'); // filter data pengeluaran

    // route report laba
    Route::get('report-laba', 'Admin\FinanceController@laba')->name('laba.index');
    Route::get('laba-filter', 'Admin\FinanceController@filterLaba')->name('laba.filter'); // filter data laba
    Route::get('/admin/finance/laba-detail', 'Admin\FinanceController@labaDetail')->name('finance.laba-detail');
    Route::get('/admin/finance/laba-export', 'Admin\FinanceController@labaExport')->name('finance.laba-export');



    // Finance
    Route::get('finance', 'Admin\FinanceController@index')->name('finance.index');

    // Notifikasi
    Route::get('read-notification', 'Admin\AdminController@notif');

    // Setting
    Route::get('settings', 'Admin\SettingsController@setting');
    Route::put('proses-setting-page/{id}', 'Admin\SettingsController@proses_set_page')->name('seting-page.update');
    Route::put('set-theme/{id}', 'Admin\SettingsController@set_theme')->name('setting-theme.update');
    Route::put('set-target-laundry/{id}', 'Admin\SettingsController@set_target_laundry')->name('set-target.update');
    Route::post('add-bank', 'Admin\SettingsController@bank')->name('setting.bank');
    Route::put('set-notif/{id}', 'Admin\SettingsController@notif')->name('set-notif.update');

    // Profile
    Route::get('profile-admin/{id}', 'Admin\AdminController@profile');
    Route::get('profile-admin-edit', 'Admin\AdminController@edit_profile');

    // Dodkumentasi
    Route::get('dokumentasi', 'Admin\DokumentasiController@index'); // Dokumentasi
    Route::get('dokumentasi/tentang', 'Admin\DokumentasiController@tentang'); // Tentang
    Route::get('dokumentasi/instalasi-penggunaan', 'Admin\DokumentasiController@instalasi'); // Instalasi & Penggunaan
    Route::get('dokumentasi/versi', 'Admin\DokumentasiController@versi'); // Versi dan Pembaruan
    Route::get('dokumentasi/notifikasi', 'Admin\DokumentasiController@notifikasi'); // Notifikasi

  });

  // Modul Karyawan
  Route::prefix('/')->middleware('role:Karyawan')->group(function () {
    Route::resource('pelayanan', 'Karyawan\PelayananController');
    // Transaksi
    Route::get('add-order', 'Karyawan\PelayananController@addorders');
    Route::get('update-status-laundry', 'Karyawan\PelayananController@updateStatusLaundry')->name('update.status.laundry');
    Route::get('history', 'Karyawan\PelayananController@histori')->name('history');
    Route::post('update-histori-laundry', 'Karyawan\PelayananController@updateHistory')->name('update.histori.laundry');
    // Customer
    Route::get('customers', 'Karyawan\CustomerController@index');
    Route::get('customers/{id}', 'Karyawan\CustomerController@detail');
    Route::get('customers-create', 'Karyawan\CustomerController@create');
    Route::post('customers-store', 'Karyawan\CustomerController@store');

    // Filter
    Route::get('listharga', 'Karyawan\PelayananController@listharga');
    Route::get('listhari', 'Karyawan\PelayananController@listhari');
    Route::get('total-harga', 'Karyawan\PelayananController@totalHarga');
    // customer name
    Route::get('getCustomerName', 'Karyawan\PelayananController@getCustomerName');
    // Laporan
    Route::get('laporan', 'Karyawan\LaporanController@laporan');
    Route::get('export-excel', 'Karyawan\LaporanController@exportExcel');

    // Invoice
    Route::get('invoice-kar/{id}', 'Karyawan\InvoiceController@invoicekar');
    Route::get('cetak-invoice/{id}/print', 'Karyawan\InvoiceController@cetakinvoice');

    // Profile
    Route::get('profile-karyawan/{id}', 'Karyawan\ProfileController@karyawanProfile');
    Route::put('profile-karyawan/update/{id}', 'Karyawan\ProfileController@karyawanProfileSave');

    // Setting
    Route::get('karyawan-setting', 'Karyawan\SettingsController@setting');
    Route::put('proses-setting-karyawan/{id}', 'Karyawan\SettingsController@proses_setting_karyawan')->name('proses-setting-karyawan.update');
  });


  // Modul Customer
  Route::prefix('/')->middleware('role:Customer')->group(function () {
    // Setting
    Route::get('setitng', 'Customer\SettingController@index')->name('customer.setting');
    Route::put('setitng/{id}', 'Customer\SettingController@settingUpdateCustomer')->name('customer.setting-update');

    // Profile
    Route::get('me', 'Customer\ProfileController@index');
    Route::put('me/{id}', 'Customer\ProfileController@updateProfile');
  });
});
