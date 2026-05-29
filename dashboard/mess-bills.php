<?php
require_once __DIR__ . '/../includes/auth.php';
requireStudent();
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();
$bills = $pdo->prepare("SELECT * FROM mess_bills WHERE student_id = ? ORDER BY month_year DESC");
$bills->execute([$_SESSION['user_id']]);
$list = $bills->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mess Bills – JBH Portal</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Serif+Display&family=IBM+Plex+Mono&family=Outfit&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    :root { --ink:#0b0c0e; --gold:#c8973a; --teal:#1a6b6b; --cream:#faf6ee; --muted:#8a8070; --border:#d4c8b0; --sidebar-w:260px; }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Outfit',sans-serif;background:var(--cream);color:var(--ink);display:flex;min-height:100vh;}
    .sidebar{width:var(--sidebar-w);min-height:100vh;background:var(--ink);padding:24px 0;position:fixed;left:0;top:0;}
    .sidebar-logo{width:40px;height:40px;background:rgba(200,151,58,0.15);border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:'Bebas Neue';color:var(--gold);margin:0 24px 20px;}
    .nav-item{display:flex;align-items:center;gap:12px;padding:11px 24px;color:rgba(255,255,255,0.5);font-size:0.85rem;text-decoration:none;}
    .nav-item:hover,.nav-item.active{color:var(--gold);}
    .main{margin-left:var(--sidebar-w);flex:1;padding:32px;}
    .card{background:white;border:1px solid var(--border);border-radius:12px;padding:24px;}
    .card h2{font-family:'DM Serif Display';margin-bottom:20px;}
    .bill-item{display:flex;justify-content:space-between;align-items:center;padding:16px 0;border-bottom:1px solid var(--border);}
    .bill-item:last-child{border:none;}
    .btn{padding:10px 20px;background:var(--ink);color:var(--gold);border-radius:6px;text-decoration:none;font-size:0.8rem;display:inline-block;}
    .btn:hover{background:var(--gold);color:var(--ink);}
    .badge{padding:3px 10px;border-radius:20px;font-size:0.65rem;text-transform:uppercase;}
    .badge-paid{background:#f0fdf4;color:#16a34a;}
    .badge-pending{background:#fef9ec;color:#b45309;}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo">JBH</div>
  <a href="student.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
  <a href="mess-bills.php" class="nav-item active"><i class="fas fa-file-invoice-dollar"></i> Mess Bills</a>
  <a href="pay.php" class="nav-item"><i class="fas fa-credit-card"></i> Pay Online</a>
  <a href="../api/logout.php" class="nav-item" style="margin-top:20px;color:#c04a2b"><i class="fas fa-sign-out-alt"></i> Logout</a>
</aside>
<div class="main">
  <div class="card">
    <h2>My Mess Bills</h2>
    <?php foreach ($list as $b):
      $parts = explode('-', $b['month_year']);
      $monthName = date('F', mktime(0,0,0,(int)$parts[1],1));
    ?>
    <div class="bill-item">
      <div>
        <strong><?= $monthName ?> <?= $parts[0] ?></strong>
        <p style="font-size:0.85rem;color:var(--muted);">Due: <?= $b['due_date'] ? date('d M Y', strtotime($b['due_date'])) : '-' ?></p>
      </div>
      <div style="display:flex;align-items:center;gap:16px;">
        <span style="font-size:1.2rem;font-weight:600;">₹<?= number_format($b['amount']) ?></span>
        <span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span>
        <?php if ($b['status'] === 'pending'): ?>
        <a href="pay.php?bill=<?= $b['id'] ?>" class="btn">Pay Now</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($list)): ?><p style="color:var(--muted);">No bills.</p><?php endif; ?>
  </div>
</div>
</body>
</html>
