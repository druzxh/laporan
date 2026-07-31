<?php

namespace App\Filament\Admin\Resources\HasilLaporanResource\Pages;

use App\Filament\Admin\Resources\HasilLaporanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHasilLaporan extends EditRecord
{
    protected static string $resource = HasilLaporanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
