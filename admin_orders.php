<?php
require_once 'config.php';
requireAdmin();

$db = getDB();

// Get all orders
$ordersStmt = $db->query("
    SELECT o.*, c.name, c.unique_code, c.email, c.phone
    FROM orders o
    JOIN clients c ON o.client_id = c.id
    ORDER BY o.created_at DESC
");
$orders = $ordersStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin</title>
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
        <h1>All Orders</h1>

        <div class="card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Client</th>
                            <th>Code</th>
                            <th>Contact</th>
                            <th>Photos</th>
                            <th>Total</th>
                            <th>Deposit</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['name']); ?></td>
                                <td><code><?php echo $order['unique_code']; ?></code></td>
                                <td>
                                    <?php if ($order['email']): ?>
                                        <a href="mailto:<?php echo $order['email']; ?>"><?php echo htmlspecialchars($order['email']); ?></a><br>
                                    <?php endif; ?>
                                    <?php if ($order['phone']): ?>
                                        <?php echo htmlspecialchars($order['phone']); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $order['total_photos']; ?></td>
                                <td><?php echo formatPrice($order['total_cost']); ?></td>
                                <td><?php echo formatPrice($order['deposit_paid']); ?></td>
                                <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td><a href="admin_order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm">Manage</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

