<?php
/**
 * Debug Session - Check admin session status
 */
session_start();

echo "<h2>🔍 Admin Session Debug</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>";

echo "<h3>Session Status:</h3>";
echo "<ul>";
echo "<li><strong>Session ID:</strong> " . session_id() . "</li>";
echo "<li><strong>Admin Logged In:</strong> " . (isset($_SESSION['admin_logged_in']) ? ($_SESSION['admin_logged_in'] ? 'YES' : 'NO') : 'NOT SET') . "</li>";

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    echo "<li><strong>Admin ID:</strong> " . ($_SESSION['admin_id'] ?? 'NOT SET') . "</li>";
    echo "<li><strong>Admin Name:</strong> " . ($_SESSION['admin_name'] ?? 'NOT SET') . "</li>";
    echo "<li><strong>Admin Email:</strong> " . ($_SESSION['admin_email'] ?? 'NOT SET') . "</li>";
    echo "<li><strong>Admin Role:</strong> " . ($_SESSION['admin_role'] ?? 'NOT SET') . "</li>";
}
echo "</ul>";

echo "<h3>All Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Quick Actions:</h3>";
echo "<p>";
echo "<a href='ViewLoginAdmin.php' style='background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin: 5px;'>Go to Login</a>";
echo "<a href='admin.php' style='background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin: 5px;'>Go to Admin</a>";
echo "<a href='ViewLoginAdmin.php?logout=1' style='background: #dc3545; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin: 5px;'>Logout</a>";
echo "</p>";

echo "</div>";

// Test database connection
echo "<h3>Database Connection Test:</h3>";
$koneksi = new mysqli("localhost", "root", "", "paket_travel");
if ($koneksi->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $koneksi->connect_error . "</p>";
} else {
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Check admin table
    $result = $koneksi->query("SELECT COUNT(*) as count FROM admin");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "<p>👤 Admin users in database: <strong>$count</strong></p>";
    }
    
    $koneksi->close();
}
?>

<script>
// Auto refresh every 10 seconds
setTimeout(function() {
    location.reload();
}, 10000);
</script>
