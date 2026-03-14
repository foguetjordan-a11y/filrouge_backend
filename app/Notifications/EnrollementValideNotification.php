<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Enrollement;

class EnrollementValideNotification extends Notification
{
    use Queueable;

    protected $enrollement;

    public function __construct(Enrollement $enrollement)
    {
        $this->enrollement = $enrollement;
    }

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
            ->subject('Enrôlement validé avec succès')
            ->greeting('Félicitations ' . $notifiable->name . ' !')
            ->line('Votre demande d\'enrôlement a été validée avec succès.')
            ->line('**Détails de votre enrôlement :**')
            ->line('Filière : ' . $this->enrollement->filiere->nom)
            ->line('Niveau : ' . $this->enrollement->niveau->nom)
            ->line('Département : ' . $this->enrollement->filiere->departement->nom)
            ->line('Année académique : ' . $this->enrollement->anneeAcademique->annee)
            ->line('Votre quitus académique a été généré et est disponible au téléchargement.')
            ->action('Télécharger mon quitus', url('/etudiant/dashboard'))
            ->line('Bienvenue dans notre établissement !');
    }

    /**
     * Données pour la base de données
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'Enrôlement validé',
            'message' => 'Votre enrôlement en ' . $this->enrollement->filiere->nom . ' a été validé avec succès.',
            'type' => 'enrollement_valide',
            'enrollement_id' => $this->enrollement->id,
            'filiere' => $this->enrollement->filiere->nom,
            'niveau' => $this->enrollement->niveau->nom,
            'action_url' => '/etudiant/dashboard'
        ];
    }
}
