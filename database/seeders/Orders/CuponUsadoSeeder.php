<?php

namespace Database\Seeders\Orders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CuponUsadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('pedidos_ejemplo/cupon_usados.csv');

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
                'cupon_id' => $row[2],
                'pedido_id' => $row[3],
                'created_at' => $row[4],
                'updated_at' => $row[5],
            ];
        }

        fclose($csv);

        // Insertamos todas las filas
        DB::table('cupon_usados')->insert($rows);
    }
}