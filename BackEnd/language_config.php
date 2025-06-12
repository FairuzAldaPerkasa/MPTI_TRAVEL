<?php
// Language Configuration for MPTI Travel Backend
// Multi-language support for API responses

class LanguageConfig {
    private $current_language = 'id';
    private $fallback_language = 'id';
    
    private $translations = [
        'id' => [
            'errors' => [
                'invalid_package_id' => 'ID paket tidak valid',
                'database_connection_failed' => 'Koneksi database gagal',
                'package_not_found' => 'Paket tidak ditemukan',
                'query_preparation_failed' => 'Persiapan query gagal',
                'query_execution_failed' => 'Eksekusi query gagal',
                'unknown_error' => 'Terjadi kesalahan tidak dikenal'
            ],
            'status' => [
                'loading' => 'Memuat...',
                'processing' => 'Memproses...',
                'success' => 'Berhasil',
                'failed' => 'Gagal'
            ],
            'currency' => [
                'prefix' => 'Rp ',
                'suffix' => '',
                'format' => 'id-ID'
            ],
            'date_format' => [
                'locale' => 'id-ID',
                'format' => 'd F Y'
            ],
            'package' => [
                'default_description' => 'Deskripsi paket akan segera tersedia',
                'price_on_request' => 'Hubungi untuk harga',
                'minimum_participants' => 'per orang (minimal 2 peserta)',
                'duration_days' => 'hari',
                'duration_nights' => 'malam',
                'contact_for_booking' => 'Hubungi kami untuk pemesanan',
                'included_default' => [
                    'Transportasi AC',
                    'Tiket masuk wisata',
                    'Makan sesuai program',
                    'Guide berpengalaman'
                ],
                'excluded_default' => [
                    'Tiket pesawat',
                    'Pengeluaran pribadi',
                    'Minuman beralkohol',
                    'Tips guide (optional)'
                ],
                'highlights_default' => [
                    'Pengalaman wisata yang tak terlupakan',
                    'Guide berpengalaman dan ramah',
                    'Destinasi wisata terpopuler',
                    'Fasilitas lengkap dan nyaman'
                ]
            ]
        ],
        'en' => [
            'errors' => [
                'invalid_package_id' => 'Invalid package ID',
                'database_connection_failed' => 'Database connection failed',
                'package_not_found' => 'Package not found',
                'query_preparation_failed' => 'Query preparation failed',
                'query_execution_failed' => 'Query execution failed',
                'unknown_error' => 'An unknown error occurred'
            ],
            'status' => [
                'loading' => 'Loading...',
                'processing' => 'Processing...',
                'success' => 'Success',
                'failed' => 'Failed'
            ],
            'currency' => [
                'prefix' => 'IDR ',
                'suffix' => '',
                'format' => 'en-US'
            ],
            'date_format' => [
                'locale' => 'en-US',
                'format' => 'F d, Y'
            ],
            'package' => [
                'default_description' => 'Package description will be available soon',
                'price_on_request' => 'Contact for price',
                'minimum_participants' => 'per person (minimum 2 participants)',
                'duration_days' => 'days',
                'duration_nights' => 'nights',
                'contact_for_booking' => 'Contact us for booking',
                'included_default' => [
                    'AC Transportation',
                    'Tourist attraction tickets',
                    'Meals according to program',
                    'Experienced guide'
                ],
                'excluded_default' => [
                    'Flight tickets',
                    'Personal expenses',
                    'Alcoholic beverages',
                    'Guide tips (optional)'
                ],
                'highlights_default' => [
                    'Unforgettable travel experience',
                    'Experienced and friendly guide',
                    'Most popular tourist destinations',
                    'Complete and comfortable facilities'
                ]
            ]
        ]
    ];
    
    public function __construct($language = null) {
        if ($language) {
            $this->setLanguage($language);
        } else {
            // Try to get language from various sources
            $this->detectLanguage();
        }
    }
    
    private function detectLanguage() {
        // Priority: URL parameter -> Header -> Session -> Cookie -> Default
        $lang = null;
        
        // 1. Check URL parameter
        if (isset($_GET['lang']) && in_array($_GET['lang'], ['id', 'en'])) {
            $lang = $_GET['lang'];
        }
        // 2. Check POST parameter
        elseif (isset($_POST['lang']) && in_array($_POST['lang'], ['id', 'en'])) {
            $lang = $_POST['lang'];
        }
        // 3. Check custom header
        elseif (isset($_SERVER['HTTP_X_LANGUAGE']) && in_array($_SERVER['HTTP_X_LANGUAGE'], ['id', 'en'])) {
            $lang = $_SERVER['HTTP_X_LANGUAGE'];
        }
        // 4. Check Accept-Language header
        elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $accept_lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            if (strpos($accept_lang, 'id') !== false) {
                $lang = 'id';
            } elseif (strpos($accept_lang, 'en') !== false) {
                $lang = 'en';
            }
        }
        
        $this->setLanguage($lang ?: $this->fallback_language);
    }
    
    public function setLanguage($language) {
        if (in_array($language, ['id', 'en'])) {
            $this->current_language = $language;
        } else {
            $this->current_language = $this->fallback_language;
        }
    }
    
    public function getLanguage() {
        return $this->current_language;
    }
    
    public function translate($key, $fallback = null) {
        $keys = explode('.', $key);
        $value = $this->translations[$this->current_language];
        
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                // Try fallback language
                $fallback_value = $this->translations[$this->fallback_language];
                foreach ($keys as $fk) {
                    if (isset($fallback_value[$fk])) {
                        $fallback_value = $fallback_value[$fk];
                    } else {
                        return $fallback ?: $key;
                    }
                }
                return $fallback_value;
            }
        }
        
        return $value;
    }
    
    public function formatCurrency($amount) {
        if (!is_numeric($amount) || $amount <= 0) {
            return $this->translate('package.price_on_request');
        }
        
        $config = $this->translations[$this->current_language]['currency'];
        $formatted = number_format($amount, 0, ',', '.');
        
        return $config['prefix'] . $formatted . $config['suffix'];
    }
    
    public function formatDate($date, $format = null) {
        if (!$date) return '';
        
        $config = $this->translations[$this->current_language]['date_format'];
        $format = $format ?: $config['format'];
        
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        
        return $date->format($format);
    }
    
    public function getDefaultInclusions() {
        return $this->translate('package.included_default');
    }
    
    public function getDefaultExclusions() {
        return $this->translate('package.excluded_default');
    }
    
    public function getDefaultHighlights() {
        return $this->translate('package.highlights_default');
    }
}

// Helper function for easy access
function lang($key, $fallback = null) {
    global $language_config;
    if (!isset($language_config)) {
        $language_config = new LanguageConfig();
    }
    return $language_config->translate($key, $fallback);
}

function formatCurrency($amount) {
    global $language_config;
    if (!isset($language_config)) {
        $language_config = new LanguageConfig();
    }
    return $language_config->formatCurrency($amount);
}

function formatDate($date, $format = null) {
    global $language_config;
    if (!isset($language_config)) {
        $language_config = new LanguageConfig();
    }
    return $language_config->formatDate($date, $format);
}
?>
