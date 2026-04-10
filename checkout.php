<?php
require_once __DIR__ . '/auth.php';
require_login();

$user = current_user();
$items = get_cart_items($pdo, $user['id']);

if (!$items) {
    set_flash('warning', 'Your cart is empty.');
    redirect('shop.php');
}

$checkout = isset($_SESSION['checkout']) && is_array($_SESSION['checkout']) ? $_SESSION['checkout'] : array();
$formData = array(
    'customer_name' => isset($checkout['customer_name']) ? $checkout['customer_name'] : $user['name'],
    'customer_phone' => isset($checkout['customer_phone']) ? $checkout['customer_phone'] : '',
    'shipping_address' => isset($checkout['shipping_address']) ? $checkout['shipping_address'] : '',
    'shipping_city' => isset($checkout['shipping_city']) ? $checkout['shipping_city'] : '',
    'shipping_postal_code' => isset($checkout['shipping_postal_code']) ? $checkout['shipping_postal_code'] : ''
);
$errors = array();

if (is_post()) {
    foreach ($formData as $key => $value) {
        $formData[$key] = trim(isset($_POST[$key]) ? $_POST[$key] : '');
    }

    foreach ($formData as $label => $value) {
        if ($value === '') {
            $errors[] = 'All delivery details are required.';
            break;
        }
    }

    if ($formData['customer_phone'] !== '' && !preg_match('/^[0-9+\-\s]{7,20}$/', $formData['customer_phone'])) {
        $errors[] = 'Please enter a valid phone number.';
    }

    if ($formData['shipping_postal_code'] !== '' && !preg_match('/^[0-9]{4,10}$/', $formData['shipping_postal_code'])) {
        $errors[] = 'Please enter a valid pincode.';
    }

    if (!$errors) {
        $formData['shipping_state'] = 'N/A';
        $_SESSION['checkout'] = $formData;
        redirect('payment.php');
    }
}

$pageTitle = 'Checkout';
$activeNav = '';
require_once __DIR__ . '/header.php';
?>
<div class="admin-title">
    <div>
        <h1 class="section-title mb-1">Checkout</h1>
        <p class="section-subtitle mb-0">Add your shipping details before payment.</p>
    </div>
    <a href="<?php echo e(base_url('cart.php')); ?>" class="btn btn-outline-dark">Back to Cart</a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="form-card p-4 p-lg-5">
            <h2 class="h4 mb-4">Delivery Information</h2>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo e($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="customer_name">Full name</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" value="<?php echo e($formData['customer_name']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="customer_phone">Phone</label>
                        <input type="text" class="form-control" id="customer_phone" name="customer_phone" value="<?php echo e($formData['customer_phone']); ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="shipping_address">Address</label>
                        <textarea class="form-control" id="shipping_address" name="shipping_address" rows="4" required><?php echo e($formData['shipping_address']); ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="shipping_city">City</label>
                        <input type="text" class="form-control" id="shipping_city" name="shipping_city" value="<?php echo e($formData['shipping_city']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="shipping_postal_code">Pincode</label>
                        <input type="text" class="form-control" id="shipping_postal_code" name="shipping_postal_code" value="<?php echo e($formData['shipping_postal_code']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <div class="panel-card p-3 h-100 d-flex align-items-center">
                            <span class="small text-muted">Delivery details will be saved with your order and shown on the final receipt.</span>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-dark mt-4">Continue to Payment</button>
            </form>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel-card p-4 summary-card">
            <h2 class="h4 mb-4">Order Preview</h2>
            <ul class="list-unstyled receipt-list mb-4">
                <?php foreach ($items as $item): ?>
                    <li class="d-flex justify-content-between gap-3">
                        <div>
                            <strong><?php echo e($item['name']); ?></strong>
                            <div class="small text-muted">Qty: <?php echo e($item['quantity']); ?></div>
                        </div>
                        <strong><?php echo e(format_price($item['price'] * $item['quantity'])); ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="d-flex justify-content-between">
                <span class="text-muted">Total</span>
                <strong><?php echo e(format_price(calculate_cart_total($items))); ?></strong>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
