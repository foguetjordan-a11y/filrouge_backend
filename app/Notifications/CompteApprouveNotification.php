<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompteApprouveNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Canaux de notification
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Message email
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Compte étudiant approuvé')
            ->greeting('Félicitations ' . $notifiable->name . ' !')
            ->line('Votre compte étudiant a été approuvé par l\'administration.')
            ->line('Vous pouvez maintenant accéder à votre espace étudiant et soumettre vos demandes d\'enrôlement.')
            ->line('**Prochaines étapes :**')
            ->line('1. Connectez-vous à votre espace étudiant')
            ->line('2. Complétez votre profil si nécessaire')
            ->line('3. Soumettez votre demande d\'enrôlement')
            ->action('Accéder à mon espace', url('/student/dashboard'))
            ->line('Bienvenue dans notre établissement !');
    }

    /**
     * Données pour la base de données
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'Compte approuvé',
            'message' => 'Votre compte étudiant a été approuvé par l\'administration.',
            'type' => 'compte_approuve',
            'action_url' => '/student/dashboard'
        ];
    }
}