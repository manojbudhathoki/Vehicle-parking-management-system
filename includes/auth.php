<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

function loginUser(PDO $pdo, string $email, string $password): bool
{
    $stmt = $pdo->prepare("SELECT id, full_name, email, password_hash, role, status FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || $user['status'] !== 'active' || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];
    return true;
}

function redirectAfterLogin(): never
{
    $role = $_SESSION['user']['role'] ?? '';
    if ($role === ROLE_ADMIN)
        redirect('admin/dashboard.php');
    if ($role === ROLE_STAFF)
        redirect('staff/dashboard.php');
    redirect('customer/dashboard.php');
}
