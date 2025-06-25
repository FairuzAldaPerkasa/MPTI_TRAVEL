<?php
$koneksi = new mysqli("localhost", "root", "", "paket_travel");
if ($koneksi->connect_error) die("Connection failed");
$result = $koneksi->query("SELECT id, nama FROM paket LIMIT 5");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " - " . $row['nama'] . "\n";
    }
} else {
    echo "No packages found\n";
}
?>
