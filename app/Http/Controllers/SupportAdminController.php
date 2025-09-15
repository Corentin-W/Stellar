<?php

// app/Http/Controllers/Admin/SupportAdminController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\SupportTemplate;
use App\Models\SupportAttachment;
use App\Models\SupportTicketHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SupportAdminController extends Controller
{
    // Le middleware 'auth' et 'admin' sont déjà appliqués dans les routes
    // Pas besoin de les ajouter ici

    /**
     * Dashboard principal du support
     */
    public function dashboard()
    {
        $stats = $this->getSupportStats();
        $recentTickets = SupportTicket::with(['user', 'category', 'assignedTo'])
                                     ->latest()
                                     ->limit(10)
                                     ->get();

        $urgentTickets = SupportTicket::where('priority', 'urgent')
                                     ->where('status', '!=', 'closed')
                                     ->with(['user', 'category'])
                                     ->orderBy('created_at', 'desc')
                                     ->limit(5)
                                     ->get();

        $myTickets = SupportTicket::where('assigned_to', auth()->id())
                                  ->where('status', '!=', 'closed')
                                  ->with(['user', 'category'])
                                  ->orderBy('last_reply_at', 'desc')
                                  ->limit(5)
                                  ->get();

        return view('admin.support.dashboard', compact('stats', 'recentTickets', 'urgentTickets', 'myTickets'));
    }

    /**
     * Liste des tickets avec filtres avancés
     */
    public function tickets(Request $request)
    {
        $query = SupportTicket::with(['user', 'category', 'assignedTo']);

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['created_at', 'last_reply_at', 'priority', 'status'])) {
            if ($sortBy === 'priority') {
                // Tri personnalisé pour la priorité
                $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low') " . ($sortOrder === 'desc' ? 'ASC' : 'DESC'));
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        $tickets = $query->paginate(20)->withQueryString();

        // Données pour les filtres
        $categories = SupportCategory::active()->ordered()->get();
        $admins = User::where('admin', true)->orderBy('name')->get();

        return view('admin.support.tickets.index', compact('tickets', 'categories', 'admins'));
    }

    /**
     * Détails d'un ticket (admin)
     */
    public function show(SupportTicket $ticket)
    {
        $ticket->load([
            'user',
            'category',
            'assignedTo',
            'messages' => function($query) {
                $query->with(['user', 'attachmentFiles'])
                      ->orderBy('created_at', 'asc');
            },
            'history' => function($query) {
                $query->with('user')->orderBy('created_at', 'desc');
            }
        ]);

        $categories = SupportCategory::active()->ordered()->get();
        $admins = User::where('admin', true)->orderBy('name')->get();
        $templates = SupportTemplate::active()->orderBy('name')->get();

        return view('admin.support.tickets.show', compact('ticket', 'categories', 'admins', 'templates'));
    }

    /**
     * Répondre à un ticket (admin)
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
            'is_internal' => 'boolean',
            'change_status' => 'nullable|in:open,in_progress,waiting_user,waiting_admin,resolved,closed',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt',
        ]);

        try {
            DB::beginTransaction();

            $isInternal = $request->boolean('is_internal', false);

            // Créer le message
            $message = SupportMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'message' => $request->message,
                'is_internal' => $isInternal,
                'is_system' => false,
            ]);

            // Gérer les fichiers joints
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->storeAttachment($file, $ticket, $message);
                }
            }

            $updateData = [
                'last_reply_at' => now(),
                'last_reply_by' => auth()->id(),
            ];

            // Changer le statut si demandé
            if ($request->filled('change_status') && $request->change_status !== $ticket->status) {
                $oldStatus = $ticket->status;
                $newStatus = $request->change_status;

                $updateData['status'] = $newStatus;

                // Actions spécifiques selon le statut
                switch ($newStatus) {
                    case 'resolved':
                        $updateData['resolved_at'] = now();
                        $updateData['resolved_by'] = auth()->id();
                        break;
                    case 'closed':
                        $updateData['closed_at'] = now();
                        $updateData['closed_by'] = auth()->id();
                        break;
                    case 'open':
                    case 'in_progress':
                        // Réinitialiser les dates de résolution/fermeture
                        $updateData['resolved_at'] = null;
                        $updateData['resolved_by'] = null;
                        $updateData['closed_at'] = null;
                        $updateData['closed_by'] = null;
                        break;
                }

                // Historique du changement de statut
                SupportTicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => auth()->id(),
                    'action' => 'status_changed',
                    'old_value' => $oldStatus,
                    'new_value' => $newStatus,
                    'description' => "Statut changé de '{$oldStatus}' à '{$newStatus}'",
                ]);
            }

            $ticket->update($updateData);

            DB::commit();

            return back()->with('success', 'Réponse ajoutée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Une erreur est survenue lors de l\'envoi de la réponse.']);
        }
    }

    /**
     * Assigner un ticket à un admin
     */
    public function assign(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $oldAssigned = $ticket->assigned_to;
        $newAssigned = $request->assigned_to;

        if ($oldAssigned == $newAssigned) {
            return back()->withErrors(['error' => 'Le ticket est déjà assigné à cette personne.']);
        }

        try {
            DB::beginTransaction();

            $ticket->update(['assigned_to' => $newAssigned]);

            // Message système
            $assignedUser = $newAssigned ? User::find($newAssigned) : null;
            $message = $newAssigned
                ? "Ticket assigné à {$assignedUser->name}."
                : "Ticket non assigné.";

            SupportMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'message' => $message,
                'is_internal' => true,
                'is_system' => true,
            ]);

            // Historique
            SupportTicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'action' => $newAssigned ? 'assigned' : 'unassigned',
                'old_value' => $oldAssigned ? User::find($oldAssigned)->name : null,
                'new_value' => $assignedUser?->name,
                'description' => $message,
            ]);

            DB::commit();

            return back()->with('success', 'Attribution mise à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Une erreur est survenue lors de l\'attribution.']);
        }
    }

    /**
     * Changer la priorité d'un ticket
     */
    public function changePriority(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'priority' => 'required|in:low,normal,high,urgent',
        ]);

        $oldPriority = $ticket->priority;
        $newPriority = $request->priority;

        if ($oldPriority === $newPriority) {
            return back()->withErrors(['error' => 'Le ticket a déjà cette priorité.']);
        }

        try {
            DB::beginTransaction();

            $ticket->update(['priority' => $newPriority]);

            // Message système
            SupportMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'message' => "Priorité changée de '{$oldPriority}' à '{$newPriority}'.",
                'is_internal' => true,
                'is_system' => true,
            ]);

            // Historique
            SupportTicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'action' => 'priority_changed',
                'old_value' => $oldPriority,
                'new_value' => $newPriority,
                'description' => "Priorité changée de '{$oldPriority}' à '{$newPriority}'",
            ]);

            DB::commit();

            return back()->with('success', 'Priorité mise à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Une erreur est survenue lors du changement de priorité.']);
        }
    }

    /**
     * Changer la catégorie d'un ticket
     */
    public function changeCategory(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'category_id' => 'required|exists:support_categories,id',
        ]);

        $oldCategory = $ticket->category;
        $newCategory = SupportCategory::find($request->category_id);

        if ($oldCategory->id === $newCategory->id) {
            return back()->withErrors(['error' => 'Le ticket est déjà dans cette catégorie.']);
        }

        try {
            DB::beginTransaction();

            $ticket->update(['category_id' => $newCategory->id]);

            // Message système
            SupportMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'message' => "Catégorie changée de '{$oldCategory->name}' à '{$newCategory->name}'.",
                'is_internal' => true,
                'is_system' => true,
            ]);

            // Historique
            SupportTicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'action' => 'category_changed',
                'old_value' => $oldCategory->name,
                'new_value' => $newCategory->name,
                'description' => "Catégorie changée de '{$oldCategory->name}' à '{$newCategory->name}'",
            ]);

            DB::commit();

            return back()->with('success', 'Catégorie mise à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Une erreur est survenue lors du changement de catégorie.']);
        }
    }

    /**
     * Télécharger un fichier joint (admin)
     */
    public function downloadAttachment(SupportAttachment $attachment)
    {
        if (!Storage::exists($attachment->file_path)) {
            abort(404, 'Fichier non trouvé.');
        }

        return Storage::download($attachment->file_path, $attachment->original_filename);
    }

    /**
     * Statistiques pour le dashboard
     */
    private function getSupportStats()
    {
        $totalTickets = SupportTicket::count();
        $openTickets = SupportTicket::whereIn('status', ['open', 'in_progress', 'waiting_user', 'waiting_admin'])->count();
        $urgentTickets = SupportTicket::where('priority', 'urgent')
                                     ->where('status', '!=', 'closed')
                                     ->count();

        $todayTickets = SupportTicket::whereDate('created_at', today())->count();
        $avgResponseTime = $this->calculateAverageResponseTime();
        $resolvedToday = SupportTicket::whereDate('resolved_at', today())->count();

        return [
            'total_tickets' => $totalTickets,
            'open_tickets' => $openTickets,
            'urgent_tickets' => $urgentTickets,
            'today_tickets' => $todayTickets,
            'avg_response_time' => $avgResponseTime,
            'resolved_today' => $resolvedToday,
        ];
    }

    /**
     * Calculer le temps de réponse moyen
     */
    private function calculateAverageResponseTime()
    {
        $tickets = SupportTicket::whereNotNull('last_reply_at')
                                ->where('last_reply_by', '!=', DB::raw('user_id'))
                                ->select('created_at', 'last_reply_at')
                                ->get();

        if ($tickets->isEmpty()) {
            return '0h';
        }

        $totalMinutes = $tickets->sum(function($ticket) {
            return $ticket->created_at->diffInMinutes($ticket->last_reply_at);
        });

        $avgMinutes = $totalMinutes / $tickets->count();

        if ($avgMinutes < 60) {
            return round($avgMinutes) . 'min';
        } else {
            return round($avgMinutes / 60, 1) . 'h';
        }
    }

    /**
     * Stocker un fichier joint
     */
    private function storeAttachment($file, SupportTicket $ticket, SupportMessage $message = null)
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = Str::random(40) . '.' . $extension;
        $path = $file->storeAs('support/tickets/' . $ticket->id, $filename, 'private');

        return SupportAttachment::create([
            'ticket_id' => $ticket->id,
            'message_id' => $message?->id,
            'user_id' => auth()->id(),
            'filename' => $filename,
            'original_filename' => $originalName,
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);
    }
}
