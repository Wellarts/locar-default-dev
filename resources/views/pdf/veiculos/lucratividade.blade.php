@extends('layouts.pdf')

@section('title', 'Relatório de Lucratividade - Veículos')

@section('content')
<style>
    body {
        font-size: 12px;
    }
    
    h1 {
        font-size: 18px;
        margin: 0 0 5px 0;
    }
    
    .header-info {
        margin-bottom: 15px;
    }
    
    .table {
        font-size: 11px;
        margin-bottom: 0;
    }
    
    .table th, .table td {
        padding: 6px 8px;
        text-align: right;
    }
    
    .table th:first-child, .table td:first-child {
        text-align: left;
    }
    
    .table th:nth-child(2), .table td:nth-child(2) {
        text-align: left;
    }
    
    .table-responsive {
        margin: 0;
    }

    .row-totais {
        font-weight: bold;
        background-color: #f0f0f0;
    }
</style>

<div class="header-info">
    <h1>Relatório de Lucratividade - Veículos</h1>
    <p style="font-size: 10px; margin: 5px 0 0 0; color: #666;">Gerado em: {{ now()->format('d/m/Y H:i') }}</p>
</div>

@if($veiculos->isEmpty())
    <div class="alert alert-info">Nenhum veículo encontrado.</div>
@else
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Placa</th>
                    <th>Modelo</th>
                    <th>Receita Total</th>
                    <th>Despesa Total</th>
                    <th>Lucro Líquido</th>
                    <th>Margem (%)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalReceita = 0;
                    $totalDespesa = 0;
                    $totalLucro = 0;
                @endphp
                @foreach($veiculos as $veiculo)
                    @php
                        $receita = $veiculo->total_locacoes ?? 0;
                        $despesa = $veiculo->total_custos ?? 0;
                        $lucro = $receita - $despesa;
                        $margem = $receita > 0 
                            ? ($lucro / $receita) * 100 
                            : 0;
                        
                        $totalReceita += $receita;
                        $totalDespesa += $despesa;
                        $totalLucro += $lucro;
                    @endphp
                    <tr>
                        <td>{{ $veiculo->placa }}</td>
                        <td>{{ $veiculo->modelo }}</td>
                        <td>R$ {{ number_format($receita, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($despesa, 2, ',', '.') }}</td>
                        <td class="{{ $lucro >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                            R$ {{ number_format($lucro, 2, ',', '.') }}
                        </td>
                        <td>{{ number_format($margem, 2, ',', '.') }}%</td>
                    </tr>
                @endforeach
                <tr class="table-dark fw-bold row-totais">
                    <td colspan="2">TOTAL</td>
                        <td>R$ {{ number_format($totalReceita, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($totalDespesa, 2, ',', '.') }}</td>
                        <td class="{{ $totalLucro >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                            R$ {{ number_format($totalLucro, 2, ',', '.') }}
                        </td>
                        <td>{{ $totalReceita > 0 ? number_format(($totalLucro / $totalReceita) * 100, 2, ',', '.') : '0,00' }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
@endsection