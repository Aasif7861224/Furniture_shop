<?php
require_once __DIR__ . '/../auth.php';
require_admin();

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editingCategory = null;
$name = '';
$errors = array();

if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = :id LIMIT 1');
    $stmt->execute(array('id' => $editId));
    $editingCategory = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$editingCategory) {
        set_flash('error', 'Category not found.');
        redirect('admin/categories.php');
    }

    $name = $editingCategory['name'];
}

if (is_post()) {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($action === 'save') {
        $categoryId = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
        $name = trim(isset($_POST['name']) ? $_POST['name'] : '');

        if ($name === '') {
            $errors[] = 'Category name is required.';
        } else {
            $duplicateStmt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE name = :name AND id != :id');
            $duplicateStmt->execute(array(
                'name' => $name,
                'id' => $categoryId
            ));

            if ((int) $duplicateStmt->fetchColumn() > 0) {
                $errors[] = 'A category with this name already exists.';
            }
        }

        if (!$errors) {
            if ($categoryId > 0) {
                $stmt = $pdo->prepare('UPDATE categories SET name = :name WHERE id = :id');
                $stmt->execute(array(
                    'name' => $name,
                    'id' => $categoryId
                ));
                set_flash('success', 'Category updated successfully.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO categories (name, created_at) VALUES (:name, NOW())');
                $stmt->execute(array('name' => $name));
                set_flash('success', 'Category created successfully.');
            }

            redirect('admin/categories.php');
        }
    }

    if ($action === 'delete') {
        $categoryId = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;

        if ($categoryId <= 0) {
            set_flash('error', 'Invalid category selected.');
            redirect('admin/categories.php');
        }

        $productCountStmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = :category_id');
        $productCountStmt->execute(array('category_id' => $categoryId));

        if ((int) $productCountStmt->fetchColumn() > 0) {
            set_flash('error', 'You cannot delete a category that still has products.');
        } else {
            $stmt = $pdo->prepare('DELETE FROM categories WHERE id = :id');
            $stmt->execute(array('id' => $categoryId));
            set_flash('success', 'Category deleted successfully.');
        }

        redirect('admin/categories.php');
    }
}

$categoriesStmt = $pdo->query('SELECT c.*, COUNT(p.id) AS product_count
                               FROM categories c
                               LEFT JOIN products p ON p.category_id = c.id
                               GROUP BY c.id, c.name, c.created_at
                               ORDER BY c.name ASC');
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Categories';
$activeNav = 'categories';
$isAdminPage = true;
require_once __DIR__ . '/../header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="form-card p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h1 class="h3 mb-1"><?php echo $editingCategory ? 'Edit Category' : 'Add Category'; ?></h1>
                    <p class="text-muted mb-0">Create and manage categories for the furniture catalog.</p>
                </div>
                <?php if ($editingCategory): ?>
                    <a class="btn btn-outline-dark btn-sm" href="<?php echo e(base_url('admin/categories.php')); ?>">New</a>
                <?php endif; ?>
            </div>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo e($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="category_id" value="<?php echo e($editingCategory ? $editingCategory['id'] : 0); ?>">
                <div class="mb-3">
                    <label class="form-label" for="name">Category name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo e($name); ?>" required>
                </div>
                <button type="submit" class="btn btn-dark"><?php echo $editingCategory ? 'Update Category' : 'Add Category'; ?></button>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="admin-title mb-3">
            <div>
                <h2 class="section-title mb-1">All Categories</h2>
                <p class="section-subtitle mb-0">Edit, review, and safely remove categories when they are no longer used.</p>
            </div>
        </div>

        <div class="panel-card table-responsive p-3">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Products</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$categories): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No categories available yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo e($category['name']); ?></td>
                                <td><?php echo e($category['product_count']); ?></td>
                                <td><?php echo e(format_datetime($category['created_at'])); ?></td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-dark" href="<?php echo e(base_url('admin/categories.php?edit=' . $category['id'])); ?>">Edit</a>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="category_id" value="<?php echo e($category['id']); ?>">
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
