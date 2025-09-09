<!-- resources/views/dashboard.blade.php -->
@extends('layouts.app')

@section('title', 'Dashboard - TelescopeApp')
@section('page-title', 'Dashboard')

@push('styles')
<style>
.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: var(--space-xl);
    margin-bottom: var(--space-2xl);
}

.dashboard-card {
    background: var(--glass-bg);
    backdrop-filter: var(--blur-md);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    padding: var(--space-xl);
    transition: var(--transition-base);
}

.dashboard-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-xl);
    border-color: var(--glass-border-hover);
}

.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--space-lg);
}

.card-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.card-icon {
    width: 48px;
    height: 48px;
    background: var(--color-primary);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-inverse);
}

.metric-value {
    font-size: 32px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: var(--space-sm);
    line-height: 1;
}

.metric-label {
    font-size: 14px;
    color: var(--text-secondary);
    margin-bottom: var(--space-lg);
}

.metric-change {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    font-size: 12px;
    font-weight: 500;
}

.metric-change.positive {
    color: var(--color-success);
}

.metric-change.negative {
    color: var(--color-error);
}

.section-title {
    font-size: 20px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: var(--space-lg);
}

.telescope-status-card {
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
    color: var(--text-inverse);
    border: none;
}

.telescope-status-card .card-title,
.telescope-status-card .metric-value,
.telescope-status-card .metric-label {
    color: var(--text-inverse);
}

.status-indicator-large {
    width: 12px;
    height: 12px;
    border-radius: var(--radius-full);
    background: var(--color-success);
    animation: pulse 2s infinite;
    margin-right: var(--space-sm);
}

.activity-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: var(--space-xl);
    margin-bottom: var(--space-2xl);
}

.session-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
}

.session-item {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    padding: var(--space-lg);
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    transition: var(--transition-base);
}

.session-item:hover {
    background: var(--glass-bg-hover);
    transform: translateX(4px);
}

.session-time {
    font-size: 12px;
    color: var(--text-tertiary);
    font-weight: 500;
    min-width: 60px;
}

.session-target {
    flex: 1;
    font-weight: 500;
    color: var(--text-primary);
}

.session-duration {
    font-size: 12px;
    color: var(--text-secondary);
}

.session-status {
    padding: 4px 8px;
    border-radius: var(--radius-md);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.session-status.active {
    background: rgba(16, 185, 129, 0.1);
    color: var(--color-success);
}

.session-status.scheduled {
    background: rgba(245, 158, 11, 0.1);
    color: var(--color-warning);
}

.session-status.completed {
    background: rgba(148, 163, 184, 0.1);
    color: var(--text-tertiary);
}

.weather-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: var(--text-inverse);
    border: none;
}

.weather-card .card-title,
.weather-card .metric-value,
.weather-card .metric-label {
    color: var(--text-inverse);
}

.weather-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-lg);
    margin-top: var(--space-lg);
}

.weather-metric {
    text-align: center;
}

.weather-metric-value {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 4px;
}

.weather-metric-label {
    font-size: 12px;
    opacity: 0.8;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--space-lg);
}

.action-button {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--space-md);
    padding: var(--space-xl);
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    text-decoration: none;
    color: var(--text-primary);
    transition: var(--transition-base);
    cursor: pointer;
}

.action-button:hover {
    background: var(--glass-bg-hover);
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.action-icon {
    width: 48px;
    height: 48px;
    background: var(--color-primary);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-inverse);
}

.action-title {
    font-weight: 600;
    text-align: center;
}

.action-description {
    font-size: 12px;
    color: var(--text-secondary);
    text-align: center;
}

@media (max-width: 1024px) {
    .dashboard-grid {
        grid-template-columns: 1fr 1fr;
    }

    .activity-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }

    .quick-actions {
        grid-template-columns: 1fr 1fr;
    }
}
</style>
@endpush

@section('content')
<div x-data="dashboardData()">
    <!-- Metrics Overview -->
    <div class="dashboard-grid">
        <!-- Telescope Status -->
        <div class="dashboard-card telescope-status-card">
            <div class="card-header">
                <h3 class="card-title">Telescope Status</h3>
                <div class="card-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
            </div>
            <div class="metric-value" x-text="telescopeStatus.toUpperCase()"></div>
            <div class="metric-label">
                <div class="status-indicator-large"></div>
                Connected and tracking
            </div>
            <div class="metric-change positive">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7 14l5-5 5 5z"/>
                </svg>
                99.2% uptime this month
            </div>
        </div>

        <!-- Total Sessions -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">Sessions</h3>
                <div class="card-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                    </svg>
                </div>
            </div>
            <div class="metric-value" x-text="totalSessions"></div>
            <div class="metric-label">This month</div>
            <div class="metric-change positive">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7 14l5-5 5 5z"/>
                </svg>
                +12% from last month
            </div>
        </div>

        <!-- Images Captured -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">Images</h3>
                <div class="card-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                    </svg>
                </div>
            </div>
            <div class="metric-value" x-text="imagesCaptured"></div>
            <div class="metric-label">Captured this month</div>
            <div class="metric-change positive">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7 14l5-5 5 5z"/>
                </svg>
                +24% from last month
            </div>
        </div>
    </div>

    <!-- Activity Overview -->
    <div class="activity-grid">
        <!-- Recent Sessions -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">Recent Sessions</h3>
                <a href="" class="btn-secondary">View All</a>
            </div>
            <div class="session-list">
                <template x-for="session in recentSessions" :key="session.id">
                    <div class="session-item">
                        <div class="session-time" x-text="session.time"></div>
                        <div class="session-target" x-text="session.target"></div>
                        <div class="session-duration" x-text="session.duration"></div>
                        <div class="session-status" :class="session.status" x-text="session.status"></div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Weather Card -->
        <div class="dashboard-card weather-card">
            <div class="card-header">
                <h3 class="card-title">Weather Conditions</h3>
                <div class="card-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
            </div>
            <div class="metric-value" x-text="weather.temperature + '°C'"></div>
            <div class="metric-label" x-text="weather.condition"></div>

            <div class="weather-details">
                <div class="weather-metric">
                    <div class="weather-metric-value" x-text="weather.humidity + '%'"></div>
                    <div class="weather-metric-label">Humidity</div>
                </div>
                <div class="weather-metric">
                    <div class="weather-metric-value" x-text="weather.windSpeed + ' km/h'"></div>
                    <div class="weather-metric-label">Wind Speed</div>
                </div>
                <div class="weather-metric">
                    <div class="weather-metric-value" x-text="weather.visibility + ' km'"></div>
                    <div class="weather-metric-label">Visibility</div>
                </div>
                <div class="weather-metric">
                    <div class="weather-metric-value" x-text="weather.cloudCover + '%'"></div>
                    <div class="weather-metric-label">Cloud Cover</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <h2 class="section-title">Quick Actions</h2>
    <div class="quick-actions">
        <a href="" class="action-button">
            <div class="action-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <div class="action-title">Control Telescope</div>
            <div class="action-description">Take remote control of the telescope</div>
        </a>

        <a href="" class="action-button">
            <div class="action-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                </svg>
            </div>
            <div class="action-title">Book Session</div>
            <div class="action-description">Schedule a new imaging session</div>
        </a>

        <a href="" class="action-button">
            <div class="action-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                </svg>
            </div>
            <div class="action-title">View Gallery</div>
            <div class="action-description">Browse your captured images</div>
        </a>

        <a href="" class="action-button">
            <div class="action-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8 0-1.85.63-3.55 1.69-4.9.16-.21.43-.1.43.17v.73c0 4.97 4.03 9 9 9h.73c.27 0 .38.27.17.43-1.35 1.06-3.05 1.69-4.9 1.69z"/>
                </svg>
            </div>
            <div class="action-title">Lunar Calendar</div>
            <div class="action-description">Check moon phases and optimal times</div>
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
function dashboardData() {
    return {
        telescopeStatus: 'online',
        totalSessions: 24,
        imagesCaptured: 156,
        recentSessions: [
            {
                id: 1,
                time: '19:30',
                target: 'Andromeda Galaxy (M31)',
                duration: '2h 30m',
                status: 'completed'
            },
            {
                id: 2,
                time: '21:00',
                target: 'Orion Nebula (M42)',
                duration: '1h 45m',
                status: 'active'
            },
            {
                id: 3,
                time: '22:45',
                target: 'Ring Nebula (M57)',
                duration: '3h 00m',
                status: 'scheduled'
            }
        ],
        weather: {
            temperature: -2,
            condition: 'Clear skies',
            humidity: 45,
            windSpeed: 8,
            visibility: 25,
            cloudCover: 5
        },

        init() {
            // Update data periodically
            setInterval(() => {
                this.updateWeatherData();
            }, 300000); // Every 5 minutes
        },

        updateWeatherData() {
            // Simulate weather updates
            this.weather.temperature = Math.floor(Math.random() * 20) - 10;
            this.weather.humidity = Math.floor(Math.random() * 50) + 30;
            this.weather.windSpeed = Math.floor(Math.random() * 15) + 1;
            this.weather.cloudCover = Math.floor(Math.random() * 30);
        }
    }
}
</script>
@endpush
