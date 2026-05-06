<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <style>
                        /* Scrollbar */
                        ::-webkit-scrollbar { width: 5px; height: 5px; }
                        ::-webkit-scrollbar-track { background: transparent; }
                        ::-webkit-scrollbar-thumb { background: rgba(203, 213, 225, 0.5); border-radius: 10px; }
                        ::-webkit-scrollbar-thumb:hover { background: rgba(148, 163, 184, 0.8); }
                        .dark ::-webkit-scrollbar-thumb { background: rgba(51, 65, 85, 0.5); }
                        .dark ::-webkit-scrollbar-thumb:hover { background: rgba(71, 85, 105, 0.8); }

                        /* ===== PREMIUM LOADING BAR ===== */
                        #si-loading-bar {
                            position: fixed;
                            top: 0; left: 0;
                            width: 0%;
                            height: 3px;
                            background: linear-gradient(90deg, #4f46e5, #7c3aed, #0d9488);
                            background-size: 200% 100%;
                            z-index: 99999;
                            transition: width 0.3s ease, opacity 0.4s ease;
                            border-radius: 0 2px 2px 0;
                            animation: bar-shimmer 1.5s infinite linear;
                            box-shadow: 0 0 8px rgba(79, 70, 229, 0.6), 0 0 20px rgba(79, 70, 229, 0.3);
                        }
                        @@keyframes bar-shimmer {
                            0%   { background-position: 200% 0; }
                            100% { background-position: -200% 0; }
                        }
                        #si-loading-bar.done {
                            width: 100% !important;
                            opacity: 0;
                            transition: width 0.2s ease, opacity 0.5s ease 0.15s;
                        }

                        /* ===== SINGLE CLICK PROTECTION ===== */
                        .si-processing { cursor: wait !important; }
                        .si-processing a, 
                        .si-processing button { pointer-events: none !important; opacity: 0.7 !important; }
                        [data-si-loading="true"] { pointer-events: none !important; opacity: 0.6 !important; position: relative; }
                        [data-si-loading="true"]::after {
                            content: "";
                            position: absolute;
                            inset: 0;
                            background: rgba(255,255,255,0.1);
                            cursor: wait;
                        }

                        /* ===== WIDGET SKELETON LOADING ===== */
                        .fi-wi-stats-overview-stat,
                        .fi-chart-widget,
                        .fi-ta-table {
                            transition: opacity 0.3s ease;
                        }
                        .fi-widget-lazy-loading-indicator {
                            animation: pulse-fade 1.2s ease-in-out infinite;
                        }
                        @@keyframes pulse-fade {
                            0%, 100% { opacity: 0.4; }
                            50% { opacity: 0.8; }
                        }

                        /* ===== LIVEWIRE LOADING OVERLAY ===== */
                        [wire\:loading] { transition: opacity 0.15s ease; }

                        /* ===== PAGE TRANSITION ===== */
                        .fi-main {
                            animation: page-in 0.2s ease;
                        }
                        @@keyframes page-in {
                            from { opacity: 0; transform: translateY(6px); }
                            to   { opacity: 1; transform: translateY(0); }
                        }

                        /* Premium Notification Styling */
                        .fi-no-notification {
                            border-radius: 12px !important;
                            border: 1px solid rgba(226, 232, 240, 0.8) !important;
                            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
                            backdrop-filter: blur(8px) !important;
                            background-color: rgba(255, 255, 255, 0.95) !important;
                        }
                        .dark .fi-no-notification {
                            background-color: rgba(15, 23, 42, 0.9) !important;
                            border: 1px solid rgba(51, 65, 85, 0.8) !important;
                        }
                        .fi-no-notification-title {
                            font-weight: 700 !important;
                            letter-spacing: -0.01em !important;
                        }
                    </style>

                    {{-- Loading Bar Element --}}
                    <div id="si-loading-bar"></div>

                    <script>
                        (function() {
                            const bar = document.getElementById("si-loading-bar");
                            if (!bar) return;

                            let timer = null;
                            let progress = 0;
                            window.siProcessing = false;

                            function startBar() {
                                window.siProcessing = true;
                                document.body.classList.add("si-processing");
                                bar.classList.remove("done");
                                progress = 0;
                                bar.style.opacity = "1";
                                bar.style.width = "0%";
                                clearInterval(timer);
                                timer = setInterval(function() {
                                    if (progress < 85) {
                                        progress += (85 - progress) * 0.12;
                                        bar.style.width = progress + "%";
                                    }
                                }, 80);
                            }

                            function finishBar() {
                                clearInterval(timer);
                                bar.classList.add("done");
                                setTimeout(function() {
                                    bar.style.width = "0%";
                                    bar.style.opacity = "0";
                                    document.body.classList.remove("si-processing");
                                    window.siProcessing = false;
                                    // Remove individual button locks
                                    document.querySelectorAll("[data-si-loading]").forEach(el => {
                                        el.removeAttribute("data-si-loading");
                                        el.disabled = false;
                                    });
                                }, 700);
                            }

                            // Livewire events
                            document.addEventListener("livewire:navigate", startBar);
                            document.addEventListener("livewire:navigated", finishBar);
                            document.addEventListener("livewire:request", function(e) {
                                startBar();
                                // Try to find the element that triggered it and lock it
                                if (e.detail && e.detail.dispatchedBy) {
                                    e.detail.dispatchedBy.setAttribute("data-si-loading", "true");
                                }
                            });
                            document.addEventListener("livewire:commit", finishBar);

                            // Global Click Protector
                            document.addEventListener("click", function(e) {
                                if (window.siProcessing) {
                                    // If it is a link or button, prevent it
                                    const trigger = e.target.closest("a, button");
                                    if (trigger) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        return false;
                                    }
                                }
                            }, true);

                            // Form submits
                            document.addEventListener("submit", function(e) {
                                if (window.siProcessing) {
                                    e.preventDefault();
                                    return false;
                                }
                                if (e.target.tagName === "FORM") {
                                    startBar();
                                    // Disable all submit buttons in this form
                                    const btn = e.target.querySelector(\'button[type="submit"]\');
                                    if (btn) {
                                        btn.setAttribute("data-si-loading", "true");
                                        setTimeout(() => btn.disabled = true, 50);
                                    }
                                }
                            });
                        })();
                    </script>

                '),
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::BODY_END,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <script>
                    (function() {
                        /* ===== OVERRIDE FILAMENT LOGOUT BUTTON ===== */
                        /* Replace POST form logout with GET-based logout (no CSRF) */
                        function patchLogoutButton() {
                            var forms = document.querySelectorAll("form");
                            forms.forEach(function(form) {
                                var action = form.getAttribute("action") || "";
                                if (action.indexOf("logout") !== -1 && !form.dataset.patched) {
                                    form.dataset.patched = "1";
                                    form.addEventListener("submit", function(e) {
                                        if (window.siProcessing) return;
                                        e.preventDefault();
                                        e.stopPropagation();
                                        window.location.href = "/admin/do-logout";
                                    });
                                }
                            });
                        }

                        /* Run on load and after any Livewire navigation */
                        document.addEventListener("DOMContentLoaded", patchLogoutButton);
                        document.addEventListener("livewire:navigated", patchLogoutButton);
                        setTimeout(patchLogoutButton, 500);
                        setTimeout(patchLogoutButton, 1500);

                        /* ===== AUTO LOGOUT AFTER INACTIVITY (30 minutes) ===== */
                        var IDLE_TIMEOUT = 30 * 60 * 1000; // 30 menit
                        var WARNING_BEFORE = 2 * 60 * 1000; // tampilkan warning 2 menit sebelum
                        var idleTimer, warnTimer;
                        var warningShown = false;

                        function createWarningBanner() {
                            var existing = document.getElementById("si-idle-warning");
                            if (existing) return;
                            var banner = document.createElement("div");
                            banner.id = "si-idle-warning";
                            banner.style.cssText = "position:fixed;top:0;left:0;right:0;z-index:999999;background:linear-gradient(90deg,#dc2626,#b91c1c);color:white;text-align:center;padding:12px 16px;font-size:14px;font-weight:600;box-shadow:0 2px 8px rgba(0,0,0,0.3);animation:slideDown 0.3s ease;";
                            banner.innerHTML = "⚠️ Sesi Anda akan berakhir dalam <span id=\'si-countdown\'>2:00</span> karena tidak ada aktivitas. <button onclick=\'resetIdleTimer()\' style=\'margin-left:12px;background:white;color:#dc2626;border:none;border-radius:6px;padding:4px 12px;cursor:pointer;font-weight:700;\'>Tetap Login</button> <button onclick=\'window.location.href=\"/admin/do-logout\"\' style=\'margin-left:6px;background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.5);border-radius:6px;padding:4px 12px;cursor:pointer;font-weight:600;\'>Keluar Sekarang</button>";
                            document.body.prepend(banner);

                            var remaining = WARNING_BEFORE;
                            var countdownEl = document.getElementById("si-countdown");
                            var countdownInterval = setInterval(function() {
                                remaining -= 1000;
                                if (remaining <= 0) {
                                    clearInterval(countdownInterval);
                                } else if (countdownEl) {
                                    var m = Math.floor(remaining / 60000);
                                    var s = Math.floor((remaining % 60000) / 1000);
                                    countdownEl.textContent = m + ":" + (s < 10 ? "0" + s : s);
                                }
                            }, 1000);
                        }

                        function removeWarningBanner() {
                            var banner = document.getElementById("si-idle-warning");
                            if (banner) banner.remove();
                            warningShown = false;
                        }

                        window.resetIdleTimer = function() {
                            clearTimeout(idleTimer);
                            clearTimeout(warnTimer);
                            removeWarningBanner();
                            warnTimer = setTimeout(function() {
                                warningShown = true;
                                createWarningBanner();
                            }, IDLE_TIMEOUT - WARNING_BEFORE);
                            idleTimer = setTimeout(function() {
                                window.location.href = "/admin/do-logout";
                            }, IDLE_TIMEOUT);
                        };

                        /* Track user activity */
                        ["mousemove","keydown","click","scroll","touchstart","focus"].forEach(function(evt) {
                            document.addEventListener(evt, function() {
                                if (!warningShown) resetIdleTimer();
                            }, { passive: true });
                        });

                        /* Start the timer */
                        if (document.body.innerHTML.indexOf("filament") !== -1 || window.location.pathname.indexOf("/admin") !== -1) {
                            resetIdleTimer();
                        }
                    })();
                    </script>
                    <style>
                        @@keyframes slideDown { from { transform: translateY(-100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
                    </style>
                '),
            )
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->colors([
                'primary' => Color::hex('#4f46e5'), // Indigo
                'danger' => Color::hex('#e63946'),  
                'success' => Color::hex('#0d9488'), // Teal
                'gray' => Color::Slate,
            ])
            ->font('Inter')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\DuplicateAlert::class,
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\EventsChart::class,
                \App\Filament\Widgets\BidangDistributionChart::class,
                \App\Filament\Widgets\KomersialChart::class,
                \App\Filament\Widgets\TopJenisKegiatanChart::class,
                \App\Filament\Widgets\TopOrganisasiWidget::class,
                \App\Filament\Widgets\TopLokasiWidget::class,
                \App\Filament\Widgets\RekapBulananWidget::class,
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
            ->brandName('SiREKAP MCC')
            ->brandLogo('/images/logo.png')
            ->darkModeBrandLogo('/images/logo-white.png')
            ->brandLogoHeight('3rem')
            ->favicon('/favicon.png')
            ->sidebarCollapsibleOnDesktop()
            ->databaseNotifications();
    }
}
