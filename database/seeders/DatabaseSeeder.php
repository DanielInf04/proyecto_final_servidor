<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Database\Seeders\Users\UsersSeeder;
//use Database\Seeders\Users\UsersSeeder2;
use Database\Seeders\Users\ClientesSeeder;
use Database\Seeders\Users\EmpresasSeeder;
use Database\Seeders\MasterData\CategoriaSeeder;
use Database\Seeders\MasterData\ProvinciaSeeder;
use Database\Seeders\MasterData\PoblacionSeeder;
use Database\Seeders\Coupons\CouponSeeder;
use Database\Seeders\Products\ProductosSeeder;
use Database\Seeders\Products\ProductosImagenesSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llamamos a los seeders que queremos ejecutar
        $this->call([
            UsersSeeder::class,
            ClientesSeeder::class,
            EmpresasSeeder::class,
            CategoriaSeeder::class,
            ProvinciaSeeder::class,
            PoblacionSeeder::class,
            CouponSeeder::class,
            ProductosSeeder::class,
            ProductosImagenesSeeder::class
        ]);
    }
}
