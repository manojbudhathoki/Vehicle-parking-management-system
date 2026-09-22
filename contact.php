<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf_token'] ?? null);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $message) {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name,email,message) VALUES (?,?,?)");
        $stmt->execute([$name, $email, $message]);
        flash('success', 'Message sent successfully.');
        redirect('contact.php');
    } else
        flash('danger', 'Please provide valid contact details.');
}
$pageTitle = 'Contact';
require __DIR__ . '/includes/header.php';
?>
<h1>Contact Us</h1>
<div class="row">
    <div class="col-md-7">
        <div class="card p-4">
            <form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control"
                        required></div>
                <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email"
                        class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Message</label><textarea name="message" rows="5"
                        class="form-control" required></textarea></div>
                <button class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>