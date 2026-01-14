@extends('layouts.pdf')

@section('title', 'Relatório - Contas a Pagar')

@section('content')
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th, td {
        padding: 6px;
        text-align: center;
        font-size: 7pt;
    }

    th {
        background: #f2f2f2;
    }

    h1 {
        text-align: center;
        font-size: 14pt;
        margin-bottom: 6px;
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    .filtros {
        margin-bottom: 18px;
        font-size: 1rem;
        color: #888;
        background: #f4f6f9;
        padding: 8px 12px;
        border-radius: 6px;
    }

    .filtros strong {
        display: block;
        margin-bottom: 4px;
    }

    .filtros span {
        margin-right: 12px;
    }

    .resumo {
        margin-top: 20px;
        display: flex;
        gap: 20px;
    }

    .resumo table {
        margin-top: 0;
    }

    .resumo h4 {
        margin: 6px 0;
        text-align: center;
    }
</style>

<h1>Relatório de Contas a Pagar</h1>

<div style="text-align:right; font-size:9pt; color:#666; margin-bottom:8px;">
    Emitido em: {{ date('d/m/Y H:i') }}
</div>

@if (isset($filtrosNomes) && collect($filtrosNomes)->filter()->count())
    <div class="filtros">
        <strong>Filtros aplicados:</strong>
        @php $sep = false; @endphp
        @foreach ($filtrosNomes as $chave => $valor)
            @if ($valor)
                @if ($sep)
                    |
                @endif
                <span><b>{{ $chave }}:</b>
                    @if (str_contains($chave, 'Data') && $valor)
                        {{ \Carbon\Carbon::parse($valor)->format('d/m/Y') }}
                    @else
                        {{ $valor }}
                    @endif
                </span>
                @php $sep = true; @endphp
            @endif
        @endforeach
    </div>
@endif

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Fornecedor</th>
            <th>Despesa</th>
            <th>Parc.</th>
            <th>Vencimento</th>
            <th>Valor Parcela</th>
            <th>Valor Pago</th>
            <th>Pago</th>
            <th>Categoria</th>
            <th>Forma Pgto</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalParcela = 0;
            $totalPago = 0;
            $qtdPagas = 0;
            $qtdPendentes = 0;
        @endphp
        
        @foreach($contas as $c)
            @php
                $totalParcela += (float) $c->valor_parcela;
                $totalPago += (float) $c->valor_pago;
                if($c->status) {
                    $qtdPagas++;
                } else {
                    $qtdPendentes++;
                }
            @endphp
            <tr>
                <td>{{ $c->id }}</td>
                <td>{{ $c->fornecedor->nome ?? '---' }}</td>
                <td>{{ $c->despesa_id }}</td>
                <td>{{ $c->ordem_parcela }}</td>
                <td>{{ optional($c->data_vencimento)->format('d/m/Y') }}</td>
                <td class="text-right">R$ {{ number_format((float) $c->valor_parcela, 2, ',', '.') }}</td>
                <td class="text-right">R$ {{ number_format((float) $c->valor_pago, 2, ',', '.') }}</td>
                <td class="text-center">{{ $c->status ? 'Sim' : 'Não' }}</td>
                <td>{{ $c->categoria->nome ?? '---' }}</td>
                <td>{{ $c->formaPgmto->nome ?? '---' }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="font-weight:bold; background:#f2f2f2;">
            <td colspan="5">Totais</td>
            <td class="text-right">R$ {{ number_format($totalParcela, 2, ',', '.') }}</td>
            <td class="text-right">R$ {{ number_format($totalPago, 2, ',', '.') }}</td>
            <td colspan="3"></td>
        </tr>
    </tfoot>
</table>

<div style="margin-top: 20px; font-weight: bold;">
    Total de Registros: {{ $contas->count() }} | 
    Pagas: {{ $qtdPagas }} | 
    Pendentes: {{ $qtdPendentes }}
</div>

@php
    $porFornecedor = $contas->groupBy(fn($it)=> $it->fornecedor->nome ?? '---')->map->sum('valor_parcela')->sortDesc();
    $porCategoria = $contas->groupBy(fn($it)=> $it->categoria->nome ?? '---')->map->sum('valor_parcela')->sortDesc();
@endphp

<div class="resumo">
    <div style="flex:1;">
        <h4>Por Fornecedor</h4>
        <table>
            <thead>
                <tr>
                    <th>Fornecedor</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($porFornecedor as $nome => $valor)
                    <tr>
                        <td>{{ $nome }}</td>
                        <td class="text-right">R$ {{ number_format($valor, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="flex:1;">
        <h4>Por Categoria</h4>
        <table>
            <thead>
                <tr>
                    <th>Categoria</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($porCategoria as $nome => $valor)
                    <tr>
                        <td>{{ $nome }}</td>
                        <td class="text-right">R$ {{ number_format($valor, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection