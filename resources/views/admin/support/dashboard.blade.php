{{-- resources/views/admin/support/dashboard.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Dashboard Support Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Dashboard Support</h1>
            <p class="text-gray-300">Gestion et supervision du système de support</p>
        </div>
        <div class="flex items-center space-x-4 mt-4 sm:mt-0">
            <a href="{{ route('admin.support.tickets.index') }}"
               class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition duration-300">
                Tous les tickets
            </a>
            <a href="{{ route('admin.support.categories.index') }}"
               class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-300">
                Catégories
            </a>
            <a href="{{ route('admin.support.templates.index') }}"
               class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-300">
                Templates
            </a>
        </div>
    </div>

    {{-- Statistiques --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        {{-- Total des tickets --}}
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm font-medium">Total Tickets</p>
                    <p class="text-3xl font-bold text-white">{{ number_format($stats['total_tickets']) }}</p>
                </div>
                <div class="divide-y divide-gray-600 max-h-64 overflow-y-auto">
                    @forelse($myTickets as $ticket)
                        <div class="px-6 py-3 hover:bg-gray-700/30 transition-colors">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-white font-medium text-sm truncate">{{ $ticket->subject }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $ticket->status_color }} ml-2">
                                    {{ $ticket->status_label }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-400 flex items-center justify-between">
                                <span>{{ $ticket->user->name }}</span>
                                <a href="{{ route('admin.support.tickets.show', $ticket->id) }}"
                                   class="text-purple-400 hover:text-purple-300">
                                    Traiter
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-4 text-center text-gray-400 text-sm">
                            Aucun ticket assigné
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
