<?php

namespace App\Filament\Resources;

use App\Filament\Exports\ContasPagarExporter;
use App\Filament\Resources\ContasPagarResource\Pages;
use App\Models\ContasPagar;
use App\Models\FluxoCaixa;
use App\Models\Fornecedor;
use Carbon\Carbon;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ContasPagarResource extends Resource
{
    protected static ?string $model = ContasPagar::class;
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $title = 'Contas a Pagar';
    protected static ?string $navigationLabel = 'Contas a Pagar';
    protected static ?string $navigationGroup = 'Financeiro';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Fornecedor com busca otimizada
                Forms\Components\Select::make('fornecedor_id')
                    ->disabled(fn($context) => $context === 'edit')
                    ->label('Fornecedor')
                    ->searchable()
                    ->getSearchResultsUsing(
                        fn(string $search): array =>
                        Fornecedor::where('nome', 'like', "%{$search}%")
                            ->limit(50)
                            ->pluck('nome', 'id')
                            ->toArray()
                    )
                    ->getOptionLabelUsing(
                        fn($value): ?string =>
                        Fornecedor::find($value)?->nome
                    )
                    ->required()
                    ->preload(),

                // Valor Total
                Forms\Components\TextInput::make('valor_total')
                    ->disabled(fn($context) => $context === 'edit')
                    ->label('Valor Total')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->numeric()
                    ->prefix('R$')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                        self::calcularValores($get, $set, $state);
                    }),

                // Categoria com pré-carregamento
                Forms\Components\Select::make('categoria_id')
                    ->label('Categoria')
                    ->relationship('categoria', 'nome')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('nome')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                    ]),

                // Próxima Parcela
                Forms\Components\Select::make('proxima_parcela')
                    ->hiddenOn('edit')
                    ->options([
                        '7' => 'Semanal',
                        '15' => 'Quinzenal',
                        '30' => 'Mensal',
                    ])
                    ->default(30)
                    ->label('Próximas Parcelas')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $set('data_vencimento', now()->addDays($get('proxima_parcela'))->format('Y-m-d'));
                    }),

                // Parcelas
                Forms\Components\TextInput::make('parcelas')
                    ->hiddenOn('edit')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        self::calcularValores($get, $set, $get('valor_total'));
                    }),

                // Forma de Pagamento
                Forms\Components\Select::make('formaPgmto')
                    ->label('Forma de Pagamento')
                    ->required()
                    ->options([
                        1 => 'Dinheiro',
                        2 => 'Pix',
                        3 => 'Cartão',
                        4 => 'Boleto',
                    ]),

                Forms\Components\Hidden::make('ordem_parcela')
                    ->default('1'),

                Forms\Components\DatePicker::make('data_vencimento')
                    ->displayFormat('d/m/Y')
                    ->default(now()->addDays(30))
                    ->label("Data do Vencimento")
                    ->required(),

                Forms\Components\Toggle::make('status')
                    ->default(false)
                    ->label('Pago')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        if ($state) {
                            $set('valor_pago', $get('valor_parcela'));
                            $set('data_pagamento', now()->format('Y-m-d'));
                        } else {
                            $set('valor_pago', 0);
                            $set('data_pagamento', null);
                        }
                    }),

                Forms\Components\TextInput::make('valor_parcela')
                    ->label('Valor Parcela')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->numeric()
                    ->prefix('R$')
                    ->readOnly(),

                Forms\Components\TextInput::make('valor_pago')
                    ->label('Valor Pago')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->numeric()
                    ->prefix('R$'),

                Forms\Components\DatePicker::make('data_pagamento')
                    ->displayFormat('d/m/Y')
                    ->label("Data Pagamento"),

                Forms\Components\Textarea::make('obs')
                    ->label('Observações')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Função auxiliar para cálculos
     */
    private static function calcularValores(Get $get, Set $set, ?string $valorTotal): void
    {
        $parcelas = (int) $get('parcelas') ?: 1;
        $valorTotal = (float) str_replace(['.', ','], ['', '.'], $valorTotal) ?: 0;

        if ($parcelas > 1) {
            $valorParcela = $valorTotal / $parcelas;
            $set('valor_parcela', number_format($valorParcela, 2, '.', ''));
            $set('status', false);
            $set('valor_pago', 0);
            $set('data_pagamento', null);
            $set('data_vencimento', now()->addDays($get('proxima_parcela'))->format('Y-m-d'));
        } else {
            $set('valor_parcela', number_format($valorTotal, 2, '.', ''));
            $set('status', true);
            $set('valor_pago', number_format($valorTotal, 2, '.', ''));
            $set('data_pagamento', now()->format('Y-m-d'));
            $set('data_vencimento', now()->format('Y-m-d'));
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('data_vencimento', 'asc')
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(ContasPagarExporter::class)
                    ->formats([
                        ExportFormat::Xlsx,
                    ])
                    ->columnMapping(false)
                    ->label('Exportar Contas')
                    ->modalHeading('Confirmar exportação?')
            ])
            ->columns([
                Tables\Columns\TextColumn::make('fornecedor.nome')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ordem_parcela')
                    ->sortable()
                    ->alignCenter()
                    ->label('Parcela Nº')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('data_vencimento')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->badge()
                    ->sortable()
                    ->color(fn($record) => $record->status ? 'success' : ($record->data_vencimento < now() ? 'danger' : 'warning'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('valor_total')
                    ->label('Valor Total')
                    ->money('BRL')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('categoria.nome')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\SelectColumn::make('formaPgmto')
                    ->label('Forma de Pagamento')
                    ->disabled()
                    ->options([
                        1 => 'Dinheiro',
                        2 => 'Pix',
                        3 => 'Cartão',
                        4 => 'Boleto',
                    ])
                    ->toggleable(),

                Tables\Columns\TextColumn::make('valor_parcela')
                    ->label('Valor Parcela')
                    ->money('BRL')
                    ->summarize(Sum::make()->money('BRL')->label('Total'))
                    ->toggleable(),

                Tables\Columns\IconColumn::make('status')
                    ->label('Pago')
                    ->icon(fn($record) => $record->status ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn($record) => $record->status ? 'success' : 'danger')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('valor_pago')
                    ->label('Valor Pago')
                    ->money('BRL')
                    ->summarize(Sum::make()->money('BRL')->label('Pago'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('data_pagamento')
                    ->label('Data Pagamento')
                    ->date('d/m/Y')
                    ->badge()
                    ->color('warning')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('pendentes')
                    ->query(fn($query) => $query->where('status', false))
                    ->default(),

                Filter::make('vencidas')
                    ->query(fn($query) => $query->where('data_vencimento', '<', now())
                        ->where('status', false)),

                Filter::make('pagas')
                    ->query(fn($query) => $query->where('status', true)),

                SelectFilter::make('fornecedor')
                    ->relationship('fornecedor', 'nome')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('categoria')
                    ->relationship('categoria', 'nome')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('data_vencimento')
                    ->form([
                        Forms\Components\DatePicker::make('vencimento_de')
                            ->label('Vencimento de:'),
                        Forms\Components\DatePicker::make('vencimento_ate')
                            ->label('Vencimento até:'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['vencimento_de'],
                                fn($query) => $query->whereDate('data_vencimento', '>=', $data['vencimento_de'])
                            )
                            ->when(
                                $data['vencimento_ate'],
                                fn($query) => $query->whereDate('data_vencimento', '<=', $data['vencimento_ate'])
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Editar conta a pagar')
                    ->after(function ($record) {
                        DB::transaction(function () use ($record) {
                            // Remover registro anterior do fluxo de caixa
                            FluxoCaixa::where('contas_pagar_id', $record->id)->delete();
                            
                            // Se a conta foi marcada como paga, adicionar ao fluxo de caixa
                            if ($record->status) {
                                FluxoCaixa::create([
                                    'valor' => $record->valor_parcela * -1,
                                    'contas_pagar_id' => $record->id,
                                    'tipo'  => 'DEBITO',
                                    'obs'   => 'Pagamento da conta do fornecedor ' . $record->fornecedor->nome . ' - Forma de Pagamento: ' . self::getFormaPagamentoTexto($record->formaPgmto),
                                ]);
                            }
                        });
                    }),

                Tables\Actions\DeleteAction::make()
                    ->after(function ($record) {
                        DB::transaction(function () use ($record) {
                            if ($record->status) {
                                FluxoCaixa::where('contas_pagar_id', $record->id)->delete();
                            }
                        });
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function ($records) {
                            DB::transaction(function () use ($records) {
                                foreach ($records as $record) {
                                    if ($record->status) {
                                        FluxoCaixa::where('contas_pagar_id', $record->id)->delete();
                                    }
                                }
                            });
                        }),
                    
                    Tables\Actions\BulkAction::make('marcarComoPago')
                        ->label('Marcar como pago')
                        ->icon('heroicon-o-check-circle')
                        ->action(function ($records) {
                            DB::transaction(function () use ($records) {
                                foreach ($records as $record) {
                                    $record->update([
                                        'status' => true,
                                        'data_pagamento' => now(),
                                        'valor_pago' => $record->valor_parcela,
                                    ]);

                                    FluxoCaixa::create([
                                        'valor' => $record->valor_parcela * -1,
                                        'contas_pagar_id' => $record->id,
                                        'tipo' => 'DEBITO',
                                        'obs' => 'Pagamento da conta do fornecedor ' . $record->fornecedor->nome . ' - Forma de Pagamento: ' . self::getFormaPagamentoTexto($record->formaPgmto),
                                    ]);
                                }
                            });
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('marcarComoPendente')
                        ->label('Marcar como pendente')
                        ->icon('heroicon-o-x-circle')
                        ->action(function ($records) {
                            DB::transaction(function () use ($records) {
                                foreach ($records as $record) {
                                    $record->update([
                                        'status' => false,
                                        'data_pagamento' => null,
                                        'valor_pago' => 0,
                                    ]);

                                    FluxoCaixa::where('contas_pagar_id', $record->id)->delete();
                                }
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->deferLoading(); // Carrega dados apenas quando necessário
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageContasPagars::route('/'),
        ];
    }

    private static function getFormaPagamentoTexto($formaPgmto): string
    {
        $formas = [
            1 => 'Dinheiro',
            2 => 'Pix',
            3 => 'Cartão',
            4 => 'Boleto',
        ];
        return $formas[$formaPgmto] ?? 'Desconhecido';
    }
}