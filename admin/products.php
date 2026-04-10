<?php
require_once __DIR__ . '/../auth.php';
require_admin();

$categories = get_categories($pdo);
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editing = $editId > 0;
$product = $editing ? get_product_by_id($pdo, $editId) : null;
$galleryImages = $editing ? get_product_gallery($pdo, $editId) : array();
$errors = array();
$formData = array(
    'category_id' => $editing && $product ? (int) $product['category_id'] : 0,
    'name' => $editing && $product ? $product['name'] : '',
    'description' => $editing && $product ? $product['description'] : '',
    'price' => $editing && $product ? $product['price'] : ''
);

if ($editing && !$product) {
    set_flash('error', 'Product not found.');
    redirect('admin/products.php');
}

if (is_post()) {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($action === 'delete') {
        $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
        $productToDelete = $productId > 0 ? get_product_by_id($pdo, $productId) : null;

        if (!$productToDelete) {
            set_flash('error', 'Product not found.');
            redirect('admin/products.php');
        }

        $images = get_product_gallery($pdo, $productId);

        try {
            $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
            $stmt->execute(array('id' => $productId));
            delete_uploaded_image($productToDelete['cover_image']);

            foreach ($images as $image) {
                delete_uploaded_image($image['image_path']);
            }

            set_flash('success', 'Product deleted successfully.');
        } catch (PDOException $exception) {
            set_flash('error', 'Unable to delete this product because it is linked to existing order history.');
        }

        redirect('admin/products.php');
    }

    if ($action === 'delete_gallery') {
        $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
        $galleryId = isset($_POST['gallery_id']) ? (int) $_POST['gallery_id'] : 0;
        $stmt = $pdo->prepare('SELECT * FROM product_images WHERE id = :id AND product_id = :product_id LIMIT 1');
        $stmt->execute(array(
            'id' => $galleryId,
            'product_id' => $productId
        ));
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($image) {
            $deleteStmt = $pdo->prepare('DELETE FROM product_images WHERE id = :id');
            $deleteStmt->execute(array('id' => $galleryId));
            delete_uploaded_image($image['image_path']);
            set_flash('success', 'Gallery image removed successfully.');
        } else {
            set_flash('error', 'Gallery image not found.');
        }

        redirect('admin/products.php?edit=' . $productId);
    }

    if ($action === 'save') {
        $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
        $editing = $productId > 0;
        $product = $editing ? get_product_by_id($pdo, $productId) : null;
        $galleryImages = $editing ? get_product_gallery($pdo, $productId) : array();
        $formData = array(
            'category_id' => isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0,
            'name' => trim(isset($_POST['name']) ? $_POST['name'] : ''),
            'description' => trim(isset($_POST['description']) ? $_POST['description'] : ''),
            'price' => trim(isset($_POST['price']) ? $_POST['price'] : '')
        );

        if ($editing && !$product) {
            $errors[] = 'Product not found.';
        }

        if (!$categories) {
            $errors[] = 'Please create at least one category before adding products.';
        }

        if ($formData['category_id'] <= 0) {
            $errors[] = 'Please choose a category.';
        } else {
            $categoryCheck = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = :id');
            $categoryCheck->execute(array('id' => $formData['category_id']));

            if (!(int) $categoryCheck->fetchColumn()) {
                $errors[] = 'Selected category does not exist.';
            }
        }

        if ($formData['name'] === '' || $formData['description'] === '' || $formData['price'] === '') {
            $errors[] = 'Name, description, and price are required.';
        }

        if ($formData['price'] !== '' && (!is_numeric($formData['price']) || (float) $formData['price'] <= 0)) {
            $errors[] = 'Price must be a valid positive number.';
        }

        $coverImageProvided = isset($_FILES['cover_image']) && isset($_FILES['cover_image']['error']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE;
        $newCoverPath = '';
        $newGalleryPaths = array();
        $galleryUploadErrors = array();

        if (!$editing && !$coverImageProvided) {
            $errors[] = 'A cover image is required.';
        }

        if (!$errors && $coverImageProvided) {
            $coverUpload = upload_product_image($_FILES['cover_image']);

            if ($coverUpload['success']) {
                $newCoverPath = $coverUpload['path'];
            } else {
                $errors[] = $coverUpload['error'];
            }
        }

        if (!$errors && isset($_FILES['gallery_images'])) {
            list($newGalleryPaths, $galleryUploadErrors) = upload_product_gallery($_FILES['gallery_images']);

            foreach ($galleryUploadErrors as $galleryUploadError) {
                $errors[] = $galleryUploadError;
            }
        }

        if ($errors) {
            if ($newCoverPath) {
                delete_uploaded_image($newCoverPath);
            }

            foreach ($newGalleryPaths as $galleryPath) {
                delete_uploaded_image($galleryPath);
            }
        } else {
            $coverPath = $editing ? $product['cover_image'] : $newCoverPath;

            if ($newCoverPath) {
                $coverPath = $newCoverPath;
            }

            try {
                $pdo->beginTransaction();

                if ($editing) {
                    $stmt = $pdo->prepare('UPDATE products
                                           SET category_id = :category_id, name = :name, description = :description, price = :price, cover_image = :cover_image
                                           WHERE id = :id');
                    $stmt->execute(array(
                        'category_id' => $formData['category_id'],
                        'name' => $formData['name'],
                        'description' => $formData['description'],
                        'price' => $formData['price'],
                        'cover_image' => $coverPath,
                        'id' => $productId
                    ));
                } else {
                    $stmt = $pdo->prepare('INSERT INTO products (category_id, name, description, price, cover_image, created_at)
                                           VALUES (:category_id, :name, :description, :price, :cover_image, NOW())');
                    $stmt->execute(array(
                        'category_id' => $formData['category_id'],
                        'name' => $formData['name'],
                        'description' => $formData['description'],
                        'price' => $formData['price'],
                        'cover_image' => $coverPath
                    ));
                    $productId = (int) $pdo->lastInsertId();
                    $editing = true;
                }

                if ($newGalleryPaths) {
                    $galleryStmt = $pdo->prepare('INSERT INTO product_images (product_id, image_path) VALUES (:product_id, :image_path)');

                    foreach ($newGalleryPaths as $galleryPath) {
                        $galleryStmt->execute(array(
                            'product_id' => $productId,
                            'image_path' => $galleryPath
                        ));
                    }
                }

                $pdo->commit();

                if ($product && $newCoverPath) {
                    delete_uploaded_image($product['cover_image']);
                }

                set_flash('success', $product ? 'Product updated successfully.' : 'Product added successfully.');
                redirect('admin/products.php');
            } catch (Exception $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                if ($newCoverPath) {
                    delete_uploaded_image($newCoverPath);
                }

                foreach ($newGalleryPaths as $galleryPath) {
                    delete_uploaded_image($galleryPath);
                }

                $errors[] = 'Unable to save the product right now.';
            }
        }
    }
}

$productsStmt = $pdo->query('SELECT p.*, c.name AS category_name,
                                    (SELECT COUNT(*) FROM product_images pi WHERE pi.product_id = p.id) AS gallery_count
                             FROM products p
                             INNER JOIN categories c ON c.id = p.category_id
                             ORDER BY p.created_at DESC');
$products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Products';
$activeNav = 'products';
$isAdminPage = true;
require_once __DIR__ . '/../header.php';
?>
<div class="row g-4">
    <div class="col-xl-5">
        <div class="form-card p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h1 class="h3 mb-1"><?php echo $product ? 'Edit Product' : 'Add Product'; ?></h1>
                    <p class="text-muted mb-0">Manage product details, cover image, and gallery uploads.</p>
                </div>
                <?php if ($product): ?>
                    <a href="<?php echo e(base_url('admin/products.php')); ?>" class="btn btn-outline-dark btn-sm">New Product</a>
                <?php endif; ?>
            </div>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo e($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="product_id" value="<?php echo e($product ? $product['id'] : 0); ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="category_id">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="0">Select category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo e($category['id']); ?>"<?php echo selected_if((int) $formData['category_id'] === (int) $category['id']); ?>><?php echo e($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="price">Price</label>
                        <input type="text" class="form-control" id="price" name="price" value="<?php echo e($formData['price']); ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="name">Product name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo e($formData['name']); ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?php echo e($formData['description']); ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="cover_image">Cover image <?php echo $product ? '(optional to replace)' : ''; ?></label>
                        <input type="file" class="form-control" id="cover_image" name="cover_image" accept=".jpg,.jpeg,.png,.gif,.webp" <?php echo $product ? '' : 'required'; ?>>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="gallery_images">Gallery images</label>
                        <input type="file" class="form-control" id="gallery_images" name="gallery_images[]" accept=".jpg,.jpeg,.png,.gif,.webp" multiple>
                    </div>
                </div>
                <button type="submit" class="btn btn-dark mt-4"><?php echo $product ? 'Update Product' : 'Add Product'; ?></button>
            </form>
        </div>

        <div class="panel-card p-4 mt-4">
            <h2 class="h5 mb-3">Current Cover</h2>
            <img src="<?php echo e(image_url($product ? $product['cover_image'] : 'uploads/products/placeholder-main.svg')); ?>" class="gallery-thumb mb-3" alt="Product cover">
            <?php if ($product): ?>
                <div class="small text-muted">Gallery images: <?php echo e(count($galleryImages)); ?></div>
            <?php else: ?>
                <div class="small text-muted">Upload a single cover image and optional gallery images for each product.</div>
            <?php endif; ?>
        </div>

        <?php if ($product): ?>
            <div class="panel-card p-4 mt-4">
                <h2 class="h5 mb-3">Gallery Images</h2>
                <?php if (!$galleryImages): ?>
                    <p class="text-muted mb-0">No gallery images uploaded yet.</p>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($galleryImages as $image): ?>
                            <div class="col-sm-6">
                                <img src="<?php echo e(image_url($image['image_path'])); ?>" class="gallery-thumb mb-2" alt="Gallery image">
                                <form method="post">
                                    <input type="hidden" name="action" value="delete_gallery">
                                    <input type="hidden" name="product_id" value="<?php echo e($product['id']); ?>">
                                    <input type="hidden" name="gallery_id" value="<?php echo e($image['id']); ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">Remove</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-xl-7">
        <div class="admin-title mb-3">
            <div>
                <h2 class="section-title mb-1">All Products</h2>
                <p class="section-subtitle mb-0">View, edit, and delete products from the catalog.</p>
            </div>
        </div>

        <div class="panel-card table-responsive p-3">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Gallery</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$products): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No products found. Add your first product from the form.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $listedProduct): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?php echo e(image_url($listedProduct['cover_image'])); ?>" alt="<?php echo e($listedProduct['name']); ?>" style="width:72px;height:72px;object-fit:cover;border-radius:16px;">
                                        <div>
                                            <strong><?php echo e($listedProduct['name']); ?></strong>
                                            <div class="small text-muted"><?php echo e(substr($listedProduct['description'], 0, 78)); ?>...</div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo e($listedProduct['category_name']); ?></td>
                                <td><?php echo e(format_price($listedProduct['price'])); ?></td>
                                <td><?php echo e($listedProduct['gallery_count']); ?></td>
                                <td><?php echo e(format_datetime($listedProduct['created_at'])); ?></td>
                                <td class="text-end">
                                    <a href="<?php echo e(base_url('admin/products.php?edit=' . $listedProduct['id'])); ?>" class="btn btn-sm btn-outline-dark">Edit</a>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="product_id" value="<?php echo e($listedProduct['id']); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../footer.php'; ?>
