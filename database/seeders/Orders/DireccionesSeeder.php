<?php

namespace Database\Seeders\Orders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DireccionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('pedidos_ejemplo/direcciones.csv');

        if (!File::exists($filePath)) {
            $this->command->error('Archivo CSV no encontrado: $filePath');
        }

        $csv = fopen($filePath, 'r');

        $header = fgetcsv($csv);

        $rows = [];
        while (($row = fgetcsv($csv)) !== false) {
            $rows[] = [
                'id' => $row[0],
                'calle' => $row[1],
                'puerta' => $row[2],
                'piso' => $row[3],
                'pais' => $row[4],
                'codigo_postal' => $row[5],
                'poblacion_id' => $row[6],
                'cliente_id' => $row[7],
                'created_at' => $row[8],
                'updated_at' => $row[9],
            ];
        }

        fclose($csv);

        // Insertamos todas las filas
        DB::table('direcciones')->insert($rows);
    }
}