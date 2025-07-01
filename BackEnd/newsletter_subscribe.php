<?php
/**
 * Newsletter Subscription API
 * 
 * @version 1.0
 * @author Vacationland
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    $email = filter_var(trim($input['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $name = trim($input['name'] ?? '');
    
    if (!$email) {
        echo json_encode([
            'success' => false, 
            'message' => 'Email tidak valid'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Database connection
    $koneksi = new mysqli("localhost", "root", "", "paket_travel");
    
    if ($koneksi->connect_error) {
        throw new Exception('Database connection failed');
    }
    
    $koneksi->set_charset("utf8mb4");
    
    // Check if email already exists
    $check_stmt = $koneksi->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $existing = $result->fetch_assoc();
        if ($existing['status'] === 'active') {
            echo json_encode([
                'success' => false,
                'message' => 'Email sudah terdaftar dalam newsletter kami'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            // Reactivate unsubscribed email
            $update_stmt = $koneksi->prepare("UPDATE newsletter_subscribers SET status = 'active', name = ?, updated_at = CURRENT_TIMESTAMP WHERE email = ?");
            $update_stmt->bind_param("ss", $name, $email);
            
            if ($update_stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Berhasil berlangganan newsletter! Kami akan mengirimkan update terbaru ke email Anda.'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception('Gagal mengaktifkan kembali subscription');
            }
        }
    } else {
        // Add new subscriber
        $unsubscribe_token = bin2hex(random_bytes(16));
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        
        $insert_stmt = $koneksi->prepare("INSERT INTO newsletter_subscribers (email, name, unsubscribe_token, user_agent, ip_address) VALUES (?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("sssss", $email, $name, $unsubscribe_token, $user_agent, $ip_address);
        
        if ($insert_stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Terima kasih! Anda telah berhasil berlangganan newsletter Vacationland. Kami akan mengirimkan promo dan update terbaru ke email Anda.'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            throw new Exception('Gagal menyimpan subscription');
        }
        $insert_stmt->close();
    }
    
    $check_stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem. Silakan coba lagi nanti.'
    ], JSON_UNESCAPED_UNICODE);
    
    // Log error for debugging
    error_log("Newsletter subscription error: " . $e->getMessage());
}

$koneksi->close();
?>
