<?php
require_once 'config.php';
requireAdmin();

$db = getDB();
$orderId = $_GET['id'] ?? 0;

// Get order details
$stmt = $db->prepare("
    SELECT o.*, c.name, c.unique_code, c.email, c.phone
    FROM orders o
    JOIN clients c ON o.client_id = c.id
    WHERE o.id = ?
");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: admin_orders.php');
    exit;
}

// Get photos for this order
$photosStmt = $db->prepare("SELECT * FROM photos WHERE order_id = ? ORDER BY uploaded_at DESC");
$photosStmt->execute([$orderId]);
$photos = $photosStmt->fetchAll();

// Update order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = sanitize($_POST['status'] ?? '');
    $deposit = floatval($_POST['deposit_paid'] ?? 0);
    $notes = sanitize($_POST['notes'] ?? '');
    
    $stmt = $db->prepare("UPDATE orders SET status = ?, deposit_paid = ?, notes = ? WHERE id = ?");
    $stmt->execute([$status, $deposit, $notes, $orderId]);
    
    header('Location: admin_order_details.php?id=' . $orderId);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <nav class="admin-nav">
        <div class="nav-container">
            <h2>Photo Orders Admin</h2>
            <div class="nav-links">
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="admin_orders.php" class="active">Orders</a>
                <a href="admin_clients.php">Clients</a>
                <a href="admin_logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <a href="admin_orders.php" class="back-link">← Back to Orders</a>
        
        <h1>Order #<?php echo $order['id']; ?></h1>

        <div class="card">
            <h2>Client Information</h2>
            <div class="info-grid">
                <div>
                    <strong>Name:</strong> <?php echo htmlspecialchars($order['name']); ?>
                </div>
                <div>
                    <strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?>
                </div>
                <div>
                    <strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?>
                </div>
                <div>
                    <strong>Unique Code:</strong> <code><?php echo $order['unique_code']; ?></code>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Order Summary</h2>
            <div class="info-grid">
                <div>
                    <strong>Total Photos:</strong> <?php echo $order['total_photos']; ?>
                </div>
                <div>
                    <strong>Total Cost:</strong> <?php echo formatPrice($order['total_cost']); ?>
                </div>
                <div>
                    <strong>Deposit Paid:</strong> <?php echo formatPrice($order['deposit_paid']); ?>
                </div>
                <div>
                    <strong>Balance:</strong> <?php echo formatPrice($order['total_cost'] - $order['deposit_paid']); ?>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Update Order</h2>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="received" <?php echo $order['status'] === 'received' ? 'selected' : ''; ?>>Received</option>
                            <option value="printing" <?php echo $order['status'] === 'printing' ? 'selected' : ''; ?>>Printing</option>
                            <option value="shipping" <?php echo $order['status'] === 'shipping' ? 'selected' : ''; ?>>Shipping</option>
                            <option value="done" <?php echo $order['status'] === 'done' ? 'selected' : ''; ?>>Done</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="deposit_paid">Deposit Paid ($)</label>
                        <input type="number" id="deposit_paid" name="deposit_paid" step="0.01" value="<?php echo $order['deposit_paid']; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="4"><?php echo htmlspecialchars($order['notes']); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Update Order</button>
            </form>
        </div>

        <div class="card">
            <h2>Photos (<?php echo count($photos); ?>)</h2>
            <div class="photos-grid">
                <?php foreach ($photos as $photo): ?>
                    <div class="photo-item">
                        <img src="<?php echo BASE_URL . '/' . $photo['filepath']; ?>" alt="Photo">
                        <div class="photo-info">
                            <p><strong>Quantity:</strong> <?php echo $photo['quantity']; ?></p>
                            <p><strong>Uploaded:</strong> <?php echo date('M d, Y H:i', strtotime($photo['uploaded_at'])); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>

