<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            text-align: center;
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .header p {
            margin: 10px 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px 20px;
        }
        .status-card {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: 600;
            color: #666;
        }
        .value {
            color: #333;
            font-weight: 500;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
        .emoji {
            font-size: 48px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="emoji">🔭</div>
            <h1>Session Démarrée !</h1>
            <p>Votre cible est maintenant en cours d'observation</p>
        </div>

        <div class="content">
            <p>Bonjour {{ $session->roboTarget->user->name }},</p>

            <p>Votre session d'observation pour la cible <strong>{{ $session->roboTarget->target_name }}</strong> vient de démarrer !</p>

            <div class="status-card">
                <div class="info-row">
                    <span class="label">🎯 Cible</span>
                    <span class="value">{{ $session->roboTarget->target_name }}</span>
                </div>
                <div class="info-row">
                    <span class="label">📍 Coordonnées</span>
                    <span class="value">RA: {{ $session->roboTarget->ra_j2000 }} / DEC: {{ $session->roboTarget->dec_j2000 }}</span>
                </div>
                <div class="info-row">
                    <span class="label">⏰ Démarrage</span>
                    <span class="value">{{ $session->session_start->format('d/m/Y à H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">🔢 Session ID</span>
                    <span class="value">{{ $session->session_guid }}</span>
                </div>
            </div>

            <p style="text-align: center;">
                <a href="{{ route('robotarget.show', ['locale' => 'fr', 'guid' => $session->roboTarget->guid]) }}?monitor=true" class="cta-button">
                    📡 Suivre en Direct
                </a>
            </p>

            <p style="background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;">
                <strong>💡 Astuce :</strong> Cliquez sur le bouton ci-dessus pour accéder au monitoring en temps réel et voir vos images au fur et à mesure qu'elles sont capturées !
            </p>

            <p style="font-size: 14px; color: #666; margin-top: 30px;">
                Vous recevrez une autre notification lorsque la session sera terminée avec un résumé complet.
            </p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} Stellar - Système RoboTarget</p>
            <p style="font-size: 12px; color: #999;">
                Cet email a été envoyé automatiquement. Vous pouvez désactiver ces notifications dans vos paramètres.
            </p>
        </div>
    </div>
</body>
</html>
