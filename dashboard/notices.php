<?php
require_once __DIR__ . '/../includes/auth.php';
requireStudent();
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();
$notices = $pdo->query("SELECT * FROM notices ORDER BY is_pinned DESC, created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Notices – JBH Portal</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Serif+Display&family=IBM+Plex+Mono&family=Outfit&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    :root { --ink:#0b0c0e; --gold:#c8973a; --teal:#1a6b6b; --cream:#faf6ee; --muted:#8a8070; --border:#d4c8b0; --sidebar-w:260px; --rust:#c04a2b; }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Outfit',sans-serif;background:var(--cream);color:var(--ink);display:flex;min-height:100vh;}
    .sidebar{width:var(--sidebar-w);min-height:100vh;background:var(--ink);padding:24px 0;position:fixed;left:0;top:0;}
    .sidebar-logo{width:40px;height:40px;background:rgba(200,151,58,0.15);border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:'Bebas Neue';color:var(--gold);margin:0 24px 20px;}
    .nav-item{display:flex;align-items:center;gap:12px;padding:11px 24px;color:rgba(255,255,255,0.5);font-size:0.85rem;text-decoration:none;}
    .nav-item:hover,.nav-item.active{color:var(--gold);}
    .main{margin-left:var(--sidebar-w);flex:1;padding:32px;}
    .card{background:white;border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:20px;}
    .card h2{font-family:'DM Serif Display';margin-bottom:16px;}
    .notice-item{padding:20px 0;border-bottom:1px solid var(--border);}
    .notice-item:last-child{border:none;}
    .notice-item h3{font-size:1rem;margin-bottom:8px;}
    .notice-item p{font-size:0.9rem;color:var(--muted);line-height:1.6;}
    .notice-item small{font-size:0.75rem;color:var(--muted);}
    .badge{display:inline-block;padding:2px 8px;border-radius:4px;font-size:0.65rem;text-transform:uppercase;margin-left:8px;}
    .badge-urgent{background:#fef2f2;color:#dc2626;}
    .badge-general{background:#f5f5f5;color:#666;}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo">JBH</div>
  <a href="student.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
  <a href="notices.php" class="nav-item active"><i class="fas fa-bell"></i> Notices</a>
  <a href="mess-bills.php" class="nav-item"><i class="fas fa-file-invoice-dollar"></i> Mess Bills</a>
  <a href="../api/logout.php" class="nav-item" style="margin-top:20px;color:var(--rust)"><i class="fas fa-sign-out-alt"></i> Logout</a>
</aside>
<div class="main">
  <div class="card">
    <h2>Hostel Notices</h2>
    <?php foreach ($notices as $n): ?>
    <div class="notice-item">
      <h3><?= htmlspecialchars($n['title']) ?><span class="badge badge-<?= $n['category'] === 'urgent' ? 'urgent' : 'general' ?>"><?= ucfirst($n['category']) ?></span></h3>
      <p><?= nl2br(htmlspecialchars($n['content'] ?? '')) ?></p>
      <small><?= date('d M Y, H:i', strtotime($n['created_at'])) ?></small>
    </div>
    <?php endforeach; ?>
    <?php if (empty($notices)): ?><p style="color:var(--muted);">No notices.</p><?php endif; ?>
  </div>
</div>
</body>
</html>
