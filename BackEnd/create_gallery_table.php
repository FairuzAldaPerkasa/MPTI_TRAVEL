<?php
/**
 * Create Gallery Table
 * Creates additional gallery table for packages
 * 
 * @version 1.0
 * @author MPTI_TRAVEL
 */

// Database connection
$koneksi = new mysqli("localhost", "root", "", "paket_travel");

if ($koneksi->connect_error) {
    die("Connection failed: " . $koneksi->connect_error);
}

echo "<h2>🖼️ Creating Gallery Table...</h2>";

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
    echo "✅ Gallery table created successfully<br>";
} else {
    echo "❌ Error creating gallery table: " . $koneksi->error . "<br>";
}

// Check if table has data
$count_result = $koneksi->query("SELECT COUNT(*) as total FROM gallery");
$count = $count_result->fetch_assoc()['total'];

echo "<p>📊 Current gallery entries: <strong>$count</strong></p>";

$koneksi->close();

echo "<hr>";
echo "<p>Gallery table setup completed!</p>";
?>
