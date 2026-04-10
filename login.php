<?php
require_once __DIR__ . '/db.php';

if (is_logged_in()) {
    redirect(is_admin() ? 'admin/index.php' : 'index.php');
}

$email = '';
$errors = array();

if (is_post()) {
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = trim(isset($_POST['password']) ? $_POST['password'] : '');

    if ($email === '' || $password === '') {
        $errors[] = 'Email and password are required.';
    } else {
        $user = get_user_by_email($pdo, $email);

        if (!$user || $user['password'] !== $password) {
            $errors[] = 'Invalid login credentials.';
        }
    }

    if (!$errors) {
        set_user_session($user);
        set_flash('success', 'Welcome back, ' . $user['name'] . '.');
        redirect($user['role'] === 'admin' ? 'admin/index.php' : 'index.php');
    }
}

$pageTitle = 'Login';
$activeNav = '';
require_once __DIR__ . '/header.php';
?>
<div class="auth-wrapper">
    <div class="row justify-content-center w-100">
        <div class="col-lg-5">
            <div class="hero-section hero-simple mb-4">
                <span class="hero-chip"><i class="bi bi-door-open"></i> One login for customers and admin</span>
                <h1 class="display-6 fw-bold mb-3">Sign in to continue shopping.</h1>
                <p class="mb-0 text-white-50">Access your cart, track orders, manage products, and explore the latest furniture collection.</p>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="form-card p-4 p-lg-5">
                <h2 class="h3 mb-3">Login</h2>
                <p class="text-muted mb-4">Use your email and password to access your account.</p>

                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo e($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo e($email); ?>" required>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Login</button>
                </form>

                <p class="text-muted mt-4 mb-0">New here? <a href="<?php echo e(base_url('register.php')); ?>">Create an account</a></p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
