<?php

namespace App\Services;

use App\Models\User;
use App\Models\Enrollement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MatriculeService
{
    /**
     * Générer un matricule unique pour un étudiant
     */
    public function generateMatricule(User $user, Enrollement $enrollement = null): string
    {
        if ($user->role !== 'etudiant') {
            throw new \Exception('Le matricule ne peut être généré que pour les étudiants');
        }

        // Vérifier si l'étudiant a déjà un matricule pour un enrôlement validé
        $existingEnrollmentWithMatricule = $user->enrollements()
            ->where('statut', 'valide')
            ->whereNotNull('matricule_etudiant')
            ->first();
            
        if ($existingEnrollmentWithMatricule) {
            throw new \Exception('L\'étudiant a déjà un matricule: ' . $existingEnrollmentWithMatricule->matricule_etudiant);
        }

        if (!$user->isProfileComplete()) {
            throw new \Exception('Le profil de l\'étudiant doit être complet pour générer un matricule');
        }

        // Si pas d'enrôlement fourni, prendre le plus récent validé et payé
        if (!$enrollement) {
            $enrollement = $user->enrollements()
                               ->where('statut', 'valide')
                               ->where('payment_status', 'paid')
                               ->orderBy('created_at', 'desc')
                               ->first();
        }

        if (!$enrollement) {
            throw new \Exception('Aucun enrôlement validé et payé trouvé pour cet étudiant');
        }

        // Vérifier que l'enrôlement est payé
        if (!$enrollement->isPaid()) {
            throw new \Exception('Le paiement doit être confirmé avant la génération du matricule');
        }

        return DB::transaction(function () use ($user, $enrollement) {
            // Composants du matricule
            $institution = 'IUC'; // Institut Universitaire du Cameroun (exemple)
            $annee = date('Y');
            $departementCode = $this->getDepartementCode($enrollement->filiere->departement);
            $niveauCode = $this->getNiveauCode($enrollement->niveau);
            $sequence = $this->getNextSequence($annee, $departementCode, $niveauCode);

            // Format: IUC-2025-INF-L1-0001
            $matricule = sprintf(
                '%s-%s-%s-%s-%04d',
                $institution,
                $annee,
                $departementCode,
                $niveauCode,
                $sequence
            );

            // Vérifier l'unicité (sécurité supplémentaire)
            $attempts = 0;
            $originalMatricule = $matricule;
            
            while (Enrollement::where('matricule_etudiant', $matricule)->exists() && $attempts < 10) {
                $attempts++;
                $sequence = $this->getNextSequence($annee, $departementCode, $niveauCode);
                $matricule = sprintf(
                    '%s-%s-%s-%s-%04d',
                    $institution,
                    $annee,
                    $departementCode,
                    $niveauCode,
                    $sequence
                );
            }

            if ($attempts >= 10) {
                throw new \Exception('Impossible de générer un matricule unique après 10 tentatives');
            }

            // Mettre à jour l'enrôlement avec le matricule généré
            $enrollement->update([
                'matricule_etudiant' => $matricule
            ]);

            Log::info("Matricule généré pour l'étudiant {$user->id} dans l'enrôlement {$enrollement->id}: {$matricule}");

            return $matricule;
        });
    }

    /**
     * Obtenir le code du département
     */
    private function getDepartementCode($departement): string
    {
        if (!$departement) {
            return 'GEN'; // Général
        }

        $codes = [
            'informatique' => 'INF',
            'mathématiques' => 'MAT',
            'physique' => 'PHY',
            'chimie' => 'CHI',
            'biologie' => 'BIO',
            'génie civil' => 'GCI',
            'génie électrique' => 'GEL',
            'génie mécanique' => 'GME',
            'économie' => 'ECO',
            'gestion' => 'GES',
            'droit' => 'DRT',
            'lettres' => 'LET',
            'langues' => 'LAN',
            'médecine' => 'MED',
            'pharmacie' => 'PHA',
            'sciences sociales' => 'SSO',
            'arts' => 'ART',
            'architecture' => 'ARC',
            'agriculture' => 'AGR',
            'environnement' => 'ENV'
        ];

        $nomDepartement = strtolower($departement->nom);
        
        // Recherche exacte
        if (isset($codes[$nomDepartement])) {
            return $codes[$nomDepartement];
        }

        // Recherche partielle
        foreach ($codes as $nom => $code) {
            if (str_contains($nomDepartement, $nom) || str_contains($nom, $nomDepartement)) {
                return $code;
            }
        }

        // Générer un code à partir des 3 premières lettres
        return strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $nomDepartement), 0, 3));
    }

    /**
     * Obtenir le code du niveau
     */
    private function getNiveauCode($niveau): string
    {
        if (!$niveau) {
            return 'L1'; // Par défaut
        }

        $codes = [
            'licence 1' => 'L1',
            'licence 2' => 'L2',
            'licence 3' => 'L3',
            'master 1' => 'M1',
            'master 2' => 'M2',
            'doctorat 1' => 'D1',
            'doctorat 2' => 'D2',
            'doctorat 3' => 'D3',
            'bts 1' => 'B1',
            'bts 2' => 'B2',
            'dut 1' => 'D1',
            'dut 2' => 'D2'
        ];

        $nomNiveau = strtolower($niveau->nom);
        
        // Recherche exacte
        if (isset($codes[$nomNiveau])) {
            return $codes[$nomNiveau];
        }

        // Recherche par code existant
        if (isset($niveau->code) && !empty($niveau->code)) {
            return strtoupper($niveau->code);
        }

        // Recherche partielle
        foreach ($codes as $nom => $code) {
            if (str_contains($nomNiveau, $nom) || str_contains($nom, $nomNiveau)) {
                return $code;
            }
        }

        // Extraction automatique (L1, M2, etc.)
        if (preg_match('/([LMD])(\d+)/i', $nomNiveau, $matches)) {
            return strtoupper($matches[1] . $matches[2]);
        }

        return 'L1'; // Par défaut
    }

    /**
     * Obtenir le prochain numéro de séquence
     */
    private function getNextSequence(string $annee, string $departementCode, string $niveauCode): int
    {
        $pattern = "IUC-{$annee}-{$departementCode}-{$niveauCode}-%";
        
        $lastEnrollment = Enrollement::where('matricule_etudiant', 'LIKE', $pattern)
                            ->orderBy('matricule_etudiant', 'desc')
                            ->first();

        if (!$lastEnrollment) {
            return 1;
        }

        // Extraire le numéro de séquence du dernier matricule
        if (preg_match('/(\d+)$/', $lastEnrollment->matricule_etudiant, $matches)) {
            return (int)$matches[1] + 1;
        }

        return 1;
    }

    /**
     * Valider le format d'un matricule
     */
    public function validateMatriculeFormat(string $matricule): bool
    {
        // Format: IUC-2025-INF-L1-0001
        $pattern = '/^[A-Z]{2,4}-\d{4}-[A-Z]{2,4}-[A-Z]\d+-\d{4}$/';
        return preg_match($pattern, $matricule) === 1;
    }

    /**
     * Générer automatiquement le matricule après paiement confirmé
     */
    public function generateAfterPayment(User $user, Enrollement $enrollement): ?string
    {
        try {
            if (!$user->canGenerateMatricule()) {
                Log::info("Conditions non remplies pour générer le matricule de l'utilisateur {$user->id}");
                return null;
            }

            // Vérifier que l'enrôlement est payé et validé
            if (!$enrollement->isPaid() || $enrollement->statut !== 'valide') {
                Log::info("Enrôlement {$enrollement->id} non payé ou non validé, matricule non généré");
                return null;
            }

            // Vérifier si le matricule n'existe pas déjà
            if (!empty($enrollement->matricule_etudiant)) {
                Log::info("Matricule déjà existant pour l'enrôlement {$enrollement->id}: {$enrollement->matricule_etudiant}");
                return $enrollement->matricule_etudiant;
            }

            $matricule = $this->generateMatricule($user, $enrollement);
            
            Log::info("Matricule généré automatiquement après paiement", [
                'user_id' => $user->id,
                'enrollement_id' => $enrollement->id,
                'matricule' => $matricule
            ]);

            return $matricule;

        } catch (\Exception $e) {
            Log::error("Erreur lors de la génération automatique du matricule", [
                'user_id' => $user->id,
                'enrollement_id' => $enrollement->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Obtenir les statistiques des matricules
     */
    public function getMatriculeStatistics(): array
    {
        $totalEtudiants = User::etudiants()->count();
        
        // Compter les enrôlements avec matricule
        $enrollementsAvecMatricule = Enrollement::whereNotNull('matricule_etudiant')->count();
        $enrollementsSansMatricule = Enrollement::where('statut', 'valide')
            ->where('payment_status', 'paid')
            ->whereNull('matricule_etudiant')
            ->count();

        // Statistiques par année (basé sur la création de l'enrôlement)
        $matriculesParAnnee = Enrollement::whereNotNull('matricule_etudiant')
            ->selectRaw('YEAR(created_at) as annee, COUNT(*) as count')
            ->groupBy('annee')
            ->orderBy('annee', 'desc')
            ->get();

        // Statistiques par département (basé sur le matricule dans enrollements)
        $matriculesParDepartement = Enrollement::whereNotNull('matricule_etudiant')
            ->selectRaw('SUBSTRING_INDEX(SUBSTRING_INDEX(matricule_etudiant, "-", 3), "-", -1) as departement_code, COUNT(*) as count')
            ->groupBy('departement_code')
            ->orderBy('count', 'desc')
            ->get();

        return [
            'total_etudiants' => $totalEtudiants,
            'enrollements_avec_matricule' => $enrollementsAvecMatricule,
            'enrollements_sans_matricule' => $enrollementsSansMatricule,
            'pourcentage_avec_matricule' => ($enrollementsAvecMatricule + $enrollementsSansMatricule) > 0 ? 
                round(($enrollementsAvecMatricule / ($enrollementsAvecMatricule + $enrollementsSansMatricule)) * 100, 2) : 0,
            'par_annee' => $matriculesParAnnee,
            'par_departement' => $matriculesParDepartement
        ];
    }
}