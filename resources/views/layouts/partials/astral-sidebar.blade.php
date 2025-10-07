{{-- resources/views/layouts/partials/astral-sidebar.blade.php --}}

<aside id="astral-sidebar" class="sidebar fixed inset-y-0 left-0 z-40 flex h-screen flex-col overflow-hidden transition-transform duration-300 -translate-x-full lg:translate-x-0 peer-checked:translate-x-0"
       style="background: rgba(0, 0, 0, 0.25); backdrop-filter: blur(30px) saturate(180%); -webkit-backdrop-filter: blur(30px) saturate(180%); border-right: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.1);">

    <!-- Logo Section -->
    <div class="p-4 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-500 flex items-center justify-center flex-shrink-0 shadow-lg">
                <span class="font-black text-white text-2xl tracking-tighter" style="font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', system-ui, sans-serif;">S</span>
            </div>
            <div class="min-w-0">
                <h1 class="font-black text-white text-xl tracking-tight" style="font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', system-ui, sans-serif;">STELLARLOC</h1>
                <p class="text-white/60 text-xs">Astral Interface</p>
            </div>
        </div>
    </div>

    <!-- Credits Balance (if authenticated) -->
    @auth
    @php
        $controlBooking = null;
        $controlState = null;
        $controlStartsAt = null;
        $controlEndsAt = null;
        $controlCountdown = null;
        $controlRemaining = null;

        $bookingTimezone = config('app.booking_timezone', config('app.timezone', 'UTC'));
        $nowInTimezone = now($bookingTimezone);
        $nowUtc = $nowInTimezone->copy()->setTimezone('UTC');
        $todayStartUtc = $nowInTimezone->copy()->startOfDay()->setTimezone('UTC');
        $todayEndUtc = $nowInTimezone->copy()->endOfDay()->setTimezone('UTC');

        $candidateBooking = \App\Models\EquipmentBooking::query()
            ->where('user_id', auth()->id())
            ->whereIn('status', ['confirmed'])
            ->where('end_datetime', '>=', $nowUtc)
            ->where(function ($query) use ($todayStartUtc, $todayEndUtc) {
                $query->whereBetween('start_datetime', [$todayStartUtc, $todayEndUtc])
                      ->orWhere(function ($overlap) use ($todayStartUtc) {
                          $overlap->where('start_datetime', '<', $todayStartUtc)
                                  ->where('end_datetime', '>', $todayStartUtc);
                      });
            })
            ->with(['equipment:id,name'])
            ->orderBy('start_datetime')
            ->first();

        if ($candidateBooking) {
            $reference = $nowInTimezone->copy();
            $state = $candidateBooking->getAccessState($reference);

            if (in_array($state, ['active', 'upcoming'], true)) {
                $controlBooking = $candidateBooking;
                $controlState = $state;
                $controlStartsAt = $controlBooking->start_datetime->copy()->setTimezone($bookingTimezone)->locale(app()->getLocale());
                $controlEndsAt = $controlBooking->end_datetime->copy()->setTimezone($bookingTimezone)->locale(app()->getLocale());
                $controlCountdown = $state === 'upcoming' ? $controlBooking->secondsUntilStart($reference) : null;
                $controlRemaining = $state === 'active' ? $controlBooking->secondsUntilEnd($reference) : null;
            }
        }
    @endphp
    <div class="px-4 mb-4">
        <div class="bg-gradient-to-r from-yellow-500/20 to-orange-500/20 border border-yellow-500/30 rounded-lg p-3">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-white font-semibold">{{ number_format(auth()->user()->credits_balance ?? 0) }}</div>
                    <div class="text-white/60 text-xs">Crédits disponibles</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-yellow-500/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    @endauth

    @if($controlBooking)
        @php
            $isActiveControl = $controlState === 'active';
            $controlPanelClasses = $isActiveControl
                ? 'bg-green-500/15 border border-green-500/30'
                : 'bg-purple-500/15 border border-purple-500/30';
            $badgeClasses = $isActiveControl
                ? 'bg-green-500/30 text-green-100'
                : 'bg-purple-500/30 text-purple-100';
            $timerLabel = $isActiveControl ? __('Temps restant') : __('Début dans');
            $timerSeconds = $isActiveControl ? $controlRemaining : $controlCountdown;
            $timerInitial = null;
            if ($timerSeconds !== null) {
                $safeSeconds = max(0, (int) $timerSeconds);
                $hours = str_pad((string) intdiv($safeSeconds, 3600), 2, '0', STR_PAD_LEFT);
                $minutes = str_pad((string) intdiv($safeSeconds % 3600, 60), 2, '0', STR_PAD_LEFT);
                $secondsPart = str_pad((string) ($safeSeconds % 60), 2, '0', STR_PAD_LEFT);
                $timerInitial = "{$hours}:{$minutes}:{$secondsPart}";
            }
        @endphp
        <div class="px-4 mb-4">
            <div class="rounded-xl {{ $controlPanelClasses }} p-4 text-sm text-white/80">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <p class="text-xs uppercase tracking-wide text-white/50">Contrôle du matériel</p>
                        <p class="text-base font-semibold text-white">{{ $controlBooking->equipment->name }}</p>
                        <p class="text-xs text-white/60">
                            {{ $controlStartsAt->isoFormat('dddd D MMM HH:mm') }} – {{ $controlEndsAt->isoFormat('HH:mm') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeClasses }}">
                            {{ $isActiveControl ? 'En cours' : 'À venir' }}
                        </span>
                        @if($timerInitial)
                            <div class="mt-2 text-[0.65rem] uppercase tracking-wide text-white/50">{{ $timerLabel }}</div>
                            <div class="text-lg font-semibold text-white"
                                 data-countdown="{{ $timerSeconds }}"
                                 data-countdown-state="{{ $controlState }}"
                                 data-countdown-refresh="{{ $controlState === 'upcoming' ? 'true' : 'false' }}">
                                {{ $timerInitial }}
                            </div>
                        @endif
                    </div>
                </div>
                <a href="{{ route('bookings.access', ['locale' => app()->getLocale(), 'booking' => $controlBooking]) }}"
                   class="mt-4 inline-flex items-center justify-center w-full rounded-lg px-3 py-2 text-sm font-semibold text-white transition-colors
                          {{ $isActiveControl ? 'bg-green-500/30 hover:bg-green-500/40' : 'bg-purple-500/30 hover:bg-purple-500/40' }}">
                    {{ $isActiveControl ? 'Accéder au matériel' : 'Préparer l\'accès' }}
                </a>
            </div>
        </div>
    @endif

    <!-- Quick Dashboard Button -->
    @php $onDashboard = request()->is('dashboard') || request()->is('dashboard/*') || request()->is('/'); @endphp
    @if(!$onDashboard)
    <div class="px-4 mb-3">
        <a href="{{ route('dashboard') }}"
           class="w-full inline-flex items-center justify-center p-2 rounded-lg bg-blue-500/20 text-blue-200 hover:bg-blue-500/30 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-sm font-medium">{{ __('app.sidebar.dashboard') }}</span>
        </a>
    </div>
    @endif

    <!-- Language Switcher (Compact) -->
    <div class="px-4 mb-4">
        <div class="flex items-center justify-between text-xs">
            <div class="flex items-center gap-2 text-white/60">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M2 12h20M12 2a15 15 0 010 20M12 2a15 15 0 000 20" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path>
                </svg>
                <span>{{ __('app.language') }}</span>
            </div>
            <div class="flex items-center gap-1">
                <form method="POST" action="{{ route('locale.change', 'fr') }}">
                    @csrf
                    <button type="submit" class="px-2 py-1 rounded-md {{ app()->getLocale() === 'fr' ? 'bg-white/10 text-white' : 'text-white/70 hover:text-white hover:bg-white/5' }}">FR</button>
                </form>
                <form method="POST" action="{{ route('locale.change', 'en') }}">
                    @csrf
                    <button type="submit" class="px-2 py-1 rounded-md {{ app()->getLocale() === 'en' ? 'bg-white/10 text-white' : 'text-white/70 hover:text-white hover:bg-white/5' }}">EN</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 min-h-0 px-4 pb-4 space-y-1 overflow-y-auto">

        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}"
           class="sidebar-item {{ request()->is('dashboard') || request()->is('dashboard/*') || request()->is('/') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 14a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 14a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            <span class="ml-3 font-medium">{{ __('app.sidebar.dashboard') }}</span>
        </a>

        <a href="{{ route('bookings.calendar') }}"
        class="sidebar-item {{ request()->is('bookings') || request()->is('bookings/*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="ml-3 font-medium">Réservations</span>
        </a>

        <a href="{{ route('credits.shop') }}"
        class="sidebar-item {{ request()->is('credits/shop') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
            </svg>
            <span class="ml-3 font-medium">Boutique Crédits</span>
        </a>

        <!-- Historique Crédits -->
        <a href="{{ route('credits.history') }}"
        class="sidebar-item {{ request()->is('credits/history') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span class="ml-3 font-medium">Historique Crédits</span>
        </a>


        <!-- SUPPORT SECTION -->
        @php
        $openTicketsCount = auth()->user()->tickets()->whereIn('status', ['open', 'in_progress', 'waiting_admin'])->count();
        @endphp
        <a href="{{ route('support.index') }}"
           class="sidebar-item {{ request()->is('support') || request()->is('support/*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span class="ml-3 font-medium">Support</span>
            @if($openTicketsCount > 0)
                <span class="badge ml-auto bg-blue-500">
                    {{ $openTicketsCount }}
                </span>
            @endif
        </a>

        <!-- Divider for Admin Section -->
        @if(auth()->user()->admin == 1)
        <div class="px-3 py-2">
            <div class="border-t border-white/10"></div>
            <div class="text-xs text-white/50 mt-2 font-medium">ADMINISTRATION</div>
        </div>

        <!-- Admin Panel -->
        <a href="{{ route('admin.panel') }}"
           class="sidebar-item {{ request()->is('admin/panel') || request()->is('admin/panel/*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
            </svg>
            <span class="ml-3 font-medium">Panel Admin</span>
        </a>

@if(auth()->user()->admin == 1)
    <!-- Gestion Réservations Admin -->
    @php
    $pendingCount = \App\Models\EquipmentBooking::where('status', 'pending')->count();
    @endphp
    <a href="{{ route('admin.bookings.dashboard') }}"
       class="sidebar-item {{ request()->is('admin/bookings') || request()->is('admin/bookings/*') ? 'active' : '' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <span class="ml-3 font-medium">Gestion Réservations</span>
        @if($pendingCount > 0)
            <span class="badge ml-auto bg-yellow-500">
                {{ $pendingCount }}
            </span>
        @endif
    </a>
@endif


        <!-- Admin Equipment Management -->
        <a href="{{ route('admin.equipment.index') }}"
           class="sidebar-item {{ request()->is('admin/equipment') || request()->is('admin/equipment/*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <span class="ml-3 font-medium">Gestion Matériel</span>
            <span class="badge ml-auto bg-cyan-500">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"/>
                    <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-5a1 1 0 10-2 0v5H5V7h5a1 1 0 000-2H5z"/>
                </svg>
            </span>
        </a>

        <a href="{{ route('admin.credits.dashboard') }}"
            class="sidebar-item {{ request()->is('admin/credits') || request()->is('admin/credits/*') ? 'active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                </svg>
                <span class="ml-3 font-medium">Gestion Crédits</span>
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
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span class="ml-3 font-medium">Gestion Support</span>
            @if($urgentTicketsCount > 0)
                <span class="badge ml-auto bg-red-500">
                    {{ $urgentTicketsCount }}
                </span>
            @elseif($unassignedTicketsCount > 0)
                <span class="badge ml-auto bg-orange-500">
                    {{ $unassignedTicketsCount }}
                </span>
            @endif
        </a>
        @endif





        <!-- Logout -->
        <form method="POST" action="{{ route('logout', ['locale' => app()->getLocale()]) }}">
            @csrf
            <button type="submit" class="sidebar-item w-full text-left">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1"/>
                </svg>
                <span class="ml-3 font-medium">{{ __('app.sidebar.logout') }}</span>
            </button>
        </form>

    </nav>

    <!-- User Profile Section -->
    <div class="p-4 border-t border-white/10">
        <div class="flex items-center gap-3 p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white font-medium text-sm truncate">
                    {{ auth()->user()->name ?? 'Astronomer' }}
                    @if(auth()->user()->admin == 1)
                        <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Admin</span>
                    @endif
                </p>
                <p class="text-white/60 text-xs">{{ ucfirst(auth()->user()->subscription_type ?? __('app.sidebar.explorer')) }} • {{ __('app.sidebar.online') }}</p>
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
    padding: 0.75rem 1rem;
    text-decoration: none;
    color: rgba(255, 255, 255, 0.7);
    border-radius: 0.5rem;
    transition: all 0.15s ease;
    font-size: 0.875rem;
}

.sidebar-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
}

.sidebar-item.active {
    background: linear-gradient(to right, rgba(139, 92, 246, 0.2), rgba(219, 39, 119, 0.2));
    color: white;
    border: 1px solid rgba(139, 92, 246, 0.3);
}
</style>

@once
    @push('scripts')
        <script>
            (function () {
                const formatTime = (totalSeconds) => {
                    const safeSeconds = Math.max(0, Math.floor(totalSeconds));
                    const hours = String(Math.floor(safeSeconds / 3600)).padStart(2, '0');
                    const minutes = String(Math.floor((safeSeconds % 3600) / 60)).padStart(2, '0');
                    const seconds = String(safeSeconds % 60).padStart(2, '0');
                    return `${hours}:${minutes}:${seconds}`;
                };

                const initialiseCountdowns = () => {
                    document.querySelectorAll('[data-countdown]').forEach((el) => {
                        let remaining = Number.parseInt(el.dataset.countdown, 10);
                        if (Number.isNaN(remaining)) {
                            return;
                        }

                        const shouldRefresh = el.dataset.countdownRefresh === 'true';
                        const update = () => {
                            el.textContent = formatTime(remaining);
                        };

                        update();

                        if (remaining <= 0) {
                            if (shouldRefresh) {
                                setTimeout(() => window.location.reload(), 1200);
                            }
                            return;
                        }

                        const interval = window.setInterval(() => {
                            remaining -= 1;
                            if (remaining <= 0) {
                                window.clearInterval(interval);
                                remaining = 0;
                                update();
                                if (shouldRefresh) {
                                    setTimeout(() => window.location.reload(), 1200);
                                }
                            } else {
                                update();
                            }
                        }, 1000);
                    });
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initialiseCountdowns, { once: true });
                } else {
                    initialiseCountdowns();
                }
            })();
        </script>
    @endpush
@endonce
</document_content>
</document>
