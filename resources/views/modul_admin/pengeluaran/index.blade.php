@extends('layouts.backend')
@section('title', 'Admin - Data Pengeluaran')
@section('content')
@if ($message = Session::get('success'))
<div class="alert alert-success alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <strong>{{ $message }}</strong>
</div>
@elseif($message = Session::get('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <strong>{{ $message }}</strong>
</div>
@endif

@if ($getBank > 0)
<div class="row">
    <!-- Data Pengeluaran -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Data Pengeluaran</h4>
                    @if($karyawanExists)
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addPengeluaranModal">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                    @endif
                </div>

                <div class="table-responsive  m-t-0">
                    <table id="pengeluaranTable" class="table display table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Keterangan</th>
                                <th>Jumlah</th>
                                <th>Cabang</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pengeluaran as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                                <td>{{ $item->jenis }}</td>
                                <td>{{ $item->keterangan }}</td>
                                <td>{{ Rupiah::getRupiah($item->jumlah) }}</td>
                                <td>{{ $item->cabang->nama_cabang ?? 'Semua Cabang' }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item edit-btn" data-id="{{ $item->id }}" data-tanggal="{{ $item->tanggal }}" data-jenis="{{ $item->jenis }}" data-keterangan="{{ $item->keterangan }}" data-jumlah="{{ $item->jumlah }}" data-cabang="{{ $item->cabang->id }}">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a class="dropdown-item delete-btn" data-id="{{ $item->id }}">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <th colspan="4" class="text-right">Total Pengeluaran:</th>
                                <th colspan="3">{{ Rupiah::getRupiah($totalPengeluaran) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Tambah Data dan Statistik -->
    <div class="col-lg-4">
        @if($karyawanExists)
        <div class="card card-outline-info">
            <div class="card-header">
                <h4 class="m-b-0 text-black">Form Tambah Pengeluaran</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('pengeluaran.store') }}" method="POST">
                    @csrf
                    <div class="form-body">
                        <div class="row p-t-20">
                            <div class="col-lg-12 col-xl-12">
                                <div class="form-group has-success">
                                    <label class="control-label">Cabang</label>
                                    <select name="cabang_id" class="form-control @error('cabang_id') is-invalid @enderror">
                                        <option value="">-- Pilih Cabang --</option>
                                        @foreach ($cabangList as $cabang)
                                        <option value="{{ $cabang->id }}">{{ $cabang->nama_cabang }}</option>
                                        @endforeach
                                    </select>
                                    @error('cabang_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-12 col-xl-12">
                                <div class="form-group has-success">
                                    <label class="control-label">Jenis Pengeluaran</label>
                                    <select name="jenis" class="form-control @error('jenis') is-invalid @enderror">
                                        <option value="">-- Pilih Jenis --</option>
                                        <option value="Operasional">Operasional</option>
                                        <option value="Gaji">Gaji</option>
                                        <option value="Peralatan">Peralatan</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    @error('jenis')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-12 col-xl-12">
                                <div class="form-group has-success">
                                    <label class="control-label">Tanggal</label>
                                    <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror"
                                        value="{{ old('tanggal', date('Y-m-d')) }}" required>
                                    @error('tanggal')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-12 col-xl-12">
                                <div class="form-group has-success">
                                    <label class="control-label">Jumlah (Rp)</label>
                                    <input type="text" name="jumlah" class="form-control @error('jumlah') is-invalid @enderror format_harga"
                                        value="{{ old('jumlah') }}" required>
                                    @error('jumlah')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-12 col-xl-12">
                                <div class="form-group has-success">
                                    <label class="control-label">Keterangan</label>
                                    <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror"
                                        rows="3" required>{{ old('keterangan') }}</textarea>
                                    @error('keterangan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Simpan</button>
                        <button type="reset" class="btn btn-danger">Reset</button>
                    </div>
                </form>
            </div>
        </div>
        @else
        <div class="card card-outline-danger">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0">Informasi</h4>
            </div>
            <div class="card-body text-center py-4">
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <h5 class="text-danger">Data karyawan/cabang belum tersedia!</h5>
                <p>Silahkan tambahkan data karyawan terlebih dahulu untuk mengelola pengeluaran</p>
                <a href="{{ route('karyawan.create') }}" class="btn btn-primary mt-2">
                    <i class="fas fa-plus"></i> Tambah Karyawan
                </a>
            </div>
        </div>
        @endif

        <!-- Statistik Pengeluaran -->
        <div class="card mt-3">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0 text-white">Statistik Pengeluaran</h4>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Hari Ini</span>
                        <span class="badge badge-danger">{{ Rupiah::getRupiah($statistik['hari_ini']) }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Minggu Ini</span>
                        <span class="badge badge-warning">{{ Rupiah::getRupiah($statistik['minggu_ini']) }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Bulan Ini</span>
                        <span class="badge badge-primary">{{ Rupiah::getRupiah($statistik['bulan_ini']) }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong>Total</strong></span>
                        <span class="badge badge-dark">{{ Rupiah::getRupiah($statistik['total']) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<!-- Empty State -->
<div class="card">
    <div class="col text-center py-5">
        <img src="{{ asset('backend/images/pages/empty.svg') }}" style="height: 200px;" class="mb-4">
        <h2 class="mt-1">Data Bank Kosong / Tidak Aktif!</h2>
        <h4>Mohon untuk melakukan penginputan Data Bank terlebih dahulu</h4>
        <a href="{{ route('settings.index') }}" class="btn btn-primary mt-3">
            <i class="fas fa-cog"></i> Pengaturan Bank
        </a>
    </div>
</div>
@endif

<!-- Modal Edit -->
<div class="modal fade" id="editPengeluaranModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Pengeluaran</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editPengeluaranForm" method="POST">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div class="modal-body">
                    <input type="hidden" id="edit_id" name="id">

                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" id="edit_tanggal" name="tanggal" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Jenis Pengeluaran</label>
                        <select id="edit_jenis" name="jenis" class="form-control" required>
                            <option value="Operasional">Operasional</option>
                            <option value="Gaji">Gaji</option>
                            <option value="Peralatan">Peralatan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Jumlah (Rp)</label>
                        <input type="text" id="edit_jumlah" name="jumlah" class="form-control format_harga" required>
                    </div>

                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea id="edit_keterangan" name="keterangan" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Cabang</label>
                        <select id="edit_cabang" name="cabang_id" class="form-control">
                            @foreach ($cabangList as $cabang)
                            <option value="{{ $cabang->id }}">{{ $cabang->nama_cabang }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#pengeluaranTable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
            },
            order: [
                [1, 'desc']
            ]
        });

        // Format jumlah to Rupiah Value
        $(".jumlah").autoNumeric('init', {
            aSep: '.',
            aDec: ',',
            aForm: true,
            vMax: '999999999',
            vMin: '-999999999'
        });

        // Edit button handler
        $('.edit-btn').click(function() {
            const id = $(this).data('id');
            const tanggal = $(this).data('tanggal');
            const jenis = $(this).data('jenis');
            const jumlah = $(this).data('jumlah');
            const keterangan = $(this).data('keterangan');
            const cabang = $(this).data('cabang');

            $('#edit_id').val(id);
            $('#edit_tanggal').val(tanggal);
            $('#edit_jenis').val(jenis);
            $('#edit_jumlah').val(formatRupiah(jumlah.toString(), ''));
            $('#edit_keterangan').val(keterangan);
            $('#edit_cabang').val(cabang);

            $('#editPengeluaranForm').attr('action', '/pengeluaran/' + id);
            $('#editPengeluaranModal').modal('show');
            console.log(id, tanggal, jenis, jumlah, keterangan, cabang);
        });

        // Handle form submission
        $('#editPengeluaranForm').on('submit', function(e) {
            e.preventDefault();

            // Format jumlah sebelum submit
            const jumlahInput = $('#edit_jumlah');
            const jumlahValue = jumlahInput.val().replace('Rp. ', '').replace(/\./g, '');
            jumlahInput.val(jumlahValue);

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST', // Tetap POST karena menggunakan method spoofing
                data: $(this).serialize(),
                success: function(response) {
                    $('#editPengeluaranModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data pengeluaran berhasil diperbarui',
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: xhr.responseJSON.message || 'Terjadi kesalahan',
                    });
                }
            });
        });

        // Format Rupiah
        function formatRupiah(angka, prefix) {
            var number_string = angka.replace(/[^,\d]/g, '').toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
        }


        // Delete button handler
        $('.delete-btn').click(function() {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data pengeluaran akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/pengeluaran/' + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire(
                                'Terhapus!',
                                'Data berhasil dihapus.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        }
                    });
                }
            });
        });

        function formatRibu(angka) {
            return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    });
</script>
@endsection

@section('styles')
<style>
    .table th {
        white-space: nowrap;
    }

    .badge {
        font-size: 90%;
        padding: 0.35em 0.65em;
    }

    .list-group-item {
        padding: 0.75rem 1.25rem;
    }
</style>
@endsection