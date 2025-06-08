<?php

namespace Database\Seeders\Orders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            \Database\Seeders\Orders\DireccionesSeeder::class,
            \Database\Seeders\Orders\ContactoEntregasSeeder::class,
            \Database\Seeders\Orders\PedidosSeeder::class,
            \Database\Seeders\Orders\PedidoEmpresasSeeder::class,
            \Database\Seeders\Orders\DetallePedidosSeeder::class,
            \Database\Seeders\Orders\PagosSeeder::class,
            \Database\Seeders\Orders\CuponUsadoSeeder::class,
        ]);

        $this->command->info('Pedidos de ejemplo insertados correctamente.');
    }
}