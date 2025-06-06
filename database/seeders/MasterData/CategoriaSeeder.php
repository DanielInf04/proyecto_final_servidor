<?php

namespace Database\Seeders\MasterData;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Shared\Categoria;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('categorias/categorias.csv');

        if (!File::exists($filePath)) {
            $this->command->error('Archivo CSV no encontrado: $filePath');
        }

        $csv = fopen($filePath, 'r');

        $header = fgetcsv($csv);

        $rows = [];
        while(($row = fgetcsv($csv)) !== false) {
            $rows[] = [
                'id' => $row[0],
                'nombre' => $row[1],
                'iva_porcentaje' => $row[2],
                'imagen' => $row[3],
                'created_at' => $row[4],
                'updated_at' => $row[5],
            ];
        }

        fclose($csv);

        // Insertamos todas las filas
        DB::table('categorias')->insert($rows);

        $this->command->info('Categorias insertadas correctamente');

        /*DB::table('categorias')->insert([
            ['id' => 1, 'nombre' => 'Electrónica', 'iva_porcentaje' => 0.21],
            ['id' => 2, 'nombre' => 'Ropa y accesorios', 'iva_porcentaje' => 0.21],
            ['id' => 3, 'nombre' => 'Hogar y cocina', 'iva_porcentaje' => 0.21],
            ['id' => 4, 'nombre' => 'Salud y belleza', 'iva_porcentaje' => 0.21],
            ['id' => 5, 'nombre' => 'Deportes y fitness', 'iva_porcentaje' => 0.21],
            ['id' => 6, 'nombre' => 'Juguetes y juegos', 'iva_porcentaje' => 0.21],
            ['id' => 8, 'nombre' => 'Computación y tecnología', 'iva_porcentaje' => 0.21],
            ['id' => 9, 'nombre' => 'Oficina y papelería', 'iva_porcentaje' => 0.21],
        ]);

        $this->command->info('Categorias insertadas correctamente');*/
    }
}
