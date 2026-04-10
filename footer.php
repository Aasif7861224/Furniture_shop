    </div>
</main>
<footer class="site-footer">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <h5 class="mb-2"><?php echo e(APP_NAME); ?></h5>
                <p class="mb-0 text-muted">Modern furniture for warm homes, crafted for comfort and everyday elegance.</p>
            </div>
            <div class="col-lg-6 text-lg-end">
                <a class="footer-link" href="<?php echo e(base_url('shop.php')); ?>">Shop</a>
                <a class="footer-link" href="<?php echo e(base_url('about.php')); ?>">About</a>
                <?php if (is_logged_in()): ?>
                    <a class="footer-link" href="<?php echo e(base_url('orders.php')); ?>">Orders</a>
                <?php else: ?>
                    <a class="footer-link" href="<?php echo e(base_url('login.php')); ?>">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo e(base_url('assets/js/app.js')); ?>"></script>
</body>
</html>
