@extends('layouts.pdf')

@section('title', 'Relatório de Custo por Veículo')

@section('content')
<style>
    /* Mesmas margens do layout principal */
    @page {
        margin: 1.5cm;
        size: A4 portrait;
    }

    body {
        font-family: 'DejaVu Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333;
        font-size: 9pt;
        margin: 0 auto;
        padding: 0;
        line-height: 1.2;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        width: 100%;
        max-width: 21cm;
    }

    * {
        box-sizing: border-box;
    }

    .title {
        font-size: 12pt;
        color: #2d3748;
        text-align: center;
        margin: 0 auto 15px auto;
        padding-bottom: 8px;
        border-bottom: 1px solid #eaeaea;
        width: 100%;
    }

    .date-info {
        text-align: right;
        margin-bottom: 15px;
        font-size: 8pt;
        color: #666;
        width: 100%;
    }

    .filtros-info {
        background: #f7fafc;
        padding: 8px 12px;
        border-radius: 4px;
        margin-bottom: 15px;
        border: 1px solid #e2e8f0;
        font-size: 8pt;
    }

    .filtros-title {
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 4px;
    }

    .summary-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
        table-layout: fixed;
        background: #fff;
        border-radius: 4px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .summary-table th {
        background: #f7fafc;
        padding: 8px 10px;
        text-align: left;
        font-weight: 600;
        color: #4a5568;
        font-size: 9pt;
        border: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .summary-table td {
        padding: 6px 10px;
        font-size: 9pt;
        border: 1px solid #e2e8f0;
        vertical-align: top;
    }

    .summary-table .total-row {
        background: #f6eac6;
        font-weight: 700;
    }

    .summary-table .amount {
        text-align: right;
        font-family: 'Courier New', monospace;
    }

    .summary-table .highlight {
        color: #2b6cb0;
        font-weight: 600;
    }

    .summary-table tr:nth-child(even) {
        background: #f8fafc;
    }

    .grand-total {
        background: #2d3748;
        color: white;
        padding: 12px 15px;
        border-radius: 4px;
        text-align: right;
        margin-top: 25px;
        font-size: 10pt;
        width: 100%;
    }

    .grand-total .label {
        font-weight: 600;
        margin-right: 10px;
    }

    .grand-total .value {
        font-family: 'Courier New', monospace;
        font-size: 12pt;
        margin-left: 10px;
    }

    .status-badge {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 10px;
        font-size: 7pt;
        font-weight: 600;
        margin-right: 4px;
        margin-bottom: 2px;
    }

    .badge-pago {
        background: #c6f6d5;
        color: #22543d;
    }

    .badge-pendente {
        background: #fed7d7;
        color: #742a2a;
    }

    .badge-financeiro {
        background: #bee3f8;
        color: #2c5282;
    }

    .badge-nao-financeiro {
        background: #e2e8f0;
        color: #4a5568;
    }

    .badge-categoria {
        background: #faf089;
        color: #744210;
    }

    .badge-parcelas {
        background: #d6bcfa;
        color: #44337a;
    }

    .content-container {
        width: 100%;
        margin: 0 auto;
        padding: 0;
    }

    .stat-card {
        flex: 1;
        min-width: 180px;
        background: #f7fafc;
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
        margin-bottom: 10px;
    }

    .descricao-list {
        margin: 0;
        padding: 0;
        list-style-type: none;
        font-size: 8pt;
        line-height: 1.3;
    }

    .descricao-list li {
        margin-bottom: 5px;
        padding-bottom: 5px;
        border-bottom: 1px dashed #e2e8f0;
    }

    .descricao-list li:last-child {
        border-bottom: none;
    }

    .descricao-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .descricao-conteudo {
        flex: 1;
    }

    .descricao-texto {
        color: #4a5568;
        margin-bottom: 3px;
        font-weight: 500;
    }

    .descricao-detalhes {
        font-size: 7pt;
        color: #718096;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
        margin-bottom: 3px;
    }

    .descricao-fornecedor {
        background: #e6fffa;
        padding: 1px 4px;
        border-radius: 3px;
        color: #234e52;
        white-space: nowrap;
    }

    .descricao-data {
        color: #805ad5;
        white-space: nowrap;
    }

    .descricao-km {
        color: #dd6b20;
        white-space: nowrap;
    }

    .descricao-codigo {
        color: #718096;
        font-family: 'Courier New', monospace;
    }

    .descricao-status {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }

    .descricao-valor {
        font-family: 'Courier New', monospace;
        font-size: 8pt;
        color: #2b6cb0;
        font-weight: 700;
        margin-left: 8px;
        white-space: nowrap;
    }

    .resumo-status {
        background: #f8fafc;
        padding: 12px;
        border-radius: 4px;
        margin: 15px 0;
        border: 1px solid #e2e8f0;
    }

    .resumo-status-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
    }

    .resumo-status-item {
        text-align: center;
        padding: 8px;
        border-radius: 4px;
        background: white;
        border: 1px solid #e2e8f0;
    }

    .resumo-status-label {
        font-size: 8pt;
        color: #4a5568;
        margin-bottom: 4px;
    }

    .resumo-status-valor {
        font-size: 11pt;
        font-weight: 700;
        font-family: 'Courier New', monospace;
        color: #2d3748;
    }

    @media print {
        body {
            font-size: 9pt;
            max-width: 100%;
        }

        .summary-table th,
        .grand-total {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<div class="content-container">
    <h1 class="title">Relatório de Custo por Veículo</h1>

    <!-- Data do relatório -->
    <div class="date-info">
        Emitido em: {{ date('d/m/Y H:i') }}
        @if(request()->has('data_inicio') && request()->has('data_fim'))
            <br>Período: {{ date('d/m/Y', strtotime(request('data_inicio'))) }} até {{ date('d/m/Y', strtotime(request('data_fim'))) }}
        @endif
    </div>

    <!-- Filtros aplicados -->
    @if(isset($filtrosNomes) && count($filtrosNomes) > 0)
        <div class="filtros-info">
            <div class="filtros-title">Filtros Aplicados:</div>
            @foreach($filtrosNomes as $label => $valor)
                <div><strong>{{ $label }}:</strong> {{ $valor }}</div>
            @endforeach
        </div>
    @endif

    @if($dadosVeiculos && $dadosVeiculos->count() > 0)
        <!-- Tabela de custos -->
        <table class="summary-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 15%;">Veículo</th>
                    <th style="width: 10%;">Placa</th>
                    <th style="width: 40%;">Detalhes dos Custos</th>
                    <th class="amount" style="width: 15%;">Custo Total</th>
                    <th class="amount" style="width: 15%;">Média Mensal</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $contador = 1;
                @endphp

                @foreach($dadosVeiculos as $custoVeiculo)
                    <tr>
                        <td>{{ $contador++ }}</td>
                        <td>
                            <strong>{{ $custoVeiculo->veiculo->modelo ?? 'Modelo não informado' }}</strong>
                            @if(isset($custoVeiculo->veiculo->ano))
                                <br><small style="color: #666; font-size: 8pt;">Ano: {{ $custoVeiculo->veiculo->ano }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge" style="background: #e6fffa; color: #234e52;">
                                {{ $custoVeiculo->veiculo->placa ?? 'Sem placa' }}
                            </span>
                        </td>
                        <td style="font-size: 8pt; white-space: normal;">
                            @if(count($custoVeiculo->descricoes) > 0)
                                <ul class="descricao-list">
                                    @foreach($custoVeiculo->descricoes as $descricao)
                                        <li>
                                            <div class="descricao-item">
                                                <div class="descricao-conteudo">
                                                    <div class="descricao-texto">
                                                        {{ $descricao['descricao'] ?? 'Sem descrição' }}
                                                        <span class="descricao-codigo">[ID: {{ $descricao['id'] }}]</span>
                                                    </div>
                                                    <div class="descricao-detalhes">
                                                        @if(!empty($descricao['data']))
                                                            <span class="descricao-data">
                                                                {{ date('d/m/Y', strtotime($descricao['data'])) }}
                                                            </span>
                                                        @endif
                                                        @if(!empty($descricao['km_atual']))
                                                            <span class="descricao-km">
                                                                KM: {{ number_format($descricao['km_atual'], 0, '', '.') }}
                                                            </span>
                                                        @endif
                                                        @if(!empty($descricao['fornecedor']))
                                                            <span class="descricao-fornecedor">
                                                                {{ $descricao['fornecedor'] }}
                                                            </span>
                                                        @endif
                                                        @if(!empty($descricao['categoria']))
                                                            <span class="status-badge badge-categoria">
                                                                {{ $descricao['categoria'] }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="descricao-status">                                                        
                                                        <span class="status-badge {{ $descricao['financeiro'] == 1 ? 'badge-financeiro' : 'badge-nao-financeiro' }}">
                                                            {{ $descricao['financeiro'] == 1 ? 'Financeiro' : 'Sem Financeiro' }}
                                                        </span>
                                                        @if(!empty($descricao['financeiro'] == 1 ))
                                                            <span class="status-badge {{ $descricao['pago'] == 1 ? 'badge-pago' : 'badge-pendente' }}">
                                                                {{ $descricao['pago'] == 1 ? 'Pago' : 'A pagar' }}
                                                            </span>
                                                        @endif
                                                        @if(!empty($descricao['parcelas']) && $descricao['parcelas'] > 1)
                                                            <span class="status-badge badge-parcelas">
                                                                {{ $descricao['parcelas'] }}x
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="descricao-valor">
                                                    R$ {{ number_format($descricao['valor'] ?? 0, 2, ',', '.') }}
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <em style="color: #a0aec0;">Sem custos registrados</em>
                            @endif
                        </td>
                        <td class="amount highlight">R$ {{ number_format($custoVeiculo->custo_total ?? 0, 2, ',', '.') }}</td>
                        <td class="amount highlight">R$ {{ number_format($custoVeiculo->custo_medio_mensal ?? 0, 2, ',', '.') }}</td>
                    </tr>
                @endforeach

                <!-- Linha de totais -->
                <tr class="total-row">
                    <td colspan="4"><strong>TOTAL GERAL</strong></td>
                    <td class="amount"><strong>R$ {{ number_format($totalCustoGeral, 2, ',', '.') }}</strong></td>
                    <td class="amount"><strong>R$ {{ number_format($totalCustoMensal, 2, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Resumo por Status -->
        <div class="resumo-status">
            <h3 style="font-size: 10pt; text-align: center; margin-bottom: 10px; color: #4a5568;">RESUMO POR STATUS</h3>
            <div class="resumo-status-grid">
                <div class="resumo-status-item">
                    <div class="resumo-status-label">Total Pago</div>
                    <div class="resumo-status-valor" style="color: #22543d;">
                        R$ {{ number_format($totalPago, 2, ',', '.') }}
                    </div>
                </div>
                <div class="resumo-status-item">
                    <div class="resumo-status-label">Total Pendente</div>
                    <div class="resumo-status-valor" style="color: #742a2a;">
                        R$ {{ number_format($totalPendente, 2, ',', '.') }}
                    </div>
                </div>
                <div class="resumo-status-item">
                    <div class="resumo-status-label">Lançado Financeiro</div>
                    <div class="resumo-status-valor" style="color: #2c5282;">
                        R$ {{ number_format($totalFinanceiro, 2, ',', '.') }}
                    </div>
                </div>
                <div class="resumo-status-item">
                    <div class="resumo-status-label">Não Lançado</div>
                    <div class="resumo-status-valor" style="color: #4a5568;">
                        R$ {{ number_format($totalNaoFinanceiro, 2, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumo Estatístico -->
        @php
            $custoMaisAlto = $dadosVeiculos->max('custo_total') ?? 0;
            $custoMaisBaixo = $dadosVeiculos->min('custo_total') ?? 0;
        @endphp

        <div style="margin-top: 20px;">
            <h2 class="title" style="font-size: 11pt; text-align: center; margin-bottom: 15px;">RESUMO ESTATÍSTICO</h2>
            
            <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
                <div class="stat-card">
                    <div style="font-size: 8pt; color: #4a5568; margin-bottom: 4px;">Quantidade de Veículos</div>
                    <div style="font-size: 14pt; font-weight: 700; color: #2d3748;">{{ $quantidadeVeiculos }}</div>
                </div>
                
                <div class="stat-card">
                    <div style="font-size: 8pt; color: #4a5568; margin-bottom: 4px;">Média de Custo Total</div>
                    <div style="font-size: 14pt; font-weight: 700; color: #2d3748; font-family: 'Courier New', monospace;">
                        R$ {{ number_format($mediaCustoTotal, 2, ',', '.') }}
                    </div>
                </div>
                
                <div class="stat-card">
                    <div style="font-size: 8pt; color: #4a5568; margin-bottom: 4px;">Média de Custo Mensal</div>
                    <div style="font-size: 14pt; font-weight: 700; color: #2d3748; font-family: 'Courier New', monospace;">
                        R$ {{ number_format($mediaCustoMensal, 2, ',', '.') }}
                    </div>
                </div>

                <div class="stat-card">
                    <div style="font-size: 8pt; color: #4a5568; margin-bottom: 4px;">Maior Custo (Veículo)</div>
                    <div style="font-size: 14pt; font-weight: 700; color: #2b6cb0; font-family: 'Courier New', monospace;">
                        R$ {{ number_format($custoMaisAlto, 2, ',', '.') }}
                    </div>
                </div>

                <div class="stat-card">
                    <div style="font-size: 8pt; color: #4a5568; margin-bottom: 4px;">Menor Custo (Veículo)</div>
                    <div style="font-size: 14pt; font-weight: 700; color: #dd6b20; font-family: 'Courier New', monospace;">
                        R$ {{ number_format($custoMaisBaixo, 2, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Geral -->
        <div class="grand-total">
            <span class="label">CUSTO TOTAL CONSOLIDADO:</span>
            <span class="value">R$ {{ number_format($totalCustoGeral, 2, ',', '.') }}</span>
        </div>
    @else
        <div style="text-align: center; padding: 40px 20px; color: #718096;">
            <div style="font-size: 14pt; margin-bottom: 10px;">Nenhum dado encontrado</div>
            <div style="font-size: 10pt;">Não há informações de custos para o período selecionado.</div>
        </div>
    @endif
</div>
@endsection