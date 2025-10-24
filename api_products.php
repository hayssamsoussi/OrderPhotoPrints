<?php
require_once 'config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$uniqueCode = $_GET['code'] ?? '';

if (!$uniqueCode) {
    echo json_encode(['success' => false, 'error' => 'Invalid access code']);
    exit;
}

$db = getDB();

// Get client info
$stmt = $db->prepare("SELECT * FROM clients WHERE unique_code = ?");
$stmt->execute([$uniqueCode]);
$client = $stmt->fetch();

if (!$client) {
    echo json_encode(['success' => false, 'error' => 'Invalid access code']);
    exit;
}

// Get order
$stmt = $db->prepare("SELECT * FROM orders WHERE client_id = ?");
$stmt->execute([$client['id']]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['success' => false, 'error' => 'Order not found']);
    exit;
}

$orderId = $order['id'];

if ($action === 'get_products') {
    // Get all active products
    $stmt = $db->prepare("SELECT * FROM products WHERE is_active = TRUE ORDER BY type, name");
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'products' => $products]);
    
} elseif ($action === 'add_product') {
    // Check if order is in printing status
    if ($order['status'] === 'printing') {
        echo json_encode(['success' => false, 'error' => 'Cannot add products while order is in printing status']);
        exit;
    }
    
    $productId = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if (!$productId || $quantity < 1) {
        echo json_encode(['success' => false, 'error' => 'Invalid product or quantity']);
        exit;
    }
    
    // Get product details
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND is_active = TRUE");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode(['success' => false, 'error' => 'Product not found']);
        exit;
    }
    
    // Add product to order
    $stmt = $db->prepare("INSERT INTO order_products (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt->execute([$orderId, $productId, $quantity, $product['price']]);
    
    // Recalculate order total
    recalculateOrderTotal($db, $orderId);
    
    echo json_encode(['success' => true]);
    
} elseif ($action === 'remove_product') {
    // Check if order is in printing status
    if ($order['status'] === 'printing') {
        echo json_encode(['success' => false, 'error' => 'Cannot remove products while order is in printing status']);
        exit;
    }
    
    $orderProductId = intval($_GET['order_product_id'] ?? 0);
    
    if (!$orderProductId) {
        echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
        exit;
    }
    
    // Remove product from order
    $stmt = $db->prepare("DELETE FROM order_products WHERE id = ? AND order_id = ?");
    $stmt->execute([$orderProductId, $orderId]);
    
    // Recalculate order total
    recalculateOrderTotal($db, $orderId);
    
    echo json_encode(['success' => true]);
    
} elseif ($action === 'get_order_products') {
    // Get products added to this order
    $stmt = $db->prepare("
        SELECT op.*, p.name, p.description, p.type 
        FROM order_products op
        JOIN products p ON op.product_id = p.id
        WHERE op.order_id = ?
        ORDER BY op.added_at DESC
    ");
    $stmt->execute([$orderId]);
    $orderProducts = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'products' => $orderProducts]);
    
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

// Helper function to recalculate order total
function recalculateOrderTotal($db, $orderId) {
    // Calculate total from photos
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(quantity * ?), 0) as photo_cost
        FROM photos 
        WHERE order_id = ?
    ");
    $stmt->execute([PRICE_PER_PHOTO, $orderId]);
    $photoCost = $stmt->fetch()['photo_cost'];
    
    // Calculate total from order products
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(quantity * price), 0) as product_cost
        FROM order_products 
        WHERE order_id = ?
    ");
    $stmt->execute([$orderId]);
    $productCost = $stmt->fetch()['product_cost'];
    
    // Calculate total from photo options
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(p.quantity * po.extra_cost), 0) as options_cost
        FROM photos p
        LEFT JOIN photo_options po ON p.id = po.photo_id
        WHERE p.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $optionsCost = $stmt->fetch()['options_cost'];
    
    $totalCost = $photoCost + $productCost + $optionsCost;
    
    // Update order
    $stmt = $db->prepare("
        UPDATE orders 
        SET total_photos = (SELECT COUNT(*) FROM photos WHERE order_id = ?),
            total_cost = ?
        WHERE id = ?
    ");
    $stmt->execute([$orderId, $totalCost, $orderId]);
}
?>

