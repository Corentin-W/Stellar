<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stellar - Explorez l'Univers</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
            --gold: #fbbf24;
            --text-light: rgba(255, 255, 255, 0.95);
            --text-muted: rgba(255, 255, 255, 0.7);
            --glass: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.15);
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

        /* BACKGROUND SYSTEM */
        .galaxy-bg {
            position: fixed;
            inset: 0;
            background: url('/img/welcome/background.jpg') center/cover no-repeat;
            z-index: -10;
        }

        .space-gradient {
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse at center bottom, transparent 0%, rgba(0,0,0,0.3) 50%, rgba(0,0,0,0.7) 80%, #000 100%),
                radial-gradient(circle at 20% 80%, rgba(99,102,241,0.08) 0%, transparent 40%),
                radial-gradient(circle at 80% 20%, rgba(168,85,247,0.06) 0%, transparent 40%);
            z-index: -9;
        }

        /* ANIMATED PARTICLES - TAILLES VARIÉES */
        .particles {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: -8;
        }

        .particle {
            position: absolute;
            background: white;
            border-radius: 50%;
            opacity: 0;
            animation: float 15s infinite linear;
        }

        .particle.small {
            width: 1px;
            height: 1px;
        }

        .particle.medium {
            width: 2px;
            height: 2px;
            box-shadow: 0 0 4px rgba(255, 255, 255, 0.5);
        }

        .particle.large {
            width: 3px;
            height: 3px;
            box-shadow: 0 0 8px rgba(255, 255, 255, 0.8);
        }

        .particle.xlarge {
            width: 4px;
            height: 4px;
            box-shadow: 0 0 12px rgba(255, 255, 255, 1);
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) translateX(0) scale(0);
                opacity: 0;
            }
            10% {
                opacity: 0.8;
            }
            90% {
                opacity: 0.8;
            }
            100% {
                transform: translateY(-10vh) translateX(50px) scale(1);
                opacity: 0;
            }
        }

        /* BADGE BIENTOT DISPONIBLE */
        .status-badge {
            position: fixed;
            top: 2rem;
            right: 2rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 2rem;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 1px;
            backdrop-filter: blur(10px);
            z-index: 100;
            animation: badgeFloat 4s ease-in-out infinite;
            overflow: hidden;
        }

        .status-badge::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: translateX(-100%);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            100% { transform: translateX(100%); }
        }

        @keyframes badgeFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        /* HERO SECTION */
        .hero {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            max-width: 1000px;
            padding: 0 2rem;
            transform: translateY(50px);
            opacity: 0;
            animation: heroReveal 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.5s forwards;
        }

        @keyframes heroReveal {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .hero-title {
            font-size: clamp(4rem, 15vw, 12rem);
            font-weight: 900;
            letter-spacing: -0.05em;
            margin-bottom: 2rem;
            background: linear-gradient(135deg,
                #ffffff 0%,
                var(--primary) 25%,
                var(--secondary) 50%,
                var(--gold) 75%,
                #ffffff 100%
            );
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-size: 300% 300%;
            animation: gradientFlow 6s ease-in-out infinite;
            position: relative;
        }

        @keyframes gradientFlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .hero-subtitle {
            font-size: 1.8rem;
            color: var(--text-light);
            margin-bottom: 1.5rem;
            font-weight: 300;
            letter-spacing: 1px;
            animation: slideUp 1s ease-out 0.8s both;
        }

        .hero-description {
            font-size: 1.3rem;
            color: var(--text-muted);
            max-width: 750px;
            margin: 0 auto 3rem;
            line-height: 1.8;
            animation: slideUp 1s ease-out 1s both;
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .hero-actions {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: slideUp 1s ease-out 1.2s both;
        }

        .btn-primary {
            padding: 1.5rem 4rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            text-decoration: none;
            border-radius: 60px;
            font-weight: 700;
            font-size: 1.3rem;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            display: inline-flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--secondary), var(--gold));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-8px) scale(1.05);
            box-shadow: 0 40px 80px rgba(99, 102, 241, 0.4);
        }

        .btn-primary:hover::before {
            opacity: 1;
        }

        .btn-primary span {
            position: relative;
            z-index: 1;
        }

        .btn-secondary {
            padding: 1.5rem 4rem;
            background: var(--glass);
            color: white;
            text-decoration: none;
            border-radius: 60px;
            font-weight: 600;
            font-size: 1.2rem;
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(10px);
            transition: all 0.4s ease;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(255, 255, 255, 0.1);
        }

        @keyframes bounceFloat {
            0%, 20%, 50%, 80%, 100% {
                transform: translateX(-50%) translateY(0);
            }
            40% {
                transform: translateX(-50%) translateY(-15px);
            }
            60% {
                transform: translateX(-50%) translateY(-8px);
            }
        }

        /* FEATURES SECTION */
        .section {
            padding: 120px 5%;
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
        }

        .section-header {
            text-align: center;
            margin-bottom: 6rem;
            opacity: 0;
            transform: translateY(50px);
            transition: all 1s ease;
        }

        .section-header.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .section-badge {
            display: inline-block;
            padding: 0.8rem 2rem;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
        }

        .section-title {
            font-size: clamp(3rem, 8vw, 5rem);
            font-weight: 800;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #fff 0%, var(--primary) 50%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .section-description {
            font-size: 1.4rem;
            color: var(--text-muted);
            max-width: 750px;
            margin: 0 auto;
            line-height: 1.8;
        }

        /* FEATURES GRID */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 3rem;
            margin-top: 6rem;
        }

        .feature-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            padding: 4rem 3rem;
            transition: all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(80px) rotateX(10deg);
        }

        .feature-card.visible {
            opacity: 1;
            transform: translateY(0) rotateX(0deg);
        }

        .feature-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg,
                rgba(99, 102, 241, 0.1) 0%,
                rgba(168, 85, 247, 0.05) 50%,
                rgba(236, 72, 153, 0.1) 100%
            );
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .feature-card:hover {
            transform: translateY(-25px) rotateX(5deg) scale(1.02);
            box-shadow: 0 40px 80px rgba(99, 102, 241, 0.2);
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 3rem;
            font-size: 3rem;
            animation: iconFloat 6s ease-in-out infinite;
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.3);
        }

        @keyframes iconFloat {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
                box-shadow: 0 20px 40px rgba(99, 102, 241, 0.3);
            }
            50% {
                transform: translateY(-10px) rotate(5deg);
                box-shadow: 0 30px 60px rgba(99, 102, 241, 0.4);
            }
        }

        .feature-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: white;
        }

        .feature-description {
            color: var(--text-muted);
            line-height: 1.8;
            font-size: 1.2rem;
        }

        /* WAITING LIST MODAL */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            backdrop-filter: blur(10px);
            opacity: 0;
            transition: all 0.5s ease;
        }

        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }

        .modal {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            padding: 2.5rem;
            max-width: 480px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            backdrop-filter: blur(30px);
            position: relative;
            transform: scale(0.8) translateY(100px);
            transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .modal-overlay.active .modal {
            transform: scale(1) translateY(0);
        }

        .modal-close {
            position: absolute;
            top: 2rem;
            right: 2.5rem;
            background: none;
            border: none;
            font-size: 2.5rem;
            color: var(--text-light);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modal-close:hover {
            transform: rotate(90deg);
            color: var(--accent);
        }

        .modal-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
        }

        .modal-subtitle {
            color: var(--text-muted);
            margin-bottom: 2rem;
            font-size: 1rem;
            line-height: 1.6;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-light);
            font-size: 1rem;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
            background: rgba(255, 255, 255, 0.12);
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        .submit-btn {
            width: 100%;
            padding: 1.2rem 2rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 15px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--secondary), var(--gold));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(99, 102, 241, 0.4);
        }

        .submit-btn:hover::before {
            opacity: 1;
        }

        .submit-btn span {
            position: relative;
            z-index: 1;
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .success-message, .info-message {
            border-radius: 20px;
            padding: 2rem;
            margin-top: 2rem;
            text-align: center;
            display: none;
            animation: messageSlide 0.6s ease;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .success-message {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
        }

        .info-message {
            background: rgba(59, 130, 246, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #3b82f6;
        }

        .success-message.show, .info-message.show {
            display: block;
        }

        @keyframes messageSlide {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* TOAST NOTIFICATIONS */
        .toast {
            position: fixed;
            top: 2rem;
            left: 50%;
            transform: translateX(-50%) translateY(-100px);
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 1rem 2rem;
            backdrop-filter: blur(20px);
            z-index: 15000;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            max-width: 400px;
            text-align: center;
        }

        .toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .toast.success {
            border-color: rgba(16, 185, 129, 0.3);
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .toast.info {
            border-color: rgba(59, 130, 246, 0.3);
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .hero-actions {
                flex-direction: column;
                align-items: center;
                gap: 1.5rem;
            }
            .btn-primary, .btn-secondary {
                width: 100%;
                max-width: 300px;
                padding: 1.2rem 2rem;
                font-size: 1.1rem;
            }
            .features-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            .section {
                padding: 80px 5%;
            }
            .modal {
                padding: 3rem 2rem;
                margin: 1rem;
            }
            .hero-title {
                font-size: 3.5rem;
            }
            .feature-card {
                padding: 3rem 2rem;
            }
            .status-badge {
                top: 1rem;
                right: 1rem;
                padding: 0.6rem 1.5rem;
                font-size: 0.8rem;
            }
        }

        /* PERFORMANCE OPTIMIZATIONS */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <!-- BACKGROUND LAYERS -->
    <div class="galaxy-bg"></div>
    <div class="space-gradient"></div>
    <div class="particles" id="particles"></div>

    <!-- TOAST CONTAINER -->
    <div id="toastContainer"></div>

    <!-- BADGE BIENTOT DISPONIBLE -->
    <div class="status-badge">
        <span>🚀</span>
        <span>Bientôt Disponible</span>
    </div>

    <!-- HERO SECTION -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1 class="hero-title">STELLAR</h1>

            <p class="hero-subtitle">Explorez l'Univers Comme Jamais Auparavant</p>

            <p class="hero-description">
                Accédez aux meilleurs télescopes professionnels du monde depuis chez vous.
                Découvrez les merveilles du cosmos avec notre plateforme révolutionnaire qui rend
                l'astronomie accessible à tous, partout dans le monde.
            </p>

            <div class="hero-actions">
                <a href="#" class="btn-primary" onclick="openWaitingList()">
                    <span>⭐ Rejoindre la Waiting List</span>
                </a>
                <a href="#features" class="btn-secondary">Découvrir Plus</a>
            </div>
        </div>


    </section>

    <!-- FEATURES SECTION -->
    <section class="section" id="features">
        <div class="section-header">
            <div class="section-badge">Technologies Avancées</div>
            <h2 class="section-title">Une Révolution Astronomique</h2>
            <p class="section-description">
                Découvrez une nouvelle façon d'explorer l'univers grâce à nos technologies
                de pointe et notre plateforme intuitive conçue pour tous les passionnés d'astronomie.
            </p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🔭</div>
                <h3 class="feature-title">Télescopes Professionnels</h3>
                <p class="feature-description">
                    Accédez à une flotte de télescopes de classe mondiale situés dans les meilleurs
                    observatoires de la planète, avec des instruments d'une précision exceptionnelle.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🎮</div>
                <h3 class="feature-title">Contrôle à Distance</h3>
                <p class="feature-description">
                    Pilotez les télescopes en temps réel depuis chez vous avec notre
                    interface de contrôle intuitive et responsive, conçue pour une expérience immersive.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">📸</div>
                <h3 class="feature-title">Images Ultra HD</h3>
                <p class="feature-description">
                    Capturez des images époustouflantes en ultra haute résolution
                    des merveilles de l'univers et constituez votre propre galerie cosmique.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🌌</div>
                <h3 class="feature-title">Ciel Pur</h3>
                <p class="feature-description">
                    Nos observatoires sont situés dans des zones sans pollution lumineuse
                    pour une qualité d'observation optimale et des conditions d'imagerie parfaites.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">👨‍🔬</div>
                <h3 class="feature-title">Assistance Expert</h3>
                <p class="feature-description">
                    Bénéficiez de l'accompagnement de nos astronomes professionnels
                    pour optimiser vos observations et découvrir les secrets de l'univers.
                </p>
            </div>


        </div>
                    <br>
        <br>
          <div class="hero-actions">
                <a href="#" class="btn-primary" onclick="openWaitingList()">
                    <span>⭐ Rejoindre la Waiting List</span>
                </a>
            </div>
        </div>
    </section>

    <!-- WAITING LIST MODAL -->
    <div class="modal-overlay" id="waitingListModal">
        <div class="modal">
            <button class="modal-close" onclick="closeWaitingList()">&times;</button>

            <h2 class="modal-title">🚀 Rejoignez l'Aventure</h2>
            <p class="modal-subtitle">
                Soyez parmi les premiers à explorer l'univers avec Stellar.
                Recevez un accès anticipé, des tarifs préférentiels et des contenus exclusifs.
            </p>

            <form id="waitingListForm">
                <div class="form-group">
                    <label class="form-label" for="firstName">Prénom</label>
                    <input type="text" id="firstName" name="firstName" class="form-input"
                           placeholder="Votre prénom" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="lastName">Nom</label>
                    <input type="text" id="lastName" name="lastName" class="form-input"
                           placeholder="Votre nom" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input"
                           placeholder="votre@email.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="interest">Niveau d'intérêt</label>
                    <select id="interest" name="interest" class="form-select" required>
                        <option value="">Sélectionnez votre niveau</option>
                        <option value="debutant">Débutant curieux</option>
                        <option value="amateur">Amateur passionné</option>
                        <option value="avance">Utilisateur avancé</option>
                        <option value="professionnel">Professionnel</option>
                    </select>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <span>🌟 Rejoindre la Waiting List</span>
                </button>

                <div class="success-message" id="successMessage">
                    ✅ Parfait ! Vous êtes maintenant sur la waiting list.
                    Un email de confirmation vous a été envoyé.
                </div>

                <div class="info-message" id="infoMessage">
                    ℹ️ Vous êtes déjà inscrit sur notre waiting list !
                </div>
            </form>


    </div>

    <script>
        // PARTICLES ANIMATION AVEC TAILLES VARIÉES
        function createParticles() {
            const container = document.getElementById('particles');
            container.innerHTML = ''; // Clear existing particles
            const particleCount = window.innerWidth < 768 ? 20 : 30;

            const sizes = ['small', 'medium', 'large', 'xlarge'];
            const sizeDistribution = [0.5, 0.3, 0.15, 0.05]; // Probabilités: 50% small, 30% medium, 15% large, 5% xlarge

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';

                // Sélection de la taille selon la distribution
                const random = Math.random();
                let cumulativeProbability = 0;
                let selectedSize = 'small';

                for (let j = 0; j < sizes.length; j++) {
                    cumulativeProbability += sizeDistribution[j];
                    if (random <= cumulativeProbability) {
                        selectedSize = sizes[j];
                        break;
                    }
                }

                particle.classList.add(selectedSize);

                // Position et timing
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = '100vh'; // Start from bottom

                // Délai et durée aléatoires (plus grandes particules = plus lentes)
                const baseDelay = Math.random() * 20;
                const baseDuration = selectedSize === 'xlarge' ? 20 :
                                   selectedSize === 'large' ? 18 :
                                   selectedSize === 'medium' ? 16 : 15;

                particle.style.animationDelay = baseDelay + 's';
                particle.style.animationDuration = (baseDuration + Math.random() * 5) + 's';

                // Opacité variable selon la taille
                const baseOpacity = selectedSize === 'xlarge' ? 0.9 :
                                   selectedSize === 'large' ? 0.7 :
                                   selectedSize === 'medium' ? 0.5 : 0.3;
                particle.style.opacity = baseOpacity + Math.random() * 0.2;

                container.appendChild(particle);
            }
        }

        // INTERSECTION OBSERVER FOR ANIMATIONS
        const observerOptions = {
            threshold: 0.2,
            rootMargin: '-50px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        // MODAL FUNCTIONS
        function openWaitingList() {
            document.getElementById('waitingListModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeWaitingList() {
            document.getElementById('waitingListModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        function scrollToFeatures() {
            document.getElementById('features').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        // TOAST SYSTEM - SIMPLE
        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');

            // Remove existing toast
            const existingToast = container.querySelector('.toast');
            if (existingToast) {
                existingToast.remove();
            }

            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = message;

            container.appendChild(toast);

            // Show toast
            setTimeout(() => toast.classList.add('show'), 100);

            // Hide and remove toast
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            }, 4000);
        }

        // FORM SUBMISSION AVEC TOASTS SIMPLES
        document.getElementById('waitingListForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const successMessage = document.getElementById('successMessage');
            const infoMessage = document.getElementById('infoMessage');

            // Reset messages
            successMessage.classList.remove('show');
            infoMessage.classList.remove('show');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>⏳ Inscription en cours...</span>';

            const formData = new FormData(e.target);
            const data = {
                firstName: formData.get('firstName'),
                lastName: formData.get('lastName'),
                email: formData.get('email'),
                interest: formData.get('interest')
            };

            try {
                const response = await fetch('/waiting-list', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok) {
                    // Succès - nouvelle inscription
                    showToast('✅ Parfait ! Vous êtes maintenant sur la waiting list.', 'success');
                    e.target.reset();
                    setTimeout(() => closeWaitingList(), 1500);

                } else if (response.status === 409) {
                    // Utilisateur déjà inscrit - CORRIGÉ
                    console.log('User already registered:', result); // Debug
                    showToast(`ℹ️ ${result.message}`, 'info');
                    setTimeout(() => closeWaitingList(), 2500);

                } else if (response.status === 422) {
                    // Erreurs de validation
                    const errors = result.errors;
                    if (errors && errors.email) {
                        showToast(`❌ ${errors.email[0]}`, 'info');
                    } else {
                        showToast('❌ Veuillez vérifier vos informations.', 'info');
                    }

                } else {
                    // Autres erreurs
                    showToast(`❌ ${result.message || 'Une erreur est survenue.'}`, 'info');
                }

            } catch (error) {
                console.error('Network or parsing error:', error);
                showToast('❌ Erreur de connexion. Veuillez réessayer.', 'info');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>🌟 Rejoindre la Waiting List</span>';
            }
        });

        // CLOSE MODAL ON OVERLAY CLICK
        document.getElementById('waitingListModal').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                closeWaitingList();
            }
        });

        // ESCAPE KEY TO CLOSE MODAL
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeWaitingList();
            }
        });

        // SMOOTH SCROLLING FOR ANCHORS
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // INITIALIZE ON LOAD
        document.addEventListener('DOMContentLoaded', () => {
            // Create particles with varied sizes
            createParticles();

            // Observe elements for animations
            document.querySelectorAll('.section-header, .feature-card').forEach(el => {
                observer.observe(el);
            });

            // Add stagger animation to feature cards
            const featureCards = document.querySelectorAll('.feature-card');
            featureCards.forEach((card, index) => {
                card.style.transitionDelay = `${index * 0.15}s`;
            });
        });

        // PERFORMANCE: Pause animations when page not visible
        document.addEventListener('visibilitychange', () => {
            const particles = document.querySelectorAll('.particle');
            particles.forEach(particle => {
                if (document.hidden) {
                    particle.style.animationPlayState = 'paused';
                } else {
                    particle.style.animationPlayState = 'running';
                }
            });
        });

        // RESIZE HANDLER FOR PARTICLES
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                // Recreate particles on significant resize
                const container = document.getElementById('particles');
                container.innerHTML = '';
                createParticles();
            }, 500);
        });
    </script>
</body>
</html>
