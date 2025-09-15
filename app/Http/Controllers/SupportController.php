<?php

// app/Http/Controllers/SupportController.php
namespace App\Http\Controllers;

use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\SupportAttachment;
use App\Models\SupportTicketHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    // Le middleware 'auth' est déjà appliqué dans les routes
    // Pas besoin de l'ajouter ici

    /**
     * Liste des tickets de l'utilisateur
     */
    public function index(Request $request)
    {
        $query = auth()->user()->tickets()->with(['category', 'messages' => function($q) {
            $q->where('is_internal', false)->latest()->limit(1);
        }]);

        // Filtres
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(10);
        $categories = SupportCategory::active()->ordered()->get();

        return view('support.index', compact('tickets', 'categories'));
    }

    /**
     * Formulaire de création d'un nouveau ticket
     */
    public function create()
    {
        $categories = SupportCategory::active()->ordered()->get();
        return view('support.create', compact('categories'));
    }

    /**
     * Enregistrer un nouveau ticket
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:support_categories,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt',
        ]);

        try {
            DB::beginTransaction();

            // Créer le ticket
            $ticket = SupportTicket::create([
                'user_id' => auth()->id(),
                'category_id' => $request->category_id,
                'subject' => $request->subject,
                'priority' => $request->priority,
                'status' => 'open',
                'last_reply_at' => now(),
                'last_reply_by' => auth()->id(),
            ]);

            // Créer le premier message
            $message = SupportMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'message' => $request->message,
                'is_internal' => false,
                'is_system' => false,
            ]);

            // Gérer les fichiers joints
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->storeAttachment($file, $ticket, $message);
                }
            }

            // Mettre à jour le ticket
            $ticket->update([
                'last_reply_at' => now(),
                'last_reply_by' => auth()->id(),
                'status' => $ticket->status === 'waiting_user' ? 'waiting_admin' : $ticket->status,
            ]);

            DB::commit();

            return back()->with('success', 'Votre réponse a été ajoutée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Une erreur est survenue lors de l\'envoi de la réponse.']);
        }
    }

    /**
     * Fermer un ticket (utilisateur)
     */
    public function close(SupportTicket $ticket)
    {
        // Vérifier que l'utilisateur peut fermer ce ticket
        if ($ticket->user_id !== auth()->id()) {
            abort(403, 'Vous ne pouvez pas fermer ce ticket.');
        }

        if ($ticket->isClosed()) {
            return back()->withErrors(['error' => 'Ce ticket est déjà fermé.']);
        }

        try {
            DB::beginTransaction();

            $ticket->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closed_by' => auth()->id(),
            ]);

            // Message système
            SupportMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'message' => 'Ticket fermé par l\'utilisateur.',
                'is_internal' => false,
                'is_system' => true,
            ]);

            // Historique
            SupportTicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'action' => 'closed',
                'old_value' => $ticket->getOriginal('status'),
                'new_value' => 'closed',
                'description' => 'Ticket fermé par l\'utilisateur',
            ]);

            DB::commit();

            return back()->with('success', 'Le ticket a été fermé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Une erreur est survenue lors de la fermeture du ticket.']);
        }
    }

    /**
     * Rouvrir un ticket
     */
    public function reopen(SupportTicket $ticket)
    {
        // Vérifier que l'utilisateur peut rouvrir ce ticket
        if ($ticket->user_id !== auth()->id()) {
            abort(403, 'Vous ne pouvez pas rouvrir ce ticket.');
        }

        if (!$ticket->isClosed() && !$ticket->isResolved()) {
            return back()->withErrors(['error' => 'Ce ticket n\'est pas fermé.']);
        }

        try {
            DB::beginTransaction();

            $oldStatus = $ticket->status;
            $ticket->update([
                'status' => 'open',
                'closed_at' => null,
                'closed_by' => null,
                'resolved_at' => null,
                'resolved_by' => null,
                'last_reply_at' => now(),
                'last_reply_by' => auth()->id(),
            ]);

            // Message système
            SupportMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'message' => 'Ticket rouvert par l\'utilisateur.',
                'is_internal' => false,
                'is_system' => true,
            ]);

            // Historique
            SupportTicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'action' => 'reopened',
                'old_value' => $oldStatus,
                'new_value' => 'open',
                'description' => 'Ticket rouvert par l\'utilisateur',
            ]);

            DB::commit();

            return back()->with('success', 'Le ticket a été rouvert avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Une erreur est survenue lors de la réouverture du ticket.']);
        }
    }

    /**
     * Télécharger un fichier joint
     */
    public function downloadAttachment(SupportAttachment $attachment)
    {
        // Vérifier que l'utilisateur peut télécharger ce fichier
        if ($attachment->ticket->user_id !== auth()->id()) {
            abort(403, 'Vous ne pouvez pas télécharger ce fichier.');
        }

        if (!Storage::exists($attachment->file_path)) {
            abort(404, 'Fichier non trouvé.');
        }

        return Storage::download($attachment->file_path, $attachment->original_filename);
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
