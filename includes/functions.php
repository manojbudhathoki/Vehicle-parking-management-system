<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . BASE_URL . ltrim($path, '/'));
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function getFlashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(?string $token): void
{
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user']['id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        flash('warning', 'Please log in first.');
        redirect('login.php');
    }
}

function requireRole(string ...$roles): void
{
    requireLogin();
    $role = $_SESSION['user']['role'] ?? '';
    if (!in_array($role, $roles, true)) {
        http_response_code(403);
        exit('Access denied.');
    }
}

function old(string $key, string $default = ''): string
{
    return e($_POST[$key] ?? $default);
}

function money(float $amount): string
{
    return 'Rs. ' . number_format($amount, 2);
}

function calculateParkingFee(PDO $pdo, string $vehicleType, int $durationMinutes): float
{
    $stmt = $pdo->prepare(
        "SELECT first_hour_rate, additional_hour_rate
         FROM parking_rates
         WHERE vehicle_type = ? AND is_active = 1
         ORDER BY id DESC LIMIT 1"
    );
    $stmt->execute([$vehicleType]);
    $rate = $stmt->fetch();

    if (!$rate) {
        return 0.0;
    }

    $hours = max(1, (int) ceil($durationMinutes / 60));
    $first = (float) $rate['first_hour_rate'];
    $additional = (float) $rate['additional_hour_rate'];

    return $hours <= 1 ? $first : $first + (($hours - 1) * $additional);
}

function audit(PDO $pdo, int $userId, string $action, string $module, ?int $recordId = null, string $description = ''): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $stmt = $pdo->prepare(
        "INSERT INTO audit_logs (user_id, action, module, record_id, description, ip_address)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$userId, $action, $module, $recordId, $description, $ip]);
}
