<?php
require_once __DIR__ . '/auth.php';
require_login();

$user = current_user();
$orderId = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_SESSION['last_order_id']) ? (int) $_SESSION['last_order_id'] : 0);
$order = $orderId > 0 ? get_order_details($pdo, $orderId, $user['id']) : null;

if (!$order) {
    set_flash('error', 'Order receipt not found.');
    redirect('orders.php');
}

unset($_SESSION['last_order_id']);

$locationLine = $order['shipping_city'] . ' - ' . $order['shipping_postal_code'];

if (isset($order['shipping_state']) && $order['shipping_state'] !== '' && strtoupper($order['shipping_state']) !== 'N/A') {
    $locationLine = $order['shipping_city'] . ', ' . $order['shipping_state'] . ' - ' . $order['shipping_postal_code'];
}

$pageTitle = 'Order Success';
$activeNav = 'orders';
require_once __DIR__ . '/header.php';
?>
<section class="hero-section hero-simple mb-5">
    <div class="row g-4 align-items-center">
        <div class="col-lg-8">
            <span class="hero-chip"><i class="bi bi-check-circle"></i> Order placed successfully</span>
            <h1 class="display-6 fw-bold mb-3">Your order has been confirmed.</h1>
            <p class="mb-0 text-white-50">Here is your receipt with ordered items, payment method, and delivery details.</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <a class="btn btn-light" href="<?php echo e(base_url('orders.php')); ?>">View Order History</a>
        </div>
    </div>
</section>

<div class="panel-card p-4 p-lg-5">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h2 class="h3 mb-1">Order Receipt</h2>
            <p class="text-muted mb-0">Order ID: #<?php echo e($order['id']); ?> | <?php echo e(format_datetime($order['created_at'])); ?></p>
        </div>
        <div class="text-lg-end">
            <div class="badge bg-success-subtle text-success-emphasis status-badge px-3 py-2"><?php echo e($order['status']); ?></div>
            <div class="mt-2 text-muted">Payment method: <?php echo e($order['payment_method']); ?></div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <ul class="list-unstyled receipt-list mb-0">
                <?php foreach ($order['items'] as $item): ?>
                    <li class="d-flex justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?php echo e(image_url($item['cover_image'])); ?>" alt="<?php echo e($item['name']); ?>" style="width:72px;height:72px;object-fit:cover;border-radius:16px;">
                            <div>
                                <strong><?php echo e($item['name']); ?></strong>
                                <div class="small text-muted">Qty: <?php echo e($item['quantity']); ?> x <?php echo e(format_price($item['price'])); ?></div>
                            </div>
                        </div>
                        <strong><?php echo e(format_price($item['price'] * $item['quantity'])); ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="col-lg-5">
            <div class="panel-card p-4 h-100">
                <h3 class="h5 mb-3">Delivery Details</h3>
                <p class="mb-1"><strong><?php echo e($order['customer_name']); ?></strong></p>
                <p class="text-muted mb-1"><?php echo e($order['customer_phone']); ?></p>
                <p class="text-muted mb-1"><?php echo e($order['shipping_address']); ?></p>
                <p class="text-muted mb-4"><?php echo e($locationLine); ?></p>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Total Amount</span>
                    <strong><?php echo e(format_price($order['total_amount'])); ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
