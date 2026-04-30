<?php

namespace App\Filament\Resources\MasterKategoriResource\Pages;

use App\Filament\Resources\MasterKategoriResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterKategoris extends ListRecords
{
    protected static string $resource = MasterKategoriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
