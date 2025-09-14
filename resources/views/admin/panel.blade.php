{{-- resources/views/admin/panel.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Panel Admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-xl p-6 shadow-2xl border border-white/10">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Panel Admin</h1>
                <p class="text-gray-400">Gestion des utilisateurs et connexion rapide</p>
            </div>
            <div class="bg-red-500/20 text-red-400 px-3 py-1 rounded-full text-sm">
                Mode Administrateur
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Utilisateurs</p>
                        <p class="text-white text-xl font-bold">{{ $stats['total_users'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Actifs</p>
                        <p class="text-white text-xl font-bold">{{ $stats['active_users'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Admins</p>
                        <p class="text-white text-xl font-bold">{{ $stats['admin_users'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-yellow-500/20 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Aujourd'hui</p>
                        <p class="text-white text-xl font-bold">{{ $stats['today_logins'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="mb-6">
            <div class="relative">
                <input type="text"
                       id="userSearch"
                       placeholder="Rechercher un utilisateur..."
                       class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <svg class="absolute right-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white/5 rounded-lg border border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10">
                <h3 class="text-lg font-semibold text-white">Liste des utilisateurs</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-400 uppercase bg-white/5">
                        <tr>
                            <th class="px-6 py-3">Utilisateur</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Statut</th>
                            <th class="px-6 py-3">Inscription</th>
                            <th class="px-6 py-3">Dernière connexion</th>
                            <th class="px-6 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        @foreach($users as $user)
                        <tr class="bg-transparent border-b border-white/10 hover:bg-white/5 user-row"
                            data-user-name="{{ strtolower($user->name) }}"
                            data-user-email="{{ strtolower($user->email) }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-xs mr-3">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-white">{{ $user->name }}</div>
                                        @if($user->admin)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Admin</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-300">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                @if($user->email_verified_at)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Vérifié</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">En attente</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-300">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-gray-300">
                                @if($user->last_login_at)
                                    {{ $user->last_login_at->diffForHumans() }}
                                @else
                                    Jamais
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('admin.login-as', $user->id) }}" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded-lg text-xs hover:bg-blue-500/30 transition-colors"
                                                @if($user->id === auth()->id()) disabled @endif>
                                            @if($user->id === auth()->id())
                                                Vous
                                            @else
                                                Se connecter
                                            @endif
                                        </button>
                                    </form>

                                    <button onclick="toggleAdmin({{ $user->id }}, {{ $user->admin ? 'false' : 'true' }})"
                                            class="px-3 py-1 {{ $user->admin ? 'bg-red-500/20 text-red-400 hover:bg-red-500/30' : 'bg-green-500/20 text-green-400 hover:bg-green-500/30' }} rounded-lg text-xs transition-colors">
                                        {{ $user->admin ? 'Retirer admin' : 'Rendre admin' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-white/10">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('userSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('.user-row');

    rows.forEach(row => {
        const name = row.dataset.userName;
        const email = row.dataset.userEmail;

        if (name.includes(searchTerm) || email.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Toggle admin status
function toggleAdmin(userId, makeAdmin) {
    if (confirm(`Êtes-vous sûr de vouloir ${makeAdmin ? 'rendre cet utilisateur admin' : 'retirer les droits admin à cet utilisateur'} ?`)) {
        fetch(`/admin/toggle-admin/${userId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                admin: makeAdmin
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la modification');
            }
        });
    }
}
</script>
@endsection
