<?php
/**
 * Clean Session Script
 * Use this to reset any corrupt sessions
 */

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

echo "<h2>🧹 Session Cleaned</h2>";
echo "<p>All sessions and cookies have been cleared.</p>";
echo "<p><a href='ViewLoginAdmin.php'>Go to Login Page</a></p>";
?>
