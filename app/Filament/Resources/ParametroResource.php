<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParametroResource\Pages;
use App\Filament\Resources\ParametroResource\RelationManagers;
use App\Models\Parametro;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ParametroResource extends Resource
{
    protected static ?string $model = Parametro::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Parâmetros';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações da Empresa')
                    ->schema([
                        Forms\Components\TextInput::make('nome_empresa')
                            ->label('Nome da Empresa')
                            ->required(false)
                            ->maxLength(255)
                            ->columnSpan('full'),
                        Forms\Components\TextInput::make('cpf_cnpj')
                            ->label('CPF/CNPJ')
                            ->required(false)
                            ->maxLength(255)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->required(false)
                            ->email()
                            ->maxLength(255)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('telefone')
                            ->label('Telefone')
                            ->required(false)
                            ->maxLength(255)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('redes_sociais')
                            ->label('Redes Sociais')
                            ->maxLength(255)
                            ->nullable()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('endereco_completo')
                            ->label('Endereço Completo')
                            ->required(false)
                            ->maxLength(255)
                            ->columnSpan('full'),
                    ])->columns(2),
                Forms\Components\Section::make('Logo e Mídia')
                    ->schema([
                        Forms\Components\FileUpload::make('logo')
                            ->label('Logo da Empresa')
                            ->image()
                            ->maxSize(1024)
                            ->nullable()
                            ->columnSpan('full'),
                    ]),
                Forms\Components\Section::make('Configurações do Sistema')
                    ->visible(fn (): bool => auth()->user()->hasRole('TI'))
                    ->schema([
                        Forms\Components\TextInput::make('versao_sistema')
                            ->label('Versão do Sistema')
                            ->required(false)
                            ->maxLength(50)
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('data_atualizacao')
                            ->label('Data de Atualização')
                            ->nullable()
                            ->columnSpan(1),
                        Forms\Components\Textarea::make('detalhe_atualizacao')
                            ->label('Detalhes da Atualização')
                            ->nullable()
                            ->columnSpan('full'),                        
                        
                        Forms\Components\Toggle::make('ativar_notificacoes')
                            ->label('Ativar Notificações de Contas a Pagar, Receber e Manutenções')
                            ->default(false)
                            ->columnSpan(1),
                        ]),
                    Forms\Components\Section::make('Notificações para o Usuário')
                        ->visible(fn (): bool => auth()->user()->hasRole('TI'))
                        ->schema([
                            Forms\Components\Toggle::make('ativar_notificacao_usuario')
                                ->label('Ativar Notificações ao Usuário')
                                ->default(true)
                                ->columnSpan(1),
                            Forms\Components\Textarea::make('notificar_usuario')
                                ->label('Mensagem de Notificação ao Usuário')
                                ->autosize()
                                ->nullable()
                                ->columnSpan('full'),
                        ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome_empresa')->label('Nome da Empresa')->limit(30),
                Tables\Columns\TextColumn::make('cpf_cnpj')->label('CPF/CNPJ'),
                Tables\Columns\TextColumn::make('email')->label('Email')->limit(30),
                Tables\Columns\TextColumn::make('telefone')->label('Telefone'),
                
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Parametro $record): bool => auth()->user()->hasRole('TI')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
             //       Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageParametros::route('/'),
        ];
    }
}
