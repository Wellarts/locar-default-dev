<?php

namespace App\Filament\Resources\VeiculoResource\Pages;

use App\Filament\Resources\VeiculoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageVeiculos extends ManageRecords
{
    protected static string $resource = VeiculoResource::class;

    protected static ?string $title = 'Veículos';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus')
                ->modalHeading('Criar Novo Veículo'),
        ];
    }
}
