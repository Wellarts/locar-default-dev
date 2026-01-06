<?php

namespace App\Filament\Widgets;

use App\Models\ContasPagar;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ContasPagarHoje extends BaseWidget
{
    protected static ?string $heading = 'Para Pagar Hoje/Vencidas';
    protected static ?int $sort = 7;

    protected function getTableQuery(): Builder
    {
        return ContasPagar::query()
            ->with('fornecedor:id,nome')
            ->where('status', 0)
            ->whereDate('data_vencimento', '<=', now()->toDateString());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('fornecedor.nome')
                    ->sortable()
                    ->searchable()
                    ->label('Fornecedor')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('ordem_parcela')
                    ->alignCenter()
                    ->label('Parcela Nº')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('data_vencimento')
                    ->label('Vencimento')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('danger')
                    ->date('d/m/Y')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('valor_parcela')
                    ->label('Valor Parcela')
                    ->summarize(
                        Sum::make()
                            ->money('BRL')
                            ->label('Total')
                    )
                    ->alignCenter()
                    ->badge()
                    ->color('danger')
                    ->money('BRL')
                    ->toggleable(),
            ])
            ->defaultPaginationPageOption(10)
            ->paginated([10, 25, 50])
            ->defaultSort('data_vencimento', 'asc')
            ->emptyStateHeading('Nenhuma conta a pagar hoje ou vencida.');
    }

    public static function canView(): bool
    {
        return ContasPagar::where('status', 0)
            ->whereDate('data_vencimento', '<=', now()->toDateString())
            ->exists();
    }
}