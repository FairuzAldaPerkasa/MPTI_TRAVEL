<?php
session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $error = '';
    
    // Validate input
    if (empty($email) || empty($password)) {
        header("Location: ../FrontEnd/html/profile.html?error=missing_fields&message=" . urlencode("Harap isi email dan password!"));
        exit;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../FrontEnd/html/profile.html?error=invalid_email&message=" . urlencode("Format email tidak valid!"));
        exit;
    } else {
        // Database connection
        $koneksi = new mysqli("localhost", "root", "", "paket_travel");
        
        if ($koneksi->connect_error) {
            header("Location: ../FrontEnd/html/profile.html?error=database_error&message=" . urlencode("Koneksi database gagal!"));
            exit;
        } else {
            try {
                $stmt = $koneksi->prepare("SELECT id, email, password, name FROM admins WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $admin = $result->fetch_assoc();
                
                if ($admin && password_verify($password, $admin['password'])) {
                    // Login successful
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_name'] = $admin['name'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['admin_role'] = 'admin';
                    
                    $stmt->close();
                    $koneksi->close();
                    
                    // Clear any error messages and redirect to admin
                    header("Location: admin.php");
                    exit;
                } else {
                    // Login failed - redirect to profile.html with error
                    $stmt->close();
                    $koneksi->close();
                    header("Location: ../FrontEnd/html/profile.html?error=login_failed&message=" . urlencode("Email atau password salah! Periksa kembali kredensial Anda."));
                    exit;
                }
            } catch (Exception $e) {
                // Database error - show user-friendly message
                $error = "Terjadi kesalahan sistem. Silakan coba lagi dalam beberapa saat.";
                if (isset($stmt)) $stmt->close();
            }
            
            if (isset($koneksi)) $koneksi->close();
        }
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_start();
    session_unset();
    session_destroy();
    
    // Clear all cookies
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Redirect to profile page instead of login page
    header("Location: ../FrontEnd/html/profile.html");
    exit;
}

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin.php");
    exit;
}

// Get messages and error handling
$success_message = '';
$error_message = isset($error) ? $error : '';

// Handle URL parameters for messages
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'logged_out':
            $success_message = 'You have been logged out successfully';
            break;
        case 'session_expired':
            $error_message = 'Your session has expired. Please log in again.';
            break;
        case 'access_denied':
            $error_message = 'Access denied. Please log in to continue.';
            break;
        default:
            // Prevent any other strange messages
            break;
    }
}

// Prevent caching of this page
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Admin Login | MPTI Travel</title>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../admin/css/admin-clean.css">
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="logo-section">
                <div class="logo">
                    <img src="../assets/images/logompti.png" alt="MPTI Travel Logo">
                    <span>MPTI Travel</span>
                </div>
                <div class="admin-badge">
                    <i class="fas fa-shield-alt"></i> Admin Portal
                </div>
                <p class="admin-info">
                    <i class="fas fa-info-circle"></i> 
                    Halaman login administrator untuk mengelola website MPTI Travel.
                </p>
            </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="alert-content">
                    <strong>Login Gagal</strong>
                    <p><?= htmlspecialchars($error_message) ?></p>
                    <small>Pastikan email dan password yang Anda masukkan benar.</small>
                </div>
                <button type="button" class="alert-close" onclick="this.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <div class="alert-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="alert-content">
                    <strong>Berhasil</strong>
                    <p><?= htmlspecialchars($success_message) ?></p>
                </div>
                <button type="button" class="alert-close" onclick="this.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="login-form">
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                           required 
                           autocomplete="email" 
                           placeholder="Masukkan email Anda">
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           autocomplete="current-password" 
                           placeholder="Masukkan password Anda">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
                </div>
            </div>

            <div class="remember-section">
                <div class="remember-checkbox">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Ingat saya</label>
                </div>
            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </button>
        </form>

        <div class="footer-text">
            <p>&copy; <?= date('Y') ?> MPTI Travel Admin Panel</p>
        </div>

        <div class="back-to-site">
            <a href="../FrontEnd/html/profile.html">
                <i class="fas fa-arrow-left"></i> Kembali ke Website Utama
            </a>
        </div>
        </div>
    </div>
    </div>

    <script src="../admin/js/admin-clean.js"></script>
    <script>
        // Clean URL fragments when there's an error
        if (window.location.hash) {
            // Remove the hash without reloading the page
            history.replaceState(null, null, window.location.pathname + window.location.search);
        }
        
        // Prevent form resubmission on refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>