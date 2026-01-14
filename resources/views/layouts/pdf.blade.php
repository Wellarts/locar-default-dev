<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Relatório PDF')</title>
    
    <style>
        /* Margens iguais e centralização forçada */
        @page { 
            margin: 1.5cm;
            size: A4 portrait;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: sans-serif;
            background: #ffffff;
            color: #333;
            margin: 0 auto;
            padding: 0;
            width: 100%;
            max-width: 21cm; /* Largura de A4 menos margens */
        }

        .report-container {
            width: 100%;
            margin: 0 auto;
            background: #fff;
            box-shadow: none;
            padding: 0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            width: 100%;
        }

        .header img {
            height: 60px;
            max-width: 100%;
        }

        .header-info {
            text-align: right;
        }

        .header-info h1 {
            font-size: 1.6rem;
            margin: 0;
            color: #2c3e50;
            font-weight: 700;
        }

        .header-info p {
            margin: 2px 0;
            font-size: 0.9rem;
            color: #666;
        }

        /* Conteúdo centralizado */
        .main-content {
            width: 100%;
            margin: 0 auto;
            padding: 0;
        }

        .section-title {
            text-align: center;
            color: #6d6d6d;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .badge {
            display: inline-block;
            background: #777f1a;
            color: #fff;
            padding: 6px 14px;
            border-radius: 18px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .info-line {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 0.95rem;
            margin: 20px 0;
            padding: 12px 16px;
            background: #f9fafc;
            border: 1px solid #eee;
            border-radius: 10px;
        }

        .info-item strong {
            color: #2c3e50;
            font-weight: 600;
            margin-right: 6px;
        }

        /* Tabelas centralizadas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 14px 0;
            font-size: 0.95rem;
        }

        th,
        td {
            padding: 10px 12px;
            text-align: left;
        }

        th {
            background: #f4f6f9;
            font-weight: 600;
            color: #2c3e50;
        }

        td {
            background: #fff;
            border-bottom: 1px solid #eee;
        }

        .text-center {
            text-align: center;
        }

        .summary {
            margin-top: 20px;
            font-size: 1rem;
            padding: 15px;
            background: transparent;
            border: 1px solid #eee;
            border-radius: 6px;
            page-break-inside: avoid;
            -webkit-column-break-inside: avoid;
            break-inside: avoid;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .summary-row strong {
            color: #2c3e50;
            font-weight: 600;
        }

        .signature {
            text-align: center;
            margin-top: 36px;
        }

        /* Evita que elementos com fundo sejam divididos entre páginas */
        .info-line, .summary, tr, thead {
            page-break-inside: avoid;
            -webkit-column-break-inside: avoid;
            break-inside: avoid;
        }

        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }

        .signature hr {
            width: 60%;
            margin: 20px auto 10px;
            border: 0;
            border-top: 1px solid #bbb;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            font-size: 0.85rem;
            color: #888;
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="report-container">
        <header class="header">
            <table style="width:100%;">
                @php
                    $p = null;
                    try {
                        $p = DB::table('parametros')->first();
                    } catch (Exception $e) {
                        $p = null;
                    }
                    $companyName = $p->empresa_nome ?? ($p->nome_empresa ?? ($p->nome ?? 'Nome da Empresa'));
                    $companyCnpj = $p->cnpj ?? '00.000.000/0000-00';
                    $companyAddress = $p->endereco_completo ?? 'Sem Endereço Informado';
                    $companyPhones = $p->telefone ?? '(00) 0000-0000';
                    $companyInsta = $p->redes_sociais ?? '@empresa';
                    $logo = $p->logo;
                @endphp
                <tr>
                    <td style="width: 80px; vertical-align: top;">
                        <img src="{{ public_path('storage/' . $logo) }}"
                            alt="Logo da Empresa" style="height: 60px; width: auto; max-width: 120px;">
                    </td>
                    <td style="text-align: right;">
                        <div class="header-info">
                            <h2 style="font-size: 1.6rem; margin: 0; color: #2563eb; font-weight: 700;">
                                @yield('title', 'Relatório')</h2>
                            <p style="font-size: 12px; color: #2563eb; font-weight:700; margin:6px 0 2px;">
                                {{ $companyName }}</p>
                            <p style="font-size: 10px; color: #aaa; margin:0;">
                                <br>
                                CNPJ/CPF: {{ $companyCnpj }}<br>
                                Endereço: {{ $companyAddress }}
                            </p>
                            <p style="font-size: 10px; color: #aaa;">
                                Telefones: {{ $companyPhones }}
                            </p>
                            <p style="font-size: 10px; color: #aaa;">
                                Redes Sociais: {{ $companyInsta }}
                            </p>
                        </div>
                    </td>
                </tr>
            </table>
        </header>
        
        <div class="main-content">
            @yield('content')
            @yield('summary')
        </div>
        
        <footer class="footer">
            Documento gerado em {{ date('d/m/Y H:i') }}
        </footer>
    </div>
</body>

</html>