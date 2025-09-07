<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stellar - Location de Matériel d'Astronomie</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #6366f1;
            --secondary-color: #a855f7;
            --accent-color: #ec4899;
            --dark: #0f0f23;
            --light: #ffffff;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            overflow-x: hidden;
            background: #000;
            color: #fff;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, var(--primary-color), var(--secondary-color));
            border-radius: 10px;
        }

        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 1.5rem 5%;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        nav.scrolled {
            background: rgba(0, 0, 0, 0.8);
            padding: 1rem 5%;
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
            font-weight: 900;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .nav-links {
            display: flex;
            gap: 2.5rem;
            list-style: none;
        }

        .nav-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.95rem;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-links a:hover {
            color: #fff;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url('https://static.vecteezy.com/system/resources/previews/026/977/316/non_2x/nebula-galaxy-background-with-purple-blue-outer-space-cosmos-clouds-and-beautiful-universe-night-stars-ai-generative-free-photo.jpg') center/cover;
            background-attachment: fixed;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at center, transparent 0%, rgba(0, 0, 0, 0.4) 100%);
        }

        .hero-content {
            text-align: center;
            position: relative;
            z-index: 10;
            max-width: 1000px;
            padding: 0 2rem;
            opacity: 0;
            animation: fadeInUp 1.5s ease forwards;
        }

        .hero-title {
            font-size: clamp(4rem, 12vw, 10rem);
            font-weight: 900;
            letter-spacing: -0.02em;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #fff 0%, #a78bfa 50%, #60a5fa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 0 30px rgba(139, 92, 246, 0.5));
            animation: glow 3s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from { filter: drop-shadow(0 0 20px rgba(139, 92, 246, 0.5)); }
            to { filter: drop-shadow(0 0 40px rgba(139, 92, 246, 0.8)); }
        }

        .hero-subtitle {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: 300;
        }

        .hero-description {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 3rem;
            line-height: 1.8;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-button {
            display: inline-block;
            padding: 1.2rem 3rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);
            position: relative;
            overflow: hidden;
        }

        .cta-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .cta-button:hover::before {
            left: 100%;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(139, 92, 246, 0.4);
        }

        /* Floating Elements */
        .floating-stars {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }

        .star {
            position: absolute;
            background: white;
            border-radius: 50%;
            animation: twinkle 3s infinite;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.2); }
        }

        /* Sections */
        .section {
            padding: 100px 5%;
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
        }

        .section-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            opacity: 0;
            transform: translateY(50px);
        }

        .section-title.visible {
            animation: fadeInUp 1s ease forwards;
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2.5rem;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(50px);
        }

        .feature-card.visible {
            animation: fadeInUp 0.8s ease forwards;
            animation-delay: calc(var(--index) * 0.1s);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(139, 92, 246, 0.5);
            box-shadow: 0 20px 40px rgba(139, 92, 246, 0.2);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .feature-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #fff;
        }

        .feature-description {
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
        }

        /* Equipment Section */
        .equipment-showcase {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
        }

        .equipment-card {
            position: relative;
            height: 400px;
            border-radius: 20px;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(168, 85, 247, 0.1));
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            opacity: 0;
            transform: scale(0.9);
        }

        .equipment-card.visible {
            animation: scaleIn 0.8s ease forwards;
            animation-delay: calc(var(--index) * 0.15s);
        }

        @keyframes scaleIn {
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .equipment-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 30px 60px rgba(139, 92, 246, 0.3);
        }

        .equipment-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 2rem;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
        }

        .equipment-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .equipment-specs {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }

        /* Animations */
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Parallax Elements */
        .parallax-element {
            position: absolute;
            pointer-events: none;
            opacity: 0.5;
        }

        .planet {
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, #8b5cf6, #3b0764);
            border-radius: 50%;
            position: absolute;
            right: 10%;
            top: 20%;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hero-title {
                font-size: 3rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .section {
                padding: 60px 5%;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Loading Animation */
        .loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }

        .loader.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .loader-text {
            font-size: 2rem;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: pulse 1.5s ease infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Side text */
        .side-text {
            position: fixed;
            left: 2rem;
            top: 50%;
            transform: translateY(-50%) rotate(-90deg);
            transform-origin: left center;
            font-size: 0.8rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.3);
            z-index: 100;
        }
    </style>
</head>
<body>
    <!-- Loader -->
    <div class="loader" id="loader">
        <div class="loader-text">STELLAR</div>
    </div>

    <!-- Side Text -->
    <div class="side-text">BEYOND THE STARS</div>

    <!-- Navigation -->
    <nav id="navbar">
        <div class="nav-container">
            <div class="logo">STELLAR</div>
            <ul class="nav-links">
                <li><a href="#home">Accueil</a></li>
                <li><a href="#features">Fonctionnalités</a></li>
                <li><a href="#equipment">Équipement</a></li>
                <li><a href="#experience">Expérience</a></li>
                <li><a href="#about">À propos</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="floating-stars" id="stars"></div>
        <div class="planet parallax-element"></div>
        <div class="hero-content">
            <h1 class="hero-title">STELLAR</h1>
            <p class="hero-subtitle">Explorez l'Univers Comme Jamais Auparavant</p>
            <p class="hero-description">
                Accédez aux meilleurs télescopes professionnels du monde depuis chez vous.
                Découvrez les merveilles du cosmos avec notre plateforme de location
                de matériel d'astronomie à distance.
            </p>
            <a href="#" class="cta-button">Réserver Votre Observation</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section" id="features">
        <h2 class="section-title">Une Nouvelle Façon d'Observer les Étoiles</h2>
        <div class="features-grid">
            <div class="feature-card" style="--index: 0">
                <div class="feature-icon">🔭</div>
                <h3 class="feature-title">Télescopes Professionnels</h3>
                <p class="feature-description">
                    Accédez à des télescopes de pointe situés dans les meilleurs sites
                    d'observation du monde, sans quitter votre domicile.
                </p>
            </div>
            <div class="feature-card" style="--index: 1">
                <div class="feature-icon">🌍</div>
                <h3 class="feature-title">Contrôle à Distance</h3>
                <p class="feature-description">
                    Pilotez les télescopes en temps réel depuis votre ordinateur.
                    Pointez, zoomez et capturez les merveilles de l'univers.
                </p>
            </div>
            <div class="feature-card" style="--index: 2">
                <div class="feature-icon">📸</div>
                <h3 class="feature-title">Images Haute Résolution</h3>
                <p class="feature-description">
                    Capturez des images époustouflantes en haute résolution
                    et téléchargez vos observations pour les conserver.
                </p>
            </div>
            <div class="feature-card" style="--index: 3">
                <div class="feature-icon">🌌</div>
                <h3 class="feature-title">Ciel Sans Pollution</h3>
                <p class="feature-description">
                    Nos observatoires sont situés dans des zones sans pollution lumineuse
                    pour une observation optimale du ciel profond.
                </p>
            </div>
            <div class="feature-card" style="--index: 4">
                <div class="feature-icon">📚</div>
                <h3 class="feature-title">Assistance Expert</h3>
                <p class="feature-description">
                    Bénéficiez de l'aide de nos astronomes professionnels
                    pour vos sessions d'observation.
                </p>
            </div>
            <div class="feature-card" style="--index: 5">
                <div class="feature-icon">🎯</div>
                <h3 class="feature-title">Planification Intelligente</h3>
                <p class="feature-description">
                    Notre système vous aide à planifier vos observations
                    en fonction des conditions météo et astronomiques.
                </p>
            </div>
        </div>
    </section>

    <!-- Equipment Section -->
    <section class="section" id="equipment">
        <h2 class="section-title">Notre Flotte d'Équipements</h2>
        <div class="equipment-showcase">
            <div class="equipment-card" style="--index: 0">
                <div class="equipment-info">
                    <h3 class="equipment-name">Celestron CGX-L 1400</h3>
                    <p class="equipment-specs">
                        Schmidt-Cassegrain 14" • f/11 • 3910mm<br>
                        Idéal pour l'observation planétaire
                    </p>
                </div>
            </div>
            <div class="equipment-card" style="--index: 1">
                <div class="equipment-info">
                    <h3 class="equipment-name">PlaneWave CDK24</h3>
                    <p class="equipment-specs">
                        Corrected Dall-Kirkham 24" • f/6.5<br>
                        Parfait pour le ciel profond
                    </p>
                </div>
            </div>
            <div class="equipment-card" style="--index: 2">
                <div class="equipment-info">
                    <h3 class="equipment-name">Takahashi FSQ-106</h3>
                    <p class="equipment-specs">
                        Réfracteur apochromatique • f/5<br>
                        Excellence en astrophotographie grand champ
                    </p>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Loader
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('loader').classList.add('hidden');
            }, 1500);
        });

        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Generate stars
        const starsContainer = document.getElementById('stars');
        for (let i = 0; i < 100; i++) {
            const star = document.createElement('div');
            star.className = 'star';
            star.style.width = Math.random() * 3 + 'px';
            star.style.height = star.style.width;
            star.style.left = Math.random() * 100 + '%';
            star.style.top = Math.random() * 100 + '%';
            star.style.animationDelay = Math.random() * 3 + 's';
            starsContainer.appendChild(star);
        }

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        // Observe elements
        document.querySelectorAll('.section-title, .feature-card, .equipment-card').forEach(el => {
            observer.observe(el);
        });

        // Parallax effect
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallaxElements = document.querySelectorAll('.parallax-element');

            parallaxElements.forEach(element => {
                const speed = element.dataset.speed || 0.5;
                element.style.transform = `translateY(${scrolled * speed}px)`;
            });
        });

        // Smooth scroll for navigation links
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

        // Mouse move effect for hero
        document.addEventListener('mousemove', (e) => {
            const mouseX = e.clientX / window.innerWidth - 0.5;
            const mouseY = e.clientY / window.innerHeight - 0.5;

            const heroTitle = document.querySelector('.hero-title');
            if (heroTitle) {
                heroTitle.style.transform = `translate(${mouseX * 20}px, ${mouseY * 20}px)`;
            }
        });
    </script>
</body>
</html>
