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
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
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
    
    fetch('gallery-manager.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Foto berhasil diupload!', 'success');
            form.reset();
            document.getElementById('galleryCaptions').innerHTML = '';
            loadExistingPhotos(formData.get('package_id'));
        } else {
            showNotification(data.message || 'Gagal mengupload foto', 'error');
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
    
    fetch(`gallery-manager.php?action=get_photos&package_id=${packageId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.photos && data.photos.length > 0) {
                let html = '';
                data.photos.forEach(photo => {
                    html += `<div class="photo-item">
                        <img src="uploads/gallery/${photo.filename}" alt="${photo.caption}">
                        <div class="photo-info">
                            <p>${photo.caption}</p>
                            <div class="photo-actions">
                                <button onclick="editPhotoCaption(${photo.id}, '${photo.caption}')" class="btn-edit-photo">Edit</button>
                                <button onclick="deleteGalleryPhoto(${photo.id})" class="btn-delete-photo">Hapus</button>
                            </div>
                        </div>
                    </div>`;
                });
                container.innerHTML = html;
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
    if (confirm(`Yakin ingin menghapus paket "${name}"?\n\nSemua foto dan data akan dihapus permanen!`)) {
        window.location.href = `admin.php?hapus=${id}`;
    }
}

function deleteGalleryPhoto(photoId) {
    if (confirm('Yakin ingin menghapus foto ini?')) {
        const packageId = document.getElementById('packageId').value;
        
        fetch('delete_gallery_photo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `photo_id=${photoId}&package_id=${packageId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Foto berhasil dihapus', 'success');
                loadExistingPhotos(packageId);
            } else {
                showNotification(data.message || 'Gagal menghapus foto', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan', 'error');
        });
    }
}

function editPhotoCaption(photoId, currentCaption) {
    const newCaption = prompt('Edit caption foto:', currentCaption);
    if (newCaption !== null) {
        fetch('update_photo_caption.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                photo_id: photoId,
                caption: newCaption
            })
        })
        .then(response => response.json())
        .then(data => {
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
function addHighlight() {
    const container = document.getElementById('highlightsContainer');
    const newItem = createListItem('highlights[]', 'Masukkan highlight menarik...');
    container.appendChild(newItem);
    newItem.classList.add('list-item-enter');
}

// Itinerary Functions
function addDay() {
    dayCounter++;
    const container = document.getElementById('itineraryContainer');
    const newDay = createDayElement(dayCounter);
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

function addActivity(button) {
    const dayElement = button.closest('.itinerary-day');
    const dayIndex = Array.from(dayElement.parentNode.children).indexOf(dayElement);
    const activitiesList = dayElement.querySelector('.activities-list');
    
    const newActivity = createActivityElement(dayIndex, activityCounters[dayIndex]);
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

function createDayElement(dayNum) {
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
                <input type="text" name="itinerary_titles[]" placeholder="Contoh: Eksplorasi Kota & Kuliner Tour" maxlength="100">
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

function createActivityElement(dayIndex, activityIndex) {
    const activityElement = document.createElement('div');
    activityElement.className = 'activity-item';
    activityElement.innerHTML = `
        <div class="activity-time">
            <input type="time" name="itinerary_times[${dayIndex}][]" value="08:00">
        </div>
        <div class="activity-desc">
            <textarea name="itinerary_activities[${dayIndex}][]" placeholder="Deskripsi aktivitas..." rows="2"></textarea>
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
function addInclusion() {
    const container = document.getElementById('inclusionsContainer');
    const newItem = createInclusionExclusionItem('inclusions[]', 'inclusion_icons[]', 'inclusion');
    container.appendChild(newItem);
    newItem.classList.add('list-item-enter');
}

function addExclusion() {
    const container = document.getElementById('exclusionsContainer');
    const newItem = createInclusionExclusionItem('exclusions[]', 'exclusion_icons[]', 'exclusion');
    container.appendChild(newItem);
    newItem.classList.add('list-item-enter');
}

function createInclusionExclusionItem(nameAttr, iconAttr, type) {
    const item = document.createElement('div');
    item.className = 'list-item';
    
    const icons = type === 'inclusion' ? [
        'fas fa-check-circle',
        'fas fa-hotel',
        'fas fa-utensils',
        'fas fa-bus-alt',
        'fas fa-ticket-alt',
        'fas fa-user-tie'
    ] : [
        'fas fa-times-circle',
        'fas fa-plane-departure',
        'fas fa-shopping-bag',
        'fas fa-cocktail',
        'fas fa-gift',
        'fas fa-hand-holding-usd'
    ];
    
    item.innerHTML = `
        <div class="item-icon">
            <select name="${iconAttr}" class="icon-select">
                ${icons.map(icon => `<option value="${icon}">${icon}</option>`).join('')}
            </select>
        </div>
        <div class="item-content">
            <input type="text" name="${nameAttr}" placeholder="Masukkan item..." required>
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
function createListItem(nameAttr, placeholder) {
    const item = document.createElement('div');
    item.className = 'list-item';
    item.innerHTML = `
        <div class="item-content">
            <input type="text" name="${nameAttr}" placeholder="${placeholder}" required>
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

// Add missing showNotification function
function showNotification(message, type = 'info') {
    let notification = document.getElementById('notification');
    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'notification';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s ease;
        `;
        document.body.appendChild(notification);
    }
    
    const colors = {
        success: '#28a745',
        error: '#dc3545',
        warning: '#ffc107',
        info: '#17a2b8'
    };
    
    notification.style.backgroundColor = colors[type] || colors.info;
    notification.textContent = message;
    
    notification.style.opacity = '1';
    notification.style.transform = 'translateY(0)';
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
    }, 3000);
}

// Make functions available globally
window.openGallery = openGallery;
window.closeGallery = closeGallery;
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