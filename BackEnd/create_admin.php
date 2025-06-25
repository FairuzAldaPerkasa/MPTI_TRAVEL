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
$admin_table_sql = "CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
)";

if ($koneksi->query($admin_table_sql) === TRUE) {
    echo "✅ Admin table created/verified successfully<br>";
} else {
    echo "❌ Error creating admin table: " . $koneksi->error . "<br>";
}

// Check if admin already exists
$check_admin = $koneksi->query("SELECT id FROM admin WHERE username = 'admin'");

if ($check_admin->num_rows > 0) {
    echo "ℹ️ Admin user already exists<br>";
} else {
    // Create default admin user
    $username = 'admin';
    $password = password_hash('admin123', PASSWORD_DEFAULT); // Change this password!
    
    $stmt = $koneksi->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);
    
    if ($stmt->execute()) {
        echo "✅ Admin user created successfully<br>";
        echo "<div style='background: #fef3c7; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
        echo "<h3>⚠️ Default Login Credentials:</h3>";
        echo "<p><strong>Username:</strong> admin</p>";
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
