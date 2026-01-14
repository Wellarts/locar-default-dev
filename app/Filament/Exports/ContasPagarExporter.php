<?php

namespace App\Filament\Exports;

use App\Models\ContasPagar;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Support\RawJs;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;


class ContasPagarExporter extends Exporter
{
    protected static ?string $model = ContasPagar::class;

    public function getXlsxHeaderCellStyle(): ?Style
    {
        return (new Style())
            ->setFontBold()
            // ->setFontItalic()
            ->setFontSize(14)
            ->setFontName('Arial')
            ->setFontColor(Color::rgb(9, 13, 10))
            ->setBackgroundColor(Color::rgb(230, 230, 235))
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER);
    }

    public function getXlsxCellStyle(): ?Style
    {
        return (new Style())
            ->setFontSize(12)
            ->setFontName('Arial');
            
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('fornecedor.nome')
                ->label('Fornecedor'),
            // ExportColumn::make('parcelas')
            //     ->label('Parcelas'),
            ExportColumn::make('ordem_parcela')
                ->label('Parcela Nº'),
            ExportColumn::make('formaPgmto.nome')
                
                ->label('Forma de Pagamento'),
            ExportColumn::make('data_vencimento')
                ->formatStateUsing(fn($state) => Carbon::parse($state)->format('d/m/Y'))
                ->label('Vencimento'),
            ExportColumn::make('data_pagamento')
                ->formatStateUsing(fn($state) => Carbon::parse($state)->format('d/m/Y'))
                ->label('Pagamento'),
            ExportColumn::make('status')
                ->formatStateUsing(function ($state) {
                    if ($state == 1) {
                        return 'Sim';
                    } elseif ($state == 0) {
                        return 'Não';
                    }
                })
                ->label('Pago'),
            ExportColumn::make('valor_total')
                ->formatStateUsing(function ($state) {
                    return str_replace('.', ',', $state);
                })
                ->label('Valor Total'),
            ExportColumn::make('valor_parcela')
                ->formatStateUsing(function ($state) {
                    return str_replace('.', ',', $state);
                })
                ->label('Valor Parcela'),
            ExportColumn::make('valor_pago')
                 ->formatStateUsing(function ($state) {
                     return str_replace('.', ',', $state);
                })
                ->label('Valor Pago'),
            ExportColumn::make('obs')
                ->label('Observações'),
            ExportColumn::make('created_at')
                ->label('Criado'),
            ExportColumn::make('Atualizado'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your contas pagar export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
