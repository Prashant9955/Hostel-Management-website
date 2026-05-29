<?php
require_once __DIR__ . '/../includes/auth.php';
requireStudent();
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();
$leaves = $pdo->prepare("SELECT * FROM leave_applications WHERE student_id = ? ORDER BY created_at DESC");
$leaves->execute([$_SESSION['user_id']]);
$list = $leaves->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Leave Request – JBH Portal</title>
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
    .card{background:white;border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:24px;}
    .form-group{margin-bottom:16px;}
    .form-group label{display:block;font-family:'IBM Plex Mono';font-size:0.65rem;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:6px;}
    .form-group input,.form-group textarea{width:100%;padding:12px;border:1.5px solid var(--border);border-radius:6px;}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
    .btn{padding:12px 24px;background:var(--ink);color:var(--gold);border:none;border-radius:6px;cursor:pointer;}
    .leave-item{padding:16px 0;border-bottom:1px solid var(--border);}
    .badge{padding:3px 8px;border-radius:20px;font-size:0.65rem;}
    .badge-pending{background:#fef9ec;color:#b45309;}
    .badge-approved{background:#f0fdf4;color:#16a34a;}
    .badge-rejected{background:#fef2f2;color:#dc2626;}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo">JBH</div>
  <a href="student.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
  <a href="leave.php" class="nav-item active"><i class="fas fa-calendar-check"></i> Leave</a>
  <a href="../api/logout.php" class="nav-item" style="margin-top:20px;color:#c04a2b"><i class="fas fa-sign-out-alt"></i> Logout</a>
</aside>
<div class="main">
  <div class="card">
    <h2 style="font-family:'DM Serif Display';margin-bottom:20px;">Apply for Leave</h2>
    <form id="leaveForm" action="../api/leave.php" method="POST">
      <div class="form-row">
        <div class="form-group"><label>From Date</label><input type="date" name="from_date" required/></div>
        <div class="form-group"><label>To Date</label><input type="date" name="to_date" required/></div>
      </div>
      <div class="form-group"><label>Reason</label><textarea name="reason" rows="3" placeholder="Reason for leave..."></textarea></div>
      <button type="submit" class="btn"><i class="fas fa-paper-plane"></i> Submit</button>
    </form>
  </div>
  <div class="card">
    <h2 style="font-family:'DM Serif Display';margin-bottom:20px;">My Leave History</h2>
    <?php foreach ($list as $l): ?>
    <div class="leave-item">
      <strong><?= $l['from_date'] ?> to <?= $l['to_date'] ?></strong>
      <span class="badge badge-<?= $l['status'] ?>"><?= ucfirst($l['status']) ?></span>
      <p style="font-size:0.9rem;color:var(--muted);margin-top:4px;"><?= htmlspecialchars($l['reason'] ?? '-') ?></p>
    </div>
    <?php endforeach; ?>
    <?php if (empty($list)): ?><p style="color:var(--muted);">No leave requests yet.</p><?php endif; ?>
  </div>
</div>
<script>
document.getElementById('leaveForm').addEventListener('submit',async function(e){
  e.preventDefault();
  const res=await fetch(this.action,{method:'POST',body:new FormData(this)});
  const data=await res.json();
  alert(data.success?'Leave request submitted!':data.message||'Error');
  if(data.success) location.reload();
});
</script>
</body>
</html>
