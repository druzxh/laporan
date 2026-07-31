<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

use Illuminate\Http\Request; // Pastikan Request diimpor
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf; // Untuk PDF
use PhpOffice\PhpWord\PhpWord; // Untuk DOC
use Filament\Notifications\Notification;

class ReportGenerator extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.pages.report-generator';

    public function generatePDF(Request $request)
    {
        $request->validate([
            'tahun' => 'required|numeric',
            'bulan' => 'required|string|size:2',
        ]);

        $tahun = $request->tahun;
        $bulan = $request->bulan;
        $userId = $request->user_id;
        $verifikatorTtdId = $request->verifikator_id;
        $persetujuanTtdId = $request->persetujuan_id;

        $namaBulan = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        $bulanNama = $namaBulan[$bulan] ?? 'Unknown';

        // $user = auth()->user(); 

        $user = \App\Models\User::where('id', $userId)->first();
        $userTtd = $user->tandaTangans;
        $verifikatorTtd = \App\Models\TandaTangan::where('id', $verifikatorTtdId)->first();
        $persetujuanTtd = \App\Models\TandaTangan::where('id', $persetujuanTtdId)->first();
        
        if (!$user) {
            Notification::make()
                ->title('User not found')
                ->body('The user with the provided ID does not exist.')
                ->danger() // untuk menandakan error
                ->send();
            return;
        }
        
        if (!$verifikatorTtd) {
            Notification::make()
                ->title('Verifikator not found')
                ->body('The verifikator with the provided ID does not exist.')
                ->danger()
                ->send();
            return;
        }
        
        if (!$persetujuanTtd) {
            Notification::make()
                ->title('Persetujuan not found')
                ->body('The persetujuan with the provided ID does not exist.')
                ->danger()
                ->send();
            return;
        }

        $reports = \App\Models\HasilLaporan::where('user_id', $userId)
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->get()
            ->map(function ($hasil) {
                return [
                    'hari' => $hasil->hari,
                    'tanggal' => $hasil->tanggal,
                    'bulan' => $hasil->bulan,
                    'tahun' => $hasil->tahun,
                    'aktifitas' => [$hasil->aktifitas], // Tetap array agar kompatibel dengan pdf.blade.php
                    'gambar' => $hasil->lampiran ?? [],
                    'diff_text' => $hasil->diff_text,
                ];
            })
            ->sortBy(function ($report) {
                return "{$report['tahun']}-{$report['bulan']}-{$report['tanggal']}";
            });

        $ttd = \App\Models\TandaTangan::all();
        $pdf = Pdf::loadView('reports.pdf', compact(['user', 'reports', 'bulanNama', 'tahun', 'userTtd', 'verifikatorTtd', 'persetujuanTtd']))->setPaper('a4', 'landscape');

        if ($request->has('preview')) {
            return $pdf->stream("Laporan {$bulanNama} {$tahun}.pdf");
        }

        $filename = "Laporan {$bulanNama} {$tahun}.pdf";
        return $pdf->download($filename);
    }

    public function generateDOC(Request $request)
    {
        $request->validate([
            'tahun' => 'required|numeric',
            'bulan' => 'required|string|size:2',
            'user_id' => 'required|exists:users,id',
        ]);

        $tahun = $request->tahun;
        $bulan = $request->bulan;
        $userId = $request->user_id;

        $namaBulan = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        $bulanNama = $namaBulan[$bulan] ?? 'Unknown';

        $user = \App\Models\User::where('id', $userId)->first();

        $reports = \App\Models\HasilLaporan::where('user_id', $userId)
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->get()
            ->map(function ($hasil) {
                return [
                    'hari' => $hasil->hari,
                    'tanggal' => $hasil->tanggal,
                    'bulan' => $hasil->bulan,
                    'tahun' => $hasil->tahun,
                    'aktifitas' => [$hasil->aktifitas], // Tetap array agar kompatibel dengan pdf.blade.php
                    'gambar' => $hasil->lampiran ?? [],
                    'diff_text' => $hasil->diff_text,
                ];
            })
            ->sortBy(function ($report) {
                return "{$report['tahun']}-{$report['bulan']}-{$report['tanggal']}";
            });

        // Generate DOC
        $phpWord = new PhpWord();
        // Set default font untuk mencegah error di Google Docs
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection();

        // Judul
        $section->addText("Laporan Aktivitas {$bulanNama} {$tahun}", ['bold' => true, 'size' => 16], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addText("Disusun oleh: {$user->name}", ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addTextBreak(1);

        // Tabel
        $styleTable = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80];
        $styleFirstRow = ['borderBottomSize' => 18, 'borderBottomColor' => '000000', 'bgColor' => 'F2F2F2'];
        $phpWord->addTableStyle('LaporanTable', $styleTable, $styleFirstRow);
        
        $table = $section->addTable('LaporanTable');
        $table->addRow();
        $table->addCell(500)->addText('No.', ['bold' => true]);
        $table->addCell(2000)->addText('Hari/Tanggal', ['bold' => true]);
        $table->addCell(4000)->addText('Deskripsi', ['bold' => true]);
        $table->addCell(4000)->addText('Lampiran Diff', ['bold' => true]);

        $no = 1;
        $lastDate = '';
        foreach ($reports as $report) {
            $currentDate = $report['tanggal'] . '/' . $report['bulan'] . '/' . $report['tahun'];
            $table->addRow();
            $table->addCell(500)->addText($no++);
            
            $cell2 = $table->addCell(2000);
            if ($lastDate !== $currentDate) {
                $cell2->addText($report['hari'] . ",");
                $cell2->addText($currentDate);
                $lastDate = $currentDate;
            }
            
            $cell3 = $table->addCell(4000);
            foreach ($report['aktifitas'] as $aktifitas) {
                $cell3->addText(strip_tags($aktifitas));
                $cell3->addTextBreak(1);
            }

            $cell4 = $table->addCell(4000);
            if (!empty($report['diff_text'])) {
                $lines = explode("\n", $report['diff_text']);
                foreach ($lines as $line) {
                    $color = '000000';
                    $bgColor = null;
                    if (str_starts_with($line, '+')) {
                        $color = '28a745';
                    } elseif (str_starts_with($line, '-')) {
                        $color = 'cb2431';
                    } elseif (str_starts_with($line, '@@') || str_starts_with($line, 'File:')) {
                        $color = '0366d6';
                    }
                    $cell4->addText($line, ['color' => $color, 'name' => 'Courier New', 'size' => 6], ['spaceAfter' => 0, 'spaceBefore' => 0]);
                }
            } elseif (!empty($report['gambar'][0])) {
                $imgPath = storage_path('app/public/' . $report['gambar'][0]);
                if (file_exists($imgPath) && !is_dir($imgPath)) {
                    $cell4->addImage($imgPath, ['width' => 450]);
                } else {
                    $cell4->addText('Gambar tidak ditemukan', ['italic' => true]);
                }
            } else {
                $cell4->addText('Tidak ada lampiran diff text atau gambar', ['italic' => true]);
            }
        }

        // Simpan file DOC
        $filename = "Laporan {$bulanNama} {$tahun}.docx";

        $filePath = storage_path("app/public/{$filename}");
        $phpWord->save($filePath, 'Word2007');

        // Bersihkan output buffer agar file DOCX tidak korup karena karakter kosong di PHP
        if (ob_get_length()) {
            ob_end_clean();
        }

        return response()->download($filePath);
    }

    public static function getPages(): array
    {
        return [
            'sort' => Pages\ReportGenerator::route('/report/generator'),
        ];
    }

    private function getSummarizedAktifitas($userId, $tanggalStr, $aktifitasArray)
    {
        $combinedAktifitas = implode("\n", $aktifitasArray);
        $cacheKey = "laporan_ai_summary_{$userId}_{$tanggalStr}_" . md5($combinedAktifitas) . "_v3";

        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        $accountId = env('CLOUDFLARE_ACCOUNT_ID');
        $apiToken = env('CLOUDFLARE_API_TOKEN');

        if ($accountId && $apiToken) {
            try {
                $prompt = "Tugas Anda adalah membuat ringkasan dari beberapa pesan commit teknis berikut menjadi uraian tugas harian yang profesional dan mudah dipahami oleh orang awam (non-IT). " .
                          "Jelaskan aktivitas apa saja yang telah dikerjakan berdasarkan pesan commit berikut dalam SATU PARAGRAF utuh.\n\n" .
                          "ATURAN PENTING:\n" .
                          "1. Gunakan kalimat pasif atau kata kerja dasar (contoh: 'Melakukan pengembangan...', 'Telah diselesaikan...', 'Menambahkan fitur...').\n" .
                          "2. DILARANG KERAS menggunakan kata ganti orang pertama seperti 'Saya', 'Kami', 'Aku', atau frasa seperti 'Saya telah'.\n" .
                          "3. Berikan hasil akhir HANYA deskripsinya saja (tanpa tanda kutip, tanpa kata pengantar, tanpa menggunakan format list/bullet/angka).\n\n" .
                          "Pesan Commit:\n" .
                          $combinedAktifitas;

                $response = Http::withToken($apiToken)
                    ->timeout(20)
                    ->post("https://api.cloudflare.com/client/v4/accounts/{$accountId}/ai/run/@cf/meta/llama-3.1-8b-instruct-fp8", [
                        'messages' => [
                            ['role' => 'system', 'content' => 'Anda adalah sistem pembuat laporan profesional. Terjemahkan pesan commit menjadi satu paragraf ringkasan harian tanpa kata ganti orang pertama (tanpa kata "Saya").'],
                            ['role' => 'user', 'content' => $prompt]
                        ]
                    ]);

                if ($response->successful()) {
                    $aiText = trim($response->json('result.response'));
                    if (!empty($aiText)) {
                        $result = trim($aiText, "\"' \t\n\r\0\x0B");
                        Cache::put($cacheKey, $result, 60*60*24*30);
                        return $result;
                    }
                } else {
                    \Illuminate\Support\Facades\Log::error('Cloudflare AI Error: ' . $response->body());
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Cloudflare AI Exception: ' . $e->getMessage());
            }
        }

        return $combinedAktifitas;
    }
}
