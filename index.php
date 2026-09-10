<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . dashboardUrlForRole(currentRole()));
} else {
    header('Location: ' . BASE_URL . '/portal.php');
}
exit;
