<?php
require_once __DIR__ . '/../includes/auth.php';
requireStudent();
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();
$menu = $pdo->query("SELECT * FROM mess_menu ORDER BY CASE WHEN day_of_week = 0 THEN 7 ELSE day_of_week END")->fetchAll();
$dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
$today = (int)date('w');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mess Menu – JBH Portal</title>
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
    .card{background:white;border:1px solid var(--border);border-radius:12px;overflow:hidden;}
    .card h2{font-family:'DM Serif Display';padding:24px 24px 0;}
    .meal-row{display:grid;grid-template-columns:100px 1fr 1fr 1fr;gap:16px;padding:16px 24px;border-bottom:1px solid var(--border);align-items:center;}
    .meal-row.today{background:rgba(200,151,58,0.06);}
    .meal-row:last-child{border:none;}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo">JBH</div>
  <a href="student.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
  <a href="mess-menu.php" class="nav-item active"><i class="fas fa-utensils"></i> Mess Menu</a>
  <a href="mess-bills.php" class="nav-item"><i class="fas fa-file-invoice-dollar"></i> Mess Bills</a>
  <a href="../api/logout.php" class="nav-item" style="margin-top:20px;color:#c04a2b"><i class="fas fa-sign-out-alt"></i> Logout</a>
</aside>
<div class="main">
  <div class="card">
    <h2>Weekly Mess Menu</h2>
    <div class="meal-row" style="background:var(--cream);font-family:'IBM Plex Mono';font-size:0.65rem;text-transform:uppercase;color:var(--muted);">
      <span>Day</span><span>Breakfast</span><span>Lunch</span><span>Dinner</span>
    </div>
    <?php foreach ($menu as $m):
      $dow = (int)$m['day_of_week'];
      $isToday = ($dow === $today);
    ?>
    <div class="meal-row <?= $isToday ? 'today' : '' ?>">
      <span><strong><?= $dayNames[$dow] ?></strong><?= $isToday ? ' (Today)' : '' ?></span>
      <span><?= htmlspecialchars($m['breakfast'] ?? '-') ?></span>
      <span><?= htmlspecialchars($m['lunch'] ?? '-') ?></span>
      <span><?= htmlspecialchars($m['dinner'] ?? '-') ?></span>
    </div>
    <?php endforeach; ?>
  </div>
</div>
</body>
</html>
