<?php

namespace App\Http\Controllers;

use App\Models\Locacao;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Contrato as ContratoModel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\FacadesLog;

class Contrato extends Controller
{
    public function debugTemplate($contratoId)
    {
        $contrato = ContratoModel::findOrFail($contratoId);

        $rawTemplate = $contrato->descricao ?? '';

        // Mostrar como está armazenado
        return response()->json([
            'titulo' => $contrato->titulo,
            'descricao_raw' => $rawTemplate,
            'descricao_length' => strlen($rawTemplate),
            'descricao_bin' => bin2hex(substr($rawTemplate, 0, 100)),
            'contains_{{ ' => strpos($rawTemplate, '{{') !== false,
            'contains_&# ' => strpos($rawTemplate, '&#') !== false,
            'contains_@{{ ' => strpos($rawTemplate, '@{{') !== false,
            'first_500_chars' => substr($rawTemplate, 0, 500),
        ]);
    }
    
    public function testLogo()
    {
        $parametros = \App\Models\Parametro::first();
        
        if (!$parametros) {
            return response()->json(['error' => 'Parâmetros não encontrados']);
        }
        
        $logoPath = storage_path('app/public/' . $parametros->logo);
        $logoBase64 = null;
        $logo_html = '';
        
        if ($parametros->logo && file_exists($logoPath)) {
            $imageData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode($imageData);
            $logo_html = '<div style="position: absolute; top: 20px; left: 20px; border: 2px solid red;"><img src="' . $logoBase64 . '" alt="Logo" style="max-height: 80px;"></div>';
            
            return response("<html><body>{$logo_html}<h1>Teste de Logo</h1><p>Se você vê a logo acima com borda vermelha, está funcionando.</p><p>Caminho: {$logoPath}</p></body></html>");
        }
        
        return response()->json([
            'parametros' => $parametros->toArray(),
            'logo_field' => $parametros->logo,
            'logo_path' => $logoPath,
            'file_exists' => file_exists($logoPath),
            'storage_path' => storage_path('app/public/'),
            'logo_html' => $logo_html
        ]);
    }

    public function printLocacao($id)
    {
        //FORMATAR DATA
        $locacao = Locacao::find($id);
        Carbon::setLocale('pt-BR');
        $dataAtual = Carbon::now();

        //FORMATAR CPF
        $CPF_LENGTH = 11;
        $cnpj_cpf = preg_replace("/\D/", '', $locacao->Cliente->cpf_cnpj);

        if (strlen($cnpj_cpf) === $CPF_LENGTH) {
            $cpfCnpj = preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cnpj_cpf);
        } else {
            $cpfCnpj =  preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj_cpf);
        }

        //FORMATAR TELEFONE
        $tel_1 = $locacao->Cliente->telefone_1;
        $tel_2 = $locacao->Cliente->telefone_2;

        return pdf::loadView('pdf.locacao.contrato', compact([
            'locacao',
            'dataAtual',
            'cpfCnpj',
            'tel_1',
            'tel_2'
        ]))->stream();
    }

    /**
     * Gerar contrato usando template do modelo Contrato preenchendo variáveis
     */
    public function printLocacaoContrato($locacaoId, $contratoId)
    {
        $locacao = Locacao::with(['Cliente', 'Veiculo', 'Veiculo.Marca', 'Cliente.Cidade', 'Cliente.Estado'])->findOrFail($locacaoId);
        $contrato = ContratoModel::findOrFail($contratoId);
        
        // BUSCAR LOGO DOS PARÂMETROS
        $parametros = \App\Models\Parametro::first();
        $logoBase64 = null;
        $logo_html = '';
        $logo_raw = '';
        
        if ($parametros && $parametros->logo) {
            Log::info('Tentando carregar logo: ' . $parametros->logo);
            $logoPath = storage_path('app/public/' . $parametros->logo);
            
            if (file_exists($logoPath)) {
                Log::info('Logo encontrada em: ' . $logoPath);
                try {
                    $imageData = file_get_contents($logoPath);
                    $logoBase64 = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode($imageData);
                    $logo_html = '<div style="position: absolute; top: 20px; left: 20px;"><img src="' . $logoBase64 . '" alt="Logo" style="max-height: 80px;"></div>';
                    $logo_raw = $logo_html; // Mesmo conteúdo, mas será usado com {!! !!}
                    Log::info('Logo convertida com sucesso. Tamanho base64: ' . strlen($logoBase64));
                } catch (\Exception $e) {
                    Log::error('Erro ao converter logo: ' . $e->getMessage());
                }
            } else {
                Log::warning('Arquivo de logo não encontrado: ' . $logoPath);
            }
        } else {
            Log::info('Nenhuma logo configurada nos parâmetros');
        }

        $dataAtual = Carbon::now();

        // CPF/CNPJ format
        $CPF_LENGTH = 11;
        $cnpj_cpf = preg_replace("/\D/", '', $locacao->Cliente->cpf_cnpj ?? '');
        if (strlen($cnpj_cpf) === $CPF_LENGTH) {
            $cpfCnpj = preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", '$1.$2.$3-$4', $cnpj_cpf);
        } else {
            $cpfCnpj = preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", '$1.$2.$3/$4-$5', $cnpj_cpf);
        }

        $tel_1 = $locacao->Cliente->telefone_1 ?? '';
        $tel_2 = $locacao->Cliente->telefone_2 ?? '';

        // Dados para serem usados no template (variáveis de conveniência e formatação)
        $cliente = $locacao->Cliente;
        $veiculo = $locacao->Veiculo;

        // Formatar datas
        $data_saida_fmt = $locacao->data_saida ? Carbon::parse($locacao->data_saida)->format('d/m/Y') : '';
        $data_retorno_fmt = $locacao->data_retorno ? Carbon::parse($locacao->data_retorno)->format('d/m/Y') : '';
        $data_hoje = Carbon::now()->format('d/m/Y');

        // Formatar valores monetários
        $valor_total = isset($locacao->valor_total) ? number_format($locacao->valor_total, 2, ',', '.') : '';
        $valor_total_desconto = isset($locacao->valor_total_desconto) ? number_format($locacao->valor_total_desconto, 2, ',', '.') : '';
        $valor_caucao = isset($locacao->valor_caucao) ? number_format($locacao->valor_caucao, 2, ',', '.') : '';
        $valor_desconto = isset($locacao->valor_desconto) ? number_format($locacao->valor_desconto, 2, ',', '.') : '';

        // Formatar data de nascimento do cliente
        $cliente_data_nascimento = $cliente->data_nascimento ? Carbon::parse($cliente->data_nascimento)->format('d/m/Y') : '';

        $data = [
            // Logo como string base64 (para uso em src da imagem)
            'logo' => $logoBase64,
            // Logo como HTML completo (para facilitar a implementação)
            'logo_html' => $logo_html,
            // Logo como HTML sem escape (para uso com {!! !!})
            'logo_raw' => $logo_raw,
            
            // Dados existentes
            'locacao' => $locacao,
            'cliente' => $cliente,
            'veiculo' => $veiculo,
            'dataAtual' => $dataAtual,
            'cpfCnpj' => $cpfCnpj,
            'telefone_1' => $tel_1,
            'telefone_2' => $tel_2,

            // Informações do Cliente
            'cliente_nome' => $cliente->nome ?? '',
            'cliente_cpf_cnpj' => $cliente->cpf_cnpj ?? '',
            'cliente_rg' => $cliente->rg ?? '',
            'cliente_endereco' => $cliente->endereco ?? '',
            'cliente_cidade' => $cliente->Cidade->nome ?? '',
            'cliente_estado' => $cliente->Estado->nome ?? '',
            'cliente_email' => $cliente->email ?? '',
            'cliente_cnh' => $cliente->cnh ?? '',
            'cliente_telefone_1' => $cliente->telefone_1 ?? '',
            'cliente_telefone_2' => $cliente->telefone_2 ?? '',
            'cliente_validade_cnh' => $cliente->validade_cnh ? Carbon::parse($cliente->validade_cnh)->format('d/m/Y') : '',
            'cliente_orgao_emissor' => $cliente->exp_rg ?? '',
            'cliente_uf_rg' => $cliente->Estado->nome ?? '',
            'cliente_data_nascimento' => $cliente_data_nascimento,
            'cliente_rede_social' => $cliente->rede_social ?? '',

            // Dados do Veículo
            'veiculo_marca' => $veiculo->Marca->nome ?? '',
            'veiculo_modelo' => $veiculo->modelo ?? '',
            'veiculo_placa' => $veiculo->placa ?? '',
            'veiculo_chassi' => $veiculo->chassi ?? '',
            'veiculo_ano' => $veiculo->ano ?? '',
            'veiculo_cor' => $veiculo->cor ?? '',
            'veiculo_renavam' => $veiculo->renavam ?? '',
            'veiculo_km_saida' => $locacao->km_saida ?? '',

            // Datas e Valores da Locação
            'data_saida' => $data_saida_fmt,
            'hora_saida' => $locacao->hora_saida ?? '',
            'data_retorno' => $data_retorno_fmt,
            'hora_retorno' => $locacao->hora_retorno ?? '',
            'qtd_diarias' => $locacao->qtd_diarias ?? '',
            'qtd_semanas' => $locacao->qtd_semanas ?? '',
            'valor_total' => $valor_total,
            'valor_desconto' => $valor_desconto,
            'valor_total_desconto' => $valor_total_desconto,
            'valor_caucao' => $valor_caucao,
            'data_hoje' => $data_hoje,
            'observacoes' => $locacao->obs ?? '',

            // Informações adicionais
            'testemunha_1' => $locacao->testemunha_1 ?? '',
            'testemunha_1_rg' => $locacao->testemunha_1_rg ?? '',
            'testemunha_2' => $locacao->testemunha_2 ?? '',
            'testemunha_2_rg' => $locacao->testemunha_2_rg ?? '',
            'fiador' => $locacao->fiador ?? '',
            'dados_fiador' => $locacao->dados_fiador ?? '',
        ];

        // Preparar e renderizar o conteúdo do contrato (HTML) preenchendo as variáveis usando Blade
        $rawTemplate = $contrato->descricao ?? '';

        // Muitos editors convertem as chaves ou adicionam @ para escapar. Normalizamos:
        // Primeiro remover @ prefixo
        $rawTemplate = str_replace('@{{', '{{', $rawTemplate);
        $rawTemplate = str_replace('@{{{', '{{{', $rawTemplate);

        // Decodificar entidades HTML
        $rawTemplate = html_entity_decode($rawTemplate, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalizar entidades numéricas remanescentes
        $rawTemplate = str_replace('&#123;&#123;', '{{', $rawTemplate);
        $rawTemplate = str_replace('&#125;&#125;', '}}', $rawTemplate);
        $rawTemplate = str_replace('&#x7B;&#x7B;', '{{', $rawTemplate);
        $rawTemplate = str_replace('&#x7D;&#x7D;', '}}', $rawTemplate);

        try {
            // Tentar renderizar com Blade
            $filledHtml = Blade::render($rawTemplate, $data);
        } catch (\Throwable $e) {
            // Se falhar, fazer substituição manual com regex
            Log::warning('Blade::render falhou, usando substituição manual: ' . $e->getMessage());

            $filledHtml = $rawTemplate;

            // Substituir {{ $var }} por valor direto
            foreach ($data as $key => $value) {
                if (is_scalar($value) || is_null($value)) {
                    // Para variáveis de HTML, não usar htmlspecialchars
                    if (in_array($key, ['logo_html', 'logo_raw'])) {
                        $filledHtml = preg_replace(
                            '/\{\{\s*\$' . preg_quote($key) . '\s*\}\}/',
                            (string)$value,
                            $filledHtml
                        );
                    } else {
                        $filledHtml = preg_replace(
                            '/\{\{\s*\$' . preg_quote($key) . '\s*\}\}/',
                            htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'),
                            $filledHtml
                        );
                    }
                }
            }

            // Substituir {{ $objeto->propriedade }}
            if (isset($data['cliente']) && is_object($data['cliente'])) {
                foreach ($data['cliente']->getAttributes() as $attr => $value) {
                    $filledHtml = preg_replace(
                        '/\{\{\s*\$cliente->' . preg_quote($attr) . '\s*\}\}/',
                        htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'),
                        $filledHtml
                    );
                }
            }
            if (isset($data['veiculo']) && is_object($data['veiculo'])) {
                foreach ($data['veiculo']->getAttributes() as $attr => $value) {
                    $filledHtml = preg_replace(
                        '/\{\{\s*\$veiculo->' . preg_quote($attr) . '\s*\}\}/',
                        htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'),
                        $filledHtml
                    );
                }
            }
        }

        // Debug: Log das variáveis principais
        Log::debug('Template renderizado - Logo:', [
            'logo_path' => $parametros->logo ?? 'Nenhuma',
            'logo_html' => $logo_html ? 'Definida' : 'Vazia',
            'logo_raw' => $logo_raw ? 'Definida' : 'Vazia',
            'template_has_logo_html' => strpos($rawTemplate, 'logo_html') !== false,
            'template_has_logo_raw' => strpos($rawTemplate, 'logo_raw') !== false,
            'filled_has_logo_html' => strpos($filledHtml, 'logo_html') !== false,
            'filled_has_logo_raw' => strpos($filledHtml, 'logo_raw') !== false,
        ]);

        // Gerar PDF a partir do HTML preenchido
        $pdf = Pdf::loadHTML($filledHtml)
            ->setPaper('a4')
            ->setOption('encoding', 'UTF-8');
        return $pdf->stream("contrato_locacao_{$locacao->id}.pdf");
    }
}