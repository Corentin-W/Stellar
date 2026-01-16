{{-- resources/views/layouts/partials/astral-sidebar.blade.php --}}

<aside id="astral-sidebar" class="sidebar fixed inset-y-0 left-0 z-40 flex h-screen flex-col overflow-hidden transition-transform duration-300 -translate-x-full lg:translate-x-0 peer-checked:translate-x-0"
       style="background: rgba(0, 0, 0, 0.25); backdrop-filter: blur(30px) saturate(180%); -webkit-backdrop-filter: blur(30px) saturate(180%); border-right: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.1);">

    <!-- Logo Section -->
    <div class="p-3 border-b border-white/10">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-500 flex items-center justify-center flex-shrink-0 shadow-lg transition-transform hover:scale-105">
                <span class="font-black text-white text-lg tracking-tighter" style="font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', system-ui, sans-serif;">S</span>
            </div>
            <div class="min-w-0">
                <h1 class="font-bold text-white text-base tracking-tight" style="font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', system-ui, sans-serif;">STELLAR</h1>
                <p class="text-white/50 text-[10px] uppercase tracking-wider">Observatory</p>
            </div>
        </div>
    </div>

    <!-- Subscription & Credits Info (if authenticated) -->
    @auth
    @php
        $userSubscription = auth()->user()->subscription;
        $creditsBalance = auth()->user()->credits_balance ?? 0;
    @endphp
    <div class="px-3 mb-3 space-y-2">
        <!-- Subscription Card -->
        @if($userSubscription && $userSubscription->isActive())
        <div class="bg-gradient-to-br from-purple-500/20 to-pink-500/20 border border-purple-500/30 rounded-md p-2.5 transition-all hover:border-purple-500/50 hover:shadow-lg hover:shadow-purple-500/20 group">
            <div class="flex items-center justify-between mb-1.5">
                <div class="text-white/60 text-[10px] font-medium uppercase tracking-wide">Abonnement</div>
                <div class="text-base transition-transform group-hover:scale-110">{{ $userSubscription->getPlanBadge() }}</div>
            </div>
            <div class="text-white font-bold text-sm">{{ $userSubscription->getPlanName() }}</div>
            <div class="text-white/70 text-[10px] mt-0.5">{{ $userSubscription->credits_per_month }} crédits/mois</div>
        </div>
        @endif

        <!-- Credits Balance -->
        <div class="bg-gradient-to-r from-yellow-500/20 to-orange-500/20 border border-yellow-500/30 rounded-md p-2.5 transition-all hover:border-yellow-500/50 hover:shadow-lg hover:shadow-yellow-500/20 group cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-white font-bold text-base">{{ number_format($creditsBalance) }}</div>
                    <div class="text-white/60 text-[10px] uppercase tracking-wide">Crédits</div>
                </div>
                <div class="w-7 h-7 rounded-md bg-yellow-500/20 flex items-center justify-center transition-transform group-hover:scale-110 group-hover:rotate-12">
                    <svg class="w-3.5 h-3.5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    @endauth


    <!-- Quick Dashboard Button -->
    @php $onDashboard = request()->is('dashboard') || request()->is('dashboard/*') || request()->is('/'); @endphp
    @if(!$onDashboard)
    <div class="px-3 mb-2">
        <a href="{{ route('dashboard') }}"
           class="w-full inline-flex items-center justify-center px-2.5 py-1.5 rounded-md bg-blue-500/20 text-blue-200 hover:bg-blue-500/30 transition-all hover:scale-[1.02] active:scale-[0.98] group">
            <svg class="w-4 h-4 mr-1.5 transition-transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-xs font-medium">{{ __('app.sidebar.dashboard') }}</span>
        </a>
    </div>
    @endif

    <!-- Language Switcher (Compact) -->
    <div class="px-3 mb-2">
        <div class="flex items-center justify-between text-[10px]">
            <div class="flex items-center gap-1.5 text-white/50 uppercase tracking-wider">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M2 12h20M12 2a15 15 0 010 20M12 2a15 15 0 000 20" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path>
                </svg>
                <span>{{ __('app.language') }}</span>
            </div>
            <div class="flex items-center gap-0.5">
                <form method="POST" action="{{ route('locale.change', 'fr') }}">
                    @csrf
                    <button type="submit" class="px-1.5 py-0.5 rounded text-[10px] font-medium transition-all {{ app()->getLocale() === 'fr' ? 'bg-white/10 text-white' : 'text-white/60 hover:text-white hover:bg-white/5' }}">FR</button>
                </form>
                <form method="POST" action="{{ route('locale.change', 'en') }}">
                    @csrf
                    <button type="submit" class="px-1.5 py-0.5 rounded text-[10px] font-medium transition-all {{ app()->getLocale() === 'en' ? 'bg-white/10 text-white' : 'text-white/60 hover:text-white hover:bg-white/5' }}">EN</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 min-h-0 px-3 pb-3 space-y-0.5 overflow-y-auto">

        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}"
           class="sidebar-item {{ request()->is('dashboard') || request()->is('dashboard/*') || request()->is('/') ? 'active' : '' }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 14a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 14a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            <span class="ml-2 font-medium">{{ __('app.sidebar.dashboard') }}</span>
        </a>

        <!-- RoboTarget - Système principal -->
        <a href="{{ route('robotarget.index', ['locale' => app()->getLocale()]) }}"
           class="sidebar-item {{ request()->is('*/robotarget') || request()->is('*/robotarget/*') && !request()->is('*/robotarget/gallery') || request()->is('robotarget') || request()->is('robotarget/*') && !request()->is('robotarget/gallery') ? 'active' : '' }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
            </svg>
            <span class="ml-2 font-medium">Mes Targets</span>
        </a>

        <!-- Galerie d'Images -->
        <a href="{{ route('robotarget.gallery', ['locale' => app()->getLocale()]) }}"
           class="sidebar-item {{ request()->is('*/robotarget/gallery') || request()->is('robotarget/gallery') ? 'active' : '' }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="ml-2 font-medium">🖼️ Galerie</span>
        </a>

        <!-- Mon Abonnement -->
        <a href="{{ route('subscriptions.manage', ['locale' => app()->getLocale()]) }}"
           class="sidebar-item {{ request()->is('*/subscriptions/manage') || request()->is('subscriptions/manage') ? 'active' : '' }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
            <span class="ml-2 font-medium">Mon Abonnement</span>
        </a>


        <!-- SUPPORT SECTION -->
        @php
        $openTicketsCount = auth()->user()->tickets()->whereIn('status', ['open', 'in_progress', 'waiting_admin'])->count();
        @endphp
        <a href="{{ route('support.index') }}"
           class="sidebar-item {{ request()->is('support') || request()->is('support/*') ? 'active' : '' }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span class="ml-2 font-medium">Support</span>
            @if($openTicketsCount > 0)
                <span class="ml-auto inline-flex items-center justify-center min-w-[1.125rem] h-[1.125rem] px-1 text-[10px] font-semibold rounded bg-blue-500 text-white">
                    {{ $openTicketsCount }}
                </span>
            @endif
        </a>

        <!-- Divider for Admin Section -->
        @if(auth()->user()->admin == 1)
        <div class="px-2 py-1.5">
            <div class="border-t border-white/10"></div>
            <div class="text-[10px] text-white/40 mt-1.5 font-semibold uppercase tracking-wider">Admin</div>
        </div>

        <!-- Admin Panel -->
        <a href="{{ route('admin.panel') }}"
           class="sidebar-item {{ request()->is('admin/panel') || request()->is('admin/panel/*') ? 'active' : '' }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
            </svg>
            <span class="ml-2 font-medium">Panel Admin</span>
        </a>

        <!-- Documentation Projet -->
        <a href="{{ route('admin.documentation') }}"
           class="sidebar-item {{ request()->is('admin/documentation') ? 'active' : '' }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span class="ml-2 font-medium">📖 Documentation</span>
            <span class="ml-auto px-1.5 py-0.5 bg-gradient-to-r from-purple-500 to-pink-500 text-white text-[9px] rounded font-bold uppercase tracking-wide">PO</span>
        </a>

        <!-- Gestion Abonnements -->
        @php
        $activeSubscriptionsCount = \App\Models\Subscription::where('status', 'active')->count();
        $trialSubscriptionsCount = \App\Models\Subscription::where('stripe_status', 'trialing')->count();
        @endphp
        <a href="{{ route('admin.subscriptions.dashboard') }}"
           class="sidebar-item {{ request()->is('admin/subscriptions') || request()->is('admin/subscriptions/*') ? 'active' : '' }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
            <span class="ml-2 font-medium">Gestion Abonnements</span>
            @if($activeSubscriptionsCount > 0)
                <span class="ml-auto inline-flex items-center justify-center min-w-[1.125rem] h-[1.125rem] px-1 text-[10px] font-semibold rounded bg-green-500 text-white">
                    {{ $activeSubscriptionsCount }}
                </span>
            @endif
        </a>

        <!-- Configuration Plans -->
        <a href="{{ route('admin.plans.index') }}"
           class="sidebar-item {{ request()->is('admin/plans') || request()->is('admin/plans/*') ? 'active' : '' }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            <span class="ml-2 font-medium">Configuration Plans</span>
        </a>

        <!-- Test & Monitoring -->
        <a href="{{ route('test.voyager') }}"
           class="sidebar-item {{ request()->is('test/voyager') ? 'active' : '' }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
            </svg>
            <span class="ml-2 font-medium">Test & Monitoring</span>
            <span class="ml-auto px-1.5 py-0.5 text-[9px] rounded bg-blue-500/20 text-blue-400 border border-blue-500/30 font-semibold">
                RT
            </span>
        </a>

        <!-- Target Templates Management -->
        @php
        $templatesCount = \App\Models\TargetTemplate::count();
        $activeTemplatesCount = \App\Models\TargetTemplate::where('is_active', true)->count();
        @endphp
        <a href="{{ route('admin.target-templates.index') }}"
           class="sidebar-item {{ request()->is('admin/target-templates') || request()->is('admin/target-templates/*') ? 'active' : '' }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
            <span class="ml-2 font-medium">Templates Targets</span>
            @if($templatesCount > 0)
                <span class="ml-auto inline-flex items-center justify-center min-w-[1.125rem] h-[1.125rem] px-1 text-[10px] font-semibold rounded bg-purple-500 text-white">
                    {{ $activeTemplatesCount }}/{{ $templatesCount }}
                </span>
            @endif
        </a>

        <!-- RoboTarget Sets Management -->
        <a href="{{ route('admin.robotarget.sets') }}"
           class="sidebar-item {{ request()->is('admin/robotarget/sets') || request()->is('admin/robotarget/sets/*') ? 'active' : '' }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
            <span class="ml-2 font-medium">🎯 RoboTarget Sets</span>
            <span class="ml-auto px-1.5 py-0.5 text-[9px] rounded bg-blue-500/20 text-blue-400 border border-blue-500/30 font-semibold">
                Admin
            </span>
        </a>

        <!-- Admin Support Management -->
        @php
        $urgentTicketsCount = \App\Models\SupportTicket::where('priority', 'urgent')
                                                       ->where('status', '!=', 'closed')
                                                       ->count();
        $unassignedTicketsCount = \App\Models\SupportTicket::whereNull('assigned_to')
                                                           ->whereIn('status', ['open', 'waiting_admin'])
                                                           ->count();
        @endphp
        <a href="{{ route('admin.support.dashboard') }}"
           class="sidebar-item {{ request()->is('admin/support') || request()->is('admin/support/*') ? 'active' : '' }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span class="ml-2 font-medium">Gestion Support</span>
            @if($urgentTicketsCount > 0)
                <span class="ml-auto inline-flex items-center justify-center min-w-[1.125rem] h-[1.125rem] px-1 text-[10px] font-semibold rounded bg-red-500 text-white">
                    {{ $urgentTicketsCount }}
                </span>
            @elseif($unassignedTicketsCount > 0)
                <span class="ml-auto inline-flex items-center justify-center min-w-[1.125rem] h-[1.125rem] px-1 text-[10px] font-semibold rounded bg-orange-500 text-white">
                    {{ $unassignedTicketsCount }}
                </span>
            @endif
        </a>
        @endif





        <!-- Logout -->
        <form method="POST" action="{{ route('logout', ['locale' => app()->getLocale()]) }}">
            @csrf
            <button type="submit" class="sidebar-item w-full text-left">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1"/>
                </svg>
                <span class="ml-2 font-medium">{{ __('app.sidebar.logout') }}</span>
            </button>
        </form>

    </nav>

    <!-- Notifications Widget -->
    @include('layouts.partials.sidebar-notifications')

    <!-- User Profile Section -->
    <div class="p-2.5 border-t border-white/10">
        <div class="flex items-center gap-2 p-2 rounded-md bg-white/5 hover:bg-white/8 transition-all group cursor-pointer">
            <div class="w-7 h-7 rounded-md bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0 transition-transform group-hover:scale-110">
                {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white font-medium text-xs truncate flex items-center gap-1">
                    {{ auth()->user()->name ?? 'Astronomer' }}
                    @if(auth()->user()->admin == 1)
                        <span class="inline-flex items-center px-1 py-0.5 rounded text-[9px] font-bold bg-red-500 text-white uppercase tracking-wide">A</span>
                    @endif
                </p>
                @php
                    $userSub = auth()->user()->subscription;
                    $planName = $userSub && $userSub->isActive() ? $userSub->getPlanName() : 'Sans abonnement';
                @endphp
                <p class="text-white/50 text-[10px]">{{ $planName }}</p>
            </div>
        </div>
    </div>

</aside>

<!-- CSS pour mobile -->
<style>
#astral-sidebar {
    height: 100dvh;
    max-height: 100dvh;
    overscroll-behavior: contain;
}

#astral-sidebar nav {
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
}

@supports not (height: 100dvh) {
    #astral-sidebar {
        height: 100vh;
        max-height: 100vh;
    }
}

@media (max-width: 768px) {
    #astral-sidebar {
        backdrop-filter: blur(15px) !important;
        -webkit-backdrop-filter: blur(15px) !important;
    }
}

/* Styles pour les badges */
.badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.25rem;
    height: 1.25rem;
    padding: 0 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
    border-radius: 0.5rem;
    color: white;
}

.sidebar-item {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 0.5rem 0.75rem;
    text-decoration: none;
    color: rgba(255, 255, 255, 0.65);
    border-radius: 0.375rem;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 0.8125rem;
    position: relative;
    overflow: hidden;
}

.sidebar-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(to bottom, rgba(139, 92, 246, 0.8), rgba(219, 39, 119, 0.8));
    transform: scaleY(0);
    transition: transform 0.2s ease;
}

.sidebar-item:hover {
    background-color: rgba(255, 255, 255, 0.08);
    color: white;
    transform: translateX(2px);
    padding-left: 0.875rem;
}

.sidebar-item:hover::before {
    transform: scaleY(1);
}

.sidebar-item.active {
    background: linear-gradient(to right, rgba(139, 92, 246, 0.15), rgba(219, 39, 119, 0.15));
    color: white;
    border: 1px solid rgba(139, 92, 246, 0.25);
    font-weight: 500;
}

.sidebar-item.active::before {
    transform: scaleY(1);
}

.sidebar-item svg {
    transition: transform 0.2s ease;
}

.sidebar-item:hover svg {
    transform: scale(1.1);
}

.sidebar-item:active {
    transform: translateX(2px) scale(0.98);
}
</style>

</document_content>
</document>
