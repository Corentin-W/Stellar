<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://static.vecteezy.com" crossorigin>
    <title>Stellar - Location de Matériel d'Astronomie</title>
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

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', system-ui, sans-serif;
            background: #000;
            color: white;
            overflow-x: hidden;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* BACKGROUND GALAXIE - TOUJOURS VISIBLE */
        .galaxy-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://static.vecteezy.com/system/resources/previews/026/977/316/non_2x/nebula-galaxy-background-with-purple-blue-outer-space-cosmos-clouds-and-beautiful-universe-night-stars-ai-generative-free-photo.jpg') center/cover no-repeat;
            z-index: -3;
        }

        /* OVERLAY TRANSPARENT POUR LA LISIBILITÉ */
        .content-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            z-index: -2;
        }

        /* STARFIELD CANVAS ABOVE OVERLAY, BELOW CONTENT */
        #stars-canvas {
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;
        }

        /* NAVIGATION */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 2rem 5%;
            z-index: 1000;
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .navbar.scrolled {
            padding: 1rem 5%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(12px);
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
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 2px;
        }

        .nav-links {
            display: flex;
            gap: 3rem;
            list-style: none;
        }

        .nav-links a {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .login-btn {
            padding: 0.8rem 2rem;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .login-btn:hover {
            background: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
        }

        /* HERO SECTION */
        .hero {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            text-align: center;
        }

        .hero-content {
            max-width: 1000px;
            padding: 0 2rem;
            transform: translateY(100px);
            opacity: 0;
            animation: heroReveal 1.5s cubic-bezier(0.16, 1, 0.3, 1) 0.5s forwards;
        }

        @keyframes heroReveal {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .hero-badge {
            display: inline-block;
            padding: 0.8rem 2rem;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 500;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
            animation: badgeFloat 3s ease-in-out infinite;
        }

        @keyframes badgeFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .hero-title {
            font-size: clamp(4rem, 15vw, 12rem);
            font-weight: 900;
            letter-spacing: -0.05em;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #fff 0%, var(--primary) 30%, var(--secondary) 70%, #fff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-size: 300% 300%;
            animation: gradientMove 8s ease-in-out infinite;
        }

        @keyframes gradientMove {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .hero-subtitle {
            font-size: 1.5rem;
            color: var(--text-light);
            margin-bottom: 1.5rem;
            font-weight: 300;
            letter-spacing: 1px;
        }

        .hero-description {
            font-size: 1.2rem;
            color: var(--text-muted);
            max-width: 700px;
            margin: 0 auto 3rem;
            line-height: 1.8;
        }

        .cta-buttons {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            padding: 1.2rem 3rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            text-decoration: none;
            border-radius: 60px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 30px 60px rgba(99, 102, 241, 0.4);
        }

        .btn-secondary {
            padding: 1.2rem 3rem;
            background: var(--glass);
            color: white;
            text-decoration: none;
            border-radius: 60px;
            font-weight: 600;
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
        }

        /* SCROLL INDICATOR */
        .scroll-indicator {
            position: absolute;
            bottom: 3rem;
            left: 50%;
            transform: translateX(-50%);
            animation: bounceScroll 2s infinite;
        }

        @keyframes bounceScroll {
            0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
            40% { transform: translateX(-50%) translateY(-10px); }
            60% { transform: translateX(-50%) translateY(-5px); }
        }

        /* SECTIONS AVEC ANIMATIONS AWARD-WINNING */
        .section {
            padding: 120px 5%;
            position: relative;
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-reveal {
            opacity: 0;
            transform: translateY(80px);
            transition: all 1s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .section-reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .section-header {
            text-align: center;
            margin-bottom: 5rem;
        }

        .section-badge {
            display: inline-block;
            padding: 0.6rem 1.5rem;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(10px);
        }

        .section-title {
            font-size: clamp(3rem, 8vw, 5rem);
            font-weight: 800;
            margin-bottom: 1.5rem;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #fff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .section-description {
            font-size: 1.2rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* FEATURES GRID */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 3rem;
            margin-top: 5rem;
        }

        .feature-card {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            padding: 3rem 2.5rem;
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            opacity: 0;
            transform: translateY(50px) scale(0.9);
        }

        .feature-card.visible {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .feature-card:hover {
            transform: translateY(-15px) scale(1.03);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 18px 36px rgba(99, 102, 241, 0.18);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
        }

        .feature-icon svg {
            width: 40px;
            height: 40px;
            color: white;
        }

        .feature-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: white;
        }

        .feature-description {
            color: var(--text-muted);
            line-height: 1.7;
            font-size: 1.1rem;
        }

        /* EQUIPMENT SHOWCASE */
        .equipment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 3rem;
            margin-top: 5rem;
        }

        .equipment-card {
            height: 500px;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            overflow: hidden;
            position: relative;
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            backdrop-filter: blur(10px);
            opacity: 0;
            transform: rotateY(20deg) translateZ(-50px);
        }

        .equipment-card.visible {
            opacity: 1;
            transform: rotateY(0deg) translateZ(0px);
        }

        .equipment-card:hover {
            transform: translateY(-20px) rotateY(-5deg);
            box-shadow: 0 24px 48px rgba(99, 102, 241, 0.24);
        }
        /* Lightweight hover feedback (CSS-only) to replace JS ripple */
        .feature-card::after, .equipment-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(120px 120px at var(--mx,50%) var(--my,50%), rgba(99,102,241,0.15), transparent 60%);
            opacity: 0;
            transition: opacity 200ms ease;
            pointer-events: none;
        }
        .feature-card:hover::after, .equipment-card:hover::after { opacity: 1; }

        @media (max-width: 768px) {
            .navbar.scrolled { backdrop-filter: none; background: rgba(0,0,0,0.9); }
            .feature-card, .equipment-card, .login-btn { backdrop-filter: none !important; }
            .feature-card, .equipment-card { background: rgba(255,255,255,0.06); }
            .feature-icon { box-shadow: none; }
        }

        .equipment-visual {
            height: 70%;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(168, 85, 247, 0.1));
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .equipment-visual::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at center, transparent 40%, rgba(0, 0, 0, 0.3));
        }

        .equipment-icon {
            width: 100px;
            height: 100px;
            color: white;
            opacity: 0.8;
            z-index: 1;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.3));
        }

        .equipment-info {
            padding: 2.5rem;
            height: 30%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .equipment-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: white;
        }

        .equipment-specs {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.5;
        }





        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .nav-links { display: none; }
            .cta-buttons { flex-direction: column; align-items: center; }
            .features-grid { grid-template-columns: 1fr; gap: 2rem; }
            .equipment-grid { grid-template-columns: 1fr; }
            .hero-title { font-size: 4rem; }
            .navbar { padding: 1rem 5%; }
            .hero-subtitle { font-size: 1.1rem; }
            .hero-description { font-size: 1rem; margin-bottom: 2rem; padding: 0 0.5rem; }
            .cta-buttons a { width: 100%; text-align: center; }
            .section { padding: 70px 6%; }
            .features-grid { grid-template-columns: 1fr; gap: 1.25rem; }
            .equipment-grid { grid-template-columns: 1fr; gap: 1.5rem; }
            .equipment-card { height: auto; }
            .equipment-visual { height: 220px; }
            .nav-links { display: none; }
        }

        /* CUSTOM SCROLLBAR */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(var(--primary), var(--secondary));
            border-radius: 10px;
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
            transition: opacity 0.8s ease;
        }

        .loader.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .loader-text {
            font-size: 3rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.05); }
        }
        @media (prefers-reduced-motion: reduce) {
            * { animation-duration: 0.001ms !important; animation-iteration-count: 1 !important; transition-duration: 0.001ms !important; }
            .hero-title{ animation: none !important; }
        }
    </style>
</head>
<body>
    <!-- LOADER -->
    <div class="loader" id="loader">
        <div class="loader-text">STELLAR</div>
    </div>

    <!-- BACKGROUND GALAXIE FIXE -->
    <div class="galaxy-background"></div>
    <div class="content-overlay"></div>
    <canvas id="stars-canvas"></canvas>

    <!-- NAVIGATION -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <div class="logo">STELLAR</div>
            <ul class="nav-links">
                <li><a href="#home">Accueil</a></li>
                <li><a href="#features">Fonctionnalités</a></li>
                <li><a href="#equipment">Équipement</a></li>
                <li><a href="#about">À propos</a></li>
            </ul>
            <a href="/fr/login" class="login-btn">Se connecter</a>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1 class="hero-title">STELLAR</h1>
            <p class="hero-subtitle">Explorez l'Univers Comme Jamais Auparavant</p>
            <p class="hero-description">
                Accédez aux meilleurs télescopes professionnels du monde depuis chez vous.
                Découvrez les merveilles du cosmos avec notre plateforme révolutionnaire.
            </p>
            <div class="cta-buttons">
                <a href="/fr/register" class="btn-primary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2L15.09 8.26L22 9L17 14L18.18 21L12 17.77L5.82 21L7 14L2 9L8.91 8.26L12 2Z" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    Commencer l'Exploration
                </a>
                <a href="#features" class="btn-secondary">Découvrir Plus</a>
            </div>
        </div>
        <div class="scroll-indicator">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M7 13L12 18L17 13" stroke="white" stroke-width="2" stroke-linecap="round"/>
                <path d="M7 6L12 11L17 6" stroke="white" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>


    </section>

    <!-- FEATURES SECTION -->
    <section class="section section-reveal" id="features">
        <div class="section-header">
            <div class="section-badge">Technologies Avancées</div>
            <h2 class="section-title">Une Révolution Astronomique</h2>
            <p class="section-description">
                Découvrez une nouvelle façon d'explorer l'univers grâce à nos technologies de pointe
                et notre plateforme intuitive.
            </p>
        </div>

        <div class="features-grid">
            <div class="feature-card" style="transition-delay: 0.1s;">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                        <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
                <h3 class="feature-title">Télescopes Professionnels</h3>
                <p class="feature-description">
                    Accédez à des télescopes de classe mondiale situés dans les meilleurs
                    observatoires de la planète.
                </p>
            </div>

            <div class="feature-card" style="transition-delay: 0.2s;">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                        <path d="M8 14s1.5 2 4 2 4-2 4-2" stroke="currentColor" stroke-width="2"/>
                        <path d="M9 9h.01M15 9h.01" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
                <h3 class="feature-title">Contrôle à Distance</h3>
                <p class="feature-description">
                    Pilotez les télescopes en temps réel depuis chez vous avec notre
                    interface de contrôle intuitive.
                </p>
            </div>

            <div class="feature-card" style="transition-delay: 0.3s;">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="2"/>
                        <path d="M21 15l-5-5L5 21" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
                <h3 class="feature-title">Images Ultra HD</h3>
                <p class="feature-description">
                    Capturez des images époustouflantes en ultra haute résolution
                    des merveilles de l'univers.
                </p>
            </div>

            <div class="feature-card" style="transition-delay: 0.4s;">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" stroke="currentColor" stroke-width="2"/>
                        <polyline points="3.27,6.96 12,12.01 20.73,6.96" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
                <h3 class="feature-title">Ciel Pur</h3>
                <p class="feature-description">
                    Nos observatoires sont situés dans des zones sans pollution lumineuse
                    pour une qualité d'observation optimale.
                </p>
            </div>

            <div class="feature-card" style="transition-delay: 0.5s;">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2"/>
                        <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
                <h3 class="feature-title">Assistance Expert</h3>
                <p class="feature-description">
                    Bénéficiez de l'accompagnement de nos astronomes professionnels
                    pour optimiser vos observations.
                </p>
            </div>

            <div class="feature-card" style="transition-delay: 0.6s;">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                        <polyline points="12,6 12,12 16,14" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
                <h3 class="feature-title">Planification Intelligente</h3>
                <p class="feature-description">
                    Notre IA vous aide à planifier vos sessions d'observation selon
                    les conditions météorologiques et astronomiques.
                </p>
            </div>
        </div>
    </section>

    <!-- EQUIPMENT SECTION -->
    <section class="section section-reveal" id="equipment">
        <div class="section-header">
            <div class="section-badge">Équipement Premium</div>
            <h2 class="section-title">Notre Flotte Stellaire</h2>
            <p class="section-description">
                Découvrez notre collection d'instruments d'observation de classe mondiale,
                situés dans les meilleurs observatoires de la planète.
            </p>
        </div>

        <div class="equipment-grid">
            <div class="equipment-card" style="transition-delay: 0.1s;">
                <div class="equipment-visual">
                    <svg class="equipment-icon" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                        <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1" stroke="currentColor" stroke-width="2"/>
                        <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1" opacity="0.3"/>
                    </svg>
                </div>
                <div class="equipment-info">
                    <h3 class="equipment-name">Celestron CGX-L 1400</h3>
                    <p class="equipment-specs">
                        Schmidt-Cassegrain 14" • f/11 • 3910mm<br>
                        Excellence en observation planétaire et ciel profond
                    </p>
                </div>
            </div>

            <div class="equipment-card" style="transition-delay: 0.2s;">
                <div class="equipment-visual">
                    <svg class="equipment-icon" viewBox="0 0 24 24" fill="none">
                        <polygon points="12,2 22,8.5 22,15.5 12,22 2,15.5 2,8.5" stroke="currentColor" stroke-width="2"/>
                        <line x1="12" y1="22" x2="12" y2="12" stroke="currentColor" stroke-width="2"/>
                        <line x1="2" y1="8.5" x2="12" y2="12" stroke="currentColor" stroke-width="2"/>
                        <line x1="22" y1="8.5" x2="12" y2="12" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
                <div class="equipment-info">
                    <h3 class="equipment-name">PlaneWave CDK24</h3>
                    <p class="equipment-specs">
                        Corrected Dall-Kirkham 24" • f/6.5<br>
                        Perfection absolue pour l'astrophotographie
                    </p>
                </div>
            </div>

            <div class="equipment-card" style="transition-delay: 0.3s;">
                <div class="equipment-visual">
                    <svg class="equipment-icon" viewBox="0 0 24 24" fill="none">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" stroke="currentColor" stroke-width="2"/>
                        <polyline points="7.5,9.5 12,12 16.5,9.5" stroke="currentColor" stroke-width="2"/>
                        <line x1="12" y1="12" x2="12" y2="16.5" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
                <div class="equipment-info">
                    <h3 class="equipment-name">Takahashi FSQ-106</h3>
                    <p class="equipment-specs">
                        Réfracteur apochromatique • f/5<br>
                        Légende en astrophotographie grand champ
                    </p>
                </div>
            </div>
        </div>
    </section>

    <script>
        // LOADER
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('loader').classList.add('hidden');
            }, 1500);
        });

        // NAVBAR SCROLL EFFECT
        let lastScrollY = window.scrollY;
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            const scrollY = window.scrollY;

            if (scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }

            // Hide/show navbar on scroll direction
            if (scrollY > lastScrollY && scrollY > 200) {
                navbar.style.transform = 'translateY(-100%)';
            } else {
                navbar.style.transform = 'translateY(0)';
            }
            lastScrollY = scrollY;
        }, { passive: true });

        // AWARD-WINNING SCROLL ANIMATIONS
        const observerOptions = {
            threshold: 0.2,
            rootMargin: '-50px 0px -50px 0px'
        };

        const scrollObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');

                    // Animate feature cards with stagger
                    if (entry.target.classList.contains('features-grid')) {
                        const cards = entry.target.querySelectorAll('.feature-card');
                        cards.forEach((card, index) => {
                            setTimeout(() => {
                                card.classList.add('visible');
                            }, index * 100);
                        });
                    }

                    // Animate equipment cards with stagger
                    if (entry.target.classList.contains('equipment-grid')) {
                        const cards = entry.target.querySelectorAll('.equipment-card');
                        cards.forEach((card, index) => {
                            setTimeout(() => {
                                card.classList.add('visible');
                            }, index * 150);
                        });
                    }
                }
            });
        }, observerOptions);

        // Observe elements
        document.querySelectorAll('.section-reveal, .features-grid, .equipment-grid').forEach(el => {
            scrollObserver.observe(el);
        });

        // PARALLAX EFFECT FOR GALAXY BACKGROUND
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.3;

            // Galaxy background parallax
            const galaxyBg = document.querySelector('.galaxy-background');
            galaxyBg.style.transform = `translateY(${rate}px) scale(1.1)`;


        }, { passive: true });

        // 3D STARFIELD PARALLAX (CANVAS)
        (function() {
            const canvas = document.getElementById('stars-canvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d', { alpha: true });

            const isMobile = window.matchMedia('(max-width: 768px)').matches;
            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            // DPR cap for perf
            const dpr = Math.min(window.devicePixelRatio || 1, (isMobile ? 1 : 1.5));

            // Canvas sizing (CSS vs device pixels)
            let cssW = window.innerWidth, cssH = window.innerHeight;
            let width = canvas.width = Math.floor(cssW * dpr);
            let height = canvas.height = Math.floor(cssH * dpr);
            canvas.style.width = cssW + 'px';
            canvas.style.height = cssH + 'px';
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

            // Base density tuned down for smoothness
            const baseDensity = reducedMotion ? 0.00005 : (isMobile ? 0.00007 : 0.00011);

            // Parallax factor per layer (near moves more)
            const PARALLAX_BASE = isMobile ? 7 : 16; // overall softness

            // Layer definitions (far, mid, near)
            const layers = [
                { key: 'far',  mult: 0.45, parallax: 0.35, size: 0.70, glow: (isMobile ? 1.2 : 1.6) },
                { key: 'mid',  mult: 0.35, parallax: 0.75, size: 1.00, glow: (isMobile ? 1.6 : 2.2) },
                { key: 'near', mult: 0.20, parallax: 1.25, size: 1.35, glow: (isMobile ? 2.0 : 2.8) }
            ];

            // State
            const field = new Map(); // key -> array of stars
            let totalCount = clamp(Math.round(cssW * cssH * baseDensity), 150, 650);

            let mouseX = 0, mouseY = 0;   // [-1..1]
            let parallaxX = 0, parallaxY = 0; // smoothed
            let lastT = 0, fpsAvg = 60, running = true;

            function clamp(v, a, b){ return Math.max(a, Math.min(v, b)); }
            function rand(min, max){ return Math.random() * (max - min) + min; }

            function createLayerStars() {
                field.clear();
                layers.forEach(layer => {
                    const count = Math.round(totalCount * layer.mult);
                    const arr = [];
                    for (let i = 0; i < count; i++) {
                        // depth z biased to far for subtlety inside each layer
                        const z = Math.pow(Math.random(), 2);
                        arr.push({
                            x: Math.random() * cssW, // fixed pixel positions
                            y: Math.random() * cssH,
                            z,
                            r: rand(0.2, 1.2) * (1 - z) * layer.size,
                            tw: rand(0.5, 1.5),
                            ph: rand(0, Math.PI * 2)
                        });
                    }
                    field.set(layer.key, arr);
                });
            }

            function resize(){
                const prevW = cssW, prevH = cssH;
                cssW = window.innerWidth; cssH = window.innerHeight;
                width = canvas.width = Math.floor(cssW * dpr);
                height = canvas.height = Math.floor(cssH * dpr);
                canvas.style.width = cssW + 'px';
                canvas.style.height = cssH + 'px';
                ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

                // Reproject existing stars proportionally
                field.forEach(arr => {
                    arr.forEach(s => {
                        s.x = Math.min(s.x, prevW) * (cssW / prevW);
                        s.y = Math.min(s.y, prevH) * (cssH / prevH);
                    });
                });

                // Recompute total count and rebuild if changed
                const newTotal = clamp(Math.round(cssW * cssH * baseDensity), 150, 650);
                if (newTotal !== totalCount) {
                    totalCount = newTotal;
                    createLayerStars();
                }
            }

            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(resize, 150);
            }, { passive: true });

            // Mouse target for parallax (desktop only effectively)
            document.addEventListener('mousemove', (e) => {
                mouseX = (e.clientX / cssW - 0.5) * 2;
                mouseY = (e.clientY / cssH - 0.5) * 2;
            });

            function updateParallax(){
                parallaxX += (mouseX - parallaxX) * 0.04; // slow, cinematic
                parallaxY += (mouseY - parallaxY) * 0.04;
            }

            document.addEventListener('visibilitychange', () => {
                running = !document.hidden;
                if (running) requestAnimationFrame(draw);
            });

            function draw(t){
                if (!running) return;
                updateParallax();
                ctx.clearRect(0, 0, width, height);

                // FPS adaptation
                const dt = (t - lastT) || 16; lastT = t;
                const fps = 1000 / dt; fpsAvg = fpsAvg * 0.9 + fps * 0.1;
                if (fpsAvg < 45 && totalCount > 220) {
                    totalCount = Math.floor(totalCount * 0.9);
                    createLayerStars();
                }

                // Gentle autonomous drift shared by all layers (no scroll influence)
                const autoX = Math.sin(t * 0.00010) * 2;
                const autoY = Math.cos(t * 0.00012) * 2;

                // Render back-to-front: far -> mid -> near
                layers.forEach(layer => {
                    const arr = field.get(layer.key) || [];
                    const p = PARALLAX_BASE * layer.parallax; // depth weight
                    const glow = layer.glow;
                    for (let i = 0; i < arr.length; i++) {
                        const s = arr[i];
                        const depth = 1 - s.z;

                        const px = s.x + (parallaxX * depth * p) + (autoX * depth * layer.parallax);
                        const py = s.y + (parallaxY * depth * p) + (autoY * depth * layer.parallax);

                        const twinkle = reducedMotion ? 1 : (0.88 + 0.12 * Math.sin(t * 0.001 * s.tw + s.ph));
                        const r = Math.max(0.18, s.r * (0.7 + 0.3 * depth)) * (0.92 + 0.08 * twinkle);

                        ctx.beginPath();
                        ctx.arc(px, py, r, 0, Math.PI * 2);
                        ctx.fillStyle = `rgba(255,255,255,${0.45 + 0.45 * twinkle})`;
                        ctx.shadowBlur = glow * depth;
                        ctx.shadowColor = 'rgba(255,255,255,0.8)';
                        ctx.fill();
                        ctx.shadowBlur = 0;
                    }
                });

                requestAnimationFrame(draw);
            }

            createLayerStars();
            requestAnimationFrame(draw);
        })();

        // MAGNETIC CURSOR EFFECT FOR BUTTONS
        const isTouch = ('ontouchstart' in window) || navigator.maxTouchPoints > 0;
        const buttons = document.querySelectorAll('.btn-primary, .btn-secondary, .login-btn');
        if (!isTouch) {
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
        }

        // SMOOTH SCROLL FOR NAVIGATION
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const targetPosition = target.offsetTop - 100;
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // HERO TITLE MOUSE TRACKING
        const heroTitle = document.querySelector('.hero-title');
        let mouseX = 0, mouseY = 0;
        let titleX = 0, titleY = 0;
        const disableHeroMouse = isTouch || window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (!disableHeroMouse) {
            document.addEventListener('mousemove', (e) => {
                mouseX = (e.clientX / window.innerWidth - 0.5) * 2;
                mouseY = (e.clientY / window.innerHeight - 0.5) * 2;
            });
        }

        function animateTitle() {
            if (disableHeroMouse) { requestAnimationFrame(animateTitle); return; }
            titleX += (mouseX - titleX) * 0.1;
            titleY += (mouseY - titleY) * 0.1;

            if (window.scrollY < window.innerHeight) {
                heroTitle.style.transform = `translate(${titleX * 10}px, ${titleY * 10}px)`;
            }

            requestAnimationFrame(animateTitle);
        }
        animateTitle();

        // Lightweight hover feedback: update CSS vars for radial highlight (no DOM churn)
        document.querySelectorAll('.feature-card, .equipment-card').forEach(card => {
            card.addEventListener('pointermove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = ((e.clientX || 0) - rect.left) + 'px';
                const y = ((e.clientY || 0) - rect.top) + 'px';
                card.style.setProperty('--mx', x);
                card.style.setProperty('--my', y);
            }, { passive: true });
        });

        // PERFORMANCE OPTIMIZATION
        let ticking = false;

        function updateScrollEffects() {
            // All scroll-based animations here
            ticking = false;
        }

        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(updateScrollEffects);
                ticking = true;
            }
        }, { passive: true });

        // ACCESSIBILITY ENHANCEMENTS
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                document.body.style.setProperty('--focus-visible', '2px solid #6366f1');
            }
        });

        document.addEventListener('mousedown', () => {
            document.body.style.setProperty('--focus-visible', 'none');
        });

        // PRELOAD CRITICAL RESOURCES
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.willChange = 'transform, opacity';
                } else {
                    entry.target.style.willChange = 'auto';
                }
            });
        });

        document.querySelectorAll('.feature-card, .equipment-card').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>
