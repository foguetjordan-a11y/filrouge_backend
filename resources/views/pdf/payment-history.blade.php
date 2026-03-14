<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des Paiements - {{ $user->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 15px;
        }
        
        .university-name {
            font-size: 20px;
            font-weight: bold;
            color: #3b82f6;
            margin-bottom: 5px;
        }
        
        .document-title {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            margin-top: 10px;
        }
        
        .user-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 8px;
        }
        
        .user-info-left,
        .user-info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .user-info-right {
            text-align: right;
        }
        
        .info-title {
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
            font-size: 10px;
        }
        
        .info-content {
            font-size: 10px;
            line-height: 1.4;
        }
        
        .summary-section {
            margin: 20px 0;
            padding: 15px;
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
        }
        
        .summary-title {
            font-size: 12px;
            font-weight: bold;
            color: #1d4ed8;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
        }
        
        .summary-row {
            display: table-row;
        }
        
        .summary-cell {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 8px;
            border-right: 1px solid #bfdbfe;
        }
        
        .summary-cell:last-child {
            border-right: none;
        }
        
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #1d4ed8;
        }
        
        .summary-label {
            font-size: 9px;
            color: #6b7280;
            margin-top: 2px;
        }
        
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 9px;
        }
        
        .payments-table th {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 8px;
        }
        
        .payments-table td {
            border: 1px solid #d1d5db;
            padding: 6px 4px;
            font-size: 8px;
        }
        
        .payments-table .text-right {
            text-align: right;
        }
        
        .payments-table .text-center {
            text-align: center;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-completed {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-failed {
            background-color: #fee2e2;
            color: #dc2626;
        }
        
        .status-cancelled {
            background-color: #f3f4f6;
            color: #6b7280;
        }
        
        .period-info {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            background-color: #f9fafb;
            border-radius: 6px;
            font-size: 9px;
            color: #6b7280;
        }
        
        .totals-section {
            margin: 20px 0;
            padding: 15px;
            background-color: #ecfdf5;
            border: 2px solid #10b981;
            border-radius: 8px;
        }
        
        .totals-title {
            font-size: 12px;
            font-weight: bold;
            color: #047857;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .totals-grid {
            display: table;
            width: 100%;
        }
        
        .totals-row {
            display: table-row;
        }
        
        .totals-label,
        .totals-value {
            display: table-cell;
            padding: 5px 10px;
            font-size: 10px;
        }
        
        .totals-label {
            font-weight: bold;
            width: 70%;
        }
        
        .totals-value {
            text-align: right;
            font-weight: bold;
            color: #047857;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .no-payments {
            text-align: center;
            padding: 40px;
            color: #6b7280;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="university-name">UNIVERSITÉ VIRTUELLE DU CAMEROUN</div>
        <div class="document-title">HISTORIQUE DES PAIEMENTS</div>
    </div>

    <div class="user-info">
        <div class="user-info-left">
            <div class="info-title">{{ $user->role === 'admin' ? 'RAPPORT GLOBAL' : 'ÉTUDIANT(E)' }}</div>
            <div class="info-content">
                @if($user->role === 'admin')
                    <strong>Rapport administrateur</strong><br>
                    Généré par: {{ $user->name }}<br>
                    Tous les paiements du système
                @else
                    <strong>{{ $user->name }}</strong><br>
                    Email: {{ $user->email }}<br>
                    @if($user->telephone)
                        Téléphone: {{ $user->telephone }}<br>
                    @endif
                    Statut: Étudiant(e)
                @endif
            </div>
        </div>
        
        <div class="user-info-right">
            <div class="info-title">Informations du rapport</div>
            <div class="info-content">
                Date de génération: {{ $generated_at->format('d/m/Y à H:i') }}<br>
                Nombre de paiements: {{ $payments->count() }}<br>
                @if($period['from'] && $period['to'])
                    Période: {{ $period['from']->format('d/m/Y') }} au {{ $period['to']->format('d/m/Y') }}
                @endif
            </div>
        </div>
    </div>

    <div class="summary-section">
        <div class="summary-title">RÉSUMÉ DES PAIEMENTS</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell">
                    <div class="summary-value">{{ $summary['total_payments'] }}</div>
                    <div class="summary-label">Total</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ $summary['completed_payments'] }}</div>
                    <div class="summary-label">Réussis</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ $summary['pending_payments'] }}</div>
                    <div class="summary-label">En attente</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ $summary['failed_payments'] }}</div>
                    <div class="summary-label">Échoués</div>
                </div>
            </div>
        </div>
    </div>

    <div class="totals-section">
        <div class="totals-title">MONTANTS FINANCIERS</div>
        <div class="totals-grid">
            <div class="totals-row">
                <div class="totals-label">Montant total payé:</div>
                <div class="totals-value">{{ number_format($summary['total_amount_paid'], 0, ',', ' ') }} FCFA</div>
            </div>
            <div class="totals-row">
                <div class="totals-label">Montant en attente:</div>
                <div class="totals-value">{{ number_format($summary['total_amount_pending'], 0, ',', ' ') }} FCFA</div>
            </div>
            <div class="totals-row">
                <div class="totals-label">Frais de traitement payés:</div>
                <div class="totals-value">{{ number_format($summary['total_fees_paid'], 0, ',', ' ') }} FCFA</div>
            </div>
        </div>
    </div>

    @if($payments->count() > 0)
        <h3 style="font-size: 12px; color: #374151; margin: 20px 0 10px 0;">DÉTAIL DES PAIEMENTS</h3>
        
        <table class="payments-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Référence</th>
                    <th style="width: 10%;">Date</th>
                    @if($user->role === 'admin')
                        <th style="width: 15%;">Étudiant</th>
                    @endif
                    <th style="width: 15%;">Formation</th>
                    <th style="width: 12%;">Méthode</th>
                    <th style="width: 10%;" class="text-right">Montant</th>
                    <th style="width: 8%;" class="text-center">Statut</th>
                    <th style="width: 8%;" class="text-center">Vérification</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr>
                    <td style="font-family: monospace; font-size: 7px;">{{ $payment->payment_reference }}</td>
                    <td>{{ $payment->created_at->format('d/m/Y') }}</td>
                    @if($user->role === 'admin')
                        <td>{{ $payment->user->name ?? 'N/A' }}</td>
                    @endif
                    <td>{{ $payment->enrollement->filiere->nom ?? 'N/A' }}</td>
                    <td>{{ $payment->paymentMethod->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($payment->amount, 0, ',', ' ') }}</td>
                    <td class="text-center">
                        <span class="status-badge status-{{ $payment->status }}">
                            @switch($payment->status)
                                @case('completed') ✓ @break
                                @case('pending') ⏳ @break
                                @case('failed') ✗ @break
                                @case('cancelled') ⊘ @break
                                @default {{ $payment->status }}
                            @endswitch
                        </span>
                    </td>
                    <td class="text-center">
                        @if($payment->verification_status)
                            @switch($payment->verification_status)
                                @case('verified') ✓ @break
                                @case('awaiting_verification') ⏳ @break
                                @case('disputed') ⚠ @break
                                @default {{ $payment->verification_status }}
                            @endswitch
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-payments">
            <p>Aucun paiement trouvé pour la période sélectionnée.</p>
        </div>
    @endif

    @if($period['from'] && $period['to'])
    <div class="period-info">
        <strong>Période couverte:</strong> 
        du {{ $period['from']->format('d/m/Y') }} au {{ $period['to']->format('d/m/Y') }}
        ({{ $period['from']->diffInDays($period['to']) + 1 }} jours)
    </div>
    @endif

    <div class="footer">
        <p>Historique des paiements généré le {{ $generated_at->format('d/m/Y à H:i') }}</p>
        <p>Université Virtuelle du Cameroun - Service Financier</p>
        <p>Ce document est confidentiel et destiné uniquement à {{ $user->role === 'admin' ? 'l\'administration' : $user->name }}</p>
        <p>Pour toute question: finance@universite-cameroun.cm | +237 6 XX XX XX XX</p>
    </div>
</body>
</html>