@extends('layouts.backend')
@section('title','Admin - Invoice Customer')
@section('header','Invoice Customer')
@section('content')
<div class="col-md-12">
    <div class="card card-body printableArea" style="border: none; box-shadow: 0 0 20px rgba(0,0,0,0.1);">
        <!-- Invoice Header -->
        <div class="invoice-header" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); padding: 20px; border-radius: 8px 8px 0 0;">
            <div class="row">
                <div class="col-md-6">
                    <h2 style="color: white; margin: 0;"><i class="fa fa-file-text"></i> INVOICE</h2>
                </div>
                <div class="col-md-6 text-right">
                    <h3 style="color: white; margin: 0;">#{{$dataInvoice->invoice}}</h3>
                </div>
            </div>
        </div>

        <!-- Business and Customer Info -->
        <div class="row p-4" style="border-bottom: 1px solid #eee;">
            <div class="col-md-6">
                <div class="business-info">
                    <h4 class="text-primary">{{$dataInvoice->user->nama_cabang}}</h4>
                    <div class="info-item">
                        <span class="info-label"><i class="fa fa-user"></i> Diterima Oleh:</span>
                        <span class="info-value">{{$dataInvoice->user->name}}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fa fa-map-marker"></i> Alamat:</span>
                        <span class="info-value">{{$dataInvoice->user->alamat_cabang}}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fa fa-phone"></i> No. Telp:</span>
                        <span class="info-value">{{$dataInvoice->user->no_telp == 0 ? '-' : $dataInvoice->user->no_telp}}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="customer-info">
                    <h4 class="text-primary">Detail Pelanggan</h4>
                    <div class="info-item">
                        <span class="info-label"><i class="fa fa-user"></i> Nama:</span>
                        <span class="info-value">{{$dataInvoice->customers->name}}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fa fa-map-marker"></i> Alamat:</span>
                        <span class="info-value">{{$dataInvoice->customers->alamat}}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fa fa-phone"></i> No. Telp:</span>
                        <span class="info-value">{{$dataInvoice->customers->no_telp == 0 ? '-' : $dataInvoice->customers->no_telp}}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Dates -->
        <div class="row p-4" style="background: #f9f9f9; border-bottom: 1px solid #eee;">
            <div class="col-md-6">
                <div class="date-info">
                    <div class="info-item">
                        <span class="info-label"><i class="fa fa-calendar-check-o"></i> Tanggal Masuk:</span>
                        <span class="info-value">{{carbon\carbon::parse($dataInvoice->customers->tgl_transaksi)->format('d F Y H:i')}}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="date-info">
                    <div class="info-item">
                        <span class="info-label"><i class="fa fa-calendar-times-o"></i> Tanggal Diambil:</span>
                        <span class="info-value">
                            @foreach($invoice as $item)
                            @if($item->tgl_ambil == "")
                            Status masih {{$item->status_order}}
                            @else
                            {{carbon\carbon::parse($item->tgl_ambil)->format('d F Y H:i')}}
                            @endif
                            @endforeach
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="p-4">
            <h4 class="text-primary mb-3">Detail Pesanan</h4>
            <div class="table-responsive">
                <table class="table table-hover" style="border: 1px solid #f0f0f0;">
                    <thead style="background: #f5f5f5;">
                        <tr>
                            <th class="text-center" style="width: 5%;">#</th>
                            <th style="width: 45%;">Jenis Pakaian</th>
                            <th class="text-right" style="width: 15%;">Berat</th>
                            <th class="text-right" style="width: 15%;">Harga</th>
                            <th class="text-right" style="width: 20%;">Sub Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $total = 0; @endphp
                        @foreach ($invoice as $key => $item)
                        @php
                        $subtotal = $item->kg * $item->harga;
                        $total += $subtotal;
                        @endphp
                        <tr>
                            <td class="text-center">{{$key+1}}</td>
                            <td>{{$item->price->jenis}}</td>
                            <td class="text-right">{{$item->kg}} Kg</td>
                            <td class="text-right">{{Rupiah::getRupiah($item->harga)}} /Kg</td>
                            <td class="text-right">{{Rupiah::getRupiah($subtotal)}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="row p-4" style="background: #f9f9f9; border-top: 1px solid #eee;">
            <div class="col-md-6">
                <div class="payment-method">
                    <h5 style="color: #764ba2; margin-bottom: 15px;">
                        <i class="fa fa-credit-card"></i> Metode Pembayaran
                    </h5>
                    @if($dataInvoice->jenis_pembayaran == 'Transfer')
                    <div style="background: white; padding: 10px 15px; border-radius: 5px; display: inline-block;">
                        <i class="fa fa-check-circle" style="color: #28a745;"></i>
                        <span style="margin-left: 5px; font-weight: 500;">{{$dataInvoice->jenis_pembayaran}}</span>
                        @foreach($bank as $b)
                        <div class="bank-info">
                            <span class="bank-name">{{$b->nama_bank ?? 'Tidak Tersedia'}}-</span>
                            <span class="bank-account">{{$b->no_rekening ?? 'Tidak Tersedia'}}-</span>
                            <span class="account-holder">{{$b->nama_pemilik ?? 'Tidak Tersedia'}}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div style="background: white; padding: 10px 15px; border-radius: 5px; display: inline-block;">
                        <i class="fa fa-check-circle" style="color: #28a745;"></i>
                        <span style="margin-left: 5px; font-weight: 500;">{{$dataInvoice->jenis_pembayaran}}</span>
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="payment-summary text-right">
                    <div class="summary-item">
                        <span class="summary-label">Total:</span>
                        <span class="summary-value">{{Rupiah::getRupiah($total)}}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Diskon ({{$item->disc ? $item->disc : 0}}%):</span>
                        <span class="summary-value text-danger">- {{Rupiah::getRupiah(($total * $item->disc) / 100)}}</span>
                    </div>
                    <div class="summary-item" style="border-top: 1px solid #ddd; padding-top: 10px; margin-top: 10px;">
                        <span class="summary-label" style="font-weight: bold; font-size: 16px;">Total Bayar:</span>
                        <span class="summary-value" style="font-weight: bold; font-size: 18px; color: #2575fc;">{{Rupiah::getRupiah($total - (($total * $item->disc) / 100))}}</span>
                        <span style="display: inline-block; width: 150px; text-align: right; padding-right: 15px; font-weight: bold; font-size: 16px;">Status :</span>
                        @if($item->status_payment == 'Success')
                        <span style="display: inline-block; width: 150px; text-align: right; font-weight: bold; font-size: 18px; color: green;">{{$item->status_payment}}</span>
                        @else
                        <span style="display: inline-block; width: 150px; text-align: right; font-weight: bold; font-size: 18px; color: red;">{{$item->status_payment}}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-4 text-center" style="background: #f5f5f5; border-radius: 0 0 8px 8px;">
            <p style="margin-bottom: 5px;">Terima kasih telah menggunakan layanan kami</p>
            <p style="margin-bottom: 0;"><i class="fa fa-phone"></i> {{$dataInvoice->user->no_telp == 0 ? '-' : $dataInvoice->user->no_telp}} |
                <i class="fa fa-envelope"></i> {{$dataInvoice->user->email ?? '-'}}
            </p>

            <div class="mt-3">
                <a href="{{route('transaksi.index')}}" class="btn btn-light" style="border: 1px solid #ddd;">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .info-item {
        margin-bottom: 10px;
        display: flex;
    }

    .info-label {
        width: 120px;
        font-weight: 500;
        color: #555;
    }

    .info-value {
        flex: 1;
    }

    .summary-item {
        margin-bottom: 8px;
    }

    .summary-label {
        display: inline-block;
        width: 150px;
        text-align: right;
        padding-right: 15px;
    }

    .summary-value {
        display: inline-block;
        width: 150px;
        text-align: right;
    }

    .table {
        margin-bottom: 0;
    }

    .table th {
        border-top: none;
    }

    .text-primary {
        color: #2575fc !important;
    }

    .printableArea {
        border-radius: 8px;
        overflow: hidden;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        body {
            background: none;
            padding: 0;
        }

        .printableArea {
            box-shadow: none;
            border: none;
        }
    }
</style>
@endsection