<?php
require_once __DIR__ . '/db.php';

$featuredProducts = get_featured_products($pdo, 6);
$categories = get_categories($pdo);

$pageTitle = 'Home';
$activeNav = 'home';
require_once __DIR__ . '/header.php';
?>
<section class="hero-section mb-5">
    <div class="row align-items-center g-4">
        <div class="col-lg-7">
            <span class="hero-chip"><i class="bi bi-house-heart"></i> Crafted for beautiful living spaces</span>
            <h1 class="display-5 fw-bold mb-3">Furniture that makes every room feel complete.</h1>
            <p class="lead text-white-50 mb-4">Shop elegant sofas, cozy beds, statement tables, and timeless storage pieces with a premium browsing experience.</p>
            <div class="d-flex flex-wrap gap-3">
                <a class="btn btn-light btn-lg" href="<?php echo e(base_url('shop.php')); ?>">Explore Collection</a>
                <a class="btn btn-outline-light btn-lg" href="<?php echo e(base_url('about.php')); ?>">Why Choose Us</a>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="row g-3">
                <div class="col-sm-6">
                    <div class="feature-card">
                        <h2 class="h6">Responsive Shopping</h2>
                        <p class="small mb-0 text-white-50">Browse smoothly on desktop, tablet, and mobile.</p>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="feature-card">
                        <h2 class="h6">Admin Control</h2>
                        <p class="small mb-0 text-white-50">Manage categories, products, orders, and sales insights.</p>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="feature-card">
                        <h2 class="h6">Easy Checkout</h2>
                        <p class="small mb-0 text-white-50">Personal cart, delivery details, and fake payment options.</p>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="feature-card">
                        <h2 class="h6">Gallery Images</h2>
                        <p class="small mb-0 text-white-50">See detailed product visuals before you order.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mb-5">
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
        <div>
            <h2 class="section-title">Featured Products</h2>
            <p class="section-subtitle mb-0">Fresh arrivals selected from our latest catalog.</p>
        </div>
        <a class="btn btn-outline-dark" href="<?php echo e(base_url('shop.php')); ?>">View All Products</a>
    </div>
    <div class="row g-4">
        <?php foreach ($featuredProducts as $product): ?>
            <div class="col-md-6 col-xl-4">
                <div class="product-card card border-0">
                    <img src="<?php echo e(image_url($product['cover_image'])); ?>" class="product-image" alt="<?php echo e($product['name']); ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="category-pill"><?php echo e($product['category_name']); ?></span>
                            <span class="product-price"><?php echo e(format_price($product['price'])); ?></span>
                        </div>
                        <h3 class="h5"><?php echo e($product['name']); ?></h3>
                        <p class="text-muted"><?php echo e(substr($product['description'], 0, 95)); ?>...</p>
                        <div class="d-flex gap-2">
                            <a class="btn btn-outline-dark flex-fill" href="<?php echo e(base_url('product.php?id=' . $product['id'])); ?>">Details</a>
                            <form method="post" action="<?php echo e(base_url('cart.php')); ?>" class="flex-fill">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo e($product['id']); ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-dark w-100">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="row g-4">
    <?php foreach (array_slice($categories, 0, 3) as $category): ?>
        <div class="col-md-4">
            <div class="panel-card p-4 h-100">
                <span class="badge-soft mb-3 d-inline-block"><?php echo e($category['name']); ?></span>
                <h3 class="h4 mb-3">Designed around <?php echo e(strtolower($category['name'])); ?> living.</h3>
                <p class="text-muted mb-4">Discover thoughtfully selected pieces that combine comfort, storage, and standout styling for modern homes.</p>
                <a href="<?php echo e(base_url('shop.php?category=' . $category['id'])); ?>" class="btn btn-outline-dark">Shop <?php echo e($category['name']); ?></a>
            </div>
        </div>
    <?php endforeach; ?>
</section>
<?php require_once __DIR__ . '/footer.php'; ?>
