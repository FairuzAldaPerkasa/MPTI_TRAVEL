<?php
$koneksi = new mysqli("localhost", "root", "", "paket_travel");

if ($koneksi->connect_error) {
    die("Connection failed: " . $koneksi->connect_error);
}

echo "<h2>🔐 Hashing Existing Passwords</h2>";

// Ambil semua admin
$result = $koneksi->query("SELECT id, email, password FROM admins");

while ($row = $result->fetch_assoc()) {
    $current_password = $row['password'];
    
    // Cek apakah sudah di-hash (panjang > 50 karakter dan ada $)
    if (strlen($current_password) < 60 || strpos($current_password, '$') === false) {
        // Hash password
        $hashed_password = password_hash($current_password, PASSWORD_DEFAULT);
        
        // Update di database
        $stmt = $koneksi->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $row['id']);
        
        if ($stmt->execute()) {
            echo "<p>✅ Password hashed for: {$row['email']}</p>";
        } else {
            echo "<p>❌ Failed to hash password for: {$row['email']}</p>";
        }
        
        $stmt->close();
    } else {
        echo "<p>ℹ️ Password already hashed for: {$row['email']}</p>";
    }
}

$koneksi->close();
?>