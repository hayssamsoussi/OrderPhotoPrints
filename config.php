<?php
// Database configuration
define('DB_HOST', 'jo0a.your-database.de');
define('DB_NAME', 'puzzleg_photos');
define('DB_USER', 'puzzleg_1');
define('DB_PASS', 'a8~Rf_*kip*r');

// Base URL configuration
define('BASE_URL', 'http://photos.puzzleslb.com');

// Admin configuration
define('ADMIN_SESSION_KEY', 'admin_logged_in');

// File upload configuration
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Price configuration
define('PRICE_PER_PHOTO', 0.50); // Price per photo (adjust as needed)

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// Database connection
function getDB() {
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $conn;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate unique code for clients
function generateUniqueCode($length = 12) {
    return strtoupper(substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', $length)), 0, $length));
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION[ADMIN_SESSION_KEY]) && $_SESSION[ADMIN_SESSION_KEY] === true;
}

// Require admin login
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: admin_login.php');
        exit;
    }
}

// Format price
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

// Security: Sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>

