<?php

namespace App\Filament\Resources\FluxoCaixaResource\Pages;

use App\Filament\Resources\FluxoCaixaResource;
use App\Livewire\CaixaStatsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageFluxoCaixas extends ManageRecords
{
    protected static string $resource = FluxoCaixaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->after(
                    function ($data, $record) {
                        if ($record->tipo == 'DEBITO') {
                            $record->update([
                                'valor' => $record->valor * -1,
                            ]);
                        }
                    }
                ),
            Actions\Action::make('exportar_pdf')
                ->label('Relatório Fluxo de Caixa')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('data_de')->label('Data de'),
                    \Filament\Forms\Components\DatePicker::make('data_ate')->label('Data até'),
                    \Filament\Forms\Components\Select::make('tipo')
                        ->label('Tipo')
                        ->options([
                            '' => 'Todos',
                            'CREDITO' => 'Crédito',
                            'DEBITO' => 'Débito',
                        ]),
                   
                ])
                ->action(function (array $data, $livewire) {
                    $query = http_build_query(array_filter($data));
                    $url = route('relatorio.fluxo.caixa.pdf') . '?' . $query;
                     $livewire->js("window.open('{$url}', '_blank')");
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CaixaStatsOverview::class,
         //   VendasMesChart::class,
        ];
    }
}
