{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.astral-app')

@section('title', __('app.cosmic_dashboard') . ' - TelescopeApp')
@section('page-title', __('app.cosmic_dashboard'))

@section('content')
<div class="p-6 lg:p-8" x-data="cosmicDashboard()" x-init="init()">

    <!-- Welcome Section -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>

                <p class="text-white/70 text-lg animate-fade-in-up" style="animation-delay: 0.1s">
                    {{ __('app.welcome_message') }}
                </p>
            </div>

            <div class="flex items-center gap-4 animate-fade-in-up" style="animation-delay: 0.2s">
                <div class="text-right">
                    <div class="text-sm text-white/60">{{ __('app.local_sidereal_time') }}</div>
                    <div class="text-xl font-mono font-bold text-white" x-text="siderealTime"></div>
                </div>
                <button @click="startNewSession()"
                        class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl font-semibold hover:scale-105 transition-transform shadow-lg">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    {{ __('app.new_observation') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        <!-- Telescope Status -->
        <div class="dashboard-card p-6 bg-gradient-to-br from-blue-500/20 to-purple-600/20 border-blue-500/30 animate-fade-in-up" style="animation-delay: 0.3s">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-white">{{ __('app.telescope_status') }}</h3>
                <div class="status-online"></div>
            </div>
            <div class="text-2xl font-bold text-white mb-2" x-text="translations.online"></div>
            <div class="text-white/80 text-sm">{{ __('app.connected_tracking') }}</div>
            <div class="mt-3 text-xs text-green-400 flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 14l5-5 5 5z"/>
                </svg>
                99.8% {{ __('app.uptime_month') }}
            </div>
        </div>

        <!-- Sessions -->
        <div class="dashboard-card p-6 animate-fade-in-up" style="animation-delay: 0.4s">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-white">{{ __('app.sessions') }}</h3>
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-white mb-2" x-text="metrics.sessions"></div>
            <div class="text-white/60 text-sm">{{ __('app.lunar_cycle') }}</div>
            <div class="mt-3 text-xs text-green-400 flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 14l5-5 5 5z"/>
                </svg>
                +15% {{ __('app.from_last_cycle') }}
            </div>
        </div>

        <!-- Images Captured -->
        <div class="dashboard-card p-6 animate-fade-in-up" style="animation-delay: 0.5s">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-white">{{ __('app.cosmic_images') }}</h3>
                <svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-white mb-2" x-text="metrics.images"></div>
            <div class="text-white/60 text-sm">{{ __('app.deep_sky_captures') }}</div>
            <div class="mt-3 text-xs text-green-400 flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 14l5-5 5 5z"/>
                </svg>
                +28% {{ __('app.quality_improvement') }}
            </div>
        </div>

        <!-- Exposure Time -->
        <div class="dashboard-card p-6 animate-fade-in-up" style="animation-delay: 0.6s">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-white">{{ __('app.exposure_time') }}</h3>
                <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-white mb-2">47.2{{ __('app.hours') }}</div>
            <div class="text-white/60 text-sm">{{ __('app.total_month') }}</div>
            <div class="mt-3 text-xs text-green-400 flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 14l5-5 5 5z"/>
                </svg>
                +12% {{ __('app.efficiency_gain') }}
            </div>
        </div>

    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        <!-- Recent Sessions -->
        <div class="lg:col-span-2 dashboard-card p-6 animate-fade-in-up" style="animation-delay: 0.7s">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-white">{{ __('app.recent_cosmic_sessions') }}</h3>
                <button class="text-blue-400 hover:text-blue-300 text-sm font-medium transition-colors">{{ __('app.view_all') }}</button>
            </div>

            <div class="space-y-4">
                <template x-for="session in recentSessions" :key="session.id">
                    <div class="flex items-center justify-between p-4 rounded-lg bg-white/5 hover:bg-white/10 transition-all duration-300 group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-medium text-white" x-text="session.target"></h4>
                                <p class="text-white/60 text-sm" x-text="session.type"></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-white/80 text-sm font-mono" x-text="session.duration"></div>
                            <div class="text-xs mt-1"
                                 :class="{
                                     'text-green-400': session.status === 'completed',
                                     'text-blue-400': session.status === 'active',
                                     'text-yellow-400': session.status === 'scheduled'
                                 }"
                                 x-text="getStatusLabel(session.status)">
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Weather & Conditions -->
        <div class="dashboard-card p-6 animate-fade-in-up" style="animation-delay: 0.8s">
            <h3 class="text-xl font-semibold text-white mb-6">{{ __('app.atmospheric_conditions') }}</h3>

            <div class="space-y-4">
                <div class="text-center">
                    <div class="text-3xl font-bold text-white mb-2" x-text="weather.temperature + '{{ __('app.celsius') }}'"></div>
                    <div class="text-white/60" x-text="weather.condition"></div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-3 rounded-lg bg-white/5">
                        <div class="text-lg font-semibold text-white" x-text="weather.humidity + '{{ __('app.percent') }}'"></div>
                        <div class="text-white/60 text-xs">{{ __('app.humidity') }}</div>
                    </div>
                    <div class="text-center p-3 rounded-lg bg-white/5">
                        <div class="text-lg font-semibold text-white" x-text="weather.windSpeed + ' {{ __('app.kmh') }}'"></div>
                        <div class="text-white/60 text-xs">{{ __('app.wind') }}</div>
                    </div>
                    <div class="text-center p-3 rounded-lg bg-white/5">
                        <div class="text-lg font-semibold text-white" x-text="weather.visibility + ' {{ __('app.km') }}'"></div>
                        <div class="text-white/60 text-xs">{{ __('app.visibility') }}</div>
                    </div>
                    <div class="text-center p-3 rounded-lg bg-white/5">
                        <div class="text-lg font-semibold text-white" x-text="weather.seeing + '{{ __('app.arcsec') }}'"></div>
                        <div class="text-white/60 text-xs">{{ __('app.seeing') }}</div>
                    </div>
                </div>

                <div class="p-3 rounded-lg border"
                     :class="{
                         'bg-green-500/20 border-green-500/30': weather.seeingQuality === 'excellent',
                         'bg-blue-500/20 border-blue-500/30': weather.seeingQuality === 'good',
                         'bg-yellow-500/20 border-yellow-500/30': weather.seeingQuality === 'poor'
                     }">
                    <div class="flex items-center gap-2">
                        <div class="status-online"
                             :class="{
                                 'bg-green-500': weather.seeingQuality === 'excellent',
                                 'bg-blue-500': weather.seeingQuality === 'good',
                                 'bg-yellow-500': weather.seeingQuality === 'poor'
                             }"></div>
                        <span class="text-sm font-medium"
                              :class="{
                                  'text-green-400': weather.seeingQuality === 'excellent',
                                  'text-blue-400': weather.seeingQuality === 'good',
                                  'text-yellow-400': weather.seeingQuality === 'poor'
                              }"
                              x-text="getConditionsLabel(weather.seeingQuality)"></span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Tonight's Celestial Highlights -->
    <div class="dashboard-card p-6 mb-8 animate-fade-in-up" style="animation-delay: 0.9s">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-white">{{ __('app.tonight_highlights') }}</h3>
            <div class="text-right">
                <span class="text-sm text-white/60">{{ __('app.twilight_ends') }}</span>
                <span class="text-blue-400 font-mono font-semibold ml-2" x-text="timeToTwilight"></span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <template x-for="target in celestialTargets" :key="target.id">
                <div class="p-4 rounded-lg bg-white/5 hover:bg-white/10 transition-all duration-300 cursor-pointer group"
                     @click="selectTarget(target)">
                    <div class="w-full h-20 rounded-lg mb-3 bg-gradient-to-br from-blue-500/20 to-purple-600/20 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <svg class="w-8 h-8 text-white/60" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h4 class="font-medium text-white text-sm" x-text="target.name"></h4>
                        <p class="text-white/60 text-xs" x-text="target.type"></p>
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-1">
                                <span class="text-white/50">Mag:</span>
                                <span class="text-white" x-text="target.magnitude"></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="text-white/50">Alt:</span>
                                <span class="text-white" x-text="target.altitude + '{{ __('app.degrees') }}'"></span>
                            </div>
                        </div>
                        <div class="text-xs"
                             :class="{
                                 'text-green-400': target.visibility === 'excellent',
                                 'text-blue-400': target.visibility === 'good',
                                 'text-yellow-400': target.visibility === 'poor'
                             }"
                             x-text="target.visibility.toUpperCase()">
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="animate-fade-in-up" style="animation-delay: 1s">
        <h2 class="text-2xl font-semibold text-white mb-6">{{ __('app.quick_cosmic_actions') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            <button @click="autoAlign()"
                    class="dashboard-card p-6 text-center hover:scale-105 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h4 class="font-semibold text-white mb-2">{{ __('app.auto_alignment') }}</h4>
                <p class="text-white/60 text-sm">{{ __('app.precise_goto') }}</p>
            </button>

            <button @click="startPhotoSession()"
                    class="dashboard-card p-6 text-center hover:scale-105 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h4 class="font-semibold text-white mb-2">{{ __('app.astrophotography') }}</h4>
                <p class="text-white/60 text-sm">{{ __('app.start_imaging') }}</p>
            </button>

            <button @click="openPlanetarium()"
                    class="dashboard-card p-6 text-center hover:scale-105 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-500 to-blue-600 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h4 class="font-semibold text-white mb-2">{{ __('app.sky_planetarium') }}</h4>
                <p class="text-white/60 text-sm">{{ __('app.interactive_map') }}</p>
            </button>

            <button @click="lunarPlanning()"
                    class="dashboard-card p-6 text-center hover:scale-105 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-yellow-500 to-purple-600 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </div>
                <h4 class="font-semibold text-white mb-2">{{ __('app.lunar_planning') }}</h4>
                <p class="text-white/60 text-sm">{{ __('app.moon_phases') }}</p>
            </button>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function cosmicDashboard() {
    return {
        siderealTime: '14:32:18',
        timeToTwilight: '2h 34m',
        translations: @json(__('app')),
        metrics: {
            telescopeStatus: 'ONLINE',
            sessions: 28,
            images: 194,
            exposureTime: '47.2h'
        },
        recentSessions: [
            {
                id: 1,
                target: 'Andromeda Galaxy',
                type: 'M31 - Spiral Galaxy',
                duration: '2h 45m',
                status: 'completed'
            },
            {
                id: 2,
                target: 'Orion Nebula',
                type: 'M42 - Emission Nebula',
                duration: '1h 30m',
                status: 'active'
            },
            {
                id: 3,
                target: 'Ring Nebula',
                type: 'M57 - Planetary Nebula',
                duration: '3h 15m',
                status: 'scheduled'
            },
            {
                id: 4,
                target: 'Whirlpool Galaxy',
                type: 'M51 - Spiral Galaxy',
                duration: '2h 00m',
                status: 'scheduled'
            }
        ],
        weather: {
            temperature: -3,
            condition: 'Excellent Clear Skies',
            humidity: 42,
            windSpeed: 6,
            visibility: 28,
            seeing: 1.2,
            seeingQuality: 'excellent'
        },
        celestialTargets: [
            {
                id: 1,
                name: 'M31 - Andromeda',
                type: 'Spiral Galaxy',
                magnitude: 3.4,
                altitude: 78,
                visibility: 'excellent'
            },
            {
                id: 2,
                name: 'M42 - Orion Nebula',
                type: 'Emission Nebula',
                magnitude: 4.0,
                altitude: 65,
                visibility: 'excellent'
            },
            {
                id: 3,
                name: 'M13 - Hercules Cluster',
                type: 'Globular Cluster',
                magnitude: 5.8,
                altitude: 52,
                visibility: 'good'
            },
            {
                id: 4,
                name: 'Saturn',
                type: 'Planet',
                magnitude: 0.2,
                altitude: 45,
                visibility: 'excellent'
            }
        ],

        getStatusLabel(status) {
            const statusLabels = {
                'completed': this.translations.completed,
                'active': this.translations.active,
                'scheduled': this.translations.scheduled
            };
            return statusLabels[status] || status.toUpperCase();
        },

        getConditionsLabel(quality) {
            const conditionLabels = {
                'excellent': this.translations.excellent_conditions,
                'good': this.translations.good_conditions,
                'poor': this.translations.poor_conditions
            };
            return conditionLabels[quality] || quality.toUpperCase();
        },

        init() {
            // Animation d'entrée
            setTimeout(() => {
                const elements = document.querySelectorAll('.animate-fade-in-up');
                elements.forEach((el, index) => {
                    el.style.animationDelay = `${index * 0.1}s`;
                });
            }, 100);

            // Mise à jour du temps sidéral
            this.updateSiderealTime();
            setInterval(() => {
                this.updateSiderealTime();
            }, 1000);

            // Mise à jour météo périodique
            setInterval(() => {
                this.updateWeatherData();
            }, 60000);

            // Message de bienvenue
            setTimeout(() => {
                window.showNotification(
                    this.translations.cosmic_dashboard_active,
                    this.translations.systems_operational,
                    'success'
                );
            }, 1500);

            console.log('🌌 Cosmic Dashboard initialized');
        },

        updateSiderealTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            this.siderealTime = `${hours}:${minutes}:${seconds}`;
        },

        updateWeatherData() {
            // Simulation de mise à jour météo réaliste
            const baseTemp = -3;
            this.weather.temperature = Math.round((baseTemp + (Math.random() * 4 - 2)) * 10) / 10;
            this.weather.humidity = Math.max(25, Math.min(80, this.weather.humidity + (Math.random() * 6 - 3)));
            this.weather.windSpeed = Math.max(0, Math.min(15, this.weather.windSpeed + (Math.random() * 2 - 1)));
            this.weather.seeing = Math.max(0.8, Math.min(3.0, this.weather.seeing + (Math.random() * 0.4 - 0.2)));

            // Mise à jour qualité seeing
            if (this.weather.seeing <= 1.5) {
                this.weather.seeingQuality = 'excellent';
            } else if (this.weather.seeing <= 2.5) {
                this.weather.seeingQuality = 'good';
            } else {
                this.weather.seeingQuality = 'poor';
            }

            this.weather.seeing = Math.round(this.weather.seeing * 10) / 10;
        },

        startNewSession() {
            window.showNotification(
                this.translations.new_session,
                'Opening session planner interface...',
                'info'
            );
        },

        selectTarget(target) {
            window.showNotification(
                'Target Selected',
                `${target.name} added to observation queue`,
                'success'
            );
        },

        autoAlign() {
            if (this.metrics.telescopeStatus === 'ONLINE') {
                window.showNotification(
                    this.translations.auto_alignment,
                    'Starting 3-star alignment procedure...',
                    'info'
                );

                setTimeout(() => {
                    window.showNotification(
                        'Alignment Complete',
                        'Telescope aligned with ±2 arcmin precision',
                        'success'
                    );
                }, 4000);
            } else {
                window.showNotification(this.translations.error, this.translations.telescope_not_connected, 'error');
            }
        },

        startPhotoSession() {
            window.showNotification(
                this.translations.astrophotography,
                'Initializing imaging sequence...',
                'info'
            );
        },

        openPlanetarium() {
            window.showNotification(
                this.translations.sky_planetarium,
                'Loading interactive star map...',
                'info'
            );
        },

        lunarPlanning() {
            window.showNotification(
                this.translations.lunar_planning,
                'Opening moon phase calculator...',
                'info'
            );
        }
    }
}
</script>
@endpush{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Cosmic Dashboard - TelescopeApp')
@section('page-title', 'Cosmic Dashboard')

@section('content')
<div class="p-6 lg:p-8" x-data="cosmicDashboard()" x-init="init()">

    <!-- Welcome Section -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="font-astral text-3xl lg:text-4xl font-bold text-white mb-2 animate-fade-in-up">
                    Cosmic Dashboard
                </h1>
                <p class="text-white/70 text-lg animate-fade-in-up" style="animation-delay: 0.1s">
                    Welcome back, Astronomer. The universe awaits your exploration.
                </p>
            </div>

            <div class="flex items-center gap-4 animate-fade-in-up" style="animation-delay: 0.2s">
                <div class="text-right">
                    <div class="text-sm text-white/60">Local Sidereal Time</div>
                    <div class="text-xl font-mono font-bold text-white" x-text="siderealTime"></div>
                </div>
                <button @click="startNewSession()"
                        class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl font-semibold hover:scale-105 transition-transform shadow-lg">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    New Observation
                </button>
            </div>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        <!-- Telescope Status -->
        <div class="dashboard-card p-6 bg-gradient-to-br from-blue-500/20 to-purple-600/20 border-blue-500/30 animate-fade-in-up" style="animation-delay: 0.3s">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-white">Telescope Status</h3>
                <div class="status-online"></div>
            </div>
            <div class="text-2xl font-bold text-white mb-2" x-text="metrics.telescopeStatus"></div>
            <div class="text-white/80 text-sm">Connected & Tracking</div>
            <div class="mt-3 text-xs text-green-400 flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 14l5-5 5 5z"/>
                </svg>
                99.8% uptime this month
            </div>
        </div>

        <!-- Sessions -->
        <div class="dashboard-card p-6 animate-fade-in-up" style="animation-delay: 0.4s">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-white">Sessions</h3>
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-white mb-2" x-text="metrics.sessions"></div>
            <div class="text-white/60 text-sm">This lunar cycle</div>
            <div class="mt-3 text-xs text-green-400 flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 14l5-5 5 5z"/>
                </svg>
                +15% from last cycle
            </div>
        </div>

        <!-- Images Captured -->
        <div class="dashboard-card p-6 animate-fade-in-up" style="animation-delay: 0.5s">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-white">Images</h3>
                <svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-white mb-2" x-text="metrics.images"></div>
            <div class="text-white/60 text-sm">Deep sky captures</div>
            <div class="mt-3 text-xs text-green-400 flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 14l5-5 5 5z"/>
                </svg>
                +28% quality improvement
            </div>
        </div>

        <!-- Exposure Time -->
        <div class="dashboard-card p-6 animate-fade-in-up" style="animation-delay: 0.6s">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-white">Exposure</h3>
                <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-white mb-2">47.2h</div>
            <div class="text-white/60 text-sm">Total this month</div>
            <div class="mt-3 text-xs text-green-400 flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 14l5-5 5 5z"/>
                </svg>
                +12% efficiency gain
            </div>
        </div>

    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        <!-- Recent Sessions -->
        <div class="lg:col-span-2 dashboard-card p-6 animate-fade-in-up" style="animation-delay: 0.7s">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-white">Recent Cosmic Sessions</h3>
                <button class="text-blue-400 hover:text-blue-300 text-sm font-medium transition-colors">View All</button>
            </div>

            <div class="space-y-4">
                <template x-for="session in recentSessions" :key="session.id">
                    <div class="flex items-center justify-between p-4 rounded-lg bg-white/5 hover:bg-white/10 transition-all duration-300 group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-medium text-white" x-text="session.target"></h4>
                                <p class="text-white/60 text-sm" x-text="session.type"></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-white/80 text-sm font-mono" x-text="session.duration"></div>
                            <div class="text-xs mt-1"
                                 :class="{
                                     'text-green-400': session.status === 'completed',
                                     'text-blue-400': session.status === 'active',
                                     'text-yellow-400': session.status === 'scheduled'
                                 }"
                                 x-text="session.status.toUpperCase()">
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Weather & Conditions -->
        <div class="dashboard-card p-6 animate-fade-in-up" style="animation-delay: 0.8s">
            <h3 class="text-xl font-semibold text-white mb-6">Atmospheric Conditions</h3>

            <div class="space-y-4">
                <div class="text-center">
                    <div class="text-3xl font-bold text-white mb-2" x-text="weather.temperature + '°C'"></div>
                    <div class="text-white/60" x-text="weather.condition"></div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-3 rounded-lg bg-white/5">
                        <div class="text-lg font-semibold text-white" x-text="weather.humidity + '%'"></div>
                        <div class="text-white/60 text-xs">Humidity</div>
                    </div>
                    <div class="text-center p-3 rounded-lg bg-white/5">
                        <div class="text-lg font-semibold text-white" x-text="weather.windSpeed + ' km/h'"></div>
                        <div class="text-white/60 text-xs">Wind</div>
                    </div>
                    <div class="text-center p-3 rounded-lg bg-white/5">
                        <div class="text-lg font-semibold text-white" x-text="weather.visibility + ' km'"></div>
                        <div class="text-white/60 text-xs">Visibility</div>
                    </div>
                    <div class="text-center p-3 rounded-lg bg-white/5">
                        <div class="text-lg font-semibold text-white" x-text="weather.seeing + '\"'"></div>
                        <div class="text-white/60 text-xs">Seeing</div>
                    </div>
                </div>

                <div class="p-3 rounded-lg border"
                     :class="{
                         'bg-green-500/20 border-green-500/30': weather.seeingQuality === 'excellent',
                         'bg-blue-500/20 border-blue-500/30': weather.seeingQuality === 'good',
                         'bg-yellow-500/20 border-yellow-500/30': weather.seeingQuality === 'poor'
                     }">
                    <div class="flex items-center gap-2">
                        <div class="status-online"
                             :class="{
                                 'bg-green-500': weather.seeingQuality === 'excellent',
                                 'bg-blue-500': weather.seeingQuality === 'good',
                                 'bg-yellow-500': weather.seeingQuality === 'poor'
                             }"></div>
                        <span class="text-sm font-medium"
                              :class="{
                                  'text-green-400': weather.seeingQuality === 'excellent',
                                  'text-blue-400': weather.seeingQuality === 'good',
                                  'text-yellow-400': weather.seeingQuality === 'poor'
                              }"
                              x-text="weather.seeingQuality.toUpperCase() + ' CONDITIONS'"></span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Tonight's Celestial Highlights -->
    <div class="dashboard-card p-6 mb-8 animate-fade-in-up" style="animation-delay: 0.9s">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-white">Tonight's Celestial Highlights</h3>
            <div class="text-right">
                <span class="text-sm text-white/60">Astronomical twilight ends in</span>
                <span class="text-blue-400 font-mono font-semibold ml-2" x-text="timeToTwilight"></span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <template x-for="target in celestialTargets" :key="target.id">
                <div class="p-4 rounded-lg bg-white/5 hover:bg-white/10 transition-all duration-300 cursor-pointer group"
                     @click="selectTarget(target)">
                    <div class="w-full h-20 rounded-lg mb-3 bg-gradient-to-br from-blue-500/20 to-purple-600/20 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <svg class="w-8 h-8 text-white/60" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h4 class="font-medium text-white text-sm" x-text="target.name"></h4>
                        <p class="text-white/60 text-xs" x-text="target.type"></p>
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-1">
                                <span class="text-white/50">Mag:</span>
                                <span class="text-white" x-text="target.magnitude"></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="text-white/50">Alt:</span>
                                <span class="text-white" x-text="target.altitude + '°'"></span>
                            </div>
                        </div>
                        <div class="text-xs"
                             :class="{
                                 'text-green-400': target.visibility === 'excellent',
                                 'text-blue-400': target.visibility === 'good',
                                 'text-yellow-400': target.visibility === 'poor'
                             }"
                             x-text="target.visibility.toUpperCase()">
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="animate-fade-in-up" style="animation-delay: 1s">
        <h2 class="text-2xl font-semibold text-white mb-6">Quick Cosmic Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            <button @click="autoAlign()"
                    class="dashboard-card p-6 text-center hover:scale-105 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h4 class="font-semibold text-white mb-2">Auto Alignment</h4>
                <p class="text-white/60 text-sm">Precise GoTo calibration</p>
            </button>

            <button @click="startPhotoSession()"
                    class="dashboard-card p-6 text-center hover:scale-105 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h4 class="font-semibold text-white mb-2">Astrophotography</h4>
                <p class="text-white/60 text-sm">Start imaging sequence</p>
            </button>

            <button @click="openPlanetarium()"
                    class="dashboard-card p-6 text-center hover:scale-105 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-500 to-blue-600 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h4 class="font-semibold text-white mb-2">Sky Planetarium</h4>
                <p class="text-white/60 text-sm">Interactive star map</p>
            </button>

            <button @click="lunarPlanning()"
                    class="dashboard-card p-6 text-center hover:scale-105 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-yellow-500 to-purple-600 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </div>
                <h4 class="font-semibold text-white mb-2">Lunar Planning</h4>
                <p class="text-white/60 text-sm">Moon phases & timing</p>
            </button>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function cosmicDashboard() {
    return {
        siderealTime: '14:32:18',
        timeToTwilight: '2h 34m',
        metrics: {
            telescopeStatus: 'ONLINE',
            sessions: 28,
            images: 194,
            exposureTime: '47.2h'
        },
        recentSessions: [
            {
                id: 1,
                target: 'Andromeda Galaxy',
                type: 'M31 - Spiral Galaxy',
                duration: '2h 45m',
                status: 'completed'
            },
            {
                id: 2,
                target: 'Orion Nebula',
                type: 'M42 - Emission Nebula',
                duration: '1h 30m',
                status: 'active'
            },
            {
                id: 3,
                target: 'Ring Nebula',
                type: 'M57 - Planetary Nebula',
                duration: '3h 15m',
                status: 'scheduled'
            },
            {
                id: 4,
                target: 'Whirlpool Galaxy',
                type: 'M51 - Spiral Galaxy',
                duration: '2h 00m',
                status: 'scheduled'
            }
        ],
        weather: {
            temperature: -3,
            condition: 'Excellent Clear Skies',
            humidity: 42,
            windSpeed: 6,
            visibility: 28,
            seeing: 1.2,
            seeingQuality: 'excellent'
        },
        celestialTargets: [
            {
                id: 1,
                name: 'M31 - Andromeda',
                type: 'Spiral Galaxy',
                magnitude: 3.4,
                altitude: 78,
                visibility: 'excellent'
            },
            {
                id: 2,
                name: 'M42 - Orion Nebula',
                type: 'Emission Nebula',
                magnitude: 4.0,
                altitude: 65,
                visibility: 'excellent'
            },
            {
                id: 3,
                name: 'M13 - Hercules Cluster',
                type: 'Globular Cluster',
                magnitude: 5.8,
                altitude: 52,
                visibility: 'good'
            },
            {
                id: 4,
                name: 'Saturn',
                type: 'Planet',
                magnitude: 0.2,
                altitude: 45,
                visibility: 'excellent'
            }
        ],

        init() {
            // Animation d'entrée
            setTimeout(() => {
                const elements = document.querySelectorAll('.animate-fade-in-up');
                elements.forEach((el, index) => {
                    el.style.animationDelay = `${index * 0.1}s`;
                });
            }, 100);

            // Mise à jour du temps sidéral
            this.updateSiderealTime();
            setInterval(() => {
                this.updateSiderealTime();
            }, 1000);

            // Mise à jour météo périodique
            setInterval(() => {
                this.updateWeatherData();
            }, 60000);

            // Message de bienvenue
            setTimeout(() => {
                window.showNotification(
                    'Cosmic Dashboard Active',
                    'All systems operational. Ready for stellar observations.',
                    'success'
                );
            }, 1500);

            console.log('🌌 Cosmic Dashboard initialized');
        },

        updateSiderealTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            this.siderealTime = `${hours}:${minutes}:${seconds}`;
        },

        updateWeatherData() {
            // Simulation de mise à jour météo réaliste
            const baseTemp = -3;
            this.weather.temperature = Math.round((baseTemp + (Math.random() * 4 - 2)) * 10) / 10;
            this.weather.humidity = Math.max(25, Math.min(80, this.weather.humidity + (Math.random() * 6 - 3)));
            this.weather.windSpeed = Math.max(0, Math.min(15, this.weather.windSpeed + (Math.random() * 2 - 1)));
            this.weather.seeing = Math.max(0.8, Math.min(3.0, this.weather.seeing + (Math.random() * 0.4 - 0.2)));

            // Mise à jour qualité seeing
            if (this.weather.seeing <= 1.5) {
                this.weather.seeingQuality = 'excellent';
            } else if (this.weather.seeing <= 2.5) {
                this.weather.seeingQuality = 'good';
            } else {
                this.weather.seeingQuality = 'poor';
            }

            this.weather.seeing = Math.round(this.weather.seeing * 10) / 10;
        },

        startNewSession() {
            window.showNotification(
                'New Observation Session',
                'Opening session planner interface...',
                'info'
            );
        },

        selectTarget(target) {
            window.showNotification(
                'Target Selected',
                `${target.name} added to observation queue`,
                'success'
            );
        },

        autoAlign() {
            if (this.metrics.telescopeStatus === 'ONLINE') {
                window.showNotification(
                    'Auto Alignment',
                    'Starting 3-star alignment procedure...',
                    'info'
                );

                setTimeout(() => {
                    window.showNotification(
                        'Alignment Complete',
                        'Telescope aligned with ±2 arcmin precision',
                        'success'
                    );
                }, 4000);
            } else {
                window.showNotification('Error', 'Telescope not connected', 'error');
            }
        },

        startPhotoSession() {
            window.showNotification(
                'Astrophotography Mode',
                'Initializing imaging sequence...',
                'info'
            );
        },

        openPlanetarium() {
            window.showNotification(
                'Sky Planetarium',
                'Loading interactive star map...',
                'info'
            );
        },

        lunarPlanning() {
            window.showNotification(
                'Lunar Planning',
                'Opening moon phase calculator...',
                'info'
            );
        }
    }
}
</script>
@endpush
