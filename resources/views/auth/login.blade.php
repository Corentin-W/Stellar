<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion - Stellar</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #4F46E5;
            --primary-light: #6366F1;
            --secondary: #7C3AED;
            --accent: #EC4899;
            --background: #0F0F23;
            --surface: rgba(255, 255, 255, 0.03);
            --surface-hover: rgba(255, 255, 255, 0.06);
            --border: rgba(255, 255, 255, 0.08);
            --border-focus: rgba(99, 102, 241, 0.4);
            --text-primary: rgba(255, 255, 255, 0.95);
            --text-secondary: rgba(255, 255, 255, 0.7);
            --text-muted: rgba(255, 255, 255, 0.5);
            --success: #10B981;
            --error: #EF4444;
            --warning: #F59E0B;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Inter', system-ui, sans-serif;
            background: var(--background);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* BACKGROUND */
        .space-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(79, 70, 229, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(124, 58, 237, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 40% 80%, rgba(236, 72, 153, 0.08) 0%, transparent 50%),
                linear-gradient(135deg, #0F0F23 0%, #1a1a3a 50%, #0F0F23 100%);
            z-index: -2;
        }

        .stars {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                radial-gradient(1px 1px at 20px 30px, rgba(255,255,255,0.3), transparent),
                radial-gradient(1px 1px at 40px 70px, rgba(255,255,255,0.2), transparent),
                radial-gradient(1px 1px at 90px 40px, rgba(255,255,255,0.4), transparent),
                radial-gradient(1px 1px at 130px 80px, rgba(255,255,255,0.2), transparent);
            background-repeat: repeat;
            background-size: 250px 150px;
            animation: twinkle 4s ease-in-out infinite alternate;
            z-index: -1;
        }

        @keyframes twinkle {
            0% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        /* MAIN CONTAINER */
        .container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: var(--surface);
            backdrop-filter: blur(20px) saturate(110%);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 3rem;
            box-shadow:
                0 25px 50px rgba(0, 0, 0, 0.5),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* HEADER */
        .header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo {
            width: 64px;
            height: 64px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
            animation: gentle-float 6s ease-in-out infinite;
        }

        @keyframes gentle-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .logo svg {
            width: 32px;
            height: 32px;
            color: white;
        }

        .title {
            font-size: 2.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #fff 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.02em;
        }

        .subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
            margin-bottom: 0;
        }

        /* SOCIAL AUTH */
        .social-auth {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .social-btn:hover {
            background: var(--surface-hover);
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-1px);
        }

        .social-btn svg {
            width: 20px;
            height: 20px;
        }

        /* DIVIDER */
        .divider {
            position: relative;
            margin: 2rem 0;
            text-align: center;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border), transparent);
        }

        .divider span {
            background: var(--surface);
            padding: 0 1rem;
            color: var(--text-muted);
            font-size: 0.875rem;
            backdrop-filter: blur(10px);
        }

        /* FORM */
        .form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-input {
            padding: 1rem 1.25rem;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--border-focus);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-input.error {
            border-color: var(--error);
            background: rgba(239, 68, 68, 0.05);
        }

        /* FORM OPTIONS */
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-checkbox {
            width: 18px;
            height: 18px;
            border: 2px solid var(--border);
            border-radius: 4px;
            background: transparent;
            accent-color: var(--primary);
        }

        .checkbox-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .forgot-link {
            color: var(--primary-light);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: var(--secondary);
        }

        /* ERROR MESSAGES */
        .error-message {
            color: var(--error);
            font-size: 0.8rem;
            margin-top: 0.5rem;
            opacity: 0;
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }

        .error-message.show {
            opacity: 1;
            transform: translateY(0);
        }

        .server-errors {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .server-errors div {
            color: var(--error);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .server-errors div:last-child {
            margin-bottom: 0;
        }

        /* SUCCESS MESSAGE */
        .success-message {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            color: var(--success);
            font-size: 0.875rem;
            text-align: center;
        }

        /* SUBMIT BUTTON */
        .submit-btn {
            padding: 1.25rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 0.5rem;
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
        }

        .submit-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(79, 70, 229, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* FOOTER */
        .footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border);
        }

        .footer-text {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .footer-link {
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .footer-link:hover {
            color: var(--secondary);
        }

        .home-link {
            display: inline-block;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.3s ease;
        }

        .home-link:hover {
            color: var(--text-secondary);
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .login-card {
                padding: 2rem;
            }

            .title {
                font-size: 2rem;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }

        /* REDUCED MOTION */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <!-- Background -->
    <div class="space-background"></div>
    <div class="stars"></div>

    <!-- Main Container -->
    <div class="container">
        <div class="login-card">
            <!-- Header -->
            <div class="header">
                <div class="logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10,17 15,12 10,7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                </div>
                <h1 class="title">Connexion</h1>
                <p class="subtitle">Bon retour dans l'univers Stellar</p>
            </div>

            <!-- Social Authentication -->
            <div class="social-auth">
                <a href="/fr/auth/google" class="social-btn">
                    <svg viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Continuer avec Google
                </a>

                <a href="/fr/auth/github" class="social-btn">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                    Continuer avec GitHub
                </a>
            </div>

            <!-- Divider -->
            <div class="divider">
                <span>ou avec votre email</span>
            </div>

            <!-- Success Message (si nécessaire) -->
            <div class="success-message" style="display: none;" id="successMessage">
                Connexion réussie ! Redirection en cours...
            </div>

            <!-- Server Errors (remplacez par votre logique de gestion d'erreurs) -->
            <div class="server-errors" style="display: none;" id="serverErrors">
                <div>Erreur de connexion</div>
            </div>

            <!-- Login Form -->
            <form class="form" id="loginForm" method="POST" action="/fr/login">
                <!-- CSRF Token (pour Laravel) -->
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="form-group">
                    <label class="form-label" for="email">Adresse email</label>
                    <input class="form-input" type="email" id="email" name="email" placeholder="nom@exemple.com" required>
                    <div class="error-message" id="emailError"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe</label>
                    <input class="form-input" type="password" id="password" name="password" placeholder="••••••••" required>
                    <div class="error-message" id="passwordError"></div>
                </div>

                <div class="form-options">
                    <div class="checkbox-wrapper">
                        <input class="form-checkbox" type="checkbox" id="remember" name="remember">
                        <label class="checkbox-label" for="remember">Se souvenir de moi</label>
                    </div>
                    <a href="#" class="forgot-link" onclick="showForgotPassword()">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <span id="submitText">Se connecter</span>
                </button>
            </form>

            <!-- Footer -->
            <div class="footer">
                <div class="footer-text">
                    Pas encore de compte ?
                    <a href="/fr/register" class="footer-link">Créer un compte</a>
                </div>
                <a href="/fr" class="home-link">← Retour à l'accueil</a>
            </div>
        </div>
    </div>

    <script>
        function showError(elementId, message) {
            const errorElement = document.getElementById(elementId);
            errorElement.textContent = message;
            errorElement.classList.add('show');
        }

        function clearErrors() {
            const errorElements = document.querySelectorAll('.error-message');
            errorElements.forEach(element => {
                element.classList.remove('show');
            });

            const inputs = document.querySelectorAll('.form-input');
            inputs.forEach(input => {
                input.classList.remove('error');
            });
        }

        function validateEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function showForgotPassword() {
            // Vous pouvez implémenter un modal ou rediriger vers une page dédiée
            alert('Fonctionnalité de récupération de mot de passe à implémenter');
        }

        // Elements
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('loginForm');

        // Validation en temps réel
        emailInput.addEventListener('input', function() {
            const email = this.value.trim();

            if (email && !validateEmail(email)) {
                showError('emailError', 'Format d\'email invalide');
                this.classList.add('error');
            } else {
                document.getElementById('emailError').classList.remove('show');
                this.classList.remove('error');
            }
        });

        passwordInput.addEventListener('input', function() {
            const password = this.value;

            if (password && password.length < 6) {
                showError('passwordError', 'Le mot de passe doit contenir au moins 6 caractères');
                this.classList.add('error');
            } else {
                document.getElementById('passwordError').classList.remove('show');
                this.classList.remove('error');
            }
        });

        // Form submission
        form.addEventListener('submit', function(e) {
            const email = emailInput.value.trim();
            const password = passwordInput.value;

            clearErrors();

            let hasErrors = false;

            // Validation email
            if (!email) {
                showError('emailError', 'L\'email est requis');
                emailInput.classList.add('error');
                hasErrors = true;
            } else if (!validateEmail(email)) {
                showError('emailError', 'Format d\'email invalide');
                emailInput.classList.add('error');
                hasErrors = true;
            }

            // Validation mot de passe
            if (!password) {
                showError('passwordError', 'Le mot de passe est requis');
                passwordInput.classList.add('error');
                hasErrors = true;
            } else if (password.length < 6) {
                showError('passwordError', 'Le mot de passe doit contenir au moins 6 caractères');
                passwordInput.classList.add('error');
                hasErrors = true;
            }

            if (hasErrors) {
                e.preventDefault();
                return;
            }

            // Show loading state
            const submitText = document.getElementById('submitText');
            submitBtn.disabled = true;
            submitText.innerHTML = `
                <div class="loading">
                    <div class="spinner"></div>
                    Connexion...
                </div>
            `;

            // Le formulaire sera soumis normalement si pas d'erreurs
        });

        // Gestion des erreurs de session (côté serveur)
        // Cette partie dépend de votre backend Laravel
        document.addEventListener('DOMContentLoaded', function() {
            // Exemple pour afficher les erreurs de session Laravel
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');

            if (error) {
                const serverErrors = document.getElementById('serverErrors');
                serverErrors.style.display = 'block';
                serverErrors.innerHTML = `<div>${decodeURIComponent(error)}</div>`;
            }

            // Masquer le loader/succès après un délai
            setTimeout(() => {
                const successMessage = document.getElementById('successMessage');
                if (successMessage.style.display === 'block') {
                    successMessage.style.display = 'none';
                }
            }, 3000);
        });

        // Animation des boutons sociaux au survol
        const socialBtns = document.querySelectorAll('.social-btn');
        socialBtns.forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px) scale(1.02)';
            });

            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>
