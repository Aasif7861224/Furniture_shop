<?php
function base_url($path = '')
{
    $base = rtrim(BASE_URL, '/');

    if ($path === '') {
        return $base;
    }

    return $base . '/' . ltrim($path, '/');
}

function redirect($path)
{
    header('Location: ' . base_url($path));
    exit;
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function set_flash($type, $message)
{
    $_SESSION['flash'] = array(
        'type' => $type,
        'message' => $message
    );
}

function get_flash()
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function render_flash()
{
    $flash = get_flash();

    if (!$flash) {
        return;
    }

    $map = array(
        'success' => 'success',
        'error' => 'danger',
        'warning' => 'warning',
        'info' => 'info'
    );

    $class = isset($map[$flash['type']]) ? $map[$flash['type']] : 'info';

    echo '<div class="alert alert-' . e($class) . ' alert-dismissible fade show mb-4" role="alert">';
    echo e($flash['message']);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
}

function is_post()
{
    return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'POST';
}

function current_user()
{
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function is_logged_in()
{
    return current_user() !== null;
}

function is_admin()
{
    $user = current_user();

    return $user && isset($user['role']) && $user['role'] === 'admin';
}

function set_user_session(array $user)
{
    $_SESSION['user'] = array(
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role']
    );
}

function refresh_user_session(PDO $pdo, $userId)
{
    $user = get_user_by_id($pdo, $userId);

    if ($user) {
        set_user_session($user);
    }
}

function format_price($amount)
{
    return 'Rs. ' . number_format((float) $amount, 2);
}

function format_datetime($value)
{
    if (!$value) {
        return '';
    }

    return date('d M Y, h:i A', strtotime($value));
}

function image_url($path)
{
    if (!$path) {
        return base_url('uploads/products/placeholder-main.svg');
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    return base_url($path);
}

function selected_if($condition)
{
    return $condition ? ' selected' : '';
}

function checked_if($condition)
{
    return $condition ? ' checked' : '';
}

function hiddens_for_query(array $data, array $skip = array())
{
    foreach ($data as $key => $value) {
        if (in_array($key, $skip, true) || is_array($value)) {
            continue;
        }

        echo '<input type="hidden" name="' . e($key) . '" value="' . e($value) . '">';
    }
}

function render_database_error($message)
{
    http_response_code(500);
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo e(APP_NAME); ?> - Database Error</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4 p-lg-5">
                            <h1 class="h3 mb-3">Database connection failed</h1>
                            <p class="text-muted mb-3">Please import the SQL file in phpMyAdmin and confirm the database credentials in <code>includes/config.php</code>.</p>
                            <div class="alert alert-danger mb-0"><?php echo e($message); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
