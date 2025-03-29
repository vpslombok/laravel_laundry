@extends('layouts.backend')
@section('title', 'Admin - Data Karyawan')
@section('header', 'Data Karyawan')
@section('content')
@if ($message = Session::get('success'))
<div class="alert alert-success alert-block">
  <button type="button" class="close" data-dismiss="alert">×</button>
  <strong>{{ $message }}</strong>
</div>
@elseif($message = Session::get('error'))
<div class="alert alert-danger alert-block">
  <button type="button" class="close" data-dismiss="alert">×</button>
  <strong>{{ $message }}</strong>
</div>
@endif

<div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title"> Data Karyawan / Cabang
          <a href="{{route('karyawan.create')}}" class="btn btn-primary">Tambah</a>
        </h4>

        <div class="table-responsive">
          <table class="table zero-configuration">
            <thead>
              <tr>
                <th>#</th>
                <th>Nama Karyawan</th>
                <th>Email</th>
                <th>Alamat Cabang</th>
                <th>Nama Cabang</th>
                <th>No Telp</th>
                <th>Status</th>
                <th>Menu</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($kry as $item)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->email }}</td>
                <td>{{ $item->alamat_cabang }}</td>
                <td>{{ $item->nama_cabang }}</td>
                <td>{{ $item->no_telp }}</td>
                <td>
                  <span class="label label-{{ $item->status == 'Active' ? 'success' : 'danger' }}">
                    {{ $item->status == 'Active' ? 'Aktif' : 'Tidak Aktif' }}
                  </span>
                </td>
                <td>
                  <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      Menu
                    </button>
                    <div class="dropdown-menu">
                      <button class="dropdown-item edit-btn"
                        data-id="{{ $item->id }}"
                        data-name="{{ $item->name }}"
                        data-email="{{ $item->email }}"
                        data-alamat_cabang="{{ $item->alamat_cabang }}"
                        data-nama_cabang="{{ $item->nama_cabang }}"
                        data-no_telp="{{ $item->no_telp }}"
                        data-status="{{ $item->status }}"
                        data-toggle="modal"
                        data-target="#editKaryawanModal">
                        <i class="fas fa-edit"></i> Edit
                      </button>
                      <form action="{{ route('karyawan.destroy', $item->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                          <i class="fas fa-trash"></i> Hapus
                        </button>
                      </form>
                      <a class="dropdown-item status-btn" href="#" data-id-update="{{ $item->id }}">
                        <i class="fas fa-power-off"></i> {{ $item->status == 'Active' ? 'Non-Aktifkan' : 'Aktifkan' }}
                      </a>
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

<!-- Edit Karyawan Modal -->
<div class="modal fade" id="editKaryawanModal" tabindex="-1" role="dialog" aria-labelledby="editKaryawanModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editKaryawanModalLabel">Edit Data Karyawan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="editKaryawanForm" action="{{ route('karyawan.update-data', ['id' => '__ID__']) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_name">Nama Karyawan</label>
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
                <label for="edit_nama_cabang">Nama Cabang</label>
                <input type="text" class="form-control" id="edit_nama_cabang" name="nama_cabang" required>
              </div>
              <div class="form-group">
                <label for="edit_alamat_cabang">Alamat Cabang</label>
                <textarea class="form-control" id="edit_alamat_cabang" name="alamat_cabang" rows="3" required></textarea>
              </div>
              <div class="form-group">
                <label for="edit_status">Status</label>
                <select class="form-control" id="edit_status" name="status" required>
                  <option value="Active">Aktif</option>
                  <option value="Inactive">Tidak Aktif</option>
                </select>
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
@endsection

@section('scripts')
<script type="text/javascript">
  // Update Status Karyawan
  $(document).on('click', '.status-btn', function(e) {
    e.preventDefault();
    var id = $(this).attr('data-id-update');

    Swal.fire({
      title: 'Konfirmasi',
      text: "Apakah Anda yakin ingin mengubah status karyawan ini?",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ya, Ubah!'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: '{{ route("karyawan.update-status") }}',
          type: 'GET',
          data: {
            '_token': '{{ csrf_token() }}',
            'id': id
          },
          success: function(response) {
            Swal.fire(
              'Berhasil!',
              'Status karyawan telah diubah.',
              'success'
            ).then(() => {
              location.reload();
            });
          },
          error: function() {
            Swal.fire(
              'Gagal!',
              'Terjadi kesalahan saat mengubah status.',
              'error'
            );
          }
        });
      }
    });
  });

  // Edit Karyawan Modal
  $(document).ready(function() {
    // Handle click on edit button
    $('.edit-btn').click(function() {
      var id = $(this).data('id');

      // Set form action URL with the correct ID
      var form = $('#editKaryawanForm');
      var actionUrl = form.attr('action').replace('__ID__', id);
      form.attr('action', actionUrl);

      // Populate form fields
      $('#edit_name').val($(this).data('name'));
      $('#edit_email').val($(this).data('email'));
      $('#edit_no_telp').val($(this).data('no_telp'));
      $('#edit_nama_cabang').val($(this).data('nama_cabang'));
      $('#edit_alamat_cabang').val($(this).data('alamat_cabang'));
      $('#edit_status').val($(this).data('status'));
    });

    // Handle form submission
    $('#editKaryawanForm').submit(function(e) {
      e.preventDefault();
      var form = $(this);
      var url = form.attr('action');

      $.ajax({
        type: "POST",
        url: url,
        data: form.serialize(),
        success: function(response) {
          $('#editKaryawanModal').modal('hide');
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: 'Data karyawan berhasil diperbarui',
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