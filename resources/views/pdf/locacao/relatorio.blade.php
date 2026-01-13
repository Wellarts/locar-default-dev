@php
use Illuminate\Support\Carbon;
@endphp

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Relatório de Locações</title>
    <style>
        @page {
            margin: 1.5cm;
            size: A4 portrait;
        }
        
        body {
            font-family: 'DejaVu Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            font-size: 9pt;
            margin: 0;
            padding: 0;
            line-height: 1.2;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        .container {
            width: 100%;
            max-width: 100%;
            margin: 0;
            background: #fff;
        }
        
        .header {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eaeaea;
        }
        
        .logo-cell {
            display: table-cell;
            width: 20%;
            vertical-align: top;
        }
        
        .company-cell {
            display: table-cell;
            width: 80%;
            vertical-align: top;
            text-align: right;
        }
        
        .logo {
            max-width: 80px;
            height: auto;
        }
        
        .company-name {
            font-size: 11pt;
            font-weight: 700;
            color: #1a365d;
            margin: 0 0 3px 0;
        }
        
        .company-details {
            font-size: 8pt;
            color: #666;
        }
        
        .title {
            font-size: 12pt;
            color: #2d3748;
            text-align: center;
            margin: 10px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid #eaeaea;
        }
        
        /* Layout de duas colunas para locações */
        .two-columns {
            column-count: 2;
            column-gap: 10px;
            margin-bottom: 12px;
        }

        .locacao-card {
            break-inside: avoid;
            page-break-inside: avoid;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px;
            margin-bottom: 8px;
            background: #fff;
            width: 100%;
            display: inline-block;
        }
        
        .locacao-header {
            background: #f7fafc;
            padding: 5px 8px;
            margin: -8px -8px 8px -8px;
            border-radius: 4px 4px 0 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .locacao-title {
            font-size: 9pt;
            font-weight: 700;
            color: #2d3748;
            margin: 0;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }

        /* Cada linha terá 4 células: label, value, label, value - para aproveitar espaço horizontal */
        .info-table td {
            padding: 1px 4px;
            vertical-align: top;
        }

        .info-label {
            font-weight: 600;
            color: #4a5568;
            white-space: nowrap;
            text-align: right;
            padding-right: 6px;
        }

        .info-value {
            color: #2d3748;
            padding-left: 6px;
        }
        
        .info-value.highlight {
            font-weight: 600;
            color: #2b6cb0;
        }
        
        .info-value.amount {
            font-family: 'Courier New', monospace;
        }
        
        .status-badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 10px;
            font-size: 8pt;
            font-weight: 600;
            background: #e2e8f0;
            color: #4a5568;
        }
        
        /* Seção de resumos */
        .page-break {
            page-break-before: always;
        }
        
        .summary-section {
            margin-top: 20px;
        }
        
        .summary-title {
            font-size: 11pt;
            font-weight: 700;
            color: #2d3748;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .summary-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 15px;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .summary-table th {
            background: #f7fafc;
            padding: 6px 8px;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            font-size: 8pt;
            border: 1px solid #e2e8f0;
        }
        
        .summary-table td {
            padding: 5px 8px;
            font-size: 8pt;
            border: 1px solid #e2e8f0;
        }
        
        .summary-table .total-row {
            background: #f6eac6;
            font-weight: 700;
        }
        
        .summary-table .amount {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        .grand-total {
            background: #2d3748;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            text-align: right;
            margin-top: 20px;
            font-size: 10pt;
        }
        
        .grand-total .label {
            font-weight: 600;
        }
        
        .grand-total .value {
            font-family: 'Courier New', monospace;
            font-size: 12pt;
            margin-left: 10px;
        }
        
        /* Melhorias para impressão */
        @media print {
            body {
                font-size: 9pt;
            }
            
            .two-columns {
                column-count: 2;
                column-gap: 12px;
            }
            
            .locacao-card {
                break-inside: avoid;
                page-break-inside: avoid;
            }
            
            .page-break {
                page-break-before: always;
                margin-top: 20px;
            }
            
            .summary-section {
                margin-top: 0;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Cabeçalho -->
        <div class="header">
            <div class="logo-cell">
                <img src="img/logo_real.png" alt="Logo" class="logo">
            </div>
            <div class="company-cell">
                <div class="company-name">REAL LOCAÇÕES E SERVIÇOS LTDA</div>
                <div class="company-details">
                    CNPJ: 12.874.349/0001-31 • Rua Joaquim Domingos Neto, 554 • (85) 3411-8010
                </div>
            </div>
        </div>
        
        <h1 class="title">Relatório de Locações</h1>
        
        <!-- Data do relatório -->
        <div style="text-align: right; margin-bottom: 15px; font-size: 8pt; color: #666;">
            Emitido em: {{ date('d/m/Y H:i') }}
        </div>
        
        <!-- Locações em duas colunas -->
        <div class="two-columns">
            @foreach($locacoes as $locacao)
            <div class="locacao-card">
                <div class="locacao-header">
                    <div class="locacao-title">
                        #{{ $locacao->id }} - {{ $locacao->cliente->nome ?? 'Cliente não informado' }}
                    </div>
                </div>
                
                <table class="info-table">
                    <tr>
                        <td class="info-label" style="width:18%">Veículo:</td>
                        <td class="info-value" style="width:32%">{{ $locacao->veiculo->modelo ?? $locacao->veiculo_id }}</td>
                        <td class="info-label" style="width:18%">Forma Pagto:</td>
                        <td class="info-value" style="width:32%">{{ $locacao->formaPgmto->nome ?? $locacao->forma_pgmto_id ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Período:</td>
                        <td class="info-value" style="font-size: 9px">{{ Carbon::parse($locacao->data_saida)->format('d/m/Y') ?? '—' }} às {{ Carbon::parse($locacao->hora_saida)->format('H:i') }} até {{ Carbon::parse($locacao->data_retorno)->format('d/m/Y') ?? '—' }} às {{ Carbon::parse($locacao->hora_retorno)->format('H:i') }}</td>
                        <td class="info-label">Diárias / Semanas:</td>
                        <td class="info-value">{{ $locacao->qtd_diarias ?? '0' }} / {{ $locacao->qtd_semanas ?? '0' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">KM Saída:</td>
                        <td class="info-value">{{ $locacao->km_saida ?? '0' }}</td>
                        <td class="info-label">KM Retorno:</td>
                        <td class="info-value">{{ $locacao->km_retorno ?? '0' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">KM Percorrido:</td>
                        <td class="info-value highlight">{{ $locacao->km_percorrido ?? ($locacao->km_retorno - $locacao->km_saida ?? '0') }}</td>
                        <td class="info-label">Valor Total:</td>
                        <td class="info-value amount highlight">R$ {{ number_format($locacao->valor_total ?? 0, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Valor c/ Desc:</td>
                        <td class="info-value amount highlight">R$ {{ number_format($locacao->valor_total_desconto ?? 0, 2, ',', '.') }}</td>
                        <td class="info-label">Valor Caução:</td>
                        <td class="info-value amount highlight">R$ {{ number_format($locacao->valor_caucao ?? 0, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Status:</td>
                        <td class="info-value">
                            <span class="status-badge">{{ $locacao->status == 0 ? 'Ativa' : 'Finalizada' }}</span>
                        </td>
                        <td class="info-label">Ocorrências:</td>
                        <td class="info-value">{{ $locacao->ocorrencias()->count() ?? 0 }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Observações:</td>
                        <td class="info-value" colspan="4" style="font-size: 9px">{{ $locacao->obs ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Lançado no Financeiro:</td>
                        <td class="info-value">
                            <span class="status-badge" style="background: {{ $locacao->valor_total_financeiro > 0 ? '#c6f6d5' : '#fed7d7' }}; color: {{ $locacao->valor_total_financeiro > 0 ? '#22543d' : '#742a2a' }}">{{ $locacao->valor_total_financeiro > 0 ? 'Sim' : 'Não' }}</span>
                        </td>
                        <td class="info-label">Tipo de Pagamento:</td>
                        <td class="info-value">
                            <span class="status-badge" style="background: {{ $locacao->status_pago_financeiro == 1 ? '#bee3f8' : '#fefcbf' }}; color: {{ $locacao->status_pago_financeiro == 1 ? '#2a4365' : '#744210' }}">{{ $locacao->status_pago_financeiro == 1 ? 'Pago' : 'Parcelado - ' . $locacao->parcelas_financeiro . 'x' ?? '0' }}</span>
                        </td>
                    </tr>
                    
                </table>
            </div>
            @endforeach
        </div>
        
        <!-- Quebra de página para resumos -->
        <div class="page-break"></div>
        
        <!-- Seção de Resumos -->
        <div class="summary-section">
            <h2 class="summary-title">RESUMOS ESTATÍSTICOS</h2>
            
            <!-- Grid com dois resumos lado a lado -->
            <div class="summary-grid">
                <!-- Resumo por Forma de Pagamento -->
                <div class="summary-column">
                    <h3 class="summary-title" style="font-size: 10pt;">Por Forma de Pagamento</h3>
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th>Forma de Pagamento</th>
                                <th class="amount">Valor Total</th>
                                <th>Qtd</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $pagamentos = [];
                                $totalPagamentos = 0;
                                $qtdPagamentos = 0;
                                
                                foreach($locacoes as $locacao) {
                                    $forma = $locacao->formaPgmto->nome ?? 'Não informado';
                                    $valor = $locacao->valor_total_desconto ?? 0;
                                    
                                    if(!isset($pagamentos[$forma])) {
                                        $pagamentos[$forma] = [
                                            'valor' => 0,
                                            'qtd' => 0
                                        ];
                                    }
                                    
                                    $pagamentos[$forma]['valor'] += $valor;
                                    $pagamentos[$forma]['qtd']++;
                                    $totalPagamentos += $valor;
                                    $qtdPagamentos++;
                                }
                                
                                ksort($pagamentos);
                            @endphp
                            
                            @foreach($pagamentos as $forma => $dados)
                            <tr>
                                <td>{{ $forma }}</td>
                                <td class="amount">R$ {{ number_format($dados['valor'], 2, ',', '.') }}</td>
                                <td>{{ $dados['qtd'] }}</td>
                            </tr>
                            @endforeach
                            
                            <tr class="total-row">
                                <td><strong>TOTAL</strong></td>
                                <td class="amount"><strong>R$ {{ number_format($totalPagamentos, 2, ',', '.') }}</strong></td>
                                <td><strong>{{ $qtdPagamentos }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Resumo por Status -->
                <div class="summary-column">
                    <h3 class="summary-title" style="font-size: 10pt;">Por Status</h3>
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th class="amount">Valor Total</th>
                                <th>Qtd</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $statusData = [];
                                $totalStatus = 0;
                                $qtdStatus = 0;
                                
                                foreach($locacoes as $locacao) {
                                    $status = $locacao->status == 0 ? 'Ativa' : 'Finalizada';                                   
                                    $valor = $locacao->valor_total_desconto ?? 0;
                                    
                                    if(!isset($statusData[$status])) {
                                        $statusData[$status] = [
                                            'valor' => 0,
                                            'qtd' => 0
                                        ];
                                    }
                                    
                                    $statusData[$status]['valor'] += $valor;
                                    $statusData[$status]['qtd']++;
                                    $totalStatus += $valor;
                                    $qtdStatus++;
                                }
                                
                                ksort($statusData);
                            @endphp
                            
                            @foreach($statusData as $status => $dados)
                            <tr>
                                <td>{{ $status }}</td>
                                <td class="amount">R$ {{ number_format($dados['valor'], 2, ',', '.') }}</td>
                                <td>{{ $dados['qtd'] }}</td>
                            </tr>
                            @endforeach
                            
                            <tr class="total-row">
                                <td><strong>TOTAL</strong></td>
                                <td class="amount"><strong>R$ {{ number_format($totalStatus, 2, ',', '.') }}</strong></td>
                                <td><strong>{{ $qtdStatus }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Grid com dois resumos lado a lado -->
            <div class="summary-grid">
                <!-- Resumo por Cliente -->
                <div class="summary-column">
                    <h3 class="summary-title" style="font-size: 10pt;">Clientes</h3>
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th class="amount">Valor Total</th>
                                <th>Qtd</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $clientes = [];
                                $totalClientes = 0;
                                $qtdClientes = 0;
                                
                                foreach($locacoes as $locacao) {
                                    $clienteNome = $locacao->cliente->nome ?? 'Cliente #' . $locacao->cliente_id;
                                    $valor = $locacao->valor_total_desconto ?? 0;
                                    
                                    if(!isset($clientes[$clienteNome])) {
                                        $clientes[$clienteNome] = [
                                            'valor' => 0,
                                            'qtd' => 0
                                        ];
                                    }
                                    
                                    $clientes[$clienteNome]['valor'] += $valor;
                                    $clientes[$clienteNome]['qtd']++;
                                    $totalClientes += $valor;
                                    $qtdClientes++;
                                }
                                
                                // Ordena por valor decrescente
                                uasort($clientes, function($a, $b) {
                                    return $b['valor'] <=> $a['valor'];
                                });
                            @endphp
                            
                            @foreach($clientes as $cliente => $dados)
                            <tr>
                                <td>{{ strlen($cliente) > 25 ? substr($cliente, 0, 25) . '...' : $cliente }}</td>
                                <td class="amount">R$ {{ number_format($dados['valor'], 2, ',', '.') }}</td>
                                <td>{{ $dados['qtd'] }}</td>
                            </tr>
                            @endforeach
                            
                            <tr class="total-row">
                                <td><strong>TOTAL GERAL</strong></td>
                                <td class="amount"><strong>R$ {{ number_format($totalClientes, 2, ',', '.') }}</strong></td>
                                <td><strong>{{ $qtdClientes }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Resumo por Veículo (Modelo + Placa) -->
                <div class="summary-column">
                    <h3 class="summary-title" style="font-size: 10pt;">Veículos</h3>
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th>Veículo</th>
                                <th class="amount">Valor Total</th>
                                <th>Qtd</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $veiculos = [];
                                $totalVeiculos = 0;
                                $qtdVeiculos = 0;
                                
                                foreach($locacoes as $locacao) {
                                    $modelo = $locacao->veiculo->modelo ?? 'Modelo desconhecido';
                                    $placa = $locacao->veiculo->placa ?? 'Placa desconhecida';
                                    $veiculoNome = "$modelo ($placa)";
                                    $valor = $locacao->valor_total_desconto ?? 0;
                                    
                                    if(!isset($veiculos[$veiculoNome])) {
                                        $veiculos[$veiculoNome] = [
                                            'valor' => 0,
                                            'qtd' => 0
                                        ];
                                    }
                                    
                                    $veiculos[$veiculoNome]['valor'] += $valor;
                                    $veiculos[$veiculoNome]['qtd']++;
                                    $totalVeiculos += $valor;
                                    $qtdVeiculos++;
                                }
                                
                                // Ordena por valor decrescente
                                uasort($veiculos, function($a, $b) {
                                    return $b['valor'] <=> $a['valor'];
                                });
                            @endphp
                            
                            @foreach($veiculos as $veiculo => $dados)
                            <tr>
                                <td>{{ strlen($veiculo) > 25 ? substr($veiculo, 0, 25) . '...' : $veiculo }}</td>
                                <td class="amount">R$ {{ number_format($dados['valor'], 2, ',', '.') }}</td>
                                <td>{{ $dados['qtd'] }}</td>
                            </tr>
                            @endforeach
                            
                            <tr class="total-row">
                                <td><strong>TOTAL GERAL</strong></td>
                                <td class="amount"><strong>R$ {{ number_format($totalVeiculos, 2, ',', '.') }}</strong></td>
                                <td><strong>{{ $qtdVeiculos }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Total Geral -->
            <div class="grand-total">
                <span class="label">VALOR TOTAL GERAL:</span>
                <span class="value">R$ {{ number_format($locacoes->sum('valor_total_desconto'), 2, ',', '.') }}</span>
            </div>
            
            <!-- Resumo Financeiro -->
            <div style="margin-top: 20px;">
                <h3 class="summary-title" style="font-size: 10pt;">Resumo Financeiro</h3>
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th class="amount">Valor Total</th>
                            <th class="amount">Valor Médio</th>
                            <th>Qtd Locações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $valorTotal = $locacoes->where('valor_total_financeiro', '>', 0)->sum('valor_total');
                            $valorTotalDesconto = $locacoes->where('valor_total_financeiro', '>', 0)->sum('valor_total_desconto');
                            $valorTotalCaucao = $locacoes->where('valor_total_financeiro', '>', 0)->sum('valor_caucao');
                            $valorTotalDescontoAplicado = $locacoes->where('valor_total_financeiro', '>', 0)->sum('valor_desconto');
                            $valorTotalFinanceiro = $locacoes->sum('valor_total_financeiro');
                            $qtdLocacoes = $locacoes->where('valor_total_financeiro', '>', 0)->count();
                            
                            $valorMedio = $qtdLocacoes > 0 ? $valorTotal / $qtdLocacoes : 0;
                            $valorMedioDesconto = $qtdLocacoes > 0 ? $valorTotalDesconto / $qtdLocacoes : 0;
                        @endphp
                        
                        <tr>
                            <td>Valor Total (sem desconto)</td>
                            <td class="amount">R$ {{ number_format($valorTotal, 2, ',', '.') }}</td>
                            <td class="amount">R$ {{ number_format($valorMedio, 2, ',', '.') }}</td>
                            <td>{{ $qtdLocacoes }}</td>
                        </tr>
                        <tr>
                            <td>Valor Total (com desconto)</td>
                            <td class="amount">R$ {{ number_format($valorTotalDesconto, 2, ',', '.') }}</td>
                            <td class="amount">R$ {{ number_format($valorMedioDesconto, 2, ',', '.') }}</td>
                            <td>{{ $qtdLocacoes }}</td>
                        </tr>
                        <tr>
                            <td>Total de Descontos Concedidos</td>
                            <td class="amount">R$ {{ number_format($valorTotalDescontoAplicado, 2, ',', '.') }}</td>
                            <td class="amount">R$ {{ number_format($valorTotalDescontoAplicado / max($qtdLocacoes, 1), 2, ',', '.') }}</td>
                            <td>{{ $qtdLocacoes }}</td>
                        </tr>
                        <tr>
                            <td>Total de Caução</td>
                            <td class="amount">R$ {{ number_format($valorTotalCaucao, 2, ',', '.') }}</td>
                            <td class="amount">R$ {{ number_format($valorTotalCaucao / max($qtdLocacoes, 1), 2, ',', '.') }}</td>
                            <td>{{ $qtdLocacoes }}</td>
                        </tr>
                        <tr class="total-row">
                            <td><strong>LANÇADO NO FINANCEIRO (parcelado/a vista)</strong></td>
                            <td class="amount"><strong>R$ {{ number_format($valorTotalFinanceiro, 2, ',', '.') }}</strong></td>
                            <td class="amount"><strong>R$ {{ number_format($valorTotalFinanceiro / max($qtdLocacoes, 1), 2, ',', '.') }}</strong></td>
                            <td><strong>{{ $qtdLocacoes }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>