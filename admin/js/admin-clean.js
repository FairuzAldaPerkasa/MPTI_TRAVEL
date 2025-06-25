// GALLERY UPLOAD TEST - Version 2.0 - 20250612
console.log('🔧 Admin Clean JS Loaded - Gallery Fixed Version 2.0');

// Admin Panel JavaScript - Clean Version
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Admin panel loaded');
    
    // Initialize navigation
    initNavigation();
    
    // Initialize form handlers
    initFormHandlers();
    
    // Initialize gallery
    initGallery();
    
    // Initialize dynamic forms
    initDynamicForms();
    
    // Initialize website links
    initWebsiteLinks();
});

// Global counters
let dayCounter = 1;
let activityCounters = [0]; // Track activities per day

// Define initSortable BEFORE initDynamicForms
function initSortable() {
    console.log('Sortable functionality would be initialized here.');
}

// Initialize dynamic forms
function initDynamicForms() {
    // Initialize sortable for better UX
    initSortable();
    
    // Initialize highlight functions
    window.addHighlight = addHighlight;
    
    // Initialize itinerary functions
    window.addDay = addDay;
    window.removeDay = removeDay;
    window.addActivity = addActivity;
    window.removeActivity = removeActivity;
    
    // Initialize inclusion/exclusion functions
    window.addInclusion = addInclusion;
    window.addExclusion = addExclusion;
}

// Navigation System
function initNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('.content-section');
      // Handle navigation clicks
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Skip if it's an external link (has target="_blank")
            if (this.getAttribute('target') === '_blank') {
                return; // Let the browser handle the external link
            }
            
            // Skip if href doesn't start with #
            const href = this.getAttribute('href');
            if (!href || !href.startsWith('#')) {
                return;
            }
            
            e.preventDefault();
            const targetId = href.substring(1);
            showSection(targetId);
        });
    });
    
    // Show section based on hash
    const hash = window.location.hash.substring(1);
    if (hash) {
        showSection(hash);
    } else {
        showSection('dashboard');
    }
}

function showSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Remove active class from nav links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    
    // Show target section
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
        targetSection.classList.add('active');
    }
    
    // Add active class to corresponding nav link
    const targetLink = document.querySelector(`[href="#${sectionId}"]`);
    if (targetLink) {
        targetLink.classList.add('active');
    }
    
    // Update URL hash
    window.history.replaceState(null, null, `#${sectionId}`);
}

// Form Handlers
function initFormHandlers() {
    // Price formatting
    const priceInput = document.getElementById('price');
    if (priceInput) {
        priceInput.addEventListener('input', function() {
            formatPrice(this);
        });
    }
    
    // File upload preview
    const fileInput = document.getElementById('fotos');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            showFilePreview(this);
        });
    }
    
    // Form validation
    const packageForm = document.querySelector('.package-form');
    if (packageForm) {
        packageForm.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    }
}

function formatPrice(input) {
    // Remove non-digits
    let value = input.value.replace(/[^0-9]/g, '');
    
    if (value) {
        // Format with thousand separators
        value = new Intl.NumberFormat('id-ID').format(value);
        input.value = value;
    }
}

function showFilePreview(input) {
    const preview = document.getElementById('file-preview');
    if (!preview) return;
    
    preview.innerHTML = '';
    
    if (input.files && input.files.length > 0) {
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'file-preview-item';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview ${index + 1}">
                    <div class="file-name">${file.name}</div>
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
}

function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('field-error');
            isValid = false;
        } else {
            field.classList.remove('field-error');
        }
    });
    
    // Validate file count
    const fileInput = form.querySelector('input[type="file"]');
    if (fileInput && fileInput.files) {
        const fileCount = fileInput.files.length;
        if (fileCount < 3 || fileCount > 6) {
            showNotification('Upload 3-6 foto saja!', 'error');
            isValid = false;
        }
    }
    
    // Validate price
    const priceInput = form.querySelector('#price');
    if (priceInput) {
        const price = parseInt(priceInput.value.replace(/[^0-9]/g, ''));
        if (price < 100000 || price > 50000000) {
            showNotification('Harga harus antara Rp 100.000 - Rp 50.000.000', 'error');
            isValid = false;
        }
    }
    
    return isValid;
}

// Gallery Management
function initGallery() {
    const galleryForm = document.getElementById('galleryForm');
    if (galleryForm) {
        galleryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            uploadGalleryPhotos(this);
        });
    }
    
    const galleryFiles = document.getElementById('galleryFiles');
    if (galleryFiles) {
        galleryFiles.addEventListener('change', function() {
            generateCaptionInputs(this.files);
        });
    }
}

function openGallery(packageId, packageName) {
    const modal = document.getElementById('galleryModal');
    const packageNameEl = document.getElementById('packageName');
    const packageIdEl = document.getElementById('packageId');
    
    if (packageNameEl) packageNameEl.textContent = packageName;
    if (packageIdEl) packageIdEl.value = packageId;
    
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    loadExistingPhotos(packageId);
}

function closeGallery() {
    const modal = document.getElementById('galleryModal');
    modal.classList.remove('show');
    document.body.style.overflow = '';
}

function generateCaptionInputs(files) {
    const container = document.getElementById('galleryCaptions');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (files.length > 0) {
        const label = document.createElement('label');
        label.textContent = 'Caption untuk foto:';
        container.appendChild(label);
        
        Array.from(files).forEach((file, index) => {
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'captions[]';
            input.placeholder = `Caption untuk ${file.name}`;
            input.style.marginBottom = '0.5rem';
            input.style.width = '100%';
            input.style.padding = '0.5rem';
            input.style.border = '1px solid var(--border)';
            input.style.borderRadius = 'var(--radius)';
            container.appendChild(input);
        });
    }
}

function uploadGalleryPhotos(form) {
    const formData = new FormData(form);
    
    showNotification('Mengupload foto...', 'info');
    
    // Use relative path from BackEnd directory
    fetch('../BackEnd/upload_additional_photos.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('📡 Upload response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('📤 Upload response data:', data);
        if (data.success) {
            showNotification(data.message || 'Foto berhasil diupload!', 'success');
            form.reset();
            document.getElementById('galleryCaptions').innerHTML = '';
            loadExistingPhotos(formData.get('package_id'));
        } else {
            showNotification(data.error || data.message || 'Gagal mengupload foto', 'error');
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        showNotification('Terjadi kesalahan saat mengupload', 'error');
    });
}

function loadExistingPhotos(packageId) {
    const container = document.getElementById('existingPhotos');
    if (!container) return;
    
    container.innerHTML = '<p>Memuat foto...</p>';
    
    // Use relative path from BackEnd directory
    fetch(`../BackEnd/get_gallery_photos.php?package_id=${packageId}`)
        .then(response => {
            console.log('📡 Load photos response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('📸 Gallery photos response:', data);
            
            // Check if data is an array or if it has error property
            if (data.error) {
                throw new Error(data.error);
            }
            
            const photos = Array.isArray(data) ? data : [];
              if (photos.length > 0) {
                let html = '';
                photos.forEach((photo, index) => {
                    const caption = photo.caption || 'Tanpa caption';
                    const photoUrl = `../BackEnd/uploads/gallery/${photo.photo_filename}`;
                    const safeCaption = caption.replace(/'/g, "&#39;").replace(/"/g, "&quot;");
                      html += `
                        <div class="photo-item" data-photo-id="${photo.id}">
                            <div class="photo-preview" onclick="previewPhoto('${photoUrl}', '${safeCaption}')">
                                <img src="${photoUrl}" 
                                     alt="${safeCaption}" 
                                     loading="lazy"
                                     onerror="this.src='../assets/images/default.jpg'; this.classList.add('error-image');">
                                <div class="photo-overlay">
                                    <button class="overlay-btn preview-btn" onclick="previewPhoto('${photoUrl}', '${safeCaption}')" title="Preview">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="photo-info">
                                <div class="photo-caption" title="${safeCaption}">
                                    ${caption.length > 30 ? caption.substring(0, 30) + '...' : caption}
                                </div>
                                <div class="photo-meta">
                                    Order: ${photo.photo_order || index + 1} | 
                                    ${photo.uploaded_at ? new Date(photo.uploaded_at).toLocaleDateString('id-ID') : 'Unknown'}
                                </div>
                                <div class="photo-actions">
                                    <button class="action-btn edit-btn" onclick="editPhotoCaption(${photo.id}, '${safeCaption}')" title="Edit Caption">
                                        <i class="fas fa-edit"></i>
                                        <span>Edit</span>
                                    </button>
                                    <button class="action-btn delete-btn" onclick="deleteGalleryPhoto(${photo.id})" title="Hapus Foto">
                                        <i class="fas fa-trash"></i>
                                        <span>Hapus</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                container.innerHTML = html;
                
                // Add masonry layout initialization
                setTimeout(() => {
                    initGalleryLayout();
                }, 100);
            } else {
                container.innerHTML = '<p style="text-align: center; color: var(--gray); padding: 2rem;">Belum ada foto galeri.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading photos:', error);
            container.innerHTML = '<p style="color: var(--danger); text-align: center; padding: 2rem;">Gagal memuat foto galeri.</p>';
        });
}

// Package Management
function editPackage(id) {
    showNotification('Fitur edit sedang dalam pengembangan', 'info');
}

function deletePackage(id, name) {
    const modal = document.getElementById('deleteModal');
    const packageNameEl = document.getElementById('deletePackageName');
    const packageIdInput = document.getElementById('deletePackageId');

    if (modal && packageNameEl && packageIdInput) {
        packageNameEl.textContent = name;
        packageIdInput.value = id;
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    } else {
        // Fallback to old method if modal is not found
        if (confirm(`Yakin ingin menghapus paket "${name}"?\n\nSemua foto dan data akan dihapus permanen!`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'admin.php';

            const hiddenField = document.createElement('input');
            hiddenField.type = 'hidden';
            hiddenField.name = 'hapus';
            hiddenField.value = id;

            form.appendChild(hiddenField);
            document.body.appendChild(form);
            form.submit();
        }
    }
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

function deleteGalleryPhoto(photoId) {
    if (confirm('Yakin ingin menghapus foto ini?')) {
        const packageId = document.getElementById('packageId').value;
        
        // Show loading state
        const photoItem = document.querySelector(`[data-photo-id="${photoId}"]`);
        if (photoItem) {
            photoItem.style.opacity = '0.5';
            photoItem.style.pointerEvents = 'none';
        }
        
        fetch('../BackEnd/delete_gallery_photo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `photo_id=${photoId}&package_id=${packageId}`
        })
        .then(response => {
            console.log('🗑️ Delete response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('🗑️ Delete response data:', data);
            if (data.success) {
                showNotification('Foto berhasil dihapus', 'success');
                
                // Animate removal
                if (photoItem) {
                    photoItem.style.transform = 'scale(0.8)';
                    photoItem.style.opacity = '0';
                    setTimeout(() => {
                        refreshGalleryDisplay(packageId);
                    }, 300);
                } else {
                    refreshGalleryDisplay(packageId);
                }
            } else {
                if (photoItem) {
                    photoItem.style.opacity = '1';
                    photoItem.style.pointerEvents = 'auto';
                }
                showNotification(data.message || 'Gagal menghapus foto', 'error');
            }        })
        .catch(error => {
            console.error('Delete error:', error);
            if (photoItem) {
                photoItem.style.opacity = '1';
                photoItem.style.pointerEvents = 'auto';
            }
            showNotification('Terjadi kesalahan saat menghapus', 'error');
        });
    }
}

function editPhotoCaption(photoId, currentCaption) {
    const newCaption = prompt('Edit caption foto:', currentCaption);
    if (newCaption !== null) {
        fetch('../BackEnd/update_photo_caption.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                photo_id: photoId,
                caption: newCaption
            })
        })
        .then(response => {
            console.log('✏️ Caption update response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('✏️ Caption update response data:', data);
            if (data.success) {
                showNotification('Caption berhasil diupdate', 'success');
                const packageId = document.getElementById('packageId').value;
                loadExistingPhotos(packageId);
            } else {
                showNotification(data.message || 'Gagal mengupdate caption', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan', 'error');
        });
    }
}

// Highlight Functions
function addHighlight(value = '') {
    const container = document.getElementById('highlightsContainer');
    const newItem = createListItem('highlights[]', 'Masukkan highlight menarik...', value);
    container.appendChild(newItem);
    newItem.classList.add('list-item-enter');
}

// Itinerary Functions
function addDay(title = '') {
    dayCounter++;
    const container = document.getElementById('itineraryContainer');
    const newDay = createDayElement(dayCounter, title);
    container.appendChild(newDay);
    activityCounters.push(0);
    newDay.classList.add('list-item-enter');
}

function removeDay(button) {
    const dayElement = button.closest('.itinerary-day');
    if (document.querySelectorAll('.itinerary-day').length > 1) {
        dayElement.classList.add('list-item-exit');
        setTimeout(() => dayElement.remove(), 300);
    } else {
        showNotification('Minimal harus ada 1 hari', 'warning');
    }
}

function addActivity(button, time = '', activity = '') {
    const dayElement = button.closest('.itinerary-day');
    const dayIndex = Array.from(dayElement.parentNode.children).indexOf(dayElement);
    const activitiesList = dayElement.querySelector('.activities-list');
    
    const newActivity = createActivityElement(dayIndex, activityCounters[dayIndex], time, activity);
    activitiesList.appendChild(newActivity);
    activityCounters[dayIndex]++;
    newActivity.classList.add('list-item-enter');
}

function removeActivity(button) {
    const activityElement = button.closest('.activity-item');
    const activitiesList = activityElement.parentNode;
    
    if (activitiesList.children.length > 1) {
        activityElement.classList.add('list-item-exit');
        setTimeout(() => activityElement.remove(), 300);
    } else {
        showNotification('Minimal harus ada 1 aktivitas per hari', 'warning');
    }
}

function createDayElement(dayNum, title = '') {
    const dayElement = document.createElement('div');
    dayElement.className = 'itinerary-day';
    dayElement.innerHTML = `
        <div class="day-header">
            <h4><i class="fas fa-calendar-day"></i> Hari ${dayNum}</h4>
            <button type="button" class="btn-remove-day" onclick="removeDay(this)" title="Hapus Hari">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="day-content">
            <div class="form-group">
                <label>Judul Hari</label>
                <input type="text" name="itinerary_titles[]" value="${title}" placeholder="Contoh: Eksplorasi Kota & Kuliner Tour" maxlength="100">
            </div>
            <div class="activities-list">
                ${createActivityElement(dayNum - 1, 0).outerHTML}
            </div>
            <button type="button" class="btn-add-activity" onclick="addActivity(this)">
                <i class="fas fa-plus"></i> Tambah Aktivitas
            </button>
        </div>
    `;
    return dayElement;
}

function createActivityElement(dayIndex, activityIndex, time = '08:00', activity = '') {
    const activityElement = document.createElement('div');
    activityElement.className = 'activity-item';
    activityElement.innerHTML = `
        <div class="activity-time">
            <input type="time" name="itinerary_times[${dayIndex}][]" value="${time}">
        </div>
        <div class="activity-desc">
            <textarea name="itinerary_activities[${dayIndex}][]" placeholder="Deskripsi aktivitas..." rows="2">${activity}</textarea>
        </div>
        <div class="activity-actions">
            <button type="button" class="btn-remove-activity" onclick="removeActivity(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    return activityElement;
}

// Inclusion/Exclusion Functions
function addInclusion(text = '', icon = '') {
    const container = document.getElementById('inclusionsContainer');
    const newItem = createInclusionExclusionItem('inclusions[]', 'inclusion_icons[]', 'inclusion', text, icon);
    container.appendChild(newItem);
    newItem.classList.add('list-item-enter');
}

function addExclusion(text = '', icon = '') {
    const container = document.getElementById('exclusionsContainer');
    const newItem = createInclusionExclusionItem('exclusions[]', 'exclusion_icons[]', 'exclusion', text, icon);
    container.appendChild(newItem);
    newItem.classList.add('list-item-enter');
}

function createInclusionExclusionItem(nameAttr, iconAttr, type, text = '', selectedIcon = '') {
    const item = document.createElement('div');
    item.className = 'list-item';
    
    const icons = type === 'inclusion' ? [
        { value: 'fas fa-hotel', label: '🏨 Hotel' },
        { value: 'fas fa-utensils', label: '🍽️ Makan' },
        { value: 'fas fa-car', label: '🚗 Transport' },
        { value: 'fas fa-ticket-alt', label: '🎫 Tiket' },
        { value: 'fas fa-user-tie', label: '👔 Guide' },
        { value: 'fas fa-camera', label: '📸 Dokumentasi' },
        { value: 'fas fa-shield-alt', label: '🛡️ Asuransi' },
        { value: 'fas fa-gift', label: '🎁 Souvenir' }
    ] : [
        { value: 'fas fa-plane', label: '✈️ Pesawat' },
        { value: 'fas fa-shopping-bag', label: '🛍️ Belanja' },
        { value: 'fas fa-utensils', label: '🍽️ Makan Tambahan' },
        { value: 'fas fa-spa', label: '💆 Spa/Massage' },
        { value: 'fas fa-cocktail', label: '🍹 Minuman' },
        { value: 'fas fa-tshirt', label: '👕 Perlengkapan' },
        { value: 'fas fa-phone', label: '📱 Komunikasi' },
        { value: 'fas fa-hand-holding-usd', label: '💰 Pengeluaran Pribadi' }
    ];
    
    const iconOptions = icons.map(icon => 
        `<option value="${icon.value}" ${selectedIcon === icon.value ? 'selected' : ''}>${icon.label}</option>`
    ).join('');
    
    item.innerHTML = `
        <div class="item-icon">
            <select name="${iconAttr}" class="icon-select">
                ${iconOptions}
            </select>
        </div>
        <div class="item-content">
            <input type="text" name="${nameAttr}" placeholder="Masukkan item..." value="${text}" required>
        </div>
        <div class="item-actions">
            <button type="button" class="btn-remove" onclick="removeListItem(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    return item;
}

// Utility Functions
function createListItem(nameAttr, placeholder, value = '') {
    const item = document.createElement('div');
    item.className = 'list-item';
    item.innerHTML = `
        <div class="item-content">
            <input type="text" name="${nameAttr}" placeholder="${placeholder}" value="${value}" required>
        </div>
        <div class="item-actions">
            <button type="button" class="btn-remove" onclick="removeListItem(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    return item;
}

function removeListItem(button) {
    const item = button.closest('.list-item');
    item.classList.add('list-item-exit');
    setTimeout(() => item.remove(), 300);
}

// Initialize website links
function initWebsiteLinks() {
    // Add click handlers for website links in navigation
    const websiteLinks = document.querySelectorAll('a[href*="FrontEnd/html/"]');
    
    websiteLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Show a brief notification
            showNotification('Membuka website dalam tab baru...', 'success');
        });
    });
}

// Show notification function
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    
    // Add to body
    document.body.appendChild(notification);
    
    // Show with animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Debug function to test gallery functionality
window.debugGallery = function() {
    console.log('🔍 === GALLERY DEBUG INFO ===');
    console.log('📍 Current location:', window.location.href);
    console.log('📁 Base path for API calls: ../BackEnd/');
    
    // Test API endpoints
    const packageId = 15; // Test with package ID 15
    
    console.log('📡 Testing get_gallery_photos.php...');
    fetch(`../BackEnd/get_gallery_photos.php?package_id=${packageId}`)
        .then(response => {
            console.log('✅ get_gallery_photos status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('📸 Gallery photos:', data);
        })
        .catch(error => {
            console.error('❌ Gallery photos error:', error);
        });
};

// Test gallery on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add debug info
    console.log('🔧 Gallery paths configured for BackEnd directory');
    console.log('📂 Upload endpoint: ../BackEnd/upload_additional_photos.php');
    console.log('📂 Get photos endpoint: ../BackEnd/get_gallery_photos.php');
    console.log('📂 Delete photo endpoint: ../BackEnd/delete_gallery_photo.php');
    console.log('📂 Update caption endpoint: ../BackEnd/update_photo_caption.php');
});

// Gallery Layout Functions - Enhanced for Anti-Overlap
function initGalleryLayout() {
    const container = document.getElementById('existingPhotos');
    if (!container) return;
    
    console.log('🔧 Initializing gallery layout...');
    
    // Force grid layout properties
    container.style.cssText = `
        display: grid !important;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important;
        gap: 2rem !important;
        width: 100% !important;
        padding: 1rem !important;
        margin: 0 !important;
        box-sizing: border-box !important;
        align-items: start !important;
        justify-items: stretch !important;
        overflow: visible !important;
        min-height: 200px !important;
        position: relative !important;
        z-index: 1 !important;
    `;
    
    // Ensure consistent height for all photo items
    const photoItems = container.querySelectorAll('.photo-item');
    console.log(`🖼️ Processing ${photoItems.length} photo items...`);
    
    photoItems.forEach((item, index) => {
        // Reset any existing styles that might cause overlap
        item.style.cssText = `
            position: relative !important;
            display: flex !important;
            flex-direction: column !important;
            width: 100% !important;
            height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            float: none !important;
            clear: none !important;
            top: auto !important;
            left: auto !important;
            right: auto !important;
            bottom: auto !important;
            transform: none !important;
            z-index: auto !important;
            opacity: 0;
            transition: all 0.3s ease;
        `;
        
        // Force photo preview dimensions
        const preview = item.querySelector('.photo-preview');
        if (preview) {
            preview.style.cssText = `
                width: 100% !important;
                height: 200px !important;
                position: relative !important;
                overflow: hidden !important;
                flex-shrink: 0 !important;
            `;
            
            const img = preview.querySelector('img');
            if (img) {
                img.style.cssText = `
                    width: 100% !important;
                    height: 100% !important;
                    object-fit: cover !important;
                    position: relative !important;
                    margin: 0 !important;
                    padding: 0 !important;
                `;
            }
        }
        
        // Force photo info dimensions
        const info = item.querySelector('.photo-info');
        if (info) {
            info.style.cssText = `
                min-height: 140px !important;
                flex: 1 !important;
                display: flex !important;
                flex-direction: column !important;
                position: relative !important;
                padding: 1.25rem !important;
                width: 100% !important;
                box-sizing: border-box !important;
            `;
        }
    });
    
    // Animate items in with stagger effect
    photoItems.forEach((item, index) => {
        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    console.log('✅ Gallery layout initialized successfully');
    
    // Debug: Log final container properties
    const computedStyle = window.getComputedStyle(container);
    console.log('📊 Final container style:', {
        display: computedStyle.display,
        gridTemplateColumns: computedStyle.gridTemplateColumns,
        gap: computedStyle.gap,
        width: computedStyle.width,
        height: computedStyle.height
    });
}

function previewPhoto(imageUrl, caption) {
    // Create modal overlay
    const overlay = document.createElement('div');
    overlay.className = 'photo-preview-overlay';
    overlay.innerHTML = `
        <div class="preview-content">
            <button class="preview-close" onclick="this.parentElement.parentElement.remove(); document.body.style.overflow = '';">
                <i class="fas fa-times"></i>
            </button>
            <img src="${imageUrl}" alt="${caption}" />
            <div class="preview-caption">${caption}</div>
        </div>
    `;
    
    // Add click outside to close
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            overlay.remove();
            document.body.style.overflow = '';
        }
    });
    
    document.body.appendChild(overlay);
    document.body.style.overflow = 'hidden';
}

// Enhanced gallery management
function refreshGalleryDisplay(packageId) {
    const container = document.getElementById('existingPhotos');
    if (!container) return;
    
    console.log('🔄 Refreshing gallery display...');
    
    // Show loading state
    container.innerHTML = `
        <div class="gallery-loading" style="grid-column: 1 / -1; text-align: center; padding: 2rem;">
            <i class="fas fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem; color: var(--primary);"></i>
            <p>Memuat ulang gallery...</p>
        </div>
    `;
    
    // Force grid layout on container
    container.style.display = 'grid';
    container.style.gridTemplateColumns = 'repeat(auto-fill, minmax(280px, 1fr))';
    container.style.gap = '2rem';
    
    // Reload photos
    setTimeout(() => {
        loadExistingPhotos(packageId);
    }, 500);
}

// Debug function for gallery issues
function debugGalleryLayout() {
    console.log('🔍 === GALLERY LAYOUT DEBUG ===');
    
    const container = document.getElementById('existingPhotos');
    if (!container) {
        console.error('❌ Container #existingPhotos not found!');
        return;
    }
    
    const computedStyle = window.getComputedStyle(container);
    console.log('📦 Container properties:', {
        display: computedStyle.display,
        gridTemplateColumns: computedStyle.gridTemplateColumns,
        gap: computedStyle.gap,
        width: computedStyle.width,
        height: computedStyle.height,
        position: computedStyle.position,
        overflow: computedStyle.overflow,
        alignItems: computedStyle.alignItems,
        justifyItems: computedStyle.justifyItems
    });
    
    const photoItems = container.querySelectorAll('.photo-item');
    console.log(`📸 Found ${photoItems.length} photo items`);
    
    photoItems.forEach((item, index) => {
        const itemStyle = window.getComputedStyle(item);
        const rect = item.getBoundingClientRect();
        
        console.log(`🖼️ Photo ${index + 1}:`, {
            position: itemStyle.position,
            top: itemStyle.top,
            left: itemStyle.left,
            width: itemStyle.width,
            height: itemStyle.height,
            margin: itemStyle.margin,
            padding: itemStyle.padding,
            float: itemStyle.float,
            display: itemStyle.display,
            flexDirection: itemStyle.flexDirection,
            boundingRect: {
                x: rect.x,
                y: rect.y,
                width: rect.width,
                height: rect.height
            }
        });
        
        // Check for overlap with previous items
        if (index > 0) {
            const prevItem = photoItems[index - 1];
            const prevRect = prevItem.getBoundingClientRect();
            
            const isOverlapping = !(rect.right <= prevRect.left || 
                                   rect.left >= prevRect.right || 
                                   rect.bottom <= prevRect.top || 
                                   rect.top >= prevRect.bottom);
            
            if (isOverlapping) {
                console.warn(`⚠️ OVERLAP DETECTED between photo ${index} and ${index - 1}!`);
                console.log('Overlap details:', {
                    current: rect,
                    previous: prevRect
                });
            }
        }
    });
    
    console.log('🎯 Debug complete. Check for OVERLAP DETECTED warnings above.');
}

// Logout function
function performLogout() {
    if (confirm('Are you sure you want to logout?')) {
        // Clear any local storage or session storage if used
        localStorage.clear();
        sessionStorage.clear();
        
        // Redirect to logout URL which will redirect to profile.html
        window.location.href = 'ViewLoginAdmin.php?logout=1';
    }
}

// Make functions available globally
window.openGallery = openGallery;
window.closeGallery = closeGallery;
window.closeDeleteModal = closeDeleteModal; // <-- Add this line
window.editPackage = editPackage;
window.deletePackage = deletePackage;
window.deleteGalleryPhoto = deleteGalleryPhoto;
window.editPhotoCaption = editPhotoCaption;
window.showSection = showSection;
window.addHighlight = addHighlight;
window.addDay = addDay;
window.removeDay = removeDay;
window.addActivity = addActivity;
window.removeActivity = removeActivity;
window.addInclusion = addInclusion;
window.addExclusion = addExclusion;
window.removeListItem = removeListItem;
window.debugGalleryLayout = debugGalleryLayout;
window.performLogout = performLogout;