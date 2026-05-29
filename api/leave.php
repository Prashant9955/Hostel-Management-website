<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isLoggedIn() || $_SESSION['user_role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$from = $_POST['from_date'] ?? '';
$to = $_POST['to_date'] ?? '';
$reason = trim($_POST['reason'] ?? '');

if (empty($from) || empty($to)) {
    echo json_encode(['success' => false, 'message' => 'Please provide from and to dates']);
    exit;
}

$fromDate = strtotime($from);
$toDate = strtotime($to);
if ($fromDate > $toDate) {
    echo json_encode(['success' => false, 'message' => 'From date must be before to date']);
    exit;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("INSERT INTO leave_applications (student_id, from_date, to_date, reason) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $from, $to, $reason]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
