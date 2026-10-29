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
                ->label('Novo')
                ->modalHeading('Criar Locação')
                ->after(function ($data, $record) {
                    // Executa todas as alterações em uma transação para consistência e desempenho
                    DB::transaction(function () use ($data, $record) {
                        // Atualiza status do veículo (uma única query)
                        if (!empty($data['veiculo_id'])) {
                            $veiculo = Veiculo::find($data['veiculo_id']);
                            if ($veiculo && $veiculo->status_locado != 1) {
                                $veiculo->status_locado = 1;
                                $veiculo->save();
                            }
                        }

                        // Se houver movimentação financeira associada
                        if ($record->status_financeiro) {
                            $clienteId = $data['cliente_id'] ?? $record->cliente_id ?? null;
                            $valorTotal = (float) ($record->valor_total_financeiro ?? $data['valor_total_financeiro'] ?? 0);
                            $parcelas = (int) ($data['parcelas_financeiro'] ?? $record->parcelas_financeiro ?? 1);
                            $formaPgmto = $data['forma_pgmto_id'] ?? $record->forma_pgmto_id ?? null;
                            $dataBase = Carbon::create($record->data_vencimento_financeiro ?? $data['data_vencimento_financeiro'] ?? now());
                            $diaOriginal = $dataBase->day;

                            $valorParcela = $parcelas > 0 ? $valorTotal / $parcelas : $valorTotal;

                            // Não pago: gera várias parcelas (insere em lote)
                            if (!$record->status_pago_financeiro) {
                                $rows = [];

                                for ($i = 0; $i < max(1, $parcelas); $i++) {
                                    $dataVencimentos = match ((string) $data['proxima_parcela']) {
                                        '7'  => $dataBase->copy()->addWeeks($i),
                                        '15' => $dataBase->copy()->addWeeks($i * 2),
                                        '30' => tap(
                                            $dataBase->copy()->startOfMonth()->addMonths($i),
                                            fn($d) => $d->day(min($diaOriginal, $d->daysInMonth))
                                        ),
                                        default => $dataBase->copy()->addDays($i * $data['proxima_parcela']),
                                    };

                                    $rows[] = [
                                        'cliente_id' => $clienteId,
                                        'valor_total' => $valorTotal,
                                        'parcelas' => $parcelas,
                                        'forma_pgmto_id' => $formaPgmto,
                                        'ordem_parcela' => $i + 1,
                                        'data_vencimento' => $dataVencimentos,
                                        'valor_recebido' => 0.00,
                                        'status' => 0,
                                        'obs' => 'Parcela referente a locação nº: ' . $record->id,
                                        'valor_parcela' => $valorParcela,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ];
                                }

                                if (!empty($rows)) {
                                    ContasReceber::insert($rows);
                                }
                            } else {
                                // Pago: cria uma única parcela e registra no fluxo de caixa
                                $conta = [
                                    'cliente_id' => $clienteId,
                                    'valor_total' => $valorTotal,
                                    'parcelas' => 1,
                                    'forma_pgmto_id' => $formaPgmto,
                                    'ordem_parcela' => 1,
                                    'data_vencimento' => $dataBase,
                                    'data_recebimento' => $dataBase,
                                    'valor_recebido' => $valorTotal,
                                    'status' => 1,
                                    'obs' => 'Recebimento referente da locação nº: ' . $record->id,
                                    'valor_parcela' => $valorTotal,
                                ];
                                ContasReceber::create($conta);

                                $clienteNome = null;
                                if ($clienteId) {
                                    $cliente = Cliente::select('nome')->find($clienteId);
                                    $clienteNome = $cliente ? $cliente->nome : null;
                                }

                                FluxoCaixa::create([
                                    'valor' => $valorTotal,
                                    'tipo'  => 'CREDITO',
                                    'obs'   => 'Recebimento da conta do cliente ' . ($clienteNome ?? ''),
                                ]);
                            }
                        }
                    });
                }),
        ];
    }
}
