<?php
require_once __DIR__ . '/../auth.php';
require_admin();

$validStatuses = array('new', 'in_progress', 'resolved');
$errors = array();

if (is_post()) {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($action === 'update_status') {
        $messageId = isset($_POST['message_id']) ? (int) $_POST['message_id'] : 0;
        $status = isset($_POST['status']) ? trim($_POST['status']) : '';

        if ($messageId <= 0) {
            set_flash('error', 'Invalid message selected.');
        } elseif (!in_array($status, $validStatuses, true)) {
            set_flash('error', 'Invalid status selected.');
        } else {
            $stmt = $pdo->prepare('UPDATE contact_messages SET status = :status WHERE id = :id');
            $stmt->execute(array(
                'status' => $status,
                'id' => $messageId
            ));
            set_flash('success', 'Message status updated.');
        }

        redirect('admin/contact_messages.php');
    }

    if ($action === 'delete') {
        $messageId = isset($_POST['message_id']) ? (int) $_POST['message_id'] : 0;

        if ($messageId <= 0) {
            set_flash('error', 'Invalid message selected.');
        } else {
            $stmt = $pdo->prepare('DELETE FROM contact_messages WHERE id = :id');
            $stmt->execute(array('id' => $messageId));
            set_flash('success', 'Message deleted successfully.');
        }

        redirect('admin/contact_messages.php');
    }
}

$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($statusFilter !== '' && !in_array($statusFilter, $validStatuses, true)) {
    $statusFilter = '';
}

$sql = 'SELECT * FROM contact_messages WHERE 1=1';
$params = array();

if ($statusFilter !== '') {
    $sql .= ' AND status = :status';
    $params['status'] = $statusFilter;
}

if ($search !== '') {
    $sql .= ' AND (name LIKE :search OR email LIKE :search OR phone LIKE :search OR subject LIKE :search)';
    $params['search'] = '%' . $search . '%';
}

$sql .= ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Messages';
$activeNav = 'messages';
$isAdminPage = true;
require_once __DIR__ . '/../header.php';
?>
<div class="admin-title">
    <div>
        <h1 class="section-title mb-1">Contact Messages</h1>
        <p class="section-subtitle mb-0">Manage customer support requests and track their status.</p>
    </div>
</div>

<div class="panel-card p-4 mb-4">
    <form method="get" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label" for="search">Search</label>
            <input type="text" class="form-control" id="search" name="search" value="<?php echo e($search); ?>" placeholder="Name, email, phone, subject">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="status">Status</label>
            <select class="form-select" id="status" name="status">
                <option value="">All</option>
                <?php foreach ($validStatuses as $status): ?>
                    <option value="<?php echo e($status); ?>"<?php echo selected_if($statusFilter === $status); ?>><?php echo e(ucfirst(str_replace('_', ' ', $status))); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-5 d-flex gap-2">
            <button type="submit" class="btn btn-dark flex-fill">Filter</button>
            <a href="<?php echo e(base_url('admin/contact_messages.php')); ?>" class="btn btn-outline-dark flex-fill">Reset</a>
        </div>
    </form>
</div>

<div class="panel-card table-responsive p-3">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>Name</th>
                <th>Contact</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$messages): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No messages found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <tr>
                        <td>
                            <strong><?php echo e($message['name']); ?></strong>
                        </td>
                        <td>
                            <div><?php echo e($message['email']); ?></div>
                            <div class="small text-muted"><?php echo e($message['phone']); ?></div>
                        </td>
                        <td>
                            <div><?php echo e($message['subject']); ?></div>
                            <div class="small text-muted"><?php echo e(substr($message['message'], 0, 80)); ?>...</div>
                        </td>
                        <td>
                            <form method="post" class="d-flex gap-2">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="message_id" value="<?php echo e($message['id']); ?>">
                                <select class="form-select form-select-sm" name="status">
                                    <?php foreach ($validStatuses as $status): ?>
                                        <option value="<?php echo e($status); ?>"<?php echo selected_if($message['status'] === $status); ?>><?php echo e(ucfirst(str_replace('_', ' ', $status))); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-dark">Save</button>
                            </form>
                        </td>
                        <td><?php echo e(format_datetime($message['created_at'])); ?></td>
                        <td class="text-end">
                            <form method="post">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="message_id" value="<?php echo e($message['id']); ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../footer.php'; ?>
