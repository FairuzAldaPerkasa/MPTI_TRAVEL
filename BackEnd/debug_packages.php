<?php
/**
 * Debug Packages
 * Debug and inspect package data structure
 * 
 * @version 1.0
 * @author MPTI_TRAVEL
 */

// Database connection
$koneksi = new mysqli("localhost", "root", "", "paket_travel");

if ($koneksi->connect_error) {
    die("Connection failed: " . $koneksi->connect_error);
}

echo "<h2>🔍 Package Debug Information</h2>";

// Get package count
$count_result = $koneksi->query("SELECT COUNT(*) as total FROM paket");
$total_packages = $count_result->fetch_assoc()['total'];

echo "<p><strong>Total Packages:</strong> $total_packages</p>";

if ($total_packages > 0) {
    // Show package details
    $result = $koneksi->query("SELECT * FROM paket ORDER BY id DESC LIMIT 3");
    
    echo "<h3>📋 Latest Packages (Max 3):</h3>";
    
    while ($package = $result->fetch_assoc()) {
        echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin: 10px 0;'>";
        echo "<h4>🎯 Package ID: {$package['id']}</h4>";
        echo "<p><strong>Name:</strong> {$package['nama']}</p>";
        echo "<p><strong>Price:</strong> Rp " . number_format($package['price'], 0, ',', '.') . "</p>";
        echo "<p><strong>Duration:</strong> {$package['duration']}</p>";
        echo "<p><strong>Created:</strong> {$package['created_at']}</p>";
        
        // Parse JSON fields
        echo "<h5>📸 Photos:</h5>";
        $photos = json_decode($package['fotos'], true);
        if ($photos && is_array($photos)) {
            echo "<ul>";
            foreach ($photos as $photo) {
                echo "<li>$photo</li>";
            }
            echo "</ul>";
        } else {
            echo "<p><em>No photos or invalid JSON</em></p>";
        }
        
        echo "<h5>⭐ Highlights:</h5>";
        $highlights = json_decode($package['highlights'], true);
        if ($highlights && is_array($highlights)) {
            echo "<ul>";
            foreach ($highlights as $highlight) {
                echo "<li>$highlight</li>";
            }
            echo "</ul>";
        } else {
            echo "<p><em>No highlights or invalid JSON</em></p>";
        }
        
        echo "<h5>📅 Itinerary:</h5>";
        $itinerary = json_decode($package['itinerary'], true);
        if ($itinerary && is_array($itinerary)) {
            foreach ($itinerary as $day) {
                echo "<strong>Day {$day['day']}: {$day['title']}</strong><br>";
                if (isset($day['activities']) && is_array($day['activities'])) {
                    foreach ($day['activities'] as $activity) {
                        echo "- {$activity['time']}: {$activity['activity']}<br>";
                    }
                }
                echo "<br>";
            }
        } else {
            echo "<p><em>No itinerary or invalid JSON</em></p>";
        }
        
        echo "</div>";
    }
} else {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px;'>";
    echo "<h3>ℹ️ No Packages Found</h3>";
    echo "<p>Add some packages first to see debug information.</p>";
    echo "<p><a href='admin.php#add-package'>Add Package</a></p>";
    echo "</div>";
}

// Check gallery data
echo "<hr>";
echo "<h3>🖼️ Gallery Debug:</h3>";
$gallery_result = $koneksi->query("SELECT COUNT(*) as total FROM gallery");
$total_gallery = $gallery_result->fetch_assoc()['total'];
echo "<p><strong>Total Gallery Images:</strong> $total_gallery</p>";

$koneksi->close();

echo "<hr>";
echo "<p><a href='admin.php'>Back to Admin Panel</a></p>";
?>
