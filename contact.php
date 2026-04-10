<?php
require_once __DIR__ . '/db.php';

$name = '';
$email = '';
$phone = '';
$subject = '';
$message = '';
$errors = array();

if (is_post()) {
    $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $phone = trim(isset($_POST['phone']) ? $_POST['phone'] : '');
    $subject = trim(isset($_POST['subject']) ? $_POST['subject'] : '');
    $message = trim(isset($_POST['message']) ? $_POST['message'] : '');

    if ($name === '' || $email === '' || $phone === '' || $subject === '' || $message === '') {
        $errors[] = 'All fields are required.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($phone !== '' && !preg_match('/^[0-9]{7,20}$/', $phone)) {
        $errors[] = 'Phone number must contain only digits.';
    }

    if (!$errors) {
        $saved = save_contact_message($pdo, array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'message' => $message
        ));

        if ($saved) {
            set_flash('success', 'Your message has been sent successfully.');
            redirect('contact.php');
        } else {
            $errors[] = 'Unable to send your message right now.';
        }
    }
}

$pageTitle = 'Contact';
$activeNav = 'contact';
require_once __DIR__ . '/header.php';
?>
<section class="hero-section hero-simple mb-5">
    <div class="row g-4 align-items-center">
        <div class="col-lg-8">
            <span class="hero-chip"><i class="bi bi-headset"></i> Contact & Support</span>
            <h1 class="display-6 fw-bold mb-3">We are here to help with your furniture needs.</h1>
            <p class="mb-0 text-white-50">Send us a message and our support team will respond quickly.</p>
        </div>
        <div class="col-lg-4">
            <div class="feature-card">
                <h2 class="h5">Support Hours</h2>
                <p class="mb-0 text-white-50">Mon - Sat: 9:00 AM to 7:00 PM</p>
            </div>
        </div>
    </div>
</section>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="form-card p-4 p-lg-5">
            <h2 class="h4 mb-4">Send Us a Message</h2>

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
                        <label class="form-label" for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo e($name); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo e($email); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo e($phone); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="subject">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" value="<?php echo e($subject); ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="message">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required><?php echo e($message); ?></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-dark mt-4">Send Message</button>
            </form>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel-card p-4 h-100">
            <h2 class="h4 mb-3">Need quick help?</h2>
            <p class="text-muted mb-4">You can also reach us at our support lines or visit the showroom for in-person guidance.</p>
            <div class="mb-3">
                <strong>Phone</strong>
                <div class="text-muted">+91 98765 43210</div>
            </div>
            <div class="mb-3">
                <strong>Email</strong>
                <div class="text-muted">support@furnitureshop.com</div>
            </div>
            <div>
                <strong>Showroom</strong>
                <div class="text-muted">Near City Center, Ahmedabad</div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
