<?php
require_once __DIR__ . '/../auth.php';
require_admin();

$stats = get_dashboard_stats($pdo);
$categorySales = get_category_sales($pdo);
$recentOrdersStmt = $pdo->query('SELECT o.*, u.name AS user_name
                                 FROM orders o
                                 INNER JOIN users u ON u.id = o.user_id
                                 ORDER BY o.created_at DESC
                                 LIMIT 5');
$recentOrders = $recentOrdersStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Admin Dashboard';
$activeNav = 'dashboard';
$isAdminPage = true;
require_once __DIR__ . '/../header.php';
?>
<div class="admin-title">
    <div>
        <h1 class="section-title mb-1">Dashboard</h1>
        <p class="section-subtitle mb-0">Track key activity across users, orders, and sales.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card p-4">
            <span class="badge-soft mb-3 d-inline-block">Users</span>
            <h2 class="display-6 mb-0"><?php echo e($stats['total_users']); ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card p-4">
            <span class="badge-soft mb-3 d-inline-block">Orders</span>
            <h2 class="display-6 mb-0"><?php echo e($stats['total_orders']); ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card p-4">
            <span class="badge-soft mb-3 d-inline-block">Sales</span>
            <h2 class="display-6 mb-0"><?php echo e(format_price($stats['total_sales'])); ?></h2>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="panel-card table-responsive p-3 h-100">
            <div class="p-2">
                <h2 class="h4 mb-1">Recent Orders</h2>
                <p class="text-muted mb-3">Latest transactions placed on the store.</p>
            </div>
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$recentOrders): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No orders available yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>#<?php echo e($order['id']); ?></td>
                                <td><?php echo e($order['user_name']); ?></td>
                                <td><?php echo e(format_price($order['total_amount'])); ?></td>
                                <td><?php echo e($order['payment_method']); ?></td>
                                <td><span class="badge text-bg-light status-badge"><?php echo e($order['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel-card table-responsive p-3 h-100">
            <div class="p-2">
                <h2 class="h4 mb-1">Category-wise Sales</h2>
                <p class="text-muted mb-3">Revenue grouped by product category.</p>
            </div>
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="text-end">Sales</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorySales as $sale): ?>
                        <tr>
                            <td><?php echo e($sale['name']); ?></td>
                            <td class="text-end"><?php echo e(format_price($sale['total_sales'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../footer.php'; ?>
