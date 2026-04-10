<?php
function get_or_create_cart_id(PDO $pdo, $userId)
{
    $stmt = $pdo->prepare('SELECT id FROM cart WHERE user_id = :user_id LIMIT 1');
    $stmt->execute(array('user_id' => $userId));
    $cartId = $stmt->fetchColumn();

    if ($cartId) {
        return (int) $cartId;
    }

    $insert = $pdo->prepare('INSERT INTO cart (user_id, created_at) VALUES (:user_id, NOW())');
    $insert->execute(array('user_id' => $userId));

    return (int) $pdo->lastInsertId();
}

function get_cart_items(PDO $pdo, $userId)
{
    $stmt = $pdo->prepare('SELECT ci.id, ci.product_id, ci.quantity, p.name, p.price, p.cover_image
                           FROM cart_items ci
                           INNER JOIN cart c ON c.id = ci.cart_id
                           INNER JOIN products p ON p.id = ci.product_id
                           WHERE c.user_id = :user_id
                           ORDER BY ci.id DESC');
    $stmt->execute(array('user_id' => $userId));

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_cart_count(PDO $pdo, $userId)
{
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(ci.quantity), 0)
                           FROM cart_items ci
                           INNER JOIN cart c ON c.id = ci.cart_id
                           WHERE c.user_id = :user_id');
    $stmt->execute(array('user_id' => $userId));

    return (int) $stmt->fetchColumn();
}

function add_to_cart(PDO $pdo, $userId, $productId, $quantity)
{
    $cartId = get_or_create_cart_id($pdo, $userId);
    $quantity = max(1, (int) $quantity);

    $stmt = $pdo->prepare('SELECT id, quantity FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id LIMIT 1');
    $stmt->execute(array(
        'cart_id' => $cartId,
        'product_id' => $productId
    ));
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        $update = $pdo->prepare('UPDATE cart_items SET quantity = :quantity WHERE id = :id');
        return $update->execute(array(
            'quantity' => $item['quantity'] + $quantity,
            'id' => $item['id']
        ));
    }

    $insert = $pdo->prepare('INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (:cart_id, :product_id, :quantity)');
    return $insert->execute(array(
        'cart_id' => $cartId,
        'product_id' => $productId,
        'quantity' => $quantity
    ));
}

function update_cart_item_quantities(PDO $pdo, $userId, array $quantities)
{
    foreach ($quantities as $itemId => $quantity) {
        $quantity = (int) $quantity;

        if ($quantity <= 0) {
            remove_cart_item($pdo, $userId, $itemId);
            continue;
        }

        $stmt = $pdo->prepare('UPDATE cart_items ci
                               INNER JOIN cart c ON c.id = ci.cart_id
                               SET ci.quantity = :quantity
                               WHERE ci.id = :item_id AND c.user_id = :user_id');
        $stmt->execute(array(
            'quantity' => $quantity,
            'item_id' => $itemId,
            'user_id' => $userId
        ));
    }
}

function remove_cart_item(PDO $pdo, $userId, $itemId)
{
    $stmt = $pdo->prepare('DELETE ci FROM cart_items ci
                           INNER JOIN cart c ON c.id = ci.cart_id
                           WHERE ci.id = :item_id AND c.user_id = :user_id');
    return $stmt->execute(array(
        'item_id' => $itemId,
        'user_id' => $userId
    ));
}

function calculate_cart_total(array $items)
{
    $total = 0;

    foreach ($items as $item) {
        $total += ((float) $item['price']) * ((int) $item['quantity']);
    }

    return $total;
}

function clear_user_cart(PDO $pdo, $userId)
{
    $stmt = $pdo->prepare('DELETE ci FROM cart_items ci
                           INNER JOIN cart c ON c.id = ci.cart_id
                           WHERE c.user_id = :user_id');
    return $stmt->execute(array('user_id' => $userId));
}
