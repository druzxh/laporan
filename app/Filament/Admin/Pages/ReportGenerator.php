<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

use Illuminate\Http\Request; // Pastikan Request diimpor
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

        $reports = \App\Models\Laporan::where('user_id', $userId)
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->get()
            ->groupBy(function ($item) {
                return sprintf('%04d-%02d-%02d', $item->tahun, $item->bulan, $item->tanggal);
            })
            ->map(function ($group) {
                return [
                    'hari' => $group->first()->hari,
                    'tanggal' => $group->first()->tanggal,
                    'bulan' => $group->first()->bulan,
                    'tahun' => $group->first()->tahun,
                    'aktifitas' => $group->pluck('aktifitas')->toArray(),
                    'gambar' => $group->pluck('gambar')->toArray(),
                ];
            })
            ->sortBy(function ($report) {
                return "{$report['tahun']}-{$report['bulan']}-{$report['tanggal']}";
            });

        $ttd = \App\Models\TandaTangan::all();
        $pdf = Pdf::loadView('reports.pdf', compact(['user', 'reports', 'bulanNama', 'tahun', 'userTtd', 'verifikatorTtd', 'persetujuanTtd']))->setPaper('a4', 'landscape');;

        if ($request->has('preview')) {
            return $pdf->stream("Laporan {$bulanNama} {$tahun}.pdf");
        }

        $filename = "Laporan {$bulanNama} {$tahun}.pdf";
        return $pdf->download($filename);
    }

    public function generateDOC(Request $request)
    {
        $tahun = $request->tahun;
        $bulan = $request->bulan;

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

        $user = auth()->user(); 

        $reports = \App\Models\Laporan::where('user_id', auth()->id())
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->get()
            ->groupBy(function ($item) {
                return $item->hari . ',' . $item->tanggal . '/' . $item->bulan . '/' . $item->tahun;
            })->map(function ($group) {
                return [
                    'hari' => $group->first()->hari,
                    'tanggal' => $group->first()->tanggal,
                    'bulan' => $group->first()->bulan,
                    'tahun' => $group->first()->tahun,
                    'aktifitas' => $group->pluck('aktifitas')->toArray(),
                    'gambar' => $group->pluck('gambar')->toArray(),
                ];
            });

        // Generate DOC
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $section->addText('Data Laporan');
        foreach ($reports as $report) {
            $section->addText("ID: {$report->id}, Title: {$report->title}, Description: {$report->aktifitas}");
        }

        // Simpan file DOC
        $filename = "Laporan {$bulanNama} {$tahun}.pdf";

        $filePath = storage_path("app/public/{$fileName}");
        $phpWord->save($filePath, 'Word2007');

        return response()->download($filePath);
    }
}
