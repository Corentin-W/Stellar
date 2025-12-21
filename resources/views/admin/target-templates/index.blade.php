@extends('layouts.admin')

@section('title', 'Gestion des Templates de Targets')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Gestion des Templates de Targets
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Gérez le catalogue des cibles pour le mode assisté
                </p>
            </div>
            <a href="{{ route('admin.target-templates.create') }}"
               class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                + Nouveau Template
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-green-800 font-medium">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    {{-- Filters --}}
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <form method="GET" class="flex gap-4">
            <input type="text"
                   name="search"
                   value="{{ $filters['search'] ?? '' }}"
                   placeholder="Rechercher..."
                   class="flex-1 px-4 py-2 border rounded-lg">

            <select name="difficulty" class="px-4 py-2 border rounded-lg">
                <option value="all">Toutes difficultés</option>
                <option value="beginner" {{ ($filters['difficulty'] ?? '') == 'beginner' ? 'selected' : '' }}>Débutant</option>
                <option value="intermediate" {{ ($filters['difficulty'] ?? '') == 'intermediate' ? 'selected' : '' }}>Intermédiaire</option>
                <option value="advanced" {{ ($filters['difficulty'] ?? '') == 'advanced' ? 'selected' : '' }}>Avancé</option>
            </select>

            <select name="status" class="px-4 py-2 border rounded-lg">
                <option value="all">Tous statuts</option>
                <option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }}>Actifs</option>
                <option value="inactive" {{ ($filters['status'] ?? '') == 'inactive' ? 'selected' : '' }}>Inactifs</option>
            </select>

            <button type="submit" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Filtrer
            </button>
        </form>
    </div>

    {{-- Templates List --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Aperçu
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Template
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Type
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Difficulté
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Statut
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($templates as $template)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($template->thumbnail_image)
                        <img src="{{ asset('storage/' . $template->thumbnail_image) }}"
                             alt="{{ $template->name }}"
                             class="h-12 w-12 rounded object-cover">
                        @else
                        <div class="h-12 w-12 rounded bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                            <span class="text-2xl">🌌</span>
                        </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900 dark:text-white">{{ $template->name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $template->constellation }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $template->type }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            {{ $template->difficulty === 'beginner' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $template->difficulty === 'intermediate' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $template->difficulty === 'advanced' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ $template->difficulty_label }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $template->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                        <a href="{{ route('admin.target-templates.edit', $template) }}"
                           class="text-blue-600 hover:text-blue-900">
                            Éditer
                        </a>
                        <form action="{{ route('admin.target-templates.destroy', $template) }}"
                              method="POST"
                              class="inline"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce template ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        Aucun template trouvé
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($templates->hasPages())
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700">
            {{ $templates->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
