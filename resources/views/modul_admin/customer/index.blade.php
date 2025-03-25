@extends('layouts.backend')
@section('title','Admin - Data Customer')
@section('header','Data Customer')
@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title"> Data Customer
                    {{-- <a href="{{url('customer-add')}}" class="btn btn-primary">Tambah</a> --}}
                </h4>

                <div class="table-responsive m-t-0">
                    <table id="myTable" class="table display table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Alamat</th>
                                <th>No Telpon</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            @foreach ($customer as $item)
                            <tr>
                                <td>{{$no}}</td>
                                <td>{{$item->name}}</td>
                                <td>{{$item->alamat}}</td>
                                <td>{{$item->no_telp}}</td>
                                <td>{{$item->email}}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{route('customer.show', $item->id)}}">
                                                <i class="fas fa-info-circle mr-2"></i> Info
                                            </a>
                                            <a class="dropdown-item edit-btn" href="#"
                                                data-id="{{$item->id}}"
                                                data-name="{{$item->name}}"
                                                data-alamat="{{$item->alamat}}"
                                                data-no_telp="{{$item->no_telp}}"
                                                data-email="{{$item->email}}"
                                                data-toggle="modal"
                                                data-target="#editCustomerModal">
                                                <i class="fas fa-edit mr-2"></i> Edit
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php $no++; ?>
                            @endforeach
                        </tbody>
                    </table>
                    <!-- Modal Edit -->
                    <div class="modal fade" id="editCustomerModal" tabindex="-1" role="dialog" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
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
                                        <div class="form-group">
                                            <label for="name">Nama Customer</label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="alamat">Alamat</label>
                                            <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="no_telp">No. Telepon</label>
                                            <input type="text" class="form-control" id="no_telp" name="no_telp" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" required>
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
        $(document).ready(function() {
            var table = $('#example').DataTable({
                "columnDefs": [{
                    "visible": false,
                    "targets": 2
                }],
                "order": [
                    [2, 'asc']
                ],
                "displayLength": 25,
                "drawCallback": function(settings) {
                    var api = this.api();
                    var rows = api.rows({
                        page: 'current'
                    }).nodes();
                    var last = null;
                    api.column(2, {
                        page: 'current'
                    }).data().each(function(group, i) {
                        if (last !== group) {
                            $(rows).eq(i).before('<tr class="group"><td colspan="5">' + group + '</td></tr>');
                            last = group;
                        }
                    });
                }
            });
            // Order by the grouping
            $('#example tbody').on('click', 'tr.group', function() {
                var currentOrder = table.order()[0];
                if (currentOrder[0] === 2 && currentOrder[1] === 'asc') {
                    table.order([2, 'desc']).draw();
                } else {
                    table.order([2, 'asc']).draw();
                }
            });
        });
    });
    $('#example23').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });
</script>
<script>
    $(document).ready(function() {
        // Handle click on edit button
        $('.edit-btn').click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var alamat = $(this).data('alamat');
            var no_telp = $(this).data('no_telp');
            var email = $(this).data('email');



            // Set form action URL
            $('#editCustomerForm').attr('action', '/customer/' + id);

            // Populate form fields
            $('#name').val(name);
            $('#alamat').val(alamat);
            $('#no_telp').val(no_telp);
            $('#email').val(email);
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
                    // Refresh page or update table row
                    location.reload();
                },
                error: function(xhr) {
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                }
            });
        });
    });
</script>
@endsection