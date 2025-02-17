<?php

namespace App\Filament\Admin\Resources\LaporanResource\Pages;

use App\Filament\Admin\Resources\LaporanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLaporans extends ListRecords
{
    protected static string $resource = LaporanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // public function getHeader(): ?string
    // {
    //     return 'Daftar Data Laporan';
    // }

    // public function getTitle(): string
    // {
    //     return 'Laporans';
    // }
}
