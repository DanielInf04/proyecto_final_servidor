<?php

namespace Database\Seeders\Orders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DetallePedidosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('pedidos_ejemplo/detalle_pedidos.csv');

        if (!File::exists($filePath)) {
            $this->command->error('Archivo CSV no encontrado: $filePath');
        }

        $csv = fopen($filePath, 'r');

        $header = fgetcsv($csv);

        $rows = [];
        while (($row = fgetcsv($csv)) !== false) {
            $rows[] = [
                'id' => $row[0],
                'pedido_empresa_id' => $row[1],
                'producto_id' => $row[2],
                'cantidad' => $row[3],
                'precio_unitario' => $row[4],
                'created_at' => $row[5],
                'updated_at' => $row[6],
            ];
        }

        fclose($csv);

        // Insertamos todas las filas
        DB::table('detalle_pedidos')->insert($rows);
    }
}