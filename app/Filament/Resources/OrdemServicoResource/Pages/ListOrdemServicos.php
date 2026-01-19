<?php

namespace App\Filament\Resources\OrdemServicoResource\Pages;

use App\Filament\Resources\OrdemServicoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


class ListOrdemServicos extends ListRecords
{
    protected static string $resource = OrdemServicoResource::class;

    protected static ?string $title = 'Ordens de Serviço';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus')
                ->modalHeading('Criar Nova Ordem de Serviço'),
                // ->before(function($record) {
                //     // Atualiza o km_atual do veículo escolhido na ordem de serviço
                //     if ($record->veiculo_id && $record->km_troca) {
                //         $veiculo = \App\Models\Veiculo::find($record->veiculo_id);
                //         if ($veiculo) {
                //             $veiculo->km_atual = $record->km_troca;
                //             $veiculo->save();
                //         }
                //     }
                // }),
            Actions\Action::make('relatorio')
                ->label('Relatório - PDF')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->modalHeading('Filtrar Relatório de Ordens de Serviço')
                ->form([
                    \Filament\Forms\Components\Fieldset::make('Filtros')
                        ->columns([
                            'sm' => 1,
                            'md' => 2,
                        ])
                        ->schema([
                            \Filament\Forms\Components\Select::make('cliente_id')
                                ->label('Cliente')
                                ->relationship('cliente', 'nome')
                                ->searchable()
                                ->preload(),
                            \Filament\Forms\Components\Select::make('veiculo_id')
                             ->label('Veículo')
                            ->live(onBlur: true)
                            ->relationship(
                                name: 'veiculo',
                                modifyQueryUsing: function (Builder $query, $context) {
                                    $query->where('status', 1)->orderBy('modelo')->orderBy('placa');
                                }
                            )
                            ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->modelo} {$record->placa}")
                            ->searchable(['modelo', 'placa']),
                            \Filament\Forms\Components\Select::make('fornecedor_id')
                                ->label('Fornecedor')
                                ->relationship('fornecedor', 'nome')
                                ->searchable()
                                ->preload(),
                            \Filament\Forms\Components\Select::make('forma_pagamento_id')
                                ->label('Forma de Pagamento')
                                ->relationship('formaPagamento', 'nome')
                                ->searchable()
                                ->preload(),
                            \Filament\Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options([
                                    '0' => 'Pendente',
                                    '1' => 'Concluído',
                                ])
                                ->searchable(),
                            \Filament\Forms\Components\DatePicker::make('data_inicio')
                                ->label('Data Inicial'),
                            \Filament\Forms\Components\DatePicker::make('data_fim')
                                ->label('Data Final'),
                        ]),
                ])
                ->action(function(array $data, $livewire) {
                    $params = [];
                    if(!empty($data['cliente_id'])) $params['cliente_id'] = $data['cliente_id'];
                    if(!empty($data['veiculo_id'])) $params['veiculo_id'] = $data['veiculo_id'];
                    if(!empty($data['fornecedor_id'])) $params['fornecedor_id'] = $data['fornecedor_id'];
                    if(!empty($data['forma_pagamento_id'])) $params['forma_pagamento_id'] = $data['forma_pagamento_id'];
                    if(!empty($data['data_inicio'])) $params['data_inicio'] = $data['data_inicio'];
                    if(!empty($data['data_fim'])) $params['data_fim'] = $data['data_fim'];
                    if(!empty($data['status'])) $params['status'] = $data['status'];
                    $queryString = http_build_query($params);
                    $url = route('imprimirOrdemServicoRelatorio') . ($queryString ? ('?' . $queryString) : '');
                    $livewire->js("window.open('{$url}', '_blank')");
                }),
               
        ];
    }
}