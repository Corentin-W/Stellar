<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Stellar</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --secondary: #a855f7;
            --accent: #ec4899;
            --text-light: rgba(255, 255, 255, 0.9);
            --text-muted: rgba(255, 255, 255, 0.6);
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --input-bg: rgba(255, 255, 255, 0.03);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', system-ui, sans-serif;
            background: #000;
            color: white;
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* BACKGROUND GALAXIE */
        .galaxy-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://static.vecteezy.com/system/resources/previews/026/977/316/non_2x/nebula-galaxy-background-with-purple-blue-outer-space-cosmos-clouds-and-beautiful-universe-night-stars-ai-generative-free-photo.jpg') center/cover no-repeat;
            z-index: -2;
        }

        .content-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: -1;
        }

        /* CONTAINER PRINCIPAL */
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
        }

        .auth-card {
            max-width: 450px;
            width: 100%;
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            padding: 3rem;
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.3);
            transform: translateY(20px);
            opacity: 0;
            animation: cardReveal 1s cubic-bezier(0.16, 1, 0.3, 1) 0.3s forwards;
        }

        @keyframes cardReveal {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* HEADER */
        .auth-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .auth-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.3);
            animation: iconFloat 3s ease-in-out infinite;
        }

        @keyframes iconFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .auth-icon svg {
            width: 40px;
            height: 40px;
            color: white;
        }

        .auth-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #fff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.02em;
        }

        .auth-subtitle {
            color: var(--text-muted);
            font-size: 1.1rem;
            line-height: 1.5;
        }

        /* SOCIAL BUTTONS */
        .social-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 1.5rem;
            background: var(--input-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .social-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .social-btn:hover::before {
            opacity: 1;
        }

        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.2);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .social-btn svg {
            margin-right: 0.75rem;
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
            background: linear-gradient(90deg, transparent, var(--glass-border), transparent);
        }

        .divider span {
            background: var(--glass);
            padding: 0 1rem;
            color: var(--text-muted);
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
        }

        /* FORM */
        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-weight: 500;
            color: var(--text-light);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-input {
            padding: 1rem 1.25rem;
            background: var(--input-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            backdrop-filter: blur(10px);
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            background: rgba(255, 255, 255, 0.08);
        }

        .error-message {
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }

        /* CHECKBOX ET LIENS */
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
            border: 2px solid var(--glass-border);
            border-radius: 4px;
            background: transparent;
            accent-color: var(--primary);
        }

        .checkbox-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .forgot-link {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: var(--secondary);
        }

        /* SUBMIT BUTTON */
        .submit-btn {
            padding: 1.25rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 16px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* FOOTER LINKS */
        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--glass-border);
        }

        .auth-link {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .auth-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .auth-link a:hover {
            color: var(--secondary);
        }

        .home-link {
            display: inline-block;
            margin-top: 1rem;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .home-link:hover {
            color: var(--text-light);
        }

        /* FLOATING ELEMENTS */
        .floating-element {
            position: fixed;
            pointer-events: none;
            border-radius: 50%;
            opacity: 0.3;
            animation: float 8s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            top: 10%;
            left: 10%;
            width: 60px;
            height: 60px;
            background: radial-gradient(circle, var(--primary), transparent);
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            bottom: 15%;
            right: 15%;
            width: 40px;
            height: 40px;
            background: radial-gradient(circle, var(--secondary), transparent);
            animation-delay: 2s;
        }

        .floating-element:nth-child(3) {
            top: 60%;
            right: 10%;
            width: 30px;
            height: 30px;
            background: radial-gradient(circle, var(--accent), transparent);
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
                opacity: 0.3;
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
                opacity: 0.6;
            }
        }

        .form-input.error {
            border-color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .auth-card {
                margin: 0.5rem;
                padding: 1.5rem;
            }

            .auth-title {
                font-size: 1.6rem;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }

        /* LOADER */
        .loader {
            position: fixed;
            inset: 0;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.6s ease;
        }

        .loader.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .loader-text {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.05); }
        }
    </style>
</head>
<body>
    <!-- LOADER -->
    <div class="loader" id="loader">
        <div class="loader-text">STELLAR</div>
    </div>

    <!-- BACKGROUND GALAXIE -->
    <div class="galaxy-background"></div>
    <div class="content-overlay"></div>

    <!-- FLOATING ELEMENTS -->
    <div class="floating-element"></div>
    <div class="floating-element"></div>
    <div class="floating-element"></div>

    <!-- CONTAINER PRINCIPAL -->
    <div class="auth-container">
        <div class="auth-card">
            <!-- HEADER -->
            <div class="auth-header">
                <div class="auth-icon">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <h1 class="auth-title">Connexion</h1>
                <p class="auth-subtitle">Bienvenue dans l'univers Stellar</p>
            </div>

            <!-- SOCIAL BUTTONS -->
            <div class="social-buttons">
                <a href="{{ route('social.login', ['provider' => 'google', 'locale' => app()->getLocale()]) }}" class="social-btn">
                    <svg viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Continuer avec Google
                </a>

                <a href="{{ route('social.login', ['provider' => 'github', 'locale' => app()->getLocale()]) }}" class="social-btn">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                    Continuer avec GitHub
                </a>
            </div>

            <!-- DIVIDER -->
            <div class="divider">
                <span>ou continuer avec votre email</span>
            </div>

            <!-- FORM -->
            <form class="auth-form" method="POST" action="/fr/login">
                <div class="form-group">
                    <label class="form-label" for="email">Adresse email</label>
                    <input class="form-input" type="email" id="email" name="email" placeholder="nom@exemple.com" required>
                    <!-- <div class="error-message">Cette adresse email est invalide</div> -->
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe</label>
                    <input class="form-input" type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="form-options">
                    <div class="checkbox-wrapper">
                        <input class="form-checkbox" type="checkbox" id="remember" name="remember">
                        <label class="checkbox-label" for="remember">Se souvenir de moi</label>
                    </div>
                    <a href="#" class="forgot-link">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="submit-btn">Se connecter</button>
            </form>

            <!-- FOOTER -->
            <div class="auth-footer">
                <div class="auth-link">
                    Pas encore de compte ?
                    <a href="/fr/register">Créer un compte</a>
                </div>
                <a href="/fr" class="home-link">← Retour à l'accueil</a>
            </div>
        </div>
    </div>

    <script>
        // LOADER
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('loader').classList.add('hidden');
            }, 1000);
        });

        // PARALLAX BACKGROUND
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const galaxyBg = document.querySelector('.galaxy-background');
            galaxyBg.style.transform = `translateY(${scrolled * -0.3}px) scale(1.1)`;
        });

        // FORM INTERACTIONS
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
            });

            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });

        // BUTTON MAGNETIC EFFECT
        const buttons = document.querySelectorAll('.social-btn, .submit-btn');
        buttons.forEach(button => {
            button.addEventListener('mousemove', (e) => {
                const rect = button.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;

                button.style.transform = `translate(${x * 0.1}px, ${y * 0.1}px)`;
            });

            button.addEventListener('mouseleave', () => {
                button.style.transform = '';
            });
        });

        // FLOATING ELEMENTS INTERACTION
        document.addEventListener('mousemove', (e) => {
            const floatingElements = document.querySelectorAll('.floating-element');
            const mouseX = e.clientX / window.innerWidth;
            const mouseY = e.clientY / window.innerHeight;

            floatingElements.forEach((element, index) => {
                const speed = (index + 1) * 0.02;
                const x = (mouseX - 0.5) * 100 * speed;
                const y = (mouseY - 0.5) * 100 * speed;

                element.style.transform += ` translate(${x}px, ${y}px)`;
            });
        });

        // FORM VALIDATION
        const form = document.querySelector('.auth-form');
        form.addEventListener('submit', (e) => {
            e.preventDefault();

            // Animation de succès
            const submitBtn = document.querySelector('.submit-btn');
            submitBtn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
            submitBtn.textContent = 'Connexion...';

            // Simuler la soumission
            setTimeout(() => {
                submitBtn.textContent = 'Connecté !';
                setTimeout(() => {
                    // Ici vous pouvez rediriger ou soumettre réellement le formulaire
                    // window.location.href = '/dashboard';
                }, 1000);
            }, 1500);
        });
    </script>
</body>
</html>
