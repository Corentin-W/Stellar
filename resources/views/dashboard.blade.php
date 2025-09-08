<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Stellar</title>
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
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', system-ui, sans-serif;
            background: #000;
            color: white;
            min-height: 100vh;
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
            background-size: 120% 120%;
            z-index: -2;
        }

        .content-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            z-index: -1;
        }

        /* NAVIGATION */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 1.5rem 5%;
            z-index: 1000;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 2px;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .logout-btn {
            padding: 0.6rem 1.2rem;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        /* MAIN CONTENT */
        .main-content {
            padding-top: 100px;
            min-height: 100vh;
            padding-left: 5%;
            padding-right: 5%;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 0;
        }

        .welcome-section {
            text-align: center;
            margin-bottom: 4rem;
            transform: translateY(30px);
            opacity: 0;
            animation: slideIn 1s cubic-bezier(0.16, 1, 0.3, 1) 0.3s forwards;
        }

        @keyframes slideIn {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .welcome-title {
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #fff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-subtitle {
            font-size: 1.2rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }

        /* DASHBOARD GRID */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .dashboard-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 2.5rem;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            transform: translateY(50px);
            opacity: 0;
            animation: cardReveal 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .dashboard-card:nth-child(1) { animation-delay: 0.1s; }
        .dashboard-card:nth-child(2) { animation-delay: 0.2s; }
        .dashboard-card:nth-child(3) { animation-delay: 0.3s; }
        .dashboard-card:nth-child(4) { animation-delay: 0.4s; }

        @keyframes cardReveal {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .dashboard-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.2);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .card-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }

        .card-icon svg {
            width: 28px;
            height: 28px;
            color: white;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: white;
        }

        .card-description {
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .card-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .card-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
        }

        /* SUCCESS ANIMATION */
        .success-animation {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            z-index: 9999;
            opacity: 0;
            scale: 0.8;
            animation: successPop 2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes successPop {
            0% {
                opacity: 0;
                scale: 0.8;
            }
            50% {
                opacity: 1;
                scale: 1.05;
            }
            100% {
                opacity: 0;
                scale: 1;
                pointer-events: none;
            }
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            animation: checkmark 0.6s ease-in-out 0.3s both;
        }

        @keyframes checkmark {
            0% { scale: 0; }
            50% { scale: 1.2; }
            100% { scale: 1; }
        }

        .success-text {
            font-size: 1.2rem;
            font-weight: 600;
            color: white;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .navbar {
                padding: 1rem 5%;
            }

            .nav-right {
                gap: 1rem;
            }

            .user-info span {
                display: none;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .main-content {
                padding-top: 80px;
            }
        }
    </style>
</head>
<body>
    <!-- BACKGROUND GALAXIE -->
    <div class="galaxy-background"></div>
    <div class="content-overlay"></div>

    <!-- SUCCESS ANIMATION -->
    <div class="success-animation" id="successAnimation">
        <div class="success-icon">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                <path stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" d="M20 6L9 17l-5-5"/>
            </svg>
        </div>
        <div class="success-text">Connexion réussie !</div>
    </div>

    <!-- NAVIGATION -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">STELLAR</div>
            <div class="nav-right">
                <div class="user-info">
                    <div class="user-avatar">{{ auth()->user()->name[0] ?? 'U' }}</div>
                    <span>{{ auth()->user()->name ?? 'Utilisateur' }}</span>
                </div>
                <form method="POST" action="{{ route('logout', app()->getLocale()) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn" style="background: var(--glass); border: 1px solid var(--glass-border); cursor: pointer;">
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="dashboard-container">
            <!-- WELCOME SECTION -->
            <div class="welcome-section">
                <h1 class="welcome-title">Bienvenue dans l'Univers Stellar</h1>
                <p class="welcome-subtitle">
                    Votre voyage à travers les étoiles commence maintenant.
                    Explorez les merveilles du cosmos avec nos télescopes de pointe.
                </p>
            </div>

            <!-- DASHBOARD GRID -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                            <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <h3 class="card-title">Réserver une Session</h3>
                    <p class="card-description">
                        Réservez du temps sur nos télescopes professionnels situés dans les meilleurs observatoires du monde.
                    </p>
                    <a href="#" class="card-action">
                        Réserver maintenant
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <div class="dashboard-card">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="2"/>
                            <path d="M21 15l-5-5L5 21" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <h3 class="card-title">Mes Observations</h3>
                    <p class="card-description">
                        Consultez et téléchargez toutes vos images capturées lors de vos sessions d'observation.
                    </p>
                    <a href="#" class="card-action">
                        Voir la galerie
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <div class="dashboard-card">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                            <polyline points="12,6 12,12 16,14" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <h3 class="card-title">Planning Astronomique</h3>
                    <p class="card-description">
                        Consultez les meilleures fenêtres d'observation selon les conditions météo et astronomiques.
                    </p>
                    <a href="#" class="card-action">
                        Voir le planning
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <div class="dashboard-card">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2"/>
                            <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <h3 class="card-title">Assistance Expert</h3>
                    <p class="card-description">
                        Contactez nos astronomes professionnels pour obtenir de l'aide dans vos observations.
                    </p>
                    <a href="#" class="card-action">
                        Contacter un expert
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script>
        // PARALLAX BACKGROUND
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const galaxyBg = document.querySelector('.galaxy-background');
            galaxyBg.style.transform = `translateY(${scrolled * -0.3}px) scale(1.1)`;
        });

        // AFFICHER L'ANIMATION DE SUCCÈS AU CHARGEMENT
        window.addEventListener('load', () => {
            // Vérifier si on vient d'une connexion/inscription réussie
            const urlParams = new URLSearchParams(window.location.search);
            const fromAuth = sessionStorage.getItem('justLoggedIn');

            if (fromAuth || document.referrer.includes('/login') || document.referrer.includes('/register')) {
                const successAnimation = document.getElementById('successAnimation');
                successAnimation.style.display = 'block';

                // Nettoyer le flag
                sessionStorage.removeItem('justLoggedIn');
            }
        });

        // HOVER EFFECTS MAGNÉTIQUES
        const cards = document.querySelectorAll('.dashboard-card');
        cards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;

                card.style.transform = `translate(${x * 0.05}px, ${y * 0.05}px) translateY(-10px)`;
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
            });
        });

        // SMOOTH SCROLL POUR LES LIENS
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>
