<?php

namespace App\Filament\Widgets;

use App\Models\ContasReceber;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ContasReceberHoje extends BaseWidget
{
    protected static ?string $heading = 'Para Receber Hoje/Vencidas';
    protected static ?int $sort = 6;

    protected function getTableQuery(): Builder
    {
        return ContasReceber::query()
            ->with('cliente:id,nome')
            ->where('status', 0)
            ->whereDate('data_vencimento', '<=', now()->toDateString());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('cliente.nome')
                    ->sortable()
                    ->searchable()
                    ->label('Cliente')
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
            ->defaultSort('data_vencimento', 'asc');
    }

    public static function canView(): bool
    {
        return ContasReceber::where('status', 0)
            ->whereDate('data_vencimento', '<=', now()->toDateString())
            ->exists();
    }
}