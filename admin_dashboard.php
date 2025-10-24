<?php
require_once 'config.php';
requireAdmin();

$db = getDB();

// Generate new client link
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_link'])) {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    
    if ($name) {
        $uniqueCode = generateUniqueCode();
        $stmt = $db->prepare("INSERT INTO clients (unique_code, name, email, phone) VALUES (?, ?, ?, ?)");
        $stmt->execute([$uniqueCode, $name, $email, $phone]);
        $clientId = $db->lastInsertId();
        
        // Create order for this client
        $stmt = $db->prepare("INSERT INTO orders (client_id) VALUES (?)");
        $stmt->execute([$clientId]);
        
        $success = "Client link generated successfully! Unique Code: $uniqueCode";
    }
}

// Get statistics
$statsStmt = $db->query("
    SELECT 
        COUNT(DISTINCT o.id) as total_orders,
        COUNT(DISTINCT c.id) as total_clients,
        SUM(o.total_photos) as total_photos,
        SUM(o.total_cost) as total_revenue
    FROM orders o
    LEFT JOIN clients c ON o.client_id = c.id
");
$stats = $statsStmt->fetch();

// Get recent orders
$ordersStmt = $db->query("
    SELECT o.*, c.name, c.unique_code, c.email, c.phone
    FROM orders o
    JOIN clients c ON o.client_id = c.id
    ORDER BY o.created_at DESC
    LIMIT 10
");
$recentOrders = $ordersStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Photo Orders</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <nav class="admin-nav">
        <div class="nav-container">
            <h2>Photo Orders Admin</h2>
            <div class="nav-links">
                <a href="admin_dashboard.php" class="active">Dashboard</a>
                <a href="admin_orders.php">Orders</a>
                <a href="admin_clients.php">Clients</a>
                <a href="admin_logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <h1>Dashboard</h1>

        <?php if (isset($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Orders</h3>
                <p class="stat-number"><?php echo $stats['total_orders']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Clients</h3>
                <p class="stat-number"><?php echo $stats['total_clients']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Photos</h3>
                <p class="stat-number"><?php echo $stats['total_photos']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <p class="stat-number"><?php echo formatPrice($stats['total_revenue']); ?></p>
            </div>
        </div>

        <!-- Generate New Client Link -->
        <div class="card">
            <h2>Generate New Client Link</h2>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Client Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                </div>
                <button type="submit" name="generate_link" class="btn btn-primary">Generate Link</button>
            </form>
        </div>

        <!-- Recent Orders -->
        <div class="card">
            <h2>Recent Orders</h2>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Client</th>
                            <th>Code</th>
                            <th>Photos</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['name']); ?></td>
                                <td><code><?php echo $order['unique_code']; ?></code></td>
                                <td><?php echo $order['total_photos']; ?></td>
                                <td><?php echo formatPrice($order['total_cost']); ?></td>
                                <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                <td><a href="admin_order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

