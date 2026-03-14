<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu Détaillé {{ $payment->payment_reference }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #059669;
            padding-bottom: 15px;
        }
        
        .university-name {
            font-size: 22px;
            font-weight: bold;
            color: #059669;
            margin-bottom: 5px;
        }
        
        .university-info {
            font-size: 10px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .document-title {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            margin-top: 10px;
        }
        
        .document-subtitle {
            font-size: 12px;
            color: #6b7280;
            margin-top: 5px;
        }
        
        .receipt-header {
            display: table;
            width: 100%;
            margin-bottom: 25px;
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 8px;
        }
        
        .receipt-header-left,
        .receipt-header-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .receipt-header-right {
            text-align: right;
        }
        
        .info-section {
            margin-bottom: 15px;
        }
        
        .info-title {
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .info-content {
            font-size: 11px;
            line-height: 1.4;
        }
        
        .payment-reference {
            font-size: 14px;
            font-weight: bold;
            color: #059669;
            font-family: monospace;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            background-color: #dcfce7;
            color: #166534;
            border-radius: 15px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .main-content {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .left-column,
        .right-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }
        
        .amount-highlight {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            border-radius: 10px;
        }
        
        .amount-label {
            font-size: 12px;
            margin-bottom: 8px;
            opacity: 0.9;
        }
        
        .amount-value {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .amount-currency {
            font-size: 12px;
            opacity: 0.8;
        }
        
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10px;
        }
        
        .details-table th {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        
        .details-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
        }
        
        .details-table .text-right {
            text-align: right;
        }
        
        .verification-section {
            margin: 20px 0;
            padding: 15px;
            background-color: #eff6ff;
            border: 2px solid #3b82f6;
            border-radius: 8px;
            text-align: center;
        }
        
        .verification-title {
            font-size: 12px;
            font-weight: bold;
            color: #1d4ed8;
            margin-bottom: 10px;
        }
        
        .qr-placeholder {
            width: 80px;
            height: 80px;
            background-color: #ddd;
            margin: 10px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #666;
        }
        
        .verification-url {
            font-size: 9px;
            color: #1d4ed8;
            word-break: break-all;
            margin-top: 5px;
        }
        
        .transaction-details {
            margin: 20px 0;
            padding: 15px;
            background-color: #fefce8;
            border-left: 4px solid #eab308;
        }
        
        .transaction-details h4 {
            margin: 0 0 10px 0;
            color: #a16207;
            font-size: 12px;
        }
        
        .transaction-grid {
            display: table;
            width: 100%;
        }
        
        .transaction-row {
            display: table-row;
        }
        
        .transaction-label,
        .transaction-value {
            display: table-cell;
            padding: 3px 0;
            font-size: 10px;
        }
        
        .transaction-label {
            font-weight: bold;
            width: 40%;
        }
        
        .security-features {
            margin: 20px 0;
            padding: 15px;
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
        }
        
        .security-title {
            font-size: 11px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 8px;
        }
        
        .security-list {
            font-size: 9px;
            color: #7f1d1d;
            line-height: 1.5;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        .signature-section {
            margin-top: 30px;
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
            margin-top: 40px;
            padding-top: 5px;
            font-size: 9px;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            color: rgba(5, 150, 105, 0.1);
            z-index: -1;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="watermark">PAYÉ</div>
    
    <div class="header">
        <div class="university-name">UNIVERSITÉ VIRTUELLE DU CAMEROUN</div>
        <div class="university-info">
            Campus Principal - Yaoundé, Cameroun<br>
            Tél: +237 6 XX XX XX XX | Email: finance@universite-cameroun.cm<br>
            Site web: www.universite-cameroun.cm
        </div>
        <div class="document-title">REÇU DE PAIEMENT DÉTAILLÉ</div>
        <div class="document-subtitle">Document officiel certifié</div>
    </div>

    <div class="receipt-header">
        <div class="receipt-header-left">
            <div class="info-section">
                <div class="info-title">Référence de paiement</div>
                <div class="payment-reference">{{ $payment->payment_reference }}</div>
            </div>
            
            <div class="info-section">
                <div class="info-title">Statut</div>
                <span class="status-badge">✓ PAIEMENT VÉRIFIÉ</span>
            </div>
        </div>
        
        <div class="receipt-header-right">
            <div class="info-section">
                <div class="info-title">Date de génération</div>
                <div class="info-content">{{ $generated_at->format('d/m/Y à H:i') }}</div>
            </div>
            
            <div class="info-section">
                <div class="info-title">Validité</div>
                <div class="info-content">Document permanent</div>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="left-column">
            <div class="info-section">
                <div class="info-title">Étudiant(e)</div>
                <div class="info-content">
                    <strong>{{ $payment->user->name }}</strong><br>
                    Email: {{ $payment->user->email }}<br>
                    @if($payment->user->telephone)
                        Téléphone: {{ $payment->user->telephone }}<br>
                    @endif
                    @if($payment->enrollement->matricule_etudiant)
                        Matricule: {{ $payment->enrollement->matricule_etudiant }}<br>
                    @endif
                    Statut: Étudiant(e) inscrit(e)
                </div>
            </div>
            
            <div class="info-section">
                <div class="info-title">Formation</div>
                <div class="info-content">
                    <strong>{{ $payment->enrollement->filiere->nom }}</strong><br>
                    @if($payment->enrollement->niveau)
                        Niveau: {{ $payment->enrollement->niveau->nom }}<br>
                    @endif
                    Département: {{ $payment->enrollement->filiere->departement->nom }}<br>
                    @if($payment->enrollement->academicYear)
                        Année académique: {{ $payment->enrollement->academicYear->annee }}<br>
                    @endif
                    Type d'inscription: {{ ucfirst($payment->enrollement->type_inscription) }}
                </div>
            </div>
        </div>
        
        <div class="right-column">
            <div class="info-section">
                <div class="info-title">Détails du paiement</div>
                <div class="info-content">
                    Date de paiement: {{ $payment->completed_at->format('d/m/Y à H:i') }}<br>
                    Méthode: {{ $payment->paymentMethod->name }}<br>
                    @if($payment->transaction_id)
                        ID Transaction: {{ $payment->transaction_id }}<br>
                    @endif
                    Confirmé par l'étudiant: {{ $payment->student_confirmed_at ? $payment->student_confirmed_at->format('d/m/Y à H:i') : 'N/A' }}<br>
                    Vérifié par l'admin: {{ $payment->admin_verified_at ? $payment->admin_verified_at->format('d/m/Y à H:i') : 'N/A' }}
                </div>
            </div>
            
            @if($payment->invoice)
            <div class="info-section">
                <div class="info-title">Facture associée</div>
                <div class="info-content">
                    Numéro: {{ $payment->invoice->invoice_number }}<br>
                    Date d'émission: {{ $payment->invoice->issue_date->format('d/m/Y') }}<br>
                    Statut: Payée intégralement
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="amount-highlight">
        <div class="amount-label">MONTANT TOTAL PAYÉ</div>
        <div class="amount-value">{{ number_format($payment->amount, 0, ',', ' ') }}</div>
        <div class="amount-currency">Francs CFA (FCFA)</div>
    </div>

    <table class="details-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Montant</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Montant principal (frais de scolarité)</td>
                <td class="text-right">{{ number_format($payment->net_amount, 0, ',', ' ') }} FCFA</td>
            </tr>
            @if($payment->fee_amount > 0)
            <tr>
                <td>Frais de traitement ({{ $payment->paymentMethod->name }})</td>
                <td class="text-right">{{ number_format($payment->fee_amount, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
            <tr style="background-color: #ecfdf5; font-weight: bold;">
                <td>TOTAL PAYÉ</td>
                <td class="text-right">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td>
            </tr>
        </tbody>
    </table>

    @if($payment->student_confirmation_details)
    <div class="transaction-details">
        <h4>DÉTAILS DE LA TRANSACTION</h4>
        <div class="transaction-grid">
            @if(isset($payment->student_confirmation_details['transaction_id']))
            <div class="transaction-row">
                <div class="transaction-label">ID de transaction:</div>
                <div class="transaction-value">{{ $payment->student_confirmation_details['transaction_id'] }}</div>
            </div>
            @endif
            
            @if(isset($payment->student_confirmation_details['payment_method_details']))
                @foreach($payment->student_confirmation_details['payment_method_details'] as $key => $value)
                <div class="transaction-row">
                    <div class="transaction-label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</div>
                    <div class="transaction-value">{{ $value }}</div>
                </div>
                @endforeach
            @endif
            
            @if($payment->student_confirmation_details['confirmation_notes'])
            <div class="transaction-row">
                <div class="transaction-label">Notes de l'étudiant:</div>
                <div class="transaction-value">{{ $payment->student_confirmation_details['confirmation_notes'] }}</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <div class="verification-section">
        <div class="verification-title">VÉRIFICATION D'AUTHENTICITÉ</div>
        <div class="qr-placeholder">QR CODE</div>
        <div class="verification-url">{{ $verification_url }}</div>
        <p style="font-size: 9px; margin-top: 10px;">
            Scannez ce code QR ou visitez l'URL ci-dessus pour vérifier l'authenticité de ce reçu
        </p>
    </div>

    <div class="security-features">
        <div class="security-title">ÉLÉMENTS DE SÉCURITÉ</div>
        <div class="security-list">
            • Document généré automatiquement avec horodatage sécurisé<br>
            • Référence unique de paiement: {{ $payment->payment_reference }}<br>
            • Vérification en ligne disponible 24h/24<br>
            • Signature numérique intégrée<br>
            • Traçabilité complète des transactions
        </div>
    </div>

    <div class="signature-section">
        <div class="signature-left">
            <div class="signature-box">
                Service Financier<br>
                Université Virtuelle du Cameroun
            </div>
        </div>
        <div class="signature-right">
            <div class="signature-box">
                Signature numérique et cachet officiel
            </div>
        </div>
    </div>

    <div class="footer">
        <p><strong>IMPORTANT:</strong> Ce document constitue une preuve officielle de paiement.</p>
        <p>Conservez-le précieusement pour vos dossiers administratifs.</p>
        <p>Document généré le {{ $generated_at->format('d/m/Y à H:i') }} - Université Virtuelle du Cameroun</p>
        <p>Pour toute question: finance@universite-cameroun.cm | +237 6 XX XX XX XX</p>
    </div>
</body>
</html>