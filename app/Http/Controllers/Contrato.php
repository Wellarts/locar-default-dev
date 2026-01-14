<?php

namespace App\Http\Controllers;

use App\Models\Locacao;
use Illuminate\Http\Request;
Use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Contrato as ContratoModel;
use Illuminate\Support\Facades\Blade;

class Contrato extends Controller
{
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
        }
        else {
            $cpfCnpj =  preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj_cpf);
        }

        //FORMATAR TELEFONE
         $tel_1 = $locacao->Cliente->telefone_1;
         $tel_2 = $locacao->Cliente->telefone_2;
       //  $tel_1 = " (".substr($tel_1, 0, 2).") ".substr($tel_1, 2, 5)."-".substr($tel_1, 7, 11);
       //  $tel_2 = " (".substr($tel_2, 0, 2).") ".substr($tel_2, 2, 5)."-".substr($tel_2, 7, 11);




         return pdf::loadView('pdf.locacao.contrato', compact(['locacao',
                                                        'dataAtual',
                                                        'cpfCnpj',
                                                        'tel_1',
                                                        'tel_2']))->stream();

       // return view('pdf.contrato', compact(['locacao']));


    }

    /**
     * Gerar contrato usando template do modelo Contrato preenchendo variáveis
     */
    public function printLocacaoContrato($locacaoId, $contratoId)
    {
        $locacao = Locacao::with(['Cliente', 'Veiculo', 'Veiculo.Marca', 'Cliente.Cidade', 'Cliente.Estado'])->findOrFail($locacaoId);
        $contrato = ContratoModel::findOrFail($contratoId);

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

        $data_saida_fmt = $locacao->data_saida ? Carbon::parse($locacao->data_saida)->format('d/m/Y') : '';
        $data_retorno_fmt = $locacao->data_retorno ? Carbon::parse($locacao->data_retorno)->format('d/m/Y') : '';

        $valor_total = isset($locacao->valor_total) ? number_format($locacao->valor_total, 2, ',', '.') : '';
        $valor_total_desconto = isset($locacao->valor_total_desconto) ? number_format($locacao->valor_total_desconto, 2, ',', '.') : '';
        $valor_caucao = isset($locacao->valor_caucao) ? number_format($locacao->valor_caucao, 2, ',', '.') : '';

        $data = [
            'locacao' => $locacao,
            'cliente' => $cliente,
            'veiculo' => $veiculo,
            'dataAtual' => $dataAtual,
            'cpfCnpj' => $cpfCnpj,
            'tel_1' => $tel_1,
            'tel_2' => $tel_2,

            // Conveniências
            'cliente_nome' => $cliente->nome ?? '',
            'cliente_cpf_cnpj' => $cliente->cpf_cnpj ?? '',
            'cliente_rg' => $cliente->rg ?? '',
            'cliente_endereco' => $cliente->endereco ?? '',
            'cliente_cidade' => $cliente->Cidade->nome ?? '',
            'cliente_estado' => $cliente->Estado->nome ?? '',
            'cliente_email' => $cliente->email ?? '',
            'cliente_cnh' => $cliente->cnh ?? '',
            'cliente_validade_cnh' => $cliente->validade_cnh ? Carbon::parse($cliente->validade_cnh)->format('d/m/Y') : '',

            'veiculo_marca' => $veiculo->Marca->nome ?? '',
            'veiculo_modelo' => $veiculo->modelo ?? '',
            'veiculo_placa' => $veiculo->placa ?? '',
            'veiculo_chassi' => $veiculo->chassi ?? '',
            'veiculo_ano' => $veiculo->ano ?? '',
            'veiculo_cor' => $veiculo->cor ?? '',
            'veiculo_renavam' => $veiculo->renavam ?? '',

            'data_saida' => $data_saida_fmt,
            'hora_saida' => $locacao->hora_saida ?? '',
            'data_retorno' => $data_retorno_fmt,
            'hora_retorno' => $locacao->hora_retorno ?? '',
            'qtd_diarias' => $locacao->qtd_diarias ?? '',
            'qtd_semanas' => $locacao->qtd_semanas ?? '',

            'valor_total' => $valor_total,
            'valor_total_desconto' => $valor_total_desconto,
            'valor_caucao' => $valor_caucao,

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
        $rawTemplate = html_entity_decode($rawTemplate, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Remover escape de Blade como @{{ ... }} => {{ ... }}
        $rawTemplate = str_replace('@{{', '{{', $rawTemplate);
        // Alguns editores transformam chaves em entidades numéricas
        $rawTemplate = str_replace('&#123;&#123;', '{{', $rawTemplate);
        $rawTemplate = str_replace('&#125;&#125;', '}}', $rawTemplate);

        try {
            $filledHtml = Blade::render($rawTemplate, $data);
        } catch (\Throwable $e) {
            // Em caso de erro ao renderizar Blade, retornar template decodificado para inspeção
            $filledHtml = $rawTemplate;
        }

        // Gerar PDF a partir do HTML preenchido
        $pdf = Pdf::loadHTML($filledHtml);
        return $pdf->stream("contrato_locacao_{$locacao->id}.pdf");
    }
}
