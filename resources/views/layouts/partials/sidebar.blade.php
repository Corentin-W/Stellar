{{-- resources/views/layouts/partials/sidebar.blade.php --}}
<aside class="sidebar"
       :class="{ 'collapsed': $store.sidebar.collapsed, 'mobile-open': $store.sidebar.isOpen }"
       x-data="navigation()">

    <!-- Header Sidebar -->
    <div class="sidebar-header">
        <div class="logo">T</div>
        <div class="app-name">TelescopeApp</div>
        <button @click="$store.sidebar.toggle()" class="lg:hidden navbar-button">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <!-- Navigation principale -->
        <div class="nav-section">
            <div class="nav-section-title">Navigation</div>

            <a href="{{ route('dashboard', 'fr') }}"
               class="nav-item"
               :class="{ 'active': isActive('/dashboard') }">
                <div class="nav-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 14a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 14a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </div>
                <span class="nav-text">Dashboard</span>
            </a>

            <a href="#" class="nav-item" :class="{ 'active': isActive('/telescope') }">
                <div class="nav-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <span class="nav-text">Control</span>
                <div class="status-indicator"
                     :class="$store.telescope.status === 'online' ? 'status-online' : 'status-offline'"></div>
            </a>

            <a href="#" class="nav-item" :class="{ 'active': isActive('/sessions') }">
                <div class="nav-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="nav-text">Sessions</span>
                <div class="nav-badge">3</div>
            </a>

            <a href="#" class="nav-item" :class="{ 'active': isActive('/gallery') }">
                <div class="nav-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="nav-text">Gallery</span>
            </a>
        </div>

        <!-- Services -->
        <div class="nav-section">
            <div class="nav-section-title">Services</div>

            <a href="#" class="nav-item" :class="{ 'active': isActive('/lunar') }">
                <div class="nav-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </div>
                <span class="nav-text">Lunar Calendar</span>
            </a>

            <a href="#" class="nav-item" :class="{ 'active': isActive('/premium') }">
                <div class="nav-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <span class="nav-text">Premium</span>
                <div class="nav-badge">Pro</div>
            </a>

            <a href="#" class="nav-item" :class="{ 'active': isActive('/shop') }">
                <div class="nav-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <span class="nav-text">Shop</span>
            </a>
        </div>

        <!-- Communauté -->
        <div class="nav-section">
            <div class="nav-section-title">Community</div>

            <a href="#" class="nav-item" :class="{ 'active': isActive('/community') }">
                <div class="nav-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <span class="nav-text">Community</span>
                <div class="nav-badge">12</div>
            </a>

            <a href="#" class="nav-item" :class="{ 'active': isActive('/support') }">
                <div class="nav-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <span class="nav-text">Support</span>
            </a>
        </div>
    </nav>

    <!-- Footer utilisateur -->
    <div class="sidebar-footer">
        <div class="user-profile" x-data="userMenu()">
            <div class="user-avatar">
                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
            </div>
            <div class="user-info">
                <div class="user-name">{{ auth()->user()->name ?? 'User' }}</div>
                <div class="user-plan">{{ ucfirst(auth()->user()->subscription_type ?? 'Free') }} Plan</div>
            </div>
        </div>
    </div>
</aside>
