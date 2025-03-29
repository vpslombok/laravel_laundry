@extends('layouts.backend')
@section('title','Dashboard Karyawan')
@section('content')
@if ($message = Session::get('success'))
<div class="alert alert-success alert-block">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <strong>{{ $message }}</strong>
</div>
@elseif ($message = Session::get('error'))
<div class="alert alert-danger alert-block">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <strong>{{ $message }}</strong>
</div>
@endif

<div class="card">
    <div class="card-body">
        <h4 class="card-title">
            <a href="{{ url('add-order') }}" class="btn btn-primary">Tambah</a>
        </h4>
        <h6>Info : <code> Klik pada bagian 'Action' untuk mengubah status order & pembayaran.</code></h6>
        <div class="table-responsive m-t-0">
            <table id="orderTable" class="table display table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>No Resi</th>
                        <th>Tanggal Trx</th>
                        <th>Customer</th>
                        <th>Status Laundry</th>
                        <th>Payment</th>
                        <th>Jenis</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td style="font-weight:bold; color:black">{{ $item->invoice }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tgl_transaksi)->format('d-m-Y') }}</td>
                        <td>{{ $item->customer }}</td>
                        <td>
                            <span class="badge bg-{{ $item->status_order == 'Done' ? 'success' : ($item->status_order == 'DiTerima' ? 'warning' : 'danger') }}">
                                {{ ucfirst($item->status_order) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $item->status_payment == 'Success' ? 'success' : 'danger' }}">
                                {{ $item->status_payment == 'Success' ? 'Lunas' : 'Pending' }}
                            </span>
                        </td>
                        <td>{{ $item->price->jenis }}</td>
                        <td>{{ Rupiah::getRupiah($item->harga_akhir) }}</td>
                        <td>
                            <div class="btn-group">
                                @if ($item->status_order == 'Process')
                                <a class="btn btn-sm btn-info updateStatus"
                                    style="color:white"
                                    data-id="{{ $item->id }}"
                                    data-status="Selesai">Selesai</a>
                                @elseif($item->status_order == 'Done')
                                @if ($item->status_payment == 'Pending')
                                <a class="btn btn-sm btn-danger updateStatusPayment"
                                    style="color:white"
                                    data-id="{{ $item->id }}">Bayar</a>
                                @elseif($item->status_payment == 'Success')
                                <a class="btn btn-sm btn-info updateStatus"
                                    style="color:white"
                                    data-id="{{ $item->id }}"
                                    data-status="Diambil">Diambil</a>
                                @endif
                                @endif
                                <a href="{{ url('invoice-kar', $item->id) }}" class="btn btn-sm btn-warning" style="color:white">Invoice</a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#orderTable').DataTable();
    });

    $(document).on('click', '.updateStatusPayment', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: "Konfirmasi",
            text: "Apakah kamu yakin ingin mengubah status pembayaran menjadi Lunas?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya, Bayar!",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('update.status.laundry') }}",
                    type: "GET",
                    data: {
                        id: id,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Swal.fire("Berhasil!", "Status pembayaran telah diperbarui menjadi Lunas.", "success").then(() => {
                            location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire("Error!", "Terjadi kesalahan saat memperbarui status pembayaran.", "error");
                    }
                });
            }
        });
    });

    $(document).on('click', '.updateStatus', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');

        Swal.fire({
            title: "Konfirmasi",
            text: `Apakah kamu yakin ingin mengubah status laundry ini menjadi "${status}"?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: `Ya, ${status}!`,
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('update.status.laundry') }}",
                    type: "GET",
                    data: {
                        id: id,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Swal.fire("Berhasil!", `Status laundry telah diperbarui menjadi "${status}".`, "success").then(() => {
                            location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire("Error!", "Terjadi kesalahan saat memperbarui status.", "error");
                    }
                });
            }
        });
    });
</script>
@endsection