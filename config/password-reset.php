<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration de la réinitialisation de mot de passe
    |--------------------------------------------------------------------------
    |
    | Configuration spécifique pour le système de réinitialisation de mot de passe
    | de l'application d'enrôlement académique.
    |
    */

    // URL du frontend pour les liens de réinitialisation
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),

    // Durée de validité des tokens (en minutes)
    'token_expire' => env('AUTH_PASSWORD_RESET_TOKEN_EXPIRE', 60),

    // Configuration des emails
    'email' => [
        'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
        'from_name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Système d\'Enrôlement')),
        'subject' => 'Réinitialisation de votre mot de passe',
    ],

    // Messages de réponse
    'messages' => [
        'sent' => 'Un lien de réinitialisation a été envoyé à votre adresse email. Vérifiez votre boîte de réception et vos spams.',
        'reset' => 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.',
        'token_invalid' => 'Le lien de réinitialisation est invalide ou a expiré. Veuillez demander un nouveau lien.',
        'user_not_found' => 'Aucun compte n\'est associé à cette adresse email',
        'account_disabled' => 'Ce compte a été désactivé. Contactez l\'administration.',
        'throttled' => 'Trop de tentatives. Veuillez réessayer dans quelques minutes.',
    ],

    // Règles de validation pour les mots de passe
    'password_rules' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => true,
    ],

    // Limitation du nombre de tentatives
    'throttle' => [
        'max_attempts' => 5, // Nombre maximum de tentatives
        'decay_minutes' => 60, // Durée de blocage en minutes
    ],
];