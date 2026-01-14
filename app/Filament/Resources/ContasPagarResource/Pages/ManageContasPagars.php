<?php

namespace App\Filament\Resources\ContasPagarResource\Pages;

use App\Filament\Resources\ContasPagarResource;
use App\Models\ContasPagar;
use App\Models\FluxoCaixa;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\DB;

class ManageContasPagars extends ManageRecords
{
    protected static string $resource = ContasPagarResource::class;
    protected static ?string $title = 'Pagamentos';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus')
                ->modalHeading('Criar Pagamento')
                ->after(function ($data, $record) {
                    // Use transação para garantir consistência
                    DB::transaction(function () use ($data, $record) {
                        if ($record->parcelas > 1) {
                            $this->criarParcelas($data, $record);
                        } else {
                            $this->processarPagamentoUnico($record);
                        }
                    });
                }),
        ];
    }

    /**
     * Cria parcelas adicionais de forma otimizada
     */
    private function criarParcelas(array $data, ContasPagar $record): void
    {
        $valorParcela = $record->valor_total / $record->parcelas;
        $vencimentoBase = Carbon::parse($record->data_vencimento);
        $proximaParcela = $data['proxima_parcela'] ?? 30;
        
        $parcelasParaCriar = [];
        
        for ($cont = 1; $cont < $data['parcelas']; $cont++) {
            $dataVencimento = $vencimentoBase->copy()->addDays($proximaParcela * $cont);
            
            $parcelasParaCriar[] = [
                'fornecedor_id' => $record->fornecedor_id,
                'valor_total' => $record->valor_total,
                'categoria_id' => $record->categoria_id,
                'parcelas' => $record->parcelas,
                'forma_pgmto_id' => $record->forma_pgmto_id,
                'ordem_parcela' => $cont + 1,
                'data_vencimento' => $dataVencimento,
                'valor_pago' => 0.00,
                'status' => false,
                'obs' => $record->obs,
                'valor_parcela' => $valorParcela,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Inserir todas as parcelas de uma vez (mais eficiente)
        if (!empty($parcelasParaCriar)) {
            ContasPagar::insert($parcelasParaCriar);
        }
    }

    /**
     * Processa pagamento único com registro no fluxo de caixa
     */
    private function processarPagamentoUnico(ContasPagar $record): void
    {
        if ($record->status) {
            FluxoCaixa::create([
                'valor' => $record->valor_total * -1,
                'contas_pagar_id' => $record->id,
                'tipo'  => 'DEBITO',
                'obs' => 'Pagamento da conta do fornecedor ' . $record->fornecedor->nome . ' - Forma de Pagamento: ' . ($record->formaPgmto ? $record->formaPgmto->nome : 'N/A'),            ]);
        }
    }

   
}