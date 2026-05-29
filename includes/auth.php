<?php
/**
 * Authentication & Session Management - JBH Hostel
 */

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . getBaseUrl() . 'login.php');
        exit;
    }
}

function requireStudent() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'student') {
        header('Location: ' . getBaseUrl() . 'dashboard/admin.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!in_array($_SESSION['user_role'], ['admin', 'warden'])) {
        header('Location: ' . getBaseUrl() . 'dashboard/student.php');
        exit;
    }
}

function getCurrentUser() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'role' => $_SESSION['user_role'] ?? null,
        'name' => $_SESSION['user_name'] ?? null,
        'student_id' => $_SESSION['student_id'] ?? null,
    ];
}

function getBaseUrl() {
    $script = $_SERVER['SCRIPT_NAME'];
    $path = dirname($script);
    if (strpos($path, 'dashboard') !== false) {
        return '../';
    }
    if (strpos($path, 'pages') !== false || strpos($path, 'api') !== false) {
        return '../';
    }
    return './';
}

function basePath($path = '') {
    return getBaseUrl() . ltrim($path, '/');
}
