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
    $password = 'adminganteng16'; // Change this password!
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $name = 'Administrator';
    
    $stmt = $koneksi->prepare("INSERT INTO admins (email, password, name) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $hashed_password, $name);
    
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

// Additional code to handle admin creation or password update
$email = "admin@mptitravel.com";
$plain_password = "adminganteng16";  // Ganti dengan password yang diinginkan

// Hash password
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

// Cek apakah email sudah ada
$check_stmt = $koneksi->prepare("SELECT id FROM admins WHERE email = ?");
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Update password yang sudah ada
    $update_stmt = $koneksi->prepare("UPDATE admins SET password = ? WHERE email = ?");
    $update_stmt->bind_param("ss", $hashed_password, $email);
    
    if ($update_stmt->execute()) {
        echo "✅ Admin password updated for: {$email}";
    } else {
        echo "❌ Failed to update admin password";
    }
    
    $update_stmt->close();
} else {
    // Buat admin baru
    $insert_stmt = $koneksi->prepare("INSERT INTO admins (email, password) VALUES (?, ?)");
    $insert_stmt->bind_param("ss", $email, $hashed_password);
    
    if ($insert_stmt->execute()) {
        echo "✅ New admin created: {$email}";
    } else {
        echo "❌ Failed to create admin";
    }
    
    $insert_stmt->close();
}

$check_stmt->close();
$koneksi->close();

echo "<hr>";
echo "<p><a href='ViewLoginAdmin.php'>Go to Login Page</a></p>";
?>
