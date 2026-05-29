<?php
/**
 * Login API - JBH Hostel
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$userId = trim($_POST['userId'] ?? '');
$password = $_POST['password'] ?? '';
$role = trim($_POST['role'] ?? 'student');

if (empty($userId) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please enter ID and password']);
    exit;
}

$pdo = getDB();
$redirect = 'dashboard/student.php';

try {
    if ($role === 'student') {
        $stmt = $pdo->prepare("SELECT id, student_id, full_name, password, room_number, block FROM students WHERE student_id = ? AND is_active = 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = 'student';
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['student_id'] = $user['student_id'];
            $_SESSION['room'] = ($user['room_number'] ?? '') . ($user['block'] ? ' (' . $user['block'] . ')' : '');
            echo json_encode(['success' => true, 'redirect' => $redirect]);
            exit;
        }
    } else {
        $stmt = $pdo->prepare("SELECT id, username, full_name, password, role FROM admins WHERE username = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['full_name'];
            $redirect = 'dashboard/admin.php';
            echo json_encode(['success' => true, 'redirect' => $redirect]);
            exit;
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid credentials. Please try again.']);
