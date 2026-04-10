<?php
function get_categories(PDO $pdo)
{
    $stmt = $pdo->query('SELECT * FROM categories ORDER BY name ASC');

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_featured_products(PDO $pdo, $limit)
{
    $limit = (int) $limit;
    $stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p INNER JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC LIMIT ' . $limit);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_shop_products(PDO $pdo, $categoryId = 0, $search = '')
{
    $sql = 'SELECT p.*, c.name AS category_name
            FROM products p
            INNER JOIN categories c ON c.id = p.category_id
            WHERE 1 = 1';
    $params = array();

    if ($categoryId > 0) {
        $sql .= ' AND p.category_id = :category_id';
        $params['category_id'] = $categoryId;
    }

    if ($search !== '') {
        $sql .= ' AND (p.name LIKE :search OR p.description LIKE :search OR c.name LIKE :search)';
        $params['search'] = '%' . $search . '%';
    }

    $sql .= ' ORDER BY p.created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_product_by_id(PDO $pdo, $productId)
{
    $stmt = $pdo->prepare('SELECT p.*, c.name AS category_name
                           FROM products p
                           INNER JOIN categories c ON c.id = p.category_id
                           WHERE p.id = :id
                           LIMIT 1');
    $stmt->execute(array('id' => $productId));

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_product_gallery(PDO $pdo, $productId)
{
    $stmt = $pdo->prepare('SELECT * FROM product_images WHERE product_id = :product_id ORDER BY id ASC');
    $stmt->execute(array('product_id' => $productId));

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function count_products_in_category(PDO $pdo, $categoryId)
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = :category_id');
    $stmt->execute(array('category_id' => $categoryId));

    return (int) $stmt->fetchColumn();
}

function get_user_by_email(PDO $pdo, $email)
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(array('email' => $email));

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_user_by_id(PDO $pdo, $userId)
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(array('id' => $userId));

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function create_user(PDO $pdo, $name, $email, $password, $role)
{
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, created_at) VALUES (:name, :email, :password, :role, NOW())');
    return $stmt->execute(array(
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'role' => $role
    ));
}

function save_contact_message(PDO $pdo, array $data)
{
    $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, phone, subject, message, status, created_at)
                           VALUES (:name, :email, :phone, :subject, :message, :status, NOW())');
    return $stmt->execute(array(
        'name' => $data['name'],
        'email' => $data['email'],
        'phone' => $data['phone'],
        'subject' => $data['subject'],
        'message' => $data['message'],
        'status' => 'new'
    ));
}
