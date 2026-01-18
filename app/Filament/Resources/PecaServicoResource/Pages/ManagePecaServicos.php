<?php

namespace App\Filament\Resources\PecaServicoResource\Pages;

use App\Filament\Resources\PecaServicoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePecaServicos extends ManageRecords
{
    protected static string $resource = PecaServicoResource::class;

    protected static ?string $title = 'Peças e Serviços';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus')
                ->modalHeading('Criar Nova Peça/Serviço'),
        ];
    }
}
