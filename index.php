<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . dashboardUrlForRole(currentRole()));
} else {
    // Landing page = portal publik (rekapan terbuka, tanpa login)
    header('Location: ' . BASE_URL . '/publik/');
}
exit;
