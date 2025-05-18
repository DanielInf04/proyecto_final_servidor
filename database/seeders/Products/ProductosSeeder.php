<?php

namespace Database\Seeders\Products;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('productos/productos.csv');

        if (!File::exists($filePath)) {
            $this->command->error('Archivo CSV no encontrado: $filePath');
        }

        $csv = fopen($filePath, 'r');

        $header = fgetcsv($csv);

        $rows = [];
        while (($row = fgetcsv($csv)) !== false) {
            $rows[] = [
                'id' => $row[0],
                'nombre' => $row[1],
                'descripcion' => $row[2],
                'precio_base' => $row[3],
                'precio_oferta' => $row[4] === 'NULL' ? null : $row[4],
                'descuento_porcentaje' => $row[5] === 'NULL' ? null : $row[5],
                'oferta_activa' => $row[6],
                'stock' => $row[7],
                'estado' => $row[8],
                'categoria_id' => $row[9],
                'empresa_id' => $row[10],
                'created_at' => $row[11],
                'updated_at' => $row[12],
            ];
        }

        fclose($csv);

        // Insertamos todas las filas
        DB::table('productos')->insert($rows);

        $this->command->info('Productos insertados correctamente');
    }
}
