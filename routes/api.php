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

// ─────────────────────────────────────────────────────────────────
// Routes publiques (sans authentification)
// ─────────────────────────────────────────────────────────────────

Route::get('/test', fn () => response()->json(['success' => true, 'message' => 'API OK']));

// ─────────────────────────────────────────────────────────────────
// Metriques Prometheus — format text/plain (Prometheus scrape)
// ─────────────────────────────────────────────────────────────────
Route::get('/metrics', function () {
    $users    = 0;
    $enrolls  = 0;
    $payments = 0;
    $dbUp     = 0;
    $memUsage = memory_get_usage(true);
    $memPeak  = memory_get_peak_usage(true);

    try {
        DB::connection()->getPdo();
        $dbUp     = 1;
        $users    = \App\Models\User::count();
        $enrolls  = \App\Models\Enrollement::count();
        $payments = \App\Models\Payment::count();
    } catch (\Exception $e) {
        // DB non disponible
    }

    $lines = [
        // Info application
        '# HELP laravel_app_info Informations sur l\'application Laravel',
        '# TYPE laravel_app_info gauge',
        'laravel_app_info{version="' . app()->version() . '",env="' . app()->environment() . '"} 1',
        '',
        // Disponibilite
        '# HELP laravel_up Application disponible (1=OK, 0=KO)',
        '# TYPE laravel_up gauge',
        'laravel_up 1',
        '',
        // Base de donnees
        '# HELP laravel_database_up Base de donnees disponible (1=OK, 0=KO)',
        '# TYPE laravel_database_up gauge',
        "laravel_database_up {$dbUp}",
        '',
        // Memoire
        '# HELP laravel_memory_usage_bytes Memoire PHP utilisee en bytes',
        '# TYPE laravel_memory_usage_bytes gauge',
        "laravel_memory_usage_bytes {$memUsage}",
        '',
        '# HELP laravel_memory_peak_bytes Pic memoire PHP en bytes',
        '# TYPE laravel_memory_peak_bytes gauge',
        "laravel_memory_peak_bytes {$memPeak}",
        '',
        // Donnees metier
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
    ];

    return response(implode("\n", $lines), 200)
        ->header('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');
});

// ─────────────────────────────────────────────────────────────────
// Health check
// ─────────────────────────────────────────────────────────────────
Route::get('/health', function () {
    $checks = ['database' => false, 'storage' => false, 'cache' => false];

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

// ─────────────────────────────────────────────────────────────────
// Authentification publique
// ─────────────────────────────────────────────────────────────────
Route::post('/create', [UserController::class, 'create']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password', [\App\Http\Controllers\PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password', [\App\Http\Controllers\PasswordResetController::class, 'resetPassword']);
Route::post('/verify-reset-token', [\App\Http\Controllers\PasswordResetController::class, 'verifyResetToken']);

// ─────────────────────────────────────────────────────────────────
// Routes authentifiées (tous rôles)
// ─────────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    Route::get('/payment-methods', [PaymentController::class, 'paymentMethods']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);

    Route::post('/matricules/validate', [MatriculeController::class, 'validate']);
    Route::get('/matricules/search', [MatriculeController::class, 'search']);
});

// ─────────────────────────────────────────────────────────────────
// Routes ADMIN
// ─────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'check.role:admin'])->group(function () {

    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::patch('/users/{id}/approve', [UserController::class, 'approve']);
    Route::patch('/users/{id}/status', [UserController::class, 'updateStatus']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    Route::get('/admin/departements', [DepartementController::class, 'index']);
    Route::post('/admin/departements', [DepartementController::class, 'store']);
    Route::put('/admin/departements/{id}', [DepartementController::class, 'update']);
    Route::delete('/admin/departements/{id}', [DepartementController::class, 'destroy']);

    Route::get('/departments', [DepartementController::class, 'index']);
    Route::post('/departments', [DepartementController::class, 'store']);
    Route::get('/departments/{id}', [DepartementController::class, 'show']);
    Route::put('/departments/{id}', [DepartementController::class, 'update']);
    Route::delete('/departments/{id}', [DepartementController::class, 'destroy']);

    Route::get('/admin/filieres', [FiliereController::class, 'index']);
    Route::post('/admin/filieres', [FiliereController::class, 'store']);
    Route::put('/admin/filieres/{id}', [FiliereController::class, 'update']);
    Route::delete('/admin/filieres/{id}', [FiliereController::class, 'destroy']);

    Route::get('/admin/niveaux', [NiveauController::class, 'index']);
    Route::post('/admin/niveaux', [NiveauController::class, 'store']);
    Route::get('/admin/niveaux/{id}', [NiveauController::class, 'show']);
    Route::put('/admin/niveaux/{id}', [NiveauController::class, 'update']);
    Route::delete('/admin/niveaux/{id}', [NiveauController::class, 'destroy']);

    Route::get('/admin/academic-years', [AcademicYearController::class, 'index']);
    Route::post('/admin/academic-years', [AcademicYearController::class, 'store']);
    Route::patch('/admin/academic-years/{id}/activate', [AcademicYearController::class, 'setActive']);

    Route::patch('/inscriptions/{id}/approve', [InscriptionController::class, 'approve']);
    Route::patch('/inscriptions/{id}/reject', [InscriptionController::class, 'reject']);

    Route::post('/notes', [NoteController::class, 'store']);

    Route::get('/admin/enrollements', [EnrollementController::class, 'index']);
    Route::put('/enrollements/{id}/valider', [EnrollementController::class, 'validateEnrollement']);
    Route::put('/enrollements/{id}/reject', [EnrollementController::class, 'rejectEnrollement']);

    Route::get('/dashboard/admin', [DashboardController::class, 'admin']);

    Route::get('/admin/quitus', [QuitusController::class, 'index']);
    Route::post('/admin/quitus/generate/{enrollmentId}', [QuitusController::class, 'generateForEnrollment']);
    Route::get('/admin/quitus/download/{userId}', [QuitusController::class, 'downloadForStudent']);

    Route::get('/admin/reports/students-enrolled', [ReportController::class, 'studentsEnrolledReport']);
    Route::get('/admin/reports/pending-applications', [ReportController::class, 'pendingApplicationsReport']);
    Route::get('/admin/reports/global-stats', [ReportController::class, 'globalStatsReport']);
    Route::get('/admin/reports/filiere/{id}', [ReportController::class, 'filiereReport']);
    Route::get('/admin/reports/departement/{id}', [ReportController::class, 'departementReport']);
    Route::get('/admin/reports/niveau/{id}', [ReportController::class, 'niveauReport']);

    Route::get('/admin/payments/pending', [PaymentController::class, 'pendingApprovals']);
    Route::get('/admin/payments/pending-verification', [PaymentController::class, 'pendingVerification']);
    Route::get('/admin/payments/statistics', [PaymentController::class, 'statistics']);
    Route::get('/admin/payments', [PaymentController::class, 'index']);
    Route::post('/admin/payments/{id}/approve', [PaymentController::class, 'approve']);
    Route::post('/admin/payments/{id}/reject', [PaymentController::class, 'reject']);
    Route::post('/admin/payments/{id}/verify', [PaymentController::class, 'adminVerify']);
    Route::get('/admin/payments/{id}/receipt', [PaymentController::class, 'downloadReceiptAdmin']);

    Route::post('/admin/invoices/generate', [InvoiceController::class, 'generate']);
    Route::get('/admin/invoices', [InvoiceController::class, 'index']);
    Route::post('/admin/invoices/{id}/send', [InvoiceController::class, 'markAsSent']);
    Route::delete('/admin/invoices/{id}', [InvoiceController::class, 'cancel']);

    Route::get('/admin/matricules/search', [MatriculeController::class, 'search']);
    Route::get('/admin/matricules/statistics', [MatriculeController::class, 'statistics']);
    Route::get('/admin/matricules/without-matricule', [MatriculeController::class, 'withoutMatricule']);
    Route::post('/admin/matricules/generate', [MatriculeController::class, 'generate']);
    Route::get('/admin/matricules/eligibility/{userId}', [MatriculeController::class, 'checkEligibility']);

    Route::get('/admin/profiles/incomplete', [ProfileController::class, 'incompleteProfiles']);
});

// ─────────────────────────────────────────────────────────────────
// Routes ÉTUDIANT
// ─────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'check.role:etudiant', 'check.approved'])->group(function () {

    Route::post('/inscriptions', [InscriptionController::class, 'store']);
    Route::get('/inscriptions', [InscriptionController::class, 'index']);

    Route::post('/enrollements', [EnrollementController::class, 'store']);
    Route::get('/enrollements', [EnrollementController::class, 'mesEnrollements']);
    Route::post('/enrollements/{id}/quitus', [EnrollementController::class, 'uploadQuitus']);
    Route::get('/enrollements/{id}/quitus', [EnrollementController::class, 'downloadQuitus']);

    Route::get('/mes-notes', [NoteController::class, 'mesNotes']);
    Route::get('/dashboard/etudiant', [DashboardController::class, 'etudiant']);
    Route::get('/quitus/download', [QuitusController::class, 'download']);

    Route::get('/filieres', [FiliereController::class, 'index']);
    Route::get('/niveaux', [NiveauController::class, 'index']);
    Route::get('/academic-years', [AcademicYearController::class, 'index']);

    Route::post('/payments/initiate', [PaymentController::class, 'initiate']);
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments/{id}/confirm', [PaymentController::class, 'confirm']);
    Route::post('/payments/{id}/student-confirm', [PaymentController::class, 'studentConfirm']);
    Route::post('/payments/{id}/cancel', [PaymentController::class, 'cancel']);
    Route::get('/payments/{id}/receipt', [PaymentController::class, 'downloadReceipt']);

    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{id}/download', [InvoiceController::class, 'download']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::patch('/profile/basic', [ProfileController::class, 'updateBasicInfo']);
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);
    Route::post('/profile/password', [ProfileController::class, 'changePassword']);
    Route::get('/profile/completion', [ProfileController::class, 'checkCompletion']);

    Route::get('/matricule/my', [MatriculeController::class, 'myMatricule']);
    Route::get('/matricule/eligibility', [MatriculeController::class, 'checkEligibility']);
});
