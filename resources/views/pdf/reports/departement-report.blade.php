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
            border-bottom: 2px solid #059669;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #059669;
            font-size: 24px;
            margin: 0 0 10px 0;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 14px;
        }
        
        .dept-info {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .dept-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .dept-stats {
            display: table;
            width: 100%;
        }
        
        .dept-stats-row {
            display: table-row;
        }
        
        .dept-stats-cell {
            display: table-cell;
            padding: 5px 15px 5px 0;
            font-size: 14px;
        }
        
        .dept-stats-label {
            opacity: 0.8;
        }
        
        .dept-stats-value {
            font-weight: bold;
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
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
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
        
        .stats-number.total { color: #059669; }
        .stats-number.pending { color: #f59e0b; }
        .stats-number.validated { color: #059669; }
        .stats-number.rejected { color: #dc2626; }
        
        .stats-label {
            font-size: 10px;
            color: #064e3b;
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
            background-color: #059669;
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
            background-color: #f0fdf4;
        }
        
        .filiere-name {
            font-weight: bold;
            color: #1f2937;
        }
        
        .filiere-description {
            font-size: 9px;
            color: #6b7280;
            margin-top: 2px;
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
            background-color: #059669;
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
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        .highlight-box {
            background-color: #ecfdf5;
            border: 1px solid #10b981;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .highlight-title {
            font-weight: bold;
            color: #065f46;
            font-size: 14px;
            margin-bottom: 10px;
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

    <!-- Informations du département -->
    <div class="dept-info">
        <div class="dept-name">🏢 {{ $departement->nom }}</div>
        <div class="dept-stats">
            <div class="dept-stats-row">
                <div class="dept-stats-cell">
                    <span class="dept-stats-label">Filières:</span>
                    <span class="dept-stats-value">{{ $stats['total_filieres'] }}</span>
                </div>
                <div class="dept-stats-cell">
                    <span class="dept-stats-label">Total Inscriptions:</span>
                    <span class="dept-stats-value">{{ $stats['total_enrollments'] }}</span>
                </div>
                <div class="dept-stats-cell">
                    <span class="dept-stats-label">Rapport généré:</span>
                    <span class="dept-stats-value">{{ $generated_at }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="stats-grid">
        <div class="stats-row">
            <div class="stats-cell first">
                <div class="stats-number total">{{ $stats['total_enrollments'] }}</div>
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

    <!-- Performance du département -->
    <div class="section">
        <div class="section-title">📊 Performance du Département</div>
        
        <div class="two-column">
            <div class="column">
                <div class="highlight-box">
                    <div class="highlight-title">Taux de Validation Global</div>
                    @php
                        $validationRate = $stats['total_enrollments'] > 0 ? round(($stats['valide'] / $stats['total_enrollments']) * 100, 1) : 0;
                    @endphp
                    <div style="font-size: 24px; font-weight: bold; color: #059669; margin-bottom: 10px;">
                        {{ $validationRate }}%
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $validationRate }}%"></div>
                    </div>
                    <div style="font-size: 10px; color: #6b7280; margin-top: 5px;">
                        {{ $stats['valide'] }} validées sur {{ $stats['total_enrollments'] }} demandes
                    </div>
                </div>
            </div>
            
            <div class="column">
                <div class="highlight-box">
                    <div class="highlight-title">Efficacité de Traitement</div>
                    @php
                        $processedRate = $stats['total_enrollments'] > 0 ? round((($stats['valide'] + $stats['rejete']) / $stats['total_enrollments']) * 100, 1) : 0;
                    @endphp
                    <div style="font-size: 24px; font-weight: bold; color: #059669; margin-bottom: 10px;">
                        {{ $processedRate }}%
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $processedRate }}%"></div>
                    </div>
                    <div style="font-size: 10px; color: #6b7280; margin-top: 5px;">
                        {{ $stats['valide'] + $stats['rejete'] }} traitées sur {{ $stats['total_enrollments'] }} demandes
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Répartition par filière -->
    <div class="section">
        <div class="section-title">🎓 Répartition par Filière</div>
        
        @if($departement->filieres->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th style="width: 35%">Filière</th>
                        <th style="width: 13%">Total</th>
                        <th style="width: 13%">En Attente</th>
                        <th style="width: 13%">Validées</th>
                        <th style="width: 13%">Rejetées</th>
                        <th style="width: 13%">Taux Validation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departement->filieres as $filiere)
                        @php
                            $filiereEnrollments = $enrollments->where('filiere_id', $filiere->id);
                            $filiereStats = [
                                'total' => $filiereEnrollments->count(),
                                'en_attente' => $filiereEnrollments->where('statut', 'en_attente')->count(),
                                'valide' => $filiereEnrollments->where('statut', 'valide')->count(),
                                'rejete' => $filiereEnrollments->where('statut', 'rejete')->count()
                            ];
                            $filiereValidationRate = $filiereStats['total'] > 0 ? round(($filiereStats['valide'] / $filiereStats['total']) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="filiere-name">{{ $filiere->nom }}</div>
                                @if($filiere->description)
                                    <div class="filiere-description">{{ Str::limit($filiere->description, 80) }}</div>
                                @endif
                            </td>
                            <td style="text-align: center; font-weight: bold;">{{ $filiereStats['total'] }}</td>
                            <td style="text-align: center; color: #f59e0b;">{{ $filiereStats['en_attente'] }}</td>
                            <td style="text-align: center; color: #059669;">{{ $filiereStats['valide'] }}</td>
                            <td style="text-align: center; color: #dc2626;">{{ $filiereStats['rejete'] }}</td>
                            <td style="text-align: center;">
                                <strong style="color: {{ $filiereValidationRate >= 70 ? '#059669' : ($filiereValidationRate >= 50 ? '#f59e0b' : '#dc2626') }}">
                                    {{ $filiereValidationRate }}%
                                </strong>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="text-align: center; padding: 40px; color: #666;">
                <p>Aucune filière trouvée pour ce département.</p>
            </div>
        @endif
    </div>

    <!-- Graphique de performance par filière -->
    @if($departement->filieres->isNotEmpty())
        <div class="chart-container">
            <div class="chart-title">📈 Performance par Filière (Taux de Validation)</div>
            
            @foreach($departement->filieres as $filiere)
                @php
                    $filiereEnrollments = $enrollments->where('filiere_id', $filiere->id);
                    $filiereTotal = $filiereEnrollments->count();
                    $filiereValide = $filiereEnrollments->where('statut', 'valide')->count();
                    $filiereRate = $filiereTotal > 0 ? round(($filiereValide / $filiereTotal) * 100, 1) : 0;
                @endphp
                
                @if($filiereTotal > 0)
                    <div style="margin-bottom: 15px;">
                        <div style="display: table; width: 100%;">
                            <div style="display: table-cell; width: 30%; padding-right: 10px; vertical-align: middle;">
                                <strong>{{ $filiere->nom }}</strong>
                            </div>
                            <div style="display: table-cell; width: 50%; vertical-align: middle;">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: {{ $filiereRate }}%; background-color: {{ $filiereRate >= 70 ? '#059669' : ($filiereRate >= 50 ? '#f59e0b' : '#dc2626') }}"></div>
                                </div>
                            </div>
                            <div style="display: table-cell; width: 20%; text-align: right; vertical-align: middle;">
                                <strong style="color: {{ $filiereRate >= 70 ? '#059669' : ($filiereRate >= 50 ? '#f59e0b' : '#dc2626') }}">
                                    {{ $filiereRate }}%
                                </strong>
                                <br>
                                <small style="color: #6b7280;">{{ $filiereValide }}/{{ $filiereTotal }}</small>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    <!-- Liste des inscriptions récentes -->
    @if($enrollments->isNotEmpty())
        <div class="section">
            <div class="section-title">📋 Inscriptions Récentes (10 dernières)</div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 25%">Étudiant</th>
                        <th style="width: 25%">Filière</th>
                        <th style="width: 15%">Niveau</th>
                        <th style="width: 15%">Date</th>
                        <th style="width: 10%">Type</th>
                        <th style="width: 10%">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($enrollments->sortByDesc('date_enrollement')->take(10) as $enrollment)
                        <tr>
                            <td>
                                <div class="student-info">{{ $enrollment->etudiant->prenom }} {{ $enrollment->etudiant->nom }}</div>
                                <div class="student-details">{{ $enrollment->etudiant->email }}</div>
                            </td>
                            <td>
                                <div class="filiere-name">{{ $enrollment->filiere->nom }}</div>
                            </td>
                            <td>{{ $enrollment->niveau->libelle ?? 'N/A' }}</td>
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
        </div>
    @endif

    <!-- Recommandations -->
    <div style="background-color: #f0fdf4; border: 1px solid #22c55e; border-radius: 5px; padding: 15px; margin-top: 20px;">
        <div style="font-weight: bold; color: #166534; font-size: 14px; margin-bottom: 10px;">
            💡 Recommandations pour le Département {{ $departement->nom }}
        </div>
        <div style="color: #166534; font-size: 11px;">
            @if($stats['en_attente'] > 0)
                • <strong>URGENT:</strong> Traiter les {{ $stats['en_attente'] }} demande(s) en attente<br>
            @endif
            @if($validationRate < 70)
                • <strong>AMÉLIORATION:</strong> Taux de validation global à {{ $validationRate }}% (objectif: 70%+)<br>
            @endif
            @if($stats['total_filieres'] > 0)
                • <strong>ÉQUILIBRAGE:</strong> Analyser la répartition des inscriptions entre les {{ $stats['total_filieres'] }} filières<br>
            @endif
            • <strong>SUIVI:</strong> Maintenir un délai de traitement inférieur à 5 jours<br>
            • <strong>COMMUNICATION:</strong> Informer régulièrement les candidats du statut de leur demande<br>
            • <strong>QUALITÉ:</strong> Documenter les critères d'admission pour chaque filière
        </div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <p>
            <strong>Rapport Département: {{ $departement->nom }}</strong><br>
            Généré le {{ $generated_at }} | 
            {{ $stats['total_enrollments'] }} inscription(s) | 
            {{ $stats['total_filieres'] }} filière(s)
        </p>
    </div>
</body>
</html>