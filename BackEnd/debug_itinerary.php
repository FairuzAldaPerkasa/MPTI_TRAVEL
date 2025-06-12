<?php
// Debug file untuk memeriksa format itinerary
header('Content-Type: application/json; charset=utf-8');

$koneksi = new mysqli("localhost", "root", "", "paket_travel");

if ($koneksi->connect_error) {
    die("Connection failed: " . $koneksi->connect_error);
}

$result = $koneksi->query("SELECT id, nama, itinerary FROM paket LIMIT 5");

$packages = [];
while ($row = $result->fetch_assoc()) {
    $packages[] = [
        'id' => $row['id'],
        'nama' => $row['nama'],
        'itinerary_raw' => $row['itinerary'],
        'itinerary_decoded' => json_decode($row['itinerary'], true),
        'itinerary_is_valid' => json_last_error() === JSON_ERROR_NONE
    ];
}

echo json_encode($packages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
