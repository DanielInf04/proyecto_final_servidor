<?php

namespace Database\Seeders\Users;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Client\Cliente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder2 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'name' => "Cliente $i",
                'email' => "cliente$i@example.com",
                'password' => Hash::make('test1234'),
                'role' => 'cliente',
            ]);

            Cliente::create([
                'user_id' => $user->id,
                'telefono' => '60012345' . $i
            ]);
        }
    }
}
