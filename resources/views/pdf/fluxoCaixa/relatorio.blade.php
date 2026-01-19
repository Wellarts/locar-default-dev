@extends('layouts.pdf')

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

        th,
        td {
            padding: 6px;
            font-size: 7pt;
            text-align: center;
        }

        th {
            background: #f2f2f2;
        }

        h1 {
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background: #f7f7f7;
        }

        tbody tr:nth-child(odd) {
            background: #fff;
        }
    </style>

    <h1>Relatório de Fluxo de Caixa</h1>
    <p>Data de emissão: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>

    @if (isset($filtros) && is_array($filtros) && collect($filtros)->filter()->count())
        <div style="margin-bottom: 18px; font-size: 1rem; color: #888; background: #f4f6f9; padding: 8px 12px; border-radius: 6px;">
            <strong>Filtros aplicados:</strong>
            @php $sep = false; @endphp
            @foreach ($filtros as $chave => $valor)
                @if ($valor)
                    @if ($sep) | @endif
                    <span><b>{{ ucfirst(str_replace('_', ' ', $chave)) }}:</b>
                        @if (str_contains($chave, 'data') && $valor)
                            {{ \Carbon\Carbon::parse($valor)->format('d/m/Y') }}
                        @else
                            {{ is_array($valor) ? implode(', ', $valor) : $valor }}
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
                <th>Data/Hora</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Descrição</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalCredito = 0;
                $totalDebito = 0;
            @endphp
            @foreach($fluxoCaixa as $item)
                @php
                    if ($item->tipo === 'CREDITO') {
                        $totalCredito += $item->valor;
                    } elseif ($item->tipo === 'DEBITO') {
                        $totalDebito += $item->valor;
                    }
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $item->tipo ?? 'N/A' }}</td>
                    <td>R$ {{ number_format($item->valor, 2, ',', '.') }}</td>
                    <td>{{ $item->obs }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="margin-top: 30px;">
        <thead>
            <tr style="font-weight:bold;background:#f2f2f2;">
                <th colspan="2">Totais</th>
                <th>Total Crédito</th>
                <th>Total Débito</th>
                <th>Saldo Final</th>
            </tr>
        </thead>
        <tbody>
            <tr style="font-weight:bold;background:#f9f9f9;">
                <td colspan="2">Totais</td>
                <td>R$ {{ number_format($totalCredito, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($totalDebito, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($totalCredito - $totalDebito, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
@endsection

