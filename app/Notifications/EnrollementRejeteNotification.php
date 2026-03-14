<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Enrollement;

class EnrollementRejeteNotification extends Notification
{
    use Queueable;

    protected $enrollement;
    protected $motifRejet;

    public function __construct(Enrollement $enrollement, string $motifRejet = null)
    {
        $this->enrollement = $enrollement;
        $this->motifRejet = $motifRejet;
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
        $mail = (new MailMessage)
            ->subject('Enrôlement rejeté')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Nous regrettons de vous informer que votre demande d\'enrôlement a été rejetée.')
            ->line('**Détails de votre demande :**')
            ->line('Filière : ' . $this->enrollement->filiere->nom)
            ->line('Niveau : ' . $this->enrollement->niveau->nom)
            ->line('Année académique : ' . $this->enrollement->anneeAcademique->annee);

        if ($this->motifRejet) {
            $mail->line('**Motif du rejet :** ' . $this->motifRejet);
        }

        return $mail
            ->line('Vous pouvez soumettre une nouvelle demande après avoir corrigé les éléments mentionnés.')
            ->action('Consulter mon dashboard', url('/etudiant/dashboard'))
            ->line('Pour toute question, n\'hésitez pas à contacter l\'administration.');
    }

    /**
     * Données pour la base de données
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'Enrôlement rejeté',
            'message' => 'Votre demande d\'enrôlement en ' . $this->enrollement->filiere->nom . ' a été rejetée.',
            'type' => 'enrollement_rejete',
            'enrollement_id' => $this->enrollement->id,
            'filiere' => $this->enrollement->filiere->nom,
            'niveau' => $this->enrollement->niveau->nom,
            'motif_rejet' => $this->motifRejet,
            'action_url' => '/etudiant/dashboard'
        ];
    }
}