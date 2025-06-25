<?php
/**
 * API Endpoint untuk Mengambil Detail Paket Wisata
 * 
 * File ini bertanggung jawab untuk mengambil detail lengkap dari satu paket wisata 
 * berdasarkan ID yang diberikan. Mendukung multi-bahasa dan menyediakan data 
 * fallback jika informasi tidak lengkap.
 *
 * @version 2.0
 * @author ARK
 * @date 2025-06-22
 */

// =============================================================================
// INISIALISASI & KONFIGURASI
// =============================================================================

// Nonaktifkan tampilan error untuk menjaga integritas output JSON
ini_set('display_errors', 0);
error_reporting(0);

// Mulai output buffering untuk menangkap output yang tidak diinginkan
ob_start();

// Sertakan file konfigurasi bahasa
require_once 'language_config.php';

// Atur header HTTP untuk respons JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // Izinkan akses dari domain manapun
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, X-Language');

// =============================================================================
// PROSES UTAMA (TRY-CATCH BLOCK)
// =============================================================================

try {
    // Inisialisasi sistem bahasa
    $language_config = new LanguageConfig();
      // Validasi ID paket dari parameter GET
    $package_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$package_id || $package_id <= 0) {
        throw new Exception($language_config->translate('errors.invalid_package_id'));
    }

    // =========================================================================
    // KONEKSI & PENGAMBILAN DATA DATABASE
    // =========================================================================

    // TODO: Pindahkan kredensial database ke file konfigurasi terpisah (e.g., db_config.php) untuk keamanan
    $db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "paket_travel";

    $koneksi = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($koneksi->connect_error) {
        throw new Exception($language_config->translate('errors.database_connection_failed') . ': ' . $koneksi->connect_error);
    }
    $koneksi->set_charset("utf8mb4");
      // Query utama untuk mengambil data paket
    $sql = "SELECT id, nama, deskripsi, fotos, itinerary, highlights, inclusions, exclusions, CAST(price AS DECIMAL(12,2)) as price, duration FROM paket WHERE id = ? LIMIT 1";
    $stmt = $koneksi->prepare($sql);
    
    if (!$stmt) {
        throw new Exception($language_config->translate('errors.query_preparation_failed') . ': ' . $koneksi->error);
    }
    
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        throw new Exception($language_config->translate('errors.package_not_found'));
    }
    
    $package = $result->fetch_assoc();

    // =========================================================================
    // PEMROSESAN DATA (FOTO, ITINERARY, DLL.)
    // =========================================================================    // --- Pemrosesan Foto (Utama & Galeri) ---
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $base_url_uploads = "{$protocol}://{$host}/MPTI_TRAVEL/BackEnd/uploads/";

    $processedFotos = [];
    $mainFotos = json_decode($package['fotos'], true) ?: [];    foreach ($mainFotos as $foto) {
        $foto = trim($foto); // Remove any whitespace/newlines
        if (!empty($foto) && file_exists(__DIR__ . '/uploads/' . $foto)) {
            $processedFotos[] = ['url' => $base_url_uploads . $foto, 'caption' => $language_config->translate('package.photo_caption_main'), 'type' => 'main'];
        }
    }

    // Ambil foto dari galeri tambahan
    $galleryStmt = $koneksi->prepare("SELECT photo_filename, caption FROM package_gallery WHERE package_id = ? ORDER BY photo_order ASC");
    if ($galleryStmt) {
        $galleryStmt->bind_param("i", $package_id);
        $galleryStmt->execute();
        $galleryResult = $galleryStmt->get_result();
        $gallery_base_url = $base_url_uploads . 'gallery/';        while ($photo = $galleryResult->fetch_assoc()) {
            if (file_exists(__DIR__ . '/uploads/gallery/' . $photo['photo_filename'])) {
                $processedFotos[] = [
                    'url' => $gallery_base_url . $photo['photo_filename'],
                    'caption' => $photo['caption'] ?: $language_config->translate('package.photo_caption_gallery'),
                    'type' => 'gallery'
                ];
            }
        }
        $galleryStmt->close();
    }    // Jika tidak ada foto sama sekali, gunakan fallback
    if (empty($processedFotos)) {
        $fallback_base_url = "{$protocol}://{$host}/MPTI_TRAVEL/assets/images/";
        $processedFotos = [
            ['url' => $fallback_base_url . 'borobudur.jpg', 'caption' => $language_config->translate('package.photo_caption_borobudur'), 'type' => 'default'],
            ['url' => $fallback_base_url . 'prambanan1.jpg', 'caption' => $language_config->translate('package.photo_caption_prambanan'), 'type' => 'default'],
        ];
    }// --- Pemrosesan Data Teks & JSON dengan Fallback ---
    $deskripsi = !empty(trim($package['deskripsi'])) ? $package['deskripsi'] : $language_config->translate('package.default_description');
    $deskripsi_singkat = substr($deskripsi, 0, 150) . (strlen($deskripsi) > 150 ? '...' : '');
      // Data inclusions dengan fallback
    $inclusions = json_decode($package['inclusions'], true);
    if (!$inclusions) {
        $inclusions = $language_config->translate('package.included_default');
    }
    
    // Data exclusions dengan fallback
    $exclusions = json_decode($package['exclusions'], true);
    if (!$exclusions) {
        $exclusions = $language_config->translate('package.excluded_default');
    }
    
    // Data highlights dengan fallback
    $highlights = json_decode($package['highlights'], true);
    if (!$highlights) {
        $highlights = $language_config->translate('package.highlights_default');
    }
    
    $itinerary = json_decode($package['itinerary'], true) ?: [];// =========================================================================
    // MEMBANGUN & MENGIRIM RESPONS JSON
    // =========================================================================
      // Format fotos untuk kompatibilitas dengan frontend
    $fotos_array = [];
    foreach ($processedFotos as $foto) {
        $fotos_array[] = $foto['url'];
    }
    
    $response_data = [
        'id'                => (int)$package['id'],
        'nama'              => trim($package['nama'] ?? ''),
        'deskripsi_singkat' => $deskripsi_singkat,
        'deskripsi'         => $deskripsi,
        'duration'          => trim($package['duration'] ?? 'N/A'), // Ubah dari 'durasi' ke 'duration'
        'price'             => $package['price'] ? (float)$package['price'] : 0,
        'formattedPrice'    => $language_config->formatCurrency($package['price']), // Ubah dari 'harga_terformat'
        'fotos'             => $fotos_array, // Array simple untuk kompatibilitas
        'gallery'           => $processedFotos, // Tetap kirim data lengkap gallery
        'itinerary'         => $itinerary,
        'inclusions'        => $inclusions, // Ubah dari 'termasuk'
        'exclusions'        => $exclusions, // Ubah dari 'tidak_termasuk'
        'highlights'        => $highlights,
    ];    $final_response = [
        'success'   => true,
        'language'  => $language_config->getCurrentLanguage(),
        'data'      => $response_data
    ];
    
    $stmt->close();
    $koneksi->close();
    
    // Hapus semua output sebelumnya dari buffer
    ob_end_clean();
    
    // Kirim respons JSON
    echo json_encode($final_response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Tangani semua error yang terjadi
    ob_end_clean();
    
    // Atur kode status HTTP sesuai jenis error
    if (!headers_sent()) {
        http_response_code($e->getCode() ?: 500);
    }
    
    // Inisialisasi ulang config bahasa jika perlu
    if (!isset($language_config)) {
        $language_config = new LanguageConfig();
    }    // Kirim respons error dalam format JSON
    echo json_encode([
        'success'  => false,
        'language' => $language_config->getCurrentLanguage(),
        'message'  => $e->getMessage(),
        'status'   => $language_config->translate('status.failed')
    ], JSON_UNESCAPED_UNICODE);
}

exit;
?>