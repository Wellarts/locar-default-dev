<?php

namespace App\Http\Controllers;

use App\Models\Locacao;
use App\Models\ContasPagar;
use App\Models\Fornecedor;
use App\Models\Categoria;
use App\Models\FormaPagamento;
use App\Models\Cliente;
use App\Models\ContasReceber;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\OrdemServico;
use Illuminate\Support\Facades\DB;

class DocumentoController extends Controller
{
    public function ordemServico($id)
    {
        $ordemServico = OrdemServico::findOrFail($id);
        $pdf = PDF::loadView('pdf.ordemServico.ordemServico', compact('ordemServico'));
        return $pdf->stream('ordem_servico.pdf');
    }

    public function ordemServicoRelatorio(Request $request)
    {
        $query = OrdemServico::query();
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }
        if ($request->filled('veiculo_id')) {
            $query->where('veiculo_id', $request->veiculo_id);
        }
        if ($request->filled('fornecedor_id')) {
            $query->where('fornecedor_id', $request->fornecedor_id);
        }
        if ($request->filled('forma_pagamento_id')) {
            $query->where('forma_pagamento_id', $request->forma_pagamento_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('data_inicio')) {
            $query->whereDate('data_emissao', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('data_emissao', '<=', $request->data_fim);
        }
        $ordemServicoRelatorio = $query->get();
        $pdf = PDF::loadView('pdf.ordemServico.relatorio', compact('ordemServicoRelatorio'))
            ->setPaper('a4', 'landscape');
        return $pdf->stream('ordem_servico_relatorio.pdf');
    }

    public function locacoesRelatorio(Request $request)
    {
        $query = Locacao::query();
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }
        if ($request->filled('veiculo_id')) {
            $query->where('veiculo_id', $request->veiculo_id);
        }
        if ($request->filled('forma_pgmto_id')) {
            $query->where('forma_pgmto_id', $request->forma_pgmto_id);
        }
        if ($request->filled('data_saida')) {
            $query->whereDate('data_saida', '>=', $request->data_saida);
        }
        if ($request->filled('data_retorno')) {
            $query->whereDate('data_retorno', '<=', $request->data_retorno);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $locacoes = $query->get();
        $pdf = PDF::loadView('pdf.locacao.relatorio', compact('locacoes'))
            ->setPaper('a4', 'landscape');
        return $pdf->stream('locacoes_relatorio.pdf');
    }

    public function contasPagarRelatorio(Request $request)
    {
        $query = ContasPagar::query()->with(['fornecedor', 'categoria', 'formaPgmto']);

        if ($request->filled('fornecedor_id')) {
            $query->where('fornecedor_id', $request->fornecedor_id);
        }
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }
        if ($request->filled('forma_pgmto_id')) {
            $query->where('forma_pgmto_id', $request->forma_pgmto_id);
        }
        if ($request->filled('status')) {
            // esperar valores '1' ou '0' ou 'paid'/'unpaid'
            $status = $request->status;
            if (in_array($status, ['1', '0'])) {
                $query->where('status', (bool) $status);
            }
        }
        if ($request->filled('data_vencimento_inicio')) {
            $query->whereDate('data_vencimento', '>=', $request->data_vencimento_inicio);
        }
        if ($request->filled('data_vencimento_fim')) {
            $query->whereDate('data_vencimento', '<=', $request->data_vencimento_fim);
        }
        if ($request->filled('data_pagamento_inicio')) {
            $query->whereDate('data_pagamento', '>=', $request->data_pagamento_inicio);
        }
        if ($request->filled('data_pagamento_fim')) {
            $query->whereDate('data_pagamento', '<=', $request->data_pagamento_fim);
        }

        $contas = $query->orderBy('data_vencimento', 'asc')->get();

        // Monta lista de filtros legíveis para exibir na view
        $filtrosNomes = [];
        if ($request->filled('fornecedor_id')) {
            $f = Fornecedor::find($request->fornecedor_id);
            $filtrosNomes['Fornecedor'] = $f->nome ?? $request->fornecedor_id;
        }
        if ($request->filled('categoria_id')) {
            $c = Categoria::find($request->categoria_id);
            $filtrosNomes['Categoria'] = $c->nome ?? $request->categoria_id;
        }
        if ($request->filled('forma_pgmto_id')) {
            $fp = FormaPagamento::find($request->forma_pgmto_id);
            $filtrosNomes['Forma Pagamento'] = $fp->nome ?? $request->forma_pgmto_id;
        }
        if ($request->filled('status')) {
            $st = $request->status;
            if ($st === '1' || $st === 1 || $st === true) {
                $filtrosNomes['Pago'] = 'Sim';
            } elseif ($st === '0' || $st === 0 || $st === false) {
                $filtrosNomes['Pago'] = 'Não';
            }
        }

        if ($request->filled('data_vencimento_inicio')) {
            $filtrosNomes['Vencimento (Início)'] = Carbon::parse($request->data_vencimento_inicio)->format('d/m/Y');
        }
        if ($request->filled('data_vencimento_fim')) {
            $filtrosNomes['Vencimento (Fim)'] = Carbon::parse($request->data_vencimento_fim)->format('d/m/Y');
        }
        if ($request->filled('data_pagamento_inicio')) {
            $filtrosNomes['Pagamento (Início)'] = Carbon::parse($request->data_pagamento_inicio)->format('d/m/Y');
        }
        if ($request->filled('data_pagamento_fim')) {
            $filtrosNomes['Pagamento (Fim)'] = Carbon::parse($request->data_pagamento_fim)->format('d/m/Y');
        }

        $pdf = PDF::loadView('pdf.contasPagar.relatorio', compact('contas', 'filtrosNomes'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('contas_pagar_relatorio.pdf');
    }

    public function contasReceberRelatorio(Request $request)
    {
        $query = ContasReceber::query()->with(['cliente', 'categoria', 'formaPgmto', 'locacao']);

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }
        if ($request->filled('forma_pgmto_id')) {
            $query->where('forma_pgmto_id', $request->forma_pgmto_id);
        }
        if ($request->filled('status')) {
            $status = $request->status;
            if (in_array($status, ['1', '0'])) {
                $query->where('status', (bool) $status);
            }
        }
        if ($request->filled('data_vencimento_inicio')) {
            $query->whereDate('data_vencimento', '>=', $request->data_vencimento_inicio);
        }
        if ($request->filled('data_vencimento_fim')) {
            $query->whereDate('data_vencimento', '<=', $request->data_vencimento_fim);
        }
        if ($request->filled('data_recebimento_inicio')) {
            $query->whereDate('data_recebimento', '>=', $request->data_recebimento_inicio);
        }
        if ($request->filled('data_recebimento_fim')) {
            $query->whereDate('data_recebimento', '<=', $request->data_recebimento_fim);
        }

        $contas = $query->orderBy('data_vencimento', 'asc')->get();

        $filtrosNomes = [];
        if ($request->filled('cliente_id')) {
            $c = Cliente::find($request->cliente_id);
            $filtrosNomes['Cliente'] = $c->nome ?? $request->cliente_id;
        }
        if ($request->filled('categoria_id')) {
            $c = Categoria::find($request->categoria_id);
            $filtrosNomes['Categoria'] = $c->nome ?? $request->categoria_id;
        }
        if ($request->filled('forma_pgmto_id')) {
            $fp = FormaPagamento::find($request->forma_pgmto_id);
            $filtrosNomes['Forma Pagamento'] = $fp->nome ?? $request->forma_pgmto_id;
        }
        if ($request->filled('status')) {
            $st = $request->status;
            if ($st === '1' || $st === 1 || $st === true) {
                $filtrosNomes['Recebido'] = 'Sim';
            } elseif ($st === '0' || $st === 0 || $st === false) {
                $filtrosNomes['Recebido'] = 'Não';
            }
        }
        if ($request->filled('data_vencimento_inicio')) {
            $filtrosNomes['Vencimento (Início)'] = Carbon::parse($request->data_vencimento_inicio)->format('d/m/Y');
        }
        if ($request->filled('data_vencimento_fim')) {
            $filtrosNomes['Vencimento (Fim)'] = Carbon::parse($request->data_vencimento_fim)->format('d/m/Y');
        }
        if ($request->filled('data_recebimento_inicio')) {
            $filtrosNomes['Recebimento (Início)'] = Carbon::parse($request->data_recebimento_inicio)->format('d/m/Y');
        }
        if ($request->filled('data_recebimento_fim')) {
            $filtrosNomes['Recebimento (Fim)'] = Carbon::parse($request->data_recebimento_fim)->format('d/m/Y');
        }

        $pdf = PDF::loadView('pdf.contasReceber.relatorio', compact('contas', 'filtrosNomes'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('contas_receber_relatorio.pdf');
    }

    public function launchContasPagarRelatorio(Request $request)
    {
        $params = $request->all();
        $url = route('imprimirContasPagarRelatorio') . (count($params) ? ('?' . http_build_query($params)) : '');
        return view('pdf.launch', ['url' => $url]);
    }

    public function launchContasReceberRelatorio(Request $request)
    {
        $params = $request->all();
        $url = route('imprimirContasReceberRelatorio') . (count($params) ? ('?' . http_build_query($params)) : '');
        return view('pdf.launch', ['url' => $url]);
    }

    public function veiculosLucratividade()
    {
        $veiculos = DB::table('veiculos')
            ->leftJoinSub(
                DB::table('locacaos')
                    ->select('veiculo_id', DB::raw('SUM(valor_total_desconto) as total_locacoes'))
                    ->groupBy('veiculo_id'),
                'locacoes_agg',
                'veiculos.id',
                '=',
                'locacoes_agg.veiculo_id'
            )
            ->leftJoinSub(
                DB::table('custo_veiculos')
                    ->select('veiculo_id', DB::raw('SUM(valor) as total_custos'))
                    ->groupBy('veiculo_id'),
                'custos_agg',
                'veiculos.id',
                '=',
                'custos_agg.veiculo_id'
            )
            ->select(
                'veiculos.id',
                'veiculos.modelo',
                'veiculos.placa',
                DB::raw('COALESCE(locacoes_agg.total_locacoes, 0) as total_locacoes'),
                DB::raw('COALESCE(custos_agg.total_custos, 0) as total_custos')
            )
            ->get()
            ->map(function ($veiculo) {
                $veiculo->total_locacoes = $veiculo->total_locacoes ?? 0;
                $veiculo->total_custos = $veiculo->total_custos ?? 0;
                $veiculo->lucratividade = $veiculo->total_locacoes - $veiculo->total_custos;
                return $veiculo;
            });
        $pdf = PDF::loadView('pdf.veiculos.lucratividade', compact('veiculos'))
            ->setPaper('a4', 'landscape');
        return $pdf->stream('veiculos_lucratividade.pdf');
    }

    public function fluxoCaixa(Request $request)
    {
        $query = DB::table('fluxo_caixas');

        if ($request->filled('data_de')) {
            $query->whereDate('created_at', '>=', $request->data_de);
        }
        if ($request->filled('data_ate')) {
            $query->whereDate('created_at', '<=', $request->data_ate);
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $fluxoCaixa = $query->orderBy('created_at', 'asc')->get();

        $pdf = PDF::loadView('pdf.fluxoCaixa.relatorio', compact('fluxoCaixa'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('fluxo_caixa_relatorio.pdf');
    }
}
