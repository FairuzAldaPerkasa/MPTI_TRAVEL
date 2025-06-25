<?php
/**
 * Admin Panel - MPTI TRAVEL
 *
 * This is the main administrative interface for managing travel packages.
 * It combines all functionalities:
 * - Dashboard with statistics.
 * - Form to add a new package (and processes the submission).
 * - List of existing packages with edit/delete/gallery management options.
 * - Deletion logic for packages.
 *
 * @version 2.0
 * @author MPTI_TRAVEL
 * @filepath c:\xampp\htdocs\MPTI_TRAVEL\BackEnd\admin.php
 */
session_start();

// --- SECURITY & SESSION CHECK ---
// Redirect to login page if the admin is not logged in.
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php?error=not_logged_in");
    exit;
}

// --- DATABASE CONNECTION ---
$koneksi = new mysqli("localhost", "root", "", "paket_travel");
if ($koneksi->connect_error) {
    // Use a more robust error handling than just die()
    // For now, we'll keep it simple for the admin panel.
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// --- FORM SUBMISSION HANDLING (from tambah.php) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] == 'update_settings') {
        // --- SETTINGS UPDATE HANDLING ---
        $settings_updated = 0;
        $settings_errors = 0;
        
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'setting_') === 0) {
                $setting_key = substr($key, 8); // Remove 'setting_' prefix
                $setting_value = trim($value);
                
                $stmt = $koneksi->prepare("UPDATE website_settings SET setting_value = ? WHERE setting_key = ?");
                if ($stmt) {
                    $stmt->bind_param("ss", $setting_value, $setting_key);
                    if ($stmt->execute()) {
                        $settings_updated++;
                    } else {
                        $settings_errors++;
                    }
                    $stmt->close();
                }
            }
        }
          if ($settings_errors == 0) {
            header("Location: admin.php?success=settings_updated#settings");
        } else {
            header("Location: admin.php?error=settings_error#settings");
        }
        exit;
    }
    
    if ($_POST['action'] == 'add_booking') {
        // --- ADD BOOKING HANDLING ---
        $customer_name = trim($_POST['customer_name'] ?? '');
        $customer_phone = trim($_POST['customer_phone'] ?? '');
        $customer_email = trim($_POST['customer_email'] ?? '');
        $package_id = $_POST['package_id'] ?? null;
        $package_name = trim($_POST['package_name'] ?? '');
        $booking_date = $_POST['booking_date'] ?? date('Y-m-d');
        $travel_date = $_POST['travel_date'] ?? '';
        $participants = (int)($_POST['participants'] ?? 1);
        $total_price = (float)($_POST['total_price'] ?? 0);
        $payment_method = trim($_POST['payment_method'] ?? '');
        $payment_status = $_POST['payment_status'] ?? 'pending';
        $booking_status = $_POST['booking_status'] ?? 'confirmed';
        $notes = trim($_POST['notes'] ?? '');
        $whatsapp_number = trim($_POST['whatsapp_number'] ?? '');
        
        if (empty($customer_name) || empty($customer_phone) || empty($package_name)) {
            header("Location: admin.php?error=booking_empty_fields#booking-history");
            exit;
        }
        
        $stmt = $koneksi->prepare("INSERT INTO booking_history (customer_name, customer_phone, customer_email, package_id, package_name, booking_date, travel_date, participants, total_price, payment_method, payment_status, booking_status, notes, whatsapp_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssississsssss", $customer_name, $customer_phone, $customer_email, $package_id, $package_name, $booking_date, $travel_date, $participants, $total_price, $payment_method, $payment_status, $booking_status, $notes, $whatsapp_number);
            if ($stmt->execute()) {
                header("Location: admin.php?success=booking_added#booking-history");
            } else {
                header("Location: admin.php?error=booking_failed#booking-history");
            }
            $stmt->close();
        }
        exit;
    }
    
    if ($_POST['action'] == 'update_payment_method') {
        // --- UPDATE PAYMENT METHOD HANDLING ---
        $method_id = (int)$_POST['method_id'];
        $method_name = trim($_POST['method_name'] ?? '');
        $method_type = $_POST['method_type'] ?? 'other';
        $icon_class = trim($_POST['icon_class'] ?? 'fas fa-credit-card');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $display_order = (int)($_POST['display_order'] ?? 0);
        
        if (empty($method_name)) {
            header("Location: admin.php?error=payment_empty_name#payment-methods");
            exit;
        }
        
        if ($method_id > 0) {
            // Update existing
            $stmt = $koneksi->prepare("UPDATE payment_methods SET method_name = ?, method_type = ?, icon_class = ?, is_active = ?, display_order = ? WHERE id = ?");
            $stmt->bind_param("sssiii", $method_name, $method_type, $icon_class, $is_active, $display_order, $method_id);
        } else {
            // Add new
            $stmt = $koneksi->prepare("INSERT INTO payment_methods (method_name, method_type, icon_class, is_active, display_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssii", $method_name, $method_type, $icon_class, $is_active, $display_order);
        }
        
        if ($stmt && $stmt->execute()) {
            header("Location: admin.php?success=payment_updated#payment-methods");
        } else {
            header("Location: admin.php?error=payment_failed#payment-methods");
        }
        if ($stmt) $stmt->close();
        exit;
    }
      if ($_POST['action'] == 'delete_payment_method') {
        // --- DELETE PAYMENT METHOD HANDLING ---
        $method_id = (int)$_POST['method_id'];
        
        $stmt = $koneksi->prepare("DELETE FROM payment_methods WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $method_id);
            if ($stmt->execute()) {
                header("Location: admin.php?success=payment_deleted#payment-methods");
            } else {
                header("Location: admin.php?error=payment_delete_failed#payment-methods");
            }
            $stmt->close();
        }
        exit;
    }
    
    if ($_POST['action'] == 'send_newsletter') {
        // --- SEND NEWSLETTER HANDLING ---
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $campaign_type = $_POST['campaign_type'] ?? 'promo';
        
        if (empty($subject) || empty($message)) {
            header("Location: admin.php?error=newsletter_empty_fields#newsletter");
            exit;
        }
        
        // Get active subscribers
        $subscribers_result = $koneksi->query("SELECT email, name FROM newsletter_subscribers WHERE status = 'active'");
        $sent_count = 0;
        
        // In a real application, you would integrate with an email service like SendGrid, MailChimp, etc.
        // For this demo, we'll just simulate sending
        while ($subscriber = $subscribers_result->fetch_assoc()) {
            // Simulate email sending
            $sent_count++;
        }
        
        // Save campaign to database
        $campaign_stmt = $koneksi->prepare("INSERT INTO email_campaigns (subject, message, campaign_type, sent_to_count) VALUES (?, ?, ?, ?)");
        $campaign_stmt->bind_param("sssi", $subject, $message, $campaign_type, $sent_count);
        
        if ($campaign_stmt->execute()) {
            // Update last_email_sent for all active subscribers
            $koneksi->query("UPDATE newsletter_subscribers SET last_email_sent = CURRENT_TIMESTAMP WHERE status = 'active'");
            header("Location: admin.php?success=newsletter_sent&count=$sent_count#newsletter");
        } else {
            header("Location: admin.php?error=newsletter_failed#newsletter");
        }
        $campaign_stmt->close();
        exit;
    }
    
    if ($_POST['action'] == 'delete_subscriber') {
        // --- DELETE SUBSCRIBER HANDLING ---
        $subscriber_id = (int)$_POST['subscriber_id'];
        
        $stmt = $koneksi->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $subscriber_id);
            if ($stmt->execute()) {
                header("Location: admin.php?success=subscriber_deleted#newsletter");
            } else {
                header("Location: admin.php?error=subscriber_delete_failed#newsletter");
            }
            $stmt->close();
        }
        exit;
    }
    
    if ($_POST['action'] == 'add_package') {
    
    // --- DATA SANITIZATION & EXTRACTION ---
    $nama = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $duration = trim($_POST['duration'] ?? '2D1N');

    // --- PRICE PROCESSING ---
    $price_input = trim($_POST['price'] ?? '0');
    $price_cleaned = preg_replace('/[^0-9]/', '', $price_input);
    $price = (int)$price_cleaned;

    // --- PRICE VALIDATION ---
    if ($price <= 0) {
        header("Location: admin.php?error=invalid_price#add-package");
        exit;
    }
    if ($price < 100000) {
        header("Location: admin.php?error=price_too_low#add-package");
        exit;
    }
    if ($price > 50000000) {
        header("Location: admin.php?error=price_too_high#add-package");
        exit;
    }

    // --- JSON DATA PROCESSING ---
    $highlights = isset($_POST['highlights']) ? json_encode(array_values(array_filter($_POST['highlights']))) : '[]';
    
    $inclusions_data = [];
    if (isset($_POST['inclusions']) && isset($_POST['inclusion_icons'])) {
        foreach ($_POST['inclusions'] as $index => $text) {
            if (!empty($text)) {
                $inclusions_data[] = ['icon' => $_POST['inclusion_icons'][$index], 'text' => $text];
            }
        }
    }
    $inclusions = json_encode($inclusions_data);

    $exclusions_data = [];
    if (isset($_POST['exclusions']) && isset($_POST['exclusion_icons'])) {
        foreach ($_POST['exclusions'] as $index => $text) {
            if (!empty($text)) {
                $exclusions_data[] = ['icon' => $_POST['exclusion_icons'][$index], 'text' => $text];
            }
        }
    }
    $exclusions = json_encode($exclusions_data);

    // --- ITINERARY PROCESSING ---
    $itinerary = [];
    if (isset($_POST['itinerary_titles'])) {
        foreach ($_POST['itinerary_titles'] as $dayIndex => $title) {
            $activities = [];
            if (isset($_POST['itinerary_times'][$dayIndex]) && isset($_POST['itinerary_activities'][$dayIndex])) {
                foreach ($_POST['itinerary_times'][$dayIndex] as $actIndex => $time) {
                    $activity_desc = $_POST['itinerary_activities'][$dayIndex][$actIndex] ?? '';
                    if (!empty($time) && !empty($activity_desc)) {
                        $activities[] = ['time' => $time, 'activity' => $activity_desc];
                    }
                }
            }
            $itinerary[] = ['day' => $dayIndex + 1, 'title' => $title, 'activities' => $activities];
        }
    }
    $itinerary_json = json_encode($itinerary);

    // --- INPUT VALIDATION ---
    if (empty($nama) || empty($deskripsi)) {
        header("Location: admin.php?error=empty_fields#add-package");
        exit;
    }
    if (strlen($nama) > 255 || strlen($deskripsi) > 1000) {
        header("Location: admin.php?error=input_too_long#add-package");
        exit;
    }

    // --- FILE UPLOAD VALIDATION ---
    if (!isset($_FILES['fotos']) || empty($_FILES['fotos']['name'][0])) {
        header("Location: admin.php?error=no_files#add-package");
        exit;
    }

    $photos = $_FILES['fotos'];
    $photoCount = count($photos['name']);

    if ($photoCount < 3 || $photoCount > 6) {
        header("Location: admin.php?error=invalid_photo_count#add-package");
        exit;
    }

    // --- FILE UPLOAD PROCESSING ---
    $uploadedFiles = [];
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    for ($i = 0; $i < $photoCount; $i++) {
        $fileName = $photos['name'][$i];
        $fileTmpName = $photos['tmp_name'][$i];
        $fileSize = $photos['size'][$i];
        $fileError = $photos['error'][$i];
        $fileType = $photos['type'][$i];

        if ($fileError !== UPLOAD_ERR_OK) {
            header("Location: admin.php?error=upload_failed#add-package");
            exit;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        if (!in_array(strtolower($fileType), $allowedTypes)) {
            header("Location: admin.php?error=invalid_file_type#add-package");
            exit;
        }
        if ($fileSize > 5 * 1024 * 1024) { // 5MB
            header("Location: admin.php?error=file_too_large#add-package");
            exit;
        }

        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = time() . '_' . bin2hex(random_bytes(8)) . '_' . ($i + 1) . '.' . $fileExtension;
        $uploadPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpName, $uploadPath)) {
            $uploadedFiles[] = $newFileName;
        } else {
            foreach ($uploadedFiles as $uploadedFile) {
                unlink($uploadDir . $uploadedFile);
            }
            header("Location: admin.php?error=upload_failed#add-package");
            exit;
        }
    }

    // --- DATABASE INSERTION ---
    $fotosJson = json_encode($uploadedFiles);

    $stmt = $koneksi->prepare("INSERT INTO paket (nama, deskripsi, price, duration, fotos, highlights, itinerary, inclusions, exclusions, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssissssss", $nama, $deskripsi, $price, $duration, $fotosJson, $highlights, $itinerary_json, $inclusions, $exclusions);    if ($stmt->execute()) {
        header("Location: admin.php?success=1#packages-list");
        exit;
    } else {
        header("Location: admin.php?error=database_error&details=" . urlencode($stmt->error) . "#add-package");
        exit;
    }
    } // END add_package action
    
    if ($_POST['action'] == 'update_package') {
        // --- UPDATE PACKAGE HANDLING ---
        $package_id = (int)($_POST['package_id'] ?? 0);
        
        if ($package_id <= 0) {
            header("Location: admin.php?error=invalid_package_id#packages-list");
            exit;
        }
        
        // Check if package exists
        $check_stmt = $koneksi->prepare("SELECT id FROM paket WHERE id = ?");
        $check_stmt->bind_param("i", $package_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows == 0) {
            header("Location: admin.php?error=package_not_found#packages-list");
            exit;
        }
        
        // --- DATA SANITIZATION & EXTRACTION ---
        $nama = trim($_POST['nama'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $duration = trim($_POST['duration'] ?? '2D1N');

        // --- PRICE PROCESSING ---
        $price_input = trim($_POST['price'] ?? '0');
        $price_cleaned = preg_replace('/[^0-9]/', '', $price_input);
        $price = (int)$price_cleaned;

        // --- PRICE VALIDATION ---
        if ($price <= 0) {
            header("Location: admin.php?error=invalid_price&edit_id=$package_id#add-package");
            exit;
        }
        if ($price < 100000) {
            header("Location: admin.php?error=price_too_low&edit_id=$package_id#add-package");
            exit;
        }
        if ($price > 50000000) {
            header("Location: admin.php?error=price_too_high&edit_id=$package_id#add-package");
            exit;
        }

        // --- JSON DATA PROCESSING ---
        $highlights = isset($_POST['highlights']) ? json_encode(array_values(array_filter($_POST['highlights']))) : '[]';
        
        $inclusions_data = [];
        if (isset($_POST['inclusions']) && isset($_POST['inclusion_icons'])) {
            foreach ($_POST['inclusions'] as $index => $text) {
                if (!empty($text)) {
                    $inclusions_data[] = ['icon' => $_POST['inclusion_icons'][$index], 'text' => $text];
                }
            }
        }
        $inclusions = json_encode($inclusions_data);

        $exclusions_data = [];
        if (isset($_POST['exclusions']) && isset($_POST['exclusion_icons'])) {
            foreach ($_POST['exclusions'] as $index => $text) {
                if (!empty($text)) {
                    $exclusions_data[] = ['icon' => $_POST['exclusion_icons'][$index], 'text' => $text];
                }
            }
        }
        $exclusions = json_encode($exclusions_data);

        // --- ITINERARY PROCESSING ---
        $itinerary = [];
        if (isset($_POST['itinerary_titles'])) {
            foreach ($_POST['itinerary_titles'] as $dayIndex => $title) {
                $activities = [];
                if (isset($_POST['itinerary_times'][$dayIndex]) && isset($_POST['itinerary_activities'][$dayIndex])) {
                    foreach ($_POST['itinerary_times'][$dayIndex] as $actIndex => $time) {
                        $activity_desc = $_POST['itinerary_activities'][$dayIndex][$actIndex] ?? '';
                        if (!empty($time) && !empty($activity_desc)) {
                            $activities[] = ['time' => $time, 'activity' => $activity_desc];
                        }
                    }
                }
                $itinerary[] = ['day' => $dayIndex + 1, 'title' => $title, 'activities' => $activities];
            }
        }
        $itinerary_json = json_encode($itinerary);

        // --- INPUT VALIDATION ---
        if (empty($nama) || empty($deskripsi)) {
            header("Location: admin.php?error=empty_fields&edit_id=$package_id#add-package");
            exit;
        }
        if (strlen($nama) > 255 || strlen($deskripsi) > 1000) {
            header("Location: admin.php?error=input_too_long&edit_id=$package_id#add-package");
            exit;
        }

        // --- FILE UPLOAD PROCESSING (Optional for updates) ---
        $fotosJson = null;
        if (isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
            $photos = $_FILES['fotos'];
            $photoCount = count($photos['name']);

            if ($photoCount < 3 || $photoCount > 6) {
                header("Location: admin.php?error=invalid_photo_count&edit_id=$package_id#add-package");
                exit;
            }

            $uploadedFiles = [];
            $uploadDir = 'uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            for ($i = 0; $i < $photoCount; $i++) {
                $fileName = $photos['name'][$i];
                $fileTmpName = $photos['tmp_name'][$i];
                $fileSize = $photos['size'][$i];
                $fileError = $photos['error'][$i];
                $fileType = $photos['type'][$i];

                if ($fileError !== UPLOAD_ERR_OK) {
                    header("Location: admin.php?error=upload_failed&edit_id=$package_id#add-package");
                    exit;
                }

                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
                if (!in_array(strtolower($fileType), $allowedTypes)) {
                    header("Location: admin.php?error=invalid_file_type&edit_id=$package_id#add-package");
                    exit;
                }
                if ($fileSize > 5 * 1024 * 1024) { // 5MB
                    header("Location: admin.php?error=file_too_large&edit_id=$package_id#add-package");
                    exit;
                }

                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $newFileName = time() . '_' . bin2hex(random_bytes(8)) . '_' . ($i + 1) . '.' . $fileExtension;
                $uploadPath = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmpName, $uploadPath)) {
                    $uploadedFiles[] = $newFileName;
                } else {
                    foreach ($uploadedFiles as $uploadedFile) {
                        unlink($uploadDir . $uploadedFile);
                    }
                    header("Location: admin.php?error=upload_failed&edit_id=$package_id#add-package");
                    exit;
                }
            }

            // Delete old photos if new ones are uploaded
            $old_stmt = $koneksi->prepare("SELECT fotos FROM paket WHERE id = ?");
            $old_stmt->bind_param("i", $package_id);
            $old_stmt->execute();
            $old_result = $old_stmt->get_result();
            if ($old_row = $old_result->fetch_assoc()) {
                $old_photos = json_decode($old_row['fotos'], true);
                if (is_array($old_photos)) {
                    foreach ($old_photos as $old_photo) {
                        $old_file_path = $uploadDir . $old_photo;
                        if (file_exists($old_file_path)) {
                            unlink($old_file_path);
                        }
                    }
                }
            }
            
            $fotosJson = json_encode($uploadedFiles);
        }

        // --- DATABASE UPDATE ---
        if ($fotosJson) {
            // Update with new photos
            $stmt = $koneksi->prepare("UPDATE paket SET nama = ?, deskripsi = ?, price = ?, duration = ?, fotos = ?, highlights = ?, itinerary = ?, inclusions = ?, exclusions = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("ssissssssi", $nama, $deskripsi, $price, $duration, $fotosJson, $highlights, $itinerary_json, $inclusions, $exclusions, $package_id);
        } else {
            // Update without changing photos
            $stmt = $koneksi->prepare("UPDATE paket SET nama = ?, deskripsi = ?, price = ?, duration = ?, highlights = ?, itinerary = ?, inclusions = ?, exclusions = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("sisssssssi", $nama, $deskripsi, $price, $duration, $highlights, $itinerary_json, $inclusions, $exclusions, $package_id);
        }

        if ($stmt->execute()) {
            header("Location: admin.php?success=package_updated#packages-list");
            exit;
        } else {
            header("Location: admin.php?error=database_error&details=" . urlencode($stmt->error) . "&edit_id=$package_id#add-package");
            exit;
        }
    } // END update_package action
} // END POST handling


// --- MESSAGE HANDLING ---
$message = '';
if (isset($_GET['success'])) {    $successMessages = [
        'package_added' => 'Paket berhasil ditambahkan!',
        'package_updated' => 'Paket berhasil diperbarui!',
        'settings_updated' => 'Pengaturan berhasil diperbarui!',
        'booking_added' => 'Booking berhasil ditambahkan!',
        'payment_updated' => 'Metode pembayaran berhasil diperbarui!',
        'payment_deleted' => 'Metode pembayaran berhasil dihapus!',
        'newsletter_sent' => 'Newsletter berhasil dikirim ke ' . ($_GET['count'] ?? '0') . ' subscriber!',
        'subscriber_deleted' => 'Subscriber berhasil dihapus!'
    ];
    $successKey = $_GET['success'];
    $successMsg = $successMessages[$successKey] ?? 'Operasi berhasil!';
    $message = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' . htmlspecialchars($successMsg) . '</div>';
} elseif (isset($_GET['error'])) {    $errorMessages = [
        'invalid_price' => 'Harga tidak valid!',
        'price_too_low' => 'Harga minimal Rp 100.000!',
        'price_too_high' => 'Harga maksimal Rp 50.000.000!',
        'empty_fields' => 'Semua field harus diisi!',
        'no_files' => 'Minimal harus upload 1 foto!',
        'invalid_photo_count' => 'Upload 3-6 foto saja!',
        'invalid_file_type' => 'Hanya file JPG, JPEG, PNG yang diperbolehkan!',
        'settings_error' => 'Gagal memperbarui pengaturan!',
        'file_too_large' => 'Ukuran file maksimal 5MB!',
        'upload_failed' => 'Gagal upload file!',
        'database_error' => 'Terjadi kesalahan database!',
        'input_too_long' => 'Input terlalu panjang!',
        'invalid_package_id' => 'ID paket tidak valid!',
        'package_not_found' => 'Paket tidak ditemukan!',
        'booking_empty_fields' => 'Nama customer, telepon, dan nama paket harus diisi!',
        'booking_failed' => 'Gagal menambahkan booking!',
        'payment_empty_name' => 'Nama metode pembayaran harus diisi!',
        'payment_failed' => 'Gagal memperbarui metode pembayaran!',
        'payment_delete_failed' => 'Gagal menghapus metode pembayaran!',
        'newsletter_empty_fields' => 'Subject dan pesan newsletter harus diisi!',
        'newsletter_failed' => 'Gagal mengirim newsletter!',
        'subscriber_delete_failed' => 'Gagal menghapus subscriber!'
    ];
    $errorKey = $_GET['error'];
    $errorMsg = $errorMessages[$errorKey] ?? 'Terjadi kesalahan tidak dikenal!';
    $message = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($errorMsg) . '</div>';
} elseif (isset($_GET['deleted'])) {
    $message = '<div class="alert alert-success"><i class="fas fa-trash"></i> Paket berhasil dihapus!</div>';
}

// --- DELETE PACKAGE LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus'])) {
    $id = intval($_POST['hapus']);
    
    // Get photos before deleting
    $stmt = $koneksi->prepare("SELECT fotos FROM paket WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Delete main photos
        $fotosArray = json_decode($row['fotos'], true);
        if ($fotosArray && is_array($fotosArray)) {
            foreach ($fotosArray as $foto) {
                $fotoPath = "uploads/" . $foto;
                if (file_exists($fotoPath)) {
                    unlink($fotoPath);
                }
            }
        }
        
        // Delete gallery photos
        $galleryStmt = $koneksi->prepare("SELECT photo_filename FROM package_gallery WHERE package_id = ?");
        $galleryStmt->bind_param("i", $id);
        $galleryStmt->execute();
        $galleryResult = $galleryStmt->get_result();
        
        while ($galleryRow = $galleryResult->fetch_assoc()) {
            $galleryPath = "uploads/gallery/" . $galleryRow['photo_filename'];
            if (file_exists($galleryPath)) {
                unlink($galleryPath);
            }
        }
        $galleryStmt->close();
        
        // Delete from database
        $deleteStmt = $koneksi->prepare("DELETE FROM paket WHERE id = ?");
        $deleteStmt->bind_param("i", $id);
        $deleteStmt->execute();
        $deleteStmt->close();
    }
    
    $stmt->close();
    header("Location: admin.php?deleted=1#packages-list");
    exit;
}

// Dashboard statistics functions
function getTotalPackages($koneksi) {
    $result = $koneksi->query("SELECT COUNT(*) as total FROM paket");
    return $result->fetch_assoc()['total'];
}

function getTotalPhotos($koneksi) {
    $result = $koneksi->query("SELECT COUNT(*) as total FROM package_gallery");
    $gallery_count = $result->fetch_assoc()['total'];
    
    $result = $koneksi->query("SELECT fotos FROM paket");
    $main_count = 0;
    while($row = $result->fetch_assoc()) {
        $fotos = json_decode($row['fotos'], true);
        if (is_array($fotos)) {
            $main_count += count($fotos);
        }
    }
    
    return $gallery_count + $main_count;
}

function getLatestPackageDate($koneksi) {
    $result = $koneksi->query("SELECT created_at FROM paket ORDER BY id DESC LIMIT 1");
    if ($result->num_rows > 0) {
        $date = $result->fetch_assoc()['created_at'];
        return date('d M Y', strtotime($date));
    }
    return 'Belum ada';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    <title>Admin Panel | Vacationland</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../admin/css/admin-clean.css?v=<?= time() ?>">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-content">
            <div class="header-left">
                <img src="../assets/images/logompti.png" alt="Logo" class="logo">
                <h1>Vacationland Admin</h1>
            </div>            <div class="header-right">
                <div class="admin-welcome">
                    <i class="fas fa-user-circle"></i>
                    <span>Hi, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
                </div>
                <a href="login.php?logout=1" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="admin-nav">        <div class="nav-content">
            <a href="#dashboard" class="nav-link active" onclick="showSection('dashboard', event)">
                <i class="fas fa-chart-pie"></i>
                Dashboard
            </a>
            <a href="#add-package" class="nav-link" onclick="showSection('add-package', event)">
                <i class="fas fa-plus-circle"></i>
                Tambah Paket
            </a>
            <a href="#packages-list" class="nav-link" onclick="showSection('packages-list', event)">
                <i class="fas fa-list"></i>
                Daftar Paket
            </a>
            <a href="#booking-history" class="nav-link" onclick="showSection('booking-history', event)">
                <i class="fas fa-history"></i>
                Riwayat Booking
            </a>            <a href="#payment-methods" class="nav-link" onclick="showSection('payment-methods', event)">
                <i class="fas fa-credit-card"></i>
                Metode Pembayaran
            </a>
            <a href="#newsletter" class="nav-link" onclick="showSection('newsletter', event)">
                <i class="fas fa-envelope"></i>
                Newsletter
            </a>
            <a href="#settings" class="nav-link" onclick="showSection('settings', event)">
                <i class="fas fa-cog"></i>
                Pengaturan
            </a>
            <a href="../FrontEnd/html/Index.html" target="_blank" class="nav-link">
                <i class="fas fa-external-link-alt"></i>
                Lihat Website
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="admin-main">
        <?= $message ?>
        
        <!-- Dashboard Section -->
        <section id="dashboard" class="content-section active">
            <div class="section-header">
                <h2><i class="fas fa-chart-pie"></i> Dashboard Overview</h2>
                <p>Ringkasan statistik sistem</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon packages">
                        <i class="fas fa-suitcase-rolling"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= getTotalPackages($koneksi) ?></h3>
                        <p>Total Paket</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon photos">
                        <i class="fas fa-images"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= getTotalPhotos($koneksi) ?></h3>
                        <p>Total Foto</p>
                    </div>
                </div>
                  <div class="stat-card">
                    <div class="stat-icon date">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= getLatestPackageDate($koneksi) ?></h3>
                        <p>Paket Terbaru</p>
                    </div>                </div>
            </div>
        </section>        <!-- Add Package Section -->
        <section id="add-package" class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-plus-circle"></i> <span id="form-mode-title">Tambah Paket Wisata</span></h2>
                <p id="form-mode-desc">Buat paket wisata baru untuk ditampilkan di website</p>
            </div>
            
            <div class="form-container">
                <form action="admin.php" method="POST" enctype="multipart/form-data" class="package-form">
                    <input type="hidden" name="action" value="add_package" id="form-action">
                    <input type="hidden" name="package_id" value="" id="package_id">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-info-circle"></i>
                            Informasi Dasar
                        </h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nama">
                                    <i class="fas fa-tag"></i>
                                    Nama Paket
                                    <span class="required">*</span>
                                </label>
                                <input type="text" id="nama" name="nama" required maxlength="100" 
                                       placeholder="Contoh: 2D1N Wisata Yogyakarta">
                            </div>
                            
                            <div class="form-group">
                                <label for="duration">
                                    <i class="fas fa-clock"></i>
                                    Durasi
                                </label>
                                <select id="duration" name="duration">
                                    <option value="1D">1 Hari</option>
                                    <option value="2D1N" selected>2 Hari 1 Malam</option>
                                    <option value="3D2N">3 Hari 2 Malam</option>
                                    <option value="4D3N">4 Hari 3 Malam</option>
                                    <option value="5D4N">5 Hari 4 Malam</option>
                                    <option value="Custom">Custom</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="deskripsi">
                                <i class="fas fa-align-left"></i>
                                Deskripsi Paket
                                <span class="required">*</span>
                            </label>
                            <textarea id="deskripsi" name="deskripsi" required rows="4" maxlength="500"
                                      placeholder="Deskripsikan paket wisata dengan menarik..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">
                                <i class="fas fa-money-bill-wave"></i>
                                Harga Paket
                                <span class="required">*</span>
                            </label>
                            <div class="price-input">
                                <span class="currency">Rp</span>
                                <input type="text" id="price" name="price" required 
                                       placeholder="1.500.000" 
                                       oninput="formatPrice(this)">
                                <span class="suffix">/ orang</span>
                            </div>
                            <small class="form-help">Masukkan harga dalam Rupiah (Rp 100.000 - Rp 50.000.000)</small>
                        </div>
                    </div>

                    <!-- Highlights Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-star"></i>
                            Highlight Paket
                        </h3>
                        <p class="section-desc">Tambahkan poin-poin menarik dari paket wisata ini</p>
                        
                        <div id="highlightsContainer" class="dynamic-list">
                            <div class="list-item">
                                <div class="item-content">
                                    <input type="text" name="highlights[]" placeholder="Masukkan highlight menarik..." required>
                                </div>
                                <div class="item-actions">
                                    <button type="button" class="btn-remove" onclick="removeListItem(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="list-actions">
                            <button type="button" class="btn-add" onclick="addHighlight()">
                                <i class="fas fa-plus"></i> Tambah Highlight
                            </button>
                        </div>
                    </div>

                    <!-- Itinerary Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-route"></i>
                            Itinerary Perjalanan
                        </h3>
                        <p class="section-desc">Susun jadwal aktivitas per hari</p>
                        
                        <div id="itineraryContainer" class="itinerary-container">
                            <!-- Hari pertama akan ditambahkan oleh JavaScript jika kosong, -->
                            <!-- atau Anda bisa meletakkan struktur hari pertama yang lengkap di sini -->
                            <div class="itinerary-day">
                                <div class="day-header">
                                    <h4><i class="fas fa-calendar-day"></i> Hari 1</h4>
                                    <button type="button" class="btn-remove-day" onclick="removeDay(this)" title="Hapus Hari">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="day-content">
                                    <div class="form-group">
                                        <label>Judul Hari</label>
                                        <input type="text" name="itinerary_titles[]" placeholder="Contoh: Eksplorasi Kota & Kuliner Tour" maxlength="100">
                                    </div>
                                    <div class="activities-list">
                                        <div class="activity-item">
                                            <div class="activity-time">
                                                <input type="time" name="itinerary_times[0][]" value="08:00">
                                            </div>
                                            <div class="activity-desc">
                                                <textarea name="itinerary_activities[0][]" placeholder="Deskripsi aktivitas..." rows="2"></textarea>
                                            </div>
                                            <div class="activity-actions">
                                                <button type="button" class="btn-remove-activity" onclick="removeActivity(this)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-add-activity" onclick="addActivity(this)">
                                        <i class="fas fa-plus"></i> Tambah Aktivitas
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="list-actions">
                            <button type="button" class="btn-add" onclick="addDay()">
                                <i class="fas fa-plus"></i> Tambah Hari
                            </button>
                        </div>
                    </div>

                    <!-- Inclusion Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-check-circle"></i>
                            Termasuk dalam Paket
                        </h3>
                        <p class="section-desc">Apa saja yang sudah termasuk dalam harga paket</p>
                        
                        <div id="inclusionsContainer" class="dynamic-list">
                            <div class="list-item">
                                <div class="item-icon">
                                    <select name="inclusion_icons[]" class="icon-select">
                                        <option value="fas fa-hotel">🏨 Hotel</option>
                                        <option value="fas fa-utensils">🍽️ Makan</option>
                                        <option value="fas fa-car">🚗 Transport</option>
                                        <option value="fas fa-ticket-alt">🎫 Tiket</option>
                                        <option value="fas fa-user-tie">👔 Guide</option>
                                        <option value="fas fa-camera">📸 Dokumentasi</option>
                                        <option value="fas fa-shield-alt">🛡️ Asuransi</option>
                                        <option value="fas fa-gift">🎁 Souvenir</option>
                                    </select>
                                </div>
                                <div class="item-content">
                                    <input type="text" name="inclusions[]" placeholder="Contoh: Hotel bintang 3 selama tour" maxlength="150">
                                </div>
                                <div class="item-actions">
                                    <button type="button" class="btn-remove" onclick="removeListItem(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="list-actions">
                            <button type="button" class="btn-add" onclick="addInclusion()">
                                <i class="fas fa-plus"></i> Tambah Item
                            </button>
                        </div>
                    </div>

                    <!-- Exclusion Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-times-circle"></i>
                            Tidak Termasuk dalam Paket
                        </h3>
                        <p class="section-desc">Apa saja yang tidak termasuk dalam harga paket</p>
                        
                        <div id="exclusionsContainer" class="dynamic-list">
                            <div class="list-item">
                                <div class="item-icon">
                                    <select name="exclusion_icons[]" class="icon-select">
                                        <option value="fas fa-plane">✈️ Pesawat</option>
                                        <option value="fas fa-shopping-bag">🛍️ Belanja</option>
                                        <option value="fas fa-utensils">🍽️ Makan Tambahan</option>
                                        <option value="fas fa-spa">💆 Spa/Massage</option>
                                        <option value="fas fa-cocktail">🍹 Minuman</option>
                                        <option value="fas fa-tshirt">👕 Perlengkapan</option>
                                        <option value="fas fa-phone">📱 Komunikasi</option>
                                        <option value="fas fa-hand-holding-usd">💰 Pengeluaran Pribadi</option>
                                    </select>
                                </div>
                                <div class="item-content">
                                    <input type="text" name="exclusions[]" placeholder="Contoh: Tiket pesawat ke destinasi" maxlength="150">
                                </div>
                                <div class="item-actions">
                                    <button type="button" class="btn-remove" onclick="removeListItem(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="list-actions">
                            <button type="button" class="btn-add" onclick="addExclusion()">
                                <i class="fas fa-plus"></i> Tambah Item
                            </button>
                        </div>
                    </div>

                    <!-- Photos Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-camera"></i>
                            Foto Paket
                        </h3>
                        
                        <div class="form-group">
                            <label for="fotos">
                                <i class="fas fa-images"></i>
                                Upload Foto Paket
                                <span class="required">*</span>
                            </label>
                            <div class="file-upload" onclick="document.getElementById('fotos').click()">
                                <div class="upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <div class="upload-text">
                                    <h4>Klik untuk Upload Foto</h4>
                                    <p>atau drag & drop file di sini</p>
                                    <small>JPG, PNG • Maks 5MB • 3-6 foto</small>
                                </div>
                            </div>
                            <input type="file" id="fotos" name="fotos[]" multiple accept="image/*" required style="display: none;">
                            <div id="file-preview" class="file-preview"></div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="reset" class="btn-secondary">
                            <i class="fas fa-undo"></i>
                            Reset
                        </button>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i>
                            Simpan Paket
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Packages List Section -->
        <section id="packages-list" class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-list"></i> Daftar Paket Wisata</h2>
                <p>Kelola paket wisata yang sudah dibuat</p>
            </div>
            
            <div class="packages-container">
                <?php
                $result = $koneksi->query("SELECT * FROM paket ORDER BY id DESC");
                if ($result->num_rows > 0) {
                    echo '<div class="packages-grid">';
                    while ($row = $result->fetch_assoc()) {
                        $fotos = json_decode($row['fotos'], true);
                        $firstPhoto = is_array($fotos) && !empty($fotos) ? $fotos[0] : 'default.jpg';
                        
                        // Get gallery count
                        $galleryCountQuery = $koneksi->prepare("SELECT COUNT(*) as count FROM package_gallery WHERE package_id = ?");
                        $galleryCountQuery->bind_param("i", $row['id']);
                        $galleryCountQuery->execute();
                        $galleryCount = $galleryCountQuery->get_result()->fetch_assoc()['count'];
                        $galleryCountQuery->close();
                        
                        echo '<div class="package-item">';
                        echo '<div class="package-image">';
                        echo '<img src="uploads/' . htmlspecialchars($firstPhoto) . '" alt="' . htmlspecialchars($row['nama']) . '" onerror="this.src=\'../assets/images/default.jpg\'">';
                        echo '</div>';
                        
                        echo '<div class="package-content">';
                        echo '<h3>' . htmlspecialchars($row['nama']) . '</h3>';
                        echo '<p class="package-desc">' . htmlspecialchars(substr($row['deskripsi'], 0, 100)) . '...</p>';
                        
                        echo '<div class="package-meta">';
                        if (isset($row['price']) && $row['price'] > 0) {
                            echo '<span class="price">Rp ' . number_format($row['price'], 0, ',', '.') . '</span>';
                        }
                        echo '<span class="date">' . date('d M Y', strtotime($row['created_at'])) . '</span>';
                        echo '</div>';
                        
                        echo '<div class="package-stats">';
                        echo '<span><i class="fas fa-camera"></i> ' . (is_array($fotos) ? count($fotos) : 0) . ' foto</span>';
                        echo '<span><i class="fas fa-images"></i> ' . $galleryCount . ' galeri</span>';
                        echo '</div>';
                        
                        echo '<div class="package-actions">';
                        echo '<button onclick="openGallery(' . $row['id'] . ', \'' . addslashes(htmlspecialchars($row['nama'])) . '\')" class="btn-gallery" title="Kelola Galeri">';
                        echo '<i class="fas fa-images"></i>';
                        echo '</button>';
                        echo '<button onclick="editPackage(' . $row['id'] . ')" class="btn-edit" title="Edit Paket">';
                        echo '<i class="fas fa-edit"></i>';
                        echo '</button>';
                        echo '<button onclick="deletePackage(' . $row['id'] . ', \'' . addslashes(htmlspecialchars($row['nama'])) . '\')" class="btn-delete" title="Hapus Paket">';
                        echo '<i class="fas fa-trash"></i>';
                        echo '</button>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                    echo '</div>';
                } else {
                    echo '<div class="empty-state">';
                    echo '<i class="fas fa-suitcase-rolling"></i>';
                    echo '<h3>Belum Ada Paket Wisata</h3>';
                    echo '<p>Mulai tambah paket wisata pertama Anda</p>';
                    echo '<button onclick="showSection(\'add-package\', event)" class="btn-primary">';
                    echo '<i class="fas fa-plus"></i> Tambah Paket Sekarang';
                    echo '</button>';
                    echo '</div>';
                }
                ?>
            </div>
        </section>
        
        <!-- Settings Section -->
        <section id="settings" class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-cog"></i> Pengaturan Website</h2>
                <p>Kelola informasi kontak dan pengaturan website</p>
            </div>
            
            <div class="form-container">
                <form action="admin.php" method="POST" class="settings-form">
                    <input type="hidden" name="action" value="update_settings">
                    
                    <!-- Kontak Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-phone"></i>
                            Informasi Kontak
                        </h3>
                        
                        <?php
                        // Fetch current settings
                        $current_settings = [];
                        $settings_result = $koneksi->query("SELECT setting_key, setting_value FROM website_settings");
                        if ($settings_result) {
                            while ($setting = $settings_result->fetch_assoc()) {
                                $current_settings[$setting['setting_key']] = $setting['setting_value'];
                            }
                        }
                        ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="setting_whatsapp_number">
                                    <i class="fab fa-whatsapp"></i>
                                    Nomor WhatsApp
                                </label>
                                <input type="text" id="setting_whatsapp_number" name="setting_whatsapp_number" 
                                       value="<?= htmlspecialchars($current_settings['whatsapp_number'] ?? '628123456789') ?>"
                                       placeholder="628123456789" pattern="[0-9]{10,15}">
                                <small class="form-help">Format: 628123456789 (gunakan kode negara tanpa +)</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="setting_phone_number">
                                    <i class="fas fa-phone"></i>
                                    Nomor Telepon
                                </label>
                                <input type="text" id="setting_phone_number" name="setting_phone_number" 
                                       value="<?= htmlspecialchars($current_settings['phone_number'] ?? '0812-3456-789') ?>"
                                       placeholder="0812-3456-789">
                                <small class="form-help">Nomor telepon yang akan ditampilkan di website</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="setting_email">
                                    <i class="fas fa-envelope"></i>
                                    Email
                                </label>
                                <input type="email" id="setting_email" name="setting_email" 
                                       value="<?= htmlspecialchars($current_settings['email'] ?? 'info@mptitravel.com') ?>"
                                       placeholder="info@mptitravel.com">
                            </div>
                            
                            <div class="form-group">
                                <label for="setting_instagram">
                                    <i class="fab fa-instagram"></i>
                                    Instagram
                                </label>
                                <input type="text" id="setting_instagram" name="setting_instagram" 
                                       value="<?= htmlspecialchars($current_settings['instagram'] ?? '@mptitravel') ?>"
                                       placeholder="@mptitravel">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pesan WhatsApp Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fab fa-whatsapp"></i>
                            Template Pesan WhatsApp
                        </h3>
                        
                        <div class="form-group">
                            <label for="setting_whatsapp_message">
                                <i class="fas fa-comment"></i>
                                Template Pesan Default
                            </label>
                            <textarea id="setting_whatsapp_message" name="setting_whatsapp_message" 
                                      rows="4" placeholder="Halo, saya tertarik dengan paket wisata..."><?= htmlspecialchars($current_settings['whatsapp_message'] ?? 'Halo, saya tertarik dengan paket wisata dari MPTI Travel. Bisakah Anda memberikan informasi lebih lanjut?') ?></textarea>
                            <small class="form-help">Pesan default yang akan muncul ketika pengunjung mengklik tombol WhatsApp</small>
                        </div>
                    </div>
                    
                    <!-- Alamat Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-map-marker-alt"></i>
                            Informasi Alamat
                        </h3>
                        
                        <div class="form-group">
                            <label for="setting_address">
                                <i class="fas fa-home"></i>
                                Alamat Lengkap
                            </label>
                            <textarea id="setting_address" name="setting_address" 
                                      rows="3" placeholder="Jl. Contoh No. 123, Yogyakarta"><?= htmlspecialchars($current_settings['address'] ?? 'Yogyakarta, Indonesia') ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="setting_city">
                                    <i class="fas fa-city"></i>
                                    Kota
                                </label>
                                <input type="text" id="setting_city" name="setting_city" 
                                       value="<?= htmlspecialchars($current_settings['city'] ?? 'Yogyakarta') ?>"
                                       placeholder="Yogyakarta">
                            </div>
                            
                            <div class="form-group">
                                <label for="setting_province">
                                    <i class="fas fa-flag"></i>
                                    Provinsi
                                </label>
                                <input type="text" id="setting_province" name="setting_province" 
                                       value="<?= htmlspecialchars($current_settings['province'] ?? 'DIY') ?>"
                                       placeholder="DIY">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Website Info Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-globe"></i>
                            Informasi Website
                        </h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="setting_website_name">
                                    <i class="fas fa-tag"></i>
                                    Nama Website
                                </label>
                                <input type="text" id="setting_website_name" name="setting_website_name" 
                                       value="<?= htmlspecialchars($current_settings['website_name'] ?? 'MPTI Travel') ?>"
                                       placeholder="MPTI Travel">
                            </div>
                            
                            <div class="form-group">
                                <label for="setting_website_url">
                                    <i class="fas fa-link"></i>
                                    URL Website
                                </label>
                                <input type="url" id="setting_website_url" name="setting_website_url" 
                                       value="<?= htmlspecialchars($current_settings['website_url'] ?? 'https://mptitravel.com') ?>"
                                       placeholder="https://mptitravel.com">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="setting_description">
                                <i class="fas fa-info-circle"></i>
                                Deskripsi Website
                            </label>
                            <textarea id="setting_description" name="setting_description" 
                                      rows="3" placeholder="Deskripsi singkat tentang website/perusahaan"><?= htmlspecialchars($current_settings['description'] ?? 'MPTI Travel - Solusi perjalanan wisata terbaik untuk liburan Anda') ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="reset" class="btn-secondary">
                            <i class="fas fa-undo"></i>
                            Reset
                        </button>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i>
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>        </section>
        
        <!-- Booking History Section -->
        <section id="booking-history" class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-history"></i> Riwayat Booking</h2>
                <p>Kelola dan pantau riwayat pemesanan paket wisata</p>
            </div>
            
            <!-- Add Booking Form -->
            <div class="form-container">
                <form action="admin.php" method="POST" class="booking-form">
                    <input type="hidden" name="action" value="add_booking">
                    
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-plus"></i>
                            Tambah Booking Baru
                        </h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="customer_name">
                                    <i class="fas fa-user"></i>
                                    Nama Customer *
                                </label>
                                <input type="text" id="customer_name" name="customer_name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="customer_phone">
                                    <i class="fas fa-phone"></i>
                                    No. Telepon *
                                </label>
                                <input type="text" id="customer_phone" name="customer_phone" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="customer_email">
                                    <i class="fas fa-envelope"></i>
                                    Email
                                </label>
                                <input type="email" id="customer_email" name="customer_email">
                            </div>
                            
                            <div class="form-group">
                                <label for="whatsapp_number">
                                    <i class="fab fa-whatsapp"></i>
                                    WhatsApp
                                </label>
                                <input type="text" id="whatsapp_number" name="whatsapp_number">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="package_id">
                                    <i class="fas fa-suitcase"></i>
                                    Paket
                                </label>
                                <select id="package_id" name="package_id">
                                    <option value="">Pilih Paket (Opsional)</option>
                                    <?php
                                    $packages = $koneksi->query("SELECT id, nama FROM paket ORDER BY nama");
                                    while ($pkg = $packages->fetch_assoc()) {
                                        echo "<option value='{$pkg['id']}'>{$pkg['nama']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="package_name">
                                    <i class="fas fa-tag"></i>
                                    Nama Paket *
                                </label>
                                <input type="text" id="package_name" name="package_name" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="booking_date">
                                    <i class="fas fa-calendar"></i>
                                    Tanggal Booking
                                </label>
                                <input type="date" id="booking_date" name="booking_date" value="<?= date('Y-m-d') ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="travel_date">
                                    <i class="fas fa-plane"></i>
                                    Tanggal Travel
                                </label>
                                <input type="date" id="travel_date" name="travel_date">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="participants">
                                    <i class="fas fa-users"></i>
                                    Jumlah Peserta
                                </label>
                                <input type="number" id="participants" name="participants" min="1" value="1">
                            </div>
                            
                            <div class="form-group">
                                <label for="total_price">
                                    <i class="fas fa-money-bill"></i>
                                    Total Harga
                                </label>
                                <input type="number" id="total_price" name="total_price" min="0" step="1000">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="payment_method">
                                    <i class="fas fa-credit-card"></i>
                                    Metode Pembayaran
                                </label>
                                <select id="payment_method" name="payment_method">
                                    <option value="">Pilih Metode</option>
                                    <?php
                                    $payment_methods = $koneksi->query("SELECT method_name FROM payment_methods WHERE is_active = 1 ORDER BY display_order");
                                    while ($method = $payment_methods->fetch_assoc()) {
                                        echo "<option value='{$method['method_name']}'>{$method['method_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="payment_status">
                                    <i class="fas fa-check-circle"></i>
                                    Status Pembayaran
                                </label>
                                <select id="payment_status" name="payment_status">
                                    <option value="pending">Pending</option>
                                    <option value="paid">Lunas</option>
                                    <option value="cancelled">Dibatalkan</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">
                                <i class="fas fa-sticky-note"></i>
                                Catatan
                            </label>
                            <textarea id="notes" name="notes" rows="3"></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i>
                                Simpan Booking
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Booking History List -->
            <div class="booking-list">
                <h3>📋 Daftar Booking</h3>
                <div class="table-container">
                    <table class="booking-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Paket</th>
                                <th>Tanggal</th>
                                <th>Peserta</th>
                                <th>Total</th>
                                <th>Pembayaran</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $bookings = $koneksi->query("SELECT * FROM booking_history ORDER BY created_at DESC LIMIT 20");
                            while ($booking = $bookings->fetch_assoc()) {
                                $paymentClass = $booking['payment_status'] == 'paid' ? 'status-paid' : ($booking['payment_status'] == 'cancelled' ? 'status-cancelled' : 'status-pending');
                                $bookingClass = $booking['booking_status'] == 'completed' ? 'status-completed' : ($booking['booking_status'] == 'cancelled' ? 'status-cancelled' : 'status-confirmed');
                                
                                echo "<tr>";
                                echo "<td>";
                                echo "<strong>{$booking['customer_name']}</strong><br>";
                                echo "<small>{$booking['customer_phone']}</small>";
                                if ($booking['customer_email']) echo "<br><small>{$booking['customer_email']}</small>";
                                echo "</td>";
                                echo "<td>{$booking['package_name']}</td>";
                                echo "<td>";
                                echo "Booking: " . date('d/m/Y', strtotime($booking['booking_date'])) . "<br>";
                                if ($booking['travel_date']) echo "Travel: " . date('d/m/Y', strtotime($booking['travel_date']));
                                echo "</td>";
                                echo "<td>{$booking['participants']} orang</td>";
                                echo "<td>Rp " . number_format($booking['total_price'], 0, ',', '.') . "</td>";
                                echo "<td><span class='status-badge $paymentClass'>{$booking['payment_method']} - " . ucfirst($booking['payment_status']) . "</span></td>";
                                echo "<td><span class='status-badge $bookingClass'>" . ucfirst($booking['booking_status']) . "</span></td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        
        <!-- Payment Methods Section -->
        <section id="payment-methods" class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-credit-card"></i> Metode Pembayaran</h2>
                <p>Kelola metode pembayaran yang tersedia di website</p>
            </div>
            
            <!-- Add/Edit Payment Method Form -->
            <div class="form-container">
                <form action="admin.php" method="POST" class="payment-form">
                    <input type="hidden" name="action" value="update_payment_method">
                    <input type="hidden" name="method_id" id="method_id" value="0">
                    
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-plus"></i>
                            <span id="form-title">Tambah Metode Pembayaran</span>
                        </h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="method_name">
                                    <i class="fas fa-tag"></i>
                                    Nama Metode *
                                </label>
                                <input type="text" id="method_name" name="method_name" required placeholder="Contoh: BCA, GoPay">
                            </div>
                            
                            <div class="form-group">
                                <label for="method_type">
                                    <i class="fas fa-layer-group"></i>
                                    Tipe
                                </label>
                                <select id="method_type" name="method_type">
                                    <option value="bank">Bank</option>
                                    <option value="ewallet">E-Wallet</option>
                                    <option value="card">Kartu Kredit</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="icon_class">
                                    <i class="fas fa-icons"></i>
                                    Icon Class
                                </label>
                                <input type="text" id="icon_class" name="icon_class" placeholder="fas fa-credit-card">
                                <small>Gunakan FontAwesome class, contoh: fas fa-credit-card, fab fa-cc-visa</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="display_order">
                                    <i class="fas fa-sort"></i>
                                    Urutan Tampil
                                </label>
                                <input type="number" id="display_order" name="display_order" min="0" value="0">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="is_active" name="is_active" checked>
                                <span class="checkmark"></span>
                                Aktif
                            </label>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="resetPaymentForm()">
                                <i class="fas fa-undo"></i>
                                Reset
                            </button>
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i>
                                Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Payment Methods List -->
            <div class="payment-methods-list">
                <h3>💳 Daftar Metode Pembayaran</h3>
                <div class="payment-grid">
                    <?php
                    $methods = $koneksi->query("SELECT * FROM payment_methods ORDER BY display_order ASC, method_name ASC");
                    while ($method = $methods->fetch_assoc()) {
                        $activeClass = $method['is_active'] ? 'active' : 'inactive';
                        echo "<div class='payment-item $activeClass'>";
                        echo "<div class='payment-icon'>";
                        echo "<i class='{$method['icon_class']}'></i>";
                        echo "</div>";
                        echo "<div class='payment-info'>";
                        echo "<h4>{$method['method_name']}</h4>";
                        echo "<p>Tipe: " . ucfirst($method['method_type']) . "</p>";
                        echo "<p>Urutan: {$method['display_order']}</p>";
                        echo "</div>";
                        echo "<div class='payment-actions'>";
                        echo "<button onclick='editPaymentMethod({$method['id']}, \"{$method['method_name']}\", \"{$method['method_type']}\", \"{$method['icon_class']}\", {$method['is_active']}, {$method['display_order']})' class='btn-edit'>";
                        echo "<i class='fas fa-edit'></i>";
                        echo "</button>";
                        echo "<form method='POST' style='display:inline;' onsubmit='return confirm(\"Hapus metode pembayaran ini?\")'>";
                        echo "<input type='hidden' name='action' value='delete_payment_method'>";
                        echo "<input type='hidden' name='method_id' value='{$method['id']}'>";
                        echo "<button type='submit' class='btn-delete'>";
                        echo "<i class='fas fa-trash'></i>";
                        echo "</button>";
                        echo "</form>";
                        echo "</div>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>        </section>

        <!-- Newsletter Section -->
        <section id="newsletter" class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-envelope"></i> Newsletter Management</h2>
                <p>Kelola email subscribers dan kirim newsletter promo</p>
            </div>
            
            <!-- Send Newsletter Form -->
            <div class="form-container">
                <form action="admin.php" method="POST" class="newsletter-form">
                    <input type="hidden" name="action" value="send_newsletter">
                    
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-paper-plane"></i>
                            Kirim Newsletter Promo
                        </h3>
                        
                        <div class="form-group">
                            <label for="newsletter_subject">
                                <i class="fas fa-heading"></i>
                                Subject *
                            </label>
                            <input type="text" id="newsletter_subject" name="newsletter_subject" required 
                                   placeholder="Contoh: Promo Spesial Paket Wisata Yogyakarta!">
                        </div>
                        
                        <div class="form-group">
                            <label for="newsletter_message">
                                <i class="fas fa-edit"></i>
                                Pesan Newsletter *
                            </label>
                            <textarea id="newsletter_message" name="newsletter_message" rows="8" required 
                                      placeholder="Tulis pesan promo Anda di sini...&#10;&#10;Contoh:&#10;Halo Sahabat Traveler!&#10;&#10;Kami menawarkan promo spesial untuk paket wisata Yogyakarta dengan diskon hingga 30%!&#10;&#10;Paket termasuk:&#10;- Transport AC&#10;- Makan 3x sehari&#10;- Guide berpengalaman&#10;- Tiket masuk wisata&#10;&#10;Hubungi kami segera di WhatsApp untuk booking!"></textarea>
                        </div>
                        
                        <div class="newsletter-stats">
                            <?php
                            $subscriber_count = $koneksi->query("SELECT COUNT(*) as total FROM newsletter_subscribers WHERE status = 'active'")->fetch_assoc()['total'];
                            ?>
                            <div class="stat-item">
                                <i class="fas fa-users"></i>
                                <span><?= $subscriber_count ?> Active Subscribers</span>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary" onclick="return confirm('Kirim newsletter ke <?= $subscriber_count ?> subscribers?')">
                                <i class="fas fa-paper-plane"></i>
                                Kirim ke Semua Subscribers
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Email Subscribers List -->
            <div class="subscribers-list">
                <h3>📧 Daftar Email Subscribers</h3>
                <div class="table-container">
                    <table class="subscribers-table">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Nama</th>
                                <th>Tanggal Subscribe</th>
                                <th>Email Terakhir</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $subscribers = $koneksi->query("SELECT * FROM newsletter_subscribers ORDER BY created_at DESC");
                            while ($subscriber = $subscribers->fetch_assoc()) {
                                $statusClass = $subscriber['status'] == 'active' ? 'status-active' : 'status-inactive';
                                
                                echo "<tr>";
                                echo "<td><strong>{$subscriber['email']}</strong></td>";
                                echo "<td>" . ($subscriber['name'] ? $subscriber['name'] : '<em>-</em>') . "</td>";
                                echo "<td>" . date('d/m/Y H:i', strtotime($subscriber['created_at'])) . "</td>";
                                echo "<td>" . ($subscriber['last_email_sent'] ? date('d/m/Y H:i', strtotime($subscriber['last_email_sent'])) : '<em>Belum pernah</em>') . "</td>";
                                echo "<td><span class='status-badge $statusClass'>" . ucfirst($subscriber['status']) . "</span></td>";
                                echo "<td>";
                                echo "<form method='POST' style='display:inline;' onsubmit='return confirm(\"Hapus subscriber ini?\")'>";
                                echo "<input type='hidden' name='action' value='delete_subscriber'>";
                                echo "<input type='hidden' name='subscriber_id' value='{$subscriber['id']}'>";
                                echo "<button type='submit' class='btn-delete small'>";
                                echo "<i class='fas fa-trash'></i>";
                                echo "</button>";
                                echo "</form>";
                                echo "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Newsletter Statistics -->
            <div class="newsletter-stats-section">
                <h3>📊 Statistik Newsletter</h3>
                <div class="stats-grid">
                    <?php
                    $total_subscribers = $koneksi->query("SELECT COUNT(*) as total FROM newsletter_subscribers")->fetch_assoc()['total'];
                    $active_subscribers = $koneksi->query("SELECT COUNT(*) as total FROM newsletter_subscribers WHERE status = 'active'")->fetch_assoc()['total'];
                    $recent_subscriptions = $koneksi->query("SELECT COUNT(*) as total FROM newsletter_subscribers WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['total'];
                    ?>
                    
                    <div class="stat-card">
                        <div class="stat-icon subscribers">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $total_subscribers ?></h3>
                            <p>Total Subscribers</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon active">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $active_subscribers ?></h3>
                            <p>Active Subscribers</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon recent">
                            <i class="fas fa-calendar-week"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $recent_subscriptions ?></h3>
                            <p>Baru (7 hari)</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content small-modal">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus</h3>
                <button class="modal-close" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus paket "<strong id="deletePackageName"></strong>"?</p>
                <p class="modal-warning">Tindakan ini tidak dapat diurungkan dan akan menghapus semua foto terkait.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="admin.php" method="POST">
                    <input type="hidden" name="hapus" id="deletePackageId">
                    <button type="button" class="btn-secondary" onclick="closeDeleteModal()">
                        <i class="fas fa-ban"></i> Batal
                    </button>
                    <button type="submit" class="btn-delete">
                        <i class="fas fa-trash"></i> Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Gallery Modal -->
    <div id="galleryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-images"></i> Kelola Galeri: <span id="packageName"></span></h3>
                <button class="modal-close" onclick="closeGallery()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="upload-section">
                    <h4>Upload Foto Baru</h4>
                    <form id="galleryForm" enctype="multipart/form-data">
                        <input type="hidden" name="package_id" id="packageId">
                        <div class="form-group">
                            <input type="file" id="galleryFiles" name="photos[]" multiple accept="image/*">
                            <div id="galleryCaptions" class="captions-container"></div>
                        </div>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-upload"></i> Upload Foto
                        </button>
                    </form>
                </div>
                
                <div class="gallery-section">
                    <h4>Foto Tersimpan</h4>
                    <div id="existingPhotos" class="photos-grid">
                        <p>Memuat foto...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Modal -->
    <div id="templateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-magic"></i> Pilih Template</h3>
                <button class="modal-close" onclick="closeTemplateModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="templateContent">
                    <!-- Template content will be loaded here -->
                </div>
        </div>
        </div>    </div>
    
    <script src="../admin/js/admin-clean.js?v=<?= time() ?>"></script>
    
    <script>
        // Payment Methods Management
        function editPaymentMethod(id, name, type, icon, active, order) {
            document.getElementById('method_id').value = id;
            document.getElementById('method_name').value = name;
            document.getElementById('method_type').value = type;
            document.getElementById('icon_class').value = icon;
            document.getElementById('is_active').checked = active == 1;
            document.getElementById('display_order').value = order;
            document.getElementById('form-title').textContent = 'Edit Metode Pembayaran';
            
            // Scroll to form
            document.querySelector('#payment-methods .form-container').scrollIntoView({behavior: 'smooth'});
        }
        
        function resetPaymentForm() {
            document.getElementById('method_id').value = '0';
            document.getElementById('method_name').value = '';
            document.getElementById('method_type').value = 'bank';
            document.getElementById('icon_class').value = '';
            document.getElementById('is_active').checked = true;
            document.getElementById('display_order').value = '0';
            document.getElementById('form-title').textContent = 'Tambah Metode Pembayaran';
        }
          // Auto-populate package name when package is selected
        document.addEventListener('DOMContentLoaded', function() {
            const packageSelect = document.getElementById('package_id');
            const packageNameInput = document.getElementById('package_name');
            
            if (packageSelect && packageNameInput) {
                packageSelect.addEventListener('change', function() {
                    if (this.value) {
                        const selectedOption = this.options[this.selectedIndex];
                        packageNameInput.value = selectedOption.text;
                    }
                });
            }
            
            // Check if we're in edit mode
            const urlParams = new URLSearchParams(window.location.search);
            const editId = urlParams.get('edit_id');
            if (editId) {
                editPackage(editId);
            }
        });
        
        // Package Edit Functions
        function editPackage(packageId) {
            // Switch to add-package section
            showSection('add-package');
            
            // Fetch package data
            fetch(`get_package_data.php?id=${packageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populatePackageForm(data.data);
                    } else {
                        alert('Gagal memuat data paket: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memuat data paket');
                });
        }
        
        function populatePackageForm(packageData) {
            // Change form mode to edit
            document.getElementById('form-action').value = 'update_package';
            document.getElementById('package_id').value = packageData.id;
            document.getElementById('form-mode-title').textContent = 'Edit Paket Wisata';
            document.getElementById('form-mode-desc').textContent = 'Perbarui informasi paket wisata';
            
            // Basic fields
            document.getElementById('nama').value = packageData.nama || '';
            document.getElementById('deskripsi').value = packageData.deskripsi || '';
            document.getElementById('duration').value = packageData.duration || '2D1N';
            document.getElementById('price').value = packageData.price_formatted || '';
            
            // Clear existing dynamic content
            clearHighlights();
            clearInclusions();
            clearExclusions();
            clearItinerary();
            
            // Populate highlights
            if (packageData.highlights && packageData.highlights.length > 0) {
                packageData.highlights.forEach(highlight => {
                    addHighlight(highlight);
                });
            } else {
                addHighlight(); // Add one empty highlight
            }
            
            // Populate inclusions
            if (packageData.inclusions && packageData.inclusions.length > 0) {
                packageData.inclusions.forEach(inclusion => {
                    addInclusion(inclusion.text, inclusion.icon);
                });
            } else {
                addInclusion(); // Add one empty inclusion
            }
            
            // Populate exclusions
            if (packageData.exclusions && packageData.exclusions.length > 0) {
                packageData.exclusions.forEach(exclusion => {
                    addExclusion(exclusion.text, exclusion.icon);
                });
            } else {
                addExclusion(); // Add one empty exclusion
            }
            
            // Populate itinerary
            if (packageData.itinerary && packageData.itinerary.length > 0) {
                packageData.itinerary.forEach((day, dayIndex) => {
                    addDay(day.title);
                    if (day.activities && day.activities.length > 0) {
                        day.activities.forEach((activity, actIndex) => {
                            if (actIndex === 0) {
                                // First activity - update existing
                                const dayContainer = document.querySelectorAll('.itinerary-day')[dayIndex];
                                const timeInput = dayContainer.querySelector('input[type="time"]');
                                const activityInput = dayContainer.querySelector('textarea');
                                if (timeInput) timeInput.value = activity.time || '';
                                if (activityInput) activityInput.value = activity.activity || '';
                            } else {
                                // Additional activities - add new
                                const dayContainer = document.querySelectorAll('.itinerary-day')[dayIndex];
                                const addButton = dayContainer.querySelector('.btn-add-activity');
                                if (addButton) {
                                    addActivity(addButton, activity.time, activity.activity);
                                }
                            }
                        });
                    }
                });
            } else {
                addDay(); // Add one empty day
            }
            
            // Update photo upload label
            const uploadText = document.querySelector('.upload-text h4');
            if (uploadText) {
                uploadText.textContent = 'Klik untuk Upload Foto Baru (Opsional)';
            }
            const uploadSmall = document.querySelector('.upload-text small');
            if (uploadSmall) {
                uploadSmall.textContent = 'JPG, PNG • Maks 5MB • 3-6 foto (Kosongkan jika tidak ingin mengubah foto)';
            }
            
            // Remove required attribute from file input
            const fileInput = document.getElementById('fotos');
            if (fileInput) {
                fileInput.removeAttribute('required');
            }
            
            // Add reset button
            addResetEditButton();
            
            // Scroll to form
            document.getElementById('add-package').scrollIntoView({behavior: 'smooth'});
        }
        
        function resetPackageForm() {
            // Reset form to add mode
            document.getElementById('form-action').value = 'add_package';
            document.getElementById('package_id').value = '';
            document.getElementById('form-mode-title').textContent = 'Tambah Paket Wisata';
            document.getElementById('form-mode-desc').textContent = 'Buat paket wisata baru untuk ditampilkan di website';
            
            // Reset form
            document.querySelector('.package-form').reset();
            
            // Reset file input
            const fileInput = document.getElementById('fotos');
            if (fileInput) {
                fileInput.setAttribute('required', 'required');
            }
            
            // Reset upload text
            const uploadText = document.querySelector('.upload-text h4');
            if (uploadText) {
                uploadText.textContent = 'Klik untuk Upload Foto';
            }
            const uploadSmall = document.querySelector('.upload-text small');
            if (uploadSmall) {
                uploadSmall.textContent = 'JPG, PNG • Maks 5MB • 3-6 foto';
            }
            
            // Clear dynamic sections
            clearHighlights();
            clearInclusions();
            clearExclusions();
            clearItinerary();
            
            // Add default items
            addHighlight();
            addInclusion();
            addExclusion();
            addDay();
            
            // Remove reset button
            removeResetEditButton();
            
            // Remove edit_id from URL
            const url = new URL(window.location);
            url.searchParams.delete('edit_id');
            window.history.replaceState({}, document.title, url);
        }
        
        function addResetEditButton() {
            // Check if button already exists
            if (document.getElementById('reset-edit-btn')) return;
            
            const formActions = document.querySelector('.package-form .form-actions');
            if (formActions) {
                const resetBtn = document.createElement('button');
                resetBtn.type = 'button';
                resetBtn.id = 'reset-edit-btn';
                resetBtn.className = 'btn-secondary';
                resetBtn.innerHTML = '<i class="fas fa-plus"></i> Mode Tambah Baru';
                resetBtn.onclick = resetPackageForm;
                
                // Insert before submit button
                const submitBtn = formActions.querySelector('button[type="submit"]');
                formActions.insertBefore(resetBtn, submitBtn);
            }
        }
        
        function removeResetEditButton() {
            const resetBtn = document.getElementById('reset-edit-btn');
            if (resetBtn) {
                resetBtn.remove();
            }
        }
        
        // Helper functions for clearing dynamic sections
        function clearHighlights() {
            const container = document.getElementById('highlightsContainer');
            if (container) container.innerHTML = '';
        }
        
        function clearInclusions() {
            const container = document.getElementById('inclusionsContainer');
            if (container) container.innerHTML = '';
        }
        
        function clearExclusions() {
            const container = document.getElementById('exclusionsContainer');
            if (container) container.innerHTML = '';
        }
        
        function clearItinerary() {
            const container = document.getElementById('itineraryContainer');
            if (container) container.innerHTML = '';
        }
    </script>
</body>
</html>
