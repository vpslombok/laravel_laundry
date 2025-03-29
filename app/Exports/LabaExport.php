<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use App\Models\{Transaksi, Pengeluaran};

class LabaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $tanggal_awal;
    protected $tanggal_akhir;

    public function __construct($tanggal_awal, $tanggal_akhir)
    {
        $this->tanggal_awal = $tanggal_awal;
        $this->tanggal_akhir = $tanggal_akhir;
    }

    public function collection()
    {
        $dates = [];
        $currentDate = Carbon::parse($this->tanggal_awal);
        $endDate = Carbon::parse($this->tanggal_akhir);

        while ($currentDate <= $endDate) {
            $date = $currentDate->format('Y-m-d');

            $pemasukan = Transaksi::where('status_payment', 'Success')
                ->whereDate('created_at', $date)
                ->sum('harga_akhir');

            $pengeluaran = Pengeluaran::whereDate('tanggal', $date)
                ->sum('jumlah');

            $laba = $pemasukan - $pengeluaran;

            // Sertakan semua tanggal meskipun nilainya 0
            $reportData[] = [
                'tanggal' => $date,
                'pemasukan' => $pemasukan,
                'pengeluaran' => $pengeluaran,
                'laba' => $laba
            ];

            $currentDate->addDay();
        }

        return collect($reportData);
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Pemasukan (Rp)',
            'Pengeluaran (Rp)',
            'Laba/Rugi (Rp)'
        ];
    }

    public function map($row): array
    {
        return [
            Carbon::parse($row['tanggal'])->format('d/m/Y'),
            $row['pemasukan'], // Nilai asli (number)
            $row['pengeluaran'], // Nilai asli (number)
            $row['laba'] // Nilai asli (number)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => '4F81BD']
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center'
            ]
        ]);

        // Format number untuk kolom B, C, D
        $sheet->getStyle('B2:D' . $sheet->getHighestRow())
            ->getNumberFormat()
            ->setFormatCode('#,##0;[Red]-#,##0');

        // Style untuk laba negatif
        $sheet->getStyle('D2:D' . $sheet->getHighestRow())
            ->getFont()
            ->getColor()
            ->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);

        // Border untuk seluruh data
        $sheet->getStyle('A1:D' . $sheet->getHighestRow())
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Alignment untuk kolom angka
        $sheet->getStyle('B2:D' . $sheet->getHighestRow())
            ->getAlignment()
            ->setHorizontal('right');
    }
}
