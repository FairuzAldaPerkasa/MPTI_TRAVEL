<?php
/**
 * Script untuk mengecek data paket yang tersedia
 */

// Konfigurasi database
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "paket_travel";

try {
    // Koneksi ke database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>📦 Data Paket yang Tersedia:</h2>";
    
    // Ambil semua paket
    $stmt = $pdo->query("SELECT id, nama, duration, price, created_at FROM paket ORDER BY id");
    $packages = $stmt->fetchAll();
    
    if (count($packages) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Nama Paket</th><th>Durasi</th><th>Harga</th><th>Created</th><th>Test Link</th></tr>";
        
        foreach ($packages as $pkg) {
            $price = $pkg['price'] ? number_format($pkg['price'], 0, ',', '.') : 'N/A';
            echo "<tr>";
            echo "<td>{$pkg['id']}</td>";
            echo "<td>{$pkg['nama']}</td>";
            echo "<td>{$pkg['duration']}</td>";
            echo "<td>Rp {$price}</td>";
            echo "<td>{$pkg['created_at']}</td>";
            echo "<td><a href='../FrontEnd/html/package_detail.html?id={$pkg['id']}' target='_blank'>Test</a></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>🔗 Link untuk Testing:</h3>";
        foreach ($packages as $pkg) {
            echo "📋 <a href='../FrontEnd/html/package_detail.html?id={$pkg['id']}' target='_blank'>{$pkg['nama']} (ID: {$pkg['id']})</a><br>";
        }
    } else {
        echo "❌ Tidak ada paket yang ditemukan di database.";
    }
    
    echo "<br><br><h3>🖼️ Gallery Photos:</h3>";
    $gallery_stmt = $pdo->query("SELECT package_id, COUNT(*) as photo_count FROM package_gallery GROUP BY package_id");
    $gallery_data = $gallery_stmt->fetchAll();
    
    if (count($gallery_data) > 0) {
        foreach ($gallery_data as $gal) {
            echo "📷 Package ID {$gal['package_id']}: {$gal['photo_count']} foto<br>";
        }
    } else {
        echo "❌ Tidak ada foto gallery yang ditemukan.";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
