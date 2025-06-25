<?php
// Test Login Script
session_start();

echo "<h2>🔍 Login Test Debug</h2>";

// Test database connection
$koneksi = new mysqli("localhost", "root", "", "paket_travel");
if ($koneksi->connect_error) {
    echo "❌ Database connection failed: " . $koneksi->connect_error . "<br>";
    exit;
} else {
    echo "✅ Database connected<br>";
}

// Check admin table structure
$result = $koneksi->query("DESCRIBE admin");
echo "<h3>📋 Admin table structure:</h3>";
while ($row = $result->fetch_assoc()) {
    echo "- {$row['Field']} ({$row['Type']})<br>";
}

// Check if admin exists
$result = $koneksi->query("SELECT * FROM admin");
echo "<h3>👤 Admin users:</h3>";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "- ID: {$row['id']}, Email: {$row['email']}<br>";
    }
} else {
    echo "No admin users found<br>";
}

// Test login
if ($_POST) {
    echo "<h3>🔑 Login attempt:</h3>";
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    echo "Email: $email<br>";
    echo "Password length: " . strlen($password) . "<br>";
    
    $stmt = $koneksi->prepare("SELECT id, email, password, name FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    
    if ($admin) {
        echo "✅ Admin found<br>";
        if (password_verify($password, $admin['password'])) {
            echo "✅ Password correct<br>";
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            echo "✅ Session set<br>";
            echo "<a href='admin.php'>Go to Admin</a><br>";
        } else {
            echo "❌ Password incorrect<br>";
        }
    } else {
        echo "❌ Admin not found<br>";
    }
}

$koneksi->close();
?>

<form method="POST">
    <h3>🧪 Test Login</h3>
    <input type="email" name="email" placeholder="Email" value="admin@mptitravel.com" required><br><br>
    <input type="password" name="password" placeholder="Password" value="admin123" required><br><br>
    <button type="submit">Test Login</button>
</form>

<p><a href="ViewLoginAdmin.php">Go to Real Login</a></p>
<p><a href="admin.php">Go to Admin Panel</a></p>
