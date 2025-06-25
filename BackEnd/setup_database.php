<?php
/**
 * Setup Database Script
 * Script untuk membuat semua tabel yang diperlukan untuk sistem MPTI Travel
 * 
 * Jalankan script ini sekali saja untuk setup database
 */

// Konfigurasi database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'paket_travel';

try {
    // Koneksi ke database
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    
    echo "<h2>Setup Database MPTI Travel</h2>\n";
    echo "<p>Memulai setup database...</p>\n";
    
    // List file setup yang akan dijalankan
    $setup_files = [
        'create_main_tables.php',
        'create_admin.php', 
        'create_gallery_table.php',
        'create_settings_table.php',
        'create_payment_booking_tables.php',
        'create_newsletter_table.php'
    ];
    
    foreach ($setup_files as $file) {
        if (file_exists($file)) {
            echo "<p>Menjalankan: $file</p>\n";
            
            // Include file dan tangkap output
            ob_start();
            include $file;
            $output = ob_get_clean();
            
            if (strpos($output, 'error') !== false || strpos($output, 'Error') !== false) {
                echo "<p style='color: orange;'>Warning: $file - $output</p>\n";
            } else {
                echo "<p style='color: green;'>✓ $file berhasil dijalankan</p>\n";
            }
        } else {
            echo "<p style='color: red;'>✗ File $file tidak ditemukan</p>\n";
        }
    }
    
    // Insert data default untuk website settings
    echo "<p>Menambahkan data default...</p>\n";
    
    // Default website settings
    $default_settings = [
        ['setting_key' => 'company_name', 'setting_value' => 'MPTI Travel'],
        ['setting_key' => 'company_address', 'setting_value' => 'Jl. Malioboro No. 123, Yogyakarta'],
        ['setting_key' => 'phone_number', 'setting_value' => '+62812-3456-7890'],
        ['setting_key' => 'whatsapp_number', 'setting_value' => '+62812-3456-7890'],
        ['setting_key' => 'email', 'setting_value' => 'info@mptitravel.com'],
        ['setting_key' => 'instagram', 'setting_value' => '@mptitravel'],
        ['setting_key' => 'facebook', 'setting_value' => 'MPTI Travel'],
        ['setting_key' => 'website_url', 'setting_value' => 'https://mptitravel.com']
    ];
    
    $stmt_settings = $conn->prepare("INSERT INTO website_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    
    foreach ($default_settings as $setting) {
        $stmt_settings->bind_param("ss", $setting['setting_key'], $setting['setting_value']);
        $stmt_settings->execute();
    }
    
    echo "<p style='color: green;'>✓ Data default website settings berhasil ditambahkan</p>\n";
    
    // Default payment methods
    $default_payments = [
        ['method_name' => 'Transfer Bank BCA', 'account_number' => '1234567890', 'account_name' => 'MPTI Travel', 'is_active' => 1],
        ['method_name' => 'Transfer Bank Mandiri', 'account_number' => '0987654321', 'account_name' => 'MPTI Travel', 'is_active' => 1],
        ['method_name' => 'E-Wallet OVO', 'account_number' => '+62812-3456-7890', 'account_name' => 'MPTI Travel', 'is_active' => 1],
        ['method_name' => 'E-Wallet GoPay', 'account_number' => '+62812-3456-7890', 'account_name' => 'MPTI Travel', 'is_active' => 1]
    ];
    
    $stmt_payment = $conn->prepare("INSERT INTO payment_methods (method_name, account_number, account_name, is_active) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE account_number = VALUES(account_number)");
    
    foreach ($default_payments as $payment) {
        $stmt_payment->bind_param("sssi", $payment['method_name'], $payment['account_number'], $payment['account_name'], $payment['is_active']);
        $stmt_payment->execute();
    }
    
    echo "<p style='color: green;'>✓ Data default payment methods berhasil ditambahkan</p>\n";
    
    // Default admin user
    $admin_username = 'admin';
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    
    $stmt_admin = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?) ON DUPLICATE KEY UPDATE password = VALUES(password)");
    $stmt_admin->bind_param("ss", $admin_username, $admin_password);
    $stmt_admin->execute();
    
    echo "<p style='color: green;'>✓ User admin default berhasil ditambahkan (username: admin, password: admin123)</p>\n";
    
    $conn->close();
    
    echo "<h3 style='color: green;'>✓ Setup Database Selesai!</h3>\n";
    echo "<p>Database MPTI Travel berhasil dibuat dengan semua tabel dan data default.</p>\n";
    echo "<p><strong>Informasi Login Admin:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>URL: <a href='admin.php'>admin.php</a></li>\n";
    echo "<li>Username: admin</li>\n";
    echo "<li>Password: admin123</li>\n";
    echo "</ul>\n";
    echo "<p style='color: orange;'><strong>Catatan:</strong> Setelah login, segera ganti password default melalui admin panel untuk keamanan.</p>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>\n";
}
?>
