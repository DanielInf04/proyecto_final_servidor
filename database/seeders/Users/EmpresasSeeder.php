<?php

namespace Database\Seeders\Users;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company\Empresa;

class EmpresasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $empresas = User::where('role', 'empresa')->get();

        $data = [
            [
                'nombre' => 'Tech Solutions S.L.',
                'telefono' => '910123456',
                'direccion' => 'Avenida del Cid 12',
                'descripcion' => 'Empresa dedicada a vender productos de tecnología.',
                'nif' => 'A12345678',
            ],
            [
                'nombre' => 'Electronics S.L.',
                'telefono' => '910122446',
                'direccion' => 'Ausias March 12',
                'descripcion' => 'Empresa dedicada a vender productos de electrónica.',
                'nif' => 'B12345678',
            ],
            [
                'nombre' => 'Moda y Estilo S.A.',
                'telefono' => '911223344',
                'direccion' => 'Calle Moda 45',
                'descripcion' => 'Empresa especializada en ropa y accesorios de moda.',
                'nif' => 'C12345678',
            ],
            [
                'nombre' => 'CasaConfort Ltda.',
                'telefono' => '911334455',
                'direccion' => 'Av. Hogar 101',
                'descripcion' => 'Productos para el hogar y cocina de alta calidad.',
                'nif' => 'D12345678',
            ],
            [
                'nombre' => 'VitalCare S.A.',
                'telefono' => '911445566',
                'direccion' => 'Calle Salud 90',
                'descripcion' => 'Artículos de salud y belleza premium.',
                'nif' => 'E12345678',
            ],
            [
                'nombre' => 'FitZone S.L.',
                'telefono' => '911556677',
                'direccion' => 'Calle Fitness 77',
                'descripcion' => 'Especialistas en productos de deporte y fitness.',
                'nif' => 'F12345678',
            ],
            [
                'nombre' => 'JuegaMás Inc.',
                'telefono' => '911667788',
                'direccion' => 'Calle Juegos 34',
                'descripcion' => 'Venta de juguetes y juegos educativos.',
                'nif' => 'G12345678',
            ],
            [
                'nombre' => 'TechPlus S.A.',
                'telefono' => '911778899',
                'direccion' => 'Calle Tecnología 88',
                'descripcion' => 'Lo último en computación y tecnología.',
                'nif' => 'H12345678',
            ],
            [
                'nombre' => 'OfiStore Ltda.',
                'telefono' => '911889900',
                'direccion' => 'Av. Oficina 67',
                'descripcion' => 'Papelería y suministros de oficina para empresas.',
                'nif' => 'I12345678',
            ],
        ];

        foreach ($empresas as $index => $empresaUser) {
            Empresa::create(array_merge(
                ['user_id' => $empresaUser->id],
                $data[$index]
            ));
        }

        $this->command->info($empresas->count() . ' empresas creadas correctamente');

    }
}
