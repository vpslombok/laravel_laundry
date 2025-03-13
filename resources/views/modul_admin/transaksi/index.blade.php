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
                                <th><input type="checkbox" id="selectAll" name="selectAll" onclick="toggleSelectAll()"></th>
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
                                    <a href="{{url('invoice-customer', $item->invoice)}}" class="btn btn-sm btn-success" style="color:white">Invoice</a>
                                    <a href="{{url('hapus-transaksi', $item->id)}}" class="btn btn-sm btn-danger" style="color:white">Hapus</a>
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
        console.log("✅ Script berjalan!");

        // Cek apakah DataTable sudah terbaca
        if ($.fn.DataTable) {
            console.log("✅ DataTable ditemukan!");
            $('#myTable').DataTable();
        } else {
            console.log("❌ DataTable TIDAK ditemukan!");
        }

        $("#selectAll").on("click", function() {
            let checkedStatus = this.checked; // Ambil status checkbox utama
            console.log("🔄 Checkbox SelectAll diubah: ", checkedStatus);

            $(".checkbox").each(function() {
                $(this).prop("checked", checkedStatus);
            });

            console.log("📌 Total Checkbox Dipilih: ", $(".checkbox:checked").length);
        });

        // Debug Checkbox Individual
        $(".checkbox").on("change", function() {
            console.log("🔍 Checkbox diubah! ID: ", $(this).val());

            // Cek apakah semua checkbox sudah dicentang
            if ($(".checkbox:checked").length === $(".checkbox").length) {
                $("#selectAll").prop("checked", true);
            } else {
                $("#selectAll").prop("checked", false);
            }
            console.log("📌 Total Checkbox Dipilih: ", $(".checkbox:checked").length);
        });

        // Debug tombol hapus
        $("#hapus-terpilih").click(function() {
            var selected = [];
            $(".checkbox:checked").each(function() {
                selected.push($(this).val());
            });

            console.log("🗑️ Checkbox yang akan dihapus: ", selected);

            if (selected.length > 0) {
                if (confirm("Apakah Anda yakin ingin menghapus data terpilih?")) {
                    $.ajax({
                        type: "POST",
                        url: "/hapus-transaksi-terpilih",
                        data: {
                            ids: selected,
                            "_token": $('meta[name=csrf-token]').attr("content")
                        },
                        success: function(response) {
                            console.log("✅ Sukses menghapus!", response);
                            location.reload();
                        },
                        error: function(xhr) {
                            console.error("❌ Gagal menghapus!", xhr.responseText);
                        }
                    });
                }
            } else {
                alert("Tidak ada data yang dipilih.");
            }
        });

        // Cek apakah checkbox ada di halaman
        console.log("📌 Total Checkbox ditemukan: ", $(".checkbox").length);
    });
</script>
@endsection