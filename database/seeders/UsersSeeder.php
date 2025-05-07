<?php

namespace Database\Seeders;

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
        // Usuario 1
        /*User::create([
            'name' => 'Daniel',
            'email' => 'danielinf0417@gmail.com',
            'password' => Hash::make('prueba2024'),
            'role' => 'cliente',
        ]);*/

        // Cliente
        $clienteUser = User::create([
            'name' => 'Daniel',
            'email' => 'danigamerrep@gmail.com',
            'password' => Hash::make('test1234'),
            'role' => 'cliente'
        ]);

        Cliente::create([
            'user_id' => $clienteUser->id,
            'telefono' => '600123456'
        ]);

        // Empresa
        $empresaUser = User::create([
            'name' => 'Technologies S.L.',
            'email' => 'danielinf0417@gmail.com',
            'password' => Hash::make('test1234'),
            'role' => 'empresa'
        ]);

        $empresaUser2 = User::create([
            'name' => 'Electronics S.L.',
            'email' => 'electronics@gmail.com',
            'password' => Hash::make('test1234'),
            'role' => 'empresa'
        ]);

        Empresa::create([
            'user_id' => $empresaUser->id,
            'nombre' => 'Tech Solutions S.L.',
            'telefono' => '910123456',
            'direccion' => 'Avenida del Cid 12',
            'descripcion' => 'Empresa dedicada al desarrollo de software.',
            'nif' => 'A12345678'
        ]);

        Empresa::create([
            'user_id' => $empresaUser2->id,
            'nombre' => 'Electronics S.L.',
            'telefono' => '910122446',
            'direccion' => 'Ausias March 12',
            'descripcion' => 'Empresa dedicada a vender productos de electrónica.',
            'nif' => 'B12345678'
        ]);

        // Administrador
        User::create([
            'name' => 'admin',
            'email' => 'admin@admin.es',
            'password' => Hash::make('admin2024'),
            'role' => 'admin'
        ]);

        /*for ($i = 1; $i <= 20; $i++) {
            User::create([
                'name' => 'Cliente' . $i,
                'email' => 'usuario' . $i . '@prueba2024.com',
                'password' => Hash::make('prueba2024'),
                'role' => 'cliente'
            ]);
        }*/

    }
}
