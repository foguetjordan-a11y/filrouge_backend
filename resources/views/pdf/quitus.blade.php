<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quitus Académique</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        
        .institution {
            font-size: 18px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .title {
            font-size: 28px;
            font-weight: bold;
            color: #1e40af;
            margin: 30px 0;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .content {
            line-height: 1.8;
            margin: 30px 0;
        }
        
        .student-info {
            background: #f1f5f9;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px dotted #ccc;
        }
        
        .info-label {
            font-weight: bold;
            color: #374151;
            width: 40%;
        }
        
        .info-value {
            color: #1f2937;
            width: 55%;
        }
        
        .declaration {
            text-align: justify;
            margin: 30px 0;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
            width: 45%;
        }
        
        .signature-line {
            border-top: 2px solid #333;
            margin-top: 60px;
            padding-top: 10px;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
        
        .reference {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 12px;
            color: #666;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(37, 99, 235, 0.1);
            z-index: -1;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="watermark">QUITUS OFFICIEL</div>
    
    <div class="reference">
        Réf: {{ $quitus->reference }}
    </div>
    
    <div class="container">
        <div class="header">
            <div class="logo">🎓 UNIVERSITÉ EXCELLENCE</div>
            <div class="institution">Institut Supérieur de Formation</div>
            <div class="institution">Service de la Scolarité</div>
        </div>
        
        <div class="title">Quitus Académique</div>
        
        <div class="content">
            <p>Le Directeur de l'Institut Supérieur de Formation certifie par la présente que :</p>
            
            <div class="student-info">
                <div class="info-row">
                    <span class="info-label">Nom et Prénom :</span>
                    <span class="info-value">{{ strtoupper($user->name) }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Matricule :</span>
                    <span class="info-value">{{ 'MAT' . str_pad($user->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                
                @if($user->enrollements && $user->enrollements->count() > 0)
                    @php $enrollement = $user->enrollements->where('statut', 'valide')->first() ?? $user->enrollements->first() @endphp
                    @if($enrollement)
                        <div class="info-row">
                            <span class="info-label">Département :</span>
                            <span class="info-value">{{ $enrollement->filiere->departement->nom ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Filière :</span>
                            <span class="info-value">{{ $enrollement->filiere->nom ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Niveau :</span>
                            <span class="info-value">{{ $enrollement->niveau->libelle ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Année Académique :</span>
                            <span class="info-value">{{ $enrollement->anneeAcademique->name ?? 'N/A' }}</span>
                        </div>
                    @endif
                @endif
                
                <div class="info-row">
                    <span class="info-label">Date d'émission :</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($quitus->date_emission)->format('d/m/Y') }}</span>
                </div>
            </div>
            
            <div class="declaration">
                <p><strong>CERTIFIE</strong> que l'étudiant(e) susmentionné(e) a satisfait à toutes les exigences administratives et académiques requises pour l'année académique en cours. Ce quitus atteste que l'intéressé(e) est en règle avec l'administration de l'établissement.</p>
                
                <p>Ce document est délivré pour servir et valoir ce que de droit.</p>
                
                <p><strong>Statut du quitus :</strong> <span style="color: #059669; font-weight: bold;">{{ strtoupper($quitus->statut) }}</span></p>
            </div>
        </div>
        
        <div class="signature-section">
            <div class="signature-box">
                <div>L'Étudiant(e)</div>
                <div class="signature-line">Signature</div>
            </div>
            
            <div class="signature-box">
                <div>Le Directeur</div>
                <div class="signature-line">Signature et Cachet</div>
            </div>
        </div>
        
        <div class="footer">
            <p>Document généré automatiquement le {{ now()->format('d/m/Y à H:i') }}</p>
            <p>Ce document est authentique et vérifiable avec la référence : <strong>{{ $quitus->reference }}</strong></p>
        </div>
    </div>
</body>
</html>