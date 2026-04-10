<?php
if (!function_exists('base_url')) {
    require_once __DIR__ . '/db.php';
}

$pageTitle = isset($pageTitle) ? $pageTitle . ' | ' . APP_NAME : APP_NAME;
$activeNav = isset($activeNav) ? $activeNav : '';
$isAdminPage = isset($isAdminPage) ? $isAdminPage : false;
$currentUser = current_user();
$cartCount = 0;

if ($currentUser && !$isAdminPage) {
    $cartCount = get_cart_count($pdo, $currentUser['id']);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo e(base_url('assets/css/style.css')); ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold brand-mark" href="<?php echo e($isAdminPage ? base_url('admin/index.php') : base_url('index.php')); ?>">
            <?php echo e(APP_NAME); ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#siteNavbar" aria-controls="siteNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="siteNavbar">
            <?php if ($isAdminPage): ?>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link<?php echo $activeNav === 'dashboard' ? ' active' : ''; ?>" href="<?php echo e(base_url('admin/index.php')); ?>">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link<?php echo $activeNav === 'categories' ? ' active' : ''; ?>" href="<?php echo e(base_url('admin/categories.php')); ?>">Categories</a></li>
                    <li class="nav-item"><a class="nav-link<?php echo $activeNav === 'products' ? ' active' : ''; ?>" href="<?php echo e(base_url('admin/products.php')); ?>">Products</a></li>
                    <li class="nav-item"><a class="nav-link<?php echo $activeNav === 'users' ? ' active' : ''; ?>" href="<?php echo e(base_url('admin/users.php')); ?>">Users</a></li>
                    <li class="nav-item"><a class="nav-link<?php echo $activeNav === 'orders' ? ' active' : ''; ?>" href="<?php echo e(base_url('admin/orders.php')); ?>">Orders</a></li>
                    <li class="nav-item"><a class="nav-link<?php echo $activeNav === 'messages' ? ' active' : ''; ?>" href="<?php echo e(base_url('admin/contact_messages.php')); ?>">Messages</a></li>
                    <li class="nav-item"><a class="nav-link<?php echo $activeNav === 'reports' ? ' active' : ''; ?>" href="<?php echo e(base_url('admin/reports.php')); ?>">Reports</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3 ms-auto">
                    <a class="btn btn-outline-dark btn-sm" href="<?php echo e(base_url('index.php')); ?>">View Store</a>
                    <a class="nav-link p-0" href="<?php echo e(base_url('logout.php')); ?>">Logout</a>
                </div>
            <?php else: ?>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link<?php echo $activeNav === 'home' ? ' active' : ''; ?>" href="<?php echo e(base_url('index.php')); ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link<?php echo $activeNav === 'shop' ? ' active' : ''; ?>" href="<?php echo e(base_url('shop.php')); ?>">Shop</a></li>
                    <li class="nav-item"><a class="nav-link<?php echo $activeNav === 'about' ? ' active' : ''; ?>" href="<?php echo e(base_url('about.php')); ?>">About</a></li>
                    <li class="nav-item"><a class="nav-link<?php echo $activeNav === 'contact' ? ' active' : ''; ?>" href="<?php echo e(base_url('contact.php')); ?>">Contact</a></li>
                    <?php if ($currentUser): ?>
                        <li class="nav-item"><a class="nav-link<?php echo $activeNav === 'orders' ? ' active' : ''; ?>" href="<?php echo e(base_url('orders.php')); ?>">Orders</a></li>
                        <li class="nav-item"><a class="nav-link<?php echo $activeNav === 'profile' ? ' active' : ''; ?>" href="<?php echo e(base_url('profile.php')); ?>">Profile</a></li>
                    <?php endif; ?>
                    <?php if (is_admin()): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('admin/index.php')); ?>">Admin</a></li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <a class="btn btn-outline-dark position-relative" href="<?php echo e(base_url('cart.php')); ?>">
                        <i class="bi bi-bag"></i>
                        <span class="badge rounded-pill text-bg-dark cart-badge"><?php echo e($cartCount); ?></span>
                    </a>
                    <?php if ($currentUser): ?>
                        <span class="small text-muted d-none d-lg-inline">Hi, <?php echo e($currentUser['name']); ?></span>
                        <a class="btn btn-dark" href="<?php echo e(base_url('logout.php')); ?>">Logout</a>
                    <?php else: ?>
                        <a class="btn btn-outline-dark" href="<?php echo e(base_url('login.php')); ?>">Login</a>
                        <a class="btn btn-dark" href="<?php echo e(base_url('register.php')); ?>">Sign Up</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="page-shell py-4">
    <div class="container">
        <?php render_flash(); ?>
