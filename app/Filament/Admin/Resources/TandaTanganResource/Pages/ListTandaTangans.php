<?php

namespace App\Filament\Admin\Resources\TandaTanganResource\Pages;

use App\Filament\Admin\Resources\TandaTanganResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTandaTangans extends ListRecords
{
    protected static string $resource = TandaTanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
