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
