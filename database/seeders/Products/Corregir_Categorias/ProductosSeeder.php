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
        $categoriasValidas = [1, 2, 3, 4, 5, 6, 7, 8];

        while (($row = fgetcsv($csv)) !== false) {
            // Reemplazamos la categoria
            $categoriaId = match ((int) $row[9]) {
                7 => 6,  // productos que apuntaban a 7 → existen en la categoría 6
                8 => 7,  // productos con categoría 8 → deben ir a 'Computación' que es id 8 en la tabla, pero tú quieres que sea 7
                9 => 8,  // productos con categoría 9 → deben apuntar a la categoría 8 real
                default => (int) $row[9]
            };

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
                'categoria_id' => $categoriaId,
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
