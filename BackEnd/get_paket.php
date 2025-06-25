<?php
/**
 * API Endpoint to Fetch Travel Packages
 *
 * This script retrieves a list of all travel packages from the database.
 * It includes multi-language support for package details and currency formatting.
 * The script ensures data consistency, provides fallback image placeholders,
 * and sanitizes all output for security.
 *
 * @version 1.3
 * @author MPTI_TRAVEL
 * @filepath c:\xampp\htdocs\MPTI_TRAVEL\BackEnd\get_paket.php
 */

// --- ROBUST ERROR HANDLING & CONFIGURATION ---

// Prevent displaying errors to the user, which would corrupt the JSON output.
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start output buffering to catch any stray output.
ob_start();

// Set a custom error handler to convert errors into exceptions
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Register a shutdown function to catch fatal errors.
// This ensures we always output a valid JSON response, even on fatal errors.
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        // Clean the output buffer to prevent mixed content
        ob_end_clean(); 
        
        // Set headers for JSON response
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500); // Internal Server Error

        // Create a detailed error response
        echo json_encode([
            'success' => false,
            'message' => 'A critical server error occurred.',
            'error' => [
                'type' => 'FATAL_ERROR',
                'details' => $error['message'],
                'file' => basename($error['file']), // Show only filename for security
                'line' => $error['line']
            ]
        ]);
        exit();
    }
});

// --- DEPENDENCIES ---
require_once 'language_config.php';

// --- HEADERS ---
// Set standard JSON content type and UTF-8 encoding.
header('Content-Type: application/json; charset=utf-8');
// Allow cross-origin requests (adjust in production for security).
header('Access-Control-Allow-Origin: *');
// Specify allowed HTTP methods.
header('Access-Control-Allow-Methods: GET');
// Specify allowed headers.
header('Access-Control-Allow-Headers: Content-Type, X-Language');

try {
    // --- INITIALIZATION ---
    // Initialize the language system to handle translations.
    $language_config = new LanguageConfig();
    
    // --- DATABASE CONNECTION ---
    // Establish a connection to the database.
    $koneksi = new mysqli("localhost", "root", "", "paket_travel");
    
    // Check for connection errors and throw a translated exception if any.
    if ($koneksi->connect_error) {
        throw new Exception($language_config->translate('errors.database_connection_failed') . ': ' . $koneksi->connect_error);
    }

    // Set the character set to UTF-8 to support international characters.
    $koneksi->set_charset("utf8");

    // --- DATA FETCHING ---
    // Prepare the SQL statement to select all packages.
    // - Fetches essential columns: id, nama, deskripsi, fotos, price, duration.
    // - Casts the price to a decimal for accurate calculations.
    // - Orders by ID descending to show the newest packages first.
    $stmt = $koneksi->prepare("SELECT id, nama, deskripsi, fotos, CAST(price AS DECIMAL(12,2)) as price, duration FROM paket ORDER BY id DESC");
    $stmt->execute();
    $result = $stmt->get_result();

    // --- DATA PROCESSING ---
    $paket = [];
    while ($row = $result->fetch_assoc()) {
        // Decode the 'fotos' JSON string into a PHP array.
        $fotosArray = json_decode($row['fotos'], true);
        
        // Fallback for cases where 'fotos' might be a single string instead of a JSON array.
        if (!is_array($fotosArray)) {
            $fotosArray = !empty($row['fotos']) ? [$row['fotos']] : [];
        }
        
        $processedFotos = [];
        $fotosExist = [];
        
        // --- IMAGE PROCESSING AND VALIDATION ---
        foreach ($fotosArray as $index => $foto) {
            if (empty($foto)) continue;
            
            // Construct the full server path for the image file.
            $fotoPath = 'uploads/' . basename($foto); // Use basename for security
            $fullPath = __DIR__ . '/' . $fotoPath;
            
            // Check if the file exists, is readable, and has a size greater than 0.
            if (file_exists($fullPath) && is_readable($fullPath) && filesize($fullPath) > 0) {
                // Construct a full, absolute URL for the image.
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                // IMPORTANT: Corrected the base URL to point to the root of the project
                $baseUrl = $protocol . '://' . $host . '/MPTI_TRAVEL/BackEnd/uploads/';
                
                // Add the sanitized, absolute URL to the list.
                $processedFotos[] = $baseUrl . htmlspecialchars(basename($foto), ENT_QUOTES, 'UTF-8');
                $fotosExist[] = true;
            } else {
                // If the image is invalid or missing, use a generated placeholder.
                $processedFotos[] = getPlaceholderImage($row['nama'], $row['deskripsi'], $index);
                $fotosExist[] = false;
            }
        }
        
        // If no valid photos were found after processing, add a final placeholder.
        if (empty($processedFotos)) {
            $processedFotos[] = getPlaceholderImage($row['nama'], $row['deskripsi'], count($processedFotos));
            $fotosExist[] = false;
        }

        // --- DATA ASSEMBLY ---
        // Add the processed package data to the main array.
        $paket[] = [
            'id' => $row['id'],
            // Sanitize and translate package name and description.
            'nama' => htmlspecialchars($language_config->translate($row['nama']), ENT_QUOTES, 'UTF-8'),
            'deskripsi' => htmlspecialchars($language_config->translate($row['deskripsi']), ENT_QUOTES, 'UTF-8'),
            'fotos' => $processedFotos,
            'price' => $row['price'], // Raw price for potential frontend calculations.
            'formattedPrice' => $language_config->formatCurrency($row['price']), // Price formatted as currency string.
            'duration' => $row['duration'],
            'fotos_exist' => $fotosExist // Information about whether images are real or placeholders.
        ];
    }

    // --- FINAL RESPONSE ---
    // Set a success response code.
    http_response_code(200);
    // Encode the final package list into a JSON object.
    echo json_encode([
        'success' => true, 
        'message' => $language_config->translate('messages.packages_loaded'),
        'data' => $paket
    ]);

} catch (Throwable $e) { // Catch both Exceptions and Errors
    // If any error occurs, send a structured JSON error response.
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching packages.',
        'error' => [
            'type' => get_class($e),
            'details' => $e->getMessage(),
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ]
    ]);
} finally {
    // --- CLEANUP ---
    // Close the database connection if it was opened.
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($koneksi)) {
        $koneksi->close();
    }
    // Clean and send the output buffer.
    ob_end_flush();
}

/**
 * Generates a placeholder image URL using a service like placehold.co.
 * This provides a visually appealing fallback when an image is missing.
 *
 * @param string $title The main title for the placeholder.
 * @param string $subtitle A secondary text or description.
 * @param int $index An index to differentiate placeholders.
 * @return string The full URL for the placeholder image.
 */
function getPlaceholderImage($title, $subtitle, $index) {
    $title = urlencode(substr($title, 0, 20)); // Limit length
    $subtitle = urlencode(substr($subtitle, 0, 30));
    return "https://placehold.co/800x600/E1E1E1/333333?text={$title}\n{$subtitle}&font=roboto";
}
?>
