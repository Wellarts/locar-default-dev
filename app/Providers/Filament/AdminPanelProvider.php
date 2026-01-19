<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\ContasPagarHoje;
use App\Filament\Widgets\ContasReceberHoje;
use App\Filament\Widgets\LocacaoMes;
use App\Filament\Widgets\SomatorioLocacao;
use App\Filament\Widgets\StatsVeiculos;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->databaseNotifications()
            ->favicon(asset('img/logo.png'))
            ->brandLogo(asset('img/logo.png'))
            ->brandLogoHeight('4rem')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->resources([
                config('filament-logger.activity_resource')
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                //   Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                // SomatorioLocacao::class,
                // LocacaoMes::class,
                // ContasReceberHoje::class,
                // ContasPagarHoje::class,
                // StatsVeiculos::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                function (): string {
                    return Blade::render('@laravelPWA');
                }
            )
            ->renderHook(
                PanelsRenderHook::FOOTER,
                function () {
                    $parametro = \App\Models\Parametro::first();
                    $version = $parametro->versao_sistema ?? '1.0.0';
                   

                    return \Illuminate\Support\Facades\Blade::render('
            <footer class="border-t bg-gray-50/50">
                <div class="flex justify-end w-full px-4 py-3">
                    <div class="flex flex-col items-end gap-2 text-xs text-gray-600 sm:flex-row sm:gap-6 sm:items-center">
                        <span>© {{ date("Y") }} Wsys - Sistemas - Todos os direitos reservados</span>
                        <div class="flex items-center gap-3">
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                                </svg>
                                Versão Sistema {{ $version }}
                            </span>
                        </div>
                    </div>
                </div>
            </footer>
        ', [
                        'version' => $version,
                        
                    ]);
                }
            )

            ->navigationItems([
                NavigationItem::make('Manuais')
                    ->url('https://drive.google.com/drive/folders/1Pt9pkPfRKporD7Q3oLafggKGpHu4Xw46?usp=sharing', shouldOpenInNewTab: true)
                    ->icon('heroicon-s-question-mark-circle')
                    ->group('Ajuda')
                    ->sort(3),
            ]);
    }
}
