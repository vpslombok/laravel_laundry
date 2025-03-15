@extends('layouts.backend')
@section('title','Admin - Data Transaksi')
@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title"> Data Transaksi
                    <div class="row">
                        <div class="col-4">
                            <select name="user_id" id="user_id" class="form-control">
                                <option value="all">--Semua Transaksi--</option>
                                @foreach ($filter as $item)
                                <option value="{{$item->id}}">Karyawan {{$item->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="cl-3">
                            <button class="btn btn-primary" id="filter">Filter</button>
                            <button class="btn btn-danger" id="hapus-terpilih">Hapus Terpilih</button>
                        </div>
                    </div>
                </h4>
                <div class="table-responsive m-t-0">
                    <table id="myTable" class="table display table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="no-sort"><input type="checkbox" id="selectAll"></th>
                                <th>#</th>
                                <th>No Resi</th>
                                <th>TGL Transaksi</th>
                                <th>Customer</th>
                                <th>Status Order</th>
                                <th>Status Pembayaran</th>
                                <th>Jenis Laundri</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="refresh_body">
                            @foreach ($transaksi as $key => $item)
                            <tr>
                                <td><input type="checkbox" name="selected[]" class="checkbox" value="{{$item->id}}"></td>
                                <td>{{$key+1}}</td>
                                <td>{{$item->invoice}}</td>
                                <td>{{carbon\carbon::parse($item->tgl_transaksi)->format('d-m-y')}}</td>
                                <td>{{$item->customer}}</td>
                                <td>
                                    @if ($item->status_order == 'Done')
                                    <span class="label label-success">Selesai</span>
                                    @elseif($item->status_order == 'DiTerima')
                                    <span class="label label-info">Sudah Diambil</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->status_payment == 'Success')
                                    <span class="label label-success">Sudah Dibayar</span>
                                    @elseif($item->status_payment == 'Pending')
                                    <span class="label label-info">Belum Dibayar</span>
                                    @endif
                                </td>
                                <td>{{$item->price->jenis}}</td>
                                <td>
                                    <p>{{Rupiah::getRupiah($item->harga_akhir)}}</p>
                                </td>
                                <td align="center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item" href="{{url('invoice-customer', $item->invoice)}}">Cetak</a>
                                            <a class="dropdown-item" href="{{url('hapus-transaksi', $item->id)}}">Hapus</a>
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
    </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        // Inisialisasi DataTable dengan sorting default di kolom kedua (bukan kolom checkbox)
        var table = $('#myTable').DataTable({
            "order": [
                [1, 'asc']
            ], // Sorting default di kolom kedua (bukan checkbox)
            "columnDefs": [{
                    "orderable": false,
                    "targets": 0
                } // Nonaktifkan sorting untuk kolom pertama (checkbox)
            ]
        });

        // Reset checkbox setelah tabel di-refresh
        table.on('draw.dt', function() {
            $("#selectAll").prop("checked", false);
        });

        // Event handler untuk Select All
        $(document).on("change", "#selectAll", function() {
            let checkedStatus = this.checked;
            $(".checkbox").prop("checked", checkedStatus);
        });

        // Event handler untuk checkbox individu
        $(document).on("change", ".checkbox", function() {
            if ($(".checkbox:checked").length === $(".checkbox").length) {
                $("#selectAll").prop("checked", true);
            } else {
                $("#selectAll").prop("checked", false);
            }
        });

        // Tombol hapus terpilih
        $("#hapus-terpilih").click(function() {
            var selected = [];
            $(".checkbox:checked").each(function() {
                selected.push($(this).val());
            });

            if (selected.length > 0) {
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Ingin menghapus data terpilih?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        $.ajax({
                            type: "POST",
                            url: "/hapus-transaksi-terpilih",
                            data: {
                                ids: selected
                            },
                            success: function(response) {
                                location.reload();
                            },
                            error: function(xhr) {
                                console.error(xhr.responseText);
                            }
                        });
                    }
                });
            } else {
                Swal.fire(
                    'Tidak ada data yang dipilih.',
                    '',
                    'warning'
                );
            }
        });
    });
</script>
@endsection