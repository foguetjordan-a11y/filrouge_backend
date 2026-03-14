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
            border-bottom: 2px solid #8b5cf6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #8b5cf6;
            font-size: 24px;
            margin: 0 0 10px 0;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 14px;
        }
        
        .niveau-info {
            background: linear-gradient(135deg, #a855f7 0%, #8b5cf6 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .niveau-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
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
            background-color: #faf5ff;
            border: 1px solid #e9d5ff;
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
        
        .stats-number.total { color: #8b5cf6; }
        .stats-number.pending { color: #f59e0b; }
        .stats-number.validated { color: #059669; }
        .stats-number.rejected { color: #dc2626; }
        
        .stats-label {
            font-size: 10px;
            color: #7c3aed;
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
            background-color: #8b5cf6;
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
            background-color: #faf5ff;
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
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        .chart-container {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
        }
        
        .chart-title {
            font-weight: bold;
            color: #1e293b;
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
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
            background-color: #8b5cf6;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">Système de Gestion des Enrôlements Académiques</div>
    </div>

    <!-- Informations du niveau -->
    <div class="niveau-info">
        <div class="niveau-name">📚 {{ $niveau->libelle }}</div>
        <div style="font-size: 14px; opacity: 0.9;">
            Rapport généré le {{ $generated_at }}
        </div>
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

    <!-- Répartition par filière -->
    @if($filiere_breakdown->isNotEmpty())
        <div class="chart-container">
            <div class="chart-title">📊 Répartition par Filière</div>
            
            @foreach($filiere_breakdown as $filiere => $data)
                <div style="margin-bottom: 15px;">
                    <div style="display: table; width: 100%;">
                        <div style="display: table-cell; width: 30%; padding-right: 10px; vertical-align: middle;">
                            <strong>{{ $filiere }}</strong>
                        </div>
                        <div style="display: table-cell; width: 50%; vertical-align: middle;">
                            <div class="progress-bar">
                                @php
                                    $percentage = $stats['total'] > 0 ? round(($data['count'] / $stats['total']) * 100, 1) : 0;
                                @endphp
                                <div class="progress-fill" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                        <div style="display: table-cell; width: 20%; text-align: right; vertical-align: middle;">
                            <strong>{{ $data['count'] }}</strong>
                            <br>
                            <small style="color: #6b7280;">{{ $percentage }}%</small>
                        </div>
                    </div>
                    <div style="font-size: 9px; color: #6b7280; margin-top: 3px;">
                        En attente: {{ $data['en_attente'] }} | 
                        Validées: {{ $data['valide'] }} | 
                        Rejetées: {{ $data['rejete'] }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Liste des inscriptions -->
    <div class="section">
        <div class="section-title">📋 Liste des Inscriptions</div>
        
        @if($enrollments->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 30%">Étudiant</th>
                        <th style="width: 25%">Filière</th>
                        <th style="width: 15%">Année Académique</th>
                        <th style="width: 15%">Date d'Inscription</th>
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
                            <td>
                                <div style="font-weight: bold; color: #1f2937;">{{ $enrollment->filiere->nom }}</div>
                                @if($enrollment->filiere->departement)
                                    <div style="font-size: 9px; color: #6b7280;">{{ $enrollment->filiere->departement->nom }}</div>
                                @endif
                            </td>
                            <td>{{ $enrollment->anneeAcademique->annee ?? 'N/A' }}</td>
                            <td>
                                {{ $enrollment->date_enrollement->format('d/m/Y') }}<br>
                                <small style="color: #6b7280;">{{ $enrollment->date_enrollement->format('H:i') }}</small>
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
                <p>Aucune inscription trouvée pour ce niveau.</p>
            </div>
        @endif
    </div>

    <!-- Analyse des performances -->
    @if($stats['total'] > 0)
        <div style="background-color: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 5px; padding: 15px; margin-top: 20px;">
            <div style="font-weight: bold; color: #0369a1; font-size: 14px; margin-bottom: 10px;">
                📈 Analyse des Performances - {{ $niveau->libelle }}
            </div>
            <div style="color: #0369a1; font-size: 11px;">
                @php
                    $validationRate = round(($stats['valide'] / $stats['total']) * 100, 1);
                    $rejectionRate = round(($stats['rejete'] / $stats['total']) * 100, 1);
                @endphp
                • <strong>Taux de validation:</strong> {{ $validationRate }}% ({{ $stats['valide'] }}/{{ $stats['total'] }})<br>
                • <strong>Taux de rejet:</strong> {{ $rejectionRate }}% ({{ $stats['rejete'] }}/{{ $stats['total'] }})<br>
                • <strong>En cours de traitement:</strong> {{ $stats['en_attente'] }} demande(s)<br>
                @if($filiere_breakdown->isNotEmpty())
                    • <strong>Filière la plus demandée:</strong> {{ $filiere_breakdown->sortByDesc('count')->keys()->first() }}<br>
                @endif
                • <strong>Recommandation:</strong> 
                @if($validationRate >= 70)
                    Excellent taux de validation pour ce niveau
                @elseif($validationRate >= 50)
                    Taux de validation correct, peut être amélioré
                @else
                    Analyser les motifs de rejet pour améliorer le taux de validation
                @endif
            </div>
        </div>
    @endif

    <!-- Pied de page -->
    <div class="footer">
        <p>
            <strong>Rapport Niveau: {{ $niveau->libelle }}</strong><br>
            Généré le {{ $generated_at }} | 
            {{ $stats['total'] }} inscription(s) | 
            Système de Gestion des Enrôlements Académiques
        </p>
    </div>
</body>
</html>
  