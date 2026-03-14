<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "🔧 Réinitialisation du mot de passe admin\n";
echo "=========================================\n";

// Trouver l'admin
$admin = User::where('email', 'admin@test.com')->first();

if ($admin) {
    // Mettre à jour le mot de passe
    $admin->password = Hash::make('password123');
    $admin->status = 'approved'; // S'assurer qu'il est approuvé
    $admin->save();
    
    echo "✅ Mot de passe admin mis à jour\n";
    echo "   Email: admin@test.com\n";
    echo "   Mot de passe: password123\n";
    echo "   Statut: approved\n";
} else {
    echo "❌ Admin non trouvé avec email: admin@test.com\n";
    
    // Lister tous les admins
    $admins = User::where('role', 'admin')->get();
    echo "\n📋 Admins existants:\n";
    foreach ($admins as $admin) {
        echo "   - {$admin->name} ({$admin->email}) - Status: {$admin->status}\n";
    }
}

echo "\n✨ Terminé!\n";
?>