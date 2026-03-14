<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Enrollement;

class EnrollementSoumisNotification extends Notification
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
            ->subject('Enrôlement soumis avec succès')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Votre demande d\'enrôlement a été soumise avec succès.')
            ->line('**Détails de votre enrôlement :**')
            ->line('Filière : ' . $this->enrollement->filiere->nom)
            ->line('Niveau : ' . $this->enrollement->niveau->nom)
            ->line('Année académique : ' . $this->enrollement->anneeAcademique->annee)
            ->line('Votre demande est en cours d\'examen par l\'administration.')
            ->line('Vous recevrez une notification dès que votre enrôlement sera traité.')
            ->action('Consulter mon dashboard', url('/etudiant/dashboard'))
            ->line('Merci de votre confiance !');
    }

    /**
     * Données pour la base de données
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'Enrôlement soumis',
            'message' => 'Votre demande d\'enrôlement en ' . $this->enrollement->filiere->nom . ' a été soumise avec succès.',
            'type' => 'enrollement_soumis',
            'enrollement_id' => $this->enrollement->id,
            'filiere' => $this->enrollement->filiere->nom,
            'niveau' => $this->enrollement->niveau->nom,
            'action_url' => '/etudiant/dashboard'
        ];
    }
}