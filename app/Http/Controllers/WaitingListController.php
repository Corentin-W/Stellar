<?php

namespace App\Http\Controllers;

use App\Models\WaitingList;
use App\Mail\WaitingListConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class WaitingListController extends Controller
{
    /**
     * Afficher le formulaire d'inscription à la waiting list
     */
    public function create()
    {
        return view('waiting-list.create');
    }

    /**
     * Traiter l'inscription à la waiting list
     */
    public function store(Request $request)
    {
        try {
            // Validation
            $validated = $request->validate([
                'firstName' => 'required|string|min:2|max:50|regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/',
                'lastName' => 'required|string|min:2|max:50|regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/',
                'email' => [
                    'required',
                    'email:rfc,dns',
                    'max:255',
                    'unique:waiting_list,email' // Uniquement dans waiting_list
                ],
                'interest' => 'required|in:debutant,amateur,avance,professionnel',
            ], [
                'firstName.required' => 'Le prénom est obligatoire',
                'firstName.regex' => 'Le prénom contient des caractères invalides',
                'lastName.required' => 'Le nom est obligatoire',
                'lastName.regex' => 'Le nom contient des caractères invalides',
                'email.required' => 'L\'email est obligatoire',
                'email.email' => 'L\'adresse email n\'est pas valide',
                'email.unique' => 'Cette adresse email est déjà inscrite sur la waiting list',
                'interest.required' => 'Veuillez sélectionner votre niveau d\'intérêt',
                'interest.in' => 'Niveau d\'intérêt invalide',
            ]);

            // Protection contre le spam (rate limiting basique)
            $recentCount = WaitingList::where('ip_address', $request->ip())
                ->where('created_at', '>=', now()->subHour())
                ->count();

            if ($recentCount >= 3) {
                throw ValidationException::withMessages([
                    'email' => ['Trop de tentatives depuis cette adresse IP. Veuillez réessayer dans une heure.']
                ]);
            }

            // Vérifier si l'email existe déjà (double sécurité)
            $existingEntry = WaitingList::where('email', strtolower(trim($validated['email'])))->first();
            if ($existingEntry) {
                $status = $existingEntry->isConfirmed() ? 'confirmée' : 'en attente de confirmation';

                return response()->json([
                    'success' => false,
                    'message' => "Vous êtes déjà inscrit sur notre waiting list (inscription $status).",
                    'data' => [
                        'already_registered' => true,
                        'confirmed' => $existingEntry->isConfirmed(),
                        'registration_date' => $existingEntry->created_at->format('d/m/Y')
                    ]
                ], 409);
            }

            // Créer l'entrée
            $waitingListEntry = WaitingList::create([
                'first_name' => $validated['firstName'],
                'last_name' => $validated['lastName'],
                'email' => strtolower(trim($validated['email'])),
                'interest_level' => $validated['interest'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'source' => 'website',
                    'referrer' => $request->header('referer'),
                    'language' => $request->getPreferredLanguage(),
                    'timestamp' => now()->toISOString(),
                ]
            ]);

            // Générer token de confirmation
            $confirmationToken = $waitingListEntry->generateConfirmationToken();

            // Envoyer email de confirmation
            try {
                Mail::to($waitingListEntry->email)
                    ->send(new WaitingListConfirmation($waitingListEntry));

                Log::info('Waiting list confirmation email sent', [
                    'id' => $waitingListEntry->id,
                    'email' => $waitingListEntry->email,
                    'interest_level' => $waitingListEntry->interest_level
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send waiting list confirmation email', [
                    'id' => $waitingListEntry->id,
                    'email' => $waitingListEntry->email,
                    'error' => $e->getMessage()
                ]);

                // L'inscription est maintenue même si l'email échoue
            }

            return response()->json([
                'success' => true,
                'message' => 'Inscription réussie ! Vérifiez votre email pour confirmer.',
                'data' => [
                    'id' => $waitingListEntry->id,
                    'full_name' => $waitingListEntry->full_name,
                    'interest_label' => $waitingListEntry->interest_label,
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Waiting list registration failed', [
                'error' => $e->getMessage(),
                'email' => $request->email ?? 'unknown',
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * Confirmer l'email via le token
     */
    public function confirm($token)
    {
        $entry = WaitingList::where('confirmation_token', $token)->first();

        if (!$entry) {
            return redirect('/')->with('error', 'Lien de confirmation invalide.');
        }

        if ($entry->isConfirmed()) {
            return redirect('/')->with('info', 'Votre email est déjà confirmé.');
        }

        $entry->confirm();

        Log::info('Waiting list email confirmed', [
            'id' => $entry->id,
            'email' => $entry->email
        ]);

        return view('waiting-list.confirmed', compact('entry'));
    }

    /**
     * Dashboard admin - stats de la waiting list
     */
    public function admin()
    {
        $this->authorize('admin'); // Middleware d'admin requis

        $stats = WaitingList::getStats();
        $recentEntries = WaitingList::with([])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.waiting-list.dashboard', compact('stats', 'recentEntries'));
    }

    /**
     * Export CSV pour campagnes email
     */
    public function export(Request $request)
    {
        $this->authorize('admin');

        $query = WaitingList::query();

        // Filtres optionnels
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->interest_level) {
            $query->where('interest_level', $request->interest_level);
        }

        if ($request->confirmed) {
            if ($request->confirmed === 'yes') {
                $query->confirmed();
            } else {
                $query->whereNull('confirmed_at');
            }
        }

        $entries = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="stellar_waiting_list_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($entries) {
            $file = fopen('php://output', 'w');

            // Headers CSV
            fputcsv($file, [
                'ID',
                'Prénom',
                'Nom',
                'Email',
                'Niveau d\'intérêt',
                'Statut',
                'Confirmé le',
                'Inscrit le',
                'IP'
            ]);

            // Données
            foreach ($entries as $entry) {
                fputcsv($file, [
                    $entry->id,
                    $entry->first_name,
                    $entry->last_name,
                    $entry->email,
                    $entry->interest_label,
                    $entry->status,
                    $entry->confirmed_at ? $entry->confirmed_at->format('d/m/Y H:i') : 'Non confirmé',
                    $entry->created_at->format('d/m/Y H:i'),
                    $entry->ip_address
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
