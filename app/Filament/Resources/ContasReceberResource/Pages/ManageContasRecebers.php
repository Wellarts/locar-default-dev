<?php

namespace App\Filament\Resources\ContasReceberResource\Pages;

use App\Filament\Resources\ContasReceberResource;
use App\Models\ContasReceber;
use App\Models\FluxoCaixa;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\DB;

class ManageContasRecebers extends ManageRecords
{
    protected static string $resource = ContasReceberResource::class;
    protected static ?string $title = 'Recebimentos';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar')
                ->icon('heroicon-o-plus')
                ->modalHeading('Criar Recebimento')
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
    private function criarParcelas(array $data, ContasReceber $record): void
    {
        $valorParcela = $record->valor_total / $record->parcelas;
        $vencimentoBase = Carbon::parse($record->data_vencimento);
        $proximaParcela = $data['proxima_parcela'] ?? 30;
        
        $parcelasParaCriar = [];
        
        for ($cont = 1; $cont < $data['parcelas']; $cont++) {
            $dataVencimento = $vencimentoBase->copy()->addDays($proximaParcela * $cont);
            
            $parcelasParaCriar[] = [
                'cliente_id' => $record->cliente_id,
                'valor_total' => $record->valor_total,
                'categoria_id' => $record->categoria_id,
                'parcelas' => $record->parcelas,
                'forma_pgmto_id' => $record->forma_pgmto_id,
                'ordem_parcela' => $cont + 1,
                'data_vencimento' => $dataVencimento,
                'valor_recebido' => 0.00,
                'status' => false,
                'obs' => $record->obs,
                'valor_parcela' => $valorParcela,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Inserir todas as parcelas de uma vez (mais eficiente)
        if (!empty($parcelasParaCriar)) {
            ContasReceber::insert($parcelasParaCriar);
        }
    }

    /**
     * Processa pagamento único com registro no fluxo de caixa
     */
    private function processarPagamentoUnico(ContasReceber $record): void
    {
        if ($record->status) {
            FluxoCaixa::create([
                'valor' => $record->valor_total,
                'contas_receber_id' => $record->id,
                'tipo'  => 'CREDITO',
                'obs'   => 'Recebimento da conta do cliente ' . $record->cliente->nome. ' - Forma de Pagamento: ' . $record->formaPgmto->nome,
            ]);
        }
    }
}