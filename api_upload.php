<?php
require_once 'config.php';

header('Content-Type: application/json');

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

// Handle file upload
if (!isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];

// Validate file
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Upload error: ' . $file['error']]);
    exit;
}

if ($file['size'] > MAX_FILE_SIZE) {
    echo json_encode(['success' => false, 'error' => 'File too large']);
    exit;
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ALLOWED_EXTENSIONS)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type']);
    exit;
}

// Generate unique filename
$filename = uniqid('photo_', true) . '.' . $ext;
$filepath = UPLOAD_DIR . $filename;

// Create upload directory if needed
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save file']);
    exit;
}

// Save to database
$relativePath = 'uploads/' . $filename;
$stmt = $db->prepare("INSERT INTO photos (order_id, filename, original_filename, filepath, quantity) VALUES (?, ?, ?, ?, 1)");
$stmt->execute([$orderId, $filename, $file['name'], $relativePath]);
$photoId = $db->lastInsertId();

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
    'photo' => [
        'id' => $photoId,
        'filename' => $filename,
        'filepath' => $relativePath,
        'quantity' => 1
    ],
    'order' => [
        'total_photos' => $updatedOrder['total_photos'],
        'total_cost' => $updatedOrder['total_cost']
    ]
]);
?>

