<?php

namespace App\Filament\Admin\Resources\HasilLaporanResource\Pages;

use App\Filament\Admin\Resources\HasilLaporanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHasilLaporans extends ListRecords
{
    protected static string $resource = HasilLaporanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('generate_hasil')
                ->label('Generate Hasil Laporan')
                ->icon('heroicon-o-cpu-chip')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Select::make('user_id')
                        ->label('Disusun Oleh')
                        ->options(\App\Models\User::pluck('name', 'id'))
                        ->required(),
                    \Filament\Forms\Components\Select::make('tahun')
                        ->options(array_combine(range(date('Y'), date('Y')-4), range(date('Y'), date('Y')-4)))
                        ->required(),
                    \Filament\Forms\Components\Select::make('bulan')
                        ->options([
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    set_time_limit(0);
                    $userId = $data['user_id'];
                    $tahun = $data['tahun'];
                    $bulan = $data['bulan'];

                    $reports = \App\Models\Laporan::where('user_id', $userId)
                        ->where('tahun', $tahun)
                        ->where('bulan', $bulan)
                        ->get()
                        ->groupBy(function ($item) {
                            return sprintf('%04d-%02d-%02d', $item->tahun, $item->bulan, $item->tanggal);
                        });

                    $accountId = env('CLOUDFLARE_ACCOUNT_ID');
                    $apiToken = env('CLOUDFLARE_API_TOKEN');

                    foreach ($reports as $tanggalStr => $group) {
                        // Hapus data lama untuk hari ini agar tidak duplikat saat di-generate ulang
                        \App\Models\HasilLaporan::where('user_id', $userId)
                            ->where('tahun', $group->first()->tahun)
                            ->where('bulan', $group->first()->bulan)
                            ->where('tanggal', $group->first()->tanggal)
                            ->delete();

                        // Pecah menjadi tiap 3 commit per baris laporan (berdasarkan permintaan)
                        $chunks = $group->chunk(3);

                        foreach ($chunks as $chunkIndex => $chunk) {
                            $aktifitasArray = $chunk->pluck('aktifitas')->toArray();
                            $combinedAktifitas = implode("\n", $aktifitasArray);
                            $ringkasan = $combinedAktifitas;

                            $cacheKey = "laporan_ai_summary_{$userId}_{$tanggalStr}_part{$chunkIndex}_" . md5($combinedAktifitas) . "_v8";
                            $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);

                            if ($cached) {
                                $ringkasan = $cached;
                            } else if ($accountId && $apiToken) {
                                try {
                                    $prompt = "Tulis ulang pesan commit teknis berikut menjadi laporan aktivitas kerja untuk dibaca oleh Manajemen/HRD (Non-IT).\n" .
                                              "ATURAN WAJIB:\n" .
                                              "1. UBAH JARGON TEKNIS: Jangan gunakan istilah coding (seperti tabel, database, API, view, controller, refactor, variabel). Ubah menjadi bahasa fitur bisnis (contoh: 'tabel user' menjadi 'data pengguna').\n" .
                                              "2. FOKUS PADA HASIL/FUNGSI: Jelaskan *apa* fungsi yang dibuat/diperbaiki dan *kegunaannya* bagi sistem atau pengguna, bukan *bagaimana* kode itu ditulis.\n" .
                                              "3. BAHASA PROFESIONAL: Gunakan bahasa Indonesia baku (EYD), kalimat pasif/objektif (contoh: 'Pembuatan fitur...', 'Penyempurnaan sistem...', 'Perbaikan kendala pada...'). Dilarang pakai kata ganti orang (saya/kami/tim).\n" .
                                              "4. TANPA BASA-BASI: Dilarang keras memakai kalimat pembuka/penutup seperti 'Berikut adalah ringkasan...'. Langsung tuliskan poin-poin hasilnya.\n" .
                                              "5. FORMAT LIST: Hasilkan poin-poin menggunakan tanda strip (-) di awal kalimat. Gabungkan aktivitas yang serumpun agar tidak terlalu banyak poin.\n\n" .
                                              "Pesan Commit Asli:\n" .
                                              $combinedAktifitas;

                                    $response = \Illuminate\Support\Facades\Http::withToken($apiToken)
                                        ->timeout(20)
                                        ->post("https://api.cloudflare.com/client/v4/accounts/{$accountId}/ai/run/@cf/meta/llama-3.1-8b-instruct-fp8", [
                                            'messages' => [
                                                ['role' => 'system', 'content' => 'Anda adalah penulis log teknis yang sangat *to the point*. Tulis ringkasan dalam bentuk list/poin tanpa basa-basi pengantar.'],
                                                ['role' => 'user', 'content' => $prompt]
                                            ]
                                        ]);

                                    if ($response->successful()) {
                                        $aiText = trim($response->json('result.response'));
                                        if (!empty($aiText)) {
                                            $ringkasan = trim($aiText, "\"' \t\n\r\0\x0B");
                                            // Hapus teks pengantar yang sering muncul (termasuk jika terpotong enter)
                                            $ringkasan = preg_replace('/^(Berikut|Di\s?bawah ini).*?:/is', '', $ringkasan);
                                            // Hapus sisa newlines di awal
                                            $ringkasan = trim($ringkasan);                                            
                                            \Illuminate\Support\Facades\Cache::put($cacheKey, $ringkasan, 60*60*24*30);
                                        }
                                    }
                                } catch (\Exception $e) {
                                    // Fallback
                                }
                            }

                            $diffTextArray = $chunk->map(function ($item) {
                                $text = "Commit:\n" . $item->aktifitas;
                                if (!empty($item->diff_text)) {
                                    $text .= "\n" . $item->diff_text;
                                }
                                return $text;
                            })->toArray();
                            $combinedDiffText = implode("\n\n", $diffTextArray);

                            \App\Models\HasilLaporan::create([
                                'user_id' => $userId,
                                'hari' => $chunk->first()->hari,
                                'tanggal' => $chunk->first()->tanggal,
                                'bulan' => $chunk->first()->bulan,
                                'tahun' => $chunk->first()->tahun,
                                'aktifitas' => $ringkasan,
                                'lampiran' => $chunk->pluck('gambar')->filter()->values()->toArray(),
                                'diff_text' => $combinedDiffText,
                            ]);
                        }
                    }
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Hasil Laporan Berhasil Digenerate')
                        ->success()
                        ->send();
                }),
        ];
    }
}
