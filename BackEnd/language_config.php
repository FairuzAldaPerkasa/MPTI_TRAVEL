<?php
/**
 * Language and Localization Configuration
 *
 * This class manages all multi-language text (translations) and localization settings,
 * such as currency and date formatting. It detects the user's requested language
 * via HTTP headers and provides a centralized system for retrieving translated strings.
 *
 * @version 1.1
 * @author MPTI_TRAVEL
 * @filepath c:\xampp\htdocs\MPTI_TRAVEL\BackEnd\language_config.php
 */
class LanguageConfig {
    // The currently active language, detected from the client's request.
    private $current_language = 'id';
    
    // The default language to use if a translation is not found in the current language.
    private $fallback_language = 'id';
    
    /**
     * A multi-dimensional array holding all translation strings.
     * Organized by language code (e.g., 'id', 'en') and then by category (e.g., 'errors', 'package').
     * @var array
     */
    private $translations = [
        // --- Indonesian Translations ---
        'id' => [
            'errors' => [
                'invalid_package_id' => 'ID paket tidak valid',
                'database_connection_failed' => 'Koneksi database gagal',
                'package_not_found' => 'Paket tidak ditemukan',
                'query_preparation_failed' => 'Persiapan query gagal',
                'query_execution_failed' => 'Eksekusi query gagal',
                'unknown_error' => 'Terjadi kesalahan tidak dikenal'
            ],
            'messages' => [
                'packages_loaded' => 'Daftar paket berhasil dimuat.',
                'package_detail_loaded' => 'Detail paket berhasil dimuat.'
            ],
            'currency' => [
                'prefix' => 'Rp ',
                'suffix' => '',
                'format' => 'id-ID' // Locale for number formatting
            ],
            'package' => [
                'default_description' => 'Deskripsi paket akan segera tersedia',
                'price_on_request' => 'Hubungi untuk harga',
                'minimum_participants' => 'per orang (min. 2 pax)',
                'duration_days' => 'hari',
                'duration_nights' => 'malam',
                'included_default' => [
                    'Transportasi AC',
                    'Tiket masuk wisata',
                    'Makan sesuai program',
                    'Guide berpengalaman'
                ],
                'excluded_default' => [
                    'Tiket pesawat',
                    'Pengeluaran pribadi',
                    'Tips guide (opsional)'
                ],                'highlights_default' => [
                    'Pengalaman wisata tak terlupakan',
                    'Destinasi populer',
                    'Fasilitas nyaman'
                ],
                'photo_caption_main' => 'Foto Utama',
                'photo_caption_gallery' => 'Galeri',
                'photo_caption_borobudur' => 'Candi Borobudur',
                'photo_caption_prambanan' => 'Candi Prambanan'
            ]
        ],
        // --- English Translations ---
        'en' => [
            'errors' => [
                'invalid_package_id' => 'Invalid package ID',
                'database_connection_failed' => 'Database connection failed',
                'package_not_found' => 'Package not found',
                'query_preparation_failed' => 'Query preparation failed',
                'query_execution_failed' => 'Query execution failed',
                'unknown_error' => 'An unknown error occurred'
            ],
            'messages' => [
                'packages_loaded' => 'Package list loaded successfully.',
                'package_detail_loaded' => 'Package detail loaded successfully.'
            ],
            'currency' => [
                'prefix' => 'IDR ',
                'suffix' => '',
                'format' => 'en-US' // Locale for number formatting
            ],
            'package' => [
                'default_description' => 'Package description will be available soon',
                'price_on_request' => 'Contact for price',
                'minimum_participants' => 'per person (min. 2 pax)',
                'duration_days' => 'days',
                'duration_nights' => 'nights',
                'included_default' => [
                    'AC Transportation',
                    'Attraction tickets',
                    'Meals as per program',
                    'Experienced guide'
                ],
                'excluded_default' => [
                    'Flight tickets',
                    'Personal expenses',
                    'Guide tips (optional)'
                ],                'highlights_default' => [
                    'Unforgettable travel experience',
                    'Popular destinations',
                    'Comfortable facilities'
                ],
                'photo_caption_main' => 'Main Photo',
                'photo_caption_gallery' => 'Gallery',
                'photo_caption_borobudur' => 'Borobudur Temple',
                'photo_caption_prambanan' => 'Prambanan Temple'
            ]
        ]
    ];

    /**
     * Constructor: Initializes the language configuration.
     * It checks for a language preference from the 'X-Language' HTTP header
     * and sets the current language accordingly.
     */
    public function __construct() {
        // Check for a custom language header sent from the client.
        if (isset($_SERVER['HTTP_X_LANGUAGE'])) {
            $lang = strtolower(trim($_SERVER['HTTP_X_LANGUAGE']));
            // If the requested language exists in our translations, use it.
            if (array_key_exists($lang, $this->translations)) {
                $this->current_language = $lang;
            }
        }
    }

    /**
     * Retrieves a translated string for a given key.
     *
     * @param string $key The key for the translation, using dot notation (e.g., 'errors.package_not_found').
     * @return string The translated string or the key itself if not found.
     */
    public function translate($key) {
        $keys = explode('.', $key);
        $temp = $this->translations[$this->current_language];

        // Traverse the translation array using the key parts.
        foreach ($keys as $k) {
            if (isset($temp[$k])) {
                $temp = $temp[$k];
            } else {
                // If not found in the current language, try the fallback language.
                return $this->translateFallback($key);
            }
        }
        return $temp;
    }

    /**
     * Fallback mechanism to retrieve a translation from the default language.
     *
     * @param string $key The key for the translation.
     * @return string The translated string from the fallback language, or the key itself.
     */
    private function translateFallback($key) {
        $keys = explode('.', $key);
        $temp = $this->translations[$this->fallback_language];
        foreach ($keys as $k) {
            if (isset($temp[$k])) {
                $temp = $temp[$k];
            } else {
                // If the key is not found even in the fallback, return the key itself.
                return $key;
            }
        }
        return $temp;
    }

    /**
     * Formats a numeric value as currency based on the current language settings.
     *
     * @param float|null $number The number to format.
     * @return string The formatted currency string (e.g., "Rp 1.500.000") or a default text if the number is null.
     */
    public function formatCurrency($number) {
        if ($number === null || !is_numeric($number)) {
            return $this->translate('package.price_on_request');
        }

        $config = $this->translations[$this->current_language]['currency'];
        $formatted_number = '';

        try {
            // First, check if the Intl extension and NumberFormatter class are available.
            if (class_exists('NumberFormatter')) {
                // Use PHP's NumberFormatter for locale-aware currency formatting.
                $formatter = new NumberFormatter($config['format'], NumberFormatter::DECIMAL);
                // Check if the formatter was created successfully before using it.
                if ($formatter) {
                    $formatted_number = $formatter->format($number);
                } else {
                    // Throw an exception to trigger the fallback if formatter creation fails.
                    throw new Exception('NumberFormatter creation failed.');
                }
            } else {
                // Throw an exception to use the fallback if the class doesn't exist.
                throw new Exception('NumberFormatter class not found.');
            }
        } catch (Throwable $e) {
            // If Intl fails for any reason (not installed, locale not supported, etc.),
            // provide a safe fallback using number_format().
            $decimal_separator = ($this->current_language === 'id') ? ',' : '.';
            $thousand_separator = ($this->current_language === 'id') ? '.' : ',';
            $formatted_number = number_format((float)$number, 0, $decimal_separator, $thousand_separator);
        }

        return $config['prefix'] . $formatted_number . $config['suffix'];
    }

    /**
     * Retrieves the currently set language.
     *
     * @return string The current language code (e.g., 'id').
     */
    public function getCurrentLanguage() {
        return $this->current_language;
    }
}
