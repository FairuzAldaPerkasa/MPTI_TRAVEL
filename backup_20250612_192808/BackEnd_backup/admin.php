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

// Handle messages
$message = '';
if (isset($_GET['success'])) {
    $message = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Paket berhasil ditambahkan!</div>';
} elseif (isset($_GET['error'])) {
    $errorMessages = [
        'invalid_price' => 'Harga tidak valid!',
        'price_too_low' => 'Harga minimal Rp 100.000!',
        'price_too_high' => 'Harga maksimal Rp 50.000.000!',
        'empty_fields' => 'Semua field harus diisi!',
        'no_files' => 'Minimal harus upload 1 foto!',
        'invalid_photo_count' => 'Upload 3-6 foto saja!',
        'invalid_file_type' => 'Hanya file JPG, JPEG, PNG yang diperbolehkan!',
        'file_too_large' => 'Ukuran file maksimal 5MB!',
        'upload_failed' => 'Gagal upload file!',
        'database_error' => 'Terjadi kesalahan database!',
        'input_too_long' => 'Input terlalu panjang!'
    ];
    $errorKey = $_GET['error'];
    $errorMsg = $errorMessages[$errorKey] ?? 'Terjadi kesalahan tidak dikenal!';
    $message = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ' . $errorMsg . '</div>';
} elseif (isset($_GET['deleted'])) {
    $message = '<div class="alert alert-success"><i class="fas fa-trash"></i> Paket berhasil dihapus!</div>';
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
    header("Location: admin.php?deleted=1");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Vacationland</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin-clean.css">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-content">
            <div class="header-left">
                <img src="../Asset/logo/logompti.png" alt="Logo" class="logo">
                <h1>Vacationland Admin</h1>
            </div>
            
            <div class="header-right">
                <div class="admin-welcome">
                    <i class="fas fa-user-circle"></i>
                    <span>Hi, <?= $_SESSION['admin_name'] ?? 'Admin' ?></span>
                </div>
                <a href="loginadmin.php?logout=1" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="admin-nav">
        <div class="nav-content">
            <a href="#dashboard" class="nav-link active">
                <i class="fas fa-chart-pie"></i>
                Dashboard
            </a>
            <a href="#add-package" class="nav-link">
                <i class="fas fa-plus-circle"></i>
                Tambah Paket
            </a>
            <a href="#packages-list" class="nav-link">
                <i class="fas fa-list"></i>
                Daftar Paket
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
                    </div>
                </div>
            </div>
        </section>

        <!-- Add Package Section -->
        <section id="add-package" class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-plus-circle"></i> Tambah Paket Wisata</h2>
                <p>Buat paket wisata baru untuk ditampilkan di website</p>
            </div>
            
            <div class="form-container">
                <form action="tambah.php" method="POST" enctype="multipart/form-data" class="package-form">
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
                        echo '<img src="uploads/' . htmlspecialchars($firstPhoto) . '" alt="' . htmlspecialchars($row['nama']) . '" onerror="this.src=\'../Asset/img/default.jpg\'">';
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
                    echo '<button onclick="showSection(\'add-package\')" class="btn-primary">';
                    echo '<i class="fas fa-plus"></i> Tambah Paket Sekarang';
                    echo '</button>';
                    echo '</div>';
                }
                ?>
            </div>
        </section>
    </main>

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
        </div>
    </div>

    <script src="admin-clean.js"></script>
</body>
</html>
