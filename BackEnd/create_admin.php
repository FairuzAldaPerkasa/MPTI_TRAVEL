<?php
/**
 * Create Admin User
 * Creates admin login credentials
 * 
 * @version 1.0
 * @author MPTI_TRAVEL
 */

// Database connection
$koneksi = new mysqli("localhost", "root", "", "paket_travel");

if ($koneksi->connect_error) {
    die("Connection failed: " . $koneksi->connect_error);
}

echo "<h2>👤 Creating Admin User...</h2>";

// Create admin table if not exists
$admin_table_sql = "CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) DEFAULT 'Admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
)";

if ($koneksi->query($admin_table_sql) === TRUE) {
    echo "✅ Admin table created/verified successfully<br>";
} else {
    echo "❌ Error creating admin table: " . $koneksi->error . "<br>";
}

// Check if admin already exists
$check_admin = $koneksi->query("SELECT id FROM admins WHERE email = 'admin@mptitravel.com'");

if ($check_admin->num_rows > 0) {
    echo "ℹ️ Admin user already exists<br>";
} else {
    // Create default admin user
    $email = 'admin@mptitravel.com';
    $password = password_hash('admin123', PASSWORD_DEFAULT); // Change this password!
    $name = 'Administrator';
    
    $stmt = $koneksi->prepare("INSERT INTO admins (email, password, name) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $password, $name);
    
    if ($stmt->execute()) {
        echo "✅ Admin user created successfully<br>";
        echo "<div style='background: #fef3c7; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
        echo "<h3>⚠️ Default Login Credentials:</h3>";
        echo "<p><strong>Email:</strong> admin@mptitravel.com</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p><em>Please change the password after first login!</em></p>";
        echo "</div>";
    } else {
        echo "❌ Error creating admin user: " . $stmt->error . "<br>";
    }
    $stmt->close();
}

$koneksi->close();

echo "<hr>";
echo "<p><a href='ViewLoginAdmin.php'>Go to Login Page</a></p>";
?>
