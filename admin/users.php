<?php
require_once __DIR__ . '/../auth.php';
require_admin();

$usersStmt = $pdo->query('SELECT * FROM users ORDER BY created_at DESC');
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Users';
$activeNav = 'users';
$isAdminPage = true;
require_once __DIR__ . '/../header.php';
?>
<div class="admin-title">
    <div>
        <h1 class="section-title mb-1">Users</h1>
        <p class="section-subtitle mb-0">View all registered users and their roles.</p>
    </div>
</div>

<div class="panel-card table-responsive p-3">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$users): ?>
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">No users found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo e($user['name']); ?></td>
                        <td><?php echo e($user['email']); ?></td>
                        <td><span class="badge text-bg-light"><?php echo e($user['role']); ?></span></td>
                        <td><?php echo e(format_datetime($user['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../footer.php'; ?>
