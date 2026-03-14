<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
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
            border-bottom: 2px solid #f59e0b;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #f59e0b;
            font-size: 24px;
            margin: 0 0 10px 0;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 14px;
        }
        
        .alert-box {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .alert-box .alert-title {
            font-weight: bold;
            color: #92400e;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .alert-box .alert-text {
            color: #92400e;
            font-size: 12px;
        }
        
        .info-section {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 5px 15px 5px 0;
            width: 30%;
        }
        
        .info-value {
            display: table-cell;
            padding: 5px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th {
            background-color: #f59e0b;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
            vertical-align: top;
        }
        
        tr:nth-child(even) {
            background-color: #fffbeb;
        }
        
        .priority-high {
            background-color: #fee2e2 !important;
        }
        
        .priority-medium {
            background-color: #fef3c7 !important;
        }
        
        .student-info {
            font-weight: bold;
            color: #1f2937;
        }
        
        .student-details {
            font-size: 9px;
            color: #6b7280;
            margin-top: 2px;
        }
        
        .academic-info {
            font-weight: bold;
            color: #2563eb;
        }
        
        .academic-details {
            font-size: 9px;
            color: #6b7280;
            margin-top: 2px;
        }
        
        .date-info {
            font-weight: bold;
            color: #059669;
        }
        
        .date-details {
            font-size: 9px;
            color: #6b7280;
            margin-top: 2px;
        }
        
        .priority-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }
        
        .priority-urgent {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .priority-normal {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .priority-low {
            background-color: #e0f2fe;
            color: #0277bd;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        .summary-box {
            background-color: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .summary-title {
            font-weight: bold;
            color: #0369a1;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .summary-stats {
            display: table;
            width: 100%;
        }
        
        .summary-row {
            display: table-row;
        }
        
        .summary-cell {
            display: table-cell;
            padding: 5px 10px;
            text-align: center;
            border-right: 1px solid #bae6fd;
        }
        
        .summary-cell:last-child {
            border-right: none;
        }
        
        .summary-number {
            font-size: 16px;
            font-weight: bold;
            color: #0369a1;
        }
        
        .summary-label {
            font-size: 10px;
            color: #0369a1;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">Système de Gestion des Enrôlements Académiques</div>
    </div>

    <!-- Alerte -->
    <div class="alert-box">
        <div class="alert-title">⚠️ ATTENTION - DEMANDES EN ATTENTE</div>
        <div class="alert-text">
            {{ $total_count }} demande(s) d'inscription nécessitent votre attention et doivent être traitées rapidement.
        </div>
    </div>

    <!-- Informations du rapport -->
    <div class="info-section">
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Date de génération :</div>
                <div class="info-value">{{ $generated_at }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Nombre de demandes :</div>
                <div class="info-value">{{ $total_count }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Statut :</div>
                <div class="info-value">En attente de traitement</div>
            </div>
        </div>
    </div>

    <!-- Résumé par priorité -->
    <div class="summary-box">
        <div class="summary-title">📊 Répartition par Priorité</div>
        <div class="summary-stats">
            <div class="summary-row">
                @php
                    $urgent = $enrollments->filter(function($e) {
                        return $e->date_enrollement->diffInDays(now()) > 7;
                    })->count();
                    
                    $normal = $enrollments->filter(function($e) {
                        $days = $e->date_enrollement->diffInDays(now());
                        return $days >= 3 && $days <= 7;
                    })->count();
                    
                    $recent = $enrollments->filter(function($e) {
                        return $e->date_enrollement->diffInDays(now()) < 3;
                    })->count();
                @endphp
                
                <div class="summary-cell">
                    <div class="summary-number">{{ $urgent }}</div>
                    <div class="summary-label">URGENT<br>(+7 jours)</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-number">{{ $normal }}</div>
                    <div class="summary-label">NORMAL<br>(3-7 jours)</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-number">{{ $recent }}</div>
                    <div class="summary-label">RÉCENT<br>(-3 jours)</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-number">{{ $total_count }}</div>
                    <div class="summary-label">TOTAL<br>À TRAITER</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des demandes -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 25%">Étudiant</th>
                <th style="width: 25%">Formation Demandée</th>
                <th style="width: 15%">Date de Demande</th>
                <th style="width: 15%">Ancienneté</th>
                <th style="width: 15%">Priorité</th>
            </tr>
        </thead>
        <tbody>
            @foreach($enrollments->sortByDesc(function($e) { return $e->date_enrollement->diffInDays(now()); }) as $index => $enrollment)
                @php
                    $daysOld = $enrollment->date_enrollement->diffInDays(now());
                    $priorityClass = '';
                    $priorityLabel = '';
                    $priorityBadge = '';
                    
                    if ($daysOld > 7) {
                        $priorityClass = 'priority-high';
                        $priorityLabel = 'URGENT';
                        $priorityBadge = 'priority-urgent';
                    } elseif ($daysOld >= 3) {
                        $priorityClass = 'priority-medium';
                        $priorityLabel = 'NORMAL';
                        $priorityBadge = 'priority-normal';
                    } else {
                        $priorityClass = '';
                        $priorityLabel = 'RÉCENT';
                        $priorityBadge = 'priority-low';
                    }
                @endphp
                
                <tr class="{{ $priorityClass }}">
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div class="student-info">{{ $enrollment->etudiant->prenom }} {{ $enrollment->etudiant->nom }}</div>
                        <div class="student-details">
                            📧 {{ $enrollment->etudiant->email }}<br>
                            @if($enrollment->etudiant->telephone)
                                📱 {{ $enrollment->etudiant->telephone }}<br>
                            @endif
                            🆔 ID: {{ $enrollment->etudiant->id }}
                        </div>
                    </td>
                    <td>
                        <div class="academic-info">{{ $enrollment->filiere->nom }}</div>
                        <div class="academic-details">
                            🏢 {{ $enrollment->filiere->departement->nom ?? 'N/A' }}<br>
                            📚 {{ $enrollment->niveau->libelle }}<br>
                            📅 {{ $enrollment->anneeAcademique->annee ?? 'N/A' }}<br>
                            📝 {{ ucfirst($enrollment->type_inscription) }}
                        </div>
                    </td>
                    <td>
                        <div class="date-info">{{ $enrollment->date_enrollement->format('d/m/Y') }}</div>
                        <div class="date-details">
                            🕐 {{ $enrollment->date_enrollement->format('H:i') }}<br>
                            📆 {{ $enrollment->date_enrollement->format('l') }}
                        </div>
                    </td>
                    <td>
                        <div class="date-info">{{ $daysOld }} jour(s)</div>
                        <div class="date-details">
                            @if($daysOld == 0)
                                Aujourd'hui
                            @elseif($daysOld == 1)
                                Hier
                            @else
                                Il y a {{ $daysOld }} jours
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="priority-badge {{ $priorityBadge }}">
                            {{ $priorityLabel }}
                        </span>
                        @if($daysOld > 10)
                            <div class="date-details" style="color: #dc2626; font-weight: bold; margin-top: 3px;">
                                ⚠️ TRÈS URGENT
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($enrollments->isEmpty())
        <div style="text-align: center; padding: 40px; color: #666;">
            <p>✅ Aucune demande en attente. Toutes les inscriptions ont été traitées !</p>
        </div>
    @endif

    <!-- Recommandations -->
    @if($total_count > 0)
        <div style="background-color: #f0fdf4; border: 1px solid #22c55e; border-radius: 5px; padding: 15px; margin-top: 20px;">
            <div style="font-weight: bold; color: #166534; font-size: 14px; margin-bottom: 10px;">
                💡 Recommandations d'Action
            </div>
            <div style="color: #166534; font-size: 11px;">
                @if($urgent > 0)
                    • <strong>PRIORITÉ 1:</strong> Traiter immédiatement les {{ $urgent }} demande(s) urgente(s) (plus de 7 jours)<br>
                @endif
                @if($normal > 0)
                    • <strong>PRIORITÉ 2:</strong> Examiner les {{ $normal }} demande(s) normale(s) dans les 24h<br>
                @endif
                @if($recent > 0)
                    • <strong>PRIORITÉ 3:</strong> Planifier le traitement des {{ $recent }} demande(s) récente(s)<br>
                @endif
                • <strong>OBJECTIF:</strong> Traiter toutes les demandes dans un délai maximum de 5 jours ouvrables<br>
                • <strong>SUIVI:</strong> Notifier automatiquement les étudiants des décisions prises
            </div>
        </div>
    @endif

    <!-- Pied de page -->
    <div class="footer">
        <p>
            <strong>Système de Gestion des Enrôlements Académiques</strong><br>
            Rapport généré le {{ $generated_at }} | 
            {{ $total_count }} demande(s) en attente | 
            Action requise par l'administration
        </p>
    </div>
</body>
</html>