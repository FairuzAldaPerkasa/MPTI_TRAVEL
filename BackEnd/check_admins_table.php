<?php
/**
 * Check Admins Table Structure
 */

$koneksi = new mysqli("localhost", "root", "", "paket_travel");

if ($koneksi->connect_error) {
    die("Connection failed: " . $koneksi->connect_error);
}

echo "<h2>📊 Checking Admins Table Structure...</h2>";

// Check if table exists
$check_table = $koneksi->query("SHOW TABLES LIKE 'admins'");
if ($check_table->num_rows > 0) {
    echo "✅ Table 'admins' exists<br><br>";
    
    // Show table structure
    echo "<h3>Table Structure:</h3>";
    $structure = $koneksi->query("DESCRIBE admins");
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    // Show data
    echo "<h3>Current Data:</h3>";
    $data = $koneksi->query("SELECT * FROM admins");
    if ($data->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Email</th><th>Name</th><th>Created At</th></tr>";
        
        while ($row = $data->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td>" . ($row['name'] ?? 'N/A') . "</td>";
            echo "<td>" . ($row['created_at'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No data found in admins table.</p>";
    }
    
} else {
    echo "❌ Table 'admins' does not exist<br>";
    echo "<p>Available tables:</p>";
    $tables = $koneksi->query("SHOW TABLES");
    while ($table = $tables->fetch_array()) {
        echo "- " . $table[0] . "<br>";
    }
}

$koneksi->close();
?>
