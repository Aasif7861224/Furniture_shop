<?php
require_once __DIR__ . '/db.php';

if (is_logged_in()) {
    redirect(is_admin() ? 'admin/index.php' : 'index.php');
}

$name = '';
$email = '';
$errors = array();

if (is_post()) {
    $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = trim(isset($_POST['password']) ? $_POST['password'] : '');
    $confirmPassword = trim(isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '');

    if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $errors[] = 'All fields are required.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($password !== '' && strlen($password) < 4) {
        $errors[] = 'Password must be at least 4 characters.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors && get_user_by_email($pdo, $email)) {
        $errors[] = 'An account with this email already exists.';
    }

    if (!$errors) {
        create_user($pdo, $name, $email, $password, 'user');
        set_flash('success', 'Account created successfully. Please login.');
        redirect('login.php');
    }
}

$pageTitle = 'Register';
require_once __DIR__ . '/header.php';
?>
<div class="auth-wrapper">
    <div class="row justify-content-center w-100">
        <div class="col-lg-5">
            <div class="form-card p-4 p-lg-5">
                <h2 class="h3 mb-3">Create Your Account</h2>
                <p class="text-muted mb-4">Sign up to save your cart, track orders, and enjoy a faster checkout.</p>

                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo e($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">Full name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo e($name); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo e($email); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Create Account</button>
                </form>

                <p class="text-muted mt-4 mb-0">Already have an account? <a href="<?php echo e(base_url('login.php')); ?>">Login here</a></p>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="hero-section hero-simple mt-4 mt-lg-0">
                <span class="hero-chip"><i class="bi bi-stars"></i> Premium pieces for every room</span>
                <h3 class="fw-bold mb-3">Discover curated comfort and modern design.</h3>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="feature-card">
                            <h4 class="h6">Secure sessions</h4>
                            <p class="small mb-0 text-white-50">Personal cart, order history, and profile access after signup.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="feature-card">
                            <h4 class="h6">Fast checkout</h4>
                            <p class="small mb-0 text-white-50">Save time with a smooth order and fake payment flow.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
