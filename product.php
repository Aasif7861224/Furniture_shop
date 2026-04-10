<?php
require_once __DIR__ . '/db.php';

$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = $productId > 0 ? get_product_by_id($pdo, $productId) : null;

if (!$product) {
    set_flash('error', 'Product not found.');
    redirect('shop.php');
}

$gallery = get_product_gallery($pdo, $productId);

if (!$gallery) {
    $gallery = array(
        array('image_path' => $product['cover_image'])
    );
}

$relatedStmt = $pdo->prepare('SELECT p.*, c.name AS category_name
                              FROM products p
                              INNER JOIN categories c ON c.id = p.category_id
                              WHERE p.category_id = :category_id AND p.id != :product_id
                              ORDER BY p.created_at DESC
                              LIMIT 3');
$relatedStmt->execute(array(
    'category_id' => $product['category_id'],
    'product_id' => $product['id']
));
$relatedProducts = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = $product['name'];
$activeNav = 'shop';
require_once __DIR__ . '/header.php';
?>
<section class="mb-5">
    <div class="row g-4 align-items-start">
        <div class="col-lg-7">
            <img src="<?php echo e(image_url($product['cover_image'])); ?>" class="detail-main-image mb-4" alt="<?php echo e($product['name']); ?>">
            <div class="product-gallery-grid">
                <?php foreach ($gallery as $image): ?>
                    <img src="<?php echo e(image_url($image['image_path'])); ?>" class="gallery-thumb" alt="<?php echo e($product['name']); ?>">
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="panel-card p-4 p-lg-5">
                <span class="category-pill mb-3"><?php echo e($product['category_name']); ?></span>
                <h1 class="display-6 fw-bold"><?php echo e($product['name']); ?></h1>
                <p class="product-price mb-3"><?php echo e(format_price($product['price'])); ?></p>
                <p class="text-muted mb-4"><?php echo nl2br(e($product['description'])); ?></p>

                <form method="post" action="<?php echo e(base_url('cart.php')); ?>" class="row g-3">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?php echo e($product['id']); ?>">
                    <div class="col-sm-4">
                        <label class="form-label" for="quantity">Quantity</label>
                        <input type="number" min="1" max="10" class="form-control" id="quantity" name="quantity" value="1">
                    </div>
                    <div class="col-sm-8 d-flex align-items-end">
                        <button type="submit" class="btn btn-dark w-100">Add to Cart</button>
                    </div>
                </form>

                <div class="row g-3 mt-4">
                    <div class="col-sm-6">
                        <div class="panel-card p-3 h-100">
                            <h2 class="h6">Responsive Design</h2>
                            <p class="small text-muted mb-0">Smooth browsing and checkout on mobile and desktop.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="panel-card p-3 h-100">
                            <h2 class="h6">Fast Delivery Flow</h2>
                            <p class="small text-muted mb-0">Order, pay, and view your receipt in a simple flow.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($relatedProducts): ?>
    <section>
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
            <div>
                <h2 class="section-title">Related Products</h2>
                <p class="section-subtitle mb-0">More pieces from the same category you may like.</p>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($relatedProducts as $related): ?>
                <div class="col-md-4">
                    <div class="product-card card border-0">
                        <img src="<?php echo e(image_url($related['cover_image'])); ?>" class="product-image" alt="<?php echo e($related['name']); ?>">
                        <div class="card-body">
                            <span class="category-pill mb-2"><?php echo e($related['category_name']); ?></span>
                            <h3 class="h5"><?php echo e($related['name']); ?></h3>
                            <p class="product-price"><?php echo e(format_price($related['price'])); ?></p>
                            <a href="<?php echo e(base_url('product.php?id=' . $related['id'])); ?>" class="btn btn-outline-dark w-100">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
<?php require_once __DIR__ . '/footer.php'; ?>
