<?php
require_once __DIR__ . '/auth.php';
function requireCustomerLogin(): void
{
    requireRole(ROLE_CUSTOMER);
}
