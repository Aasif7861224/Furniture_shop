<?php
require_once __DIR__ . '/auth.php';
require_login();

$user = current_user();
$items = get_cart_items($pdo, $user['id']);
$checkout = isset($_SESSION['checkout']) && is_array($_SESSION['checkout']) ? $_SESSION['checkout'] : null;

if (!$items) {
    unset($_SESSION['checkout']);
    set_flash('warning', 'Your cart is empty.');
    redirect('shop.php');
}

if (!$checkout) {
    set_flash('warning', 'Please complete the checkout form first.');
    redirect('checkout.php');
}

if (!isset($checkout['shipping_state']) || trim($checkout['shipping_state']) === '') {
    $checkout['shipping_state'] = 'N/A';
}

$paymentMethod = 'COD';
$errors = array();

if (is_post()) {
    $paymentMethod = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
    $validMethods = array('COD', 'UPI', 'CARD');

    if (!in_array($paymentMethod, $validMethods, true)) {
        $errors[] = 'Please choose a valid payment method.';
    }

    if ($paymentMethod === 'UPI') {
        $upiId = trim(isset($_POST['upi_id']) ? $_POST['upi_id'] : '');

        if ($upiId === '' || strpos($upiId, '@') === false) {
            $errors[] = 'Please enter a valid UPI ID.';
        }
    }

    if ($paymentMethod === 'CARD') {
        $cardName = trim(isset($_POST['card_name']) ? $_POST['card_name'] : '');
        $cardNumber = preg_replace('/\s+/', '', isset($_POST['card_number']) ? $_POST['card_number'] : '');
        $cardExpiry = trim(isset($_POST['card_expiry']) ? $_POST['card_expiry'] : '');
        $cardCvv = trim(isset($_POST['card_cvv']) ? $_POST['card_cvv'] : '');

        if ($cardName === '' || $cardNumber === '' || $cardExpiry === '' || $cardCvv === '') {
            $errors[] = 'Please fill all card details.';
        }

        if ($cardNumber !== '' && !preg_match('/^[0-9]{12,19}$/', $cardNumber)) {
            $errors[] = 'Card number must contain 12 to 19 digits.';
        }

        if ($cardCvv !== '' && !preg_match('/^[0-9]{3,4}$/', $cardCvv)) {
            $errors[] = 'CVV must be 3 or 4 digits.';
        }

        if ($cardExpiry !== '' && !preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $cardExpiry)) {
            $errors[] = 'Card expiry must be in MM/YY format.';
        }
    }

    if (!$errors) {
        try {
            $orderId = create_order_from_cart($pdo, $user['id'], $checkout, $paymentMethod);
            unset($_SESSION['checkout']);
            $_SESSION['last_order_id'] = $orderId;
            set_flash('success', 'Payment completed and order placed successfully.');
            redirect('order_success.php?id=' . $orderId);
        } catch (Exception $exception) {
            $errors[] = $exception->getMessage();
        }
    }
}

$pageTitle = 'Payment';
$activeNav = '';
require_once __DIR__ . '/header.php';
?>
<div class="admin-title">
    <div>
        <h1 class="section-title mb-1">Payment</h1>
        <p class="section-subtitle mb-0">Choose a payment mode and confirm your order.</p>
    </div>
    <a href="<?php echo e(base_url('checkout.php')); ?>" class="btn btn-outline-dark">Back to Checkout</a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="form-card p-4 p-lg-5">
            <h2 class="h4 mb-4">Payment Details</h2>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo e($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="mb-4">
                    <label class="form-label" for="payment_method">Select payment method</label>
                    <select class="form-select" id="payment_method" name="payment_method">
                        <option value="COD"<?php echo selected_if($paymentMethod === 'COD'); ?>>Cash on Delivery</option>
                        <option value="UPI"<?php echo selected_if($paymentMethod === 'UPI'); ?>>UPI</option>
                        <option value="CARD"<?php echo selected_if($paymentMethod === 'CARD'); ?>>Card</option>
                    </select>
                </div>

                <div class="payment-extra<?php echo $paymentMethod === 'COD' ? ' is-visible' : ''; ?>" data-payment-section="COD">
                    <div class="alert alert-info">You will pay at the time of delivery. The order will be marked as pending until fulfilled.</div>
                </div>

                <div class="payment-extra<?php echo $paymentMethod === 'UPI' ? ' is-visible' : ''; ?>" data-payment-section="UPI">
                    <div class="mb-3">
                        <label class="form-label" for="upi_id">UPI ID</label>
                        <input type="text" class="form-control" id="upi_id" name="upi_id" value="<?php echo e(isset($_POST['upi_id']) ? $_POST['upi_id'] : ''); ?>" placeholder="name@bank">
                    </div>
                </div>

                <div class="payment-extra<?php echo $paymentMethod === 'CARD' ? ' is-visible' : ''; ?>" data-payment-section="CARD">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="card_name">Cardholder name</label>
                            <input type="text" class="form-control" id="card_name" name="card_name" value="<?php echo e(isset($_POST['card_name']) ? $_POST['card_name'] : ''); ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="card_number">Card number</label>
                            <input type="text" class="form-control" id="card_number" name="card_number" value="<?php echo e(isset($_POST['card_number']) ? $_POST['card_number'] : ''); ?>" placeholder="1234123412341234">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="card_expiry">Expiry</label>
                            <input type="text" class="form-control" id="card_expiry" name="card_expiry" value="<?php echo e(isset($_POST['card_expiry']) ? $_POST['card_expiry'] : ''); ?>" placeholder="MM/YY">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="card_cvv">CVV</label>
                            <input type="password" class="form-control" id="card_cvv" name="card_cvv" value="<?php echo e(isset($_POST['card_cvv']) ? $_POST['card_cvv'] : ''); ?>" placeholder="123">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-dark mt-4">Confirm Payment</button>
            </form>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel-card p-4 summary-card">
            <h2 class="h4 mb-4">Shipping Summary</h2>
            <div class="mb-4">
                <strong><?php echo e($checkout['customer_name']); ?></strong>
                <div class="text-muted"><?php echo e($checkout['customer_phone']); ?></div>
                <div class="text-muted"><?php echo e($checkout['shipping_address']); ?></div>
                <div class="text-muted"><?php echo e($checkout['shipping_city']); ?> - <?php echo e($checkout['shipping_postal_code']); ?></div>
            </div>
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
                <span class="text-muted">Grand Total</span>
                <strong><?php echo e(format_price(calculate_cart_total($items))); ?></strong>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
