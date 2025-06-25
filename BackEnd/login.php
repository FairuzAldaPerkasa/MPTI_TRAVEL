<?php
session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        $koneksi = new mysqli("localhost", "root", "", "paket_travel");
        
        if (!$koneksi->connect_error) {
            $stmt = $koneksi->prepare("SELECT id, name, email, password, role, is_active FROM admins WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();
            
            if ($admin && password_verify($password, $admin['password']) && $admin['is_active']) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];
                
                header("Location: admin.php?login=success");
                exit;
            } else {
                $error = "Invalid credentials or account inactive";
            }
            
            $stmt->close();
            $koneksi->close();
        } else {
            $error = "Database connection failed";
        }
    } else {
        $error = "Please fill in all fields";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php?message=logged_out");
    exit;
}

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin.php");
    exit;
}

// Get messages
$success_message = '';
$error_message = isset($error) ? $error : '';

if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'logged_out':
            $success_message = 'You have been logged out successfully';
            break;
        case 'session_expired':
            $error_message = 'Your session has expired. Please log in again.';
            break;
    }
}
?>
{{-- filepath: resources/views/admin/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | MPTI Travel</title>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../admin/css/admin-clean.css">
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <div class="logo">
                <img src="../assets/images/logompti.png" alt="MPTI Travel Logo">
                <span>MPTI Travel</span>
            </div>
            <div class="admin-badge">
                <i class="fas fa-shield-alt"></i> Admin Portal
            </div>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                           required 
                           autocomplete="email" 
                           placeholder="Enter your email">
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
                           placeholder="Enter your password">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
                </div>
            </div>

            <div class="remember-section">
                <div class="remember-checkbox">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <div class="footer-text">
            <p>&copy; <?= date('Y') ?> MPTI Travel Admin Panel</p>
        </div>
    </div>

    <script src="../admin/js/admin-clean.js"></script>
</body>
</html>