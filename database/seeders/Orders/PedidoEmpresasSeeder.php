<?php

namespace Database\Seeders\Orders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PedidoEmpresasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('pedidos_ejemplo/pedido_empresas.csv');

        if (!File::exists($filePath)) {
            $this->command->error('Archivo CSV no encontrado: $filePath');
        }

        $csv = fopen($filePath, 'r');

        $header = fgetcsv($csv);

        $rows = [];
        while (($row = fgetcsv($csv)) !== false) {
            $rows[] = [
                'id' => $row[0],
                'pedido_id' => $row[1],
                'empresa_id' => $row[2],
                'estado_envio' => $row[3],
                'fecha_envio' => strtoupper(trim($row[4])) === 'NULL' || $row[4] === '' ? null : $row[4],
                'precio_total' => $row[5],
                'created_at' => $row[6],
                'updated_at' => $row[7],
            ];
        }

        fclose($csv);

        // Insertamos todas las filas
        DB::table('pedido_empresas')->insert($rows);
    }
}