<?php
require_once 'config.php';
requireAdmin();

$db = getDB();

// Get all clients
$clientsStmt = $db->query("
    SELECT c.*, o.id as order_id, o.status, o.total_photos, o.total_cost
    FROM clients c
    LEFT JOIN orders o ON c.id = o.client_id
    ORDER BY c.created_at DESC
");
$clients = $clientsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clients - Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <nav class="admin-nav">
        <div class="nav-container">
            <h2>Photo Orders Admin</h2>
            <div class="nav-links">
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="admin_orders.php">Orders</a>
                <a href="admin_clients.php" class="active">Clients</a>
                <a href="admin_logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <h1>All Clients</h1>

        <div class="card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Unique Code</th>
                            <th>Upload Link</th>
                            <th>Order Status</th>
                            <th>Photos</th>
                            <th>Total</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($client['name']); ?></td>
                                <td><?php echo htmlspecialchars($client['email']); ?></td>
                                <td><?php echo htmlspecialchars($client['phone']); ?></td>
                                <td><code><?php echo $client['unique_code']; ?></code></td>
                                <td><a href="upload.php?code=<?php echo $client['unique_code']; ?>" target="_blank">View Link</a></td>
                                <td>
                                    <?php if ($client['order_id']): ?>
                                        <span class="status-badge status-<?php echo $client['status']; ?>"><?php echo ucfirst($client['status']); ?></span>
                                    <?php else: ?>
                                        <span class="status-badge">No Order</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $client['total_photos'] ?? 0; ?></td>
                                <td><?php echo formatPrice($client['total_cost'] ?? 0); ?></td>
                                <td><?php echo date('M d, Y', strtotime($client['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

