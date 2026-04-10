<?php
require_once __DIR__ . '/auth.php';
require_login();

$user = get_user_by_id($pdo, current_user()['id']);
$errors = array();
$ordersCountStmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE user_id = :user_id');
$ordersCountStmt->execute(array('user_id' => $user['id']));
$ordersCount = (int) $ordersCountStmt->fetchColumn();

$name = $user['name'];
$email = $user['email'];
$password = '';

if (is_post()) {
    $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = trim(isset($_POST['password']) ? $_POST['password'] : '');

    if ($name === '' || $email === '') {
        $errors[] = 'Name and email are required.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    $existing = get_user_by_email($pdo, $email);

    if (!$errors && $existing && (int) $existing['id'] !== (int) $user['id']) {
        $errors[] = 'That email address is already in use.';
    }

    if (!$errors) {
        if ($password !== '') {
            $stmt = $pdo->prepare('UPDATE users SET name = :name, email = :email, password = :password WHERE id = :id');
            $stmt->execute(array(
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'id' => $user['id']
            ));
        } else {
            $stmt = $pdo->prepare('UPDATE users SET name = :name, email = :email WHERE id = :id');
            $stmt->execute(array(
                'name' => $name,
                'email' => $email,
                'id' => $user['id']
            ));
        }

        refresh_user_session($pdo, $user['id']);
        set_flash('success', 'Profile updated successfully.');
        redirect('profile.php');
    }
}

$pageTitle = 'Profile';
$activeNav = 'profile';
require_once __DIR__ . '/header.php';
?>
<div class="admin-title">
    <div>
        <h1 class="section-title mb-1">Profile</h1>
        <p class="section-subtitle mb-0">Manage your account details and keep your login information updated.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="panel-card p-4 h-100">
            <h2 class="h4 mb-3"><?php echo e($user['name']); ?></h2>
            <p class="text-muted mb-1"><?php echo e($user['email']); ?></p>
            <p class="text-muted mb-4">Role: <?php echo e(ucfirst($user['role'])); ?></p>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Orders placed</span>
                <strong><?php echo e($ordersCount); ?></strong>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-muted">Member since</span>
                <strong><?php echo e(date('d M Y', strtotime($user['created_at']))); ?></strong>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="form-card p-4 p-lg-5">
            <h2 class="h4 mb-4">Update Account</h2>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo e($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Full name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo e($name); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo e($email); ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="password">New password</label>
                        <input type="password" class="form-control" id="password" name="password" value="" placeholder="Leave blank to keep current password">
                    </div>
                </div>
                <button type="submit" class="btn btn-dark mt-4">Save Changes</button>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
