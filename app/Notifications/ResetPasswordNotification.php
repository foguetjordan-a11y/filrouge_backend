<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The password reset token.
     */
    public $token;

    /**
     * The callback that should be used to create the reset password URL.
     */
    public static $createUrlCallback;

    /**
     * The callback that should be used to build the mail message.
     */
    public static $toMailCallback;

    /**
     * Create a notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        return $this->buildMailMessage($this->resetUrl($notifiable));
    }

    /**
     * Get the reset password URL for the given notifiable.
     */
    protected function resetUrl($notifiable)
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        }

        // URL du frontend pour la réinitialisation
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        
        return $frontendUrl . '/reset-password?' . http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }

    /**
     * Build the mail message.
     */
    protected function buildMailMessage($url)
    {
        $appName = config('app.name', 'Système d\'Enrôlement');
        
        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe - ' . $appName)
            ->greeting('Bonjour !')
            ->line('Vous recevez cet email car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.')
            ->action('Réinitialiser le mot de passe', $url)
            ->line('Ce lien de réinitialisation expirera dans ' . config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60) . ' minutes.')
            ->line('Si vous n\'avez pas demandé de réinitialisation de mot de passe, aucune action n\'est requise de votre part.')
            ->line('Pour votre sécurité, ne partagez jamais ce lien avec personne.')
            ->salutation('Cordialement, L\'équipe ' . $appName)
            ->view('emails.reset-password', [
                'url' => $url,
                'user' => $this->getNotifiableName($this->notifiable ?? null),
                'appName' => $appName,
                'expireMinutes' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60)
            ]);
    }

    /**
     * Get the user's display name
     */
    protected function getNotifiableName($notifiable)
    {
        if (!$notifiable) return 'Utilisateur';
        
        if (isset($notifiable->name) && $notifiable->name) {
            return $notifiable->name;
        }
        
        if (isset($notifiable->prenom) && isset($notifiable->nom)) {
            return trim($notifiable->prenom . ' ' . $notifiable->nom);
        }
        
        return 'Utilisateur';
    }

    /**
     * Set a callback that should be used when creating the reset password button URL.
     */
    public static function createUrlUsing($callback)
    {
        static::$createUrlCallback = $callback;
    }

    /**
     * Set a callback that should be used when building the notification mail message.
     */
    public static function toMailUsing($callback)
    {
        static::$toMailCallback = $callback;
    }
}