<?php

namespace App\Filament\Resources\ContratoResource\Pages;

use App\Filament\Resources\ContratoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageContratos extends ManageRecords
{
    protected static string $resource = ContratoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
