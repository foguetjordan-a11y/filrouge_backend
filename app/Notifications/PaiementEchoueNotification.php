<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaiementEchoueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payment;
    protected $reason;

    public function __construct(Payment $payment, $reason = null)
    {
        $this->payment = $payment;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('❌ Échec de paiement - Frais d\'enrôlement')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Nous vous informons que votre tentative de paiement a échoué.')
            ->line('**Détails du paiement :**')
            ->line('• Référence : ' . $this->payment->payment_reference)
            ->line('• Montant : ' . number_format($this->payment->amount, 0, ',', ' ') . ' FCFA')
            ->line('• Méthode : ' . $this->payment->paymentMethod->name)
            ->line('• Date : ' . $this->payment->created_at->format('d/m/Y à H:i'));

        if ($this->reason) {
            $mail->line('• Raison : ' . $this->reason);
        }

        return $mail
            ->line('')
            ->line('Vous pouvez réessayer le paiement ou choisir une autre méthode de paiement.')
            ->action('Réessayer le paiement', url('/dashboard'))
            ->line('Si le problème persiste, contactez notre service financier.')
            ->salutation('Cordialement,<br>L\'équipe administrative');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'paiement_echoue',
            'title' => 'Paiement échoué',
            'message' => 'Votre paiement de ' . number_format($this->payment->amount, 0, ',', ' ') . ' FCFA a échoué.' . ($this->reason ? ' Raison : ' . $this->reason : ''),
            'payment_id' => $this->payment->id,
            'payment_reference' => $this->payment->payment_reference,
            'amount' => $this->payment->amount,
            'reason' => $this->reason,
            'action_url' => '/dashboard',
            'icon' => 'x-circle',
            'color' => 'red'
        ];
    }
}