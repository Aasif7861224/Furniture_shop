<?php
function create_order_from_cart(PDO $pdo, $userId, array $checkoutData, $paymentMethod)
{
    $cartItems = get_cart_items($pdo, $userId);

    if (!$cartItems) {
        throw new Exception('Your cart is empty.');
    }

    $status = $paymentMethod === 'COD' ? 'pending' : 'paid';
    $totalAmount = calculate_cart_total($cartItems);

    $pdo->beginTransaction();

    try {
        $orderStmt = $pdo->prepare('INSERT INTO orders (
                user_id, total_amount, payment_method, status, customer_name, customer_phone,
                shipping_address, shipping_city, shipping_state, shipping_postal_code, created_at
            ) VALUES (
                :user_id, :total_amount, :payment_method, :status, :customer_name, :customer_phone,
                :shipping_address, :shipping_city, :shipping_state, :shipping_postal_code, NOW()
            )');
        $orderStmt->execute(array(
            'user_id' => $userId,
            'total_amount' => $totalAmount,
            'payment_method' => $paymentMethod,
            'status' => $status,
            'customer_name' => $checkoutData['customer_name'],
            'customer_phone' => $checkoutData['customer_phone'],
            'shipping_address' => $checkoutData['shipping_address'],
            'shipping_city' => $checkoutData['shipping_city'],
            'shipping_state' => $checkoutData['shipping_state'],
            'shipping_postal_code' => $checkoutData['shipping_postal_code']
        ));

        $orderId = (int) $pdo->lastInsertId();
        $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)');

        foreach ($cartItems as $item) {
            $itemStmt->execute(array(
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ));
        }

        clear_user_cart($pdo, $userId);
        $pdo->commit();

        return $orderId;
    } catch (Exception $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

function get_orders_by_user(PDO $pdo, $userId)
{
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC');
    $stmt->execute(array('user_id' => $userId));

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_order_details(PDO $pdo, $orderId, $userId = null)
{
    $sql = 'SELECT o.*, u.name AS user_name, u.email AS user_email
            FROM orders o
            INNER JOIN users u ON u.id = o.user_id
            WHERE o.id = :order_id';
    $params = array('order_id' => $orderId);

    if ($userId !== null) {
        $sql .= ' AND o.user_id = :user_id';
        $params['user_id'] = $userId;
    }

    $sql .= ' LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        return null;
    }

    $itemStmt = $pdo->prepare('SELECT oi.*, p.name, p.cover_image
                               FROM order_items oi
                               INNER JOIN products p ON p.id = oi.product_id
                               WHERE oi.order_id = :order_id
                               ORDER BY oi.id ASC');
    $itemStmt->execute(array('order_id' => $orderId));
    $order['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

    return $order;
}

function get_all_orders(PDO $pdo)
{
    $stmt = $pdo->query('SELECT o.*, u.name AS user_name, u.email AS user_email
                         FROM orders o
                         INNER JOIN users u ON u.id = o.user_id
                         ORDER BY o.created_at DESC');

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function update_order_status(PDO $pdo, $orderId, $status)
{
    $stmt = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :order_id');
    return $stmt->execute(array(
        'status' => $status,
        'order_id' => $orderId
    ));
}

function get_dashboard_stats(PDO $pdo)
{
    $users = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $orders = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    $sales = (float) $pdo->query('SELECT COALESCE(SUM(total_amount), 0) FROM orders')->fetchColumn();

    return array(
        'total_users' => $users,
        'total_orders' => $orders,
        'total_sales' => $sales
    );
}

function get_category_sales(PDO $pdo)
{
    $stmt = $pdo->query('SELECT c.name, COALESCE(SUM(oi.quantity * oi.price), 0) AS total_sales
                         FROM categories c
                         LEFT JOIN products p ON p.category_id = c.id
                         LEFT JOIN order_items oi ON oi.product_id = p.id
                         GROUP BY c.id, c.name
                         ORDER BY total_sales DESC, c.name ASC');

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
