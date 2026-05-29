<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();
$bills = $pdo->query("SELECT mb.*, s.full_name, s.student_id FROM mess_bills mb JOIN students s ON mb.student_id = s.id ORDER BY mb.month_year DESC, s.student_id LIMIT 50")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mess Bills – Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Serif+Display&family=IBM+Plex+Mono&family=Outfit&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    :root { --ink:#0b0c0e; --gold:#c8973a; --cream:#faf6ee; --muted:#8a8070; --border:#d4c8b0; --sidebar-w:260px; }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Outfit',sans-serif;background:var(--cream);color:var(--ink);display:flex;min-height:100vh;}
    .sidebar{width:var(--sidebar-w);min-height:100vh;background:var(--ink);padding:24px 0;position:fixed;left:0;top:0;}
    .sidebar-logo{width:40px;height:40px;background:rgba(200,151,58,0.15);border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:'Bebas Neue';color:var(--gold);margin:0 24px 20px;}
    .nav-item{display:flex;align-items:center;gap:12px;padding:11px 24px;color:rgba(255,255,255,0.5);font-size:0.85rem;text-decoration:none;}
    .nav-item:hover,.nav-item.active{color:var(--gold);}
    .main{margin-left:var(--sidebar-w);flex:1;padding:32px;}
    .card{background:white;border:1px solid var(--border);border-radius:12px;padding:24px;}
    .card h2{font-family:'DM Serif Display';margin-bottom:20px;}
    table{width:100%;border-collapse:collapse;}
    th,td{padding:12px;text-align:left;border-bottom:1px solid var(--border);}
    th{font-family:'IBM Plex Mono';font-size:0.65rem;letter-spacing:2px;text-transform:uppercase;color:var(--muted);}
    .badge{padding:3px 8px;border-radius:20px;font-size:0.65rem;text-transform:uppercase;}
    .badge-paid{background:#f0fdf4;color:#16a34a;}
    .badge-pending{background:#fef9ec;color:#b45309;}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo">JBH</div>
  <a href="admin.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
  <a href="admin-notices.php" class="nav-item"><i class="fas fa-bell"></i> Notices</a>
  <a href="admin-complaints.php" class="nav-item"><i class="fas fa-tools"></i> Complaints</a>
  <a href="admin-students.php" class="nav-item"><i class="fas fa-users"></i> Students</a>
  <a href="admin-mess.php" class="nav-item active"><i class="fas fa-utensils"></i> Mess Bills</a>
  <a href="admin-leave.php" class="nav-item"><i class="fas fa-calendar-check"></i> Leave</a>
  <a href="../api/logout.php" class="nav-item" style="margin-top:20px;color:#c04a2b"><i class="fas fa-sign-out-alt"></i> Logout</a>
</aside>
<div class="main">
  <div class="card">
    <h2>Mess Bills Overview</h2>
    <table>
      <thead><tr><th>Student</th><th>Month</th><th>Amount</th><th>Due Date</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($bills as $b): ?>
        <tr>
          <td><?= htmlspecialchars($b['full_name']) ?> (<?= $b['student_id'] ?>)</td>
          <td><?= $b['month_year'] ?></td>
          <td>₹<?= number_format($b['amount']) ?></td>
          <td><?= $b['due_date'] ?? '-' ?></td>
          <td><span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if (empty($bills)): ?><p style="color:var(--muted);">No mess bills.</p><?php endif; ?>
  </div>
</div>
</body>
</html>
