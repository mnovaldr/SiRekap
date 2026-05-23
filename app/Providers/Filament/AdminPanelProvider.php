<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Enums\ThemeMode;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Blade;
use Filament\View\PanelsRenderHook;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => Blade::render(<<<'HTML'
                    @verbatim
                    <style>
                        /* ===== TRANSPARENT NAVBAR (TOPBAR) ===== */
                        .fi-topbar,
                        .fi-topbar-header,
                        .fi-topbar-header-container,
                        .fi-topbar nav,
                        .fi-topbar > nav,
                        .fi-topbar > div,
                        header.fi-topbar,
                        [class*="fi-topbar"] {
                            background-color: rgba(255, 255, 255, 0.7) !important;
                            background: rgba(255, 255, 255, 0.7) !important;
                            backdrop-filter: blur(12px) !important;
                            -webkit-backdrop-filter: blur(12px) !important;
                            box-shadow: none !important;
                        }
                        .dark .fi-topbar,
                        .dark .fi-topbar-header,
                        .dark .fi-topbar-header-container,
                        .dark .fi-topbar nav,
                        .dark .fi-topbar > nav,
                        .dark .fi-topbar > div,
                        .dark header.fi-topbar,
                        .dark [class*="fi-topbar"] {
                            background-color: rgba(15, 23, 42, 0.7) !important;
                            background: rgba(15, 23, 42, 0.7) !important;
                        }
                        .fi-topbar,
                        .fi-topbar > nav {
                            border-bottom: 1px solid rgba(226, 232, 240, 0.8) !important;
                        }
                        .dark .fi-topbar,
                        .dark .fi-topbar > nav {
                            border-bottom: 1px solid rgba(51, 65, 85, 0.5) !important;
                        }

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
                        @keyframes bar-shimmer {
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
                        
                        [data-si-loading="true"] { 
                            pointer-events: none !important; 
                            opacity: 0.8 !important; 
                            position: relative !important; 
                            color: transparent !important;
                            min-width: 40px;
                        }
                        [data-si-loading="true"]::after {
                            content: "";
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            width: 1.2rem;
                            height: 1.2rem;
                            margin-top: -0.6rem;
                            margin-left: -0.6rem;
                            border: 2px solid rgba(255,255,255,0.3);
                            border-top-color: currentColor;
                            border-radius: 50%;
                            animation: si-spin 0.6s linear infinite;
                            z-index: 10;
                        }
                        .si-btn-cancel[data-si-loading="true"]::after { border-top-color: #475569; }
                        
                        @keyframes si-spin {
                            to { transform: rotate(360deg); }
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
                        @keyframes pulse-fade {
                            0%, 100% { opacity: 0.4; }
                            50% { opacity: 0.8; }
                        }

                        /* ===== LIVEWIRE LOADING OVERLAY ===== */
                        [wire\:loading] { transition: opacity 0.15s ease; }

                        /* ===== PAGE TRANSITION ===== */
                        .fi-main {
                            animation: page-in 0.2s ease;
                        }
                        @keyframes page-in {
                            from { opacity: 0; transform: translateY(6px); }
                            to   { opacity: 1; transform: translateY(0); }
                        }

                        /* Premium Notification Styling */
                        .fi-no-notification {
                            border-radius: 16px !important;
                            border: 1px solid rgba(226, 232, 240, 0.8) !important;
                            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
                            backdrop-filter: blur(12px) !important;
                            background-color: rgba(255, 255, 255, 0.9) !important;
                            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                        }
                        .dark .fi-no-notification {
                            background-color: rgba(30, 41, 59, 0.8) !important;
                            border: 1px solid rgba(71, 85, 105, 0.5) !important;
                        }
                        .fi-no-notification-title {
                            font-weight: 800 !important;
                            letter-spacing: -0.02em !important;
                            color: #1e293b !important;
                        }
                        .dark .fi-no-notification-title { color: #f8fafc !important; }

                        /* Custom Premium Modal Style */
                        #si-premium-modal {
                            position: fixed;
                            inset: 0;
                            z-index: 999999;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            padding: 16px;
                            opacity: 0;
                            pointer-events: none;
                            transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                        }
                        #si-premium-modal.show { opacity: 1; pointer-events: auto; }
                        #si-premium-modal-overlay {
                            position: absolute;
                            inset: 0;
                            background: rgba(15, 23, 42, 0.4);
                            backdrop-filter: blur(6px);
                            -webkit-backdrop-filter: blur(6px);
                        }
                        #si-premium-modal-content {
                            position: relative;
                            background: white;
                            width: 100%;
                            max-width: 420px;
                            border-radius: 24px;
                            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
                            padding: 40px 32px;
                            text-align: center;
                            transform: scale(0.85) translateY(20px);
                            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
                        }
                        .dark #si-premium-modal-content { 
                            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); 
                            border: 1px solid rgba(255,255,255,0.1); 
                        }
                        #si-premium-modal.show #si-premium-modal-content { transform: scale(1) translateY(0); }
                        
                        .si-modal-icon {
                            width: 72px; height: 72px;
                            margin: 0 auto 24px;
                            background: #fee2e2;
                            color: #ef4444;
                            border-radius: 20px;
                            display: flex; align-items: center; justify-content: center;
                            transform: rotate(-5deg);
                            box-shadow: 0 8px 16px -4px rgba(239, 68, 68, 0.2);
                        }
                        .si-modal-title { font-size: 1.5rem; font-weight: 900; color: #0f172a; margin-bottom: 12px; letter-spacing: -0.03em; }
                        .dark .si-modal-title { color: white; }
                        .si-modal-body { font-size: 1rem; color: #475569; margin-bottom: 32px; line-height: 1.6; }
                        .dark .si-modal-body { color: #94a3b8; }
                        
                        .si-modal-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
                        .si-btn {
                            padding: 12px 20px; border-radius: 14px; font-weight: 800; font-size: 0.95rem;
                            cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: none;
                        }
                        .si-btn-cancel { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
                        .dark .si-btn-cancel { background: #334155; color: #cbd5e1; border-color: #475569; }
                        .si-btn-cancel:hover { background: #e2e8f0; transform: translateY(-2px); }
                        .si-btn-confirm { 
                            background: linear-gradient(135deg, #ef4444, #b91c1c); 
                            color: white; 
                            box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.3); 
                        }
                        .si-btn-confirm:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 20px 25px -5px rgba(239, 68, 68, 0.4); }
                        .si-btn-primary { 
                            background: linear-gradient(135deg, #4f46e5, #4338ca); 
                            color: white; 
                            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3); 
                        }
                        .si-btn-primary:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.4); }
                    </style>

                    <!-- Loading Bar Element -->
                    <div id="si-loading-bar"></div>

                    <!-- Premium Modal Template -->
                    <div id="si-premium-modal">
                        <div id="si-premium-modal-overlay"></div>
                        <div id="si-premium-modal-content">
                            <div class="si-modal-icon" id="si-modal-icon-container">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <h3 class="si-modal-title" id="si-modal-title">Konfirmasi</h3>
                            <p class="si-modal-body" id="si-modal-body">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
                            <div class="si-modal-actions" id="si-modal-actions">
                                <button class="si-btn si-btn-cancel" id="si-modal-btn-cancel">Batal</button>
                                <button class="si-btn si-btn-confirm" id="si-modal-btn-confirm">Ya, Lanjut</button>
                            </div>
                        </div>
                    </div>

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
                                
                                // Unlock UI and buttons immediately
                                document.body.classList.remove("si-processing");
                                window.siProcessing = false;
                                document.querySelectorAll("[data-si-loading]").forEach(el => {
                                    el.removeAttribute("data-si-loading");
                                    if (el.tagName === "BUTTON") el.disabled = false;
                                });

                                setTimeout(function() {
                                    bar.style.width = "0%";
                                    bar.style.opacity = "0";
                                }, 400);
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
                                const trigger = e.target.closest("a, button");
                                if (!trigger) return;

                                if (window.siProcessing) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    return false;
                                }

                                // Auto-trigger processing for regular navigation links and non-ajax buttons
                                if (trigger.tagName === "A" && trigger.href && !trigger.href.includes("#") && !trigger.getAttribute("target") && !trigger.hasAttribute("wire:click") && !trigger.hasAttribute("wire:navigate")) {
                                    startBar();
                                    trigger.setAttribute("data-si-loading", "true");
                                }
                            }, true);

                            // Form submits
                            document.addEventListener("submit", function(e) {
                                if (window.siProcessing) {
                                    e.preventDefault();
                                    return false;
                                }
                                if (e.target.tagName === "FORM") {
                                    // Don't start bar for logout as it's handled separately
                                    const action = e.target.getAttribute("action") || "";
                                    if (action.indexOf("logout") !== -1) return;

                                    startBar();
                                    // Disable all submit buttons in this form
                                    const btn = e.target.querySelector('button[type="submit"]');
                                    if (btn) {
                                        btn.setAttribute("data-si-loading", "true");
                                        setTimeout(() => btn.disabled = true, 50);
                                    }
                                }
                            });

                            /* ===== PREMIUM MODAL SYSTEM ===== */
                            const modal = document.getElementById("si-premium-modal");
                            const mTitle = document.getElementById("si-modal-title");
                            const mBody = document.getElementById("si-modal-body");
                            const mConfirm = document.getElementById("si-modal-btn-confirm");
                            const mCancel = document.getElementById("si-modal-btn-cancel");

                            window.siConfirm = function(title, body, type = "danger", confirmText = "Ya, Lanjut") {
                                return new Promise((resolve) => {
                                    mTitle.textContent = title;
                                    mBody.textContent = body;
                                    mConfirm.className = "si-btn " + (type === "danger" ? "si-btn-confirm" : "si-btn-primary");
                                    mConfirm.textContent = confirmText;
                                    
                                    // Reset buttons
                                    mConfirm.disabled = false;
                                    mConfirm.removeAttribute("data-si-loading");
                                    mCancel.disabled = false;
                                    
                                    const onConfirm = () => {
                                        mConfirm.setAttribute("data-si-loading", "true");
                                        mConfirm.disabled = true;
                                        mCancel.disabled = true;
                                        
                                        // Delay closing modal slightly if it's a redirect to show loading
                                        setTimeout(() => modal.classList.remove("show"), 400);
                                        
                                        mConfirm.removeEventListener("click", onConfirm);
                                        mCancel.removeEventListener("click", onCancel);
                                        resolve(true);
                                    };
                                    
                                    const onCancel = () => {
                                        modal.classList.remove("show");
                                        mConfirm.removeEventListener("click", onConfirm);
                                        mCancel.removeEventListener("click", onCancel);
                                        resolve(false);
                                    };
                                    
                                    mConfirm.addEventListener("click", onConfirm);
                                    mCancel.addEventListener("click", onCancel);
                                    modal.classList.add("show");
                                });
                            };

                            /* ===== OVERRIDE BROWSER DEFAULTS ===== */
                            window.confirm = function(msg) {
                                // Since window.confirm is sync and siConfirm is async, we can't easily override it for wire:confirm
                                // without changing the calling code or using a more complex hack.
                                // For now, we will use it for signout and manual calls.
                                console.warn("Native confirm() called. Use window.siConfirm instead for premium experience.");
                                return true; 
                            };

                            /* ===== HANDLE 419 EXPIRED SESSION ===== */
                            document.addEventListener("livewire:error", function(e) {
                                if (e.detail && e.detail.status === 419) {
                                    e.preventDefault();
                                    window.location.href = "/admin/login?logged_out=1&expired=1";
                                }
                            });
                        })();
                    </script>
                    @endverbatim
HTML
                ),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => Blade::render(<<<'HTML'
                    @verbatim
                    <script>
                    (function() {
                        /* ===== OVERRIDE FILAMENT LOGOUT BUTTON ===== */
                        function patchLogoutButton() {
                            var forms = document.querySelectorAll("form");
                            forms.forEach(function(form) {
                                var action = form.getAttribute("action") || "";
                                if (action.indexOf("logout") !== -1 && !form.dataset.patched) {
                                    form.dataset.patched = "1";
                                    form.addEventListener("submit", async function(e) {
                                         if (window.siProcessing) return;
                                         e.preventDefault();
                                         e.stopPropagation();
                                         
                                         // Use premium modal instead of direct logout
                                         const confirmed = await window.siConfirm(
                                             "Konfirmasi Keluar", 
                                             "Apakah Anda yakin ingin keluar dari sistem SiREKAP MCC? Sesi aktif Anda akan segera berakhir.",
                                             "danger",
                                             "Ya, Keluar"
                                         );

                                         if (confirmed) {
                                             if (typeof startBar === "function") startBar();
                                             window.location.href = "/admin/do-logout";
                                         }
                                     });
                                }
                            });
                        }

                        /* Run on load and after any Livewire navigation */
                        document.addEventListener("DOMContentLoaded", patchLogoutButton);
                        document.addEventListener("livewire:navigated", patchLogoutButton);
                        setTimeout(patchLogoutButton, 500);
                        setTimeout(patchLogoutButton, 1500);
                        setInterval(patchLogoutButton, 3000); // More aggressive patching for dynamic menus

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
                            banner.innerHTML = "⚠️ Sesi Anda akan berakhir dalam <span id='si-countdown'>2:00</span> karena tidak ada aktivitas. <button onclick='resetIdleTimer()' style='margin-left:12px;background:white;color:#dc2626;border:none;border-radius:6px;padding:4px 12px;cursor:pointer;font-weight:700;'>Tetap Login</button> <button onclick='window.location.href=\"/admin/do-logout\"' style='margin-left:6px;background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.5);border-radius:6px;padding:4px 12px;cursor:pointer;font-weight:600;'>Keluar Sekarang</button>";
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
                        @keyframes slideDown { from { transform: translateY(-100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
                    </style>
                    @endverbatim
HTML
                ),
            )
            ->renderHook(
                PanelsRenderHook::FOOTER,
                fn () => Blade::render(<<<'HTML'
                    <div class="py-6 text-center text-xs text-gray-500 dark:text-slate-400 mt-auto border-t border-gray-100 dark:border-slate-800/50">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div>
                                &copy; {{ date('Y') }} <span class="font-semibold text-gray-700 dark:text-slate-300">SiREKAP MCC</span>. All rights reserved.
                            </div>
                            <div class="flex items-center gap-1.5 text-gray-400 dark:text-slate-500">
                                <span>Dibuat oleh</span>
                                <a href="https://mnovaldr.dev" target="_blank" rel="noopener noreferrer" class="font-semibold bg-gradient-to-r from-indigo-500 via-purple-500 to-teal-500 bg-clip-text text-transparent dark:from-indigo-400 dark:to-teal-400 hover:opacity-80 transition-opacity">
                                    Muhammad Noval Dwi Ramadhani
                                </a>
                            </div>
                        </div>
                    </div>
HTML
                ),
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
                \App\Filament\Widgets\SdgsDistributionWidget::class,
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
            ->defaultThemeMode(ThemeMode::Light)
            ->sidebarCollapsibleOnDesktop()
            ->databaseNotifications();
    }
}
