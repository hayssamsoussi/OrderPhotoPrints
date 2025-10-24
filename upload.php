<?php
require_once 'config.php';

$uniqueCode = $_GET['code'] ?? '';

if (!$uniqueCode) {
    die('Invalid access code');
}

$db = getDB();

// Get client info
$stmt = $db->prepare("SELECT * FROM clients WHERE unique_code = ?");
$stmt->execute([$uniqueCode]);
$client = $stmt->fetch();

if (!$client) {
    die('Invalid access code');
}

// Get or create order
$stmt = $db->prepare("SELECT * FROM orders WHERE client_id = ?");
$stmt->execute([$client['id']]);
$order = $stmt->fetch();

if (!$order) {
    $stmt = $db->prepare("INSERT INTO orders (client_id) VALUES (?)");
    $stmt->execute([$client['id']]);
    $orderId = $db->lastInsertId();
    $order = ['id' => $orderId, 'total_photos' => 0, 'total_cost' => 0, 'status' => 'received'];
} else {
    $orderId = $order['id'];
}

// Get order status
$status = $order['status'];

// Get photos
$stmt = $db->prepare("SELECT * FROM photos WHERE order_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$orderId]);
$photos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Photos - <?php echo htmlspecialchars($client['name']); ?></title>
    <link rel="stylesheet" href="assets/css/client.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Photo Order Portal</h1>
            <p>Welcome, <?php echo htmlspecialchars($client['name']); ?></p>
        </header>

        <!-- Order Status -->
        <div class="status-section">
            <h2>Order Status</h2>
            <div class="status-bar">
                <div class="status-step <?php echo $status === 'received' ? 'active' : ''; ?>">
                    <div class="status-dot"></div>
                    <span>Received</span>
                </div>
                <div class="status-step <?php echo $status === 'printing' ? 'active' : ''; ?>">
                    <div class="status-dot"></div>
                    <span>Printing</span>
                </div>
                <div class="status-step <?php echo $status === 'shipping' ? 'active' : ''; ?>">
                    <div class="status-dot"></div>
                    <span>Shipping</span>
                </div>
                <div class="status-step <?php echo $status === 'done' ? 'active' : ''; ?>">
                    <div class="status-dot"></div>
                    <span>Done</span>
                </div>
            </div>
        </div>

        <!-- Upload Section -->
        <div class="upload-section">
            <h2>Upload Photos</h2>
            <div class="upload-area" id="uploadArea">
                <input type="file" id="fileInput" multiple accept="image/*" style="display: none;">
                <div class="upload-placeholder">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    <p>Click or drag photos here to upload</p>
                    <p class="upload-hint">Supports JPG, PNG, GIF (max 10MB each)</p>
                </div>
            </div>
            <div id="uploadProgress" class="upload-progress" style="display: none;">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <p id="progressText">Uploading...</p>
            </div>
        </div>

        <!-- Photos Grid -->
        <div class="photos-section">
            <h2>Your Photos <span id="totalPhotos">(<?php echo count($photos); ?>)</span></h2>
            <div id="photosGrid" class="photos-grid">
                <?php foreach ($photos as $photo): ?>
                    <div class="photo-card" data-photo-id="<?php echo $photo['id']; ?>">
                        <div class="photo-image">
                            <img src="<?php echo BASE_URL . '/' . $photo['filepath']; ?>" alt="Photo">
                            <button class="delete-btn" onclick="deletePhoto(<?php echo $photo['id']; ?>)">✕</button>
                        </div>
                        <div class="photo-controls">
                            <label>Quantity:</label>
                            <input type="number" class="quantity-input" value="<?php echo $photo['quantity']; ?>" 
                                   min="1" max="100" onchange="updateQuantity(<?php echo $photo['id']; ?>, this.value)">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="summary-section">
            <h2>Order Summary</h2>
            <div class="summary-box">
                <div class="summary-row">
                    <span>Total Photos:</span>
                    <strong id="summaryPhotos"><?php echo $order['total_photos']; ?></strong>
                </div>
                <div class="summary-row">
                    <span>Price per Photo:</span>
                    <strong><?php echo formatPrice(PRICE_PER_PHOTO); ?></strong>
                </div>
                <div class="summary-row total">
                    <span>Total Cost:</span>
                    <strong id="summaryTotal"><?php echo formatPrice($order['total_cost']); ?></strong>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Pass unique code to JavaScript
        const UNIQUE_CODE = '<?php echo $uniqueCode; ?>';
    </script>
    <script src="assets/js/client.js"></script>
</body>
</html>

