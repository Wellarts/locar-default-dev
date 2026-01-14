<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContratoResource\Pages;
use App\Filament\Resources\ContratoResource\RelationManagers;
use App\Models\Contrato;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContratoResource extends Resource
{
    protected static ?string $model = Contrato::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('titulo')
                    ->required()
                    ->columnSpanFull()
                    ->label('Título')
                    ->maxLength(255),
                
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('verVariaveis')
                        ->label('📋 Ver Variáveis Disponíveis')
                        ->color('primary')
                        ->icon('heroicon-o-information-circle')
                        ->url(route('contrato.variaveis'), shouldOpenInNewTab: true)
                ])->columnSpanFull(),
                
                Forms\Components\RichEditor::make('descricao')
                    ->label('Descrição')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('titulo')->label('Título')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Criado em')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Atualizado em')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ManageContratos::route('/'),
        ];
    }
}