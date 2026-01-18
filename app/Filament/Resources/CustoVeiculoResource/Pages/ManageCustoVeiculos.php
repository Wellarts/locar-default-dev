<?php

namespace App\Filament\Resources\CustoVeiculoResource\Pages;

use App\Filament\Resources\CustoVeiculoResource;
use App\Models\ContasPagar;
use App\Models\Veiculo;
use App\Models\FluxoCaixa;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCustoVeiculos extends ManageRecords
{
    protected static string $resource = CustoVeiculoResource::class;

    protected static ?string $title = 'Despesas/Manutenções';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus')
                ->modalHeading('Criar Nova Despesa/Manutenção')
                ->after(function ($data, $record) {
                    $veiculo = Veiculo::find($data['veiculo_id']);
                    $veiculo->km_atual = $data['km_atual'];
                    $veiculo->save();

                    // Se o usuário pediu para lançar no fluxo de caixa e marcou como pago
                    if (($data['financeiro'] == true) && (($data['pago'] ?? '') === 'pago')) {
                        $valor = $data['valor'];
                        if (is_string($valor)) {
                            $valor = str_replace(['R$', ' ', '.'], ['', '', ''], $valor);
                            $valor = str_replace(',', '.', $valor);
                            $valor = (float) $valor;
                        }

                        FluxoCaixa::create([
                            'valor' => ($valor * -1),
                            'tipo' => 'DEBITO',
                            'despesa_id' => $record->id,
                            'obs' => ('Despesa veículo: ' . ($veiculo->modelo ?? '') . ' - ' . ($veiculo->placa ?? '') . ' - ' . ($data['descricao'] ?? '')),
                        ]);
                    } elseif (($data['financeiro'] == true) && (($data['pago'] ?? '') === 'a_pagar')) {

                        // Lógica para gerar parcelas no conta a pagar
                        $valor = $data['valor'];
                        if (is_string($valor)) {
                            $valor = str_replace(['R$', ' ', '.'], ['', '', ''], $valor);
                            $valor = str_replace(',', '.', $valor);
                            $valor = (float) $valor;
                        }

                        $parcelas = $data['parcelas'] ?? 1;
                        $valorParcela = $valor / $parcelas;
                        $dataVencimento =  now()->addMonth();

                        for ($i = 1; $i <= $parcelas; $i++) {
                            ContasPagar::create([
                                'fornecedor_id' => $data['fornecedor_id'] ?? null,
                                'parcelas' => $parcelas,
                                'despesa_id' => $record->id,
                                'ordem_parcela' => $i,
                                'forma_pgmto_id' => $data['forma_pgmto_id'] ?? null,
                                'data_vencimento' => $dataVencimento->copy()->addMonths($i - 1),
                                'data_pagamento' => (($data['pago'] ?? '') === 'pago') ? ($data['data_pagamento'] ?? $dataVencimento->copy()->addMonths($i - 1)) : null,
                                'status' => (($data['pago'] ?? '') === 'pago'),
                                'valor_total' => $valor,
                                'valor_parcela' => $valorParcela,
                                'valor_pago' => (($data['pago'] ?? '') === 'pago') ? $valorParcela : 0,
                                'obs' => ('Despesa veículo: ' . ($veiculo->modelo ?? '') . ' - ' . ($veiculo->placa ?? '') . ' - ' . ($data['descricao'] ?? '')),
                                'categoria_id' => $data['categoria_id'] ?? null,
                            ]);
                        }
                    }
                })
        ];
    }
}
