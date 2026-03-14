<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafc;
        }
        .container {
            background-color: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .logo svg {
            width: 30px;
            height: 30px;
            color: white;
        }
        h1 {
            color: #1f2937;
            font-size: 24px;
            margin: 0 0 10px 0;
            font-weight: 600;
        }
        .subtitle {
            color: #6b7280;
            font-size: 16px;
            margin: 0;
        }
        .content {
            margin: 30px 0;
        }
        .greeting {
            font-size: 18px;
            font-weight: 500;
            color: #1f2937;
            margin-bottom: 20px;
        }
        .message {
            color: #4b5563;
            margin-bottom: 20px;
            line-height: 1.7;
        }
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        .reset-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
        }
        .warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 16px;
            margin: 30px 0;
        }
        .warning-title {
            color: #92400e;
            font-weight: 600;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }
        .warning-text {
            color: #92400e;
            font-size: 14px;
            line-height: 1.5;
        }
        .footer {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }
        .footer-text {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .security-note {
            background-color: #f3f4f6;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        .security-title {
            color: #374151;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .security-text {
            color: #4b5563;
            font-size: 14px;
            line-height: 1.6;
        }
        .expiry-info {
            background-color: #eff6ff;
            border: 1px solid #3b82f6;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
            text-align: center;
        }
        .expiry-text {
            color: #1d4ed8;
            font-size: 14px;
            font-weight: 500;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .container {
                padding: 20px;
            }
            h1 {
                font-size: 20px;
            }
            .reset-button {
                padding: 14px 24px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                <svg fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <h1>Réinitialisation de mot de passe</h1>
            <p class="subtitle">{{ $appName }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">Bonjour {{ $user }} !</div>
            
            <div class="message">
                Vous recevez cet email car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte sur <strong>{{ $appName }}</strong>.
            </div>

            <div class="message">
                Pour créer un nouveau mot de passe, cliquez sur le bouton ci-dessous :
            </div>

            <!-- Reset Button -->
            <div class="button-container">
                <a href="{{ $url }}" class="reset-button">
                    Réinitialiser mon mot de passe
                </a>
            </div>

            <!-- Expiry Info -->
            <div class="expiry-info">
                <div class="expiry-text">
                    ⏰ Ce lien expirera dans {{ $expireMinutes }} minutes
                </div>
            </div>

            <!-- Warning -->
            <div class="warning">
                <div class="warning-title">
                    ⚠️ Important
                </div>
                <div class="warning-text">
                    Si vous n'avez pas demandé cette réinitialisation, ignorez simplement cet email. Votre mot de passe actuel restera inchangé.
                </div>
            </div>

            <!-- Security Note -->
            <div class="security-note">
                <div class="security-title">
                    🔒 Note de sécurité
                </div>
                <div class="security-text">
                    • Ce lien ne peut être utilisé qu'une seule fois<br>
                    • Ne partagez jamais ce lien avec personne<br>
                    • Si le lien ne fonctionne pas, copiez-collez l'URL complète dans votre navigateur<br>
                    • En cas de problème, contactez notre support technique
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-text">
                Cet email a été envoyé automatiquement, merci de ne pas y répondre.
            </div>
            <div class="footer-text">
                <strong>{{ $appName }}</strong> - Système d'enrôlement académique
            </div>
        </div>
    </div>

    <!-- Fallback URL -->
    <div style="margin-top: 30px; padding: 20px; background-color: #f9fafb; border-radius: 8px; font-size: 12px; color: #6b7280;">
        <strong>Le bouton ne fonctionne pas ?</strong><br>
        Copiez et collez ce lien dans votre navigateur :<br>
        <a href="{{ $url }}" style="color: #3b82f6; word-break: break-all;">{{ $url }}</a>
    </div>
</body>
</html>