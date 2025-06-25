<?php
/**
 * Create Newsletter Subscribers & Email Campaigns Tables
 * 
 * @version 1.0
 * @author MPTI_TRAVEL
 */

// Database connection
$koneksi = new mysqli("localhost", "root", "", "paket_travel");

if ($koneksi->connect_error) {
    die("Connection failed: " . $koneksi->connect_error);
}

echo "<h2>📧 Creating Newsletter & Email Campaign Tables...</h2>";

// Create newsletter_subscribers table
$newsletter_sql = "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(200),
    status ENUM('active', 'inactive', 'unsubscribed') DEFAULT 'active',
    source VARCHAR(100) DEFAULT 'website',
    subscribe_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP NULL,
    preferences JSON NULL,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_subscribe_date (subscribe_date)
)";

if ($koneksi->query($newsletter_sql) === TRUE) {
    echo "✅ Table 'newsletter_subscribers' created successfully<br>";
} else {
    echo "❌ Error creating newsletter_subscribers table: " . $koneksi->error . "<br>";
}

// Create email_campaigns table
$campaigns_sql = "CREATE TABLE IF NOT EXISTS email_campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_name VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    status ENUM('draft', 'sent', 'scheduled') DEFAULT 'draft',
    created_by VARCHAR(100) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL,
    recipients_count INT DEFAULT 0,
    opened_count INT DEFAULT 0,
    clicked_count INT DEFAULT 0,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
)";

if ($koneksi->query($campaigns_sql) === TRUE) {
    echo "✅ Table 'email_campaigns' created successfully<br>";
} else {
    echo "❌ Error creating email_campaigns table: " . $koneksi->error . "<br>";
}

// Insert sample newsletter subscriber
$insert_sample = "INSERT IGNORE INTO newsletter_subscribers (email, name, source) VALUES 
    ('sample@example.com', 'Sample User', 'website'),
    ('admin@mptitravel.com', 'Admin MPTI Travel', 'admin')";

if ($koneksi->query($insert_sample) === TRUE) {
    echo "✅ Sample newsletter subscribers added<br>";
} else {
    echo "❌ Error adding sample subscribers: " . $koneksi->error . "<br>";
}

echo "<br>📧 Newsletter tables created successfully!<br>";
echo "<p><strong>Tables created:</strong></p>";
echo "<ul>";
echo "<li>newsletter_subscribers - For storing email subscribers</li>";
echo "<li>email_campaigns - For storing email campaign history</li>";
echo "</ul>";

echo "<p><strong>Next steps:</strong></p>";
echo "<ul>";
echo "<li>Test newsletter subscription from frontend</li>";
echo "<li>Send test email campaign from admin panel</li>";
echo "<li>Monitor subscriber engagement</li>";
echo "</ul>";

$koneksi->close();
?>
