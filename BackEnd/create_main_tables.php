<?php
/**
 * Create Main Database Tables
 * Creates the core paket table and related tables
 * 
 * @version 1.0
 * @author MPTI_TRAVEL
 */

// Database connection
$koneksi = new mysqli("localhost", "root", "", "paket_travel");

if ($koneksi->connect_error) {
    die("Connection failed: " . $koneksi->connect_error);
}

echo "<h2>🗄️ Creating Main Database Tables...</h2>";

// Create paket table
$paket_sql = "CREATE TABLE IF NOT EXISTS paket (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    deskripsi TEXT NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    duration VARCHAR(20) DEFAULT '2D1N',
    fotos JSON,
    highlights JSON,
    itinerary JSON,
    inclusions JSON,
    exclusions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nama (nama),
    INDEX idx_price (price),
    INDEX idx_created_at (created_at)
)";

if ($koneksi->query($paket_sql) === TRUE) {
    echo "✅ Paket table created/verified successfully<br>";
} else {
    echo "❌ Error creating paket table: " . $koneksi->error . "<br>";
}

// Create admin table
$admin_sql = "CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username)
)";

if ($koneksi->query($admin_sql) === TRUE) {
    echo "✅ Admin table created/verified successfully<br>";
} else {
    echo "❌ Error creating admin table: " . $koneksi->error . "<br>";
}

// Create gallery table
$gallery_sql = "CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255),
    caption TEXT,
    display_order INT DEFAULT 0,
    file_size INT,
    file_type VARCHAR(50),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES paket(id) ON DELETE CASCADE,
    INDEX idx_package_id (package_id),
    INDEX idx_display_order (display_order)
)";

if ($koneksi->query($gallery_sql) === TRUE) {
    echo "✅ Gallery table created/verified successfully<br>";
} else {
    echo "❌ Error creating gallery table: " . $koneksi->error . "<br>";
}

// Check and create default admin user
$check_admin = $koneksi->query("SELECT id FROM admin WHERE username = 'admin'");
if ($check_admin->num_rows == 0) {
    $username = 'admin';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    
    $stmt = $koneksi->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);
    
    if ($stmt->execute()) {
        echo "✅ Default admin user created (admin/admin123)<br>";
    } else {
        echo "❌ Error creating admin user: " . $stmt->error . "<br>";
    }
    $stmt->close();
} else {
    echo "ℹ️ Admin user already exists<br>";
}

// Show table status
echo "<br><h3>📊 Table Status:</h3>";
$tables = ['paket', 'admin', 'gallery'];
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'>";
echo "<th style='padding: 8px;'>Table</th>";
echo "<th style='padding: 8px;'>Records</th>";
echo "</tr>";

foreach ($tables as $table) {
    $result = $koneksi->query("SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "<tr>";
        echo "<td style='padding: 8px;'>$table</td>";
        echo "<td style='padding: 8px;'>$count</td>";
        echo "</tr>";
    }
}
echo "</table>";

$koneksi->close();

echo "<hr>";
echo "<div style='background: #10b981; color: white; padding: 15px; border-radius: 8px;'>";
echo "<h3>✅ Main Database Setup Complete!</h3>";
echo "<p>Core tables have been created successfully.</p>";
echo "</div>";
?>
