<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre target est active</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
            -webkit-font-smoothing: antialiased;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
        }
        .header {
            text-align: center;
            padding: 40px 20px 30px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(168, 85, 247, 0.1) 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .logo {
            display: inline-block;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 50%, #ec4899 100%);
            border-radius: 12px;
            text-align: center;
            line-height: 60px;
            font-size: 32px;
            font-weight: 900;
            color: white;
            margin-bottom: 15px;
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
        }
        .header-title {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            margin: 0 0 8px 0;
            letter-spacing: -0.5px;
        }
        .header-subtitle {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #ffffff;
            margin: 0 0 25px 0;
            font-weight: 600;
        }
        .hero-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 30px;
            margin: 0 0 30px 0;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        .hero-icon {
            font-size: 48px;
            text-align: center;
            margin-bottom: 20px;
        }
        .hero-title {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
            text-align: center;
            margin: 0 0 10px 0;
        }
        .hero-subtitle {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.7);
            text-align: center;
            margin: 0 0 25px 0;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin: 0 0 30px 0;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
            padding: 8px 15px 8px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .info-value {
            display: table-cell;
            font-size: 15px;
            color: #ffffff;
            padding: 8px 0;
            font-weight: 500;
        }
        .cta-button {
            display: block;
            width: 100%;
            max-width: 400px;
            margin: 30px auto;
            padding: 16px 32px;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 50%, #ec4899 100%);
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.4);
            transition: transform 0.2s ease;
        }
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.6);
        }
        .message {
            font-size: 15px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.8);
            margin: 0 0 20px 0;
        }
        .footer {
            padding: 30px;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .footer-text {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
            margin: 0 0 15px 0;
            line-height: 1.5;
        }
        .footer-link {
            color: #a855f7;
            text-decoration: none;
        }
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.1) 50%, transparent 100%);
            margin: 25px 0;
        }
        @media only screen and (max-width: 600px) {
            .content {
                padding: 30px 20px;
            }
            .hero-card {
                padding: 20px;
            }
            .header-title {
                font-size: 24px;
            }
            .hero-title {
                font-size: 20px;
            }
            .info-label, .info-value {
                display: block;
                padding: 4px 0;
            }
            .info-label {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="logo">S</div>
            <h1 class="header-title">STELLARLOC</h1>
            <p class="header-subtitle">Astral Observatory</p>
        </div>

        <!-- Content -->
        <div class="content">
            <p class="greeting">Bonjour {{ $user->name }},</p>

            <!-- Hero Card -->
            <div class="hero-card">
                <div class="hero-icon">🔭</div>
                <h2 class="hero-title">Votre target est en cours d'acquisition !</h2>
                <p class="hero-subtitle">Le télescope a commencé à capturer vos images</p>

                <!-- Target Info -->
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Target</div>
                        <div class="info-value">{{ $target->target_name }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Début</div>
                        <div class="info-value">{{ $session->session_start?->format('d/m/Y à H:i') ?? 'Maintenant' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Durée estimée</div>
                        <div class="info-value">{{ $target->getFormattedDuration() }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Coordonnées</div>
                        <div class="info-value">RA: {{ $target->ra_j2000 }} • DEC: {{ $target->dec_j2000 }}</div>
                    </div>
                </div>

                <!-- CTA Button -->
                <a href="{{ route('robotarget.monitor', ['locale' => app()->getLocale(), 'guid' => $target->guid]) }}" class="cta-button">
                    🌌 Voir le monitoring en direct →
                </a>
            </div>

            <div class="divider"></div>

            <!-- Additional Info -->
            <p class="message">
                <strong>Que se passe-t-il maintenant ?</strong><br>
                Le télescope est en train de pointer votre target et va commencer à capturer les images selon votre séquence configurée. Vous pouvez suivre la progression en temps réel depuis votre dashboard.
            </p>

            <p class="message">
                Vous recevrez une notification automatique une fois la session terminée avec un résumé complet des images capturées.
            </p>

            <p class="message" style="margin-top: 30px; color: rgba(255, 255, 255, 0.9); font-weight: 500;">
                Bonnes observations ! 🌟
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="footer-text">
                Vous recevez cet email car votre target sur <a href="{{ url('/') }}" class="footer-link">STELLARLOC</a> est maintenant active.
            </p>
            <p class="footer-text">
                © {{ date('Y') }} STELLARLOC - Observatoire Astral<br>
                <a href="{{ route('dashboard', ['locale' => app()->getLocale()]) }}" class="footer-link">Dashboard</a> •
                <a href="{{ route('robotarget.index', ['locale' => app()->getLocale()]) }}" class="footer-link">Mes Targets</a>
            </p>
        </div>
    </div>
</body>
</html>
