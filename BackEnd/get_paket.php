<?php
// Enhanced Package List API with Multi-language Support
// filepath: c:\xampp\htdocs\MPTI_TRAVEL\BackEnd\get_paket.php

// Pastikan tidak ada output sebelum header
ob_start();

// Include language configuration
require_once 'language_config.php';

// Set header dengan encoding yang benar
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, X-Language');

try {
    // Initialize language system
    $language_config = new LanguageConfig();
    
    // Koneksi database
    $koneksi = new mysqli("localhost", "root", "", "paket_travel");
    
    if ($koneksi->connect_error) {
        throw new Exception($language_config->translate('errors.database_connection_failed') . ': ' . $koneksi->connect_error);
    }

    // Set charset untuk database
    $koneksi->set_charset("utf8");

    // Pastikan menggunakan kolom 'fotos' dan price
    $stmt = $koneksi->prepare("SELECT id, nama, deskripsi, fotos, CAST(price AS DECIMAL(12,2)) as price, duration FROM paket ORDER BY id DESC");
    $stmt->execute();
    $result = $stmt->get_result();

    $paket = [];
    while ($row = $result->fetch_assoc()) {
        // Decode photos JSON
        $fotosArray = json_decode($row['fotos'], true);
        
        // Handle different data formats
        if (!is_array($fotosArray)) {
            $fotosArray = !empty($row['fotos']) ? [$row['fotos']] : [];
        }
        
        $processedFotos = [];
        $fotosExist = [];
        
        foreach ($fotosArray as $index => $foto) {
            if (empty($foto)) continue;
            
            $fotoPath = 'uploads/' . $foto;
            $fullPath = __DIR__ . '/' . $fotoPath;
            
            // Enhanced file checking
            $fileExists = file_exists($fullPath);
            $fileReadable = $fileExists ? is_readable($fullPath) : false;
            $fileSize = $fileExists ? filesize($fullPath) : 0;
            
            // Log file check for debugging
            error_log("File check - {$foto}: exists={$fileExists}, readable={$fileReadable}, size={$fileSize}");
            
            if ($fileExists && $fileReadable && $fileSize > 0) {
                // Create absolute URL for better compatibility
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $baseUrl = $protocol . '://' . $host . '/MPTI_TRAVEL/BackEnd/uploads/';
                
                $processedFotos[] = $baseUrl . htmlspecialchars($foto, ENT_QUOTES, 'UTF-8');
                $fotosExist[] = true;
            } else {
                // Use placeholder
                $processedFotos[] = getPlaceholderImage($row['nama'], $row['deskripsi'], $index);
                $fotosExist[] = false;
            }
        }
        
        // Ensure minimum 3 photos for slideshow
        while (count($processedFotos) < 3) {
            $processedFotos[] = getPlaceholderImage($row['nama'], $row['deskripsi'], count($processedFotos));
            $fotosExist[] = false;
        }        $paket[] = [
            'id' => (int)$row['id'],
            'nama' => trim(htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8')),
            'deskripsi' => trim(htmlspecialchars($row['deskripsi'], ENT_QUOTES, 'UTF-8')),
            'fotos' => $processedFotos,
            'fotos_original' => $fotosArray,
            'fotos_exist' => $fotosExist,
            'foto_count' => count($processedFotos),
            'price' => $row['price'] ? (float)$row['price'] : null,
            'formatted_price' => $language_config->formatCurrency($row['price']),
            'duration' => trim($row['duration'] ?? '2D1N'),
            'price_note' => $language_config->translate('package.minimum_participants')
        ];
    }
    
    $stmt->close();
    $koneksi->close();
    
    ob_end_clean();
    
    // Return proper structure expected by frontend
    $response = [
        'success' => true,
        'language' => $language_config->getLanguage(),
        'packages' => $paket,
        'total' => count($paket),
        'status' => $language_config->translate('status.success')
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    ob_end_clean();
    
    // Initialize language config for error if not exists
    if (!isset($language_config)) {
        $language_config = new LanguageConfig();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'language' => $language_config->getLanguage(),
        'error' => $e->getMessage(),
        'packages' => [],
        'status' => $language_config->translate('status.failed')
    ], JSON_UNESCAPED_UNICODE);
}

exit;

function getPlaceholderImage($nama, $deskripsi, $index = 0) {
    // Use absolute URLs for placeholders too
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = $protocol . '://' . $host . '/MPTI_TRAVEL/Asset/Package_Culture/';
    
    $placeholders = [
        $baseUrl . 'borobudur.jpg',
        $baseUrl . 'prambanan.jpg',
        $baseUrl . 'kraton.jpg',
        $baseUrl . 'malioboro.jpg',
        $baseUrl . 'taman_sari.jpg'
    ];
    
    return $placeholders[$index % count($placeholders)];
}
?>
