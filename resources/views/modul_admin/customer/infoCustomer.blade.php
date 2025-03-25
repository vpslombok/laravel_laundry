@extends('layouts.backend')
@section('title','Admin - Detail Data Customer')
@section('header','Detail Data Customer')
@section('content')
<div class="row">
    <div class="col-sm-12 col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Detail Data Customer</h4>
                <div class="float-right">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-cog"></i> Aksi
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <button class="dropdown-item edit-btn"
                                data-id="{{$customer->id}}"
                                data-name="{{$customer->name}}"
                                data-email="{{$customer->email}}"
                                data-no_telp="{{$customer->no_telp}}"
                                data-alamat="{{$customer->alamat}}"
                                data-kelamin="{{$customer->kelamin}}"
                                data-toggle="modal"
                                data-target="#editCustomerModal">
                                <i class="fas fa-edit mr-2"></i> Edit Data
                            </button>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-envelope mr-2"></i> Kirim Notifikasi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-content">
                <div class="row">
                    <div class="card-body">
                        <div class="card-text">
                            <dl class="row">
                                <dt class="col-sm-4">Nama Customer</dt>
                                <dd class="col-sm-4">: {{$customer->name}}</dd>
                            </dl>

                            <dl class="row">
                                <dt class="col-sm-4">Email Customer</dt>
                                <dd class="col-sm-4">: {{$customer->email}}</dd>
                            </dl>

                            <dl class="row">
                                <dt class="col-sm-4">No. Telepon</dt>
                                <dd class="col-sm-4">: {{$customer->no_telp == 0 ? 'Belum Input' : $customer->no_telp}}</dd>
                            </dl>

                            <dl class="row">
                                <dt class="col-sm-4">Alamat Customer</dt>
                                <dd class="col-sm-4">: {{$customer->alamat}}</dd>
                            </dl>


                        </div>
                    </div>
                    <div class="card-body">
                        <div class="card-text">
                            <dl class="row">
                                <dt class="col-sm-4">Total Kg</dt>
                                <dd class="col-sm-4">: {{$customer->transaksiCustomer()->sum('kg') ?? ''}} Kg</dd>
                            </dl>

                            <dl class="row">
                                <dt class="col-sm-4">Total Rupiah</dt>
                                <dd class="col-sm-4">: {{Rupiah::getRupiah($customer->transaksiCustomer()->sum('harga_akhir')) ?? ''}}</dd>
                            </dl>

                            <dl class="row">
                                <dt class="col-sm-4">Total Laundry</dt>
                                <dd class="col-sm-4">: {{$customer->transaksiCustomer()->count() ?? ''}} Kali</dd>
                            </dl>

                            <dl class="row">
                                <dt class="col-sm-4">Laundry Terakhir</dt>
                                <dd class="col-sm-4">: {{$customer->transaksiCustomer[0]['created_at'] ?? '-'}}</dd>
                            </dl>
                            <dl class="row">
                                <dt class="col-sm-4">Pendaftaran Akun</dt>
                                <dd class="col-sm-4">: {{$customer->created_at}}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1" role="dialog" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCustomerModalLabel">Edit Data Customer</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editCustomerForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_name">Nama Customer</label>
                                    <input type="text" class="form-control" id="edit_name" name="name" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_email">Email</label>
                                    <input type="email" class="form-control" id="edit_email" name="email" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_no_telp">No. Telepon</label>
                                    <input type="text" class="form-control" id="edit_no_telp" name="no_telp" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_alamat">Alamat</label>
                                    <textarea class="form-control" id="edit_alamat" name="alamat" rows="3" required></textarea>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-sm-12 col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Detail Transaksi Customer</h4>
            </div>
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive m-t-0">
                        <table id="myTable" class="table display table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Invoice</th>
                                    <th>Tgl Transaksi</th>
                                    <th>Tgl Diambil</th>
                                    <th>Jumlah KG</th>
                                    <th>Jenis Pembayaran</th>
                                    <th>Status Transaksi</th>
                                    <th>Total Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($customer->transaksiCustomer as $key => $item)
                                <tr>
                                    <td>{{$key+1}}</td>
                                    <td>{{$item->invoice}}</td>
                                    <td>{{$item->tgl_transaksi}}</td>
                                    <td>{{$item->tgl_ambil ?? 'Belum Diambil'}}</td>
                                    <td>{{$item->kg}} kg</td>
                                    <td>{{$item->jenis_pembayaran}}</td>
                                    <td>{{$item->status_order}}</td>
                                    <td>{{Rupiah::getRupiah($item->harga_akhir)}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $('#myTable').DataTable();

        // Handle edit button click
        $('.edit-btn').click(function() {
            var id = $(this).data('id');
            var url = "{{ route('customer.update', ':id') }}".replace(':id', id);

            $('#editCustomerForm').attr('action', url);
            $('#edit_name').val($(this).data('name'));
            $('#edit_email').val($(this).data('email'));
            $('#edit_no_telp').val($(this).data('no_telp'));
            $('#edit_alamat').val($(this).data('alamat'));
        });

        // Handle form submission
        $('#editCustomerForm').submit(function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');

            $.ajax({
                type: "POST",
                url: url,
                data: form.serialize(),
                success: function(response) {
                    $('#editCustomerModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data customer berhasil diperbarui',
                        timer: 1500
                    }).then(function() {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    var errorMsg = '';

                    $.each(errors, function(key, value) {
                        errorMsg += value + '<br>';
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: errorMsg
                    });
                }
            });
        });
    });
</script>
@endsection