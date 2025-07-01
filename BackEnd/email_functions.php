<?php
/**
 * Email Functions for Newsletter
 * 
 * @version 1.0
 * @author Vacationland
 */

function sendNewsletterEmail($subject, $content, $recipientEmails) {
    // Email configuration
    $from_email = "noreply@vacationland.com";
    $from_name = "Vacationland";
    
    // Email headers
    $headers = [
        "MIME-Version: 1.0",
        "Content-type: text/html; charset=UTF-8",
        "From: {$from_name} <{$from_email}>",
        "Reply-To: {$from_email}",
        "X-Mailer: PHP/" . phpversion()
    ];
    
    $success_count = 0;
    $failed_count = 0;
    $error_log = [];
    
    // Log file untuk debugging
    $log_file = __DIR__ . '/newsletter_logs.txt';
    $timestamp = date('Y-m-d H:i:s');
    
    // Log awal pengiriman
    file_put_contents($log_file, "[{$timestamp}] Starting newsletter send to " . count($recipientEmails) . " recipients\n", FILE_APPEND);
    
    foreach ($recipientEmails as $email) {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $failed_count++;
            $error_log[] = "Invalid email: {$email}";
            file_put_contents($log_file, "[{$timestamp}] FAILED - Invalid email: {$email}\n", FILE_APPEND);
            continue;
        }
        
        // Personalized content
        $personalized_content = str_replace(
            ['{{EMAIL}}', '{{UNSUBSCRIBE_LINK}}'],
            [$email, "http://localhost/MPTI_TRAVEL/BackEnd/unsubscribe.php?email=" . urlencode($email)],
            $content
        );
        
        // Try to send email
        $mail_result = mail($email, $subject, $personalized_content, implode("\r\n", $headers));
        
        if ($mail_result) {
            $success_count++;
            file_put_contents($log_file, "[{$timestamp}] SUCCESS - Email sent to: {$email}\n", FILE_APPEND);
        } else {
            $failed_count++;
            $last_error = error_get_last();
            $error_msg = $last_error ? $last_error['message'] : 'Unknown error';
            $error_log[] = "Failed to send to {$email}: {$error_msg}";
            file_put_contents($log_file, "[{$timestamp}] FAILED - Email to {$email}: {$error_msg}\n", FILE_APPEND);
        }
        
        // Small delay to prevent server overload
        usleep(100000); // 0.1 second
    }
    
    // Log summary
    file_put_contents($log_file, "[{$timestamp}] SUMMARY - Success: {$success_count}, Failed: {$failed_count}\n\n", FILE_APPEND);
    
    return [
        'success' => $success_count > 0,
        'total_sent' => $success_count,
        'total_failed' => $failed_count,
        'total_recipients' => count($recipientEmails),
        'errors' => $error_log,
        'log_file' => $log_file
    ];
}

function testEmailSend($test_email) {
    $subject = "Test Email dari Vacationland";
    $content = "
    <html>
    <body>
        <h2>Test Email</h2>
        <p>Ini adalah test email dari sistem newsletter Vacationland.</p>
        <p>Jika Anda menerima email ini, berarti konfigurasi email server sudah benar.</p>
        <p>Timestamp: " . date('Y-m-d H:i:s') . "</p>
    </body>
    </html>
    ";
    
    $from_email = "noreply@vacationland.com";
    $from_name = "Vacationland";
    
    $headers = [
        "MIME-Version: 1.0",
        "Content-type: text/html; charset=UTF-8",
        "From: {$from_name} <{$from_email}>",
        "Reply-To: {$from_email}",
        "X-Mailer: PHP/" . phpversion()
    ];
    
    $result = mail($test_email, $subject, $content, implode("\r\n", $headers));
    
    // Log test result
    $log_file = __DIR__ . '/email_test_logs.txt';
    $timestamp = date('Y-m-d H:i:s');
    $status = $result ? 'SUCCESS' : 'FAILED';
    
    file_put_contents($log_file, "[{$timestamp}] {$status} - Test email to: {$test_email}\n", FILE_APPEND);
    
    return $result;
}
?>