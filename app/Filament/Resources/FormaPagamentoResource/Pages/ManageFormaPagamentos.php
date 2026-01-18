<?php

namespace App\Filament\Resources\FormaPagamentoResource\Pages;

use App\Filament\Resources\FormaPagamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageFormaPagamentos extends ManageRecords
{
    protected static string $resource = FormaPagamentoResource::class;

    protected static ?string $title = 'Formas de Pagamento';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus')
                ->modalHeading('Criar Nova Forma de Pagamento'),
        ];
    }
}
