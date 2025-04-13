@extends('layouts.backend')
@section('title', 'Admin - Laporan Laba')
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

<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-body">
                <!-- Filter Form -->
                <form action="" method="get" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Tanggal Awal</label>
                                <input type="date" name="tanggal_awal" class="form-control"
                                    value="{{ $tanggal_awal }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Tanggal Akhir</label>
                                <input type="date" name="tanggal_akhir" class="form-control"
                                    value="{{ $tanggal_akhir }}">
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ url()->current() }}" class="btn btn-secondary">
                                <i class="fas fa-sync-alt"></i> Reset
                            </a>
                        </div>
                        <div class="col-md-3 d-flex align-items-end justify-content-end">
                            <button type="button" class="btn btn-success" id="btn-export-excel">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Calculate totals from array -->
                @php
                $total_pemasukan = array_sum(array_column($laporan_laba, 'pemasukan'));
                $total_pengeluaran = array_sum(array_column($laporan_laba, 'pengeluaran'));
                $total_laba = array_sum(array_column($laporan_laba, 'laba'));
                @endphp

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Pemasukan</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ formatRupiah($total_pemasukan) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Total Pengeluaran</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ formatRupiah($total_pengeluaran) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-wallet fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Laba Bersih</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ formatRupiah($total_laba) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="laporanTable">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">No</th>
                                <th>Tanggal</th>
                                <th class="text-right">Pemasukan</th>
                                <th class="text-right">Total (KG)</th>
                                <th class="text-right">Pengeluaran</th>
                                <th class="text-right">Laba</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($laporan_laba as $index => $laba)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ date('d M Y', strtotime($laba['tanggal'])) }}</td>
                                <td class="text-right">{{ formatRupiah($laba['pemasukan']) }}</td>
                                <td class="text-right">{{ $laba['total_kg'] }} kg</td>
                                <td class="text-right">{{ formatRupiah($laba['pengeluaran']) }}</td>
                                <td class="text-right font-weight-bold 
                                    {{ $laba['laba'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ formatRupiah($laba['laba']) }}
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info btn-detail"
                                        data-tanggal="{{ $laba['tanggal'] }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data untuk periode ini</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detail Laporan Tanggal: <span id="modal-tanggal"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-money-bill-wave text-success"></i> Pemasukan</h5>
                        <div id="pemasukan-detail" class="mt-3"></div>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-wallet text-danger"></i> Pengeluaran</h5>
                        <div id="pengeluaran-detail" class="mt-3"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#laporanTable').DataTable({
            dom: '<"top"f>rt<"bottom"lip><"clear">',
            pageLength: 10,
            responsive: true,
            ordering: false,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
            }
        });

        // Detail button click handler
        $(document).on('click', '.btn-detail', function() {
            const tanggal = $(this).data('tanggal');

            $('#modal-tanggal').text(formatTanggal(tanggal));

            // Show loading state
            $('#pemasukan-detail').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');
            $('#pengeluaran-detail').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');

            // Fetch detail data
            $.ajax({
                url: '/admin/finance/laba-detail',
                type: 'GET',
                data: {
                    tanggal: tanggal
                },
                success: function(response) {
                    // Populate pemasukan (transactions)
                    if (response.transaksi && response.transaksi.length > 0) {
                        let html = '<table class="table table-sm">';
                        html += '<thead><tr><th class="text-right">Berat</th><th class="text-right">Total</th></tr></thead>';
                        html += '<tbody>';
                        response.transaksi.forEach(item => {
                            html += `<tr>
                                <td class="text-right">${item.kg} kg</td>
                                <td class="text-right">${formatRupiah(item.harga_akhir, true)}</td>
                            </tr>`;
                        });
                        html += `<tr class="bg-light">
                            <th colspan="3">Total</th>
                            <th class="text-right">${formatRupiah(response.total_pemasukan)}</th>
                        </tr>`;
                        html += '</tbody></table>';
                        $('#pemasukan-detail').html(html);
                    } else {
                        $('#pemasukan-detail').html('<div class="alert alert-info">Tidak ada data transaksi</div>');
                    }

                    // Populate pengeluaran
                    if (response.pengeluaran && response.pengeluaran.length > 0) {
                        let html = '<table class="table table-sm">';
                        html += '<thead><tr><th>Jenis</th><th>Keterangan</th><th class="text-right">Jumlah</th></tr></thead>';
                        html += '<tbody>';
                        response.pengeluaran.forEach(item => {
                            html += `<tr>
                                <td>${item.jenis}</td>
                                <td>${item.keterangan}</td>
                                <td class="text-right">${formatRupiah(item.jumlah)}</td>
                            </tr>`;
                        });
                        html += `<tr class="bg-light">
                            <th colspan="2">Total</th>
                            <th class="text-right">${formatRupiah(response.total_pengeluaran)}</th>
                        </tr>`;
                        html += '</tbody></table>';
                        $('#pengeluaran-detail').html(html);
                    } else {
                        $('#pengeluaran-detail').html('<div class="alert alert-info">Tidak ada data pengeluaran</div>');
                    }
                },
                error: function(xhr) {
                    $('#pemasukan-detail').html('<div class="alert alert-danger">Gagal memuat data</div>');
                    $('#pengeluaran-detail').html('<div class="alert alert-danger">Gagal memuat data</div>');
                    console.error(xhr.responseText);
                }
            });

            $('#detailModal').modal('show');
        });

        // Export Excel button
        $('#btn-export-excel').click(function() {
            const tanggalAwal = $('input[name="tanggal_awal"]').val();
            const tanggalAkhir = $('input[name="tanggal_akhir"]').val();

            window.location.href = `/admin/finance/laba-export?tanggal_awal=${tanggalAwal}&tanggal_akhir=${tanggalAkhir}`;
        });

        // Helper functions
        function formatTanggal(dateString) {
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            return new Date(dateString).toLocaleDateString('id-ID', options);
        }

        function formatRupiah(amount) {
            return 'Rp ' + parseFloat(amount).toLocaleString('id-ID');
        }
    });
</script>
@endsection