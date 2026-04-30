<?php

namespace App\Filament\Resources\MasterKategoriResource\Pages;

use App\Filament\Resources\MasterKategoriResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterKategori extends EditRecord
{
    protected static string $resource = MasterKategoriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
