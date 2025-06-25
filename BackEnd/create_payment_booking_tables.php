<?php
/**
 * Create Payment Methods & Booking History Tables
 * 
 * @version 1.0
 * @author MPTI_TRAVEL
 */

// Database connection
$koneksi = new mysqli("localhost", "root", "", "paket_travel");

if ($koneksi->connect_error) {
    die("Connection failed: " . $koneksi->connect_error);
}

echo "<h2>🏦 Creating Payment Methods & Booking History Tables...</h2>";

// Create payment_methods table
$payment_methods_sql = "CREATE TABLE IF NOT EXISTS payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    method_name VARCHAR(100) NOT NULL,
    method_type ENUM('bank', 'ewallet', 'card', 'other') DEFAULT 'other',
    icon_class VARCHAR(100) DEFAULT 'fas fa-credit-card',
    is_active TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($koneksi->query($payment_methods_sql) === TRUE) {
    echo "✅ Table 'payment_methods' created successfully<br>";
} else {
    echo "❌ Error creating payment_methods table: " . $koneksi->error . "<br>";
}

// Create booking_history table
$booking_history_sql = "CREATE TABLE IF NOT EXISTS booking_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(200) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_email VARCHAR(200),
    package_id INT,
    package_name VARCHAR(300) NOT NULL,
    booking_date DATE NOT NULL,
    travel_date DATE,
    participants INT DEFAULT 1,
    total_price DECIMAL(15,2) NOT NULL,
    payment_method VARCHAR(100),
    payment_status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    booking_status ENUM('confirmed', 'cancelled', 'completed') DEFAULT 'confirmed',
    notes TEXT,
    whatsapp_number VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES paket(id) ON DELETE SET NULL
)";

if ($koneksi->query($booking_history_sql) === TRUE) {
    echo "✅ Table 'booking_history' created successfully<br>";
} else {
    echo "❌ Error creating booking_history table: " . $koneksi->error . "<br>";
}

// Insert default payment methods
$default_payments = [
    ['BCA', 'bank', 'fab fa-cc-visa', 1, 1],
    ['Mandiri', 'bank', 'fas fa-university', 1, 2],
    ['BNI', 'bank', 'fas fa-university', 1, 3],
    ['BRI', 'bank', 'fas fa-university', 1, 4],
    ['VISA', 'card', 'fab fa-cc-visa', 1, 5],
    ['Mastercard', 'card', 'fab fa-cc-mastercard', 1, 6],
    ['GoPay', 'ewallet', 'fas fa-mobile-alt', 1, 7],
    ['OVO', 'ewallet', 'fas fa-wallet', 1, 8],
    ['DANA', 'ewallet', 'fas fa-mobile-alt', 1, 9],
    ['ShopeePay', 'ewallet', 'fas fa-shopping-bag', 1, 10]
];

echo "<br><h3>💳 Inserting Default Payment Methods...</h3>";

foreach ($default_payments as $payment) {
    $stmt = $koneksi->prepare("INSERT INTO payment_methods (method_name, method_type, icon_class, is_active, display_order) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssii", $payment[0], $payment[1], $payment[2], $payment[3], $payment[4]);
    
    if ($stmt->execute()) {
        echo "✅ Added payment method: <strong>{$payment[0]}</strong><br>";
    } else {
        echo "❌ Failed to add: {$payment[0]}<br>";
    }
    $stmt->close();
}

// Insert sample booking history
echo "<br><h3>📋 Inserting Sample Booking History...</h3>";

// First, get existing package IDs
$package_result = $koneksi->query("SELECT id FROM paket LIMIT 3");
$package_ids = [];
while ($row = $package_result->fetch_assoc()) {
    $package_ids[] = $row['id'];
}

// Use NULL for package_id if no packages exist
$sample_bookings = [
    ['John Doe', '081234567890', 'john@email.com', !empty($package_ids) ? $package_ids[0] : null, '2D1N Wisata Yogyakarta', '2025-06-20', '2025-07-15', 2, 3000000, 'BCA', 'paid', 'confirmed', 'Booking melalui WhatsApp', '6281234567890'],
    ['Jane Smith', '081987654321', 'jane@email.com', !empty($package_ids) && isset($package_ids[1]) ? $package_ids[1] : null, '3D2N Adventure Jogja', '2025-06-21', '2025-08-10', 4, 6000000, 'GoPay', 'pending', 'confirmed', 'Menunggu konfirmasi pembayaran', '6281987654321'],
    ['Budi Santoso', '082111222333', 'budi@email.com', !empty($package_ids) ? $package_ids[0] : null, '2D1N Wisata Yogyakarta', '2025-06-22', '2025-07-20', 1, 1500000, 'OVO', 'paid', 'completed', 'Trip sudah selesai', '6282111222333']
];

foreach ($sample_bookings as $booking) {
    $stmt = $koneksi->prepare("INSERT INTO booking_history (customer_name, customer_phone, customer_email, package_id, package_name, booking_date, travel_date, participants, total_price, payment_method, payment_status, booking_status, notes, whatsapp_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssississsssss", $booking[0], $booking[1], $booking[2], $booking[3], $booking[4], $booking[5], $booking[6], $booking[7], $booking[8], $booking[9], $booking[10], $booking[11], $booking[12], $booking[13]);
    
    if ($stmt->execute()) {
        echo "✅ Added booking: <strong>{$booking[0]}</strong> - {$booking[4]}<br>";
    } else {
        echo "❌ Failed to add booking: {$booking[0]}<br>";
    }
    $stmt->close();
}

echo "<br><h3>📊 Database Summary:</h3>";
$payment_count = $koneksi->query("SELECT COUNT(*) as count FROM payment_methods")->fetch_assoc()['count'];
$booking_count = $koneksi->query("SELECT COUNT(*) as count FROM booking_history")->fetch_assoc()['count'];

echo "💳 Payment Methods: <strong>$payment_count</strong><br>";
echo "📋 Booking Records: <strong>$booking_count</strong><br>";

echo "<br><h3>🔗 Next Steps:</h3>";
echo "1. <a href='admin.php#payment-methods'>Manage Payment Methods</a><br>";
echo "2. <a href='admin.php#booking-history'>View Booking History</a><br>";
echo "3. Update frontend to use dynamic payment methods<br>";

$koneksi->close();
?>
