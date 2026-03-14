<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Quitus;

class QuitusDisponibleNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $quitus;

    public function __construct(Quitus $quitus)
    {
        $this->quitus = $quitus;
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
            ->subject('Quitus académique disponible')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Votre quitus académique est maintenant disponible.')
            ->line('**Détails du quitus :**')
            ->line('Filière : ' . $this->quitus->enrollement->filiere->nom)
            ->line('Niveau : ' . $this->quitus->enrollement->niveau->nom)
            ->line('Année académique : ' . $this->quitus->enrollement->anneeAcademique->annee)
            ->line('Date de génération : ' . $this->quitus->created_at->format('d/m/Y'))
            ->line('Vous pouvez maintenant télécharger votre quitus depuis votre dashboard.')
            ->action('Télécharger mon quitus', url('/etudiant/dashboard'))
            ->line('Conservez précieusement ce document officiel.');
    }

    /**
     * Données pour la base de données
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'Quitus disponible',
            'message' => 'Votre quitus académique est maintenant disponible en téléchargement.',
            'type' => 'quitus_disponible',
            'quitus_id' => $this->quitus->id,
            'enrollement_id' => $this->quitus->enrollement_id,
            'filiere' => $this->quitus->enrollement->filiere->nom,
            'action_url' => '/etudiant/dashboard'
        ];
    }
}
