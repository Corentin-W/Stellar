@extends('layouts.astral-app')

@section('title', 'Préparer ma session')

@section('content')
@php
    $locale = app()->getLocale();
    $observatory = config('observatory');
@endphp
<div class="min-h-screen p-6">
    <div class="max-w-6xl mx-auto space-y-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm uppercase tracking-wider text-white/40 mb-1">Préparation session</p>
                <h1 class="text-3xl md:text-4xl font-bold text-white flex items-baseline gap-3">
                    Sélection de cible
                    <span class="text-sm text-white/50 font-normal">
                        {{ $booking->start_datetime->setTimezone($observatory['timezone'])->isoFormat('dddd D MMM HH:mm') }}
                    </span>
                </h1>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('bookings.my-bookings', ['locale' => $locale]) }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-white/5 px-4 py-2 text-sm font-medium text-white/70 hover:bg-white/10">
                    ⬅️ Mes réservations
                </a>
                <a href="{{ route('bookings.access', ['locale' => $locale, 'booking' => $booking]) }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-purple-500/30 px-4 py-2 text-sm font-semibold text-white hover:bg-purple-500/40">
                    Page d'accès
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-xl border border-green-500/40 bg-green-500/15 px-4 py-3 text-green-100">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if($currentPlan)
            <div class="dashboard-card p-6 border border-white/10 bg-white/5">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div class="flex items-start gap-4">
                        @if(!empty($currentPlan['thumbnail']))
                            <img src="{{ $currentPlan['thumbnail'] }}" alt="Cible sélectionnée"
                                 class="h-24 w-24 rounded-lg object-cover border border-white/10">
                        @endif
                        <div>
                            <p class="text-sm uppercase tracking-wider text-white/40 mb-1">Cible programmée</p>
                            <h2 class="text-2xl font-semibold text-white">{{ $currentPlan['name'] ?? $booking->target_name }}</h2>
                            @if(!empty($currentPlan['constellation']))
                                <p class="text-white/60 text-sm">Constellation : {{ $currentPlan['constellation'] }}</p>
                            @endif
                            @if(!empty($currentPlan['recommended_duration']['hours']))
                                <p class="text-white/60 text-sm">
                                    Durée recommandée : {{ $currentPlan['recommended_duration']['hours'] }} h
                                </p>
                            @endif
                            @if(!empty($currentPlan['notes']))
                                <p class="text-white/50 text-sm mt-2">{{ $currentPlan['notes'] }}</p>
                            @endif
                        </div>
                    </div>
                    <form method="POST"
                          action="{{ route('bookings.prepare.destroy', ['locale' => $locale, 'booking' => $booking]) }}"
                          onsubmit="return confirm('Réinitialiser la préparation pour cette session ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-red-500/20 px-4 py-2 text-sm font-medium text-red-200 hover:bg-red-500/30">
                            Réinitialiser
                        </button>
                    </form>
                </div>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div class="rounded-lg border border-white/10 bg-black/20 p-4">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-white/60 mb-2">Filtres retenus</h3>
                        <ul class="space-y-2 text-sm text-white/70">
                            @foreach($currentPlan['recommended_filters'] ?? [] as $filter)
                                <li class="flex justify-between">
                                    <span>{{ $filter['filter'] ?? '—' }}</span>
                                    <span>{{ $filter['frames'] ?? '?' }} x {{ $filter['exposure'] ?? '?' }}s</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    @if(!empty($currentPlan['visibility']['samples']))
                        <div class="rounded-lg border border-white/10 bg-black/20 p-4">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-white/60 mb-2">Visibilité</h3>
                            <p class="text-white/70 text-sm mb-3">
                                Altitude max : {{ $currentPlan['visibility']['peak_altitude'] ?? '—' }}°
                            </p>
                            <ul class="space-y-1 text-xs text-white/60">
                                @foreach($currentPlan['visibility']['samples'] ?? [] as $sample)
                                    <li>
                                        {{ \Carbon\Carbon::parse($sample['time'])->setTimezone($observatory['timezone'])->format('H:i') }}
                                        — {{ $sample['altitude'] }}°
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white">Suggestions pour ce créneau</h2>
            <p class="text-sm text-white/50">
                Localisation observatoire : {{ $observatory['latitude'] }}°, {{ $observatory['longitude'] }}° • fuseau {{ $observatory['timezone'] }}
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            @foreach($suggestions as $target)
                <div class="rounded-2xl border border-white/10 bg-white/5 overflow-hidden">
                    <div class="relative">
                        <img src="{{ $target['thumbnail'] }}" alt="{{ $target['name'] }}"
                             class="h-44 w-full object-cover">
                        <span class="absolute top-3 left-3 rounded-full bg-black/60 px-3 py-1 text-xs font-semibold text-white">
                            Score {{ $target['score'] }}%
                        </span>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-semibold text-white">{{ $target['name'] }}</h3>
                                <p class="text-sm text-white/60">
                                    {{ ucfirst($target['type']) }} • {{ $target['constellation'] }}
                                </p>
                            </div>
                            <div class="text-right text-xs text-white/50">
                                Magnitude {{ $target['magnitude'] }}<br>
                                Altitude max {{ $target['visibility']['peak_altitude'] ?? '—' }}°
                            </div>
                        </div>

                        <p class="text-sm text-white/70">{{ $target['description'] }}</p>

                        <div class="rounded-lg border border-white/10 bg-black/20 p-3">
                            <div class="text-xs uppercase tracking-wide text-white/50 mb-2">Filtres suggérés</div>
                            <ul class="space-y-2 text-sm text-white/70">
                                @foreach($target['recommended_filters'] as $filter)
                                    <li class="flex justify-between">
                                        <span>{{ $filter['filter'] }}</span>
                                        <span>{{ $filter['frames'] }} x {{ $filter['exposure'] }}s</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="text-xs text-white/50">
                            Période idéale : {{ implode(', ', $target['best_months']) }}
                        </div>

                        <form method="POST" action="{{ route('bookings.prepare.store', ['locale' => $locale, 'booking' => $booking]) }}" class="space-y-3">
                            @csrf
                            <input type="hidden" name="target_slug" value="{{ $target['slug'] }}">
                            <label class="block">
                                <span class="text-xs text-white/60">Notes personnelles (optionnel)</span>
                                <textarea name="notes" rows="2"
                                          class="mt-1 w-full rounded-lg border border-white/10 bg-black/30 px-3 py-2 text-sm text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/30"
                                          placeholder="Intentions de cadrage, variantes de filtres…"></textarea>
                            </label>
                            <button type="submit"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-purple-500/30 px-4 py-2 text-sm font-semibold text-white hover:bg-purple-500/40">
                                Programmer cette cible
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
