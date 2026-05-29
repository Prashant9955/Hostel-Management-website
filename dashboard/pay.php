<?php
require_once __DIR__ . '/../includes/auth.php';
requireStudent();
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();
$billId = (int)($_GET['bill'] ?? 0);

if ($billId) {
    $stmt = $pdo->prepare("SELECT * FROM mess_bills WHERE id = ? AND student_id = ?");
    $stmt->execute([$billId, $_SESSION['user_id']]);
    $bill = $stmt->fetch();
    if (!$bill || $bill['status'] === 'paid') {
        header('Location: mess-bills.php');
        exit;
    }
} else {
    $stmt = $pdo->prepare("SELECT * FROM mess_bills WHERE student_id = ? AND status = 'pending' ORDER BY month_year DESC LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $bill = $stmt->fetch();
}

$student = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$student->execute([$_SESSION['user_id']]);
$student = $student->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Pay Mess Bill – JBH Portal</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Serif+Display&family=IBM+Plex+Mono&family=Outfit&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
  <style>
    :root { --ink:#0b0c0e; --gold:#c8973a; --teal:#1a6b6b; --cream:#faf6ee; --muted:#8a8070; --border:#d4c8b0; --sidebar-w:260px; }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Outfit',sans-serif;background:var(--cream);color:var(--ink);display:flex;min-height:100vh;}
    .sidebar{width:var(--sidebar-w);min-height:100vh;background:var(--ink);padding:24px 0;position:fixed;left:0;top:0;}
    .sidebar-logo{width:40px;height:40px;background:rgba(200,151,58,0.15);border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:'Bebas Neue';color:var(--gold);margin:0 24px 20px;}
    .nav-item{display:flex;align-items:center;gap:12px;padding:11px 24px;color:rgba(255,255,255,0.5);font-size:0.85rem;text-decoration:none;}
    .nav-item:hover,.nav-item.active{color:var(--gold);}
    .main{margin-left:var(--sidebar-w);flex:1;padding:32px;}
    .card{background:white;border:1px solid var(--border);border-radius:12px;padding:32px;max-width:480px;}
    .card h2{font-family:'DM Serif Display';margin-bottom:20px;}
    .amount{font-size:2.5rem;font-family:'Bebas Neue';color:var(--gold);margin:20px 0;}
    .btn{padding:14px 28px;background:var(--ink);color:var(--gold);border:none;border-radius:6px;cursor:pointer;font-family:'IBM Plex Mono';font-size:0.85rem;letter-spacing:1px;}
    .btn:hover{background:var(--gold);color:var(--ink);}
    .info{padding:16px;background:rgba(200,151,58,0.08);border-radius:8px;margin-top:20px;font-size:0.85rem;color:#6b5500;}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo">JBH</div>
  <a href="student.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
  <a href="mess-bills.php" class="nav-item"><i class="fas fa-file-invoice-dollar"></i> Mess Bills</a>
  <a href="pay.php" class="nav-item active"><i class="fas fa-credit-card"></i> Pay Online</a>
  <a href="../api/logout.php" class="nav-item" style="margin-top:20px;color:#c04a2b"><i class="fas fa-sign-out-alt"></i> Logout</a>
</aside>
<div class="main">
  <div class="card">
    <h2>Pay Mess Bill</h2>
    <?php if ($bill): ?>
      <?php $parts = explode('-', $bill['month_year']); $monthName = date('F', mktime(0,0,0,(int)$parts[1],1)); ?>
      <p style="color:var(--muted);"><?= $monthName ?> <?= $parts[0] ?> · Due: <?= $bill['due_date'] ? date('d M Y', strtotime($bill['due_date'])) : '-' ?></p>
      <div class="amount">₹<?= number_format($bill['amount']) ?></div>
      <p style="font-size:0.85rem;color:var(--muted);">Pay securely via Razorpay (Card, UPI, Net Banking)</p>
      <button type="button" id="payBtn" class="btn" style="margin-top:20px;">
        <i class="fas fa-credit-card"></i> Pay ₹<?= number_format($bill['amount']) ?>
      </button>
      <div class="info">
        <strong>Demo Mode:</strong> Razorpay test keys required. Add your keys in <code>config/razorpay.php</code> and create <code>api/create-order.php</code> to enable real payments.
      </div>
    <?php else: ?>
      <p style="color:var(--muted);">No pending bills. <a href="mess-bills.php">View all bills</a></p>
    <?php endif; ?>
  </div>
</div>

<?php if ($bill): ?>
<script>
document.getElementById('payBtn').addEventListener('click', function() {
  const amount = <?= (int)($bill['amount'] * 100) ?>; // Razorpay expects paise
  const options = {
    key: 'rzp_test_XXXXXXXX', // Replace with your Razorpay key
    amount: amount,
    currency: 'INR',
    name: 'JBH Hostel - Mess Bill',
    description: 'Mess Bill <?= $monthName ?> <?= $parts[0] ?? '' ?>',
    handler: function(response) {
      // Verify payment on server and update status
      fetch('../api/verify-payment.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'razorpay_payment_id=' + response.razorpay_payment_id + '&razorpay_order_id=' + response.razorpay_order_id + '&bill_id=<?= $bill['id'] ?>'
      }).then(r => r.json()).then(data => {
        if (data.success) {
          alert('Payment successful!');
          window.location.href = 'mess-bills.php';
        } else alert('Payment verification failed.');
      });
    }
  };
  // In demo mode without keys, show message
  if (options.key === 'rzp_test_XXXXXXXX') {
    alert('Razorpay is in demo mode. Configure your test keys in config/razorpay.php and create api/create-order.php + api/verify-payment.php for real payments.');
    return;
  }
  const rzp = new Razorpay(options);
  rzp.open();
});
</script>
<?php endif; ?>
</body>
</html>
