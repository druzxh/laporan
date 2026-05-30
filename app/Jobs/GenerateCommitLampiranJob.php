<?php

namespace App\Jobs;

use App\Models\Laporan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateCommitLampiranJob implements ShouldQueue
{
    use Queueable;

    public Laporan $laporan;
    public array $commitData;

    /**
     * Create a new job instance.
     */
    public function __construct(Laporan $laporan, array $commitData)
    {
        $this->laporan = $laporan;
        $this->commitData = $commitData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Parameter canvas
        $width = 800;
        $height = 400;

        // Bikin gambar
        $image = imagecreatetruecolor($width, $height);

        // Warna
        $bgDark = imagecolorallocate($image, 13, 17, 23); // github dark theme bg
        $textWhite = imagecolorallocate($image, 201, 209, 217);
        $textGray = imagecolorallocate($image, 139, 148, 158);
        $textGreen = imagecolorallocate($image, 46, 160, 67);
        $textBlue = imagecolorallocate($image, 88, 166, 255);

        // Fill background
        imagefill($image, 0, 0, $bgDark);

        // Ambil data untuk digambar
        $aktifitas = $this->laporan->aktifitas ?? 'Update commit';
        $tanggalStr = ($this->laporan->tanggal ?? '') . '-' . ($this->laporan->bulan ?? '') . '-' . ($this->laporan->tahun ?? '');
        $pembuat = $this->laporan->user->name ?? 'User';
        
        $sha = $this->commitData['sha'] ?? Str::random(7);
        $repo = $this->commitData['repo'] ?? 'repository';
        
        // Custom font size and positions (menggunakan build-in font imagestring ukuran 5)
        $font = 5;
        $x = 40;
        $y = 40;

        // Draw header
        imagestring($image, $font, $x, $y, "Repository: " . $repo, $textBlue);
        $y += 40;
        
        // Draw Commit message
        imagestring($image, $font, $x, $y, "Commit: " . $aktifitas, $textWhite);
        $y += 30;

        // Draw detail
        imagestring($image, $font, $x, $y, "Author: " . $pembuat, $textGray);
        $y += 25;
        imagestring($image, $font, $x, $y, "Date  : " . $tanggalStr, $textGray);
        $y += 25;
        imagestring($image, $font, $x, $y, "SHA   : " . $sha, $textGreen);
        $y += 50;
        
        imagestring($image, $font, $x, $y, "Status: Verified", $textGreen);

        // Simpan ke disk public/laporan
        $fileName = 'commit_' . $this->laporan->id . '_' . time() . '.png';
        $path = 'laporan/' . $fileName;
        
        // Menangkap output image ke variabel
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        // Simpan via Storage
        Storage::disk('public')->put($path, $imageData);

        // Update laporan
        $this->laporan->update([
            'gambar' => $path
        ]);
    }
}
