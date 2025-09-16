<?php

// app/Http/Controllers/Admin/SupportCategoryController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupportCategoryController extends Controller
{
    // Le middleware 'auth' et 'admin' sont déjà appliqués dans les routes

    /**
     * Liste des catégories
     */
    public function index()
    {
        $categories = SupportCategory::withCount('tickets')
                                    ->orderBy('sort_order')
                                    ->orderBy('name')
                                    ->paginate(20);

        return view('admin.support.categories.index', compact('categories'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        return view('admin.support.categories.create');
    }

    /**
     * Enregistrer une nouvelle catégorie
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:support_categories',
            'description' => 'nullable|string|max:1000',
            'color' => 'required|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        SupportCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true,
        ]);

        return redirect()->route('admin.support.categories.index')
                        ->with('success', 'Catégorie créée avec succès.');
    }

    /**
     * Formulaire d'édition
     */
    public function edit(SupportCategory $category)
    {
        return view('admin.support.categories.edit', compact('category'));
    }

    /**
     * Mettre à jour une catégorie
     */
    public function update(Request $request, SupportCategory $category)
    {
       $request->validate([
            'name' => 'required|string|max:255|unique:support_categories,name,' . $category->id,
            'description' => 'nullable|string|max:1000',
            'color' => ['required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.support.categories.index')
                        ->with('success', 'Catégorie mise à jour avec succès.');
    }

    /**
     * Activer/désactiver une catégorie
     */
    public function toggleStatus(SupportCategory $category)
    {
        $category->update(['is_active' => !$category->is_active]);

        $status = $category->is_active ? 'activée' : 'désactivée';

        return back()->with('success', "Catégorie {$status} avec succès.");
    }

    /**
     * Supprimer une catégorie
     */
    public function destroy(SupportCategory $category)
    {
        // Vérifier s'il y a des tickets associés
        if ($category->tickets()->count() > 0) {
            return back()->withErrors(['error' => 'Impossible de supprimer une catégorie qui contient des tickets.']);
        }

        $category->delete();

        return redirect()->route('admin.support.categories.index')
                        ->with('success', 'Catégorie supprimée avec succès.');
    }

    /**
     * Réorganiser les catégories
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:support_categories,id',
            'categories.*.sort_order' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->categories as $categoryData) {
                SupportCategory::where('id', $categoryData['id'])
                              ->update(['sort_order' => $categoryData['sort_order']]);
            }
            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur lors de la réorganisation.'], 500);
        }
    }
}

// ================================================

// app/Http/Controllers/Admin/SupportTemplateController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTemplate;
use App\Models\SupportCategory;
use Illuminate\Http\Request;

class SupportTemplateController extends Controller
{
    // Le middleware 'auth' et 'admin' sont déjà appliqués dans les routes

    /**
     * Liste des templates
     */
    public function index()
    {
        $templates = SupportTemplate::with(['category', 'creator'])
                                   ->orderBy('name')
                                   ->paginate(20);

        return view('admin.support.templates.index', compact('templates'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $categories = SupportCategory::active()->ordered()->get();
        return view('admin.support.templates.create', compact('categories'));
    }

    /**
     * Enregistrer un nouveau template
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:support_categories,id',
        ]);

        SupportTemplate::create([
            'name' => $request->name,
            'subject' => $request->subject,
            'content' => $request->content,
            'category_id' => $request->category_id,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.support.templates.index')
                        ->with('success', 'Template créé avec succès.');
    }

    /**
     * Formulaire d'édition
     */
    public function edit(SupportTemplate $template)
    {
        $categories = SupportCategory::active()->ordered()->get();
        return view('admin.support.templates.edit', compact('template', 'categories'));
    }

    /**
     * Mettre à jour un template
     */
    public function update(Request $request, SupportTemplate $template)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:support_categories,id',
        ]);

        $template->update([
            'name' => $request->name,
            'subject' => $request->subject,
            'content' => $request->content,
            'category_id' => $request->category_id,
        ]);

        return redirect()->route('admin.support.templates.index')
                        ->with('success', 'Template mis à jour avec succès.');
    }

    /**
     * Activer/désactiver un template
     */
    public function toggleStatus(SupportTemplate $template)
    {
        $template->update(['is_active' => !$template->is_active]);

        $status = $template->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Template {$status} avec succès.");
    }

    /**
     * Supprimer un template
     */
    public function destroy(SupportTemplate $template)
    {
        $template->delete();

        return redirect()->route('admin.support.templates.index')
                        ->with('success', 'Template supprimé avec succès.');
    }

    /**
     * Récupérer le contenu d'un template (AJAX)
     */
    public function getContent(SupportTemplate $template)
    {
        if (!$template->is_active) {
            return response()->json(['error' => 'Template désactivé.'], 403);
        }

        // Incrémenter le compteur d'utilisation
        $template->incrementUsage();

        return response()->json([
            'subject' => $template->subject,
            'content' => $template->content,
        ]);
    }
}

// ================================================

// app/Http/Controllers/Admin/SupportReportController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SupportReportController extends Controller
{
    // Le middleware 'auth' et 'admin' sont déjà appliqués dans les routes

    /**
     * Page principale des rapports
     */
    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        $stats = $this->getGeneralStats($dateFrom, $dateTo);
        $chartData = $this->getChartData($dateFrom, $dateTo);

        return view('admin.support.reports.index', compact('stats', 'chartData', 'dateFrom', 'dateTo'));
    }

    /**
     * Rapport par période
     */
    public function period(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $dateFrom = Carbon::parse($request->date_from)->startOfDay();
        $dateTo = Carbon::parse($request->date_to)->endOfDay();

        $tickets = SupportTicket::with(['user', 'category', 'assignedTo'])
                                ->whereBetween('created_at', [$dateFrom, $dateTo])
                                ->orderBy('created_at', 'desc')
                                ->paginate(50);

        $stats = $this->getPeriodStats($dateFrom, $dateTo);

        return view('admin.support.reports.period', compact('tickets', 'stats', 'dateFrom', 'dateTo'));
    }

    /**
     * Rapport par agent
     */
    public function agents(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        $agents = User::where('admin', true)
                     ->withCount([
                         'assignedTickets as total_assigned' => function($query) use ($dateFrom, $dateTo) {
                             $query->whereBetween('created_at', [$dateFrom, $dateTo]);
                         },
                         'assignedTickets as resolved_count' => function($query) use ($dateFrom, $dateTo) {
                             $query->where('status', 'resolved')
                                   ->whereBetween('resolved_at', [$dateFrom, $dateTo]);
                         },
                         'assignedTickets as closed_count' => function($query) use ($dateFrom, $dateTo) {
                             $query->where('status', 'closed')
                                   ->whereBetween('closed_at', [$dateFrom, $dateTo]);
                         }
                     ])
                     ->orderBy('total_assigned', 'desc')
                     ->get();

        return view('admin.support.reports.agents', compact('agents', 'dateFrom', 'dateTo'));
    }

    /**
     * Rapport par catégorie
     */
    public function categories(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        $categories = SupportCategory::withCount([
                                        'tickets as total_tickets' => function($query) use ($dateFrom, $dateTo) {
                                            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
                                        },
                                        'tickets as open_tickets' => function($query) use ($dateFrom, $dateTo) {
                                            $query->whereIn('status', ['open', 'in_progress', 'waiting_user', 'waiting_admin'])
                                                  ->whereBetween('created_at', [$dateFrom, $dateTo]);
                                        },
                                        'tickets as resolved_tickets' => function($query) use ($dateFrom, $dateTo) {
                                            $query->where('status', 'resolved')
                                                  ->whereBetween('resolved_at', [$dateFrom, $dateTo]);
                                        }
                                    ])
                                    ->orderBy('total_tickets', 'desc')
                                    ->get();

        return view('admin.support.reports.categories', compact('categories', 'dateFrom', 'dateTo'));
    }

    /**
     * Export des tickets en CSV
     */
    public function exportTickets(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        $tickets = SupportTicket::with(['user', 'category', 'assignedTo'])
                                ->whereBetween('created_at', [$dateFrom, $dateTo])
                                ->orderBy('created_at', 'desc')
                                ->get();

        $filename = 'support_tickets_' . $dateFrom . '_to_' . $dateTo . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($tickets) {
            $file = fopen('php://output', 'w');

            // Ajouter le BOM pour UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // En-têtes CSV
            fputcsv($file, [
                'Numéro Ticket',
                'Utilisateur',
                'Email',
                'Sujet',
                'Catégorie',
                'Priorité',
                'Statut',
                'Assigné à',
                'Date création',
                'Dernière réponse',
                'Date résolution',
                'Date fermeture'
            ], ';');

            foreach ($tickets as $ticket) {
                fputcsv($file, [
                    $ticket->ticket_number,
                    $ticket->user->name,
                    $ticket->user->email,
                    $ticket->subject,
                    $ticket->category->name,
                    $ticket->priority_label,
                    $ticket->status_label,
                    $ticket->assignedTo?->name ?? '',
                    $ticket->created_at->format('d/m/Y H:i'),
                    $ticket->last_reply_at?->format('d/m/Y H:i') ?? '',
                    $ticket->resolved_at?->format('d/m/Y H:i') ?? '',
                    $ticket->closed_at?->format('d/m/Y H:i') ?? ''
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export des messages en CSV
     */
    public function exportMessages(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        $messages = DB::table('support_messages')
                     ->join('support_tickets', 'support_messages.ticket_id', '=', 'support_tickets.id')
                     ->join('users as message_user', 'support_messages.user_id', '=', 'message_user.id')
                     ->join('users as ticket_user', 'support_tickets.user_id', '=', 'ticket_user.id')
                     ->whereBetween('support_messages.created_at', [$dateFrom, $dateTo])
                     ->where('support_messages.is_internal', false)
                     ->select([
                         'support_tickets.ticket_number',
                         'support_tickets.subject',
                         'ticket_user.name as ticket_owner',
                         'message_user.name as message_author',
                         'message_user.admin as is_admin',
                         'support_messages.message',
                         'support_messages.created_at'
                     ])
                     ->orderBy('support_messages.created_at', 'desc')
                     ->get();

        $filename = 'support_messages_' . $dateFrom . '_to_' . $dateTo . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($messages) {
            $file = fopen('php://output', 'w');

            // Ajouter le BOM pour UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // En-têtes CSV
            fputcsv($file, [
                'Numéro Ticket',
                'Sujet',
                'Propriétaire',
                'Auteur Message',
                'Type Auteur',
                'Message',
                'Date'
            ], ';');

            foreach ($messages as $message) {
                fputcsv($file, [
                    $message->ticket_number,
                    $message->subject,
                    $message->ticket_owner,
                    $message->message_author,
                    $message->is_admin ? 'Support' : 'Utilisateur',
                    strip_tags($message->message),
                    Carbon::parse($message->created_at)->format('d/m/Y H:i')
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Statistiques générales
     */
    private function getGeneralStats($dateFrom, $dateTo)
    {
        $totalTickets = SupportTicket::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $openTickets = SupportTicket::whereIn('status', ['open', 'in_progress', 'waiting_user', 'waiting_admin'])
                                   ->whereBetween('created_at', [$dateFrom, $dateTo])
                                   ->count();
        $resolvedTickets = SupportTicket::where('status', 'resolved')
                                       ->whereBetween('resolved_at', [$dateFrom, $dateTo])
                                       ->count();
        $closedTickets = SupportTicket::where('status', 'closed')
                                     ->whereBetween('closed_at', [$dateFrom, $dateTo])
                                     ->count();

        $avgResolutionTime = $this->calculateAvgResolutionTime($dateFrom, $dateTo);
        $avgResponseTime = $this->calculateAvgResponseTime($dateFrom, $dateTo);

        return [
            'total_tickets' => $totalTickets,
            'open_tickets' => $openTickets,
            'resolved_tickets' => $resolvedTickets,
            'closed_tickets' => $closedTickets,
            'resolution_rate' => $totalTickets > 0 ? round(($resolvedTickets + $closedTickets) / $totalTickets * 100, 1) : 0,
            'avg_resolution_time' => $avgResolutionTime,
            'avg_response_time' => $avgResponseTime,
        ];
    }

    /**
     * Données pour les graphiques
     */
    private function getChartData($dateFrom, $dateTo)
    {
        // Tickets par jour
        $dailyTickets = SupportTicket::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                                    ->groupBy('date')
                                    ->orderBy('date')
                                    ->get()
                                    ->pluck('count', 'date')
                                    ->toArray();

        // Tickets par statut
        $statusDistribution = SupportTicket::selectRaw('status, COUNT(*) as count')
                                          ->whereBetween('created_at', [$dateFrom, $dateTo])
                                          ->groupBy('status')
                                          ->get()
                                          ->pluck('count', 'status')
                                          ->toArray();

        // Tickets par catégorie
        $categoryDistribution = SupportTicket::join('support_categories', 'support_tickets.category_id', '=', 'support_categories.id')
                                            ->selectRaw('support_categories.name, COUNT(*) as count')
                                            ->whereBetween('support_tickets.created_at', [$dateFrom, $dateTo])
                                            ->groupBy('support_categories.name')
                                            ->get()
                                            ->pluck('count', 'name')
                                            ->toArray();

        return [
            'daily_tickets' => $dailyTickets,
            'status_distribution' => $statusDistribution,
            'category_distribution' => $categoryDistribution,
        ];
    }

    /**
     * Statistiques pour une période donnée
     */
    private function getPeriodStats($dateFrom, $dateTo)
    {
        return [
            'total_tickets' => SupportTicket::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'open_tickets' => SupportTicket::whereIn('status', ['open', 'in_progress', 'waiting_user', 'waiting_admin'])
                                          ->whereBetween('created_at', [$dateFrom, $dateTo])
                                          ->count(),
            'resolved_tickets' => SupportTicket::where('status', 'resolved')
                                              ->whereBetween('resolved_at', [$dateFrom, $dateTo])
                                              ->count(),
            'closed_tickets' => SupportTicket::where('status', 'closed')
                                            ->whereBetween('closed_at', [$dateFrom, $dateTo])
                                            ->count(),
            'urgent_tickets' => SupportTicket::where('priority', 'urgent')
                                            ->whereBetween('created_at', [$dateFrom, $dateTo])
                                            ->count(),
        ];
    }

    /**
     * Calculer le temps de résolution moyen
     */
    private function calculateAvgResolutionTime($dateFrom, $dateTo)
    {
        $tickets = SupportTicket::whereNotNull('resolved_at')
                                ->whereBetween('resolved_at', [$dateFrom, $dateTo])
                                ->select('created_at', 'resolved_at')
                                ->get();

        if ($tickets->isEmpty()) {
            return '0h';
        }

        $totalHours = $tickets->sum(function($ticket) {
            return $ticket->created_at->diffInHours($ticket->resolved_at);
        });

        $avgHours = $totalHours / $tickets->count();

        if ($avgHours < 1) {
            return round($avgHours * 60) . 'min';
        } else {
            return round($avgHours, 1) . 'h';
        }
    }

    /**
     * Calculer le temps de réponse moyen
     */
    private function calculateAvgResponseTime($dateFrom, $dateTo)
    {
        $tickets = SupportTicket::whereNotNull('last_reply_at')
                                ->whereBetween('created_at', [$dateFrom, $dateTo])
                                ->where('last_reply_by', '!=', DB::raw('user_id'))
                                ->select('created_at', 'last_reply_at')
                                ->get();

        if ($tickets->isEmpty()) {
            return '0h';
        }

        $totalHours = $tickets->sum(function($ticket) {
            return $ticket->created_at->diffInHours($ticket->last_reply_at);
        });

        $avgHours = $totalHours / $tickets->count();

        if ($avgHours < 1) {
            return round($avgHours * 60) . 'min';
        } else {
            return round($avgHours, 1) . 'h';
        }
    }
}
