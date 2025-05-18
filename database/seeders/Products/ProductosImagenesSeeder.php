<?php

namespace Database\Seeders\Products;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductosImagenesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('productos/producto_imagenes.csv');

        if (!File::exists($filePath)) {
            $this->command->error('Archivo CSV no encontrado: $filePath');
        }

        $csv = fopen($filePath, 'r');

        $header = fgetcsv($csv);

        $rows = [];
        while (($row = fgetcsv($csv)) !== false) {
            $rows[] = [
                'id' => $row[0],
                'imagen' => $row[1],
                'producto_id' => $row[2],
                'created_at' => $row[3],
                'updated_at' => $row[4],
            ];
        }

        fclose($csv);

        // Insertamos todas las filas
        DB::table('producto_imagenes')->insert($rows);

        $this->command->info('Imagenes de los productos insertados correctamente');
    }
}
