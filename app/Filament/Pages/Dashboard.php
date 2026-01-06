<?php

namespace App\Filament\Pages;

use App\Models\ContasPagar;
use App\Models\ContasReceber;
use App\Models\Veiculo;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Support\Facades\FilamentIcon;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Support\Facades\Route;
use Filament\Notifications\Notification;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $routePath = '/';
    protected static ?int $navigationSort = -2;
    
    /**
     * @var view-string
     */
    protected static string $view = 'filament-panels::pages.dashboard';

    public function mount(): void
    {
        $this->inicializarParametros();
        $this->verificarNotificacaoUsuario();
        $this->verificarNotificacoesSistema();
    }

    private function inicializarParametros(): void
    {
        if (DB::table('parametros')->count() == 0) {
            DB::table('parametros')->insert([
                'nome_empresa' => 'Minha Empresa',
                'endereco_completo' => '',
                'telefone' => '',
                'email' => '',
                'cpf_cnpj' => '',
                'redes_sociais' => '',
                'logo' => '',
                'versao_sistema' => '1.0.0',
                'data_atualizacao' => now(),
                'detalhe_atualizacao' => '',
                'ativo' => true,
                'notificar_usuario' => '',
                'ativar_notificacoes' => false,
                'ativar_notificacao_usuario' => false,
                'catalogo' => false,
            ]);
        }
    }

    private function verificarNotificacaoUsuario(): void
    {
        if (DB::table('parametros')->where('ativar_notificacao_usuario', '=', 1)->exists()) {
            Notification::make()
                ->title('Informação do Administrador')
                ->body(DB::table('parametros')->value('notificar_usuario'))
                ->persistent()
                ->danger()
                ->send();
        }
    }

    private function verificarNotificacoesSistema(): void
    {
        if (!DB::table('parametros')->where('ativar_notificacoes', '=', 1)->exists()) {
            return;
        }

        $this->verificarAlertasVeiculos();
        $this->verificarContasReceber();
        $this->verificarContasPagar();
    }

    private function verificarAlertasVeiculos(): void
    {
        $veiculos = Veiculo::where('status_alerta', 1)
            ->where('status', 1)
            ->get(['id', 'modelo', 'placa', 'km_atual', 'prox_troca_oleo', 'aviso_troca_oleo', 
                   'prox_troca_filtro', 'aviso_troca_filtro', 'prox_troca_correia', 
                   'aviso_troca_correia', 'prox_troca_pastilha', 'aviso_troca_pastilha']);

        foreach ($veiculos as $veiculo) {
            $this->verificarAlertaManutencao($veiculo, 'óleo', 'prox_troca_oleo', 'aviso_troca_oleo');
            $this->verificarAlertaManutencao($veiculo, 'filtro', 'prox_troca_filtro', 'aviso_troca_filtro');
            $this->verificarAlertaManutencao($veiculo, 'correia', 'prox_troca_correia', 'aviso_troca_correia');
            $this->verificarAlertaManutencao($veiculo, 'pastilha', 'prox_troca_pastilha', 'aviso_troca_pastilha');
        }
    }

    private function verificarAlertaManutencao($veiculo, $tipo, $campoProx, $campoAviso): void
    {
        $diferenca = $veiculo->$campoProx - $veiculo->km_atual;
        
        if ($diferenca <= $veiculo->$campoAviso && $diferenca > 0) {
            Notification::make()
                ->title("ATENÇÃO: Veículos com troca de {$tipo} próxima. Faltam {$diferenca} Km.")
                ->body("Veículo: {$veiculo->modelo} Placa: {$veiculo->placa}")
                ->danger()
                ->send();
        }
    }

    private function verificarContasReceber(): void
    {
        $contas = ContasReceber::where('status', '0')
            ->with('cliente:id,nome')
            ->get(['id', 'cliente_id', 'valor_parcela', 'data_vencimento']);
        
        $this->processarContas($contas, 'receber', 'cliente', 'cliente');
    }

    private function verificarContasPagar(): void
    {
        $contas = ContasPagar::where('status', '0')
            ->with('fornecedor:id,nome')
            ->get(['id', 'fornecedor_id', 'valor_parcela', 'data_vencimento']);
        
        $this->processarContas($contas, 'pagar', 'fornecedor', 'fornecedor');
    }

    private function processarContas($contas, $tipo, $relacionamento, $entidade): void
    {
        $hoje = Carbon::today();
        
        foreach ($contas as $conta) {
            $dataVencimento = Carbon::parse($conta->data_vencimento);
            $qtdDias = $hoje->diffInDays($dataVencimento, false);
            
            if ($qtdDias > 3) continue;
            
            $nome = $conta->$relacionamento->nome ?? 'Desconhecido';
            $valor = number_format($conta->valor_parcela, 2, ',', '.');
            $dataFormatada = $dataVencimento->format('d/m/Y');
            
            $this->enviarNotificacaoConta($tipo, $qtdDias, $nome, $valor, $dataFormatada, $entidade);
        }
    }

    private function enviarNotificacaoConta($tipo, $qtdDias, $nome, $valor, $dataFormatada, $entidade): void
    {
        $titulo = "ATENÇÃO: Conta a {$tipo}";
        
        if ($qtdDias <= 3 && $qtdDias > 0) {
            $titulo .= ' com vencimento próximo.';
            $tipoNotificacao = 'success';
        } elseif ($qtdDias == 0) {
            $titulo .= ' com vencimento para hoje.';
            $tipoNotificacao = 'warning';
        } else {
            $titulo .= ' vencida.';
            $tipoNotificacao = 'danger';
        }
        
        Notification::make()
            ->title($titulo)
            ->body("Do {$entidade} <b>{$nome}</b> no valor de R$ <b>{$valor}</b> com vencimento em <b>{$dataFormatada}</b>.")
            ->{$tipoNotificacao}()
            ->send();
    }

    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ??
            static::$title ??
            __('filament-panels::pages/dashboard.title');
    }

    public static function getNavigationIcon(): ?string
    {
        return static::$navigationIcon
            ?? FilamentIcon::resolve('panels::pages.dashboard.navigation-item')
            ?? (Filament::hasTopNavigation() ? 'heroicon-m-home' : 'heroicon-o-home');
    }

    public static function routes(Panel $panel): void
    {
        Route::get(static::getRoutePath(), static::class)
            ->middleware(static::getRouteMiddleware($panel))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($panel))
            ->name(static::getSlug());
    }

    public static function getRoutePath(): string
    {
        return static::$routePath;
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return Filament::getWidgets();
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getVisibleWidgets(): array
    {
        return $this->filterVisibleWidgets($this->getWidgets());
    }

    /**
     * @return int | string | array<string, int | string | null>
     */
    public function getColumns(): int | string | array
    {
        return 2;
    }

    public function getTitle(): string | Htmlable
    {
        return static::$title ?? __('filament-panels::pages/dashboard.title');
    }
}