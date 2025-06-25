<?php
/**
 * API Endpoint to Fetch Gallery Photos for a Package
 *
 * This script retrieves all gallery photos associated with a specific package ID.
 * It returns a structured JSON response containing a list of photos or an error message.
 *
 * @version 1.1
 * @author MPTI_TRAVEL
 * @filepath c:\xampp\htdocs\MPTI_TRAVEL\BackEnd\get_gallery_photos.php
 */

// --- ERROR HANDLING & CONFIGURATION ---
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();

// --- HEADERS ---
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// --- DATABASE CONNECTION ---
$koneksi = new mysqli("localhost", "root", "", "paket_travel");
if ($koneksi->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $koneksi->connect_error]);
    exit;
}
$koneksi->set_charset("utf8");

// --- INPUT VALIDATION ---
if (!isset($_GET['package_id']) || !filter_var($_GET['package_id'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing Package ID']);
    exit;
}
$package_id = (int)$_GET['package_id'];

try {
    // --- DATA FETCHING ---
    // Prepare and execute the query to get gallery photos.
    $stmt = $koneksi->prepare("SELECT id, package_id, photo_filename, caption, photo_order, uploaded_at FROM package_gallery WHERE package_id = ? ORDER BY photo_order ASC, id ASC");
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // --- DATA PROCESSING ---
    $photos = [];
    while ($row = $result->fetch_assoc()) {
        // Construct the full URL for each photo.
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $baseUrl = $protocol . '://' . $host . '/MPTI_TRAVEL/BackEnd/uploads/gallery/';

        $photos[] = [
            'id' => (int)$row['id'],
            'package_id' => (int)$row['package_id'],
            'photo_filename' => $row['photo_filename'],
            'photo_url' => $baseUrl . htmlspecialchars($row['photo_filename'], ENT_QUOTES, 'UTF-8'),
            'caption' => $row['caption'] ?? '',
            'photo_order' => (int)$row['photo_order'],
            'uploaded_at' => $row['uploaded_at']
        ];
    }
    
    // --- CLEANUP & RESPONSE ---
    $stmt->close();
    $koneksi->close();
    ob_end_clean();
    
    echo json_encode(['status' => 'success', 'data' => $photos], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch(Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An unexpected error occurred: ' . $e->getMessage()]);
}
?>