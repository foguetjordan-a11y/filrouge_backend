<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\EnrollementController;
use App\Http\Controllers\FiliereController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MatriculeController;
use App\Http\Controllers\NiveauController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuitusController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use Prometheus\RenderTextFormat;

// ─────────────────────────────────────────────────────────────────
// Routes publiques (sans authentification)
// ─────────────────────────────────────────────────────────────────

Route::get('/test', fn () => response()->json(['success' => true, 'message' => 'API OK']));

// ─────────────────────────────────────────────────────────────────
// Metriques Prometheus — format text/plain
// ─────────────────────────────────────────────────────────────────
Route::get('/metrics', function () {
    $uptime  = time() - filemtime(base_path('bootstrap/cache'));
    $users   = 0;
    $enrolls = 0;
    $payments = 0;

    try {
        $users    = \App\Models\User::count();
        $enrolls  = \App\Models\Enrollement::count();
        $payments = \App\Models\Payment::count();
    } catch (\Exception $e) {
        // DB non disponible — on retourne quand meme les metriques de base
    }

    $metrics = implode("\n", [
        '# HELP laravel_app_info Informations sur l\'application',
        '# TYPE laravel_app_info gauge',
        'laravel_app_info{version="' . app()->version() . '",env="' . app()->environment() . '"} 1',
        '',
        '# HELP laravel_users_total Nombre total d\'utilisateurs',
        '# TYPE laravel_users_total gauge',
        "laravel_users_total {$users}",
        '',
        '# HELP laravel_enrollements_total Nombre total d\'enrollements',
        '# TYPE laravel_enrollements_total gauge',
        "laravel_enrollements_total {$enrolls}",
        '',
        '# HELP laravel_payments_total Nombre total de paiements',
        '# TYPE laravel_payments_total gauge',
        "laravel_payments_total {$payments}",
        '',
        '# HELP laravel_up Application disponible (1=OK, 0=KO)',
        '# TYPE laravel_up gauge',
        'laravel_up 1',
        '',
    ]);

    return response($metrics, 200)
        ->header('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');
});

// Health check & monitoring
Route::get('/health', function () {    $checks = ['database' => false, 'storage' => false, 'cache' => false];

    try {
        DB::connection()->getPdo();
        $checks['database'] = true;
    } catch (\Exception $e) {
        Log::error('Health check - DB KO', ['error' => $e->getMessage()]);
    }

    try {
        $checks['storage'] = is_writable(storage_path('logs'));
    } catch (\Exception $e) {
        Log::error('Health check - Storage KO', ['error' => $e->getMessage()]);
    }

    try {
        Cache::put('health_check', true, 5);
        $checks['cache'] = Cache::get('health_check') === true;
    } catch (\Exception $e) {
        $checks['cache'] = true;
    }

    $allOk = !in_array(false, $checks, true);

    return response()->json([
        'status'  => $allOk ? 'OK' : 'DEGRADED',
        'version' => app()->version(),
        'env'     => app()->environment(),
        'checks'  => $checks,
        'time'    => now()->toISOString(),
    ], $allOk ? 200 : 503);
});

// Authentification
Route::post('/create', [UserController::class, 'create']);
Route::post('/login', [AuthController::class, 'login']);

// Réinitialisation de mot de passe
Route::post('/forgot-password', [\App\Http\Controllers\PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password', [\App\Http\Controllers\PasswordResetController::class, 'resetPassword']);
Route::post('/verify-reset-token', [\App\Http\Controllers\PasswordResetController::class, 'verifyResetToken']);

// ─────────────────────────────────────────────────────────────────
// Routes authentifiées (tous rôles)
// ─────────────────────────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // Méthodes de paiement & détails (accès commun)
    Route::get('/payment-methods', [PaymentController::class, 'paymentMethods']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);

    // Matricules (commun)
    Route::post('/matricules/validate', [MatriculeController::class, 'validate']);
    Route::get('/matricules/search', [MatriculeController::class, 'search']);
});

// ─────────────────────────────────────────────────────────────────
// Routes ADMIN
// ─────────────────────────────────────────────────────────────────

Route::middleware(['auth:sanctum', 'check.role:admin'])->group(function () {

    // Utilisateurs
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::patch('/users/{id}/approve', [UserController::class, 'approve']);
    Route::patch('/users/{id}/status', [UserController::class, 'updateStatus']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // Départements
    Route::get('/admin/departements', [DepartementController::class, 'index']);
    Route::post('/admin/departements', [DepartementController::class, 'store']);
    Route::put('/admin/departements/{id}', [DepartementController::class, 'update']);
    Route::delete('/admin/departements/{id}', [DepartementController::class, 'destroy']);

    // Alias /departments (compatibilité)
    Route::get('/departments', [DepartementController::class, 'index']);
    Route::post('/departments', [DepartementController::class, 'store']);
    Route::get('/departments/{id}', [DepartementController::class, 'show']);
    Route::put('/departments/{id}', [DepartementController::class, 'update']);
    Route::delete('/departments/{id}', [DepartementController::class, 'destroy']);

    // Filières
    Route::get('/admin/filieres', [FiliereController::class, 'index']);
    Route::post('/admin/filieres', [FiliereController::class, 'store']);
    Route::put('/admin/filieres/{id}', [FiliereController::class, 'update']);
    Route::delete('/admin/filieres/{id}', [FiliereController::class, 'destroy']);

    // Niveaux
    Route::get('/admin/niveaux', [NiveauController::class, 'index']);
    Route::post('/admin/niveaux', [NiveauController::class, 'store']);
    Route::get('/admin/niveaux/{id}', [NiveauController::class, 'show']);
    Route::put('/admin/niveaux/{id}', [NiveauController::class, 'update']);
    Route::delete('/admin/niveaux/{id}', [NiveauController::class, 'destroy']);

    // Années académiques
    Route::get('/admin/academic-years', [AcademicYearController::class, 'index']);
    Route::post('/admin/academic-years', [AcademicYearController::class, 'store']);
    Route::patch('/admin/academic-years/{id}/activate', [AcademicYearController::class, 'setActive']);

    // Inscriptions
    Route::patch('/inscriptions/{id}/approve', [InscriptionController::class, 'approve']);
    Route::patch('/inscriptions/{id}/reject', [InscriptionController::class, 'reject']);

    // Notes
    Route::post('/notes', [NoteController::class, 'store']);

    // Enrollements admin
    Route::get('/admin/enrollements', [EnrollementController::class, 'index']);
    Route::put('/enrollements/{id}/valider', [EnrollementController::class, 'validateEnrollement']);
    Route::put('/enrollements/{id}/reject', [EnrollementController::class, 'rejectEnrollement']);

    // Dashboard admin
    Route::get('/dashboard/admin', [DashboardController::class, 'admin']);

    // Quitus admin
    Route::get('/admin/quitus', [QuitusController::class, 'index']);
    Route::post('/admin/quitus/generate/{enrollmentId}', [QuitusController::class, 'generateForEnrollment']);
    Route::get('/admin/quitus/download/{userId}', [QuitusController::class, 'downloadForStudent']);

    // Rapports PDF — routes statiques AVANT les dynamiques
    Route::get('/admin/reports/students-enrolled', [ReportController::class, 'studentsEnrolledReport']);
    Route::get('/admin/reports/pending-applications', [ReportController::class, 'pendingApplicationsReport']);
    Route::get('/admin/reports/global-stats', [ReportController::class, 'globalStatsReport']);
    Route::get('/admin/reports/filiere/{id}', [ReportController::class, 'filiereReport']);
    Route::get('/admin/reports/departement/{id}', [ReportController::class, 'departementReport']);
    Route::get('/admin/reports/niveau/{id}', [ReportController::class, 'niveauReport']);

    // Paiements admin — routes statiques AVANT /{id}
    Route::get('/admin/payments/pending', [PaymentController::class, 'pendingApprovals']);
    Route::get('/admin/payments/pending-verification', [PaymentController::class, 'pendingVerification']);
    Route::get('/admin/payments/statistics', [PaymentController::class, 'statistics']);
    Route::get('/admin/payments', [PaymentController::class, 'index']);
    Route::post('/admin/payments/{id}/approve', [PaymentController::class, 'approve']);
    Route::post('/admin/payments/{id}/reject', [PaymentController::class, 'reject']);
    Route::post('/admin/payments/{id}/verify', [PaymentController::class, 'adminVerify']);
    Route::get('/admin/payments/{id}/receipt', [PaymentController::class, 'downloadReceiptAdmin']);

    // Factures admin
    Route::post('/admin/invoices/generate', [InvoiceController::class, 'generate']);
    Route::get('/admin/invoices', [InvoiceController::class, 'index']);
    Route::post('/admin/invoices/{id}/send', [InvoiceController::class, 'markAsSent']);
    Route::delete('/admin/invoices/{id}', [InvoiceController::class, 'cancel']);

    // Matricules admin — routes statiques AVANT /{userId}
    Route::get('/admin/matricules/search', [MatriculeController::class, 'search']);
    Route::get('/admin/matricules/statistics', [MatriculeController::class, 'statistics']);
    Route::get('/admin/matricules/without-matricule', [MatriculeController::class, 'withoutMatricule']);
    Route::post('/admin/matricules/generate', [MatriculeController::class, 'generate']);
    Route::get('/admin/matricules/eligibility/{userId}', [MatriculeController::class, 'checkEligibility']);

    // Profils admin
    Route::get('/admin/profiles/incomplete', [ProfileController::class, 'incompleteProfiles']);
});

// ─────────────────────────────────────────────────────────────────
// Routes ÉTUDIANT
// ─────────────────────────────────────────────────────────────────

Route::middleware(['auth:sanctum', 'check.role:etudiant', 'check.approved'])->group(function () {

    // Inscriptions
    Route::post('/inscriptions', [InscriptionController::class, 'store']);
    Route::get('/inscriptions', [InscriptionController::class, 'index']);

    // Enrollements
    Route::post('/enrollements', [EnrollementController::class, 'store']);
    Route::get('/enrollements', [EnrollementController::class, 'mesEnrollements']);
    Route::post('/enrollements/{id}/quitus', [EnrollementController::class, 'uploadQuitus']);
    Route::get('/enrollements/{id}/quitus', [EnrollementController::class, 'downloadQuitus']);

    // Notes & dashboard
    Route::get('/mes-notes', [NoteController::class, 'mesNotes']);
    Route::get('/dashboard/etudiant', [DashboardController::class, 'etudiant']);

    // Quitus étudiant
    Route::get('/quitus/download', [QuitusController::class, 'download']);

    // Données de référence
    Route::get('/filieres', [FiliereController::class, 'index']);
    Route::get('/niveaux', [NiveauController::class, 'index']);
    Route::get('/academic-years', [AcademicYearController::class, 'index']);

    // Paiements étudiant — routes statiques AVANT /{id}
    Route::post('/payments/initiate', [PaymentController::class, 'initiate']);
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments/{id}/confirm', [PaymentController::class, 'confirm']);
    Route::post('/payments/{id}/student-confirm', [PaymentController::class, 'studentConfirm']);
    Route::post('/payments/{id}/cancel', [PaymentController::class, 'cancel']);
    Route::get('/payments/{id}/receipt', [PaymentController::class, 'downloadReceipt']);

    // Factures étudiant
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{id}/download', [InvoiceController::class, 'download']);

    // Profil étudiant
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::patch('/profile/basic', [ProfileController::class, 'updateBasicInfo']);
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);
    Route::post('/profile/password', [ProfileController::class, 'changePassword']);
    Route::get('/profile/completion', [ProfileController::class, 'checkCompletion']);

    // Matricule étudiant
    Route::get('/matricule/my', [MatriculeController::class, 'myMatricule']);
    Route::get('/matricule/eligibility', [MatriculeController::class, 'checkEligibility']);
});


Route::get('/metrics', function () {

    $registry = new CollectorRegistry(new InMemory());

    // Créer un compteur
    $counter = $registry->registerCounter(
        'app',
        'http_requests_total',
        'Total HTTP Requests',
        ['method', 'endpoint']
    );

    // Incrémenter
    $counter->inc(['GET', '/api/metrics']);

    // Affichage
    $renderer = new RenderTextFormat();
    return response($renderer->render($registry->getMetricFamilySamples()))
        ->header('Content-Type', RenderTextFormat::MIME_TYPE);
});

