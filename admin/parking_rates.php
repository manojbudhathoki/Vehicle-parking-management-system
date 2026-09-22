<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin_auth.php';
requireAdminLogin();
$types = ['Motorcycle', 'Scooter', 'Car', 'Van', 'Bus', 'Truck', 'Other'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf_token'] ?? null);
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? 'create';
    $t = $_POST['vehicle_type'] ?? '';
    $f = (float) ($_POST['first_hour_rate'] ?? -1);
    $a = (float) ($_POST['additional_hour_rate'] ?? -1);
    $active = (int) ($_POST['is_active'] ?? 1);
    if (!in_array($t, $types, true) || $f < 0 || $a < 0) {
        flash('danger', 'Enter valid vehicle type and non-negative rates.');
    } else {
        try {
            if ($action === 'create') {
                $s = $pdo->prepare("INSERT INTO parking_rates(vehicle_type,first_hour_rate,additional_hour_rate,is_active) VALUES(?,?,?,?)");
                $s->execute([$t, $f, $a, $active]);
                flash('success', 'Rate added.');
            } else {
                $s = $pdo->prepare("UPDATE parking_rates SET vehicle_type=?,first_hour_rate=?,additional_hour_rate=?,is_active=? WHERE id=?");
                $s->execute([$t, $f, $a, $active, $id]);
                flash('success', 'Rate updated.');
            }
        } catch (PDOException $e) {
            flash('danger', 'Unable to save rate.');
        }
    }
    redirect('admin/parking_rates.php');
}
$edit = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM parking_rates WHERE id=?");
    $s->execute([(int) $_GET['edit']]);
    $edit = $s->fetch();
}
$rows = $pdo->query("SELECT * FROM parking_rates ORDER BY vehicle_type,id DESC")->fetchAll();
$pageTitle = 'Parking Rates';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<div class="panel-card mb-4">
    <div class="panel-card-header">
        <h6><?= $edit ? 'Edit Rate' : 'Add Parking Rate' ?></h6><?php if ($edit): ?><a
                href="<?= BASE_URL ?>admin/parking_rates.php"
                class="btn btn-sm btn-outline-secondary">Cancel</a><?php endif; ?>
    </div>
    <div class="panel-card-body">
        <form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden"
                name="action" value="<?= $edit ? 'update' : 'create' ?>"><input type="hidden" name="id"
                value="<?= $edit['id'] ?? 0 ?>">
            <div class="row g-2">
                <div class="col-md-3"><select name="vehicle_type" class="form-select"><?php foreach ($types as $x): ?>
                            <option <?= $x === ($edit['vehicle_type'] ?? 'Car') ? 'selected' : '' ?>><?= $x ?></option>
                        <?php endforeach; ?>
                    </select></div>
                <div class="col-md-3"><input type="number" step="0.01" min="0" name="first_hour_rate"
                        class="form-control" placeholder="First hour"
                        value="<?= e((string) ($edit['first_hour_rate'] ?? '')) ?>" required></div>
                <div class="col-md-3"><input type="number" step="0.01" min="0" name="additional_hour_rate"
                        class="form-control" placeholder="Additional hour"
                        value="<?= e((string) ($edit['additional_hour_rate'] ?? '')) ?>" required></div>
                <div class="col-md-2"><select name="is_active" class="form-select">
                        <option value="1" <?= ($edit['is_active'] ?? 1) ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= isset($edit) && !$edit['is_active'] ? 'selected' : '' ?>>Inactive</option>
                    </select></div>
                <div class="col-md-1"><button class="btn btn-primary w-100">Save</button></div>
            </div>
        </form>
    </div>
</div>
<div class="panel-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Vehicle</th>
                    <th>First Hour</th>
                    <th>Additional Hour</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody><?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= e($r['vehicle_type']) ?></td>
                        <td><?= money((float) $r['first_hour_rate']) ?></td>
                        <td><?= money((float) $r['additional_hour_rate']) ?></td>
                        <td><span
                                class="badge bg-<?= $r['is_active'] ? 'success' : 'secondary' ?>"><?= $r['is_active'] ? 'Active' : 'Inactive' ?></span>
                        </td>
                        <td class="text-end"><a href="<?= BASE_URL ?>admin/parking_rates.php?edit=<?= $r['id'] ?>"
                                class="btn btn-sm btn-outline-primary">Edit</a></td>
                    </tr><?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/panel_footer.php'; ?>