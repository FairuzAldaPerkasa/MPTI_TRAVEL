<?php
/**
 * Update Settings Table - Add Missing Fields
 * 
 * @version 1.0
 * @author MPTI_TRAVEL
 */

// Database connection
$koneksi = new mysqli("localhost", "root", "", "paket_travel");

if ($koneksi->connect_error) {
    die("Connection failed: " . $koneksi->connect_error);
}

echo "<h2>🔄 Updating Website Settings...</h2>";

// Additional settings to insert/update
$additional_settings = [
    'email' => 'info@mptitravel.com',
    'website_name' => 'MPTI Travel',
    'whatsapp_message' => 'Halo, saya tertarik dengan paket wisata dari MPTI Travel. Bisakah Anda memberikan informasi lebih lanjut?',
    'address' => 'Yogyakarta, Indonesia',
    'city' => 'Yogyakarta',
    'province' => 'DIY',
    'description' => 'MPTI Travel - Solusi perjalanan wisata terbaik untuk liburan Anda',
    'instagram' => '@mptitravel'
];

foreach ($additional_settings as $key => $value) {
    // Check if setting exists
    $check = $koneksi->prepare("SELECT setting_key FROM website_settings WHERE setting_key = ?");
    $check->bind_param("s", $key);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing
        $update = $koneksi->prepare("UPDATE website_settings SET setting_value = ? WHERE setting_key = ?");
        $update->bind_param("ss", $value, $key);
        if ($update->execute()) {
            echo "✅ Updated: <strong>$key</strong> = $value<br>";
        } else {
            echo "❌ Failed to update: $key<br>";
        }
        $update->close();
    } else {
        // Insert new
        $insert = $koneksi->prepare("INSERT INTO website_settings (setting_key, setting_value) VALUES (?, ?)");
        $insert->bind_param("ss", $key, $value);
        if ($insert->execute()) {
            echo "✅ Added: <strong>$key</strong> = $value<br>";
        } else {
            echo "❌ Failed to add: $key<br>";
        }
        $insert->close();
    }
    $check->close();
}

// Update existing settings to use consistent format
$updates = [
    'company_email' => 'info@mptitravel.com',
    'instagram_handle' => '@mptitravel',
    'company_address' => 'Yogyakarta, Indonesia',
    'website_url' => 'https://mptitravel.com'
];

foreach ($updates as $key => $value) {
    $update = $koneksi->prepare("UPDATE website_settings SET setting_value = ? WHERE setting_key = ?");
    $update->bind_param("ss", $value, $key);
    if ($update->execute()) {
        echo "🔄 Updated: <strong>$key</strong> = $value<br>";
    }
    $update->close();
}

echo "<br><h3>📋 Current Settings:</h3>";
$result = $koneksi->query("SELECT setting_key, setting_value FROM website_settings ORDER BY setting_key");
while ($row = $result->fetch_assoc()) {
    echo "<strong>{$row['setting_key']}:</strong> {$row['setting_value']}<br>";
}

echo "<br><h3>🔗 Next Steps:</h3>";
echo "1. <a href='admin.php#settings'>Test Admin Settings Panel</a><br>";
echo "2. <a href='get_settings.php'>Test Settings API</a><br>";
echo "3. <a href='../FrontEnd/html/Index.html'>Test Frontend Contact Loading</a><br>";

$koneksi->close();
?>
