<?php

namespace App\Filament\Resources\MasterLokasiResource\Pages;

use App\Filament\Resources\MasterLokasiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterLokasis extends ListRecords
{
    protected static string $resource = MasterLokasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
