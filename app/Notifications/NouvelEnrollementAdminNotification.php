<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Enrollement;

class NouvelEnrollementAdminNotification extends Notification
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
            ->subject('Nouvelle demande d\'enrôlement')
            ->greeting('Bonjour,')
            ->line('Une nouvelle demande d\'enrôlement a été soumise.')
            ->line('**Détails de la demande :**')
            ->line('Étudiant : ' . $this->enrollement->user->name)
            ->line('Email : ' . $this->enrollement->user->email)
            ->line('Filière : ' . $this->enrollement->filiere->nom)
            ->line('Niveau : ' . $this->enrollement->niveau->nom)
            ->line('Département : ' . $this->enrollement->filiere->departement->nom)
            ->line('Date de soumission : ' . $this->enrollement->created_at->format('d/m/Y à H:i'))
            ->line('Cette demande nécessite votre validation.')
            ->action('Consulter le dashboard admin', url('/admin/dashboard'))
            ->line('Merci de traiter cette demande dans les meilleurs délais.');
    }

    /**
     * Données pour la base de données
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'Nouvelle demande d\'enrôlement',
            'message' => 'Nouvelle demande de ' . $this->enrollement->user->name . ' en ' . $this->enrollement->filiere->nom,
            'type' => 'nouvel_enrollement_admin',
            'enrollement_id' => $this->enrollement->id,
            'etudiant_name' => $this->enrollement->user->name,
            'etudiant_email' => $this->enrollement->user->email,
            'filiere' => $this->enrollement->filiere->nom,
            'niveau' => $this->enrollement->niveau->nom,
            'departement' => $this->enrollement->filiere->departement->nom,
            'action_url' => '/admin/dashboard'
        ];
    }
}