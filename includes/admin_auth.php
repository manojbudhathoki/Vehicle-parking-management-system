<?php
require_once __DIR__ . '/auth.php';
function requireAdminLogin(): void
{
    requireRole(ROLE_ADMIN);
}
