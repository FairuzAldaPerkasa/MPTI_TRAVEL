<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: loginadmin.php?error=not_logged_in");
    exit;
}

// Database connection
$koneksi = new mysqli("localhost", "root", "", "paket_travel");
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Display messages
$message = '';
if (isset($_GET['success'])) {
    $message = '<div class="success">Paket berhasil ditambahkan!</div>';
} elseif (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_price':
            $message = '<div class="error">Harga tidak valid!</div>';
            break;
        case 'price_too_low':
            $message = '<div class="error">Harga minimal Rp 100.000!</div>';
            break;
        case 'price_too_high':
            $message = '<div class="error">Harga maksimal Rp 50.000.000!</div>';
            break;
        case 'empty_fields':
            $message = '<div class="error">Semua field harus diisi!</div>';
            break;
        case 'no_files':
            $message = '<div class="error">Minimal harus upload 1 foto!</div>';
            break;
        case 'invalid_photo_count':
            $message = '<div class="error">Upload 3-6 foto saja!</div>';
            break;
        case 'invalid_file_type':
            $message = '<div class="error">Hanya file JPG, JPEG, PNG yang diperbolehkan!</div>';
            break;
        case 'file_too_large':
            $message = '<div class="error">Ukuran file maksimal 5MB!</div>';
            break;
        case 'upload_failed':
            $message = '<div class="error">Gagal upload file!</div>';
            break;
        case 'database_error':
            $message = '<div class="error">Terjadi kesalahan database!</div>';
            break;
        case 'input_too_long':
            $message = '<div class="error">Input terlalu panjang!</div>';
            break;
        default:
            $message = '<div class="error">Terjadi kesalahan tidak dikenal!</div>';
            break;
    }
} elseif (isset($_GET['deleted'])) {
    $message = '<div class="success">Paket berhasil dihapus!</div>';
}

// Handle delete functionality
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    
    // Get photos before deleting
    $stmt = $koneksi->prepare("SELECT fotos FROM paket WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Decode JSON array of photos
        $fotosArray = json_decode($row['fotos'], true);
        if ($fotosArray && is_array($fotosArray)) {
            // Delete all photos
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
    header("Location: admin.php?deleted=1");
    exit;
}

// Tambahkan setelah koneksi database

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Enhanced responsive meta tags -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#3498db">
    <meta name="msapplication-navbutton-color" content="#3498db">
    <meta name="apple-mobile-web-app-title" content="Admin Panel">
    
    <title>Admin Panel | Vacationland</title>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="admin-styles.css?v=<?= time() ?>">
</head>
<body>
    <!-- Update header section -->
    <!-- Enhanced Header -->
    <header class="admin-header">
        <div class="header-content">
            <div class="logo-section">
                <img src="../Asset/logo/logompti.png" alt="Logo">
                <h1>Vacationland Admin</h1>
            </div>
            
            <!-- Mobile Menu Toggle -->
            <div class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>
            
            <div class="admin-info desktop-only">
                <div class="admin-welcome">
                    <i class="fas fa-user-shield"></i>
                    <span>Welcome, <?= $_SESSION['admin_name'] ?? 'Admin' ?></span>
                </div>
                <a href="loginadmin.php?logout=1" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Enhanced Mobile Sidebar -->
    <div class="mobile-sidebar" id="mobileSidebar">
        <div class="mobile-sidebar-header">
            <div class="mobile-logo">
                <img src="../Asset/logo/logompti.png" alt="Logo">
                <span>Admin Panel</span>
            </div>
            <button class="mobile-close" onclick="toggleMobileMenu()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <nav class="mobile-nav">
            <a href="#tambah-paket" onclick="scrollToSection('tambah-paket')">
                <i class="fas fa-plus-circle"></i>
                <span>Tambah Paket</span>
            </a>
            <a href="#daftar-paket" onclick="scrollToSection('daftar-paket')">
                <i class="fas fa-list"></i>
                <span>Daftar Paket</span>
            </a>
            <a href="../FrontEnd/html/Index.html" target="_blank">
                <i class="fas fa-eye"></i>
                <span>Lihat Website</span>
            </a>
        </nav>
        
        <div class="mobile-logout">
            <a href="loginadmin.php?logout=1">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleMobileMenu()"></div>

    <!-- Enhanced Container with improved sections -->
    <div class="container">
        <?= $message ?>
        
        <!-- Dashboard Stats Section -->
        <div class="admin-section dashboard-stats">
            <div class="section-title">
                <i class="fas fa-chart-line"></i>
                Dashboard Overview
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-suitcase-rolling"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= getTotalPackages($koneksi) ?></h3>
                        <p>Total Paket</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-images"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= getTotalPhotos($koneksi) ?></h3>
                        <p>Total Foto</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= getLatestPackageDate($koneksi) ?></h3>
                        <p>Paket Terbaru</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Add Package Section -->
        <div class="admin-section" id="tambah-paket">
            <div class="section-title">
                <i class="fas fa-plus-circle"></i>
                Tambah Paket Wisata Baru
            </div>
            
            <!-- Multi-Step Form -->
            <form action="tambah.php" method="POST" enctype="multipart/form-data" class="enhanced-form">
                
                <!-- Step 1: Basic Information -->
                <div class="form-step active" data-step="1" id="step-1">
                    <div class="form-section">
                        <div class="subsection-title">
                            <i class="fas fa-info-circle"></i>
                            Informasi Dasar Paket
                        </div>
                        
                        <div class="form-grid-two">
                            <div class="form-group">
                                <label for="nama"><i class="fas fa-tag"></i> Nama Paket <span class="required">*</span></label>
                                <input type="text" id="nama" name="nama" required maxlength="100" 
                                       placeholder="Contoh: 2D1N Wisata Yogyakarta">
                                <span class="form-help">Masukkan nama paket yang menarik dan deskriptif</span>
                            </div>
                            
                            <div class="form-group">
                                <label for="duration"><i class="fas fa-clock"></i> Durasi</label>
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
                            <label for="deskripsi"><i class="fas fa-align-left"></i> Deskripsi Paket <span class="required">*</span></label>
                            <textarea id="deskripsi" name="deskripsi" required rows="4" maxlength="500"
                                      placeholder="Deskripsikan paket wisata ini dengan menarik..."></textarea>
                            <span class="form-help">Jelaskan keunikan dan daya tarik paket wisata ini</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="price"><i class="fas fa-money-bill-wave"></i> Harga Paket <span class="required">*</span></label>
                            <div class="price-input-wrapper">
                                <span class="currency-symbol">Rp</span>
                                <input type="text" id="price" name="price" required 
                                       placeholder="0" 
                                       oninput="formatPriceInput(this)"
                                       onblur="validatePriceInput(this)">
                                <span class="price-suffix">/ orang</span>
                            </div>
                            <div id="price-preview" class="price-preview"></div>
                            <span class="form-help">Masukkan harga dalam Rupiah (tanpa titik atau koma)</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="fotos"><i class="fas fa-camera"></i> Foto Paket <span class="required">*</span></label>
                            <div class="file-upload-area" onclick="document.getElementById('fotos').click()">
                                <div class="upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <div class="upload-content">
                                    <h4>Klik untuk Upload Foto</h4>
                                    <p>atau drag & drop file di sini</p>
                                </div>
                                <div class="upload-requirements">
                                    <div class="req-item"><i class="fas fa-check"></i> Format: JPG, PNG</div>
                                    <div class="req-item"><i class="fas fa-check"></i> Ukuran: Maks 5MB per file</div>
                                    <div class="req-item"><i class="fas fa-check"></i> Jumlah: 3-6 foto</div>
                                </div>
                            </div>
                            <input type="file" id="fotos" name="fotos[]" multiple accept="image/*" required style="display: none;">
                            <div id="file-preview" class="file-preview"></div>
                        </div>
                    </div>
                    
                    <div class="step-navigation">
                        <button type="button" class="btn-next" onclick="nextStep()">
                            Lanjut ke Highlights <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Step 2: Highlights & Features -->
                <div class="form-step" data-step="2" id="step-2">
                    <div class="form-section">
                        <div class="subsection-title">
                            <i class="fas fa-star"></i>
                            Highlights & Yang Termasuk/Tidak Termasuk
                        </div>
                        
                        <div class="form-grid-two">
                            <!-- Highlights Builder -->
                            <div class="form-group">
                                <label><i class="fas fa-gem"></i> Highlights Paket</label>
                                <div class="highlights-builder">
                                    <div class="input-header">
                                        <button type="button" class="btn-add-sample" onclick="addHighlightSample()">
                                            <i class="fas fa-magic"></i> Sample Data
                                        </button>
                                        <button type="button" class="btn-add-sample" onclick="clearHighlights()">
                                            <i class="fas fa-trash"></i> Clear All
                                        </button>
                                    </div>
                                    
                                    <div class="highlight-builder-container">
                                        <div id="highlight-items" class="highlight-items">
                                            <!-- Highlight items akan ditambahkan di sini via JavaScript -->
                                        </div>
                                        
                                        <button type="button" class="btn-add-highlight" onclick="addHighlightItem()">
                                            <i class="fas fa-plus"></i> Tambah Highlight
                                        </button>
                                    </div>
                                    
                                    <!-- Hidden input untuk form submission -->
                                    <input type="hidden" id="highlights" name="highlights">
                                    
                                    <!-- Live Preview -->
                                    <div class="highlight-preview">
                                        <div class="preview-header">
                                            <i class="fas fa-eye"></i> Preview Highlights
                                        </div>
                                        <div id="highlight-preview" class="preview-content">
                                            <p><em>Highlights akan muncul di sini...</em></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Inclusions Builder -->
                            <div class="form-group">
                                <label><i class="fas fa-check-circle"></i> Yang Termasuk</label>
                                <div class="inclusions-builder">
                                    <div class="input-header">
                                        <button type="button" class="btn-add-sample" onclick="addInclusionSample()">
                                            <i class="fas fa-magic"></i> Sample Data
                                        </button>
                                        <button type="button" class="btn-add-sample" onclick="clearInclusions()">
                                            <i class="fas fa-trash"></i> Clear All
                                        </button>
                                    </div>
                                    
                                    <textarea id="inclusions-text" name="inclusions" rows="8" 
                                              placeholder="Masukkan item yang termasuk dalam paket, pisahkan dengan enter...
🏨 Akomodasi hotel
🚐 Transportasi AC
🎫 Tiket masuk objek wisata
🍽️ Makan sesuai program
👨‍🏫 Guide profesional"
                                              oninput="updateInclusionPreview()"></textarea>
                                    
                                    <!-- Live Preview -->
                                    <div class="inclusion-preview">
                                        <div class="preview-header">
                                            <i class="fas fa-eye"></i> Preview Yang Termasuk
                                        </div>
                                        <div id="inclusion-preview" class="preview-content">
                                            <p><em>Yang termasuk akan muncul di sini...</em></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Exclusions Builder -->
                        <div class="form-group">
                            <label><i class="fas fa-times-circle"></i> Yang Tidak Termasuk</label>
                            <div class="exclusions-builder">
                                <div class="input-header">
                                    <button type="button" class="btn-add-sample" onclick="addExclusionSample()">
                                        <i class="fas fa-magic"></i> Sample Data
                                    </button>
                                    <button type="button" class="btn-add-sample" onclick="clearExclusions()">
                                        <i class="fas fa-trash"></i> Clear All
                                    </button>
                                </div>
                                
                                <textarea id="exclusions-text" name="exclusions" rows="8" 
                                          placeholder="Masukkan item yang tidak termasuk dalam paket, pisahkan dengan enter...
✈️ Tiket pesawat/kereta
🍻 Minuman beralkohol
🛍️ Belanja pribadi
📱 Keperluan pribadi
💸 Tips guide (opsional)"
                                          oninput="updateExclusionPreview()"></textarea>
                                
                                <!-- Live Preview -->
                                <div class="exclusion-preview">
                                    <div class="preview-header">
                                        <i class="fas fa-eye"></i> Preview Yang Tidak Termasuk
                                    </div>
                                    <div id="exclusion-preview" class="preview-content">
                                        <p><em>Yang tidak termasuk akan muncul di sini...</em></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="step-navigation">
                        <button type="button" class="btn-prev" onclick="prevStep()">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </button>
                        <button type="button" class="btn-next" onclick="nextStep()">
                            Lanjut ke Itinerary <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Step 3: Itinerary -->
                <div class="form-step" data-step="3" id="step-3">
                    <div class="form-section">
                        <div class="subsection-title">
                            <i class="fas fa-route"></i>
                            Itinerary Perjalanan
                        </div>
                        
                        <div class="itinerary-builder">
                            <div class="input-header">
                                <button type="button" class="btn-add-sample" onclick="loadSampleItinerary()">
                                    <i class="fas fa-magic"></i> Sample Itinerary
                                </button>
                                <button type="button" class="btn-add-sample" onclick="clearItinerary()">
                                    <i class="fas fa-trash"></i> Clear All
                                </button>
                                <button type="button" class="btn-add-day" onclick="addNewDay()">
                                    <i class="fas fa-plus"></i> Tambah Hari
                                </button>
                            </div>
                            
                            <div id="itinerary-days" class="itinerary-days">
                                <!-- Days akan ditambahkan di sini via JavaScript -->
                            </div>
                            
                            <!-- Hidden input untuk form submission -->
                            <input type="hidden" id="itinerary" name="itinerary">
                            
                            <!-- Live Preview -->
                            <div class="itinerary-preview">
                                <div class="preview-header">
                                    <i class="fas fa-eye"></i> Preview Itinerary
                                </div>
                                <div id="itinerary-preview" class="preview-content">
                                    <p><em>Itinerary akan muncul di sini...</em></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="step-navigation">
                        <button type="button" class="btn-prev" onclick="prevStep()">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </button>
                        <button type="button" class="btn-next" onclick="nextStep()">
                            Review & Submit <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Step 4: Review & Submit -->
                <div class="form-step" data-step="4" id="step-4">
                    <div class="form-section">
                        <div class="subsection-title">
                            <i class="fas fa-check-double"></i>
                            Review & Submit
                        </div>
                        
                        <div class="review-summary">
                            <h3>Review Detail Paket</h3>
                            <p>Pastikan semua informasi sudah benar sebelum menyimpan paket.</p>
                            
                            <div class="review-sections">
                                <div class="review-basic">
                                    <h4><i class="fas fa-info-circle"></i> Informasi Dasar</h4>
                                    <div id="review-basic-content">
                                        <!-- Will be populated by JavaScript -->
                                    </div>
                                </div>
                                
                                <div class="review-highlights">
                                    <h4><i class="fas fa-star"></i> Highlights</h4>
                                    <div id="review-highlights-content">
                                        <!-- Will be populated by JavaScript -->
                                    </div>
                                </div>
                                
                                <div class="review-inclusions">
                                    <h4><i class="fas fa-check"></i> Inclusions/Exclusions</h4>
                                    <div id="review-inclusions-content">
                                        <!-- Will be populated by JavaScript -->
                                    </div>
                                </div>
                                
                                <div class="review-itinerary">
                                    <h4><i class="fas fa-route"></i> Itinerary</h4>
                                    <div id="review-itinerary-content">
                                        <!-- Will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="step-navigation">
                        <button type="button" class="btn-prev" onclick="prevStep()">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </button>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> Simpan Paket
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Existing Packages Section -->
        <div class="admin-section" id="paket-list">
            <!-- Content paket yang sudah ada tetap sama -->
        </div>
    </div>

    <!-- Gallery Management Modal - LENGKAP -->
    <div id="galleryManageModal" class="modal" style="backdrop-filter: blur(3px);">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3>Kelola Galeri untuk Paket: <span id="galleryPackageName" style="font-weight: bold;"></span></h3>
                <span class="close" onclick="closeGalleryModal()">&times;</span>
            </div>
            <div class="modal-body">
                <!-- Form Upload Foto Tambahan -->
                <div class="form-section" style="margin-bottom: 20px;">
                    <h4 class="subsection-title" style="font-size: 1.2rem; margin-bottom:15px;">
                        <i class="fas fa-upload"></i> Upload Foto Tambahan
                    </h4>
                    <form id="galleryUploadForm" enctype="multipart/form-data">
                        <input type="hidden" name="package_id" id="galleryPackageId">
                        <div class="form-group">
                            <label for="galleryFiles">
                                <i class="fas fa-images"></i> Pilih Foto (bisa lebih dari satu)
                            </label>
                            <input type="file" 
                                   id="galleryFiles" 
                                   name="photos[]" 
                                   multiple 
                                   accept="image/jpeg,image/jpg,image/png">
                            <small class="form-help">Format: JPG, JPEG, PNG. Maks: 5MB/file.</small>
                        </div>
                        <div id="galleryCaptions" class="form-group" style="margin-top:10px;">
                            <!-- Input caption akan digenerate oleh JS -->
                        </div>
                        <button type="submit" class="btn-submit" style="margin-top:15px;">
                            <i class="fas fa-cloud-upload-alt"></i> Upload Foto
                        </button>
                    </form>
                    <div id="galleryUploadMessage" style="margin-top:10px;"></div>
                </div>

                <!-- Daftar Foto yang Sudah Ada -->
                <div class="form-section">
                    <h4 class="subsection-title" style="font-size: 1.2rem; margin-bottom:15px;">
                        <i class="fas fa-photo-video"></i> Foto Tersimpan
                    </h4>
                    <div id="existingPhotos" class="packages-grid" 
                         style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                        <p>Tidak ada foto tambahan.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div> <!-- End container -->

    <!-- Load admin-gallery.js SEBELUM script utama -->
    <script src="admin-gallery.js?v=<?= time() ?>"></script>

    <script>
console.log('🚀 Admin script loaded');

// =================================
// NOTIFICATION SYSTEM
// =================================

function showNotification(message, type = 'info', duration = 5000) {
    console.log(`📢 Notification: ${type} - ${message}`);
    
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.admin-notification');
    existingNotifications.forEach(notification => {
        notification.remove();
    });
    
    const notification = document.createElement('div');
    notification.className = `admin-notification notification-${type}`;
    
    // Set styles based on type
    const typeStyles = {
        success: {
            background: 'linear-gradient(135deg, #27ae60, #2ecc71)',
            icon: 'fas fa-check-circle'
        },
        error: {
            background: 'linear-gradient(135deg, #e74c3c, #c0392b)',
            icon: 'fas fa-exclamation-triangle'
        },
        warning: {
            background: 'linear-gradient(135deg, #f39c12, #e67e22)',
            icon: 'fas fa-exclamation-circle'
        },
        info: {
            background: 'linear-gradient(135deg, #3498db, #2980b9)',
            icon: 'fas fa-info-circle'
        }
    };
    
    const style = typeStyles[type] || typeStyles.info;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${style.background};
        color: white;
        padding: 15px 20px;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        z-index: 10000;
        max-width: 350px;
        min-width: 250px;
        animation: slideInRight 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        font-weight: 500;
        font-size: 0.9rem;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    `;
    
    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="${style.icon}" style="font-size: 1.2rem; flex-shrink: 0;"></i>
            <span style="flex: 1; line-height: 1.4;">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" 
                    style="background: none; border: none; color: rgba(255,255,255,0.8); cursor: pointer; font-size: 1.3rem; padding: 0; margin-left: 8px; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.3s ease;"
                    title="Tutup notifikasi">×</button>
        </div>
    `;
    
    // Add animation keyframes if not already added
    if (!document.querySelector('#notification-styles')) {
        const styleSheet = document.createElement('style');
        styleSheet.id = 'notification-styles';
        styleSheet.textContent = `
            @keyframes slideInRight {
                from { opacity: 0; transform: translateX(100%); }
                to { opacity: 1; transform: translateX(0); }
            }
            @keyframes slideOutRight {
                from { opacity: 1; transform: translateX(0); }
                to { opacity: 0; transform: translateX(100%); }
            }
            .admin-notification:hover {
                transform: translateY(-2px);
                box-shadow: 0 12px 35px rgba(0,0,0,0.2) !important;
                transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            }
        `;
        document.head.appendChild(styleSheet);
    }
    
    document.body.appendChild(notification);
    
    // Auto-remove after duration
    if (duration > 0) {
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideOutRight 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 400);
            }
        }, duration);
    }
    
    return notification;
}

// Export notification functions
window.showNotification = showNotification;
window.showSuccess = (msg, dur = 3000) => showNotification(msg, 'success', dur);
window.showError = (msg, dur = 5000) => showNotification(msg, 'error', dur);
window.showWarning = (msg, dur = 4000) => showNotification(msg, 'warning', dur);
window.showInfo = (msg, dur = 3000) => showNotification(msg, 'info', dur);

// =================================
// PRICE FORMATTING & VALIDATION
// =================================

function formatPriceInput(input) {
    let value = input.value.replace(/[^\d]/g, '');
    if (value === '') {
        input.value = '';
        return;
    }
    let formatted = new Intl.NumberFormat('id-ID').format(parseInt(value));
    input.value = formatted;
    updatePricePreview(parseInt(value));
}

function validatePriceInput(input) {
    let value = input.value.replace(/[^\d]/g, '');
    let numericValue = parseInt(value);
    
    const existingError = input.parentNode.querySelector('.price-error');
    if (existingError) existingError.remove();
    
    if (isNaN(numericValue) || numericValue <= 0) {
        showPriceError(input, 'Harga harus berupa angka yang valid');
        return false;
    }
    if (numericValue < 100000) {
        showPriceError(input, 'Harga minimal Rp 100.000');
        return false;
    }
    if (numericValue > 50000000) {
        showPriceError(input, 'Harga maksimal Rp 50.000.000');
        return false;
    }
    
    input.style.borderColor = '#27ae60';
    updatePricePreview(numericValue);
    return true;
}

function showPriceError(input, message) {
    input.style.borderColor = '#e74c3c';
    const errorElement = document.createElement('div');
    errorElement.className = 'price-error';
    errorElement.style.cssText = 'color: #e74c3c; font-size: 0.8rem; margin-top: 5px;';
    errorElement.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
    input.parentNode.appendChild(errorElement);
}

function updatePricePreview(value) {
    const preview = document.getElementById('price-preview');
    if (preview) {
        if (value && value > 0) {
            const formatted = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(value);
            preview.innerHTML = `<strong>Preview:</strong> ${formatted}`;
            preview.style.color = '#27ae60';
        } else {
            preview.innerHTML = '<strong>Preview:</strong> Masukkan harga yang valid';
            preview.style.color = '#6c757d';
        }
    }
}

// =================================
// FORM STEP NAVIGATION
// =================================

let currentStep = 1;
const totalSteps = 4;

function nextStep() {
    console.log('▶️ Next step clicked, current:', currentStep);
    if (!validateCurrentStep()) return;
    
    if (currentStep < totalSteps) {
        hideStep(currentStep);
        currentStep++;
        showStep(currentStep);
        updateStepIndicator();
        document.querySelector('.enhanced-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function prevStep() {
    console.log('◀️ Previous step clicked, current:', currentStep);
    if (currentStep > 1) {
        hideStep(currentStep);
        currentStep--;
        showStep(currentStep);
        updateStepIndicator();
        document.querySelector('.enhanced-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function showStep(step) {
    const stepElement = document.getElementById(`step-${step}`);
    if (stepElement) {
        stepElement.classList.add('active');
        stepElement.style.display = 'block';
    }
}

function hideStep(step) {
    const stepElement = document.getElementById(`step-${step}`);
    if (stepElement) {
        stepElement.classList.remove('active');
        stepElement.style.display = 'none';
    }
}

function updateStepIndicator() {
    // Update step indicators and navigation buttons
    console.log('📊 Updating step indicator for step:', currentStep);
}

function validateCurrentStep() {
    console.log('✅ Validating step:', currentStep);
    return true; // Simplified validation
}

// Export step functions
window.nextStep = nextStep;
window.prevStep = prevStep;
window.showStep = showStep;
window.hideStep = hideStep;

// =================================
// HIGHLIGHTS BUILDER
// =================================

let highlightCounter = 0;

function addHighlightItem() {
    highlightCounter++;
    const container = document.getElementById('highlight-items');
    if (!container) return;
    
    const highlightDiv = document.createElement('div');
    highlightDiv.className = 'highlight-item';
    highlightDiv.setAttribute('data-id', highlightCounter);
    
    highlightDiv.innerHTML = `
        <div class="highlight-controls">
            <button type="button" class="btn-highlight-control" onclick="moveHighlight(${highlightCounter}, 'up')">
                <i class="fas fa-arrow-up"></i>
            </button>
            <button type="button" class="btn-highlight-control" onclick="moveHighlight(${highlightCounter}, 'down')">
                <i class="fas fa-arrow-down"></i>
            </button>
            <button type="button" class="btn-highlight-control delete" onclick="removeHighlight(${highlightCounter})">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="highlight-content">
            <div class="highlight-icon-picker">
                <select class="highlight-icon-select" onchange="updateHighlightPreview()">
                    <option value="fas fa-star">⭐ Bintang</option>
                    <option value="fas fa-heart">❤️ Hati</option>
                    <option value="fas fa-gem">💎 Permata</option>
                    <option value="fas fa-crown">👑 Mahkota</option>
                    <option value="fas fa-fire">🔥 Api</option>
                    <option value="fas fa-camera">📷 Kamera</option>
                    <option value="fas fa-mountain">🏔️ Gunung</option>
                </select>
            </div>
            <input type="text" class="highlight-input" placeholder="Contoh: Pemandangan sunrise yang menakjubkan" 
                   onkeyup="updateHighlightPreview()" maxlength="100">
        </div>
    `;
    
    container.appendChild(highlightDiv);
    updateHighlightPreview();
    
    setTimeout(() => {
        const newInput = highlightDiv.querySelector('.highlight-input');
        if (newInput) newInput.focus();
    }, 100);
}

function removeHighlight(id) {
    const item = document.querySelector(`[data-id="${id}"]`);
    if (item) {
        item.remove();
        updateHighlightPreview();
    }
}

function moveHighlight(id, direction) {
    const item = document.querySelector(`[data-id="${id}"]`);
    if (!item) return;
    
    const container = item.parentNode;
    const items = Array.from(container.children);
    const currentIndex = items.indexOf(item);
    
    if (direction === 'up' && currentIndex > 0) {
        container.insertBefore(item, items[currentIndex - 1]);
    } else if (direction === 'down' && currentIndex < items.length - 1) {
        container.insertBefore(items[currentIndex + 1], item);
    }
    
    updateHighlightPreview();
}

function updateHighlightPreview() {
    const preview = document.getElementById('highlight-preview');
    if (!preview) return;
    
    const items = document.querySelectorAll('#highlight-items .highlight-item');
    const highlights = [];
    
    items.forEach(item => {
        const icon = item.querySelector('.highlight-icon-select').value;
        const text = item.querySelector('.highlight-input').value.trim();
        if (text) {
            highlights.push({ icon, text });
        }
    });
    
    if (highlights.length === 0) {
        preview.innerHTML = '<p><em>Highlight akan muncul di sini...</em></p>';
        if (document.getElementById('highlights')) {
            document.getElementById('highlights').value = '';
        }
        return;
    }
    
    let previewHTML = '<div class="highlights-preview-grid">';
    highlights.forEach(highlight => {
        previewHTML += `
            <div class="highlight-preview-item">
                <i class="${highlight.icon}"></i>
                <span>${highlight.text}</span>
            </div>
        `;
    });
    previewHTML += '</div>';
    
    preview.innerHTML = previewHTML;
    
    // Update hidden input
    const highlightText = highlights.map(h => h.text).join('\n');
    if (document.getElementById('highlights')) {
        document.getElementById('highlights').value = highlightText;
    }
}

function addHighlightSample() {
    const samples = [
        { icon: 'fas fa-star', text: 'Pemandangan sunrise yang menakjubkan di Borobudur' },
        { icon: 'fas fa-crown', text: 'Kunjungan eksklusif ke Keraton Yogyakarta' },
        { icon: 'fas fa-camera', text: 'Spot foto Instagram-able di Taman Sari' },
        { icon: 'fas fa-heart', text: 'Pengalaman kuliner otentik Gudeg Jogja' },
        { icon: 'fas fa-gem', text: 'Wisata budaya di Candi Prambanan yang megah' }
    ];
    
    const container = document.getElementById('highlight-items');
    if (container) {
        container.innerHTML = '';
        highlightCounter = 0;
        
        samples.forEach(sample => {
            addHighlightItem();
            const lastItem = document.querySelector('#highlight-items .highlight-item:last-child');
            if (lastItem) {
                lastItem.querySelector('.highlight-icon-select').value = sample.icon;
                lastItem.querySelector('.highlight-input').value = sample.text;
            }
        });
        
        updateHighlightPreview();
        showSuccess('Sample highlights berhasil dimuat!');
    }
}

function clearHighlights() {
    if (confirm('Yakin ingin menghapus semua highlights?')) {
        const container = document.getElementById('highlight-items');
        if (container) {
            container.innerHTML = '';
            highlightCounter = 0;
            updateHighlightPreview();
            showSuccess('Highlights berhasil dihapus');
        }
    }
}

// =================================
// INCLUSIONS/EXCLUSIONS BUILDER
// =================================

function updateInclusionPreview() {
    const preview = document.getElementById('inclusion-preview');
    const textInput = document.getElementById('inclusions-text');
    
    if (!preview || !textInput) return;
    
    const inclusionText = textInput.value.trim();
    
    if (!inclusionText) {
        preview.innerHTML = '<p><em>Yang termasuk akan muncul di sini...</em></p>';
        return;
    }
    
    const lines = inclusionText.split('\n').filter(line => line.trim());
    
    let previewHTML = '<div class="inclusion-preview-list">';
    lines.forEach(line => {
        const trimmedLine = line.trim();
        if (trimmedLine) {
            previewHTML += `
                <div class="inclusion-preview-item">
                    <i class="fas fa-check"></i>
                    <span>${trimmedLine}</span>
                </div>
            `;
        }
    });
    previewHTML += '</div>';
    
    preview.innerHTML = previewHTML;
}

function addInclusionSample() {
    const sampleInclusions = `🏨 Akomodasi hotel bintang 3-4 dengan sarapan
🚐 Transportasi AC selama tour (antar-jemput hotel)
🎫 Tiket masuk semua objek wisata sesuai itinerary
🍽️ Makan siang dan makan malam (5 kali makan)
👨‍🏫 Guide profesional berbahasa Indonesia/Inggris
💧 Air mineral selama perjalanan
📸 Dokumentasi foto grup di setiap destinasi
🎁 Souvenir khas Yogyakarta`;
    
    const textInput = document.getElementById('inclusions-text');
    if (textInput) {
        textInput.value = sampleInclusions;
        updateInclusionPreview();
        showSuccess('Sample inclusions berhasil dimuat!');
    }
}

function clearInclusions() {
    if (confirm('Yakin ingin menghapus semua inclusions?')) {
        const textInput = document.getElementById('inclusions-text');
        if (textInput) {
            textInput.value = '';
            updateInclusionPreview();
            showSuccess('Inclusions berhasil dihapus');
        }
    }
}

function updateExclusionPreview() {
    const preview = document.getElementById('exclusion-preview');
    const textInput = document.getElementById('exclusions-text');
    
    if (!preview || !textInput) return;
    
    const exclusionText = textInput.value.trim();
    
    if (!exclusionText) {
        preview.innerHTML = '<p><em>Yang tidak termasuk akan muncul di sini...</em></p>';
        return;
    }
    
    const lines = exclusionText.split('\n').filter(line => line.trim());
    
    let previewHTML = '<div class="exclusion-preview-list">';
    lines.forEach(line => {
        const trimmedLine = line.trim();
        if (trimmedLine) {
            previewHTML += `
                <div class="exclusion-preview-item">
                    <i class="fas fa-times"></i>
                    <span>${trimmedLine}</span>
                </div>
            `;
        }
    });
    previewHTML += '</div>';
    
    preview.innerHTML = previewHTML;
}

function addExclusionSample() {
    const sampleExclusions = `✈️ Tiket pesawat/kereta ke Yogyakarta
🍻 Minuman beralkohol dan soft drink
🛍️ Belanja pribadi dan oleh-oleh tambahan
📱 Telepon, internet, dan keperluan pribadi
💆 Spa, massage, dan perawatan tambahan
🎮 Aktivitas tambahan di luar itinerary
💸 Tips untuk guide dan driver (opsional)`;
    
    const textInput = document.getElementById('exclusions-text');
    if (textInput) {
        textInput.value = sampleExclusions;
        updateExclusionPreview();
        showSuccess('Sample exclusions berhasil dimuat!');
    }
}

function clearExclusions() {
    if (confirm('Yakin ingin menghapus semua exclusions?')) {
        const textInput = document.getElementById('exclusions-text');
        if (textInput) {
            textInput.value = '';
            updateExclusionPreview();
            showSuccess('Exclusions berhasil dihapus');
        }
    }
}

// =================================
// ITINERARY BUILDER
// =================================

let itineraryData = {};
let dayCounter = 0;

function initializeItinerary() {
    console.log('📅 Initializing itinerary builder...');
    const itineraryContainer = document.getElementById('itinerary-days');
    
    if (!itineraryContainer) {
        console.warn('⚠️ Itinerary container not found');
        return;
    }
    
    itineraryContainer.innerHTML = '';
    itineraryData = {};
    dayCounter = 0;
    
    // Add first day by default
    addNewDay();
}

function addNewDay() {
    dayCounter++;
    const dayId = `day-${dayCounter}`;
    const dayNumber = Object.keys(itineraryData).length + 1;
    
    const dayData = {
        id: dayId,
        day_number: dayNumber,
        title: `Hari ${dayNumber}`,
        activities: []
    };
    
    itineraryData[dayId] = dayData;
    
    const itineraryContainer = document.getElementById('itinerary-days');
    if (!itineraryContainer) return;
    
    const dayElement = createDayElement(dayId, dayData);
    itineraryContainer.appendChild(dayElement);
    
    // Add first activity by default
    addActivity(dayId);
    
    showSuccess(`Hari ${dayNumber} berhasil ditambahkan`);
}

function createDayElement(dayId, dayData) {
    const dayDiv = document.createElement('div');
    dayDiv.className = 'itinerary-day';
    dayDiv.setAttribute('data-day-id', dayId);
    
    dayDiv.innerHTML = `
        <div class="day-header-editor">
            <div class="day-title-section">
                <div class="day-badge">Hari ${dayData.day_number}</div>
                <input type="text" class="day-title-input" 
                       value="${dayData.title}" 
                       onchange="updateDayTitle('${dayId}', this.value)"
                       placeholder="Contoh: Hari 1 - Tiba di Yogyakarta">
            </div>
            <div class="day-controls">
                <button type="button" class="btn-day-control" onclick="duplicateDay('${dayId}')" title="Duplikat">
                    <i class="fas fa-copy"></i>
                </button>
                <button type="button" class="btn-day-control delete" onclick="removeDay('${dayId}')" title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="day-activities" id="activities-${dayId}">
            <div class="activities-container"></div>
            <button type="button" class="btn-add-activity" onclick="addActivity('${dayId}')">
                <i class="fas fa-plus"></i> Tambah Aktivitas
            </button>
        </div>
    `;
    
    return dayDiv;
}

function updateDayTitle(dayId, newTitle) {
    if (!itineraryData[dayId]) return;
    itineraryData[dayId].title = newTitle;
    updateItineraryPreview();
}

function addActivity(dayId) {
    if (!itineraryData[dayId]) return;
    
    const activityId = 'activity_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    const newActivity = {
        id: activityId,
        time: '',
        activity: '',
        location: ''
    };
    
    itineraryData[dayId].activities.push(newActivity);
    renderDayActivities(dayId);
    updateItineraryPreview();
}

function renderDayActivities(dayId) {
    const activitiesContainer = document.querySelector(`#activities-${dayId} .activities-container`);
    if (!activitiesContainer || !itineraryData[dayId]) return;
    
    activitiesContainer.innerHTML = '';
    
    itineraryData[dayId].activities.forEach((activity, index) => {
        const activityElement = createActivityElement(dayId, activity, index);
        activitiesContainer.appendChild(activityElement);
    });
}

function createActivityElement(dayId, activity, index) {
    const activityDiv = document.createElement('div');
    activityDiv.className = 'activity-item';
    activityDiv.setAttribute('data-activity-id', activity.id);
    
    activityDiv.innerHTML = `
        <div class="activity-header">
            <input type="time" 
                   class="time-input" 
                   value="${activity.time || ''}" 
                   onchange="updateActivityTime('${dayId}', ${index}, this.value)">
            <span class="activity-number">#${index + 1}</span>
            <div class="activity-controls">
                <button type="button" class="btn-activity-control" onclick="removeActivity('${dayId}', ${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="activity-details">
            <div class="activity-field">
                <label>Aktivitas:</label>
                <input type="text" class="activity-name" 
                       placeholder="Contoh: Kunjungan ke Candi Borobudur"
                       value="${activity.activity || ''}"
                       onchange="updateActivityField('${dayId}', ${index}, 'activity', this.value)">
            </div>
            <div class="activity-field">
                <label>Lokasi (opsional):</label>
                <input type="text" class="activity-location" 
                       placeholder="Contoh: Magelang, Jawa Tengah"
                       value="${activity.location || ''}"
                       onchange="updateActivityField('${dayId}', ${index}, 'location', this.value)">
            </div>
        </div>
    `;
    
    return activityDiv;
}

function updateActivityField(dayId, activityIndex, field, newValue) {
    if (!itineraryData[dayId] || !itineraryData[dayId].activities[activityIndex]) return;
    itineraryData[dayId].activities[activityIndex][field] = newValue;
    updateItineraryPreview();
}

function updateActivityTime(dayId, activityIndex, newTime) {
    if (!itineraryData[dayId] || !itineraryData[dayId].activities[activityIndex]) return;
    itineraryData[dayId].activities[activityIndex].time = newTime;
    updateItineraryPreview();
}

function removeActivity(dayId, activityIndex) {
    if (!itineraryData[dayId] || !itineraryData[dayId].activities[activityIndex]) return;
    
    if (itineraryData[dayId].activities.length <= 1) {
        showError('Minimal harus ada 1 aktivitas per hari');
        return;
    }
    
    if (confirm('Yakin ingin menghapus aktivitas ini?')) {
        itineraryData[dayId].activities.splice(activityIndex, 1);
        renderDayActivities(dayId);
        updateItineraryPreview();
        showSuccess('Aktivitas berhasil dihapus');
    }
}

function duplicateDay(dayId) {
    if (!itineraryData[dayId]) return;
    
    const originalDay = itineraryData[dayId];
    addNewDay();
    
    const dayKeys = Object.keys(itineraryData);
    const newDayId = dayKeys[dayKeys.length - 1];
    
    if (itineraryData[newDayId]) {
        itineraryData[newDayId].title = originalDay.title + ' (Copy)';
        itineraryData[newDayId].activities = originalDay.activities.map(activity => ({
            ...activity,
            id: 'activity_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9)
        }));
        
        const newDayElement = document.querySelector(`[data-day-id="${newDayId}"]`);
        if (newDayElement) {
            newDayElement.querySelector('.day-title-input').value = itineraryData[newDayId].title;
            renderDayActivities(newDayId);
        }
        
        updateItineraryPreview();
        showSuccess('Hari berhasil diduplikat');
    }
}

function removeDay(dayId) {
    if (!itineraryData[dayId]) return;
    
    const dayCount = Object.keys(itineraryData).length;
    if (dayCount <= 1) {
        showError('Minimal harus ada 1 hari dalam itinerary');
        return;
    }
    
    if (confirm('Yakin ingin menghapus hari ini?')) {
        delete itineraryData[dayId];
        
        const dayElement = document.querySelector(`[data-day-id="${dayId}"]`);
        if (dayElement) {
            dayElement.remove();
        }
        
        updateItineraryPreview();
        showSuccess('Hari berhasil dihapus');
    }
}

function updateItineraryPreview() {
    const preview = document.getElementById('itinerary-preview');
    const hiddenInput = document.getElementById('itinerary');
    
    if (!preview) return;
    
    if (Object.keys(itineraryData).length === 0) {
        preview.innerHTML = '<p><em>Itinerary akan muncul di sini...</em></p>';
        if (hiddenInput) hiddenInput.value = '';
        return;
    }
    
    const formattedData = {};
    Object.values(itineraryData).forEach(day => {
        const dayKey = `day_${day.day_number}`;
        formattedData[dayKey] = {
            title: day.title,
            activities: day.activities.filter(activity => activity.activity.trim()).map(activity => ({
                time: activity.time || '',
                activity: activity.activity,
                location: activity.location || ''
            }))
        };
    });
    
    let previewHTML = '';
    Object.entries(formattedData).forEach(([dayKey, day]) => {
        previewHTML += `
            <div class="preview-day">
                <h4><i class="fas fa-calendar-day"></i> ${day.title}</h4>
                <div class="day-activities-preview">
        `;
        
        if (day.activities.length > 0) {
            day.activities.forEach(activity => {
                previewHTML += `
                    <div class="activity-preview-item">
                        <div class="activity-time">
                            ${activity.time ? `<i class="fas fa-clock"></i> ${activity.time}` : '<i class="fas fa-clock"></i> --:--'}
                        </div>
                        <div class="activity-content">
                            <div class="activity-name">${activity.activity}</div>
                            ${activity.location ? `<div class="activity-location"><i class="fas fa-map-marker-alt"></i> ${activity.location}</div>` : ''}
                        </div>
                    </div>
                `;
            });
        } else {
            previewHTML += '<p class="no-activities"><em>Belum ada aktivitas</em></p>';
        }
        
        previewHTML += `</div></div>`;
    });
    
    preview.innerHTML = previewHTML;
    
    if (hiddenInput) {
        hiddenInput.value = JSON.stringify(formattedData);
    }
}

function loadSampleItinerary() {
    const sampleData = {
        'day-1': {
            id: 'day-1',
            day_number: 1,
            title: 'Hari 1 - Tiba di Yogyakarta',
            activities: [
                { id: 'act1', time: '08:00', activity: 'Penjemputan di bandara/stasiun', location: 'Bandara Adisutcipto' },
                { id: 'act2', time: '09:30', activity: 'Check-in hotel dan istirahat', location: 'Hotel' },
                { id: 'act3', time: '10:30', activity: 'Kunjungan ke Keraton Yogyakarta', location: 'Keraton Yogyakarta' },
                { id: 'act4', time: '12:00', activity: 'Makan siang kuliner lokal', location: 'Restoran lokal' }
            ]
        },
        'day-2': {
            id: 'day-2',
            day_number: 2,
            title: 'Hari 2 - Wisata Candi',
            activities: [
                { id: 'act5', time: '05:00', activity: 'Persiapan menuju Borobudur untuk sunrise', location: 'Hotel' },
                { id: 'act6', time: '06:30', activity: 'Menikmati sunrise di Candi Borobudur', location: 'Candi Borobudur' },
                { id: 'act7', time: '10:00', activity: 'Kunjungan ke Candi Prambanan', location: 'Candi Prambanan' }
            ]
        }
    };
    
    itineraryData = sampleData;
    dayCounter = 2;
    
    const container = document.getElementById('itinerary-days');
    if (container) {
        container.innerHTML = '';
        
        Object.values(sampleData).forEach(day => {
            const dayElement = createDayElement(day.id, day);
            container.appendChild(dayElement);
            renderDayActivities(day.id);
        });
    }
    
    updateItineraryPreview();
    showSuccess('Sample itinerary berhasil dimuat!');
}

function clearItinerary() {
    if (confirm('Yakin ingin menghapus semua itinerary?')) {
        itineraryData = {};
        dayCounter = 0;
        
        const container = document.getElementById('itinerary-days');
        if (container) {
            container.innerHTML = '';
        }
        
        updateItineraryPreview();
        showSuccess('Itinerary berhasil dihapus');
        
        setTimeout(() => {
            addNewDay();
        }, 100);
    }
}

// Export itinerary functions
window.addNewDay = addNewDay;
window.initializeItinerary = initializeItinerary;
window.loadSampleItinerary = loadSampleItinerary;
window.clearItinerary = clearItinerary;
window.updateDayTitle = updateDayTitle;
window.addActivity = addActivity;
window.updateActivityField = updateActivityField;
window.updateActivityTime = updateActivityTime;
window.removeActivity = removeActivity;
window.duplicateDay = duplicateDay;
window.removeDay = removeDay;

// =================================
// FILE UPLOAD HANDLING
// =================================

function initializeFileUpload() {
    const fileInput = document.getElementById('fotos');
    const preview = document.getElementById('file-preview');
    
    if (!fileInput || !preview) return;
    
    fileInput.addEventListener('change', function(e) {
        handleFileSelection(e.target.files);
    });
    
    console.log('✅ File upload initialized');
}

function handleFileSelection(files) {
    const preview = document.getElementById('file-preview');
    if (!preview) return;
    
    preview.innerHTML = '';
    
    if (files.length === 0) return;
    
    if (files.length < 3) {
        showError('Minimal 3 foto harus diupload');
        return;
    }
    
    if (files.length > 6) {
        showError('Maksimal 6 foto dapat diupload');
        return;
    }
    
    Array.from(files).forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewItem = document.createElement('div');
                previewItem.className = 'file-preview-item';
                previewItem.innerHTML = `
                    <img src="${e.target.result}" alt="Preview ${index + 1}">
                    <div class="file-name">${file.name}</div>
                    <div class="file-size">${formatFileSize(file.size)}</div>
                `;
                preview.appendChild(previewItem);
            };
            reader.readAsDataURL(file);
        }
    });
    
    showSuccess(`${files.length} foto berhasil dipilih`);
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Export file functions
window.initializeFileUpload = initializeFileUpload;
window.handleFileSelection = handleFileSelection;
window.formatPriceInput = formatPriceInput;
window.validatePriceInput = validatePriceInput;

// =================================
// MOBILE MENU FUNCTIONS
// =================================

function toggleMobileMenu() {
    const sidebar = document.getElementById('mobileSidebar');
    const overlay = document.getElementById('mobileOverlay');
    
    if (sidebar && overlay) {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
}

function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
        toggleMobileMenu(); // Close mobile menu after clicking
    }
}

// Export mobile functions
window.toggleMobileMenu = toggleMobileMenu;
window.scrollToSection = scrollToSection;

// =================================
// INITIALIZATION
// =================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 DOM loaded, initializing all systems...');
    
    // Initialize form enhancement
    setTimeout(() => {
        // Show only first step
        for (let i = 2; i <= totalSteps; i++) {
            hideStep(i);
        }
        
        // Add price input listeners
        const priceInput = document.getElementById('price');
        if (priceInput) {
            priceInput.addEventListener('input', function() {
                formatPriceInput(this);
            });
            priceInput.addEventListener('blur', function() {
                validatePriceInput(this);
            });
        }
    }, 100);
    
    // Initialize file upload
    setTimeout(() => {
        initializeFileUpload();
    }, 200);
    
    // Initialize itinerary
    setTimeout(() => {
        initializeItinerary();
    }, 300);
    
    // Add default highlight item
    setTimeout(() => {
        if (document.getElementById('highlight-items') && document.getElementById('highlight-items').children.length === 0) {
            addHighlightItem();
        }
    }, 500);
    
    // Add event listeners for preview updates
    setTimeout(() => {
        const inclusionsText = document.getElementById('inclusions-text');
        if (inclusionsText) {
            inclusionsText.addEventListener('input', function() {
                setTimeout(updateInclusionPreview, 300);
            });
        }
        
        const exclusionsText = document.getElementById('exclusions-text');
        if (exclusionsText) {
            exclusionsText.addEventListener('input', function() {
                setTimeout(updateExclusionPreview, 300);
            });
        }
    }, 600);
    
    // Update previews on page load
    setTimeout(() => {
        updateHighlightPreview();
        updateInclusionPreview();
        updateExclusionPreview();
    }, 800);
    
    console.log('✅ All systems initialized successfully');
});

console.log('🎉 Enhanced admin script loaded successfully!');
</script>

<!-- Tambahkan tombol debug di bawah header -->
<div style="position: fixed; top: 100px; right: 20px; z-index: 999;">
    <button onclick="window.debugGallery()" style="background: #e74c3c; color: white; padding: 8px 12px; border: none; border-radius: 4px; font-size: 0.8rem; cursor: pointer;">
        🔍 Debug Gallery
    </button>
</div>
</body>
</html>
