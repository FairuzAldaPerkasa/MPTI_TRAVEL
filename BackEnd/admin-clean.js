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

// Navigation System
function initNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('.content-section');
    
    // Handle navigation clicks
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const target = this.getAttribute('href');
            
            // Skip external links
            if (target.startsWith('http') || target.includes('.html')) {
                return;
            }
            
            e.preventDefault();
            showSection(target.substring(1));
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
            const item = document.createElement('div');
            item.className = 'file-preview-item';
            
            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.onload = () => URL.revokeObjectURL(img.src);
                item.appendChild(img);
            }
            
            const name = document.createElement('div');
            name.className = 'file-name';
            name.textContent = file.name;
            item.appendChild(name);
            
            preview.appendChild(item);
        });
    }
}

function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = 'var(--danger)';
            isValid = false;
        } else {
            field.style.borderColor = 'var(--border)';
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
                    html += `
                        <div class="gallery-photo-item">
                            <img src="uploads/gallery/${photo.photo_filename}" 
                                 alt="${photo.caption || 'Gallery Photo'}" 
                                 onerror="this.src='../Asset/img/default.jpg'">
                            <div class="photo-actions">
                                <button onclick="editPhotoCaption(${photo.id}, '${(photo.caption || '').replace(/'/g, "\\'")}')" 
                                        class="btn-edit" title="Edit Caption">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteGalleryPhoto(${photo.id})" 
                                        class="btn-delete" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="photo-caption">${photo.caption || 'Tanpa caption'}</div>
                        </div>
                    `;
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

// Global counters
let dayCounter = 1;
let activityCounters = [0]; // Track activities per day

// Initialize dynamic forms
function initDynamicForms() {
    // Initialize sortable for better UX
    initSortable();
}

// Highlight Functions
function addHighlight() {
    const container = document.getElementById('highlightsContainer');
    const newItem = createListItem('highlights[]', 'Masukkan highlight menarik...');
    container.appendChild(newItem);
    newItem.classList.add('list-item-enter');
}

function loadHighlightTemplates() {
    const templates = {
        'Wisata Alam': [
            'Pemandangan sunrise/sunset yang menakjubkan',
            'Trekking di jalur yang menantang',
            'Air terjun tersembunyi yang eksotis',
            'Spot foto Instagram-able',
            'Udara segar pegunungan'
        ],
        'Wisata Budaya': [
            'Kunjungan ke situs bersejarah',
            'Pertunjukan seni tradisional',
            'Workshop kerajinan lokal',
            'Kuliner khas daerah',
            'Interaksi dengan masyarakat lokal'
        ],
        'Wisata Pantai': [
            'Pantai berpasir putih yang bersih',
            'Snorkeling di terumbu karang',
            'Sunset dinner di tepi pantai',
            'Water sport yang seru',
            'Relaxing spa treatment'
        ]
    };
    
    showTemplateModal('Pilih Template Highlight', templates, (selected) => {
        const container = document.getElementById('highlightsContainer');
        container.innerHTML = '';
        selected.forEach(highlight => {
            const item = createListItem('highlights[]', 'highlight...');
            item.querySelector('input').value = highlight;
            container.appendChild(item);
        });
    });
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
        showNotification('Minimal harus ada 1 hari dalam itinerary', 'warning');
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

function loadItineraryTemplates() {
    const templates = {
        '2D1N City Tour': {
            description: 'Template untuk wisata kota 2 hari 1 malam',
            days: [
                {
                    title: 'Kedatangan & City Tour',
                    activities: [
                        { time: '08:00', desc: 'Penjemputan di bandara/stasiun' },
                        { time: '10:00', desc: 'Check-in hotel & istirahat' },
                        { time: '13:00', desc: 'Makan siang di restoran lokal' },
                        { time: '14:30', desc: 'City tour mengunjungi tempat bersejarah' },
                        { time: '17:00', desc: 'Shopping di pusat oleh-oleh' },
                        { time: '19:00', desc: 'Makan malam & kembali ke hotel' }
                    ]
                },
                {
                    title: 'Wisata Alam & Kepulangan',
                    activities: [
                        { time: '06:00', desc: 'Sarapan di hotel & check-out' },
                        { time: '08:00', desc: 'Perjalanan ke objek wisata alam' },
                        { time: '10:00', desc: 'Trekking & foto-foto' },
                        { time: '12:00', desc: 'Makan siang di warung lokal' },
                        { time: '14:00', desc: 'Perjalanan kembali' },
                        { time: '16:00', desc: 'Pengantaran ke bandara/stasiun' }
                    ]
                }
            ]
        },
        '3D2N Adventure': {
            description: 'Template untuk wisata petualangan 3 hari 2 malam',
            days: [
                {
                    title: 'Kedatangan & Persiapan',
                    activities: [
                        { time: '07:00', desc: 'Penjemputan & perjalanan ke basecamp' },
                        { time: '10:00', desc: 'Briefing & persiapan equipment' },
                        { time: '12:00', desc: 'Makan siang & check-in penginapan' },
                        { time: '15:00', desc: 'Eksplorasi area sekitar' },
                        { time: '18:00', desc: 'BBQ dinner & api unggun' }
                    ]
                },
                {
                    title: 'Petualangan Utama',
                    activities: [
                        { time: '05:00', desc: 'Sunrise hunting & sarapan' },
                        { time: '07:00', desc: 'Trekking ke puncak/air terjun' },
                        { time: '12:00', desc: 'Lunch box di lokasi' },
                        { time: '14:00', desc: 'Aktivitas adventure (rafting/flying fox)' },
                        { time: '17:00', desc: 'Kembali ke penginapan & istirahat' },
                        { time: '19:00', desc: 'Makan malam & sharing session' }
                    ]
                },
                {
                    title: 'Eksplorasi & Kepulangan',
                    activities: [
                        { time: '06:00', desc: 'Sarapan & check-out' },
                        { time: '08:00', desc: 'Kunjungan ke desa wisata' },
                        { time: '10:00', desc: 'Workshop kerajinan lokal' },
                        { time: '12:00', desc: 'Makan siang khas daerah' },
                        { time: '14:00', desc: 'Perjalanan pulang' },
                        { time: '17:00', desc: 'Tiba di kota & pengantaran' }
                    ]
                }
            ]
        }
    };
    
    showItineraryTemplateModal(templates);
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
    
    const iconOptions = type === 'inclusion' 
        ? `<option value="fas fa-hotel">🏨 Hotel</option>
           <option value="fas fa-utensils">🍽️ Makan</option>
           <option value="fas fa-car">🚗 Transport</option>
           <option value="fas fa-ticket-alt">🎫 Tiket</option>
           <option value="fas fa-user-tie">👔 Guide</option>
           <option value="fas fa-camera">📸 Dokumentasi</option>
           <option value="fas fa-shield-alt">🛡️ Asuransi</option>
           <option value="fas fa-gift">🎁 Souvenir</option>`
        : `<option value="fas fa-plane">✈️ Pesawat</option>
           <option value="fas fa-shopping-bag">🛍️ Belanja</option>
           <option value="fas fa-utensils">🍽️ Makan Tambahan</option>
           <option value="fas fa-spa">💆 Spa/Massage</option>
           <option value="fas fa-cocktail">🍹 Minuman</option>
           <option value="fas fa-tshirt">👕 Perlengkapan</option>
           <option value="fas fa-phone">📱 Komunikasi</option>
           <option value="fas fa-hand-holding-usd">💰 Pengeluaran Pribadi</option>`;
    
    item.innerHTML = `
        <div class="item-icon">
            <select name="${iconAttr}" class="icon-select">
                ${iconOptions}
            </select>
        </div>
        <div class="item-content">
            <input type="text" name="${nameAttr}" placeholder="Masukkan item..." maxlength="150">
        </div>
        <div class="item-actions">
            <button type="button" class="btn-remove" onclick="removeListItem(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    return item;
}

function loadInclusionTemplates() {
    const templates = {
        'Paket Lengkap': [
            { icon: 'fas fa-hotel', text: 'Hotel bintang 4 dengan breakfast' },
            { icon: 'fas fa-car', text: 'Transport AC selama tour' },
            { icon: 'fas fa-utensils', text: 'Makan siang dan malam sesuai program' },
            { icon: 'fas fa-ticket-alt', text: 'Tiket masuk semua objek wisata' },
            { icon: 'fas fa-user-tie', text: 'Tour guide berpengalaman' },
            { icon: 'fas fa-camera', text: 'Dokumentasi foto selama perjalanan' },
            { icon: 'fas fa-shield-alt', text: 'Asuransi perjalanan' }
        ],
        'Paket Hemat': [
            { icon: 'fas fa-hotel', text: 'Hotel budget dengan AC' },
            { icon: 'fas fa-car', text: 'Transport sharing dengan AC' },
            { icon: 'fas fa-utensils', text: 'Makan siang sesuai program' },
            { icon: 'fas fa-ticket-alt', text: 'Tiket masuk objek wisata utama' },
            { icon: 'fas fa-user-tie', text: 'Local guide' }
        ],
        'Paket Premium': [
            { icon: 'fas fa-hotel', text: 'Hotel bintang 5 dengan full board' },
            { icon: 'fas fa-car', text: 'Private car dengan driver' },
            { icon: 'fas fa-utensils', text: 'All meals dengan menu premium' },
            { icon: 'fas fa-ticket-alt', text: 'VIP ticket semua destinasi' },
            { icon: 'fas fa-user-tie', text: 'Professional tour guide' },
            { icon: 'fas fa-camera', text: 'Professional photographer' },
            { icon: 'fas fa-gift', text: 'Welcome gift & souvenir eksklusif' },
            { icon: 'fas fa-spa', text: 'Spa treatment session' }
        ]
    };
    
    showInclusionExclusionTemplateModal('Pilih Template Inclusion', templates, 'inclusions');
}

function loadExclusionTemplates() {
    const templates = {
        'Standard Exclusions': [
            { icon: 'fas fa-plane', text: 'Tiket pesawat ke/dari destinasi' },
            { icon: 'fas fa-shopping-bag', text: 'Pengeluaran pribadi dan belanja' },
            { icon: 'fas fa-utensils', text: 'Makan di luar program' },
            { icon: 'fas fa-cocktail', text: 'Minuman beralkohol' },
            { icon: 'fas fa-hand-holding-usd', text: 'Tip untuk guide dan driver' }
        ],
        'Adventure Exclusions': [
            { icon: 'fas fa-tshirt', text: 'Perlengkapan outdoor pribadi' },
            { icon: 'fas fa-phone', text: 'Biaya komunikasi' },
            { icon: 'fas fa-spa', text: 'Massage dan spa treatment' },
            { icon: 'fas fa-shopping-bag', text: 'Souvenir dan oleh-oleh' },
            { icon: 'fas fa-hand-holding-usd', text: 'Pengeluaran di luar itinerary' }
        ]
    };
    
    showInclusionExclusionTemplateModal('Pilih Template Exclusion', templates, 'exclusions');
}

// Utility Functions
function createListItem(nameAttr, placeholder) {
    const item = document.createElement('div');
    item.className = 'list-item';
    item.innerHTML = `
        <div class="item-content">
            <input type="text" name="${nameAttr}" placeholder="${placeholder}" maxlength="150">
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
    const listItem = button.closest('.list-item');
    const container = listItem.parentNode;
    
    // Don't remove if it's the last item
    if (container.children.length > 1) {
        listItem.classList.add('list-item-exit');
        setTimeout(() => listItem.remove(), 300);
    } else {
        showNotification('Minimal harus ada 1 item', 'warning');
    }
}

// Template Modal Functions
function showTemplateModal(title, templates, callback) {
    const modal = document.getElementById('templateModal');
    const content = document.getElementById('templateContent');
    
    modal.querySelector('.modal-header h3').innerHTML = `<i class="fas fa-magic"></i> ${title}`;
    
    let html = '<div class="template-grid">';
    Object.entries(templates).forEach(([name, items]) => {
        html += `
            <div class="template-item" onclick="selectTemplate('${name}', ${JSON.stringify(items).replace(/"/g, '&quot;')}, ${callback})">
                <h4>${name}</h4>
                <div class="template-preview">
                    <ul>
                        ${items.slice(0, 3).map(item => `<li>${item}</li>`).join('')}
                        ${items.length > 3 ? `<li>... dan ${items.length - 3} lainnya</li>` : ''}
                    </ul>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    content.innerHTML = html;
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function showItineraryTemplateModal(templates) {
    const modal = document.getElementById('templateModal');
    const content = document.getElementById('templateContent');
    
    modal.querySelector('.modal-header h3').innerHTML = `<i class="fas fa-magic"></i> Pilih Template Itinerary`;
    
    let html = '<div class="template-grid">';
    Object.entries(templates).forEach(([name, template]) => {
        html += `
            <div class="template-item" onclick="selectItineraryTemplate('${name}')">
                <h4>${name}</h4>
                <p>${template.description}</p>
                <div class="template-preview">
                    <strong>Hari 1:</strong> ${template.days[0].title}<br>
                    <small>${template.days[0].activities.slice(0, 2).map(a => a.desc).join(', ')}...</small>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    content.innerHTML = html;
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function selectItineraryTemplate(templateName) {
    const templates = getItineraryTemplates();
    const selectedTemplate = templates[templateName];
    
    if (!selectedTemplate) {
        showNotification('Template tidak ditemukan', 'error');
        return;
    }
    
    // Clear existing itinerary
    const container = document.getElementById('itineraryContainer');
    container.innerHTML = '';
    
    // Reset counters
    dayCounter = 0;
    activityCounters = [];
    
    // Add template days
    selectedTemplate.days.forEach((day, index) => {
        addDayFromTemplate(day, index);
    });
    
    showNotification(`Template "${templateName}" berhasil diterapkan!`, 'success');
    closeTemplateModal();
}

function addDayFromTemplate(dayData, dayIndex) {
    const container = document.getElementById('itineraryContainer');
    const dayNum = dayIndex + 1;
    dayCounter = dayNum;
    activityCounters[dayIndex] = dayData.activities ? dayData.activities.length : 0;
    
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
                <input type="text" name="itinerary_titles[]" value="${dayData.title || ''}" placeholder="Contoh: Kedatangan & City Tour" maxlength="100">
            </div>
            <div class="activities-list" id="activities-${dayIndex}">
                <!-- Activities will be added here -->
            </div>
            <button type="button" class="btn-add-activity" onclick="addActivity(this)">
                <i class="fas fa-plus"></i> Tambah Aktivitas
            </button>
        </div>
    `;
    
    container.appendChild(dayElement);
    
    // Add activities if they exist
    if (dayData.activities && dayData.activities.length > 0) {
        const activitiesList = dayElement.querySelector('.activities-list');
        dayData.activities.forEach((activity, actIndex) => {
            const activityElement = createActivityElement(dayIndex, actIndex);
            activitiesList.appendChild(activityElement);
            
            // Fill in activity data
            const timeInput = activityElement.querySelector('input[type="time"]');
            const descTextarea = activityElement.querySelector('textarea');
            
            if (timeInput && activity.time) {
                timeInput.value = activity.time;
            }
            if (descTextarea && activity.desc) {
                descTextarea.value = activity.desc;
            }
        });
    }
}

function selectInclusionExclusionTemplate(templateName, type) {
    const templates = type === 'inclusions' ? getInclusionTemplates() : getExclusionTemplates();
    const selectedTemplate = templates[templateName];
    
    if (!selectedTemplate) {
        showNotification('Template tidak ditemukan', 'error');
        return;
    }
    
    // Clear existing items
    const containerId = type === 'inclusions' ? 'inclusionsContainer' : 'exclusionsContainer';
    const container = document.getElementById(containerId);
    container.innerHTML = '';
    
    // Add template items
    selectedTemplate.forEach(item => {
        const nameAttr = type === 'inclusions' ? 'inclusions[]' : 'exclusions[]';
        const iconAttr = type === 'inclusions' ? 'inclusion_icons[]' : 'exclusion_icons[]';
        
        const listItem = createInclusionExclusionItem(nameAttr, iconAttr, type);
        
        // Fill in data
        const iconSelect = listItem.querySelector('.icon-select');
        const textInput = listItem.querySelector('input[type="text"]');
        
        if (iconSelect && item.icon) {
            iconSelect.value = item.icon;
        }
        if (textInput && item.text) {
            textInput.value = item.text;
        }
        
        container.appendChild(listItem);
    });
    
    showNotification(`Template "${templateName}" berhasil diterapkan!`, 'success');
    closeTemplateModal();
}

function getItineraryTemplates() {
    return {
        '2D1N City Tour': {
            description: 'Template untuk wisata kota 2 hari 1 malam',
            days: [
                {
                    title: 'Kedatangan & City Tour',
                    activities: [
                        { time: '08:00', desc: 'Penjemputan di bandara/stasiun' },
                        { time: '10:00', desc: 'Check-in hotel & istirahat' },
                        { time: '13:00', desc: 'Makan siang di restoran lokal' },
                        { time: '14:30', desc: 'City tour mengunjungi tempat bersejarah' },
                        { time: '17:00', desc: 'Shopping di pusat oleh-oleh' },
                        { time: '19:00', desc: 'Makan malam & kembali ke hotel' }
                    ]
                },
                {
                    title: 'Wisata Alam & Kepulangan',
                    activities: [
                        { time: '06:00', desc: 'Sarapan di hotel & check-out' },
                        { time: '08:00', desc: 'Perjalanan ke objek wisata alam' },
                        { time: '10:00', desc: 'Trekking & foto-foto' },
                        { time: '12:00', desc: 'Makan siang di warung lokal' },
                        { time: '14:00', desc: 'Perjalanan kembali' },
                        { time: '16:00', desc: 'Pengantaran ke bandara/stasiun' }
                    ]
                }
            ]
        },
        '3D2N Adventure': {
            description: 'Template untuk wisata petualangan 3 hari 2 malam',
            days: [
                {
                    title: 'Kedatangan & Persiapan',
                    activities: [
                        { time: '07:00', desc: 'Penjemputan & perjalanan ke basecamp' },
                        { time: '10:00', desc: 'Briefing & persiapan equipment' },
                        { time: '12:00', desc: 'Makan siang & check-in penginapan' },
                        { time: '15:00', desc: 'Eksplorasi area sekitar' },
                        { time: '18:00', desc: 'BBQ dinner & api unggun' }
                    ]
                },
                {
                    title: 'Petualangan Utama',
                    activities: [
                        { time: '05:00', desc: 'Sunrise hunting & sarapan' },
                        { time: '07:00', desc: 'Trekking ke puncak/air terjun' },
                        { time: '12:00', desc: 'Lunch box di lokasi' },
                        { time: '14:00', desc: 'Aktivitas adventure (rafting/flying fox)' },
                        { time: '17:00', desc: 'Kembali ke penginapan & istirahat' },
                        { time: '19:00', desc: 'Makan malam & sharing session' }
                    ]
                },
                {
                    title: 'Eksplorasi & Kepulangan',
                    activities: [
                        { time: '06:00', desc: 'Sarapan & check-out' },
                        { time: '08:00', desc: 'Kunjungan ke desa wisata' },
                        { time: '10:00', desc: 'Workshop kerajinan lokal' },
                        { time: '12:00', desc: 'Makan siang khas daerah' },
                        { time: '14:00', desc: 'Perjalanan pulang' },
                        { time: '17:00', desc: 'Tiba di kota & pengantaran' }
                    ]
                }
            ]
        }
    };
}

function getInclusionTemplates() {
    return {
        'Paket Lengkap': [
            { icon: 'fas fa-hotel', text: 'Hotel bintang 4 dengan breakfast' },
            { icon: 'fas fa-car', text: 'Transport AC selama tour' },
            { icon: 'fas fa-utensils', text: 'Makan siang dan malam sesuai program' },
            { icon: 'fas fa-ticket-alt', text: 'Tiket masuk semua objek wisata' },
            { icon: 'fas fa-user-tie', text: 'Tour guide berpengalaman' },
            { icon: 'fas fa-camera', text: 'Dokumentasi foto selama perjalanan' },
            { icon: 'fas fa-shield-alt', text: 'Asuransi perjalanan' }
        ],
        'Paket Hemat': [
            { icon: 'fas fa-hotel', text: 'Hotel budget dengan AC' },
            { icon: 'fas fa-car', text: 'Transport sharing dengan AC' },
            { icon: 'fas fa-utensils', text: 'Makan siang sesuai program' },
            { icon: 'fas fa-ticket-alt', text: 'Tiket masuk objek wisata utama' },
            { icon: 'fas fa-user-tie', text: 'Local guide' }
        ],
        'Paket Premium': [
            { icon: 'fas fa-hotel', text: 'Hotel bintang 5 dengan full board' },
            { icon: 'fas fa-car', text: 'Private car dengan driver' },
            { icon: 'fas fa-utensils', text: 'All meals dengan menu premium' },
            { icon: 'fas fa-ticket-alt', text: 'VIP ticket semua destinasi' },
            { icon: 'fas fa-user-tie', text: 'Professional tour guide' },
            { icon: 'fas fa-camera', text: 'Professional photographer' },
            { icon: 'fas fa-gift', text: 'Welcome gift & souvenir eksklusif' },
            { icon: 'fas fa-spa', text: 'Spa treatment session' }
        ]
    };
}

function getExclusionTemplates() {
    return {
        'Standard Exclusions': [
            { icon: 'fas fa-plane', text: 'Tiket pesawat ke/dari destinasi' },
            { icon: 'fas fa-shopping-bag', text: 'Pengeluaran pribadi dan belanja' },
            { icon: 'fas fa-utensils', text: 'Makan di luar program' },
            { icon: 'fas fa-cocktail', text: 'Minuman beralkohol' },
            { icon: 'fas fa-hand-holding-usd', text: 'Tip untuk guide dan driver' }
        ],
        'Adventure Exclusions': [
            { icon: 'fas fa-tshirt', text: 'Perlengkapan outdoor pribadi' },
            { icon: 'fas fa-phone', text: 'Biaya komunikasi' },
            { icon: 'fas fa-spa', text: 'Massage dan spa treatment' },
            { icon: 'fas fa-shopping-bag', text: 'Souvenir dan oleh-oleh' },
            { icon: 'fas fa-hand-holding-usd', text: 'Pengeluaran di luar itinerary' }
        ]
    };
}