<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\SupportCategory;
use App\Models\SupportAttachment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SupportController extends Controller
{


    /**
     * Liste des tickets de l'utilisateur
     */
    public function index(Request $request)
    {
        $query = auth()->user()->supportTickets()->with(['category', 'assignedTo']);

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(15);

        $categories = SupportCategory::where('is_active', true)->orderBy('sort_order')->get();

        return view('support.index', compact('tickets', 'categories'));
    }

    /**
     * Formulaire de création d'un nouveau ticket
     */
    public function create()
    {
        $categories = SupportCategory::where('is_active', true)->orderBy('sort_order')->get();
        return view('support.create', compact('categories'));
    }

/**
 * Enregistrer un nouveau ticket
 */
public function store(Request $request)
{
    // Validation des données
    $request->validate([
        'category_id' => 'required|integer|exists:support_categories,id',
        'subject' => 'required|string|max:500',
        'priority' => 'required|in:low,normal,high,urgent',
        'message' => 'required|string|min:10',
        'attachments' => 'nullable|array|max:5',
        'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt,zip,rar',
    ], [
        'category_id.required' => 'La catégorie est obligatoire.',
        'category_id.exists' => 'La catégorie sélectionnée n\'est pas valide.',
        'subject.required' => 'Le sujet est obligatoire.',
        'subject.max' => 'Le sujet ne peut pas dépasser 500 caractères.',
        'priority.required' => 'La priorité est obligatoire.',
        'priority.in' => 'La priorité sélectionnée n\'est pas valide.',
        'message.required' => 'Le message est obligatoire.',
        'message.min' => 'Le message doit contenir au moins 10 caractères.',
        'attachments.max' => 'Vous ne pouvez joindre que 5 fichiers maximum.',
        'attachments.*.max' => 'Chaque fichier ne peut pas dépasser 10Mo.',
        'attachments.*.mimes' => 'Format de fichier non autorisé.',
    ]);

    try {
        DB::beginTransaction();

        // Vérifier que la catégorie est active
        $category = SupportCategory::where('id', $request->category_id)
                                   ->where('is_active', true)
                                   ->first();

        if (!$category) {
            return back()->withInput()->withErrors(['category_id' => 'Cette catégorie n\'est pas disponible.']);
        }

        // Générer un numéro de ticket unique
        $ticketNumber = $this->generateTicketNumber();

        // Créer le ticket
        $ticket = SupportTicket::create([
            'user_id' => auth()->id(),
            'category_id' => $request->category_id,
            'ticket_number' => $ticketNumber,
            'subject' => $request->subject,
            'priority' => $request->priority,
            'status' => 'open',
            'last_reply_at' => now(),
            'last_reply_by' => auth()->id(),
        ]);

        // Créer le message initial
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

        DB::commit();

        return redirect()->route('support.show', ['locale' => app()->getLocale(), 'ticket' => $ticket->id])
                       ->with('success', 'Votre ticket de support a été créé avec succès. Vous recevrez une réponse dans les plus brefs délais.');

    } catch (\Exception $e) {
        DB::rollBack();

        // Log l'erreur pour le debug
        \Log::error('Erreur création ticket support', [
            'user_id' => auth()->id(),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return back()->withInput()->withErrors(['error' => 'Une erreur est survenue lors de la création du ticket. Veuillez réessayer.']);
    }
}

    /**
     * Afficher un ticket spécifique
     */
    public function show(string $locale, SupportTicket $ticket)
    {
        // Vérifier que l'utilisateur peut voir ce ticket
        if ($ticket->user_id !== Auth::user()->id) {
            abort(403, 'Vous n\'êtes pas autorisé à voir ce ticket.');
        }
        $ticket->load([
            'category',
            'assignedTo',
            'messages' => function($query) {
                $query->where('is_internal', false)
                    ->with(['user', 'attachmentFiles'])
                    ->orderBy('created_at', 'asc');
            }
        ]);

        return view('support.show', compact('ticket'));
    }

    /**
     * Répondre à un ticket
     */
    public function reply(Request $request, string $locale, SupportTicket $ticket)
    {
        // Vérifier que l'utilisateur peut répondre à ce ticket
        if ($ticket->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à répondre à ce ticket.');
        }

        // Vérifier que le ticket n'est pas fermé
        if ($ticket->status === 'closed') {
            return back()->withErrors(['error' => 'Vous ne pouvez pas répondre à un ticket fermé.']);
        }

        $request->validate([
            'message' => 'required|string',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt',
        ]);

        try {
            DB::beginTransaction();

            // Créer le message
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
                'status' => 'waiting_admin', // Le ticket attend maintenant une réponse admin
            ]);

            DB::commit();

            return back()->with('success', 'Votre réponse a été ajoutée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Une erreur est survenue lors de l\'envoi de votre réponse.']);
        }
    }

    /**
     * Fermer un ticket (marquer comme résolu)
     */
    public function close(string $locale, SupportTicket $ticket)
    {
        // Vérifier que l'utilisateur peut fermer ce ticket
        if ($ticket->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à fermer ce ticket.');
        }

        if ($ticket->status === 'closed') {
            return back()->withErrors(['error' => 'Ce ticket est déjà fermé.']);
        }

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

        return back()->with('success', 'Le ticket a été fermé avec succès.');
    }

    /**
     * Rouvrir un ticket
     */
    public function reopen(string $locale, SupportTicket $ticket)
    {
        // Vérifier que l'utilisateur peut rouvrir ce ticket
        if ($ticket->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à rouvrir ce ticket.');
        }

        if ($ticket->status !== 'closed' && $ticket->status !== 'resolved') {
            return back()->withErrors(['error' => 'Ce ticket ne peut pas être rouvert.']);
        }

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

        return back()->with('success', 'Le ticket a été rouvert avec succès.');
    }

    /**
     * Télécharger un fichier joint
     */
    public function downloadAttachment(string $locale, SupportAttachment $attachment)
    {
        // Vérifier que l'utilisateur peut télécharger ce fichier
        if ($attachment->ticket->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à télécharger ce fichier.');
        }

        if (!Storage::exists($attachment->file_path)) {
            abort(404, 'Fichier non trouvé.');
        }

        return Storage::download($attachment->file_path, $attachment->original_filename);
    }

    /**
     * Générer un numéro de ticket unique
     */
    private function generateTicketNumber()
    {
        do {
            $number = 'TK-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (SupportTicket::where('ticket_number', $number)->exists());

        return $number;
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
