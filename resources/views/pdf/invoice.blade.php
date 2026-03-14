<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $invoice->invoice_number }}</title>
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
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        
        .university-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
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
        
        .invoice-info {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .invoice-info-left,
        .invoice-info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .invoice-info-right {
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
        
        .invoice-number {
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .items-table th {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        
        .items-table td {
            border: 1px solid #d1d5db;
            padding: 10px 8px;
            font-size: 11px;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .items-table .text-center {
            text-align: center;
        }
        
        .totals-section {
            width: 300px;
            margin-left: auto;
            margin-top: 20px;
        }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .totals-table .label {
            font-weight: bold;
            text-align: right;
        }
        
        .totals-table .amount {
            text-align: right;
            font-weight: bold;
        }
        
        .total-final {
            background-color: #2563eb;
            color: white;
            font-size: 14px;
        }
        
        .payment-info {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9fafb;
            border-left: 4px solid #2563eb;
        }
        
        .payment-info h4 {
            margin: 0 0 10px 0;
            color: #2563eb;
            font-size: 14px;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-sent {
            background-color: #dbeafe;
            color: #1d4ed8;
        }
        
        .status-paid {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .status-overdue {
            background-color: #fee2e2;
            color: #dc2626;
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
        <div class="document-title">FACTURE</div>
    </div>

    <div class="invoice-info">
        <div class="invoice-info-left">
            <div class="info-section">
                <div class="info-title">FACTURÉ À :</div>
                <div class="info-content">
                    <strong>{{ $invoice->user->name }}</strong><br>
                    Email: {{ $invoice->user->email }}<br>
                    @if($invoice->user->telephone)
                        Téléphone: {{ $invoice->user->telephone }}<br>
                    @endif
                    Étudiant(e)
                </div>
            </div>
            
            <div class="info-section">
                <div class="info-title">FORMATION :</div>
                <div class="info-content">
                    <strong>{{ $invoice->enrollement->filiere->nom }}</strong><br>
                    @if($invoice->enrollement->niveau)
                        Niveau: {{ $invoice->enrollement->niveau->nom }}<br>
                    @endif
                    Département: {{ $invoice->enrollement->filiere->departement->nom }}<br>
                    Année académique: {{ $invoice->enrollement->academic_year ?? date('Y') . '-' . (date('Y') + 1) }}
                </div>
            </div>
        </div>
        
        <div class="invoice-info-right">
            <div class="info-section">
                <div class="info-title">FACTURE N° :</div>
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
            </div>
            
            <div class="info-section">
                <div class="info-title">DATE D'ÉMISSION :</div>
                <div class="info-content">{{ $invoice->issue_date->format('d/m/Y') }}</div>
            </div>
            
            <div class="info-section">
                <div class="info-title">DATE D'ÉCHÉANCE :</div>
                <div class="info-content">{{ $invoice->due_date->format('d/m/Y') }}</div>
            </div>
            
            <div class="info-section">
                <div class="info-title">STATUT :</div>
                <div class="info-content">
                    <span class="status-badge status-{{ $invoice->status }}">
                        {{ $invoice->status_label }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th class="text-center" style="width: 10%;">Qté</th>
                <th class="text-right" style="width: 20%;">Prix unitaire</th>
                <th class="text-right" style="width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->line_items as $item)
            <tr>
                <td>{{ $item['description'] }}</td>
                <td class="text-center">{{ $item['quantity'] }}</td>
                <td class="text-right">{{ number_format($item['unit_price'], 0, ',', ' ') }} FCFA</td>
                <td class="text-right">{{ number_format($item['total'], 0, ',', ' ') }} FCFA</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="label">Sous-total :</td>
                <td class="amount">{{ number_format($invoice->subtotal, 0, ',', ' ') }} FCFA</td>
            </tr>
            @if($invoice->tax_amount > 0)
            <tr>
                <td class="label">TVA :</td>
                <td class="amount">{{ number_format($invoice->tax_amount, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
            <tr class="total-final">
                <td class="label">TOTAL À PAYER :</td>
                <td class="amount">{{ number_format($invoice->total_amount, 0, ',', ' ') }} FCFA</td>
            </tr>
        </table>
    </div>

    <div class="payment-info">
        <h4>INFORMATIONS DE PAIEMENT</h4>
        <p><strong>Montant à payer :</strong> {{ number_format($invoice->total_amount, 0, ',', ' ') }} FCFA</p>
        <p><strong>Date limite :</strong> {{ $invoice->due_date->format('d/m/Y') }}</p>
        <p>Veuillez vous connecter à votre espace étudiant pour procéder au paiement en ligne ou contactez le service financier pour les autres modalités de paiement.</p>
        <p><strong>Référence à mentionner :</strong> {{ $invoice->invoice_number }}</p>
    </div>

    @if($invoice->notes)
    <div class="payment-info">
        <h4>NOTES</h4>
        <p>{{ $invoice->notes }}</p>
    </div>
    @endif

    <div class="footer">
        <p>Cette facture a été générée automatiquement le {{ $generated_at->format('d/m/Y à H:i') }}</p>
        <p>Pour toute question concernant cette facture, contactez le service financier : finance@universite-cameroun.cm</p>
    </div>
</body>
</html>