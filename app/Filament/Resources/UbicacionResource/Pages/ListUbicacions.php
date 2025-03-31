<?php

namespace App\Filament\Resources\UbicacionResource\Pages;

use App\Filament\Resources\UbicacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUbicacions extends ListRecords
{
    protected static string $resource = UbicacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
