<?php

namespace Database\Seeders\Orders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ContactoEntregasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('pedidos_ejemplo/contacto_entregas.csv');

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
                'apellidos' => $row[2],
                'email' => $row[3],
                'telefono' => $row[4],
                'created_at' => $row[5],
                'updated_at' => $row[6],
            ];
        }

        fclose($csv);

        // Insertamos todas las filas
        DB::table('contacto_entregas')->insert($rows);
    }
}