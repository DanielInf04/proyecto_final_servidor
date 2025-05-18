<?php

namespace Database\Seeders\Coupons;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Shared\Cupon;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Cupon::create([
            'codigo' => 'BIENVENIDO10',
            'porcentaje_descuento' => 10,
            'solo_nuevos_usuarios' => 1
        ]);

        $this->command->info('Cupon creado correctamente');
    }
}
