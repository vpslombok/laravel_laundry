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
        <h6>Info : <code> Klik pada bagian 'Action' untuk edit status yang salah</code></h6>
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
                            <span class="badge bg-{{ $item->status_order == 'Done' ? 'success' : ($item->status_order == 'DiTerima' ? 'success' : 'danger') }}">
                                {{ ucfirst($item->status_order) }}
                            </span>
                        </td>
                        <td>
                            <span class="label label-{{ $item->status_payment == 'Success' ? 'success' : 'success' }}">
                                {{ $item->status_payment == 'Success' ? 'Lunas' : 'Pending' }}
                            </span>
                        </td>
                        <td>{{ $item->price->jenis }}</td>
                        <td>{{ Rupiah::getRupiah($item->harga_akhir) }}</td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-info dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Action
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" href="{{ url('invoice-kar', $item->id) }}">Cetak</a>
                                    <a class="dropdown-item" data-toggle="modal" data-target="#editModal{{ $item->id }}">Edit</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach ($order as $item)
<div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit Status Laundry</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ url('update-histori-laundry') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ $item->id }}">
                    <div class="form-group">
                        <label for="status_order">Status Laundry</label>
                        <select name="status_order" class="form-control">
                            <option value="Process" {{ $item->status_order == 'Process' ? 'selected' : '' }}>Dalam Proses</option>
                            <option value="Done" {{ $item->status_order == 'Done' ? 'selected' : '' }}>Selesai</option>
                            <option value="DiTerima" {{ $item->status_order == 'DiTerima' ? 'selected' : '' }}>Diterima</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#orderTable').DataTable();
    });
</script>

@endsection