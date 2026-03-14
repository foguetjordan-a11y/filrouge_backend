<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaiementConfirmeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('✅ Paiement confirmé - Frais d\'enrôlement')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Nous avons le plaisir de vous confirmer que votre paiement a été reçu et traité avec succès.')
            ->line('**Détails du paiement :**')
            ->line('• Référence : ' . $this->payment->payment_reference)
            ->line('• Montant : ' . number_format($this->payment->amount, 0, ',', ' ') . ' FCFA')
            ->line('• Méthode : ' . $this->payment->paymentMethod->name)
            ->line('• Date : ' . $this->payment->completed_at->format('d/m/Y à H:i'))
            ->line('• Transaction : ' . ($this->payment->transaction_id ?? 'N/A'))
            ->line('')
            ->line('Votre enrôlement est maintenant confirmé. Vous pouvez télécharger votre quitus depuis votre espace étudiant.')
            ->action('Accéder à mon espace', url('/dashboard'))
            ->line('Conservez ce reçu comme preuve de paiement.')
            ->salutation('Cordialement,<br>L\'équipe administrative');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'paiement_confirme',
            'title' => 'Paiement confirmé',
            'message' => 'Votre paiement de ' . number_format($this->payment->amount, 0, ',', ' ') . ' FCFA a été confirmé avec succès.',
            'payment_id' => $this->payment->id,
            'payment_reference' => $this->payment->payment_reference,
            'amount' => $this->payment->amount,
            'transaction_id' => $this->payment->transaction_id,
            'action_url' => '/dashboard',
            'icon' => 'check-circle',
            'color' => 'green'
        ];
    }
}