<?php
require_once __DIR__ . '/auth.php';
require_login();

$user = current_user();

if (is_post()) {
    if (isset($_POST['remove_item_id'])) {
        $itemId = (int) $_POST['remove_item_id'];
        remove_cart_item($pdo, $user['id'], $itemId);
        set_flash('success', 'Item removed from cart.');
        redirect('cart.php');
    }

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'add') {
        $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;
        $product = $productId > 0 ? get_product_by_id($pdo, $productId) : null;

        if (!$product) {
            set_flash('error', 'Selected product could not be found.');
        } else {
            add_to_cart($pdo, $user['id'], $productId, $quantity);
            set_flash('success', $product['name'] . ' has been added to your cart.');
        }

        redirect('cart.php');
    }

    if ($action === 'update') {
        $quantities = isset($_POST['quantities']) && is_array($_POST['quantities']) ? $_POST['quantities'] : array();
        update_cart_item_quantities($pdo, $user['id'], $quantities);
        set_flash('success', 'Cart updated successfully.');
        redirect('cart.php');
    }

}

$items = get_cart_items($pdo, $user['id']);
$totalAmount = calculate_cart_total($items);

$pageTitle = 'Cart';
$activeNav = '';
require_once __DIR__ . '/header.php';
?>
<div class="admin-title">
    <div>
        <h1 class="section-title mb-1">Your Cart</h1>
        <p class="section-subtitle mb-0">Review your selections before moving to checkout.</p>
    </div>
    <a href="<?php echo e(base_url('shop.php')); ?>" class="btn btn-outline-dark">Continue Shopping</a>
</div>

<?php if (!$items): ?>
    <div class="panel-card empty-state">
        <h2 class="h4 mb-2">Your cart is empty.</h2>
        <p class="text-muted mb-4">Explore the catalog and add your favorite furniture pieces to get started.</p>
        <a class="btn btn-dark" href="<?php echo e(base_url('shop.php')); ?>">Go to Shop</a>
    </div>
<?php else: ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <form method="post">
                <input type="hidden" name="action" value="update">
                <div class="panel-card table-responsive p-3">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th width="140">Quantity</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?php echo e(image_url($item['cover_image'])); ?>" alt="<?php echo e($item['name']); ?>" style="width:72px;height:72px;object-fit:cover;border-radius:16px;">
                                            <div>
                                                <h3 class="h6 mb-1"><?php echo e($item['name']); ?></h3>
                                                <a href="<?php echo e(base_url('product.php?id=' . $item['product_id'])); ?>" class="small text-muted">View details</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo e(format_price($item['price'])); ?></td>
                                    <td>
                                        <input type="number" min="1" class="form-control" name="quantities[<?php echo e($item['id']); ?>]" value="<?php echo e($item['quantity']); ?>">
                                    </td>
                                    <td><?php echo e(format_price($item['price'] * $item['quantity'])); ?></td>
                                    <td class="text-end">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" name="remove_item_id" value="<?php echo e($item['id']); ?>">Remove</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-dark">Update Cart</button>
                </div>
            </form>
        </div>
        <div class="col-lg-4">
            <div class="panel-card p-4 summary-card">
                <h2 class="h4 mb-4">Order Summary</h2>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Items</span>
                    <strong><?php echo e(count($items)); ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Estimated Total</span>
                    <strong><?php echo e(format_price($totalAmount)); ?></strong>
                </div>
                <a href="<?php echo e(base_url('checkout.php')); ?>" class="btn btn-dark w-100">Proceed to Checkout</a>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/footer.php'; ?>
