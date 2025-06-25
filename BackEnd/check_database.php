<?php
/**
 * Database Status Checker
 * Script sederhana untuk mengecek status tabel database
 */

// Konfigurasi database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'paket_travel';

try {
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    
    echo "<h2>Status Database MPTI Travel</h2>\n";
    
    // Daftar tabel yang harus ada
    $required_tables = [
        'paket',
        'admin', 
        'gallery',
        'website_settings',
        'payment_methods',
        'booking_history',
        'newsletter_subscribers',
        'email_campaigns'
    ];
    
    echo "<h3>Status Tabel:</h3>\n";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr><th>Nama Tabel</th><th>Status</th><th>Jumlah Data</th></tr>\n";
    
    foreach ($required_tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        
        if ($result && $result->num_rows > 0) {
            // Tabel ada, hitung jumlah data
            $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
            $count = $count_result ? $count_result->fetch_assoc()['count'] : 0;
            
            echo "<tr><td>$table</td><td style='color: green;'>✓ Ada</td><td>$count</td></tr>\n";
        } else {
            echo "<tr><td>$table</td><td style='color: red;'>✗ Tidak Ada</td><td>-</td></tr>\n";
        }
    }
    
    echo "</table>\n";
    
    // Info koneksi admin
    $admin_result = $conn->query("SELECT COUNT(*) as count FROM admin WHERE 1");
    if ($admin_result && $admin_result->fetch_assoc()['count'] > 0) {
        echo "<p style='color: green;'>✓ User admin tersedia</p>\n";
        echo "<p><a href='admin.php'>Akses Admin Panel</a></p>\n";
    } else {
        echo "<p style='color: red;'>✗ User admin belum dibuat</p>\n";
        echo "<p>Jalankan <a href='setup_database.php'>setup_database.php</a> terlebih dahulu</p>\n";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>\n";
}
?>
