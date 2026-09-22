<?php
require_once __DIR__ . '/auth.php';
function requireStaffLogin(): void
{
    // Staff area is intentionally restricted to staff accounts.
    // Administrators have their own protected admin area.
    requireRole(ROLE_STAFF);
}
