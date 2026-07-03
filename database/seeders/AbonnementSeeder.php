<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Abonnement;

class AbonnementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Abonnement::updateOrCreate(
            ['slug' => 'starter'],
            [
                'nomAgence' => 'Starter',
                'description' => 'Idéal pour les petites agences locales.',
                'prix_mensuel' => 49000,
                'prix_annuel' => 500000,
                'devise' => 'CDF',
                'max_succursales' => 3,
                'max_vehicules' => 5,
                'max_utilisateurs' => 2,
            ]
        );

        Abonnement::updateOrCreate(
            ['slug' => 'business'],
            [
                'nomAgence' => 'Business',
                'description' => 'Le meilleur choix pour les agences en croissance.',
                'prix_mensuel' => 129000,
                'prix_annuel' => 1300000,
                'devise' => 'CDF',
                'max_succursales' => 10,
                'max_vehicules' => 20,
                'max_utilisateurs' => 10,
            ]
        );

        Abonnement::updateOrCreate(
            ['slug' => 'enterprise'],
            [
                'nomAgence' => 'Enterprise',
                'description' => 'Puissance totale pour les grands réseaux.',
                'prix_mensuel' => 299000,
                'prix_annuel' => 3000000,
                'devise' => 'CDF',
                'max_succursales' => 999,
                'max_vehicules' => 999,
                'max_utilisateurs' => 999,
            ]
        );
    }
}
