<?php

// Cargar JSON
$provinciasJson = json_decode(file_get_contents('provincias.json'), true);
$poblacionesJson = json_decode(file_get_contents('poblaciones.json'), true);

// Crear mapa de provincias usando el código oficial (string con ceros)
$provinciaMap = []; // [code => id]
$provinciaCsv = fopen('provincias.csv', 'w');
fputcsv($provinciaCsv, ['id', 'nombre']);

$provinciaId = 1;
foreach ($provinciasJson as $provincia) {
    $code = $provincia['code'];
    $label = $provincia['label'];
    $provinciaMap[$code] = $code;
    fputcsv($provinciaCsv, [$code, $label]);
}
fclose($provinciaCsv);

// Crear CSV de poblaciones
$poblacionCsv = fopen('poblaciones.csv', 'w');
fputcsv($poblacionCsv, ['id', 'nombre', 'provincia_id']);

$poblacionId = 1;
foreach ($poblacionesJson as $poblacion) {
    $parentCode = $poblacion['parent_code'];
    $label = $poblacion['label'];

    if (isset($provinciaMap[$parentCode])) {
        $provinciaId = $provinciaMap[$parentCode];
        fputcsv($poblacionCsv, [$poblacionId, $label, $provinciaId]);
        $poblacionId++;
    }
}

fclose($poblacionCsv);

echo "✅ CSVs generados correctamente: provincias.csv y poblaciones.csv\n";