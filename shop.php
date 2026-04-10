<?php
require_once __DIR__ . '/db.php';

$categories = get_categories($pdo);
$selectedCategory = isset($_GET['category']) ? (int) $_GET['category'] : 0;
$search = trim(isset($_GET['search']) ? $_GET['search'] : '');
$products = get_shop_products($pdo, $selectedCategory, $search);

$pageTitle = 'Shop';
$activeNav = 'shop';
require_once __DIR__ . '/header.php';
?>
<section class="mb-5">
    <div class="hero-section hero-simple">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="hero-chip"><i class="bi bi-grid"></i> Curated furniture catalog</span>
                <h1 class="display-6 fw-bold mb-3">Shop every piece for your space.</h1>
                <p class="mb-0 text-white-50">Filter by category, search by keyword, and browse every product in a responsive grid layout.</p>
            </div>
            <div class="col-lg-5">
                <form method="get" class="panel-card p-4 bg-white text-dark">
                    <div class="mb-3">
                        <label class="form-label" for="search">Search products</label>
                        <input type="text" class="form-control" id="search" name="search" value="<?php echo e($search); ?>" placeholder="Sofa, bed, table...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="category">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="0">All categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo e($category['id']); ?>"<?php echo selected_if($selectedCategory === (int) $category['id']); ?>><?php echo e($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark flex-fill">Apply</button>
                        <a href="<?php echo e(base_url('shop.php')); ?>" class="btn btn-outline-dark flex-fill">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<section>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="section-title mb-1">Products</h2>
            <p class="section-subtitle mb-0"><?php echo e(count($products)); ?> item(s) found for your filters.</p>
        </div>
    </div>

    <?php if (!$products): ?>
        <div class="panel-card empty-state">
            <h3 class="h4 mb-2">No products matched your search.</h3>
            <p class="text-muted mb-4">Try another keyword or clear the category filter to explore the full collection.</p>
            <a class="btn btn-dark" href="<?php echo e(base_url('shop.php')); ?>">View All Products</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
                <div class="col-md-6 col-xl-4">
                    <div class="product-card card border-0">
                        <img src="<?php echo e(image_url($product['cover_image'])); ?>" class="product-image" alt="<?php echo e($product['name']); ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="category-pill"><?php echo e($product['category_name']); ?></span>
                                <span class="product-price"><?php echo e(format_price($product['price'])); ?></span>
                            </div>
                            <h3 class="h5"><?php echo e($product['name']); ?></h3>
                            <p class="text-muted"><?php echo e(substr($product['description'], 0, 105)); ?>...</p>
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
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/footer.php'; ?>
