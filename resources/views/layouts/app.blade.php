<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="APAR SRP">
    <meta name="theme-color" content="#15803d">
    <meta name="description" content="Sistem manajemen APAR PT Sinar Rimba Pasifik">

    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <title>@yield('title', 'APAR Management') — SRP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('head')
</head>
<body class="bg-gray-100 min-h-screen">

<div class="flex h-screen overflow-hidden">

    {{-- ===================== SIDEBAR ===================== --}}
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-30 flex flex-col w-64 bg-white shadow-xl
               -translate-x-full transition-transform duration-300 ease-in-out
               lg:static lg:translate-x-0 lg:shadow-lg">

        {{-- Brand — h-14 harus sama persis dengan header navbar --}}
        <div class="flex items-center gap-3 px-4 h-14 bg-green-700 text-white flex-shrink-0">
            <span class="text-2xl">🔥</span>
            <div class="min-w-0">
                <p class="font-bold text-sm leading-tight truncate">APAR Management</p>
                <p class="text-green-200 text-xs truncate">PT Sinar Rimba Pasifik</p>
            </div>
            {{-- Close button (mobile only) --}}
            <button id="sidebar-close" class="lg:hidden ml-auto p-1 rounded hover:bg-green-800 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Nav Links --}}
        <nav class="flex-1 overflow-y-auto py-3 px-2">
            <p class="text-xs text-gray-400 uppercase tracking-widest font-semibold px-3 mb-2">Menu</p>

            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium mb-1 transition-colors
                      {{ request()->routeIs('dashboard') ? 'bg-green-50 text-green-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
                @if(request()->routeIs('dashboard'))
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-green-600"></span>
                @endif
            </a>

            <a href="{{ route('apar.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium mb-1 transition-colors
                      {{ request()->routeIs('apar.*') ? 'bg-green-50 text-green-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <span>Daftar APAR</span>
                @if(request()->routeIs('apar.*'))
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-green-600"></span>
                @endif
            </a>

            <a href="{{ route('apar.maintenance.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium mb-1 transition-colors
                      {{ request()->routeIs('apar.maintenance.*') ? 'bg-green-50 text-green-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>History Maintenance</span>
                @if(request()->routeIs('apar.maintenance.*'))
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-green-600"></span>
                @endif
            </a>

            @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('users.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium mb-1 transition-colors
                      {{ request()->routeIs('users.*') ? 'bg-green-50 text-green-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>Manajemen User</span>
                @if(request()->routeIs('users.*'))
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-green-600"></span>
                @endif
            </a>
            @endif
        </nav>

        {{-- User + Logout (bottom of sidebar) --}}
        <div class="flex-shrink-0 border-t border-gray-200">
            {{-- User info --}}
            <div class="flex items-center gap-3 px-4 py-3">
                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                    <span class="text-green-700 font-bold text-sm uppercase">{{ substr(auth()->user()->username, 0, 1) }}</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ auth()->user()->username }}</p>
                    <p class="text-xs text-gray-400 capitalize">{{ auth()->user()->role }}</p>
                </div>
            </div>
            {{-- Logout button --}}
            <form method="POST" action="{{ route('logout') }}" class="px-3 pb-3">
                @csrf
                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-sm font-medium
                           text-red-600 hover:bg-red-50 transition-colors border border-red-200 hover:border-red-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- Overlay (mobile) --}}
    <div id="sidebar-overlay"
         class="hidden fixed inset-0 bg-black/50 z-20 lg:hidden backdrop-blur-sm"></div>

    {{-- ===================== MAIN AREA ===================== --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- Top Navbar --}}
        <header class="bg-green-700 text-white shadow-md z-10 flex-shrink-0">
            <div class="flex items-center gap-3 px-4 h-14">
                {{-- Hamburger (mobile / tablet) --}}
                <button id="sidebar-toggle"
                    class="lg:hidden p-1.5 rounded-lg hover:bg-green-800 focus:outline-none transition-colors flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                {{-- Page title (dynamic) --}}
                <div class="flex-1 min-w-0">
                    <h1 class="font-semibold text-base truncate">@yield('title', 'APAR Management')</h1>
                </div>

                {{-- Right: username badge on desktop --}}
                <div class="hidden sm:flex items-center gap-2 text-sm flex-shrink-0">
                    <span class="text-green-200 text-xs">{{ auth()->user()->username }}</span>
                    <span class="text-xs bg-green-900 px-2 py-0.5 rounded-full capitalize">{{ auth()->user()->role }}</span>
                </div>
            </div>
        </header>

        {{-- Scrollable content --}}
        <main class="flex-1 overflow-y-auto p-4 md:p-6">
            {{-- Flash messages --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-800 rounded-lg px-4 py-3 flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800 ml-3 font-bold text-lg leading-none">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-800 rounded-lg px-4 py-3 flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800 ml-3 font-bold text-lg leading-none">&times;</button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
    const toggle  = document.getElementById('sidebar-toggle');
    const close   = document.getElementById('sidebar-close');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    toggle?.addEventListener('click', openSidebar);
    close?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // Close sidebar on nav link click (mobile)
    sidebar?.querySelectorAll('a[href]').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 1024) closeSidebar();
        });
    });
</script>
@stack('scripts')

{{-- PWA: Install prompt banner --}}
<div id="pwa-banner"
     class="hidden fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-200 shadow-xl px-4 py-3
            flex items-center gap-3 sm:max-w-sm sm:left-4 sm:right-auto sm:bottom-4 sm:rounded-xl sm:border">
    <img src="/icons/icon.svg" class="w-10 h-10 rounded-xl flex-shrink-0" alt="APAR SRP">
    <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-gray-800">Pasang ke HP</p>
        <p class="text-xs text-gray-500 truncate">Akses lebih cepat tanpa browser</p>
    </div>
    <button id="pwa-install-btn"
        class="bg-green-700 hover:bg-green-800 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors flex-shrink-0">
        Pasang
    </button>
    <button id="pwa-dismiss-btn"
        class="text-gray-400 hover:text-gray-600 p-1 flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>

<script>
    // ── Service Worker ─────────────────────────────────────────────────────
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .catch(err => console.warn('SW register failed:', err));
        });
    }

    // ── PWA Install Prompt ─────────────────────────────────────────────────
    let deferredPrompt = null;
    const banner       = document.getElementById('pwa-banner');
    const installBtn   = document.getElementById('pwa-install-btn');
    const dismissBtn   = document.getElementById('pwa-dismiss-btn');

    // Don't show if user dismissed before
    const dismissed = localStorage.getItem('pwa-dismissed');

    window.addEventListener('beforeinstallprompt', e => {
        e.preventDefault();
        deferredPrompt = e;
        if (!dismissed) {
            setTimeout(() => banner.classList.remove('hidden'), 2000);
        }
    });

    installBtn?.addEventListener('click', async () => {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        if (outcome === 'accepted') banner.classList.add('hidden');
        deferredPrompt = null;
    });

    dismissBtn?.addEventListener('click', () => {
        banner.classList.add('hidden');
        localStorage.setItem('pwa-dismissed', '1');
    });

    window.addEventListener('appinstalled', () => {
        banner.classList.add('hidden');
        deferredPrompt = null;
    });
</script>
</body>
</html>
