<?php
/**
 * Installation Script for Photo Order Prints Platform
 * 
 * This script helps set up the database and initial configuration.
 * Delete this file after installation for security.
 */


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Photo Order Prints</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .install-box {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
        }
        h1 { margin-bottom: 20px; color: #333; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        input { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 1rem; }
        input:focus { outline: none; border-color: #667eea; }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
        }
        button:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3); }
        .message { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .success { background: #efe; color: #3c3; border: 1px solid #cfc; }
        .error { background: #fee; color: #c33; border: 1px solid #fcc; }
        .info { background: #e3f2fd; color: #1976d2; border: 1px solid #bbdefb; }
    </style>
</head>
<body>
    <div class="install-box">
        <h1>Installation</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dbHost = $_POST['db_host'] ?? 'localhost';
            $dbName = $_POST['db_name'] ?? 'photo_orders';
            $dbUser = $_POST['db_user'] ?? 'root';
            $dbPass = $_POST['db_pass'] ?? '';
            $baseUrl = $_POST['base_url'] ?? 'http://localhost';
            
            // Test database connection
            try {
                $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Create database if it doesn't exist
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // Select database
                $pdo->exec("USE `$dbName`");
                
                // Read and execute SQL file
                $sql = file_get_contents('database.sql');
                $sql = str_replace('CREATE DATABASE IF NOT EXISTS photo_orders', '-- Database already created', $sql);
                $sql = str_replace('USE photo_orders;', '-- Using selected database', $sql);
                
                $pdo->exec($sql);
                
                // Create config file
                $configContent = "<?php
// Database configuration
define('DB_HOST', '{$dbHost}');
define('DB_NAME', '{$dbName}');
define('DB_USER', '{$dbUser}');
define('DB_PASS', '{$dbPass}');

// Base URL configuration
define('BASE_URL', '{$baseUrl}');

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
    static \$conn = null;
    if (\$conn === null) {
        try {
            \$conn = new PDO(
                \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=utf8mb4\",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException \$e) {
            die(\"Database connection failed: \" . \$e->getMessage());
        }
    }
    return \$conn;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate unique code for clients
function generateUniqueCode(\$length = 12) {
    return strtoupper(substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', \$length)), 0, \$length));
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset(\$_SESSION[ADMIN_SESSION_KEY]) && \$_SESSION[ADMIN_SESSION_KEY] === true;
}

// Require admin login
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: admin_login.php');
        exit;
    }
}

// Format price
function formatPrice(\$price) {
    return '\$' . number_format(\$price, 2);
}

// Security: Sanitize input
function sanitize(\$data) {
    return htmlspecialchars(strip_tags(trim(\$data)));
}
?>";
                
                file_put_contents('config.php', $configContent);
                
                // Create uploads directory
                if (!file_exists('uploads')) {
                    mkdir('uploads', 0777, true);
                }
                
                echo '<div class="message success">';
                echo '<h2>Installation Successful!</h2>';
                echo '<p>The platform has been installed successfully.</p>';
                echo '<p><strong>Default Admin Credentials:</strong></p>';
                echo '<ul style="margin: 10px 0 10px 20px;">';
                echo '<li>Username: <code>admin</code></li>';
                echo '<li>Password: <code>admin123</code></li>';
                echo '</ul>';
                echo '<p style="color: #c33;"><strong>Please change the password immediately after login!</strong></p>';
                echo '<p style="margin-top: 20px;"><a href="admin_login.php" style="display: inline-block; background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;">Go to Admin Login</a></p>';
                echo '</div>';
                
                echo '<div class="message info">';
                echo '<p><strong>Important:</strong> Delete this install.php file for security purposes.</p>';
                echo '</div>';
                
            } catch (PDOException $e) {
                echo '<div class="message error">';
                echo '<h2>Installation Failed</h2>';
                echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '</div>';
            }
        } else {
        ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="db_host">Database Host</label>
                <input type="text" id="db_host" name="db_host" value="localhost" required>
            </div>
            
            <div class="form-group">
                <label for="db_name">Database Name</label>
                <input type="text" id="db_name" name="db_name" value="photo_orders" required>
            </div>
            
            <div class="form-group">
                <label for="db_user">Database Username</label>
                <input type="text" id="db_user" name="db_user" value="root" required>
            </div>
            
            <div class="form-group">
                <label for="db_pass">Database Password</label>
                <input type="password" id="db_pass" name="db_pass">
            </div>
            
            <div class="form-group">
                <label for="base_url">Base URL</label>
                <input type="url" id="base_url" name="base_url" value="http://localhost" required>
                <small style="color: #666; display: block; margin-top: 5px;">Full URL where the platform will be accessed</small>
            </div>
            
            <button type="submit">Install Platform</button>
        </form>
        
        <?php } ?>
    </div>
</body>
</html>

