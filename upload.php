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

// Get photos with options
$stmt = $db->prepare("
    SELECT p.*, po.has_frame, po.has_woodboard, po.bigger_size, po.extra_cost
    FROM photos p
    LEFT JOIN photo_options po ON p.id = po.photo_id
    WHERE p.order_id = ? 
    ORDER BY p.uploaded_at DESC
");
$stmt->execute([$orderId]);
$photos = $stmt->fetchAll();

// Get products
$stmt = $db->prepare("SELECT * FROM products WHERE is_active = TRUE ORDER BY type, name");
$stmt->execute();
$products = $stmt->fetchAll();

// Get order products
$stmt = $db->prepare("
    SELECT op.*, p.name, p.description, p.type 
    FROM order_products op
    JOIN products p ON op.product_id = p.id
    WHERE op.order_id = ?
    ORDER BY op.added_at DESC
");
$stmt->execute([$orderId]);
$orderProducts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Photos - <?php echo htmlspecialchars($client['name']); ?></title>
    <link rel="stylesheet" href="assets/css/client.css?v=<?php echo filemtime('style.css'); ?>">
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

        <!-- Products Section -->
        <?php if ($status !== 'printing'): ?>
        <div class="products-section">
            <h2>Add Products to Order</h2>
            <div class="products-grid" id="productsGrid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card" data-product-id="<?php echo $product['id']; ?>">
                        <div class="product-icon">
                            <?php if ($product['type'] === 'album'): ?>
                                📷
                            <?php elseif ($product['type'] === 'box'): ?>
                                📦
                            <?php elseif ($product['type'] === 'frame'): ?>
                                🖼️
                            <?php else: ?>
                                🎁
                            <?php endif; ?>
                        </div>
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                        <button class="add-product-btn" onclick="addProduct(<?php echo $product['id']; ?>)">
                            Add to Order
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Added Products -->
        <?php if (!empty($orderProducts)): ?>
        <div class="added-products-section">
            <h2>Products in Order</h2>
            <div class="added-products-list" id="addedProductsList">
                <?php foreach ($orderProducts as $orderProduct): ?>
                    <div class="added-product-item" data-order-product-id="<?php echo $orderProduct['id']; ?>">
                        <span class="product-name"><?php echo htmlspecialchars($orderProduct['name']); ?></span>
                        <span class="product-quantity">Qty: <?php echo $orderProduct['quantity']; ?></span>
                        <span class="product-price"><?php echo formatPrice($orderProduct['price'] * $orderProduct['quantity']); ?></span>
                        <?php if ($status !== 'printing'): ?>
                            <button class="remove-product-btn" onclick="removeProduct(<?php echo $orderProduct['id']; ?>)">Remove</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Upload Section -->
        <div class="upload-section">
            <h2>Upload Photos</h2>
            <?php if ($status === 'printing'): ?>
                <div class="upload-disabled">
                    <p>📸 Order is currently in printing status. Uploads are disabled.</p>
                </div>
            <?php else: ?>
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
            <?php endif; ?>
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
                            <?php if ($status !== 'printing'): ?>
                                <button class="delete-btn" onclick="deletePhoto(<?php echo $photo['id']; ?>)">✕</button>
                            <?php endif; ?>
                        </div>
                        <div class="photo-controls">
                            <div class="control-row">
                                <label>Quantity:</label>
                                <input type="number" class="quantity-input" value="<?php echo $photo['quantity']; ?>" 
                                       min="1" max="100" <?php echo $status === 'printing' ? 'disabled' : ''; ?> 
                                       onchange="updateQuantity(<?php echo $photo['id']; ?>, this.value)">
                            </div>
                            <?php if ($status !== 'printing'): ?>
                            <div class="photo-options">
                                <label class="options-label">Options:</label>
                                <div class="options-grid">
                                    <label class="option-item">
                                        <input type="checkbox" <?php echo $photo['has_frame'] ? 'checked' : ''; ?> 
                                               onchange="updatePhotoOption(<?php echo $photo['id']; ?>, 'has_frame', this.checked)">
                                        🖼️ Frame (+$5)
                                    </label>
                                    <label class="option-item">
                                        <input type="checkbox" <?php echo $photo['has_woodboard'] ? 'checked' : ''; ?> 
                                               onchange="updatePhotoOption(<?php echo $photo['id']; ?>, 'has_woodboard', this.checked)">
                                        🪵 Woodboard (+$8)
                                    </label>
                                    <label class="option-item">
                                        <input type="checkbox" <?php echo $photo['bigger_size'] ? 'checked' : ''; ?> 
                                               onchange="updatePhotoOption(<?php echo $photo['id']; ?>, 'bigger_size', this.checked)">
                                        📏 Bigger Size (+$3)
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>
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
                <?php if (!empty($orderProducts)): ?>
                <div class="summary-row">
                    <span>Products:</span>
                    <strong id="summaryProducts"><?php 
                        $productTotal = 0;
                        foreach ($orderProducts as $op) {
                            $productTotal += $op['price'] * $op['quantity'];
                        }
                        echo formatPrice($productTotal);
                    ?></strong>
                </div>
                <?php endif; ?>
                <div class="summary-row total">
                    <span>Total Cost:</span>
                    <strong id="summaryTotal"><?php echo formatPrice($order['total_cost']); ?></strong>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Pass unique code and order status to JavaScript
        const UNIQUE_CODE = '<?php echo $uniqueCode; ?>';
        const ORDER_STATUS = '<?php echo $status; ?>';
    </script>
    <script src="assets/js/client.js?v=<?php echo filemtime('style.css'); ?>"></script>
</body>
</html>

