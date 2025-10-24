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

if ($action === 'update_quantity') {
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if ($quantity < 1 || $quantity > 100) {
        echo json_encode(['success' => false, 'error' => 'Invalid quantity']);
        exit;
    }
    
    // Get order ID and check status
    $stmt = $db->prepare("SELECT order_id FROM photos WHERE id = ?");
    $stmt->execute([$photoId]);
    $photo = $stmt->fetch();
    
    if ($photo) {
        $orderId = $photo['order_id'];
        
        // Check order status
        $stmt = $db->prepare("SELECT status FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        if ($order && $order['status'] === 'printing') {
            echo json_encode(['success' => false, 'error' => 'Cannot modify quantities while order is in printing status']);
            exit;
        }
    
        // Update photo quantity
        $stmt = $db->prepare("UPDATE photos SET quantity = ? WHERE id = ?");
        $stmt->execute([$quantity, $photoId]);
        
        // Recalculate order total
        recalculateOrderTotal($db, $orderId);
        
        // Get updated totals
        $stmt = $db->prepare("SELECT total_photos, total_cost FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $updatedOrder = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'order' => [
                'total_photos' => $updatedOrder['total_photos'],
                'total_cost' => $updatedOrder['total_cost']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Photo not found']);
    }
    
} elseif ($action === 'delete') {
    // Get photo info before deleting
    $stmt = $db->prepare("SELECT order_id, filepath FROM photos WHERE id = ?");
    $stmt->execute([$photoId]);
    $photo = $stmt->fetch();
    
    if (!$photo) {
        echo json_encode(['success' => false, 'error' => 'Photo not found']);
        exit;
    }
    
    $orderId = $photo['order_id'];
    $filepath = $photo['filepath'];
    
    // Check order status
    $stmt = $db->prepare("SELECT status FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if ($order && $order['status'] === 'printing') {
        echo json_encode(['success' => false, 'error' => 'Cannot delete photos while order is in printing status']);
        exit;
    }
    
    // Delete file
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    
    // Delete from database
    $stmt = $db->prepare("DELETE FROM photos WHERE id = ?");
    $stmt->execute([$photoId]);
    
    // Recalculate order total
    recalculateOrderTotal($db, $orderId);
    
    // Get updated totals
    $stmt = $db->prepare("SELECT total_photos, total_cost FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $updatedOrder = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'order' => [
            'total_photos' => $updatedOrder['total_photos'],
            'total_cost' => $updatedOrder['total_cost']
        ]
    ]);
    
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

