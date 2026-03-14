<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\EnrollementController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FiliereController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\NiveauController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QuitusController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MatriculeController;
use App\Http\Middleware\CheckApproved;
use App\Http\Middleware\CheckRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API OK'
    ]);
});


// -------------------------
// Routes publiques
// -------------------------
Route::post('/create', [UserController::class, 'create']); // Creation libre
Route::post('/login', [AuthController::class, 'login']);       // Login

// Routes de réinitialisation de mot de passe
Route::post('/forgot-password', [\App\Http\Controllers\PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password', [\App\Http\Controllers\PasswordResetController::class, 'resetPassword']);
Route::post('/verify-reset-token', [\App\Http\Controllers\PasswordResetController::class, 'verifyResetToken']);

// -------------------------
// Routes protégées
// -------------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// ADMIN
Route::middleware(['auth:sanctum', 'check.role:admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index']); // Liste tous les utilisateurs
    Route::get('/users/{id}', [UserController::class, 'show']); // afficher un utilisateur
    Route::patch('/users/{id}/approve', [UserController::class, 'approve']); // Approuver un compte
    Route::patch('/users/{id}/status', [UserController::class, 'updateStatus']); // Mettre à jour le statut
    Route::delete('/users/{id}', [UserController::class, 'destroy']); // Supprimer un étudiant
    Route::get('/admin/filieres', [FiliereController::class, 'index']);
    Route::post('/admin/filieres', [FiliereController::class, 'store']);
    Route::put('/admin/filieres/{id}', [FiliereController::class, 'update']);
    Route::delete('/admin/filieres/{id}', [FiliereController::class, 'destroy']);
    
    Route::get('/admin/departements', [DepartementController::class, 'index']);
    Route::post('/admin/departements', [DepartementController::class, 'store']);
    Route::put('/admin/departements/{id}', [DepartementController::class, 'update']);
    Route::delete('/admin/departements/{id}', [DepartementController::class, 'destroy']);
    Route::get('/admin/academic-years', [AcademicYearController::class, 'index']);
    Route::post('/admin/academic-years', [AcademicYearController::class, 'store']);
    Route::patch('/admin/academic-years/{id}/activate', [AcademicYearController::class, 'setActive']);
    Route::patch('/inscriptions/{id}/approve', [InscriptionController::class, 'approve']);
    Route::patch('/inscriptions/{id}/reject', [InscriptionController::class, 'reject']);
    Route::post('/notes', [NoteController::class, 'store']);
    Route::get('/admin/enrollements', [EnrollementController::class, 'index']); // Liste tous les enrollements pour admin
    Route::put('/enrollements/{id}/valider', [EnrollementController::class, 'validateEnrollement']);
    Route::put('/enrollements/{id}/reject', [EnrollementController::class, 'rejectEnrollement']);
    Route::get('/dashboard/admin', [DashboardController::class, 'admin']);
    Route::get('/departments', [DepartementController::class, 'index']);
    Route::post('/departments', [DepartementController::class, 'store']);
    Route::get('/departments/{id}', [DepartementController::class, 'show']);
    Route::put('/departments/{id}', [DepartementController::class, 'update']);
    Route::delete('/departments/{id}', [DepartementController::class, 'destroy']);
    Route::get('/admin/niveaux', [NiveauController::class, 'index']);
    Route::post('/admin/niveaux', [NiveauController::class, 'store']);
    Route::get('/admin/niveaux/{id}', [NiveauController::class, 'show']);
    Route::put('/admin/niveaux/{id}', [NiveauController::class, 'update']);
    Route::delete('/admin/niveaux/{id}', [NiveauController::class, 'destroy']);
    
    // Routes Quitus Admin
    Route::get('/admin/quitus', [QuitusController::class, 'index']); // Liste tous les quitus
    Route::post('/admin/quitus/generate/{enrollmentId}', [QuitusController::class, 'generateForEnrollment']); // Générer quitus pour un enrôlement
    Route::get('/admin/quitus/download/{userId}', [QuitusController::class, 'downloadForStudent']); // Télécharger quitus d'un étudiant
    
    // Routes Rapports PDF Admin
    Route::get('/admin/reports/students-enrolled', [\App\Http\Controllers\ReportController::class, 'studentsEnrolledReport']); // Liste étudiants enrôlés PDF
    Route::get('/admin/reports/pending-applications', [\App\Http\Controllers\ReportController::class, 'pendingApplicationsReport']); // Candidatures en attente PDF
    Route::get('/admin/reports/filiere/{id}', [\App\Http\Controllers\ReportController::class, 'filiereReport']); // Rapport par filière PDF
    Route::get('/admin/reports/departement/{id}', [\App\Http\Controllers\ReportController::class, 'departementReport']); // Rapport par département PDF
    Route::get('/admin/reports/niveau/{id}', [\App\Http\Controllers\ReportController::class, 'niveauReport']); // Rapport par niveau PDF
    Route::get('/admin/reports/global-stats', [\App\Http\Controllers\ReportController::class, 'globalStatsReport']); // Statistiques globales PDF
    
    // Routes Paiements Admin
    Route::get('/admin/payments', [PaymentController::class, 'index']); // Liste tous les paiements
    Route::get('/admin/payments/pending', [PaymentController::class, 'pendingApprovals']); // Paiements en attente d'approbation (legacy)
    Route::get('/admin/payments/pending-verification', [PaymentController::class, 'pendingVerification']); // Paiements en attente de vérification
    Route::post('/admin/payments/{id}/approve', [PaymentController::class, 'approve']); // Approuver un paiement (legacy - déprécié)
    Route::post('/admin/payments/{id}/reject', [PaymentController::class, 'reject']); // Rejeter un paiement (legacy - déprécié)
    Route::post('/admin/payments/{id}/verify', [PaymentController::class, 'adminVerify']); // Vérifier un paiement (nouvelle méthode)
    Route::get('/admin/payments/statistics', [PaymentController::class, 'statistics']); // Statistiques des paiements
    Route::get('/admin/payments/{id}/receipt', [PaymentController::class, 'downloadReceiptAdmin']); // Télécharger reçu (admin)
    Route::post('/admin/invoices/generate', [InvoiceController::class, 'generate']); // Générer une facture
    Route::get('/admin/invoices', [InvoiceController::class, 'index']); // Liste toutes les factures
    Route::post('/admin/invoices/{id}/send', [InvoiceController::class, 'markAsSent']); // Marquer facture comme envoyée
    Route::delete('/admin/invoices/{id}', [InvoiceController::class, 'cancel']); // Annuler une facture
    
    // Routes Matricules Admin
    Route::post('/admin/matricules/generate', [MatriculeController::class, 'generate']); // Générer un matricule
    Route::get('/admin/matricules/search', [MatriculeController::class, 'search']); // Rechercher par matricule
    Route::get('/admin/matricules/statistics', [MatriculeController::class, 'statistics']); // Statistiques matricules
    Route::get('/admin/matricules/without-matricule', [MatriculeController::class, 'withoutMatricule']); // Étudiants sans matricule
    Route::get('/admin/matricules/eligibility/{userId}', [MatriculeController::class, 'checkEligibility']); // Vérifier éligibilité
    
    // Routes Profils Admin
    Route::get('/admin/profiles/incomplete', [ProfileController::class, 'incompleteProfiles']); // Profils incomplets
});

// ÉTUDIANT
Route::middleware(['auth:sanctum', 'check.role:etudiant', 'check.approved'])->group(function () {
    Route::post('/inscriptions', [InscriptionController::class, 'store']);
    Route::get('/inscriptions', [InscriptionController::class, 'index']);
    Route::post('/enrollements', [EnrollementController::class, 'store']);
    Route::get('/enrollements', [EnrollementController::class, 'mesEnrollements']); // Enrollements de l'étudiant connecté
    Route::get('/mes-notes', [NoteController::class, 'mesNotes']);
    Route::get('/dashboard/etudiant', [DashboardController::class, 'etudiant']);
    Route::get('/quitus/download', [QuitusController::class, 'download']);
    Route::post('/enrollements/{id}/quitus', [EnrollementController::class, 'uploadQuitus']);
    Route::get('/enrollements/{id}/quitus', [EnrollementController::class, 'downloadQuitus']);
    
    // Routes pour les données de référence (étudiants)
    Route::get('/filieres', [FiliereController::class, 'index']); // Liste des filières pour inscription
    Route::get('/niveaux', [NiveauController::class, 'index']); // Liste des niveaux pour inscription
    Route::get('/academic-years', [AcademicYearController::class, 'index']); // Liste des années académiques
    
    // Routes Paiements Étudiant
    Route::get('/payments', [PaymentController::class, 'index']); // Mes paiements
    Route::post('/payments/initiate', [PaymentController::class, 'initiate']); // Initier un paiement
    Route::post('/payments/{id}/confirm', [PaymentController::class, 'confirm']); // Confirmer un paiement (legacy)
    Route::post('/payments/{id}/student-confirm', [PaymentController::class, 'studentConfirm']); // Confirmer paiement réel (nouvelle méthode)
    Route::post('/payments/{id}/cancel', [PaymentController::class, 'cancel']); // Annuler un paiement
    Route::get('/payments/{id}/receipt', [PaymentController::class, 'downloadReceipt']); // Télécharger reçu
    Route::get('/invoices', [InvoiceController::class, 'index']); // Mes factures
    Route::get('/invoices/{id}/download', [InvoiceController::class, 'download']); // Télécharger facture
    
    // Routes Profil Étudiant
    Route::get('/profile', [ProfileController::class, 'show']); // Mon profil
    Route::put('/profile', [ProfileController::class, 'update']); // Mettre à jour mon profil
    Route::patch('/profile/basic', [ProfileController::class, 'updateBasicInfo']); // Mise à jour info de base
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']); // Upload photo identité
    Route::post('/profile/password', [ProfileController::class, 'changePassword']); // Changer mot de passe
    Route::get('/profile/completion', [ProfileController::class, 'checkCompletion']); // Vérifier complétude profil
    
    // Routes Matricule Étudiant
    Route::get('/matricule/my', [MatriculeController::class, 'myMatricule']); // Mon matricule
    Route::get('/matricule/eligibility', [MatriculeController::class, 'checkEligibility']); // Mon éligibilité
});

// Routes communes (Admin + Étudiant)
Route::middleware('auth:sanctum')->group(function () {
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    
    // Paiements et Factures (accès selon les permissions)
    Route::get('/payment-methods', [PaymentController::class, 'paymentMethods']); // Méthodes de paiement disponibles
    Route::get('/payments/{id}', [PaymentController::class, 'show']); // Détails d'un paiement
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']); // Détails d'une facture
    
    // Matricules (accès selon les permissions)
    Route::post('/matricules/validate', [MatriculeController::class, 'validate']); // Valider format matricule
    Route::get('/matricules/search', [MatriculeController::class, 'search']); // Rechercher par matricule
});