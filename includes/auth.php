<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function currentRole(): ?string {
    return $_SESSION['role'] ?? null;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requireRole(array $roles): void {
    requireLogin();
    if (!in_array(currentRole(), $roles, true)) {
        header('Location: ' . BASE_URL . '/unauthorized.php');
        exit;
    }
}

function dashboardUrlForRole(string $role): string {
    switch ($role) {
        case 'operator':  return BASE_URL . '/operator/dashboard.php';
        case 'dosen':     return BASE_URL . '/dosen/dashboard.php';
        case 'mahasiswa': return BASE_URL . '/portal.php'; // peserta kini akses lewat Portal Publik (NIM/NISN)
        default:          return BASE_URL . '/login.php';
    }
}

function currentMahasiswaId(): ?int {
    global $pdo;
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
        return null;
    }
    if (isset($_SESSION['mahasiswa_id'])) {
        return (int)$_SESSION['mahasiswa_id'];
    }
    $stmt = $pdo->prepare('SELECT id FROM mahasiswa WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $id = $stmt->fetchColumn();
    if ($id) {
        $_SESSION['mahasiswa_id'] = (int)$id;
        return (int)$id;
    }
    return null;
}

function currentDosenId(): ?int {
    global $pdo;
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dosen') {
        return null;
    }
    if (isset($_SESSION['dosen_id'])) {
        return (int)$_SESSION['dosen_id'];
    }
    $stmt = $pdo->prepare('SELECT id FROM dosen WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $id = $stmt->fetchColumn();
    if ($id) {
        $_SESSION['dosen_id'] = (int)$id;
        return (int)$id;
    }
    return null;
}
