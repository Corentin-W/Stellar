@extends('layouts.astral-app')

@section('title', 'Mes Targets RoboTarget')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    🎯 Mes Targets RoboTarget
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">
                    Gérez vos cibles d'acquisition automatisées
                </p>
            </div>
            <a href="{{ route('robotarget.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                + Nouvelle Target
            </a>
        </div>

        {{-- User Info Card --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                <div class="text-sm text-gray-600 dark:text-gray-400">Abonnement</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $subscription->getPlanBadge() }} {{ $subscription->getPlanName() }}
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                <div class="text-sm text-gray-600 dark:text-gray-400">Crédits disponibles</div>
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                    {{ $creditsBalance }}
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                <div class="text-sm text-gray-600 dark:text-gray-400">Targets actives</div>
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                    {{ $stats['active'] + $stats['executing'] }}
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                <div class="text-sm text-gray-600 dark:text-gray-400">Targets complétées</div>
                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                    {{ $stats['completed'] }}
                </div>
            </div>
        </div>
    </div>

    {{-- Popular Targets Section --}}
    @php
        $popularTemplates = \App\Models\TargetTemplate::active()
            ->orderBy('display_order')
            ->orderBy('name')
            ->limit(6)
            ->get();
    @endphp
    @if($popularTemplates->count() > 0 && $targets->isEmpty())
    <div class="mb-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                ⭐ Découvrez nos Cibles Populaires
            </h2>
            <p class="text-gray-600 dark:text-gray-400">
                Explorez notre catalogue et créez votre première target en un clic
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($popularTemplates as $template)
            <a href="{{ route('robotarget.create', ['locale' => app()->getLocale()]) }}"
               class="group bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                @if($template->thumbnail_image || $template->preview_image)
                <div class="relative h-48 bg-gradient-to-br from-gray-800 to-gray-900 overflow-hidden">
                    <img src="{{ asset('storage/' . ($template->thumbnail_image ?? $template->preview_image)) }}"
                         alt="{{ $template->name }}"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                         loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                    <div class="absolute bottom-3 left-3 right-3">
                        <h3 class="text-white font-bold text-lg mb-1">{{ $template->name }}</h3>
                        <p class="text-white/80 text-xs">{{ $template->type }} • {{ $template->constellation }}</p>
                    </div>
                    <span class="absolute top-3 right-3 px-2 py-1 rounded text-xs font-medium backdrop-blur-sm
                        {{ $template->difficulty === 'beginner' ? 'bg-green-500/90 text-white' : '' }}
                        {{ $template->difficulty === 'intermediate' ? 'bg-yellow-500/90 text-white' : '' }}
                        {{ $template->difficulty === 'advanced' ? 'bg-red-500/90 text-white' : '' }}">
                        {{ $template->difficulty_label }}
                    </span>
                </div>
                @else
                <div class="h-48 bg-gradient-to-br from-blue-500/20 to-purple-500/20 flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-6xl mb-2">🌌</div>
                        <h3 class="text-gray-900 dark:text-white font-bold">{{ $template->name }}</h3>
                    </div>
                </div>
                @endif
                <div class="p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                        {{ $template->short_description }}
                    </p>
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-1 text-gray-500 dark:text-gray-500">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $template->estimated_time ?? 'N/A' }}
                        </span>
                        <span class="text-blue-600 dark:text-blue-400 font-medium group-hover:underline">
                            Créer cette target →
                        </span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        <div class="mt-6 text-center">
            <a href="{{ route('robotarget.create', ['locale' => app()->getLocale()]) }}"
               class="inline-block bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-8 py-3 rounded-lg font-medium transition-all shadow-lg hover:shadow-xl">
                📖 Voir tout le catalogue
            </a>
        </div>
    </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('robotarget.index') }}" class="flex gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Statut</label>
                <select name="status" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                    <option value="">Tous</option>
                    <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="executing" {{ $filters['status'] === 'executing' ? 'selected' : '' }}>En cours</option>
                    <option value="completed" {{ $filters['status'] === 'completed' ? 'selected' : '' }}>Terminée</option>
                    <option value="error" {{ $filters['status'] === 'error' ? 'selected' : '' }}>Erreur</option>
                    <option value="aborted" {{ $filters['status'] === 'aborted' ? 'selected' : '' }}>Annulée</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition-colors">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    {{-- Targets List --}}
    @if($targets->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
            <div class="text-6xl mb-4">🌌</div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                Aucune target pour le moment
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Créez votre première target pour commencer vos acquisitions automatisées !
            </p>
            <a href="{{ route('robotarget.create') }}"
               class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                Créer ma première target
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($targets as $target)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow">
                    <a href="{{ route('robotarget.show', $target->guid) }}" class="block p-6">
                        <div class="flex items-start justify-between">
                            {{-- Target Info --}}
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                        {{ $target->target_name }}
                                    </h3>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-{{ $target->getStatusColor() }}-100 text-{{ $target->getStatusColor() }}-800">
                                        {{ $target->getStatusLabel() }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600 dark:text-gray-400">
                                    <div>
                                        <span class="font-medium">RA:</span> {{ $target->ra_j2000 }}
                                    </div>
                                    <div>
                                        <span class="font-medium">DEC:</span> {{ $target->dec_j2000 }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Priorité:</span> {{ $target->priority }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Durée estimée:</span> {{ $target->getFormattedDuration() }}
                                    </div>
                                </div>

                                <div class="mt-3 flex gap-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-600 dark:text-gray-400">Shots:</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ $target->shots->count() }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-600 dark:text-gray-400">Crédits:</span>
                                        <span class="font-semibold text-blue-600 dark:text-blue-400">
                                            {{ $target->credits_charged > 0 ? $target->credits_charged : $target->estimated_credits }}
                                        </span>
                                    </div>
                                    @if($target->sessions->count() > 0)
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-600 dark:text-gray-400">Sessions:</span>
                                        <span class="font-semibold text-green-600 dark:text-green-400">{{ $target->sessions->count() }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2 ml-4">
                                @if($target->isPending())
                                    <span class="text-sm text-gray-500">En attente de soumission</span>
                                @elseif($target->isActive() || $target->isExecuting())
                                    <div class="flex items-center gap-2 text-blue-600">
                                        <div class="animate-pulse">●</div>
                                        <span class="text-sm font-medium">En cours...</span>
                                    </div>
                                @elseif($target->isCompleted())
                                    <span class="text-green-600 text-sm font-medium">✓ Terminée</span>
                                @endif
                            </div>
                        </div>

                        {{-- Options de la target --}}
                        <div class="mt-4 flex gap-2 flex-wrap">
                            @if($target->c_moon_down)
                                <span class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded text-xs">
                                    🌙 Nuit noire
                                </span>
                            @endif
                            @if($target->c_hfd_mean_limit)
                                <span class="px-2 py-1 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded text-xs">
                                    ⭐ HFD < {{ $target->c_hfd_mean_limit }}
                                </span>
                            @endif
                            @if($target->is_repeat)
                                <span class="px-2 py-1 bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 rounded text-xs">
                                    🔄 Multi-nuits
                                </span>
                            @endif
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
