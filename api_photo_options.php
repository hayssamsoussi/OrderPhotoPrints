<?php
require_once 'config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$photoId = intval($_GET['id'] ?? 0);

if (!$photoId) {
    echo json_encode(['success' => false, 'error' => 'Invalid photo ID']);
    exit;
}

$db = getDB();

// Get photo order ID
$stmt = $db->prepare("SELECT order_id FROM photos WHERE id = ?");
$stmt->execute([$photoId]);
$photo = $stmt->fetch();

if (!$photo) {
    echo json_encode(['success' => false, 'error' => 'Photo not found']);
    exit;
}

$orderId = $photo['order_id'];

// Check order status
$stmt = $db->prepare("SELECT status FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if ($order && $order['status'] === 'printing') {
    echo json_encode(['success' => false, 'error' => 'Cannot modify options while order is in printing status']);
    exit;
}

if ($action === 'update_option') {
    $option = $_GET['option'] ?? '';
    $value = $_GET['value'] ?? '';
    
    if (!$option) {
        echo json_encode(['success' => false, 'error' => 'Invalid option']);
        exit;
    }
    
    // Get or create photo options
    $stmt = $db->prepare("SELECT * FROM photo_options WHERE photo_id = ?");
    $stmt->execute([$photoId]);
    $photoOptions = $stmt->fetch();
    
    if (!$photoOptions) {
        // Create new photo options record
        $stmt = $db->prepare("INSERT INTO photo_options (photo_id) VALUES (?)");
        $stmt->execute([$photoId]);
        $photoOptions = ['photo_id' => $photoId];
    }
    
    // Define option costs
    $optionCosts = [
        'has_frame' => 5.00,
        'has_woodboard' => 8.00,
        'bigger_size' => 3.00
    ];
    
    // Update the specific option
    $fieldName = $option;
    $boolValue = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    
    // For checkboxes, convert to boolean
    if ($value === 'true' || $value === '1' || $value === true) {
        $boolValue = true;
    } else {
        $boolValue = false;
    }
    
    // Update photo options
    $stmt = $db->prepare("UPDATE photo_options SET $fieldName = ? WHERE photo_id = ?");
    $stmt->execute([$boolValue ? 1 : 0, $photoId]);
    
    // Calculate extra cost based on selected options
    $extraCost = 0;
    $stmt = $db->prepare("SELECT * FROM photo_options WHERE photo_id = ?");
    $stmt->execute([$photoId]);
    $currentOptions = $stmt->fetch();
    
    if ($currentOptions) {
        if ($currentOptions['has_frame']) $extraCost += $optionCosts['has_frame'];
        if ($currentOptions['has_woodboard']) $extraCost += $optionCosts['has_woodboard'];
        if ($currentOptions['bigger_size']) $extraCost += $optionCosts['bigger_size'];
    }
    
    // Update extra cost
    $stmt = $db->prepare("UPDATE photo_options SET extra_cost = ? WHERE photo_id = ?");
    $stmt->execute([$extraCost, $photoId]);
    
    // Recalculate order total
    recalculateOrderTotal($db, $orderId);
    
    echo json_encode(['success' => true]);
    
} elseif ($action === 'get_options') {
    // Get photo options
    $stmt = $db->prepare("SELECT * FROM photo_options WHERE photo_id = ?");
    $stmt->execute([$photoId]);
    $photoOptions = $stmt->fetch();
    
    if (!$photoOptions) {
        $photoOptions = [
            'has_frame' => false,
            'has_woodboard' => false,
            'bigger_size' => false,
            'extra_cost' => 0
        ];
    }
    
    echo json_encode(['success' => true, 'options' => $photoOptions]);
    
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

