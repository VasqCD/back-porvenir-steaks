<?php

namespace App\Filament\Resources\NotificacionResource\Pages;

use App\Filament\Resources\NotificacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNotificacion extends ViewRecord
{
    protected static string $resource = NotificacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
