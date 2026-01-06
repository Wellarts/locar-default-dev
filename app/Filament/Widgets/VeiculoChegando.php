<?php

namespace App\Filament\Widgets;

use App\Models\Locacao;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class VeiculoChegando extends BaseWidget
{
    protected static ?string $heading = 'Próximos Retornos';
    protected static ?int $sort = 2;

    protected function getTableQuery(): Builder
    {
        return Locacao::query()
            ->with(['veiculo:id,modelo,placa,ano'])
            ->where('status', 0)
            ->orderBy('data_retorno', 'asc')
            ->select(['id', 'veiculo_id', 'data_retorno']);
    }

    public function table(Table $table): Table
    {
        $hoje = Carbon::today();

        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('veiculo.modelo')
                    ->badge()
                    ->color('warning')
                    ->label('Modelo')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('veiculo.placa')
                    ->label('Placa')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('veiculo.ano')
                    ->label('Ano')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('data_retorno')
                    ->badge()
                    ->label('Data Retorno')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($state): string => $this->getDataRetornoColor($state))
                    ->tooltip(function ($state) use ($hoje) {
                        if (empty($state)) return null;
                        
                        try {
                            $dataRetorno = Carbon::parse($state);
                            $diasRestantes = $hoje->diffInDays($dataRetorno, false);
                            
                            if ($diasRestantes < 0) {
                                return "Atrasado há " . abs($diasRestantes) . " dias";
                            } elseif ($diasRestantes == 0) {
                                return "Retorna hoje";
                            } else {
                                return "Faltam {$diasRestantes} dias";
                            }
                        } catch (\Throwable $e) {
                            return null;
                        }
                    }),
            ])
            ->defaultPaginationPageOption(5)
            ->paginated([5, 10, 15])
            ->emptyStateHeading('Nenhum veículo em locação')
            ->emptyStateDescription('Não há veículos com retornos próximos.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->deferLoading() // Adia o carregamento para melhor performance inicial
            ->striped();
    }

    private function getDataRetornoColor(?string $state): string
    {
        if (empty($state)) {
            return 'secondary';
        }

        try {
            $dataRetorno = Carbon::parse($state);
            $hoje = Carbon::today();
            $qtdDias = $hoje->diffInDays($dataRetorno, false);

            if ($qtdDias < 0) {
                return 'danger'; // Atrasado
            }

            if ($qtdDias <= 1) {
                return 'warning'; // Retorna hoje ou amanhã
            }

            if ($qtdDias <= 3) {
                return 'info'; // Retorna em até 3 dias
            }

            return 'success'; // Retorna em mais de 3 dias
        } catch (\Throwable $e) {
            return 'secondary';
        }
    }

    public static function canView(): bool
    {
        return Locacao::where('status', 0)->exists();
    }

    protected function getTableRecordClassesUsing(): ?\Closure
    {
        return function ($record) {
            if (empty($record->data_retorno)) {
                return null;
            }

            try {
                $dataRetorno = Carbon::parse($record->data_retorno);
                $hoje = Carbon::today();
                $qtdDias = $hoje->diffInDays($dataRetorno, false);

                if ($qtdDias < 0) {
                    return 'bg-danger-50 dark:bg-danger-900/20'; // Atrasado
                }

                if ($qtdDias <= 1) {
                    return 'bg-warning-50 dark:bg-warning-900/20'; // Retorna hoje ou amanhã
                }
            } catch (\Throwable $e) {
                return null;
            }

            return null;
        };
    }

    protected function getTablePollingInterval(): ?string
    {
        // Atualiza a cada 60 segundos para mostrar dados atualizados
        return '60s';
    }
}