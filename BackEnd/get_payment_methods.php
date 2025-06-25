<?php
/**
 * API untuk mengambil payment methods
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
    
    // Get active payment methods
    $sql = "SELECT id, method_name, method_type, icon_class, display_order 
            FROM payment_methods 
            WHERE is_active = 1 
            ORDER BY display_order ASC, method_name ASC";
    
    $result = $koneksi->query($sql);
    
    $payment_methods = [];
    while ($row = $result->fetch_assoc()) {
        $payment_methods[] = [
            'id' => (int)$row['id'],
            'name' => $row['method_name'],
            'type' => $row['method_type'],
            'icon' => $row['icon_class'],
            'order' => (int)$row['display_order']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $payment_methods,
        'total' => count($payment_methods)
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
