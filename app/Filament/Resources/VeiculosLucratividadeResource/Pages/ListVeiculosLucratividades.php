<?php

namespace App\Filament\Resources\VeiculosLucratividadeResource\Pages;

use App\Filament\Resources\VeiculosLucratividadeResource;
use App\Filament\Widgets\TotalLucratividade;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ListVeiculosLucratividades extends ListRecords
{
    protected static string $resource = VeiculosLucratividadeResource::class;

    protected static ?string $title = 'Lucratividade dos Veículos';


    protected function getHeaderActions(): array
    {
        return [
          //  Actions\CreateAction::make(),
           

        ];
    }

    protected function getHeaderWidgets(): array
    {

        return [
           TotalLucratividade::class

        ];
    }
}
