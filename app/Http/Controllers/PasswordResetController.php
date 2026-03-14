<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    /**
     * Demande de réinitialisation de mot de passe
     * POST /api/forgot-password
     */
    public function forgotPassword(Request $request)
    {
        try {
            // Validation de l'email
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email'
            ], [
                'email.required' => 'L\'adresse email est obligatoire',
                'email.email' => 'L\'adresse email n\'est pas valide',
                'email.exists' => 'Aucun compte n\'est associé à cette adresse email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier que l'utilisateur existe et est actif
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun compte n\'est associé à cette adresse email'
                ], 404);
            }

            // Vérifier le statut du compte
            if (isset($user->status) && $user->status === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce compte a été désactivé. Contactez l\'administration.'
                ], 403);
            }

            // Envoyer le lien de réinitialisation
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'success' => true,
                    'message' => 'Un lien de réinitialisation a été envoyé à votre adresse email. Vérifiez votre boîte de réception et vos spams.'
                ]);
            }

            // Gestion des erreurs d'envoi
            $errorMessages = [
                Password::RESET_THROTTLED => 'Trop de tentatives. Veuillez réessayer dans quelques minutes.',
                Password::INVALID_USER => 'Aucun compte n\'est associé à cette adresse email'
            ];

            return response()->json([
                'success' => false,
                'message' => $errorMessages[$status] ?? 'Erreur lors de l\'envoi du lien de réinitialisation'
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Erreur forgot password: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.'
            ], 500);
        }
    }

    /**
     * Réinitialisation du mot de passe
     * POST /api/reset-password
     */
    public function resetPassword(Request $request)
    {
        try {
            // Validation des données
            $validator = Validator::make($request->all(), [
                'token' => 'required|string',
                'email' => 'required|email',
                'password' => [
                    'required',
                    'confirmed',
                    PasswordRule::min(8)
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                ],
                'password_confirmation' => 'required'
            ], [
                'token.required' => 'Le token de réinitialisation est obligatoire',
                'email.required' => 'L\'adresse email est obligatoire',
                'email.email' => 'L\'adresse email n\'est pas valide',
                'password.required' => 'Le mot de passe est obligatoire',
                'password.confirmed' => 'La confirmation du mot de passe ne correspond pas',
                'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
                'password_confirmation.required' => 'La confirmation du mot de passe est obligatoire'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier que l'utilisateur existe
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            // Réinitialiser le mot de passe
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    // Déclencher l'événement de réinitialisation
                    event(new PasswordReset($user));
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'success' => true,
                    'message' => 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.'
                ]);
            }

            // Gestion des erreurs de réinitialisation
            $errorMessages = [
                Password::INVALID_TOKEN => 'Le lien de réinitialisation est invalide ou a expiré. Veuillez demander un nouveau lien.',
                Password::INVALID_USER => 'Utilisateur non trouvé',
                Password::RESET_THROTTLED => 'Trop de tentatives. Veuillez réessayer dans quelques minutes.'
            ];

            return response()->json([
                'success' => false,
                'message' => $errorMessages[$status] ?? 'Erreur lors de la réinitialisation du mot de passe'
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Erreur reset password: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.'
            ], 500);
        }
    }

    /**
     * Vérifier la validité d'un token de réinitialisation
     * POST /api/verify-reset-token
     */
    public function verifyResetToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string',
                'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier que l'utilisateur existe
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            // Vérifier la validité du token
            $tokenExists = \DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->where('token', Hash::check($request->token, \DB::table('password_reset_tokens')->where('email', $request->email)->value('token')) ? \DB::table('password_reset_tokens')->where('email', $request->email)->value('token') : 'invalid')
                ->where('created_at', '>', now()->subHours(1)) // Token valide 1 heure
                ->exists();

            if (!$tokenExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le lien de réinitialisation est invalide ou a expiré'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Token valide',
                'data' => [
                    'email' => $request->email,
                    'user_name' => $user->name ?? ($user->prenom . ' ' . $user->nom)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur verify token: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification du token'
            ], 500);
        }
    }
}