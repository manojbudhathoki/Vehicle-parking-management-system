<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin_auth.php';
requireAdminLogin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf_token'] ?? null);
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? 'create';
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $pass = $_POST['password'] ?? '';
    try {
        if ($action === 'create') {
            if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8)
                throw new Exception('Valid name, email and password (8+) are required.');
            $s = $pdo->prepare("INSERT INTO users(full_name,email,phone,password_hash,role,status) VALUES(?,?,?,?, 'staff','active')");
            $s->execute([$name, $email, $phone, password_hash($pass, PASSWORD_DEFAULT)]);
            flash('success', 'Staff created.');
        } elseif ($action === 'update') {
            if (!$id || !$name || !filter_var($email, FILTER_VALIDATE_EMAIL))
                throw new Exception('Valid name and email are required.');
            $s = $pdo->prepare("UPDATE users SET full_name=?,email=?,phone=? WHERE id=? AND role='staff'");
            $s->execute([$name, $email, $phone, $id]);
            if ($pass) {
                if (strlen($pass) < 8)
                    throw new Exception('Password must be at least 8 characters.');
                $pdo->prepare("UPDATE users SET password_hash=? WHERE id=? AND role='staff'")->execute([password_hash($pass, PASSWORD_DEFAULT), $id]);
            }
            flash('success', 'Staff updated.');
        } elseif ($action === 'status') {
            $s = $pdo->prepare("UPDATE users SET status=CASE WHEN status='active' THEN 'disabled' ELSE 'active' END WHERE id=? AND role='staff'");
            $s->execute([$id]);
            flash('success', 'Staff status updated.');
        } elseif ($action === 'delete') {
            try {
                $s = $pdo->prepare("DELETE FROM users WHERE id=? AND role='staff'");
                $s->execute([$id]);
                flash('success', 'Staff deleted.');
            } catch (PDOException $e) {
                flash('danger', 'Staff cannot be deleted because parking/payment/audit records reference this account. Disable it instead.');
            }
        }
    } catch (PDOException $e) {
        flash('danger', 'Unable to save staff. Email may already exist.');
    } catch (Throwable $e) {
        flash('danger', $e->getMessage());
    }
    redirect('admin/staff.php');
}
$edit = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT id,full_name,email,phone,status FROM users WHERE id=? AND role='staff'");
    $s->execute([(int) $_GET['edit']]);
    $edit = $s->fetch();
}
$rows = $pdo->query("SELECT id,full_name,email,phone,status,created_at FROM users WHERE role='staff' ORDER BY id DESC")->fetchAll();
$pageTitle = 'Staff Management';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<div class="panel-card mb-4">
    <div class="panel-card-header">
        <h6><?= $edit ? 'Edit Staff' : 'Add Staff' ?></h6><?php if ($edit): ?><a href="<?= BASE_URL ?>admin/staff.php"
                class="btn btn-sm btn-outline-secondary">Cancel</a><?php endif; ?>
    </div>
    <div class="panel-card-body">
        <form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden"
                name="action" value="<?= $edit ? 'update' : 'create' ?>"><input type="hidden" name="id"
                value="<?= $edit['id'] ?? 0 ?>">
            <div class="row g-2">
                <div class="col-md-3"><input name="full_name" class="form-control" placeholder="Full name"
                        value="<?= e($edit['full_name'] ?? '') ?>" required></div>
                <div class="col-md-3"><input type="email" name="email" class="form-control" placeholder="Email"
                        value="<?= e($edit['email'] ?? '') ?>" required></div>
                <div class="col-md-2"><input name="phone" class="form-control" placeholder="Phone"
                        value="<?= e($edit['phone'] ?? '') ?>"></div>
                <div class="col-md-2"><input type="password" name="password" class="form-control"
                        placeholder="<?= $edit ? 'New password (optional)' : 'Password' ?>" <?= $edit ? '' : 'required' ?>
                        minlength="8"></div>
                <div class="col-md-2"><button
                        class="btn btn-primary w-100"><?= $edit ? 'Save Changes' : 'Create Staff' ?></button></div>
            </div>
        </form>
    </div>
</div>
<div class="panel-card">
    <div class="panel-card-header">
        <h6>Staff Accounts</h6><span class="small text-muted"><?= count($rows) ?> staff</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody><?php foreach ($rows as $r): ?>
                    <tr>
                        <td><strong><?= e($r['full_name']) ?></strong></td>
                        <td><?= e($r['email']) ?><br><?= e($r['phone']) ?></td>
                        <td><span
                                class="badge bg-<?= $r['status'] === 'active' ? 'success' : 'secondary' ?>"><?= e(ucfirst($r['status'])) ?></span>
                        </td>
                        <td><?= e($r['created_at']) ?></td>
                        <td class="text-end"><a href="<?= BASE_URL ?>admin/staff.php?edit=<?= $r['id'] ?>"
                                class="btn btn-sm btn-outline-primary">Edit</a>
                            <form method="post" class="d-inline"><input type="hidden" name="csrf_token"
                                    value="<?= e(csrfToken()) ?>"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button
                                    name="action" value="status"
                                    class="btn btn-sm btn-outline-warning"><?= $r['status'] === 'active' ? 'Disable' : 'Enable' ?></button>
                            </form>
                            <form method="post" class="d-inline" data-confirm="Delete this staff account?"><input
                                    type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden"
                                    name="id" value="<?= $r['id'] ?>"><button name="action" value="delete"
                                    class="btn btn-sm btn-outline-danger">Delete</button></form>
                        </td>
                    </tr><?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/panel_footer.php'; ?>