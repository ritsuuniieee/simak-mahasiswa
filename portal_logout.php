<?php
require_once __DIR__ . '/includes/auth.php';
unset($_SESSION['portal_peserta_id']);
header('Location: ' . BASE_URL . '/portal.php');
exit;
