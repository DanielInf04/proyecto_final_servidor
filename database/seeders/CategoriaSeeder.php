<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categorias')->insert([
            ['id' => 1, 'nombre' => 'Electrónica', 'iva_porcentaje' => 0.21],
            ['id' => 2, 'nombre' => 'Ropa y accesorios', 'iva_porcentaje' => 0.21],
            ['id' => 3, 'nombre' => 'Hogar y cocina', 'iva_porcentaje' => 0.21],
            ['id' => 4, 'nombre' => 'Salud y belleza', 'iva_porcentaje' => 0.21],
            ['id' => 5, 'nombre' => 'Deportes y fitness', 'iva_porcentaje' => 0.21],
            ['id' => 6, 'nombre' => 'Juguetes y juegos', 'iva_porcentaje' => 0.21],
            ['id' => 8, 'nombre' => 'Computación y tecnología', 'iva_porcentaje' => 0.21],
            ['id' => 9, 'nombre' => 'Oficina y papelería', 'iva_porcentaje' => 0.21],
        ]);
    }
}
