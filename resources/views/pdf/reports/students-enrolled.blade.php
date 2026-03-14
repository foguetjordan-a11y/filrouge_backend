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
        
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .stats-row {
            display: table-row;
        }
        
        .stats-cell {
            display: table-cell;
            text-align: center;
            padding: 10px;
            background-color: #e5e7eb;
            border: 1px solid #d1d5db;
        }
        
        .stats-cell.first {
            border-radius: 5px 0 0 5px;
        }
        
        .stats-cell.last {
            border-radius: 0 5px 5px 0;
        }
        
        .stats-number {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
        }
        
        .stats-label {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
        }
        
        tr:nth-child(even) {
            background-color: #f9fafb;
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
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">Système de Gestion des Enrôlements Académiques</div>
    </div>

    <!-- Informations du rapport -->
    <div class="info-section">
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Date de génération :</div>
                <div class="info-value">{{ $generated_at }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Nombre total d'étudiants :</div>
                <div class="info-value">{{ $total_count }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Filière :</div>
                <div class="info-value">{{ $filters['filiere'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Niveau :</div>
                <div class="info-value">{{ $filters['niveau'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Statut :</div>
                <div class="info-value">{{ $filters['statut'] }}</div>
            </div>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="stats-grid">
        <div class="stats-row">
            <div class="stats-cell first">
                <div class="stats-number">{{ $total_count }}</div>
                <div class="stats-label">TOTAL ÉTUDIANTS</div>
            </div>
            <div class="stats-cell">
                <div class="stats-number">{{ $students->where('status', 'approved')->count() }}</div>
                <div class="stats-label">APPROUVÉS</div>
            </div>
            <div class="stats-cell">
                <div class="stats-number">{{ $students->flatMap->enrollements->where('statut', 'valide')->count() }}</div>
                <div class="stats-label">ENRÔLÉS</div>
            </div>
            <div class="stats-cell last">
                <div class="stats-number">{{ $students->flatMap->enrollements->where('statut', 'en_attente')->count() }}</div>
                <div class="stats-label">EN ATTENTE</div>
            </div>
        </div>
    </div>

    <!-- Liste des étudiants -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 25%">Nom Complet</th>
                <th style="width: 25%">Email</th>
                <th style="width: 20%">Filière</th>
                <th style="width: 15%">Niveau</th>
                <th style="width: 10%">Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $student->prenom }} {{ $student->nom }}</strong>
                        @if($student->telephone)
                            <br><small>{{ $student->telephone }}</small>
                        @endif
                    </td>
                    <td>{{ $student->email }}</td>
                    <td>
                        @if($student->enrollements->isNotEmpty())
                            {{ $student->enrollements->first()->filiere->nom ?? 'N/A' }}
                            @if($student->enrollements->first()->filiere->departement)
                                <br><small>{{ $student->enrollements->first()->filiere->departement->nom }}</small>
                            @endif
                        @else
                            <em>Aucune inscription</em>
                        @endif
                    </td>
                    <td>
                        @if($student->enrollements->isNotEmpty())
                            {{ $student->enrollements->first()->niveau->libelle ?? 'N/A' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($student->enrollements->isNotEmpty())
                            @php
                                $statut = $student->enrollements->first()->statut;
                            @endphp
                            <span class="status-badge status-{{ $statut }}">
                                @switch($statut)
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
                                        {{ $statut }}
                                @endswitch
                            </span>
                        @else
                            <span class="status-badge status-en-attente">Non inscrit</span>
                        @endif
                    </td>
                </tr>
                
                @if(($index + 1) % 25 == 0 && $index + 1 < $students->count())
                    </tbody>
                    </table>
                    <div class="page-break"></div>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 25%">Nom Complet</th>
                                <th style="width: 25%">Email</th>
                                <th style="width: 20%">Filière</th>
                                <th style="width: 15%">Niveau</th>
                                <th style="width: 10%">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                @endif
            @endforeach
        </tbody>
    </table>

    @if($students->isEmpty())
        <div style="text-align: center; padding: 40px; color: #666;">
            <p>Aucun étudiant trouvé avec les critères sélectionnés.</p>
        </div>
    @endif

    <!-- Pied de page -->
    <div class="footer">
        <p>
            <strong>Système de Gestion des Enrôlements Académiques</strong><br>
            Rapport généré le {{ $generated_at }} | 
            Total: {{ $total_count }} étudiant(s) | 
            Page générée automatiquement
        </p>
    </div>
</body>
</html>