<?php
/**
 * Razorpay Payment Verification - JBH Hostel
 * 
 * Setup:
 * 1. Install: composer require razorpay/razorpay
 * 2. Add your keys in config/razorpay.php
 * 3. Create order via Razorpay API in create-order.php before opening checkout
 * 4. Verify signature and update mess_bills status here
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isLoggedIn() || $_SESSION['user_role'] !== 'student') {
    echo json_encode(['success' => false]);
    exit;
}

$paymentId = $_POST['razorpay_payment_id'] ?? '';
$orderId = $_POST['razorpay_order_id'] ?? '';
$billId = (int)($_POST['bill_id'] ?? 0);

if (empty($paymentId) || empty($orderId) || $billId <= 0) {
    echo json_encode(['success' => false]);
    exit;
}

try {
    $pdo = getDB();
    
    // Verify bill belongs to student
    $stmt = $pdo->prepare("SELECT id FROM mess_bills WHERE id = ? AND student_id = ?");
    $stmt->execute([$billId, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false]);
        exit;
    }
    
    // In production: verify Razorpay signature and payment status via API
    // $api = new \Razorpay\Api\Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
    // $payment = $api->payment->fetch($paymentId);
    // if ($payment->status === 'captured') { ... }
    
    // For demo: mark as paid (skip in production without proper verification)
    $update = $pdo->prepare("UPDATE mess_bills SET status = 'paid', paid_at = NOW(), razorpay_payment_id = ?, razorpay_order_id = ? WHERE id = ?");
    $update->execute([$paymentId, $orderId, $billId]);
    
    // Log to payments table
    $log = $pdo->prepare("INSERT INTO payments (mess_bill_id, student_id, amount, razorpay_order_id, razorpay_payment_id, status) SELECT ?, student_id, amount, ?, ?, 'captured' FROM mess_bills WHERE id = ?");
    $log->execute([$billId, $orderId, $paymentId, $billId]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false]);
}
