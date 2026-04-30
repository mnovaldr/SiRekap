<?php

namespace App\Filament\Resources\MasterLokasiResource\Pages;

use App\Filament\Resources\MasterLokasiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterLokasi extends EditRecord
{
    protected static string $resource = MasterLokasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
