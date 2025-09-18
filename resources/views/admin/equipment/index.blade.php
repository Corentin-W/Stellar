{{-- resources/views/admin/equipment/index.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Gestion du Matériel - Admin')

@section('content')
<div class="min-h-screen p-6" style="background: linear-gradient(135deg, #0a0a0f 0%, #1a1a2e 100%);">

    <!-- Header -->
    <div class="max-w-7xl mx-auto mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Gestion du Matériel</h1>
                <p class="text-white/60">Gérez les équipements d'observation astronomique</p>
            </div>
            <a href="{{ route('admin.equipment.create') }}"
               class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 shadow-lg hover:shadow-xl">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Ajouter un équipement
            </a>
        </div>
    </div>

    <div class="max-w-7xl mx-auto">

        <!-- Filtres -->
        <div class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">

                <!-- Recherche -->
                <div>
                    <label class="block text-white/70 text-sm font-medium mb-2">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Nom, description, lieu..."
                           class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white placeholder-white/50 focus:outline-none focus:border-blue-500 focus:bg-white/20">
                </div>

                <!-- Type -->
                <div>
                    <label class="block text-white/70 text-sm font-medium mb-2">Type</label>
                    <select name="type" class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500">
                        <option value="">Tous les types</option>
                        @foreach(\App\Models\Equipment::TYPES as $key => $label)
                            <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }} class="bg-gray-800">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Statut -->
                <div>
                    <label class="block text-white/70 text-sm font-medium mb-2">Statut</label>
                    <select name="status" class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500">
                        <option value="">Tous les statuts</option>
                        @foreach(\App\Models\Equipment::STATUSES as $key => $label)
                            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }} class="bg-gray-800">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                        Filtrer
                    </button>
                    <a href="{{ route('admin.equipment.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Stats rapides -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            @php
                $totalEquipment = \App\Models\Equipment::count();
                $availableEquipment = \App\Models\Equipment::where('status', 'available')->count();
                $maintenanceEquipment = \App\Models\Equipment::where('status', 'maintenance')->count();
                $featuredEquipment = \App\Models\Equipment::where('is_featured', true)->count();
            @endphp

            <div class="bg-gradient-to-br from-blue-500/20 to-blue-600/20 border border-blue-500/30 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-200 text-sm font-medium">Total Équipements</p>
                        <p class="text-white text-2xl font-bold">{{ $totalEquipment }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-500/20 to-green-600/20 border border-green-500/30 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-200 text-sm font-medium">Disponibles</p>
                        <p class="text-white text-2xl font-bold">{{ $availableEquipment }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-orange-500/20 to-orange-600/20 border border-orange-500/30 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-200 text-sm font-medium">En maintenance</p>
                        <p class="text-white text-2xl font-bold">{{ $maintenanceEquipment }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.268 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-500/20 to-purple-600/20 border border-purple-500/30 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-200 text-sm font-medium">Mis en avant</p>
                        <p class="text-white text-2xl font-bold">{{ $featuredEquipment }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des équipements -->
        <div class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 overflow-hidden">
            @if($equipment->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-white/10">
                            <tr>
                                <th class="text-left p-4 text-white/80 font-medium">Image</th>
                                <th class="text-left p-4 text-white/80 font-medium">Équipement</th>
                                <th class="text-left p-4 text-white/80 font-medium">Type</th>
                                <th class="text-left p-4 text-white/80 font-medium">Statut</th>
                                <th class="text-left p-4 text-white/80 font-medium">Lieu</th>
                                <th class="text-left p-4 text-white/80 font-medium">Prix/h</th>
                                <th class="text-left p-4 text-white/80 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            @foreach($equipment as $item)
                                <tr class="hover:bg-white/5 transition-colors">
                                    <!-- Image -->
                                    <td class="p-4">
                                        @if($item->main_image)
                                            <img src="{{ Storage::url($item->main_image) }}"
                                                 alt="{{ $item->name }}"
                                                 class="w-16 h-16 object-cover rounded-lg border border-white/20">
                                        @else
                                            <div class="w-16 h-16 bg-white/10 rounded-lg border border-white/20 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Équipement -->
                                    <td class="p-4">
                                        <div class="flex items-center space-x-3">
                                            <div>
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-white font-medium">{{ $item->name }}</span>
                                                    @if($item->is_featured)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-300 border border-yellow-500/30">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.922-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                            </svg>
                                                            Vedette
                                                        </span>
                                                    @endif
                                                    @if(!$item->is_active)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-500/20 text-gray-300 border border-gray-500/30">
                                                            Inactif
                                                        </span>
                                                    @endif
                                                </div>
                                                @if($item->description)
                                                    <p class="text-white/60 text-sm mt-1">{{ Str::limit($item->description, 60) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Type -->
                                    <td class="p-4">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                            {{ $item->type_label }}
                                        </span>
                                    </td>

                                    <!-- Statut -->
                                    <td class="p-4">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $item->getStatusBadgeClass() }}/20 text-white border border-{{ str_replace('bg-', '', $item->getStatusBadgeClass()) }}/30">
                                            {{ $item->status_label }}
                                        </span>
                                    </td>

                                    <!-- Lieu -->
                                    <td class="p-4">
                                        <span class="text-white/80">{{ $item->location ?: '—' }}</span>
                                    </td>

                                    <!-- Prix -->
                                    <td class="p-4">
                                        <span class="text-white/80">
                                            {{ $item->price_per_hour_credits ? number_format($item->price_per_hour_credits) . ' crédits' : '—' }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="p-4">
                                        <div class="flex items-center space-x-2">
                                            <!-- Voir -->
                                            <a href="{{ route('admin.equipment.show', $item) }}"
                                               class="text-blue-400 hover:text-blue-300 transition-colors" title="Voir">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>

                                            <!-- Éditer -->
                                            <a href="{{ route('admin.equipment.edit', $item) }}"
                                               class="text-yellow-400 hover:text-yellow-300 transition-colors" title="Éditer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>

                                            <!-- Toggle Statut -->
                                            <form method="POST" action="{{ route('admin.equipment.toggle-status', $item) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="text-green-400 hover:text-green-300 transition-colors"
                                                        title="Changer le statut">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                                    </svg>
                                                </button>
                                            </form>

                                            <!-- Supprimer -->
                                            <form method="POST" action="{{ route('admin.equipment.destroy', $item) }}"
                                                  class="inline"
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet équipement ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-red-400 hover:text-red-300 transition-colors"
                                                        title="Supprimer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto w-16 h-16 text-white/40 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <h3 class="text-white text-lg font-medium mb-2">Aucun équipement trouvé</h3>
                    <p class="text-white/60 mb-6">Ajoutez votre premier équipement d'observation.</p>
                    <a href="{{ route('admin.equipment.create') }}"
                       class="inline-flex items-center px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Ajouter un équipement
                    </a>
                </div>
            @endif
        </div>

        <!-- Pagination -->
        @if($equipment->hasPages())
            <div class="mt-8 flex justify-center">
                <div class="bg-white/5 backdrop-blur-sm rounded-lg border border-white/10 p-2">
                    {{ $equipment->appends(request()->query())->links('pagination::tailwind') }}
                </div>
            </div>
        @endif

    </div>
</div>

@if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         class="fixed top-6 right-6 bg-green-500/20 border border-green-500/30 text-green-200 px-6 py-4 rounded-lg backdrop-blur-sm z-50">
        {{ session('success') }}
    </div>
@endif
@endsection

@push('styles')
<style>
.bg-green-500\/20 { background-color: rgb(34 197 94 / 0.2); }
.border-green-500\/30 { border-color: rgb(34 197 94 / 0.3); }
.bg-red-500\/20 { background-color: rgb(239 68 68 / 0.2); }
.border-red-500\/30 { border-color: rgb(239 68 68 / 0.3); }
.bg-orange-500\/20 { background-color: rgb(249 115 22 / 0.2); }
.border-orange-500\/30 { border-color: rgb(249 115 22 / 0.3); }
.bg-blue-500\/20 { background-color: rgb(59 130 246 / 0.2); }
.border-blue-500\/30 { border-color: rgb(59 130 246 / 0.3); }
</style>
@endpush
