<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de Paiement {{ $payment->payment_reference }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #059669;
            padding-bottom: 20px;
        }
        
        .university-name {
            font-size: 24px;
            font-weight: bold;
            color: #059669;
            margin-bottom: 5px;
        }
        
        .university-info {
            font-size: 11px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .document-title {
            font-size: 20px;
            font-weight: bold;
            color: #1f2937;
            margin-top: 15px;
        }
        
        .receipt-info {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .receipt-info-left,
        .receipt-info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .receipt-info-right {
            text-align: right;
        }
        
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-title {
            font-weight: bold;
            color: #374151;
            margin-bottom: 8px;
            font-size: 13px;
        }
        
        .info-content {
            font-size: 12px;
            line-height: 1.5;
        }
        
        .payment-reference {
            font-size: 16px;
            font-weight: bold;
            color: #059669;
        }
        
        .success-badge {
            display: inline-block;
            padding: 6px 15px;
            background-color: #dcfce7;
            color: #166534;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .payment-details {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #f9fafb;
        }
        
        .payment-details th,
        .payment-details td {
            border: 1px solid #d1d5db;
            padding: 12px;
            text-align: left;
        }
        
        .payment-details th {
            background-color: #f3f4f6;
            font-weight: bold;
            font-size: 11px;
        }
        
        .payment-details td {
            font-size: 12px;
        }
        
        .amount-section {
            width: 350px;
            margin: 30px auto;
            padding: 20px;
            background-color: #ecfdf5;
            border: 2px solid #059669;
            border-radius: 8px;
            text-align: center;
        }
        
        .amount-label {
            font-size: 14px;
            color: #374151;
            margin-bottom: 10px;
        }
        
        .amount-value {
            font-size: 28px;
            font-weight: bold;
            color: #059669;
            margin-bottom: 5px;
        }
        
        .amount-currency {
            font-size: 14px;
            color: #6b7280;
        }
        
        .transaction-info {
            margin: 30px 0;
            padding: 15px;
            background-color: #f8fafc;
            border-left: 4px solid #059669;
        }
        
        .transaction-info h4 {
            margin: 0 0 10px 0;
            color: #059669;
            font-size: 14px;
        }
        
        .qr-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background-color: #f9fafb;
            border-radius: 8px;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        
        .signature-left,
        .signature-right {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        
        .signature-box {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="university-name">UNIVERSITÉ VIRTUELLE DU CAMEROUN</div>
        <div class="university-info">
            Campus Principal - Yaoundé, Cameroun<br>
            Tél: +237 6 XX XX XX XX | Email: finance@universite-cameroun.cm
        </div>
        <div class="document-title">REÇU DE PAIEMENT</div>
        <div style="margin-top: 15px;">
            <span class="success-badge">✓ PAIEMENT CONFIRMÉ</span>
        </div>
    </div>

    <div class="receipt-info">
        <div class="receipt-info-left">
            <div class="info-section">
                <div class="info-title">PAYÉ PAR :</div>
                <div class="info-content">
                    <strong>{{ $payment->user->name }}</strong><br>
                    Email: {{ $payment->user->email }}<br>
                    @if($payment->user->telephone)
                        Téléphone: {{ $payment->user->telephone }}<br>
                    @endif
                    Étudiant(e)
                </div>
            </div>
            
            <div class="info-section">
                <div class="info-title">FORMATION :</div>
                <div class="info-content">
                    <strong>{{ $payment->enrollement->filiere->nom }}</strong><br>
                    @if($payment->enrollement->niveau)
                        Niveau: {{ $payment->enrollement->niveau->nom }}<br>
                    @endif
                    Département: {{ $payment->enrollement->filiere->departement->nom }}<br>
                    Année académique: {{ $payment->enrollement->academic_year ?? date('Y') . '-' . (date('Y') + 1) }}
                </div>
            </div>
        </div>
        
        <div class="receipt-info-right">
            <div class="info-section">
                <div class="info-title">RÉFÉRENCE PAIEMENT :</div>
                <div class="payment-reference">{{ $payment->payment_reference }}</div>
            </div>
            
            <div class="info-section">
                <div class="info-title">DATE DE PAIEMENT :</div>
                <div class="info-content">{{ $payment->completed_at->format('d/m/Y à H:i') }}</div>
            </div>
            
            <div class="info-section">
                <div class="info-title">MÉTHODE DE PAIEMENT :</div>
                <div class="info-content">{{ $payment->paymentMethod->name }}</div>
            </div>
            
            @if($payment->transaction_id)
            <div class="info-section">
                <div class="info-title">ID TRANSACTION :</div>
                <div class="info-content">{{ $payment->transaction_id }}</div>
            </div>
            @endif
        </div>
    </div>

    <div class="amount-section">
        <div class="amount-label">MONTANT PAYÉ</div>
        <div class="amount-value">{{ number_format($payment->amount, 0, ',', ' ') }}</div>
        <div class="amount-currency">Francs CFA (FCFA)</div>
    </div>

    <table class="payment-details">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: right;">Montant</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Montant principal</td>
                <td style="text-align: right;">{{ number_format($payment->net_amount, 0, ',', ' ') }} FCFA</td>
            </tr>
            @if($payment->fee_amount > 0)
            <tr>
                <td>Frais de traitement</td>
                <td style="text-align: right;">{{ number_format($payment->fee_amount, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
            <tr style="background-color: #ecfdf5; font-weight: bold;">
                <td>TOTAL PAYÉ</td>
                <td style="text-align: right;">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td>
            </tr>
        </tbody>
    </table>

    @if($payment->invoice)
    <div class="transaction-info">
        <h4>FACTURE ASSOCIÉE</h4>
        <p><strong>Numéro de facture :</strong> {{ $payment->invoice->invoice_number }}</p>
        <p><strong>Description :</strong> {{ $payment->invoice->title }}</p>
        <p><strong>Statut :</strong> Payée intégralement</p>
    </div>
    @endif

    <div class="transaction-info">
        <h4>INFORMATIONS IMPORTANTES</h4>
        <p>• Ce reçu confirme que votre paiement a été reçu et traité avec succès.</p>
        <p>• Conservez ce document comme preuve de paiement.</p>
        <p>• Votre enrôlement est maintenant confirmé et vous pouvez procéder aux étapes suivantes.</p>
        <p>• En cas de questions, contactez le service financier avec la référence : <strong>{{ $payment->payment_reference }}</strong></p>
    </div>

    <div class="qr-section">
        <p><strong>Vérification en ligne</strong></p>
        <p>Scannez ce code ou visitez notre site pour vérifier l'authenticité de ce reçu</p>
        <p style="font-family: monospace; font-size: 10px; margin-top: 10px;">
            Référence: {{ $payment->payment_reference }}
        </p>
    </div>

    <div class="signature-section">
        <div class="signature-left">
            <div class="signature-box">
                Service Financier<br>
                Université Virtuelle
            </div>
        </div>
        <div class="signature-right">
            <div class="signature-box">
                Signature et cachet
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Ce reçu a été généré automatiquement le {{ $generated_at->format('d/m/Y à H:i') }}</p>
        <p>Document officiel - Université Virtuelle du Cameroun - Service Financier</p>
        <p>Pour toute question : finance@universite-cameroun.cm | +237 6 XX XX XX XX</p>
    </div>
</body>
</html>