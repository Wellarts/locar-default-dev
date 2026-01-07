<?php

namespace App\Filament\Resources\LocacaoResource\Pages;

use App\Filament\Resources\ContasReceberResource;
use App\Filament\Resources\LocacaoResource;
use App\Models\Cliente;
use App\Models\ContasReceber;
use App\Models\FluxoCaixa;
use App\Models\Locacao;
use App\Models\Veiculo;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ManageLocacaos extends ManageRecords
{
    protected static string $resource = LocacaoResource::class;
    protected static ?string $title = 'Locações';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar Locação')
                ->icon('heroicon-o-plus')
                ->modalHeading('Criar Locação')                
                ->after(function ($data, $record) {
                    // 1. Atualização do status do veículo em uma única query
                    Veiculo::where('id', $data['veiculo_id'])
                        ->update(['status_locado' => 1]);
                    
                    // 2. Processamento financeiro otimizado
                    if ($record->status_financeiro == true) {
                        if ($record->status_pago_financeiro == false) {
                            $this->criarParcelasFinanceiras($record, $data);
                        } else {
                            $this->criarPagamentoAVista($record, $data);
                        }
                    }
                }),
        ];
    }

    /**
     * Cria parcelas financeiras em lote para melhor performance
     */
    protected function criarParcelasFinanceiras(Locacao $record, array $data): void
    {
        $valor_parcela = ($record->valor_total_financeiro / $record->parcelas_financeiro);
        $vencimentos = Carbon::create($record->data_vencimento_financeiro);
        $parcelas = [];
        
        // Prepara todas as parcelas em um array para inserção em lote
        for ($cont = 0; $cont < $data['parcelas_financeiro']; $cont++) {
            $parcelas[] = [
                'cliente_id' => $data['cliente_id'],
                'locacao_id' => $record->id,
                'valor_total' => $data['valor_total_financeiro'],
                'parcelas' => $data['parcelas_financeiro'],
                'forma_pgmto_id' => $data['forma_pgmto_id'],
                'ordem_parcela' => $cont + 1,
                'data_vencimento' => $vencimentos->copy(),
                'valor_recebido' => 0.00,
                'status' => 0,
                'obs' => 'Parcela referente a locação nº: ' . $record->id,
                'valor_parcela' => $valor_parcela,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $vencimentos->addDays($data['proxima_parcela']);
        }
        
        // Inserção em lote - muito mais rápido que múltiplas inserções individuais
        ContasReceber::insert($parcelas);
    }

    /**
     * Cria pagamento à vista com transação para garantir integridade
     */
    protected function criarPagamentoAVista(Locacao $record, array $data): void
    {
        DB::transaction(function () use ($record, $data) {
            // 1. Cria a conta a receber
            $contaReceber = ContasReceber::create([
                'cliente_id' => $data['cliente_id'],
                'locacao_id' => $record->id,
                'valor_total' => $data['valor_total_financeiro'],
                'parcelas' => $data['parcelas_financeiro'],
                'forma_pgmto_id' => $data['forma_pgmto_id'],
                'ordem_parcela' => 1,
                'data_vencimento' => $data['data_vencimento_financeiro'],
                'data_recebimento' => $data['data_vencimento_financeiro'],
                'valor_recebido' => $data['valor_total_financeiro'],
                'status' => 1,
                'obs' => 'Recebimento referente da locação nº: ' . $record->id,
                'valor_parcela' => $data['valor_total_financeiro'],
            ]);
            
            // 2. Busca cliente uma única vez (eager loading se já tiver sido carregado)
            $clienteNome = Cliente::where('id', $data['cliente_id'])
                ->value('nome') ?? $record->cliente->nome ?? 'Cliente';
            
            // 3. Cria registro no fluxo de caixa
            FluxoCaixa::create([
                'valor' => $data['valor_total_financeiro'],
                'locacao_id' => $record->id,
                'tipo' => 'CREDITO',
                'obs' => 'Recebimento da conta do cliente ' . $clienteNome . ' - Forma de Pagamento: ' . ($record->formaPgmto->nome ?? 'N/A'),
            ]);
        });
    }
}