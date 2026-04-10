<?php
require_once __DIR__ . '/../auth.php';
require_admin();

$validStatuses = array('pending', 'paid', 'shipped', 'delivered');

if (is_post() && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
    $status = trim(isset($_POST['status']) ? $_POST['status'] : '');

    if ($orderId <= 0) {
        set_flash('error', 'Invalid order selected.');
    } elseif (in_array($status, $validStatuses, true)) {
        update_order_status($pdo, $orderId, $status);
        set_flash('success', 'Order status updated successfully.');
    } else {
        set_flash('error', 'Invalid order status selected.');
    }

    redirect('admin/orders.php');
}

$orders = get_all_orders($pdo);

$pageTitle = 'Orders';
$activeNav = 'orders';
$isAdminPage = true;
require_once __DIR__ . '/../header.php';
?>
<div class="admin-title">
    <div>
        <h1 class="section-title mb-1">Orders</h1>
        <p class="section-subtitle mb-0">Monitor every order and update its fulfillment status.</p>
    </div>
</div>

<div class="panel-card table-responsive p-3">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Shipping</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$orders): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No orders have been placed yet.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <?php
                    $shippingLine = $order['shipping_city'] . ' - ' . $order['shipping_postal_code'];

                    if (isset($order['shipping_state']) && $order['shipping_state'] !== '' && strtoupper($order['shipping_state']) !== 'N/A') {
                        $shippingLine = $order['shipping_city'] . ', ' . $order['shipping_state'] . ' - ' . $order['shipping_postal_code'];
                    }
                    ?>
                    <tr>
                        <td>
                            <strong>#<?php echo e($order['id']); ?></strong>
                            <div class="small text-muted"><?php echo e(format_datetime($order['created_at'])); ?></div>
                        </td>
                        <td>
                            <strong><?php echo e($order['user_name']); ?></strong>
                            <div class="small text-muted"><?php echo e($order['user_email']); ?></div>
                        </td>
                        <td>
                            <div><?php echo e($order['customer_name']); ?></div>
                            <div class="small text-muted"><?php echo e($order['customer_phone']); ?></div>
                            <div class="small text-muted"><?php echo e($shippingLine); ?></div>
                        </td>
                        <td><?php echo e(format_price($order['total_amount'])); ?></td>
                        <td><?php echo e($order['payment_method']); ?></td>
                        <td>
                            <form method="post" class="d-flex gap-2">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="order_id" value="<?php echo e($order['id']); ?>">
                                <select class="form-select form-select-sm" name="status">
                                    <?php foreach ($validStatuses as $status): ?>
                                        <option value="<?php echo e($status); ?>"<?php echo selected_if($order['status'] === $status); ?>><?php echo e(ucfirst($status)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-dark">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../footer.php'; ?>
