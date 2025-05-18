<?php

namespace Database\Seeders\Users;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client\Cliente;

class ClientesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtenemos todos los usuarios que tengan el role cliente
        $clientes = User::where('role', 'cliente')->get();

        if ($clientes->isEmpty()) {
            $this->command->warn('No se encontraron usuarios con rol cliente');
            return;
        }

        foreach ($clientes as $clienteUser) {
            Cliente::create([
                'user_id' => $clienteUser->id,
                'telefono' => '6' . fake()->randomNumber(8, true),
            ]);
        }

        $this->command->info($clientes->count() . ' clientes creados correctamente');
    }
}
