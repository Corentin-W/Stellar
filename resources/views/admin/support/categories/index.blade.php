{{-- resources/views/admin/support/categories/index.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Admin - Catégories de Support')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Catégories de Support</h1>
            <p class="text-gray-300">Gestion des catégories pour organiser les tickets</p>
        </div>
        <div class="flex items-center space-x-4 mt-4 sm:mt-0">
            <a href="{{ route('admin.support.dashboard') }}"
               class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-300">
                🏠 Dashboard
            </a>
            <a href="{{ route('admin.support.categories.create') }}"
               class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition duration-300">
                ➕ Nouvelle Catégorie
            </a>
        </div>
    </div>

    {{-- Messages flash --}}
    @if(session('success'))
        <div class="bg-green-500/20 border border-green-500 text-green-100 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-500/20 border border-red-500 text-red-100 px-4 py-3 rounded-lg mb-6">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Liste des catégories --}}
    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            Ordre
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            Catégorie
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            Description
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            Couleur
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            Tickets
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            Statut
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($categories as $category)
                        <tr class="hover:bg-gray-700/30 transition-colors" id="category-{{ $category->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-white font-medium">{{ $category->sort_order }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 rounded-full mr-3" style="background-color: {{ $category->color }};"></div>
                                    <div>
                                        <div class="text-white font-medium">{{ $category->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-300 text-sm max-w-xs truncate">
                                    {{ $category->description ?: 'Aucune description' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-6 h-6 rounded border border-gray-600 mr-2" style="background-color: {{ $category->color }};"></div>
                                    <span class="text-gray-300 text-sm font-mono">{{ $category->color }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $category->tickets_count }} tickets
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    {{-- Éditer --}}
                                    <a href="{{ route('admin.support.categories.edit', $category) }}"
                                       class="text-blue-400 hover:text-blue-300 transition-colors"
                                       title="Éditer">
                                        ✏️
                                    </a>

                                    {{-- Activer/Désactiver --}}
                                    <form method="POST" action="{{ route('admin.support.categories.toggle-status', $category) }}" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="text-yellow-400 hover:text-yellow-300 transition-colors"
                                                title="{{ $category->is_active ? 'Désactiver' : 'Activer' }}">
                                            {{ $category->is_active ? '⏸️' : '▶️' }}
                                        </button>
                                    </form>

                                    {{-- Supprimer --}}
                                    @if($category->tickets_count == 0)
                                        <form method="POST" action="{{ route('admin.support.categories.destroy', $category) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-400 hover:text-red-300 transition-colors"
                                                    title="Supprimer"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')">
                                                🗑️
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-500 cursor-not-allowed" title="Impossible de supprimer : contient des tickets">
                                            🗑️
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <div class="text-4xl mb-4">📂</div>
                                    <div class="text-lg mb-2">Aucune catégorie trouvée</div>
                                    <div class="text-sm">Commencez par créer votre première catégorie de support</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($categories->hasPages())
        <div class="mt-6">
            {{ $categories->links() }}
        </div>
    @endif

    {{-- Aide --}}
    <div class="mt-8 bg-blue-500/20 border border-blue-500 rounded-lg p-6">
        <h3 class="text-blue-100 font-medium mb-2">💡 Conseils pour les catégories</h3>
        <ul class="text-blue-100 text-sm space-y-1">
            <li>• Utilisez des couleurs distinctes pour faciliter l'identification</li>
            <li>• L'ordre d'affichage peut être modifié avec le champ "Ordre"</li>
            <li>• Les catégories inactives n'apparaissent plus dans les formulaires</li>
            <li>• Impossible de supprimer une catégorie contenant des tickets</li>
        </ul>
    </div>
</div>
@endsection
