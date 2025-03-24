@extends('layouts.backend')
@section('title','Karyawan - Invoice Customer')
@section('header','Invoice Customer')
@section('content')
<div class="col-md-12">
    <div class="card card-body printableArea" style="border: none; box-shadow: 0 0 25px rgba(0,0,0,0.1); border-radius: 10px; overflow: hidden;">
        <!-- Invoice Header -->
        <div class="invoice-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; color: white;">
            <div class="row">
                <div class="col-md-6">
                    <h2 style="margin: 0; font-weight: 600;"><i class="fa fa-file-text-o"></i> INVOICE LAUNDRY</h2>
                    <p style="margin: 5px 0 0; opacity: 0.9;">{{$data->user->nama_cabang}}</p>
                </div>
                <div class="col-md-6 text-right">
                    <h3 style="margin: 0; font-weight: 600;">#{{$data->invoice}}</h3>
                    <p style="margin: 5px 0 0; opacity: 0.9;">
                        {{\Carbon\Carbon::parse($data->tgl_transaksi)->format('d F Y')}}
                    </p>
                </div>
            </div>
        </div>

        <!-- Business and Customer Info -->
        <div class="row p-4" style="border-bottom: 1px solid #f0f0f0;">
            <div class="col-md-6">
                <div class="business-info">
                    <h4 class="text-primary" style="color: #f0f0f0; margin-bottom: 15px;">
                        <i class="fa fa-building-o"></i> Informasi Laundry
                    </h4>
                    <div class="info-item" style="margin-bottom: 8px;">
                        <span style="display: inline-block; width: 120px; color: #666;">Diterima Oleh</span>
                        <span>: {{$data->user->name}}</span>
                    </div>
                    <div class="info-item" style="margin-bottom: 8px;">
                        <span style="display: inline-block; width: 120px; color: #666;">Alamat</span>
                        <span>: {{$data->user->alamat_cabang}}</span>
                    </div>
                    <div class="info-item">
                        <span style="display: inline-block; width: 120px; color: #666;">No. Telp</span>
                        <span>: {{$data->user->no_telp ?: '-'}}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="customer-info">
                    <h4 class="text-primary" style="color: #764ba2; margin-bottom: 15px;">
                        <i class="fa fa-user-o"></i> Informasi Pelanggan
                    </h4>
                    <div class="info-item" style="margin-bottom: 8px;">
                        <span style="display: inline-block; width: 120px; color: #666;">Nama</span>
                        <span>: {{$data->customers->name}}</span>
                    </div>
                    <div class="info-item" style="margin-bottom: 8px;">
                        <span style="display: inline-block; width: 120px; color: #666;">Alamat</span>
                        <span>: {{$data->customers->alamat}}</span>
                    </div>
                    <div class="info-item">
                        <span style="display: inline-block; width: 120px; color: #666;">No. Telp</span>
                        <span>: {{$data->customers->no_telp ?: '-'}}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Timeline -->
        <div class="row p-4" style="background: #f9f9f9; border-bottom: 1px solid #f0f0f0;">
            <div class="col-md-6">
                <div class="timeline-item">
                    <span style="color: #666;"><i class="fa fa-calendar-check-o"></i> Tanggal Masuk</span>
                    <p style="margin: 5px 0 0; font-weight: 500;">
                        {{\Carbon\Carbon::parse($data->tgl_transaksi)->format('d F Y H:i')}}
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="timeline-item">
                    <span style="color: #666;"><i class="fa fa-calendar-times-o"></i> Tanggal Diambil</span>
                    <p style="margin: 5px 0 0; font-weight: 500;">
                        @if($data->tgl_ambil)
                        {{\Carbon\Carbon::parse($data->tgl_ambil)->format('d F Y H:i')}}
                        @else
                        <span class="text-warning">Belum Diambil</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="p-4">
            <h4 style="color: #764ba2; margin-bottom: 20px;">
                <i class="fa fa-list-ul"></i> Detail Pesanan
            </h4>
            <div class="table-responsive">
                <table class="table" style="border: 1px solid #f0f0f0;">
                    <thead style="background: #f5f5f5;">
                        <tr>
                            <th class="text-center" style="width: 5%;">#</th>
                            <th style="width: 45%;">Jenis Pakaian</th>
                            <th class="text-right" style="width: 15%;">Berat</th>
                            <th class="text-right" style="width: 15%;">Harga</th>
                            <th class="text-right" style="width: 20%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice as $item)
                        <tr>
                            <td class="text-center">1</td>
                            <td>{{$item->price->jenis}}</td>
                            <td class="text-right">{{$item->kg}} kg</td>
                            <td class="text-right">{{Rupiah::getRupiah($item->harga)}}/kg</td>
                            <td class="text-right">{{Rupiah::getRupiah($item->kg * $item->harga)}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="row p-4" style="background: #f9f9f9; border-top: 1px solid #f0f0f0;">
            <div class="col-md-6">
                <div class="payment-method">
                    <h5 style="color: #764ba2; margin-bottom: 15px;">
                        <i class="fa fa-credit-card"></i> Metode Pembayaran
                    </h5>
                    <div style="background: white; padding: 10px 15px; border-radius: 5px; display: inline-block;">
                        <i class="fa fa-check-circle" style="color: #28a745;"></i>
                        <span style="margin-left: 5px; font-weight: 500;">{{$data->jenis_pembayaran}}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="payment-summary text-right">
                    <div class="summary-item" style="margin-bottom: 8px;">
                        <span style="display: inline-block; width: 150px; text-align: right; padding-right: 15px;">Subtotal:</span>
                        <span style="display: inline-block; width: 150px; text-align: right;">{{Rupiah::getRupiah($item->kg * $item->harga)}}</span>
                    </div>
                    <div class="summary-item" style="margin-bottom: 8px;">
                        <span style="display: inline-block; width: 150px; text-align: right; padding-right: 15px;">Diskon ({{$item->disc ?: 0}}%):</span>
                        <span style="display: inline-block; width: 150px; text-align: right; color: #dc3545;">- {{Rupiah::getRupiah(($item->kg * $item->harga * $item->disc)/100)}}</span>
                    </div>
                    <div class="summary-item" style="border-top: 1px solid #ddd; padding-top: 10px; margin-top: 10px;">
                        <span style="display: inline-block; width: 150px; text-align: right; padding-right: 15px; font-weight: bold; font-size: 16px;">Total Bayar:</span>
                        <span style="display: inline-block; width: 150px; text-align: right; font-weight: bold; font-size: 18px; color: #764ba2;">{{Rupiah::getRupiah($item->harga_akhir)}}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-4 text-center" style="background: #f5f5f5;">
            <p style="margin-bottom: 5px; color: #666;">Terima kasih telah mempercayakan laundry kepada kami</p>
            <p style="margin-bottom: 0; color: #666;">
                <i class="fa fa-phone"></i> {{$data->user->no_telp ?: '-'}} |
                <i class="fa fa-map-marker"></i> {{$data->user->alamat_cabang}}
            </p>

            <div class="mt-3">
                <a href="{{url('pelayanan')}}" class="btn btn-light" style="border: 1px solid #ddd; margin-right: 10px;">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
                <a href="{{url('cetak-invoice/'.$item->id. '/print')}}" target="_blank" class="btn btn-primary">
                    <i class="fa fa-print"></i> Cetak Invoice
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .printableArea {
        border-radius: 10px;
    }

    .invoice-header {
        color: white;
    }

    .table th {
        background-color: #f8f9fa !important;
        border-top: none !important;
    }

    @media print {
        body {
            background: none !important;
            padding: 0 !important;
        }

        .printableArea {
            box-shadow: none !important;
            border: none !important;
        }

        .no-print {
            display: none !important;
        }
    }
</style>
@endsection