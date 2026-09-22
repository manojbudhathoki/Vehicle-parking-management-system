<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/customer_auth.php';
requireCustomerLogin();
$uid = (int) $_SESSION['user']['id'];
$types = ['Motorcycle', 'Scooter', 'Car', 'Van', 'Bus', 'Truck', 'Other'];
$edit = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM vehicles WHERE id=? AND customer_id=?");
    $s->execute([(int) $_GET['edit'], $uid]);
    $edit = $s->fetch();
    if (!$edit) {
        flash('danger', 'Vehicle not found.');
        redirect('customer/vehicles.php');
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf_token'] ?? null);
    $id = (int) ($_POST['id'] ?? 0);
    $n = trim($_POST['vehicle_number'] ?? '');
    $t = $_POST['vehicle_type'] ?? '';
    $b = trim($_POST['brand'] ?? '');
    $m = trim($_POST['model'] ?? '');
    $c = trim($_POST['color'] ?? '');
    if (!$n || !in_array($t, $types, true)) {
        flash('danger', 'Vehicle number and valid type are required.');
    } else {
        try {
            if ($id) {
                $s = $pdo->prepare("UPDATE vehicles SET vehicle_number=?,vehicle_type=?,brand=?,model=?,color=? WHERE id=? AND customer_id=?");
                $s->execute([$n, $t, $b, $m, $c, $id, $uid]);
                flash('success', 'Vehicle updated.');
            } else {
                $s = $pdo->prepare("INSERT INTO vehicles(customer_id,vehicle_number,vehicle_type,brand,model,color,status) VALUES(?,?,?,?,?,?,'active')");
                $s->execute([$uid, $n, $t, $b, $m, $c]);
                flash('success', 'Vehicle added.');
            }
            redirect('customer/vehicles.php');
        } catch (PDOException $e) {
            flash('danger', 'Vehicle number already exists in your account.');
        }
    }
}
$pageTitle = $edit ? 'Edit Vehicle' : 'Add Vehicle';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="panel-card">
            <div class="panel-card-header">
                <h6><?= $edit ? 'Edit Vehicle' : 'Add Vehicle' ?></h6><a href="<?= BASE_URL ?>customer/vehicles.php"
                    class="btn btn-sm btn-outline-secondary">Back</a>
            </div>
            <div class="panel-card-body">
                <form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input
                        type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Vehicle Number</label><input
                                name="vehicle_number" class="form-control" value="<?= e($edit['vehicle_number'] ?? '') ?>"
                                required></div>
                        <div class="col-md-6"><label class="form-label">Vehicle Type</label><select name="vehicle_type"
                                class="form-select"><?php foreach ($types as $x): ?>
                                    <option <?= $x === ($edit['vehicle_type'] ?? 'Car') ? 'selected' : '' ?>><?= $x ?></option>
                                <?php endforeach; ?>
                            </select></div>
                        <div class="col-md-4"><label class="form-label">Brand</label><input name="brand"
                                class="form-control" value="<?= e($edit['brand'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Model</label><input name="model"
                                class="form-control" value="<?= e($edit['model'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Color</label><input name="color"
                                class="form-control" value="<?= e($edit['color'] ?? '') ?>"></div>
                        <div class="col-12"><button class="btn btn-primary">Save Vehicle</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/panel_footer.php'; ?>