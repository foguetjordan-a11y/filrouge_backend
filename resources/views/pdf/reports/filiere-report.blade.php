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
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #2563eb;
            font-size: 24px;
            margin: 0 0 10px 0;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 14px;
        }
        
        .filiere-info {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .filiere-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .filiere-dept {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        
        .filiere-description {
            font-size: 12px;
            opacity: 0.8;
            line-height: 1.5;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .stats-row {
            display: table-row;
        }
        
        .stats-cell {
            display: table-cell;
            text-align: center;
            padding: 15px 10px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            width: 25%;
        }
        
        .stats-cell.first {
            border-radius: 8px 0 0 8px;
        }
        
        .stats-cell.last {
            border-radius: 0 8px 8px 0;
        }
        
        .stats-number {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stats-number.total { color: #2563eb; }
        .stats-number.pending { color: #f59e0b; }
        .stats-number.validated { color: #059669; }
        .stats-number.rejected { color: #dc2626; }
        
        .stats-label {
            font-size: 10px;
            color: #64748b;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th {
            background-color: #2563eb;
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
            background-color: #f8fafc;
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
        
        .status-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }
        
        .status-valide {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .status-en-attente {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-rejete {
            background-color: #fee2e2;
            color: #991b1b;
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
        
        .progress-bar {
            background-color: #e2e8f0;
            border-radius: 10px;
            height: 15px;
            margin: 5px 0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 10px;
        }
        
        .progress-fill.validated { background-color: #059669; }
        .progress-fill.pending { background-color: #f59e0b; }
        .progress-fill.rejected { background-color: #dc2626; }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        .two-column {
            display: table;
            width: 100%;
        }
        
        .column {
            display: table-cell;
            width: 50%;
            padding: 0 10px;
            vertical-align: top;
        }
        
        .column:first-child {
            padding-left: 0;
        }
        
        .column:last-child {
            padding-right: 0;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">Système de Gestion des Enrôlements Académiques</div>
    </div>

    <!-- Informations de la filière -->
    <div class="filiere-info">
        <div class="filiere-name">{{ $filiere->nom }}</div>
        <div class="filiere-dept">Département: {{ $filiere->departement->nom ?? 'Non spécifié' }}</div>
        @if($filiere->description)
            <div class="filiere-description">{{ $filiere->description }}</div>
        @endif
    </div>

    <!-- Statistiques principales -->
    <div class="stats-grid">
        <div class="stats-row">
            <div class="stats-cell first">
                <div class="stats-number total">{{ $stats['total'] }}</div>
                <div class="stats-label">Total Inscriptions</div>
            </div>
            <div class="stats-cell">
                <div class="stats-number pending">{{ $stats['en_attente'] }}</div>
                <div class="stats-label">En Attente</div>
            </div>
            <div class="stats-cell">
                <div class="stats-number validated">{{ $stats['valide'] }}</div>
                <div class="stats-label">Validées</div>
            </div>
            <div class="stats-cell last">
                <div class="stats-number rejected">{{ $stats['rejete'] }}</div>
                <div class="stats-label">Rejetées</div>
            </div>
        </div>
    </div>

    <!-- Analyse des performances -->
    <div class="section">
        <div class="section-title">📊 Analyse des Performances</div>
        
        <div class="two-column">
            <div class="column">
                <div class="summary-box">
                    <div class="summary-title">Taux de Validation</div>
                    @php
                        $validationRate = $stats['total'] > 0 ? round(($stats['valide'] / $stats['total']) * 100, 1) : 0;
                    @endphp
                    <div style="font-size: 20px; font-weight: bold; color: #059669; margin-bottom: 10px;">
                        {{ $validationRate }}%
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill validated" style="width: {{ $validationRate }}%"></div>
                    </div>
                    <div style="font-size: 10px; color: #6b7280; margin-top: 5px;">
                        {{ $stats['valide'] }} validées sur {{ $stats['total'] }} demandes
                    </div>
                </div>
            </div>
            
            <div class="column">
                <div class="summary-box">
                    <div class="summary-title">Demandes en Attente</div>
                    @php
                        $pendingRate = $stats['total'] > 0 ? round(($stats['en_attente'] / $stats['total']) * 100, 1) : 0;
                    @endphp
                    <div style="font-size: 20px; font-weight: bold; color: #f59e0b; margin-bottom: 10px;">
                        {{ $pendingRate }}%
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill pending" style="width: {{ $pendingRate }}%"></div>
                    </div>
                    <div style="font-size: 10px; color: #6b7280; margin-top: 5px;">
                        {{ $stats['en_attente'] }} en attente sur {{ $stats['total'] }} demandes
                    </div>
                </div>
            </div>
        </div>
        
        @if($stats['rejete'] > 0)
            <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 5px; padding: 15px; margin-top: 15px;">
                <div style="font-weight: bold; color: #991b1b; font-size: 13px; margin-bottom: 5px;">
                    ⚠️ Taux de Rejet: {{ $stats['total'] > 0 ? round(($stats['rejete'] / $stats['total']) * 100, 1) : 0 }}%
                </div>
                <div style="color: #991b1b; font-size: 11px;">
                    {{ $stats['rejete'] }} demande(s) rejetée(s). Analyser les motifs de rejet pour améliorer le processus d'admission.
                </div>
            </div>
        @endif
    </div>

    <!-- Liste détaillée des inscriptions -->
    <div class="section">
        <div class="section-title">📋 Liste des Inscriptions</div>
        
        @if($enrollments->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 30%">Étudiant</th>
                        <th style="width: 15%">Niveau</th>
                        <th style="width: 15%">Année Académique</th>
                        <th style="width: 15%">Date d'Inscription</th>
                        <th style="width: 10%">Type</th>
                        <th style="width: 10%">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($enrollments as $index => $enrollment)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="student-info">{{ $enrollment->etudiant->prenom }} {{ $enrollment->etudiant->nom }}</div>
                                <div class="student-details">
                                    📧 {{ $enrollment->etudiant->email }}<br>
                                    @if($enrollment->etudiant->telephone)
                                        📱 {{ $enrollment->etudiant->telephone }}
                                    @endif
                                </div>
                            </td>
                            <td>{{ $enrollment->niveau->libelle ?? 'N/A' }}</td>
                            <td>{{ $enrollment->anneeAcademique->annee ?? 'N/A' }}</td>
                            <td>
                                {{ $enrollment->date_enrollement->format('d/m/Y') }}<br>
                                <small style="color: #6b7280;">{{ $enrollment->date_enrollement->format('H:i') }}</small>
                            </td>
                            <td>
                                <small>{{ ucfirst($enrollment->type_inscription) }}</small>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $enrollment->statut }}">
                                    @switch($enrollment->statut)
                                        @case('valide')
                                            Validé
                                            @break
                                        @case('en_attente')
                                            En attente
                                            @break
                                        @case('rejete')
                                            Rejeté
                                            @break
                                        @default
                                            {{ $enrollment->statut }}
                                    @endswitch
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="text-align: center; padding: 40px; color: #666;">
                <p>Aucune inscription trouvée pour cette filière.</p>
            </div>
        @endif
    </div>

    <!-- Répartition par niveau -->
    @if($enrollments->isNotEmpty())
        <div class="section">
            <div class="section-title">📚 Répartition par Niveau</div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 40%">Niveau</th>
                        <th style="width: 15%">Total</th>
                        <th style="width: 15%">En Attente</th>
                        <th style="width: 15%">Validées</th>
                        <th style="width: 15%">Rejetées</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $niveauStats = $enrollments->groupBy('niveau.libelle')->map(function($group) {
                            return [
                                'total' => $group->count(),
                                'en_attente' => $group->where('statut', 'en_attente')->count(),
                                'valide' => $group->where('statut', 'valide')->count(),
                                'rejete' => $group->where('statut', 'rejete')->count()
                            ];
                        });
                    @endphp
                    
                    @foreach($niveauStats as $niveau => $stats)
                        <tr>
                            <td><strong>{{ $niveau ?: 'Non spécifié' }}</strong></td>
                            <td style="text-align: center; font-weight: bold;">{{ $stats['total'] }}</td>
                            <td style="text-align: center; color: #f59e0b;">{{ $stats['en_attente'] }}</td>
                            <td style="text-align: center; color: #059669;">{{ $stats['valide'] }}</td>
                            <td style="text-align: center; color: #dc2626;">{{ $stats['rejete'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Recommandations -->
    @if($stats['en_attente'] > 0 || $stats['rejete'] > 0)
        <div style="background-color: #f0fdf4; border: 1px solid #22c55e; border-radius: 5px; padding: 15px; margin-top: 20px;">
            <div style="font-weight: bold; color: #166534; font-size: 14px; margin-bottom: 10px;">
                💡 Recommandations pour la Filière {{ $filiere->nom }}
            </div>
            <div style="color: #166534; font-size: 11px;">
                @if($stats['en_attente'] > 0)
                    • Traiter rapidement les {{ $stats['en_attente'] }} demande(s) en attente<br>
                @endif
                @if($stats['rejete'] > 0)
                    • Analyser les motifs de rejet des {{ $stats['rejete'] }} demande(s) rejetée(s)<br>
                @endif
                @if($validationRate < 70)
                    • Améliorer le taux de validation (actuellement {{ $validationRate }}%)<br>
                @endif
                • Maintenir une communication régulière avec les candidats<br>
                • Documenter les critères d'admission pour cette filière
            </div>
        </div>
    @endif

    <!-- Pied de page -->
    <div class="footer">
        <p>
            <strong>Rapport Filière: {{ $filiere->nom }}</strong><br>
            Généré le {{ $generated_at }} | 
            {{ $stats['total'] }} inscription(s) | 
            Département: {{ $filiere->departement->nom ?? 'Non spécifié' }}
        </p>
    </div>
</body>
</html>