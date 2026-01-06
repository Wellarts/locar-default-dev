<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgendamentoResource\Pages;
use App\Filament\Resources\AgendamentoResource\RelationManagers;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Veiculo;
use App\Models\Estado;
use Filament\Forms\Components\FileUpload;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Support\RawJs;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AgendamentoResource extends Resource
{
    protected static ?string $model = Agendamento::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Locar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Dados do Agendamento')
                    ->schema([
                        Grid::make([
                            'xl' => 4,
                            '2xl' => 4,
                        ])
                            ->schema([

                                Forms\Components\Select::make('cliente_id')
                                    ->label('Cliente')
                                    ->searchable()
                                    ->native(false)
                                    ->columnSpan([
                                        'xl' => 2,
                                        '2xl' => 2,
                                    ])
                                    ->live()
                                    ->required(false)
                                    ->relationship('cliente', 'nome')
                                    ->createOptionForm([
                                        Grid::make([
                                            'xl' => 3,
                                            '2xl' => 3,
                                        ])
                                            ->schema([
                                                Forms\Components\TextInput::make('nome')
                                                    ->label('Nome')
                                                    ->columnSpan([
                                                        'xl' => 2,
                                                        '2xl' => 2,
                                                    ])
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('cpf_cnpj')
                                                    ->label('CPF/CNPJ')
                                                    ->mask(RawJs::make(<<<'JS'
                                                            $input.length > 14 ? '99.999.999/9999-99' : '999.999.999-99'
                                                        JS))
                                                    ->rule('cpf_ou_cnpj'),
                                                Forms\Components\Textarea::make('endereco')
                                                    ->label('Endereço')
                                                    ->columnSpanFull(),
                                                Forms\Components\Select::make('estado_id')
                                                    ->label('Estado')
                                                    ->native(false)
                                                    ->searchable()
                                                    ->required()
                                                    ->default(33)
                                                    ->options(Estado::all()->pluck('nome', 'id')->toArray())
                                                    ->live(),
                                                Forms\Components\Select::make('cidade_id')
                                                    ->label('Cidade')
                                                    ->default(3243)
                                                    ->native(false)
                                                    ->searchable()
                                                    ->required()
                                                    ->options(function (callable $get) {
                                                        $estado = Estado::find($get('estado_id'));
                                                        if (!$estado) {
                                                            return Estado::all()->pluck('nome', 'id');
                                                        }
                                                        return $estado->cidade->pluck('nome', 'id');
                                                    })
                                                    ->live(),
                                                Forms\Components\TextInput::make('telefone_1')
                                                    ->label('Telefone 1')
                                                    ->tel()
                                                    ->mask('(99)99999-9999'),
                                                Forms\Components\TextInput::make('telefone_2')
                                                    ->tel()
                                                    ->label('Telefone 2')
                                                    ->tel()
                                                    ->mask('(99)99999-9999'),
                                                Forms\Components\TextInput::make('email')
                                                    ->columnSpan([
                                                        'xl' => 2,
                                                        '2xl' => 2,
                                                    ])
                                                    ->email()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('rede_social')
                                                    ->label('Rede Social'),
                                                Forms\Components\TextInput::make('cnh')
                                                    ->label('CNH'),
                                                Forms\Components\DatePicker::make('validade_cnh')
                                                    //   ->format('d/m/Y')
                                                    ->label('Valiade da CNH'),
                                                Forms\Components\TextInput::make('rg')
                                                    ->label('RG'),
                                                Forms\Components\TextInput::make('exp_rg')
                                                    ->label('Orgão Exp.'),
                                                Forms\Components\Select::make('estado_exp_rg')
                                                    ->searchable()
                                                    ->label('UF - Expedidor')
                                                    ->options(Estado::all()->pluck('nome', 'id')->toArray()),
                                                FileUpload::make('img_cnh')
                                                    ->columnSpan([
                                                        'xl' => 2,
                                                        '2xl' => 2,
                                                    ])
                                                    ->downloadable()
                                                    ->label('Foto CNH'),

                                                Forms\Components\DatePicker::make('data_nascimento')
                                                    ->label('Data de Nascimento'),



                                            ])
                                    ]),


                                Forms\Components\Select::make('veiculo_id')
                                    ->required(false)
                                    ->label('Veículo')
                                    ->live(onBlur: true)
                                    ->relationship(name: 'veiculo', titleAttribute: 'modelo')
                                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->modelo} {$record->placa}")
                                    ->searchable(['modelo', 'placa'])
                                    // ->afterStateUpdated(function (Set $set, $state) {
                                    //     $veiculo = Veiculo::find($state);
                                    //     if ($state != null) {
                                    //         $set('km_saida', $veiculo->km_atual);
                                    //     }
                                    // })
                                    ->columnSpan([
                                        'xl' => 2,
                                        '2xl' => 2,
                                    ]),
                                Forms\Components\DatePicker::make('data_saida')
                                    ->default(Carbon::today())
                                    ->displayFormat('d/m/Y')
                                    ->label('Data Saída')
                                    ->required(false),
                                Forms\Components\TimePicker::make('hora_saida')
                                    ->seconds(false)
                                    ->default(Carbon::now())
                                    ->label('Hora Saída')
                                    ->required(false),
                                Forms\Components\DatePicker::make('data_retorno')
                                    ->displayFormat('d/m/Y')
                                    ->label('Data Retorno')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                        $dt_saida = Carbon::parse($get('data_saida'));
                                        $dt_retorno = Carbon::parse($get('data_retorno'));
                                        $qtd_dias = $dt_retorno->diffInDays($dt_saida);
                                        $set('qtd_diarias', $qtd_dias);

                                        $carro = Veiculo::find($get('veiculo_id'));
                                        $valor_total = $carro->valor_diaria * $qtd_dias;
                                        $set('valor_total', $valor_total);
                                        $set('valor_desconto', 0);
                                        // $set('valor_pago',$valor_total);
                                        $set('valor_restante', 0);

                                        ### CALCULO DOS DIAS E SEMANAS
                                        $diferencaEmDias = $dt_saida->diffInDays($dt_retorno);
                                        // Calculando a diferença em semanas
                                        $diferencaEmSemanas = $diferencaEmDias / 7;

                                        // Arredondando para baixo para obter o número inteiro de semanas
                                        $semanasCompletas = floor($diferencaEmSemanas);
                                        // Calculando os dias restantes (módulo 7)
                                        $diasRestantes = $diferencaEmDias % 7;
                                        //Calculando os meses
                                        $mesesCompleto = $diferencaEmDias / 30;
                                        //Calculando os meses em número inteiro
                                        $mesesCompleto = floor($mesesCompleto);
                                        //Calculando semanas restantes
                                        $diasRestantesMeses = $diferencaEmDias % 30;

                                        Notification::make()
                                            ->title('ATENÇÃO')
                                            ->body(
                                                'Para as datas escolhida temos:<br>
                                                <b>' . $qtd_dias . ' DIA(AS).</b><br>
                                                <b>' . $semanasCompletas . ' SEMANA(AS) e ' . $diasRestantes . ' DIA(AS). </b> <br>
                                                <b>' . $mesesCompleto . ' MÊS/MESES  e ' . $diasRestantesMeses . ' DIA(AS).</b><br>
                                            '
                                            )
                                            ->danger()
                                            ->persistent()
                                            ->send();
                                    })
                                    ->required(false),
                                Forms\Components\TimePicker::make('hora_retorno')
                                    ->seconds(false)
                                    ->default(Carbon::now())
                                    ->label('Hora Retorno')
                                    ->required(false),
                                

                            ]),
                        Fieldset::make('Valores')
                            ->schema([

                                Forms\Components\TextInput::make('qtd_diarias')
                                    ->extraInputAttributes(['tabindex' => 1, 'style' => 'font-weight: bolder; font-size: 1rem; color: #CF9A16;'])
                                    ->label('Qtd Diárias')
                                    ->readOnly()
                                    ->required(false),
                                Forms\Components\TextInput::make('valor_total')
                                    ->extraInputAttributes(['tabindex' => 1, 'style' => 'font-weight: bolder; font-size: 1rem; color: #D33644;'])
                                    ->label('Valor Total')
                                    ->prefix('R$')
                                    ->inputMode('decimal')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                    ->readOnly()
                                    ->required(false),
                                Forms\Components\TextInput::make('valor_desconto')
                                    ->extraInputAttributes(['tabindex' => 1, 'style' => 'font-weight: bolder; font-size: 1rem; color: #3668D3;'])
                                    ->label('Desconto')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->inputMode('decimal')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                    ->required(true)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                        $valorRestante =  ((float)$get('valor_total') - ((float)$get('valor_pago') + (float)$state));
                                        $set('valor_restante', $valorRestante);
                                    }),
                                Forms\Components\TextInput::make('valor_pago')
                                    ->extraInputAttributes(['tabindex' => 1, 'style' => 'font-weight: bolder; font-size: 1rem; color: #D33644;'])
                                    ->label('Valor Pago')
                                    ->prefix('R$')
                                    ->inputMode('decimal')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                        $valorRestante =  ((float)$get('valor_total') - ((float)$get('valor_desconto') + (float)$state));
                                        $set('valor_restante', $valorRestante);
                                    })
                                    ->required(false),
                                Forms\Components\TextInput::make('valor_restante')
                                    ->extraInputAttributes(['tabindex' => 1, 'style' => 'font-weight: bolder; font-size: 1rem; color: #17863E;'])
                                    ->label('Valor Restante')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                    ->numeric()
                                    ->prefix('R$')
                                    ->inputMode('decimal')
                                    ->readOnly()
                                    ->required(false),
                                Forms\Components\Textarea::make('obs')
                                    ->autosize()
                                    ->columnSpanFull()
                                    ->label('Observações'),
                                ToggleButtons::make('status')
                                    ->label('Finalizar')
                                    ->default(false)
                                    ->boolean()
                                    ->grouped()

                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cliente.nome')
                    ->sortable()
                    ->searchable()
                    ->label('Cliente'),
                Tables\Columns\TextColumn::make('veiculo.modelo')
                    ->sortable()
                    ->searchable()
                    ->label('Veículo'),
                Tables\Columns\TextColumn::make('veiculo.placa')
                    ->searchable()
                    ->label('Placa'),
                Tables\Columns\TextColumn::make('data_saida')
                    ->label('Data Saída')
                    ->date(),
                Tables\Columns\TextColumn::make('hora_saida')
                    ->sortable()
                    ->label('Hora Saída'),
                Tables\Columns\TextColumn::make('data_retorno')
                    ->label('Data Retorno')
                    ->date(),
                Tables\Columns\TextColumn::make('hora_retorno')
                    ->label('Hora Retorno'),
                Tables\Columns\TextColumn::make('qtd_diarias')
                    ->label('Qtd Diárias'),
                Tables\Columns\TextColumn::make('valor_total')
                    ->label('Valor Total')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('valor_desconto')
                    ->label('Valor Desconto')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('valor_pago')
                    ->label('Valor Pago')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('valor_restante')
                    ->label('Valor Restante')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('obs')
                    ->label('Observações'),
                Tables\Columns\TextColumn::make('status')
                    // ->summarize(Count::make())
                    ->Label('Status')
                    ->badge()
                    ->alignCenter()
                    ->color(fn(string $state): string => match ($state) {
                        '0' => 'danger',
                        '1' => 'success',
                    })
                    ->formatStateUsing(function ($state) {
                        if ($state == 0) {
                            return 'Agendado';
                        }
                        if ($state == 1) {
                            return 'Finalizado';
                        }
                    }),
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
                Filter::make('Agendados')
                    ->query(fn(Builder $query): Builder => $query->where('status', false))
                    ->default(1),
                SelectFilter::make('cliente')->searchable()->relationship('cliente', 'nome'),
                SelectFilter::make('veiculo')->searchable()->relationship('veiculo', 'placa'),
                Tables\Filters\Filter::make('datas')
                    ->form([
                        DatePicker::make('data_saida_de')
                            ->label('Saída de:'),
                        DatePicker::make('data_saida_ate')
                            ->label('Saída ate:'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['data_saida_de'],
                                fn($query) => $query->whereDate('data_saida', '>=', $data['data_saida_de'])
                            )
                            ->when(
                                $data['data_saida_ate'],
                                fn($query) => $query->whereDate('data_saida', '<=', $data['data_saida_ate'])
                            );
                    })
            ])
            ->actions([
                // Tables\Actions\Action::make('Imprimir')
                //     ->url(fn(Agendamento $record): string => route('imprimirAgendamento', $record))
                //     ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAgendamentos::route('/'),
        ];
    }
}
