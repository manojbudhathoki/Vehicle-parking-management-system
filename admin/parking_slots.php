<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin_auth.php';
requireAdminLogin();
$types = ['Motorcycle', 'Scooter', 'Car', 'Van', 'Bus', 'Truck', 'Other'];
$statuses = ['available', 'reserved', 'occupied', 'maintenance', 'inactive'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf_token'] ?? null);
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? 'create';
    $n = trim($_POST['slot_number'] ?? '');
    $type = $_POST['vehicle_type'] ?? '';
    $zone = trim($_POST['zone'] ?? '');
    $status = $_POST['status'] ?? 'available';
    try {
        if (!in_array($type, $types, true) || !$n || !$zone)
            throw new Exception('Slot number, type and zone are required.');
        if ($action === 'create') {
            $s = $pdo->prepare("INSERT INTO parking_slots(slot_number,vehicle_type,zone,status,description) VALUES(?,?,?,?,?)");
            $s->execute([$n, $type, $zone, $status, trim($_POST['description'] ?? '')]);
            flash('success', 'Slot added.');
        } else {
            $s = $pdo->prepare("UPDATE parking_slots SET slot_number=?,vehicle_type=?,zone=?,status=?,description=? WHERE id=?");
            $s->execute([$n, $type, $zone, $status, trim($_POST['description'] ?? ''), $id]);
            flash('success', 'Slot updated.');
        }
    } catch (PDOException $e) {
        flash('danger', 'Unable to save slot. Slot number may already exist.');
    } catch (Throwable $e) {
        flash('danger', $e->getMessage());
    }
    redirect('admin/parking_slots.php');
}
$edit = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM parking_slots WHERE id=?");
    $s->execute([(int) $_GET['edit']]);
    $edit = $s->fetch();
}
$rows = $pdo->query("SELECT * FROM parking_slots ORDER BY zone,slot_number")->fetchAll();
$pageTitle = 'Parking Slots';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<div class="panel-card mb-4">
    <div class="panel-card-header">
        <h6><?= $edit ? 'Edit Slot' : 'Add Parking Slot' ?></h6><?php if ($edit): ?><a
                href="<?= BASE_URL ?>admin/parking_slots.php"
                class="btn btn-sm btn-outline-secondary">Cancel</a><?php endif; ?>
    </div>
    <div class="panel-card-body">
        <form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden"
                name="action" value="<?= $edit ? 'update' : 'create' ?>"><input type="hidden" name="id"
                value="<?= $edit['id'] ?? 0 ?>">
            <div class="row g-2">
                <div class="col-md-2"><input name="slot_number" class="form-control" placeholder="A01"
                        value="<?= e($edit['slot_number'] ?? '') ?>" required></div>
                <div class="col-md-3"><select name="vehicle_type" class="form-select"><?php foreach ($types as $x): ?>
                            <option <?= $x === ($edit['vehicle_type'] ?? 'Car') ? 'selected' : '' ?>><?= $x ?></option>
                        <?php endforeach; ?>
                    </select></div>
                <div class="col-md-2"><input name="zone" class="form-control" placeholder="Zone"
                        value="<?= e($edit['zone'] ?? 'A') ?>" required></div>
                <div class="col-md-2"><select name="status" class="form-select"><?php foreach ($statuses as $x): ?>
                            <option <?= $x === ($edit['status'] ?? 'available') ? 'selected' : '' ?>><?= $x ?></option>
                        <?php endforeach; ?>
                    </select></div>
                <div class="col-md-2"><input name="description" class="form-control" placeholder="Description"
                        value="<?= e($edit['description'] ?? '') ?>"></div>
                <div class="col-md-1"><button class="btn btn-primary w-100">Save</button></div>
            </div>
        </form>
    </div>
</div>
<div class="row g-3"><?php foreach ($rows as $r): ?>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="slot slot-<?= e($r['status']) ?> position-relative"><a
                    href="<?= BASE_URL ?>admin/parking_slots.php?edit=<?= $r['id'] ?>"
                    class="position-absolute top-0 end-0 m-2 btn btn-sm btn-light">Edit</a><?= e($r['slot_number']) ?><br><small><?= e($r['vehicle_type']) ?>
                    · <?= e($r['status']) ?></small><br><small><?= e($r['zone']) ?></small></div>
        </div><?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/panel_footer.php'; ?>