@extends('layouts.admin')

@section('title', $template->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ $template->name }}
                </h1>
                <div class="mt-2 flex items-center gap-3">
                    <span class="px-3 py-1 text-sm font-semibold rounded-full
                        {{ $template->difficulty === 'beginner' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $template->difficulty === 'intermediate' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $template->difficulty === 'advanced' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ $template->difficulty_label }}
                    </span>
                    <span class="text-gray-600 dark:text-gray-400">{{ $template->type }} • {{ $template->constellation }}</span>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.target-templates.edit', $template) }}"
                   class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    ✏️ Éditer
                </a>
                <a href="{{ route('admin.target-templates.index') }}"
                   class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                    ← Retour
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Colonne principale --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Images --}}
            @if($template->preview_image)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <img src="{{ asset('storage/' . $template->preview_image) }}"
                     alt="{{ $template->name }}"
                     class="w-full h-auto">
            </div>
            @endif

            {{-- Description --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    📖 Description
                </h2>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                    {{ $template->short_description }}
                </p>
                @if($template->full_description)
                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                        {{ $template->full_description }}
                    </p>
                </div>
                @endif
            </div>

            {{-- Conseils --}}
            @if($template->tips)
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h3 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">💡 Conseils</h3>
                        <p class="text-blue-800 dark:text-blue-200">{{ $template->tips }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Paramètres recommandés --}}
            @if($template->recommended_shots)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    📷 Acquisitions Recommandées
                </h2>
                <div class="space-y-3">
                    @foreach($template->recommended_shots as $shot)
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center gap-4">
                            <span class="px-3 py-1 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded font-semibold text-sm">
                                {{ $shot['filter_name'] }}
                            </span>
                            <span class="text-gray-700 dark:text-gray-300">
                                {{ $shot['num'] }}× {{ $shot['exposure'] }}s
                            </span>
                        </div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            Binning: {{ $shot['binning'] ?? 1 }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @if($template->estimated_time)
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-semibold">Temps estimé: {{ $template->estimated_time }}</span>
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>

        {{-- Colonne latérale --}}
        <div class="space-y-6">
            {{-- Informations rapides --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">
                    ℹ️ Informations
                </h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">ID Template</dt>
                        <dd class="text-gray-900 dark:text-white font-mono">{{ $template->template_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Statut</dt>
                        <dd>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $template->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Ordre d'affichage</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $template->display_order }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Coordonnées --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">
                    🌐 Coordonnées J2000
                </h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Ascension Droite</dt>
                        <dd class="text-gray-900 dark:text-white font-mono">
                            {{ sprintf('%02d:%02d:%04.1f', $template->ra_hours, $template->ra_minutes, $template->ra_seconds) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Déclinaison</dt>
                        <dd class="text-gray-900 dark:text-white font-mono">
                            {{ sprintf('%s%02d:%02d:%04.1f', $template->dec_degrees >= 0 ? '+' : '-', abs($template->dec_degrees), $template->dec_minutes, $template->dec_seconds) }}
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Meilleurs mois --}}
            @if($template->best_months && count($template->best_months) > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">
                    📅 Meilleurs mois
                </h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($template->best_months as $month)
                    <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm font-medium">
                        {{ $month }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Miniature --}}
            @if($template->thumbnail_image)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">
                    🖼️ Miniature
                </h3>
                <img src="{{ asset('storage/' . $template->thumbnail_image) }}"
                     alt="{{ $template->name }}"
                     class="w-full h-auto rounded-lg">
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
