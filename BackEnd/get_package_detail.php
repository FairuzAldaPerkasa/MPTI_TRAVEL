<?php
// Enhanced Package Detail API with Multi-language Support
// filepath: c:\xampp\htdocs\MPTI_TRAVEL\BackEnd\get_package_detail.php

// Disable error display to prevent JSON corruption
ini_set('display_errors', 0);
error_reporting(0);

// Start output buffering
ob_start();

// Include language configuration
require_once 'language_config.php';

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, X-Language');

$startTime = microtime(true);

try {
    // Initialize language system
    $language_config = new LanguageConfig();
    
    // Validate package ID
    $package_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if (!$package_id || $package_id <= 0) {
        throw new Exception($language_config->translate('errors.invalid_package_id'));
    }    // Database connection
    $koneksi = new mysqli("localhost", "root", "", "paket_travel");
    
    if ($koneksi->connect_error) {
        throw new Exception($language_config->translate('errors.database_connection_failed') . ': ' . $koneksi->connect_error);
    }

    $koneksi->set_charset("utf8mb4");
    
    // Get package data
    $stmt = $koneksi->prepare("
        SELECT id, nama, deskripsi, fotos, itinerary, highlights, inclusions, exclusions, 
               CAST(price AS DECIMAL(12,2)) as price, duration
        FROM paket 
        WHERE id = ? 
        LIMIT 1
    ");
    
    if (!$stmt) {
        throw new Exception($language_config->translate('errors.query_preparation_failed') . ': ' . $koneksi->error);
    }
    
    $stmt->bind_param("i", $package_id);
    
    if (!$stmt->execute()) {
        throw new Exception($language_config->translate('errors.query_execution_failed') . ': ' . $stmt->error);
    }
      $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception($language_config->translate('errors.package_not_found'));
    }
    
    $package = $result->fetch_assoc();
    
    // Process main photos
    $processedFotos = [];
    $fotosArray = json_decode($package['fotos'], true);
    
    if (is_array($fotosArray) && !empty($fotosArray)) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $baseUrl = $protocol . '://' . $host . '/MPTI_TRAVEL/BackEnd/uploads/';
        
        foreach ($fotosArray as $foto) {
            if (!empty($foto)) {
                $fotoPath = __DIR__ . '/uploads/' . $foto;
                
                if (file_exists($fotoPath) && is_readable($fotoPath)) {
                    $processedFotos[] = [
                        'url' => $baseUrl . $foto,
                        'caption' => 'Foto Paket',
                        'type' => 'main'
                    ];
                }
            }
        }
    }
    
    // Get additional gallery photos
    $galleryStmt = $koneksi->prepare("
        SELECT photo_filename, caption, photo_order 
        FROM package_gallery 
        WHERE package_id = ? 
        ORDER BY photo_order ASC
    ");
    
    if ($galleryStmt) {
        $galleryStmt->bind_param("i", $package_id);
        $galleryStmt->execute();
        $galleryResult = $galleryStmt->get_result();
        
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $galleryBaseUrl = $protocol . '://' . $host . '/MPTI_TRAVEL/BackEnd/uploads/gallery/';
        
        while ($galleryPhoto = $galleryResult->fetch_assoc()) {
            $galleryPath = __DIR__ . '/uploads/gallery/' . $galleryPhoto['photo_filename'];
            
            if (file_exists($galleryPath) && is_readable($galleryPath)) {
                $processedFotos[] = [
                    'url' => $galleryBaseUrl . $galleryPhoto['photo_filename'],
                    'caption' => $galleryPhoto['caption'] ?: 'Galeri Foto',
                    'type' => 'gallery'
                ];
            }
        }
        $galleryStmt->close();
    }
    
    // Add fallback photos if none exist
    if (empty($processedFotos)) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $baseUrl = $protocol . '://' . $host . '/MPTI_TRAVEL/Asset/Package_Culture/';
        
        $processedFotos = [
            ['url' => $baseUrl . 'borobudur.jpg', 'caption' => 'Candi Borobudur', 'type' => 'default'],
            ['url' => $baseUrl . 'prambanan.jpg', 'caption' => 'Candi Prambanan', 'type' => 'default'],
            ['url' => $baseUrl . 'kraton.jpg', 'caption' => 'Keraton Yogyakarta', 'type' => 'default']
        ];
    }
      // Process default data with language support
    $description = trim($package['deskripsi'] ?? '');
    if (empty($description)) {
        $description = $language_config->translate('package.default_description');
    }
    
    // Process inclusions with defaults
    $inclusions = [];
    if ($package['inclusions']) {
        $inclusions = json_decode($package['inclusions'], true);
    }
    if (empty($inclusions) || !is_array($inclusions)) {
        $defaultInclusions = $language_config->getDefaultInclusions();
        $inclusions = [];
        foreach ($defaultInclusions as $item) {
            $inclusions['fas fa-check'] = $item;
        }
    }
    
    // Process exclusions with defaults
    $exclusions = [];
    if ($package['exclusions']) {
        $exclusions = json_decode($package['exclusions'], true);
    }
    if (empty($exclusions) || !is_array($exclusions)) {
        $defaultExclusions = $language_config->getDefaultExclusions();
        $exclusions = [];
        foreach ($defaultExclusions as $item) {
            $exclusions['fas fa-times'] = $item;
        }
    }
    
    // Process highlights with defaults
    $highlights = [];
    if ($package['highlights']) {
        $highlights = json_decode($package['highlights'], true);
    }
    if (empty($highlights) || !is_array($highlights)) {
        $highlights = $language_config->getDefaultHighlights();
    }
    
    // Build response
    $response = [
        'success' => true,
        'language' => $language_config->getLanguage(),
        'id' => (int)$package['id'],
        'nama' => trim($package['nama'] ?? ''),
        'deskripsi' => $description,
        'fotos' => $processedFotos,
        'itinerary' => $package['itinerary'] ? json_decode($package['itinerary'], true) : null,
        'itinerary_raw' => trim($package['itinerary'] ?? ''),
        'highlights' => $highlights,
        'inclusions' => $inclusions,
        'exclusions' => $exclusions,
        'price' => $package['price'] ? (float)$package['price'] : null,
        'formatted_price' => $language_config->formatCurrency($package['price']),
        'duration' => trim($package['duration'] ?? '2D1N'),
        'total_photos' => count($processedFotos),
        'price_note' => $language_config->translate('package.minimum_participants')
    ];
    
    $stmt->close();
    $koneksi->close();
    
    // Clear output buffer and send response
    ob_end_clean();
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    ob_end_clean();
    
    // Initialize language config for error if not exists
    if (!isset($language_config)) {
        $language_config = new LanguageConfig();
    }
    
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'language' => $language_config->getLanguage(),
        'error' => $e->getMessage(),
        'status' => $language_config->translate('status.failed')
    ], JSON_UNESCAPED_UNICODE);
}

exit;
?>