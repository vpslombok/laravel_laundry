<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\transaksi; // Sesuaikan dengan model pesanan
use App\Models\harga; // Sesuaikan dengan model harga
use App\Models\User; // Sesuaikan dengan model pengguna

class WebhookController extends Controller
{
    public static function text($text)
    {
        return json_encode(['text' => $text, 'status' => true]);
    }


    public function handleWebhook(Request $request)
    {
        $data = $request->all();
        $message = strtolower($data['message']);
        $from = strtolower($data['from']);
        if (substr($from, 0, 2) == '62') {
            $from = '0' . substr($from, 2);
        }
        // $bufferimage = isset($data['bufferImage']) ? $data['bufferImage'] : null;
        $respon = false;

        // untuk pesan teks
        if ($message == 'cek status') {
            $respon = self::text("Input Nomor Resi Untuk Cek Status Laundry Anda Contoh:123452025 ...", true);
        }

        if (strlen($message) == 9 && is_numeric($message)) {
            $nomorInvoice = $message;
            $transaksi = Transaksi::where('invoice', $nomorInvoice)->first();
            $jenisPakaian = harga::where('id', $transaksi->harga_id)->first();
            if ($transaksi) {
                $statusOrder = $transaksi->status_order;
                $nameCustomer = $transaksi->customers->name; // get name customer
                $respon = self::text("👋 Halo Kak *$nameCustomer* 🌟\n\n"
                    . "Laundry Kakak dengan *Nomor Resi {$transaksi->invoice}* Berikut detailnya:\n\n"
                    . "📅 *Tanggal*: " . date('d-m-Y', strtotime($transaksi->tgl_transaksi)) . "\n"
                    . "🕰️ *Jam*: " . date('H:i:s', strtotime($transaksi->tgl_transaksi)) . "\n"
                    . "👔 *Jenis Layanan*: " . $jenisPakaian->jenis . "\n"
                    . "🏋️‍♂️ *Berat*: " . $transaksi->kg . " Kg\n"
                    . "📆 *Estimasi Hari*: " . $transaksi->hari . " Hari\n"
                    . "🏁 *Estimasi Selesai*: " . date('d-m-Y', strtotime($transaksi->created_at . ' + ' . $transaksi->hari . ' days')) . "\n"
                    . "📝 *Status:* " . ($statusOrder == 'Process' ? 'Sedang Diproses' : ($statusOrder == 'Done' ? 'Siap Diambil' : $statusOrder)) . "\n\n"
                    . ($transaksi->status_order == 'DiTerima' ? "📦 *Diterima Pada*: {$transaksi->tgl_ambil}\n\n" : '')
                    . "🙏 Terima kasih sudah menggunakan layanan kami! 🌈", true);
            } else {
                $respon = self::text("Mohon Maaf Kak {name}, nomor Resi $nomorInvoice tidak ditemukan di sistem kami. 🙏", true);
            }
        }

        if ($message == 'list laundry') {
            $user = User::where('no_telp', $from)->first();

            if (!$user) {
                $respon = self::text("⚠️ *Mohon Maaf * ⚠️\nNomor *{$from}* Belum Terdaftar di sistem Kami...", true);
            } else {
                $listLaundry = Transaksi::where('customer_id', $user->id)->where('status_order', '!=', 'DiTerima')->get();

                if ($listLaundry->isEmpty()) {
                    $respon = self::text("*Mohon maaf, untuk saat ini tidak ada laundryan yang sedang kami diproses dengan atas nama* *{$user->name}*.", true);
                } else {
                    // Awal teks
                    $pesan = "*Daftar List Laundry Anda*\n";
                    $pesan .= "👤 *Nama:* {$user->name}\n";
                    $pesan .= "-------------------------------------------------------------\n";

                    // Looping daftar laundry
                    foreach ($listLaundry as $laundry) {
                        $pesan .= "📝 *No resi:* {$laundry->invoice}\n";
                        $pesan .= "📅 *Tanggal:* " . date('d-m-Y', strtotime($laundry->tgl_transaksi)) . "\n";
                        $pesan .= "🕰️ *Jam:* " . date('H:i:s', strtotime($laundry->tgl_transaksi)) . "\n";
                        $pesan .= "💰 *Status Pembayaran:* " . ($laundry->status_payment == 'Success' ? 'Lunas' : 'Belum Lunas') . "\n";

                        // Menentukan status order
                        $statusOrder = match ($laundry->status_order) {
                            'Process' => 'Sedang Diproses',
                            'Done' => 'Siap Diambil',
                            'DiTerima' => 'Telah Diambil',
                            default => 'Status Tidak Diketahui',
                        };

                        $pesan .= "🔄 *Status:* {$statusOrder}\n";
                        $pesan .= "-------------------------------------------------------------\n";
                    }

                    // Tambahkan instruksi
                    $pesan .= "ℹ️ *Untuk cek detail laundry, silakan kirim nomor Resi.*";

                    $respon = self::text($pesan, true);
                }
            }
        }

        echo $respon;
    }
}
