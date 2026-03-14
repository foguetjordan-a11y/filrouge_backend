<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run()
    {
        $paymentMethods = [
            [
                'name' => 'MTN Mobile Money',
                'code' => 'mtn_momo',
                'type' => 'mobile_money',
                'description' => 'Paiement via MTN Mobile Money (Cameroun)',
                'configuration' => [
                    'phone_number' => '+237 6 77 12 34 56',
                    'operator' => 'MTN Mobile Money',
                    'merchant_code' => 'MTN_UNIV_001',
                    'country' => 'Cameroun',
                    'currency' => 'FCFA',
                    'ussd_code' => '*126#',
                    'instructions' => 'Composez *126# puis suivez les instructions pour envoyer de l\'argent'
                ],
                'is_active' => true,
                'min_amount' => 500,
                'max_amount' => 2000000,
                'fee_percentage' => 1.0,
                'fee_fixed' => 100,
                'sort_order' => 1
            ],
            [
                'name' => 'Orange Money Cameroun',
                'code' => 'orange_money_cm',
                'type' => 'mobile_money',
                'description' => 'Paiement via Orange Money (Cameroun)',
                'configuration' => [
                    'phone_number' => '+237 6 99 87 65 43',
                    'operator' => 'Orange Money',
                    'merchant_code' => 'OM_UNIV_CM_001',
                    'country' => 'Cameroun',
                    'currency' => 'FCFA',
                    'ussd_code' => '#150#',
                    'instructions' => 'Composez #150# puis suivez les instructions pour le transfert d\'argent'
                ],
                'is_active' => true,
                'min_amount' => 500,
                'max_amount' => 1500000,
                'fee_percentage' => 1.2,
                'fee_fixed' => 150,
                'sort_order' => 2
            ],
            [
                'name' => 'Virement Bancaire',
                'code' => 'bank_transfer',
                'type' => 'bank',
                'description' => 'Paiement par virement bancaire (Banques camerounaises)',
                'configuration' => [
                    'bank_name' => 'Banque Atlantique Cameroun',
                    'account_number' => '10001234567890',
                    'account_name' => 'Université Virtuelle - Frais d\'enrôlement',
                    'swift_code' => 'ATCMCMCX',
                    'rib' => '10001 12345 67890123456 78',
                    'country' => 'Cameroun',
                    'currency' => 'FCFA'
                ],
                'is_active' => true,
                'min_amount' => 10000,
                'max_amount' => 10000000,
                'fee_percentage' => 0,
                'fee_fixed' => 0,
                'sort_order' => 3
            ],
            [
                'name' => 'Express Union Mobile',
                'code' => 'eu_mobile',
                'type' => 'mobile_money',
                'description' => 'Paiement via Express Union Mobile',
                'configuration' => [
                    'phone_number' => '+237 6 55 44 33 22',
                    'operator' => 'Express Union',
                    'merchant_code' => 'EU_UNIV_001',
                    'country' => 'Cameroun',
                    'currency' => 'FCFA',
                    'instructions' => 'Rendez-vous dans une agence Express Union avec votre pièce d\'identité'
                ],
                'is_active' => true,
                'min_amount' => 1000,
                'max_amount' => 5000000,
                'fee_percentage' => 0.8,
                'fee_fixed' => 200,
                'sort_order' => 4
            ],
            [
                'name' => 'Paiement en Espèces',
                'code' => 'cash',
                'type' => 'cash',
                'description' => 'Paiement en espèces au bureau des finances',
                'configuration' => [
                    'office_address' => 'Bureau des Finances - Campus Principal, Dakar',
                    'office_hours' => 'Lundi-Vendredi: 8h00-17h00, Samedi: 8h00-12h00',
                    'contact_phone' => '+221 33 123 45 67'
                ],
                'is_active' => true,
                'min_amount' => 1000,
                'max_amount' => null,
                'fee_percentage' => 0,
                'fee_fixed' => 0,
                'sort_order' => 5
            ],
            [
                'name' => 'Paiement Simulé',
                'code' => 'simulation',
                'type' => 'simulation',
                'description' => 'Paiement simulé pour les tests et démonstrations',
                'configuration' => [
                    'auto_confirm' => true,
                    'delay_seconds' => 3
                ],
                'is_active' => true,
                'min_amount' => 100,
                'max_amount' => 10000000,
                'fee_percentage' => 0,
                'fee_fixed' => 0,
                'sort_order' => 99
            ]
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }

        $this->command->info('Méthodes de paiement créées avec succès!');
    }
}