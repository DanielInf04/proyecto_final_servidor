<?php

namespace Database\Seeders\Orders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PedidosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('pedidos_ejemplo/pedidos.csv');

        if (!File::exists($filePath)) {
            $this->command->error('Archivo CSV no encontrado: $filePath');
        }

        $csv = fopen($filePath, 'r');

        $header = fgetcsv($csv);

        $rows = [];
        while (($row = fgetcsv($csv)) !== false) {
            $rows[] = [
                'id' => $row[0],
                'cliente_id' => $row[1],
                'direccion_id' => $row[2],
                'contacto_entrega_id' => $row[3],
                'total' => $row[4],
                'status' => $row[5],
                'fecha_pedido' => $row[6],
                'created_at' => $row[7],
                'updated_at' => $row[8],
            ];
        }

        fclose($csv);

        // Insertamos todas las filas
        DB::table('pedidos')->insert($rows);
    }
}