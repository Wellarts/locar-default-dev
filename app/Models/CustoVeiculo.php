<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CustoVeiculo extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'fornecedor_id',
        'veiculo_id',
        'km_atual',
        'data',
        'descricao',
        'valor',
        'financeiro',
        'pago',
        'parcelas',
        'categoria_id',
    ];

    protected $appends = ['data_formatada', 'valor_formatado', 'status_pago', 'status_financeiro'];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function getDataFormatadaAttribute()
    {
        return $this->data ? \Carbon\Carbon::parse($this->data)->format('d/m/Y') : null;
    }

    public function getValorFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }

    public function getFornecedorNomeAttribute()
    {
        return $this->fornecedor ? $this->fornecedor->nome : 'Não informado';
    }

    public function getStatusPagoAttribute()
    {
        return $this->pago == 1 ? 'Pago' : 'Pendente';
    }

    public function getStatusFinanceiroAttribute()
    {
        return $this->financeiro == 1 ? 'Lançado' : 'Não lançado';
    }

    public function getCategoriaNomeAttribute()
    {
        return $this->categoria ? $this->categoria->nome : 'Sem categoria';
    }

    public function getKmAtualFormatadoAttribute()
    {
        return $this->km_atual ? number_format($this->km_atual, 0, '', '.') . ' km' : null;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*']);
    }

    // Método auxiliar para relatório
    public static function getCustosAgrupadosPorVeiculo($filtros = [])
    {
        $query = self::with(['veiculo', 'fornecedor', 'categoria']);
        
        if (!empty($filtros['veiculo_id'])) {
            $query->where('veiculo_id', $filtros['veiculo_id']);
        }
        
        if (!empty($filtros['data_inicio'])) {
            $query->whereDate('data', '>=', $filtros['data_inicio']);
        }
        
        if (!empty($filtros['data_fim'])) {
            $query->whereDate('data', '<=', $filtros['data_fim']);
        }
        
        if (!empty($filtros['fornecedor_id'])) {
            $query->where('fornecedor_id', $filtros['fornecedor_id']);
        }
        
        if (!empty($filtros['categoria_id'])) {
            $query->where('categoria_id', $filtros['categoria_id']);
        }
        
        if (isset($filtros['pago'])) {
            $query->where('pago', $filtros['pago']);
        }
        
        if (isset($filtros['financeiro'])) {
            $query->where('financeiro', $filtros['financeiro']);
        }
        
        $custos = $query->get();
        
        $agrupados = [];
        foreach ($custos as $custo) {
            $veiculoId = $custo->veiculo_id;
            
            if (!isset($agrupados[$veiculoId])) {
                $agrupados[$veiculoId] = [
                    'veiculo' => $custo->veiculo,
                    'custo_total' => 0,
                    'contagem' => 0,
                    'descricoes' => []
                ];
            }
            
            $agrupados[$veiculoId]['custo_total'] += $custo->valor;
            $agrupados[$veiculoId]['contagem']++;
            $agrupados[$veiculoId]['descricoes'][] = [
                'id' => $custo->id,
                'descricao' => $custo->descricao,
                'valor' => $custo->valor,
                'data' => $custo->data,
                'data_formatada' => $custo->data_formatada,
                'km_atual' => $custo->km_atual,
                'km_atual_formatado' => $custo->km_atual_formatado,
                'financeiro' => $custo->financeiro,
                'pago' => $custo->pago,
                'parcelas' => $custo->parcelas,
                'status_pago' => $custo->status_pago,
                'status_financeiro' => $custo->status_financeiro,
                'fornecedor' => $custo->fornecedor_nome,
                'fornecedor_id' => $custo->fornecedor_id,
                'categoria' => $custo->categoria_nome,
                'categoria_id' => $custo->categoria_id
            ];
        }
        
        // Calcular média mensal
        foreach ($agrupados as &$item) {
            $item['custo_medio_mensal'] = $item['custo_total'] / max($item['contagem'], 1);
        }
        
        return $agrupados;
    }
}