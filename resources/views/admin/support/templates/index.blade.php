{{-- resources/views/admin/support/templates/index.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Admin - Templates de Support')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Templates de Support</h1>
            <p class="text-gray-300">Gestion des modèles de réponses prédéfinies</p>
        </div>
        <div class="flex items-center space-x-4 mt-4 sm:mt-0">
            <a href="{{ route('admin.support.dashboard') }}"
               class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-300">
                Dashboard
            </a>
            <a href="{{ route('admin.support.templates.create') }}"
               class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition duration-300">
                Nouveau Template
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

    {{-- Liste des templates --}}
    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700">
        @if($templates->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Template
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Catégorie
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Sujet
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Créé par
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
                        @foreach($templates as $template)
                            <tr class="hover:bg-gray-700/30 transition-colors">
                                {{-- Nom du template --}}
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-white font-medium">{{ $template->name }}</div>
                                        <div class="text-gray-400 text-sm">
                                            {{ Str::limit(strip_tags($template->content), 100) }}
                                        </div>
                                    </div>
                                </td>

                                {{-- Catégorie --}}
                                <td class="px-6 py-4">
                                    @if($template->category)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                              style="background-color: {{ $template->category->color }}20; color: {{ $template->category->color }};">
                                            {{ $template->category->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-sm italic">Toutes catégories</span>
                                    @endif
                                </td>

                                {{-- Sujet --}}
                                <td class="px-6 py-4">
                                    @if($template->subject)
                                        <div class="text-white text-sm">{{ Str::limit($template->subject, 50) }}</div>
                                    @else
                                        <span class="text-gray-400 text-sm italic">Pas de sujet défini</span>
                                    @endif
                                </td>

                                {{-- Créé par --}}
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-white text-sm">{{ $template->creator->name }}</div>
                                        <div class="text-gray-400 text-xs">{{ $template->created_at->format('d/m/Y') }}</div>
                                    </div>
                                </td>

                                {{-- Statut --}}
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $template->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        {{-- Prévisualiser --}}
                                        <button onclick="previewTemplate({{ $template->id }})"
                                                class="text-blue-400 hover:text-blue-300 transition-colors"
                                                title="Prévisualiser">
                                            👁️
                                        </button>

                                        {{-- Éditer --}}
                                        <a href="{{ route('admin.support.templates.edit', $template) }}"
                                           class="text-yellow-400 hover:text-yellow-300 transition-colors"
                                           title="Éditer">
                                            ✏️
                                        </a>

                                        {{-- Activer/Désactiver --}}
                                        <form method="POST" action="{{ route('admin.support.templates.toggle-status', $template) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="text-orange-400 hover:text-orange-300 transition-colors"
                                                    title="{{ $template->is_active ? 'Désactiver' : 'Activer' }}">
                                                {{ $template->is_active ? '⏸️' : '▶️' }}
                                            </button>
                                        </form>

                                        {{-- Supprimer --}}
                                        <form method="POST" action="{{ route('admin.support.templates.destroy', $template) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-400 hover:text-red-300 transition-colors"
                                                    title="Supprimer"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce template ?')">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($templates->hasPages())
                <div class="px-6 py-4 border-t border-gray-700">
                    {{ $templates->links() }}
                </div>
            @endif
        @else
            <div class="py-12 text-center">
                <div class="text-gray-400">
                    <div class="text-4xl mb-4">📝</div>
                    <div class="text-lg mb-2">Aucun template trouvé</div>
                    <div class="text-sm">Commencez par créer votre premier template de réponse</div>
                </div>
            </div>
        @endif
    </div>

    {{-- Conseils --}}
    <div class="mt-8 bg-blue-500/20 border border-blue-500 rounded-lg p-6">
        <h3 class="text-blue-100 font-medium mb-2">💡 Conseils pour les templates</h3>
        <ul class="text-blue-100 text-sm space-y-1">
            <li>• Utilisez des variables comme {nom} ou {sujet} dans vos templates</li>
            <li>• Définissez un sujet pour pré-remplir automatiquement le champ sujet</li>
            <li>• Associez vos templates à une catégorie spécifique pour un accès plus rapide</li>
            <li>• Les templates inactifs n'apparaissent plus dans les sélecteurs</li>
        </ul>
    </div>
</div>

{{-- Modal de prévisualisation --}}
<div id="previewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-800 rounded-lg border border-gray-600 max-w-4xl w-full max-h-[80vh] overflow-hidden">
            <div class="flex items-center justify-between p-6 border-b border-gray-600">
                <h3 class="text-xl font-semibold text-white">Prévisualisation du Template</h3>
                <button onclick="closePreview()" class="text-gray-400 hover:text-white">
                    ✕
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-96">
                <div id="previewContent" class="text-white">
                    <!-- Contenu du template chargé via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewTemplate(templateId) {
    fetch(`/admin/support/templates/${templateId}/content`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }

            let content = '<div class="space-y-4">';

            if (data.subject) {
                content += `<div>
                    <h4 class="font-medium text-gray-300 mb-2">Sujet :</h4>
                    <p class="text-white bg-gray-700 px-3 py-2 rounded">${data.subject}</p>
                </div>`;
            }

            content += `<div>
                <h4 class="font-medium text-gray-300 mb-2">Contenu :</h4>
                <div class="text-white bg-gray-700 px-3 py-2 rounded whitespace-pre-wrap">${data.content}</div>
            </div></div>`;

            document.getElementById('previewContent').innerHTML = content;
            document.getElementById('previewModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors du chargement du template');
        });
}

function closePreview() {
    document.getElementById('previewModal').classList.add('hidden');
}

// Fermer la modal en cliquant à l'extérieur
document.getElementById('previewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePreview();
    }
});
</script>
@endsection
