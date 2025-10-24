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
    
    // Update photo quantity
    $stmt = $db->prepare("UPDATE photos SET quantity = ? WHERE id = ?");
    $stmt->execute([$quantity, $photoId]);
    
    // Get order ID
    $stmt = $db->prepare("SELECT order_id FROM photos WHERE id = ?");
    $stmt->execute([$photoId]);
    $photo = $stmt->fetch();
    
    if ($photo) {
        $orderId = $photo['order_id'];
        
        // Update order totals
        $stmt = $db->prepare("
            UPDATE orders 
            SET total_photos = (SELECT COUNT(*) FROM photos WHERE order_id = ?),
                total_cost = (SELECT SUM(quantity * ?) FROM photos WHERE order_id = ?)
            WHERE id = ?
        ");
        $stmt->execute([$orderId, PRICE_PER_PHOTO, $orderId, $orderId]);
        
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
    
    // Delete file
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    
    // Delete from database
    $stmt = $db->prepare("DELETE FROM photos WHERE id = ?");
    $stmt->execute([$photoId]);
    
    // Update order totals
    $stmt = $db->prepare("
        UPDATE orders 
        SET total_photos = (SELECT COUNT(*) FROM photos WHERE order_id = ?),
            total_cost = (SELECT SUM(quantity * ?) FROM photos WHERE order_id = ?)
        WHERE id = ?
    ");
    $stmt->execute([$orderId, PRICE_PER_PHOTO, $orderId, $orderId]);
    
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
?>

