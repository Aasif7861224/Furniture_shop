<?php
require_once __DIR__ . '/db.php';

$pageTitle = 'About';
$activeNav = 'about';
require_once __DIR__ . '/header.php';
?>
<section class="hero-section mb-5">
    <div class="row g-4 align-items-center">
        <div class="col-lg-8">
            <span class="hero-chip"><i class="bi bi-flower1"></i> About Furniture Shop</span>
            <h1 class="display-6 fw-bold mb-3">We bring warm materials, modern lines, and practical comfort together.</h1>
            <p class="lead text-white-50 mb-0">Furniture Shop is built for customers who want stylish, dependable pieces for bedrooms, living rooms, dining spaces, and home offices.</p>
        </div>
        <div class="col-lg-4">
            <div class="feature-card">
                <h2 class="h5">What we offer</h2>
                <p class="mb-0 text-white-50">A streamlined full-stack shopping experience with curated furniture, account management, order history, and a complete admin panel.</p>
            </div>
        </div>
    </div>
</section>

<section class="row g-4">
    <div class="col-lg-4">
        <div class="panel-card p-4 h-100">
            <h2 class="h4">Thoughtful Selection</h2>
            <p class="text-muted mb-0">Each product page is designed to show elegant visuals, useful details, and easy purchase actions without clutter.</p>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="panel-card p-4 h-100">
            <h2 class="h4">Customer Convenience</h2>
            <p class="text-muted mb-0">Users can register, manage their profile, save items in a personal cart, and revisit every order receipt any time.</p>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="panel-card p-4 h-100">
            <h2 class="h4">Admin Simplicity</h2>
            <p class="text-muted mb-0">Admins can manage categories, products, users, order status, and reports from one clean dashboard.</p>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/footer.php'; ?>
