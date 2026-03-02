<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

if (!isset($_FILES['imagen'])) {
    echo json_encode(["error" => "No se subió imagen"]);
    exit;
}

if (!is_dir("uploads")) {
    mkdir("uploads", 0777, true);
}

$nombreArchivo = "uploads/" . time() . "_" . basename($_FILES['imagen']['name']);
move_uploaded_file($_FILES['imagen']['tmp_name'], $nombreArchivo);

$apiKey = "AQUI VA LA APIKEY";
$url = "https://my-api.plantnet.org/v2/identify/all?api-key=$apiKey&lang=es";

$image = curl_file_create($nombreArchivo);

$data = [
    "images" => $image,
    "organs" => "auto"
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $data,
    CURLOPT_RETURNTRANSFER => true
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (!isset($data['results'][0])) {
    echo json_encode(["error" => "No se pudo identificar"]);
    exit;
}

$r = $data['results'][0];

$resultado = [
    "nombreComun" => $r['species']['commonNames'][0] ?? 'No disponible',
    "nombreCientifico" => $r['species']['scientificName'] ?? 'Desconocido',
    "familia" => $r['species']['family']['scientificName'] ?? 'No disponible',
    "genero" => $r['species']['genus']['scientificName'] ?? 'No disponible',
    "confianza" => round($r['score'] * 100),
    "organo" => $r['images'][0]['organ'] ?? 'No detectado',
    "imagenSubida" => $nombreArchivo,
    "imagenReferencia" => $r['species']['images'][0]['url']['o'] ?? null
];

echo json_encode($resultado);

exit;
