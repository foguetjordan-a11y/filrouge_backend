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
            border-bottom: 2px solid #7c3aed;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #7c3aed;
            font-size: 24px;
            margin: 0 0 10px 0;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 14px;
        }
        
        .header .period {
            color: #7c3aed;
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
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
            color: #7c3aed;
            margin-bottom: 5px;
        }
        
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
        
        .subsection {
            margin-bottom: 20px;
        }
        
        .subsection-title {
            font-size: 14px;
            font-weight: bold;
            color: #475569;
            margin-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th {
            background-color: #7c3aed;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }
        
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .metric-box {
            background-color: #f1f5f9;
            border-left: 4px solid #7c3aed;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .metric-title {
            font-weight: bold;
            color: #1e293b;
            font-size: 13px;
            margin-bottom: 8px;
        }
        
        .metric-value {
            font-size: 20px;
            font-weight: bold;
            color: #7c3aed;
            margin-bottom: 5px;
        }
        
        .metric-description {
            font-size: 10px;
            color: #64748b;
        }
        
        .chart-placeholder {
            background-color: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            color: #64748b;
            margin: 15px 0;
        }
        
        .progress-bar {
            background-color: #e2e8f0;
            border-radius: 10px;
            height: 20px;
            margin: 5px 0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background-color: #7c3aed;
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        
        .progress-text {
            font-size: 10px;
            color: #475569;
            margin-top: 3px;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .highlight-number {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .highlight-text {
            font-size: 14px;
            opacity: 0.9;
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
        <div class="period">{{ $period }}</div>
    </div>

    <!-- Statistiques principales -->
    <div class="stats-grid">
        <div class="stats-row">
            <div class="stats-cell first">
                <div class="stats-number">{{ $general_stats['total_students'] }}</div>
                <div class="stats-label">Total Étudiants</div>
            </div>
            <div class="stats-cell">
                <div class="stats-number">{{ $general_stats['total_enrollments'] }}</div>
                <div class="stats-label">Total Inscriptions</div>
            </div>
            <div class="stats-cell">
                <div class="stats-number">{{ $general_stats['validated_enrollments'] }}</div>
                <div class="stats-label">Inscriptions Validées</div>
            </div>
            <div class="stats-cell last">
                <div class="stats-number">{{ $general_stats['pending_enrollments'] }}</div>
                <div class="stats-label">En Attente</div>
            </div>
        </div>
    </div>

    <!-- Métriques clés -->
    <div class="section">
        <div class="section-title">📊 Métriques Clés de Performance</div>
        
        <div class="two-column">
            <div class="column">
                <div class="metric-box">
                    <div class="metric-title">Taux d'Approbation des Étudiants</div>
                    <div class="metric-value">
                        {{ $general_stats['total_students'] > 0 ? round(($general_stats['approved_students'] / $general_stats['total_students']) * 100, 1) : 0 }}%
                    </div>
                    <div class="metric-description">
                        {{ $general_stats['approved_students'] }} approuvés sur {{ $general_stats['total_students'] }} étudiants
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $general_stats['total_students'] > 0 ? round(($general_stats['approved_students'] / $general_stats['total_students']) * 100, 1) : 0 }}%"></div>
                    </div>
                </div>
                
                <div class="metric-box">
                    <div class="metric-title">Taux de Validation des Inscriptions</div>
                    <div class="metric-value">
                        {{ $general_stats['total_enrollments'] > 0 ? round(($general_stats['validated_enrollments'] / $general_stats['total_enrollments']) * 100, 1) : 0 }}%
                    </div>
                    <div class="metric-description">
                        {{ $general_stats['validated_enrollments'] }} validées sur {{ $general_stats['total_enrollments'] }} inscriptions
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $general_stats['total_enrollments'] > 0 ? round(($general_stats['validated_enrollments'] / $general_stats['total_enrollments']) * 100, 1) : 0 }}%"></div>
                    </div>
                </div>
            </div>
            
            <div class="column">
                <div class="metric-box">
                    <div class="metric-title">Demandes en Attente</div>
                    <div class="metric-value">{{ $general_stats['pending_enrollments'] }}</div>
                    <div class="metric-description">
                        Nécessitent une action administrative
                    </div>
                    @if($general_stats['pending_enrollments'] > 0)
                        <div class="progress-text" style="color: #dc2626; font-weight: bold;">
                            ⚠️ Action requise
                        </div>
                    @else
                        <div class="progress-text" style="color: #059669; font-weight: bold;">
                            ✅ Toutes traitées
                        </div>
                    @endif
                </div>
                
                <div class="metric-box">
                    <div class="metric-title">Taux de Rejet</div>
                    <div class="metric-value">
                        {{ $general_stats['total_enrollments'] > 0 ? round(($general_stats['rejected_enrollments'] / $general_stats['total_enrollments']) * 100, 1) : 0 }}%
                    </div>
                    <div class="metric-description">
                        {{ $general_stats['rejected_enrollments'] }} rejetées sur {{ $general_stats['total_enrollments'] }} inscriptions
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $general_stats['total_enrollments'] > 0 ? round(($general_stats['rejected_enrollments'] / $general_stats['total_enrollments']) * 100, 1) : 0 }}%; background-color: #dc2626;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques par filière -->
    <div class="section">
        <div class="section-title">🎓 Répartition par Filière</div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 30%">Filière</th>
                    <th style="width: 20%">Département</th>
                    <th style="width: 12%">Total</th>
                    <th style="width: 12%">En Attente</th>
                    <th style="width: 13%">Validées</th>
                    <th style="width: 13%">Rejetées</th>
                </tr>
            </thead>
            <tbody>
                @foreach($filiere_stats as $filiere)
                    <tr>
                        <td><strong>{{ $filiere['nom'] }}</strong></td>
                        <td>{{ $filiere['departement'] }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $filiere['total_enrollments'] }}</td>
                        <td style="text-align: center; color: #f59e0b;">{{ $filiere['en_attente'] }}</td>
                        <td style="text-align: center; color: #059669;">{{ $filiere['valide'] }}</td>
                        <td style="text-align: center; color: #dc2626;">{{ $filiere['rejete'] }}</td>
                    </tr>
                @endforeach
                
                @if($filiere_stats->isEmpty())
                    <tr>
                        <td colspan="6" style="text-align: center; color: #64748b; font-style: italic;">
                            Aucune donnée disponible
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Évolution mensuelle -->
    <div class="section">
        <div class="section-title">📈 Évolution des Inscriptions (6 derniers mois)</div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 30%">Mois</th>
                    <th style="width: 20%">Nouvelles Inscriptions</th>
                    <th style="width: 25%">Évolution</th>
                    <th style="width: 25%">Graphique</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthly_stats as $index => $month)
                    @php
                        $prevMonth = $index > 0 ? $monthly_stats[$index - 1]['enrollments'] : 0;
                        $evolution = $prevMonth > 0 ? round((($month['enrollments'] - $prevMonth) / $prevMonth) * 100, 1) : 0;
                        $maxEnrollments = collect($monthly_stats)->max('enrollments');
                        $barWidth = $maxEnrollments > 0 ? round(($month['enrollments'] / $maxEnrollments) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td><strong>{{ $month['month'] }}</strong></td>
                        <td style="text-align: center; font-weight: bold;">{{ $month['enrollments'] }}</td>
                        <td style="text-align: center;">
                            @if($evolution > 0)
                                <span style="color: #059669;">↗️ +{{ $evolution }}%</span>
                            @elseif($evolution < 0)
                                <span style="color: #dc2626;">↘️ {{ $evolution }}%</span>
                            @else
                                <span style="color: #64748b;">→ 0%</span>
                            @endif
                        </td>
                        <td>
                            <div class="progress-bar" style="height: 15px;">
                                <div class="progress-fill" style="width: {{ $barWidth }}%; background-color: #7c3aed;"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Résumé exécutif -->
    <div class="highlight-box">
        <div class="highlight-number">
            {{ $general_stats['total_enrollments'] > 0 ? round(($general_stats['validated_enrollments'] / $general_stats['total_enrollments']) * 100, 1) : 0 }}%
        </div>
        <div class="highlight-text">
            Taux de réussite global des inscriptions<br>
            <small>{{ $general_stats['validated_enrollments'] }} inscriptions validées sur {{ $general_stats['total_enrollments'] }} demandes</small>
        </div>
    </div>

    <!-- Recommandations -->
    @if($general_stats['pending_enrollments'] > 0 || $general_stats['pending_students'] > 0)
        <div style="background-color: #fef3c7; border: 1px solid #f59e0b; border-radius: 5px; padding: 15px; margin-top: 20px;">
            <div style="font-weight: bold; color: #92400e; font-size: 14px; margin-bottom: 10px;">
                💡 Actions Recommandées
            </div>
            <div style="color: #92400e; font-size: 11px;">
                @if($general_stats['pending_enrollments'] > 0)
                    • Traiter les {{ $general_stats['pending_enrollments'] }} inscription(s) en attente<br>
                @endif
                @if($general_stats['pending_students'] > 0)
                    • Approuver les {{ $general_stats['pending_students'] }} compte(s) étudiant(s) en attente<br>
                @endif
                • Maintenir un délai de traitement inférieur à 5 jours ouvrables<br>
                • Surveiller l'évolution mensuelle des inscriptions<br>
                • Optimiser les filières avec un faible taux d'inscription
            </div>
        </div>
    @endif

    <!-- Pied de page -->
    <div class="footer">
        <p>
            <strong>Système de Gestion des Enrôlements Académiques</strong><br>
            Rapport généré le {{ $generated_at }} | 
            Période: {{ $period }} | 
            Données consolidées automatiquement
        </p>
    </div>
</body>
</html>