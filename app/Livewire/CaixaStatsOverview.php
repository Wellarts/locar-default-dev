<?php

namespace App\Livewire;

use App\Models\FluxoCaixa;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CaixaStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Consulta única eficiente para obter todos os valores necessários
        $resultado = FluxoCaixa::selectRaw('
            SUM(valor) as saldo,
            SUM(CASE WHEN valor < 0 THEN valor ELSE 0 END) as debitos,
            SUM(CASE WHEN valor > 0 THEN valor ELSE 0 END) as creditos
        ')->first();

        $saldo = $resultado->saldo ?? 0;
        $debitos = $resultado->debitos ?? 0;
        $creditos = $resultado->creditos ?? 0;

        // Formatação condicional para melhor visualização
        $formatDescription = function($value, $label, $isCurrency = true) {
            $formattedValue = $isCurrency 
                ? 'R$ ' . number_format(abs($value), 2, ',', '.')
                : number_format(abs($value), 0, ',', '.');
            
            if ($value > 0) {
                return "{$label} ({$formattedValue})";
            } elseif ($value < 0) {
                return "{$label} (-{$formattedValue})";
            }
            return 'Sem ' . strtolower($label);
        };

        // Determinar cores baseadas nos valores
        $saldoColor = $saldo > 0 ? 'success' : ($saldo < 0 ? 'danger' : 'gray');
        $debitosColor = $debitos < 0 ? 'danger' : 'gray';
        $creditosColor = $creditos > 0 ? 'success' : 'gray';

        // Ícones condicionais
        $saldoIcon = $saldo > 0 ? 'heroicon-m-arrow-trending-up' : 
                    ($saldo < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-minus-circle');
        
        $debitosIcon = $debitos < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-minus-circle';
        $creditosIcon = $creditos > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-plus-circle';

        return [
            Stat::make('Saldo', 'R$ ' . number_format($saldo, 2, ',', '.'))
                ->description($formatDescription($saldo, 'Saldo atual'))
                ->descriptionIcon($saldoIcon)
                ->color($saldoColor)
                ->chart($this->getChartData('saldo', $saldo))
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-md transition-shadow duration-200',
                ]),
            
            Stat::make('Débitos', 'R$ ' . number_format(abs($debitos), 2, ',', '.'))
                ->description($formatDescription($debitos, 'Total de débitos'))
                ->descriptionIcon($debitosIcon)
                ->color($debitosColor)
                ->chart($this->getChartData('debitos', $debitos))
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-md transition-shadow duration-200',
                ]),
            
            Stat::make('Créditos', 'R$ ' . number_format($creditos, 2, ',', '.'))
                ->description($formatDescription($creditos, 'Total de créditos'))
                ->descriptionIcon($creditosIcon)
                ->color($creditosColor)
                ->chart($this->getChartData('creditos', $creditos))
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-md transition-shadow duration-200',
                ]),
        ];
    }

    /**
     * Gera dados de gráfico simples baseado no valor atual
     */
    private function getChartData(string $tipo, float $valor): array
    {
        // Para valores zero, retornar array de zeros
        if ($valor === 0.0) {
            return [0, 0, 0, 0, 0, 0, 0, 0];
        }

        $absValor = abs($valor);
        $baseValue = max(1, min($absValor / 100, 100)); // Ajusta a escala para gráficos
        
        return match($tipo) {
            'saldo' => $valor > 0 
                ? [$baseValue, $baseValue * 1.1, $baseValue * 0.9, $baseValue * 1.2, $baseValue * 0.8, $baseValue * 1.1, $baseValue, $baseValue * 1.05]
                : [$baseValue, $baseValue * 0.9, $baseValue * 1.1, $baseValue * 0.8, $baseValue * 1.2, $baseValue * 0.9, $baseValue, $baseValue * 0.95],
            'debitos' => [$baseValue, $baseValue * 1.2, $baseValue * 0.8, $baseValue * 1.3, $baseValue * 0.7, $baseValue * 1.1, $baseValue, $baseValue * 1.15],
            'creditos' => [$baseValue, $baseValue * 1.05, $baseValue * 0.95, $baseValue * 1.1, $baseValue * 0.9, $baseValue, $baseValue * 0.95, $baseValue * 1.02],
            default => array_fill(0, 8, $baseValue),
        };
    }

    /**
     * Override do método para adicionar polling
     */
    protected function getPollingInterval(): ?string
    {
        // Atualiza a cada 60 segundos para dados mais recentes
        return '10s';
    }
}