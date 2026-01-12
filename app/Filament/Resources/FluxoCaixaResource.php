<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FluxoCaixaResource\Pages;
use App\Filament\Resources\FluxoCaixaResource\RelationManagers;
use App\Models\FluxoCaixa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;

class FluxoCaixaResource extends Resource
{
    protected static ?string $model = FluxoCaixa::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $navigationLabel = 'Fluxo de Caixa';

    protected static ?string $navigationGroup = 'Financeiro';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tipo')
                    ->options([
                        'CREDITO' => 'CRÉDITO',
                        'DEBITO' => 'DÉBITO',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('valor')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->numeric()
                    ->prefix('R$')
                    ->inputMode('decimal')
                    ->required(),

                Forms\Components\Textarea::make('obs')
                    ->label('Descrição')
                    ->autosize()
                    ->columnSpanFull()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('tipo')
                    ->searchable()
                    ->badge()
                    ->color(static function ($state): string {
                        if ($state === 'CREDITO') {
                            return 'success';
                        }

                        return 'danger';
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('valor')
                    ->summarize(Sum::make()->money('BRL')->label('Total'))
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('obs')
                    ->label('Descrição')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                // ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
             ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Data Inicial')
                            ->default(now()->toDateString()),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Data Final')
                            ->default(now()->toDateString()),
                    ])
                    ->query(function ($query, $data) {
                        if ($data['created_from']) {
                            $query->whereDate('created_at', '>=', $data['created_from']);
                        }
                        if ($data['created_until']) {
                            $query->whereDate('created_at', '<=', $data['created_until']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Editar lançamento de caixa'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFluxoCaixas::route('/'),
        ];
    }
}
