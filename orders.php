<?php
require_once __DIR__ . '/auth.php';
require_login();

$user = current_user();
$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$order = null;

if ($orderId > 0) {
    $order = get_order_details($pdo, $orderId, $user['id']);

    if (!$order) {
        set_flash('error', 'Order not found.');
        redirect('orders.php');
    }
}

$orders = $order ? array() : get_orders_by_user($pdo, $user['id']);
$locationLine = '';

if ($order) {
    $locationLine = $order['shipping_city'] . ' - ' . $order['shipping_postal_code'];

    if (isset($order['shipping_state']) && $order['shipping_state'] !== '' && strtoupper($order['shipping_state']) !== 'N/A') {
        $locationLine = $order['shipping_city'] . ', ' . $order['shipping_state'] . ' - ' . $order['shipping_postal_code'];
    }
}

$pageTitle = $order ? 'Order Details' : 'Order History';
$activeNav = 'orders';
require_once __DIR__ . '/header.php';
?>
<?php if ($order): ?>
    <div class="admin-title">
        <div>
            <h1 class="section-title mb-1">Order #<?php echo e($order['id']); ?></h1>
            <p class="section-subtitle mb-0">Placed on <?php echo e(format_datetime($order['created_at'])); ?></p>
        </div>
        <a href="<?php echo e(base_url('orders.php')); ?>" class="btn btn-outline-dark">Back to Orders</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="panel-card p-4">
                <h2 class="h4 mb-4">Items</h2>
                <ul class="list-unstyled receipt-list mb-0">
                    <?php foreach ($order['items'] as $item): ?>
                        <li class="d-flex justify-content-between gap-3">
                            <div>
                                <strong><?php echo e($item['name']); ?></strong>
                                <div class="small text-muted">Qty: <?php echo e($item['quantity']); ?> x <?php echo e(format_price($item['price'])); ?></div>
                            </div>
                            <strong><?php echo e(format_price($item['price'] * $item['quantity'])); ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="panel-card p-4">
                <h2 class="h4 mb-3">Summary</h2>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Payment method</span>
                    <strong><?php echo e($order['payment_method']); ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Status</span>
                    <strong class="status-badge"><?php echo e($order['status']); ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <span class="text-muted">Total</span>
                    <strong><?php echo e(format_price($order['total_amount'])); ?></strong>
                </div>
                <h3 class="h6">Shipping Address</h3>
                <p class="mb-1"><?php echo e($order['customer_name']); ?></p>
                <p class="mb-1 text-muted"><?php echo e($order['customer_phone']); ?></p>
                <p class="mb-1 text-muted"><?php echo e($order['shipping_address']); ?></p>
                <p class="mb-0 text-muted"><?php echo e($locationLine); ?></p>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="admin-title">
        <div>
            <h1 class="section-title mb-1">Order History</h1>
            <p class="section-subtitle mb-0">Track every order you have placed with Furniture Shop.</p>
        </div>
    </div>

    <?php if (!$orders): ?>
        <div class="panel-card empty-state">
            <h2 class="h4 mb-2">No orders yet.</h2>
            <p class="text-muted mb-4">Your future purchases will show up here with receipts and delivery status.</p>
            <a class="btn btn-dark" href="<?php echo e(base_url('shop.php')); ?>">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="panel-card table-responsive p-3">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $row): ?>
                        <tr>
                            <td>#<?php echo e($row['id']); ?></td>
                            <td><?php echo e(format_datetime($row['created_at'])); ?></td>
                            <td><?php echo e(format_price($row['total_amount'])); ?></td>
                            <td><?php echo e($row['payment_method']); ?></td>
                            <td><span class="badge text-bg-light status-badge"><?php echo e($row['status']); ?></span></td>
                            <td class="text-end"><a href="<?php echo e(base_url('orders.php?id=' . $row['id'])); ?>" class="btn btn-sm btn-outline-dark">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?php require_once __DIR__ . '/footer.php'; ?>
