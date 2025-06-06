<?php

namespace Database\Seeders\Users;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client\Cliente;
use App\Models\Company\Empresa;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        $defaultPassword = Hash::make('test1234');

        // === ADMINISTRADORES ===

        $admins = [
            [
                'name' => 'admin',
                'email' => 'admin@marketease.com',
                'password' => Hash::make('admin1234'),
                'role' => 'admin',
            ],
        ];

        // === CLIENTES ===

        $clientes = [
            [
                'name' => 'Daniel',
                'email' => 'daniel@gmail.com',
                'password' => $defaultPassword,
                'role' => 'cliente',
            ],
        ];

        // === EMPRESAS ===

        $empresas = [
            [
                'name' => 'Technologies S.L.',
                'email' => 'technologies@empresa.com',
                'password' => $defaultPassword,
                'role' => 'empresa',
            ],
            [
                'name' => 'Electronics S.L.',
                'email' => 'electronics@empresa.com',
                'password' => $defaultPassword,
                'role' => 'empresa',
            ],
            [
                'name' => 'Moda y Estilo S.A.',
                'email' => 'moda@empresa.com',
                'password' => $defaultPassword,
                'role' => 'empresa',
            ],
            [
                'name' => 'CasaConfort Ltda.',
                'email' => 'hogarycocina@empresa.com',
                'password' => $defaultPassword,
                'role' => 'empresa',
            ],
            [
                'name' => 'VitalCare S.A.',
                'email' => 'saludybelleza@empresa.com',
                'password' => $defaultPassword,
                'role' => 'empresa',
            ],
            [
                'name' => 'FitZone S.L.',
                'email' => 'deportesfitness@empresa.com',
                'password' => $defaultPassword,
                'role' => 'empresa',
            ],
            [
                'name' => 'JuegaMás Inc.',
                'email' => 'juguetesyjuegos@empresa.com',
                'password' => $defaultPassword,
                'role' => 'empresa',
            ],
            [
                'name' => 'TechPlus S.A.',
                'email' => 'computacionytecnologia@empresa.com',
                'password' => $defaultPassword,
                'role' => 'empresa',
            ],
            [
                'name' => 'OfiStore Ltda.',
                'email' => 'oficinaypapeleria@empresa.com',
                'password' => $defaultPassword,
                'role' => 'empresa',
            ],
        ];

        // Creamos todos los usuarios
        foreach (array_merge($admins, $clientes, $empresas) as $user) {
            User::create($user);
        }

        $this->command->info('Usuarios creados correctamente');

    }
}
