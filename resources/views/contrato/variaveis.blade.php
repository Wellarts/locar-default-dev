<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Variáveis para Contrato - Locadora</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }
        .container {
            max-width: 900px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .variable-card {
            background-color: #f8f9fa;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 0 8px 8px 0;
        }
        .variable-code {
            background-color: #1e293b;
            color: #e2e8f0;
            padding: 10px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            margin: 0;
        }
        .copy-btn {
            cursor: pointer;
            transition: all 0.3s;
        }
        .copy-btn:hover {
            transform: scale(1.05);
        }
        .example-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            border-radius: 0 8px 8px 0;
        }
        .section-title {
            color: #1e40af;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header text-center">
            <h1 class="display-5">📋 Variáveis para Contratos</h1>
            <p class="lead mb-0">Copie e cole estas variáveis no campo "Descrição" do contrato</p>
            <p class="small mt-2">As variáveis serão automaticamente substituídas pelas informações reais</p>
        </div>

        <div class="alert alert-info">
            <h5>💡 Como usar</h5>
            <p class="mb-0">1. Selecione e copie as variáveis abaixo<br>
            2. Cole no campo "Descrição" do contrato<br>
            3. Ao gerar o contrato, as variáveis serão substituídas pelos dados reais</p>
        </div>

        <!-- Informações do Cliente -->
        <h3 class="section-title">👤 Informações do Cliente</h3>
        <div class="row">
            @php
                $clienteVars = [
                    '{{ $cliente->nome }}' => 'Nome completo',
                    '{{ $cpfCnpj }}' => 'CPF ou CNPJ',
                    '{{ $cliente->rg }}' => 'RG',
                    '{{ $cliente_endereco }}' => 'Endereço completo',
                    '{{ $cliente_cidade }}' => 'Cidade',
                    '{{ $cliente_estado }}' => 'Estado',


                ];
            @endphp
            
            @foreach($clienteVars as $var => $desc)
            <div class="col-md-6 mb-3">
                <div class="variable-card d-flex justify-content-between align-items-center">
                    <div>
                        <p class="variable-code mb-1">{{ $var }}</p>
                        <small class="text-muted">{{ $desc }}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-primary copy-btn" data-clipboard-text="{{ $var }}">
                        Copiar
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Dados do Veículo -->
        <h3 class="section-title mt-4">🚗 Dados do Veículo</h3>
        <div class="row">
            @php
                $veiculoVars = [
                    '{{ $veiculo_marca }}' => 'Marca',
                    '{{ $veiculo_modelo }}' => 'Modelo',
                    '{{ $veiculo_placa }}' => 'Placa',
                    '{{ $veiculo_ano }}' => 'Ano',
                    '{{ $veiculo_cor }}' => 'Cor',
                    '{{ $veiculo_chassi }}' => 'Chassi',
                    '{{ $veiculo_renavam }}' => 'Renavam',
                    '{{ $veiculo_km }}' => 'KM',
                ];
            @endphp
            
            @foreach($veiculoVars as $var => $desc)
            <div class="col-md-6 mb-3">
                <div class="variable-card d-flex justify-content-between align-items-center">
                    <div>
                        <p class="variable-code mb-1">{{ $var }}</p>
                        <small class="text-muted">{{ $desc }}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-primary copy-btn" data-clipboard-text="{{ $var }}">
                        Copiar
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Datas e Valores -->
        <h3 class="section-title mt-4">📅 Datas e Valores</h3>
        <div class="row">
            @php
                $dataVars = [
                    '{{ $data_saida }}' => 'Data de saída',
                    '{{ $data_retorno }}' => 'Data de retorno',
                    '{{ $hora_saida }}' => 'Hora de saída',
                    '{{ $hora_retorno }}' => 'Hora de retorno',
                    '{{ $qtd_diarias }}' => 'Quantidade de diárias',
                    '{{ $qtd_semanas }}' => 'Quantidade de semanas',
                    '{{ $valor_total }}' => 'Valor total (R$)',
                    '{{ $valor_total_desconto }}' => 'Valor total com desconto (R$)',
                    '{{ $valor_caucao }}' => 'Caução (R$)',
                    '{{ $dataAtual->format("d/m/Y") }}' => 'Data de hoje',
                ];
            @endphp
            
            @foreach($dataVars as $var => $desc)
            <div class="col-md-6 mb-3">
                <div class="variable-card d-flex justify-content-between align-items-center">
                    <div>
                        <p class="variable-code mb-1">{{ $var }}</p>
                        <small class="text-muted">{{ $desc }}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-primary copy-btn" data-clipboard-text="{{ $var }}">
                        Copiar
                    </button>
                </div>
            </div>
            @endforeach
        </div>

       

        <!-- Botão de Fechar -->
        <div class="text-center mt-4">
            <button onclick="window.close()" class="btn btn-secondary">
                Fechar Esta Janela
            </button>
            <p class="text-muted mt-2 small">Esta página pode ser fechada após copiar as variáveis necessárias</p>
        </div>
    </div>

    <!-- Clipboard.js -->
    <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.11/dist/clipboard.min.js"></script>
    <script>
        // Inicializar Clipboard.js
        new ClipboardJS('.copy-btn');
        
        // Feedback ao copiar
        document.querySelectorAll('.copy-btn').forEach(button => {
            button.addEventListener('click', function() {
                const originalText = this.innerHTML;
                this.innerHTML = '✓ Copiado!';
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-success');
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.classList.remove('btn-success');
                    this.classList.add('btn-outline-primary');
                }, 2000);
            });
        });
        
        // Botão para copiar tudo
        // const copyAllBtn = document.createElement('button');
        // copyAllBtn.className = 'btn btn-primary mb-4';
        // copyAllBtn.innerHTML = '📋 Copiar Todas as Variáveis';
        // document.querySelector('.alert').after(copyAllBtn);
        
        copyAllBtn.addEventListener('click', function() {
            let allVars = '';
            document.querySelectorAll('.variable-code').forEach(code => {
                allVars += code.textContent + '\n';
            });
            
            navigator.clipboard.writeText(allVars).then(() => {
                const originalText = this.innerHTML;
                this.innerHTML = '✓ Todas as variáveis copiadas!';
                this.classList.remove('btn-primary');
                this.classList.add('btn-success');
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.classList.remove('btn-success');
                    this.classList.add('btn-primary');
                }, 3000);
            });
        });
    </script>
</body>
</html>