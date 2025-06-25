<?php
/**
 * Script untuk membuat tabel settings untuk pengaturan website
 * Jalankan sekali untuk membuat tabel, lalu hapus file ini
 */

// Konfigurasi database
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "paket_travel";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Buat tabel settings jika belum ada
    $create_table = "
    CREATE TABLE IF NOT EXISTS website_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT NOT NULL,
        setting_type ENUM('text', 'number', 'email', 'url', 'textarea') DEFAULT 'text',
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($create_table);
    echo "✅ Tabel website_settings berhasil dibuat!<br><br>";
    
    // Insert default settings
    $default_settings = [
        [
            'key' => 'whatsapp_number',
            'value' => '6281234567890',
            'type' => 'text',
            'description' => 'Nomor WhatsApp untuk booking (format: 62xxx tanpa +)'
        ],
        [
            'key' => 'phone_number',
            'value' => '+62 812 3456 7890',
            'type' => 'text',
            'description' => 'Nomor telepon untuk kontak'
        ],
        [
            'key' => 'company_email',
            'value' => 'Vacationland@email.com',
            'type' => 'email',
            'description' => 'Email perusahaan'
        ],
        [
            'key' => 'company_address',
            'value' => 'Jl. Malioboro No. 123, Yogyakarta',
            'type' => 'textarea',
            'description' => 'Alamat perusahaan'
        ],
        [
            'key' => 'instagram_handle',
            'value' => '@Vacationland',
            'type' => 'text',
            'description' => 'Handle Instagram'
        ],
        [
            'key' => 'facebook_page',
            'value' => 'Vacationland',
            'type' => 'text',
            'description' => 'Nama halaman Facebook'
        ],
        [
            'key' => 'website_url',
            'value' => 'Vacationland.co.id',
            'type' => 'url',
            'description' => 'URL website'
        ]
    ];
    
    $insert_stmt = $pdo->prepare("
        INSERT INTO website_settings (setting_key, setting_value, setting_type, description) 
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        setting_value = VALUES(setting_value),
        setting_type = VALUES(setting_type),
        description = VALUES(description)
    ");
    
    foreach ($default_settings as $setting) {
        $insert_stmt->execute([
            $setting['key'],
            $setting['value'],
            $setting['type'],
            $setting['description']
        ]);
    }
    
    echo "✅ Default settings berhasil diinsert!<br><br>";
    
    echo "<h3>📋 Settings yang Tersedia:</h3>";
    $stmt = $pdo->query("SELECT setting_key, setting_value, description FROM website_settings ORDER BY setting_key");
    while ($row = $stmt->fetch()) {
        echo "<strong>{$row['setting_key']}:</strong> {$row['setting_value']}<br>";
        echo "<em>{$row['description']}</em><br><br>";
    }
    
    echo "<h3>🔗 Next Steps:</h3>";
    echo "1. <a href='admin.php'>Buka Admin Panel</a> untuk mengelola settings<br>";
    echo "2. Hapus file ini setelah selesai untuk keamanan<br>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
