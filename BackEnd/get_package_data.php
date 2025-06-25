<?php
/**
 * Get Package Data for Editing
 * 
 * @version 1.0
 * @author MPTI_TRAVEL
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $package_id = (int)($_GET['id'] ?? 0);
    
    if ($package_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid package ID']);
        exit;
    }
    
    // Database connection
    $koneksi = new mysqli("localhost", "root", "", "paket_travel");
    
    if ($koneksi->connect_error) {
        throw new Exception('Database connection failed');
    }
    
    $koneksi->set_charset("utf8mb4");
    
    // Get package data
    $stmt = $koneksi->prepare("SELECT * FROM paket WHERE id = ?");
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Package not found']);
        exit;
    }
    
    $package = $result->fetch_assoc();
    
    // Parse JSON fields
    $package['fotos'] = json_decode($package['fotos'] ?? '[]', true);
    $package['highlights'] = json_decode($package['highlights'] ?? '[]', true);
    $package['itinerary'] = json_decode($package['itinerary'] ?? '[]', true);
    $package['inclusions'] = json_decode($package['inclusions'] ?? '[]', true);
    $package['exclusions'] = json_decode($package['exclusions'] ?? '[]', true);
    
    // Format price for display
    $package['price_formatted'] = number_format($package['price'], 0, ',', '.');
    
    echo json_encode([
        'success' => true,
        'data' => $package
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred'
    ], JSON_UNESCAPED_UNICODE);
}

$koneksi->close();
?>
