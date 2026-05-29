<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (!isLoggedIn() || $_SESSION['user_role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? 'other';
    $room = trim($_POST['room_location'] ?? '');

    if (empty($subject)) {
        echo json_encode(['success' => false, 'message' => 'Subject is required']);
        exit;
    }

    $allowed = ['electrical', 'plumbing', 'furniture', 'cleaning', 'other'];
    if (!in_array($category, $allowed)) $category = 'other';

    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("INSERT INTO complaints (student_id, subject, description, category, room_location) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $subject, $description, $category, $room]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
