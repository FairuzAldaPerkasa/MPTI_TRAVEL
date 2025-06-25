<?php
/**
 * API untuk mengambil website settings
 * 
 * @version 1.0
 * @author MPTI_TRAVEL
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Database connection
    $koneksi = new mysqli("localhost", "root", "", "paket_travel");
    
    if ($koneksi->connect_error) {
        throw new Exception('Database connection failed');
    }
    
    $koneksi->set_charset("utf8mb4");
    
    // Get all settings
    $sql = "SELECT setting_key, setting_value FROM website_settings";
    $result = $koneksi->query($sql);
    
    $settings = [];
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $settings
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$koneksi->close();
?>
