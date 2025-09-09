<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Afficher le formulaire de connexion
     */
    public function login()
    {
        return view('auth.login');
    }

    /**
     * Traiter la connexion
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ], [
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'L\'adresse email doit être valide.',
            'password.required' => 'Le mot de passe est requis.'
        ]);

        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Redirection vers le dashboard avec la locale
            return redirect()->intended('/' . app()->getLocale() . '/dashboard')
                ->with('success', 'Connexion réussie ! Bienvenue dans l\'univers Stellar.');
        }

        return back()->withErrors([
            'email' => 'Ces identifiants ne correspondent à aucun compte.',
        ])->withInput($request->except('password'));
    }

    /**
     * Afficher le formulaire d'inscription
     */
    public function register()
    {
        return view('auth.register');
    }

    /**
     * Traiter l'inscription
     */
    public function store(Request $request)
    {
        // Validation des données
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:2',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ], [
            // Messages d'erreur personnalisés en français
            'name.required' => 'Le nom est requis.',
            'name.min' => 'Le nom doit contenir au moins 2 caractères.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'password.required' => 'Le mot de passe est requis.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        try {
            // Créer l'utilisateur
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(), // Auto-vérification pour simplifier
            ]);

            // Connecter automatiquement l'utilisateur
            Auth::login($user);

            // Redirection avec message de succès
            return redirect('/' . app()->getLocale() . '/dashboard')
                ->with('success', 'Compte créé avec succès ! Bienvenue dans l\'univers Stellar, ' . $user->name . ' !');

        } catch (\Exception $e) {
            // Log l'erreur pour le débogage
            \Log::error('Erreur lors de la création du compte: ' . $e->getMessage());

            return back()
                ->withErrors(['email' => 'Une erreur est survenue lors de la création du compte. Veuillez réessayer.'])
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/' . app()->getLocale() . '/')
            ->with('success', 'Vous avez été déconnecté avec succès.');
    }

    /**
     * Redirection vers le provider OAuth
     */
    public function redirectToProvider($provider)
    {
        // Vérifier que le provider est supporté
        $supportedProviders = ['google', 'github', 'facebook', 'twitter'];

        if (!in_array($provider, $supportedProviders)) {
            return redirect('/' . app()->getLocale() . '/login')
                ->withErrors(['provider' => 'Provider non supporté.']);
        }

        try {
            return Socialite::driver($provider)->redirect();
        } catch (\Exception $e) {
            \Log::error('Erreur redirection OAuth ' . $provider . ': ' . $e->getMessage());

            return redirect('/' . app()->getLocale() . '/login')
                ->withErrors(['provider' => 'Erreur de connexion avec ' . ucfirst($provider) . '.']);
        }
    }

    /**
     * Gérer le callback du provider OAuth
     */
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();

            // Vérifier si l'utilisateur existe déjà par email
            $existingUser = User::where('email', $socialUser->getEmail())->first();

            if ($existingUser) {
                // Mettre à jour les informations OAuth si l'utilisateur existe
                $existingUser->update([
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'email_verified_at' => $existingUser->email_verified_at ?? now(),
                ]);

                $user = $existingUser;
                $message = 'Connexion réussie via ' . ucfirst($provider) . ' !';
            } else {
                // Créer un nouvel utilisateur
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Utilisateur',
                    'email' => $socialUser->getEmail(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'password' => Hash::make(Str::random(24)), // Mot de passe aléatoire
                    'email_verified_at' => now(), // Auto-vérification pour OAuth
                ]);

                $message = 'Compte créé et connexion réussie via ' . ucfirst($provider) . ' !';
            }

            // Connecter l'utilisateur
            Auth::login($user);

            return redirect('/' . app()->getLocale() . '/dashboard')
                ->with('success', $message);

        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            \Log::error('OAuth Invalid State Exception pour ' . $provider . ': ' . $e->getMessage());

            return redirect('/' . app()->getLocale() . '/login')
                ->withErrors(['provider' => 'Session expirée. Veuillez réessayer la connexion.']);

        } catch (\Exception $e) {
            \Log::error('Erreur OAuth callback ' . $provider . ': ' . $e->getMessage());

            return redirect('/' . app()->getLocale() . '/login')
                ->withErrors(['provider' => 'Erreur lors de la connexion avec ' . ucfirst($provider) . '. Veuillez réessayer.']);
        }
    }

    /**
     * Afficher le formulaire de mot de passe oublié
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Envoyer le lien de réinitialisation
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.exists' => 'Aucun compte n\'est associé à cette adresse email.'
        ]);

        // Ici vous pouvez implémenter l'envoi d'email de réinitialisation
        // Pour l'instant, on simule le succès
        return back()->with('success', 'Un lien de réinitialisation a été envoyé à votre adresse email.');
    }

    /**
     * Vérifier si l'email existe (pour validation AJAX)
     */
    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        $exists = User::where('email', $email)->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'Cette adresse email est déjà utilisée.' : 'Adresse email disponible.'
        ]);
    }

    /**
     * Validation de mot de passe (pour validation AJAX)
     */
    public function validatePassword(Request $request)
    {
        $password = $request->input('password');

        // Critères de validation
        $criteria = [
            'length' => strlen($password) >= 8,
            'lowercase' => preg_match('/[a-z]/', $password),
            'uppercase' => preg_match('/[A-Z]/', $password),
            'number' => preg_match('/[0-9]/', $password),
            'special' => preg_match('/[^a-zA-Z0-9]/', $password),
        ];

        $score = array_sum($criteria);

        $levels = [
            0 => 'Très faible',
            1 => 'Faible',
            2 => 'Moyen',
            3 => 'Fort',
            4 => 'Très fort',
            5 => 'Excellent'
        ];

        return response()->json([
            'score' => $score,
            'level' => $levels[$score] ?? 'Inconnu',
            'criteria' => $criteria,
            'valid' => $score >= 3
        ]);
    }
}
