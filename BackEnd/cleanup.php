<?php
/**
 * Database Cleanup Script
 * Cleans up test data and resets tables if needed
 * 
 * @version 1.0
 * @author MPTI_TRAVEL
 */

// Database connection
$koneksi = new mysqli("localhost", "root", "", "paket_travel");

if ($koneksi->connect_error) {
    die("Connection failed: " . $koneksi->connect_error);
}

echo "<h2>🧹 Database Cleanup Script</h2>";

// WARNING MESSAGE
echo "<div style='background: #fef2f2; border: 2px solid #ef4444; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>⚠️ WARNING</h3>";
echo "<p>This script will clean up test data. Make sure you want to proceed!</p>";
echo "<p>Uncomment specific sections below to perform cleanup operations.</p>";
echo "</div>";

// Show current data counts
echo "<h3>📊 Current Data Status:</h3>";

$tables = [
    'paket' => 'Packages',
    'gallery' => 'Gallery Images', 
    'website_settings' => 'Website Settings',
    'payment_methods' => 'Payment Methods',
    'booking_history' => 'Booking History',
    'newsletter_subscribers' => 'Newsletter Subscribers',
    'email_campaigns' => 'Email Campaigns'
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'>";
echo "<th style='padding: 8px;'>Table</th>";
echo "<th style='padding: 8px;'>Count</th>";
echo "</tr>";

foreach ($tables as $table => $name) {
    $result = $koneksi->query("SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "<tr>";
        echo "<td style='padding: 8px;'>$name ($table)</td>";
        echo "<td style='padding: 8px;'>$count</td>";
        echo "</tr>";
    }
}
echo "</table>";

// Cleanup operations (commented out for safety)
/*
echo "<hr>";
echo "<h3>🗑️ Cleanup Operations:</h3>";

// Clean up test newsletter subscribers
$test_emails = ['test@example.com', 'demo@test.com'];
foreach ($test_emails as $email) {
    $stmt = $koneksi->prepare("DELETE FROM newsletter_subscribers WHERE email = ?");
    $stmt->bind_param("s", $email);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "✅ Deleted test subscriber: $email<br>";
    }
    $stmt->close();
}

// Clean up test booking entries
$koneksi->query("DELETE FROM booking_history WHERE customer_name LIKE '%test%' OR customer_name LIKE '%demo%'");
echo "✅ Cleaned up test booking entries<br>";

// Reset auto increment for empty tables
$tables_to_reset = ['gallery', 'email_campaigns'];
foreach ($tables_to_reset as $table) {
    $count_result = $koneksi->query("SELECT COUNT(*) as count FROM $table");
    $count = $count_result->fetch_assoc()['count'];
    if ($count == 0) {
        $koneksi->query("ALTER TABLE $table AUTO_INCREMENT = 1");
        echo "✅ Reset auto increment for $table<br>";
    }
}
*/

echo "<hr>";
echo "<div style='background: #dbeafe; padding: 15px; border-radius: 8px;'>";
echo "<h3>📝 Manual Cleanup Instructions:</h3>";
echo "<ol>";
echo "<li>Uncomment the cleanup section in this script</li>";
echo "<li>Run the script to clean test data</li>";
echo "<li>Check data integrity after cleanup</li>";
echo "<li>Re-comment the cleanup section</li>";
echo "</ol>";
echo "</div>";

$koneksi->close();
?>
