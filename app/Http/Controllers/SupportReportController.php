<?php

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
