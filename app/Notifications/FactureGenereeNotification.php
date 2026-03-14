<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FactureGenereeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('📄 Facture générée - Frais d\'enrôlement')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Votre facture d\'enrôlement a été générée avec succès.')
            ->line('**Détails de la facture :**')
            ->line('• Numéro : ' . $this->invoice->invoice_number)
            ->line('• Montant : ' . number_format($this->invoice->total_amount, 0, ',', ' ') . ' FCFA')
            ->line('• Date d\'échéance : ' . $this->invoice->due_date->format('d/m/Y'))
            ->line('• Formation : ' . $this->invoice->enrollement->filiere->nom)
            ->line('')
            ->line('Vous pouvez maintenant procéder au paiement de vos frais d\'enrôlement.')
            ->action('Voir la facture', url('/dashboard'))
            ->line('Merci de procéder au paiement avant la date d\'échéance.')
            ->salutation('Cordialement,<br>L\'équipe administrative');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'facture_generee',
            'title' => 'Facture générée',
            'message' => 'Votre facture d\'enrôlement a été générée. Montant : ' . number_format($this->invoice->total_amount, 0, ',', ' ') . ' FCFA',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => $this->invoice->total_amount,
            'due_date' => $this->invoice->due_date,
            'action_url' => '/dashboard',
            'icon' => 'receipt',
            'color' => 'blue'
        ];
    }
}