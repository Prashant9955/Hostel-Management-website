<?php
require_once __DIR__ . '/../includes/auth.php';
requireStudent();

require_once __DIR__ . '/../config/database.php';
$pdo = getDB();
$userId = $_SESSION['user_id'];

// Fetch student info
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$userId]);
$student = $stmt->fetch();
if (!$student) { header('Location: ../login.php'); exit; }

// Initials for avatar
$initials = implode('', array_map(fn($w) => $w[0], explode(' ', $student['full_name'], 2)));

// Stats
$currentBill = $pdo->prepare("SELECT amount, status FROM mess_bills WHERE student_id = ? AND month_year = ? ORDER BY id DESC LIMIT 1");
$currentBill->execute([$userId, date('Y-m')]);
$bill = $currentBill->fetch();
$currentBillAmt = $bill ? number_format($bill['amount']) : '0';
$billStatus = $bill['status'] ?? 'pending';

$pendingComplaints = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE student_id = ? AND status IN ('pending','in_progress')");
$pendingComplaints->execute([$userId]);
$pendingCount = $pendingComplaints->fetchColumn();

// Notices
$notices = $pdo->query("SELECT * FROM notices ORDER BY created_at DESC LIMIT 6")->fetchAll();

// Mess bills
$bills = $pdo->prepare("SELECT * FROM mess_bills WHERE student_id = ? ORDER BY month_year DESC LIMIT 4");
$bills->execute([$userId]);
$messBills = $bills->fetchAll();

// Mess menu (day 0=Sun, 1=Mon... - order Mon first)
$menu = $pdo->query("SELECT * FROM mess_menu ORDER BY CASE WHEN day_of_week = 0 THEN 7 ELSE day_of_week END")->fetchAll();
$dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
$today = (int)date('w');

// Complaints
$complaints = $pdo->prepare("SELECT * FROM complaints WHERE student_id = ? ORDER BY created_at DESC LIMIT 5");
$complaints->execute([$userId]);
$myComplaints = $complaints->fetchAll();

$noticeCount = $pdo->query("SELECT COUNT(*) FROM notices WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
$dueBillsCount = $pdo->prepare("SELECT COUNT(*) FROM mess_bills WHERE student_id = ? AND status = 'pending'");
$dueBillsCount->execute([$userId]);
$dueBillsCount = $dueBillsCount->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Dashboard – JBH Portal</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Serif+Display:ital@0;1&family=IBM+Plex+Mono:wght@400;600&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    :root { --ink:#0b0c0e; --paper:#f5f0e8; --gold:#c8973a; --gold-light:#e8b85a; --rust:#c04a2b; --teal:#1a6b6b; --teal-light:#2a9090; --cream:#faf6ee; --muted:#8a8070; --border:#d4c8b0; --sidebar-w:260px; }
    *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
    body { font-family:'Outfit',sans-serif; background:var(--cream); color:var(--ink); display:flex; min-height:100vh; cursor:none; overflow-x:hidden; }
    .cursor { width:8px;height:8px;background:var(--gold);border-radius:50%;position:fixed;top:0;left:0;pointer-events:none;z-index:9999; }
    .cursor-ring { width:30px;height:30px;border:1px solid var(--gold);border-radius:50%;position:fixed;top:0;left:0;pointer-events:none;z-index:9998;transition:transform 0.2s cubic-bezier(.25,.8,.25,1); }
    .sidebar { width:var(--sidebar-w); min-height:100vh; position:fixed; top:0; left:0; z-index:100; background:var(--ink); display:flex; flex-direction:column; padding:0; overflow:hidden; transition:width 0.3s; }
    .sidebar-header { padding:28px 24px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; gap:14px; }
    .sidebar-logo { width:40px; height:40px; background:rgba(200,151,58,0.15); border:1px solid rgba(200,151,58,0.3); border-radius:8px; display:flex; align-items:center; justify-content:center; font-family:'Bebas Neue',sans-serif; color:var(--gold); font-size:14px; flex-shrink:0; }
    .sidebar-title h3 { font-family:'DM Serif Display',serif; color:white; font-size:1rem; }
    .sidebar-title p { font-family:'IBM Plex Mono',monospace; font-size:0.55rem; color:rgba(255,255,255,0.3); letter-spacing:2px; text-transform:uppercase; margin-top:2px; }
    .sidebar-user { padding:20px 24px; display:flex; align-items:center; gap:12px; border-bottom:1px solid rgba(255,255,255,0.06); }
    .user-avatar { width:42px; height:42px; border-radius:50%; background:linear-gradient(135deg,var(--teal),var(--gold)); display:flex; align-items:center; justify-content:center; font-family:'Bebas Neue',sans-serif; color:white; font-size:1.1rem; flex-shrink:0; }
    .user-info h4 { color:white; font-size:0.88rem; font-weight:500; }
    .user-info p { font-family:'IBM Plex Mono',monospace; font-size:0.6rem; color:rgba(255,255,255,0.3); letter-spacing:1px; margin-top:1px; }
    .user-status { display:flex; align-items:center; gap:5px; margin-top:4px; }
    .user-status span { width:6px; height:6px; background:#22c55e; border-radius:50%; }
    .user-status p { font-size:0.7rem; color:#22c55e; }
    nav.sidebar-nav { flex:1; padding:16px 0; overflow-y:auto; }
    .nav-section-label { font-family:'IBM Plex Mono',monospace; font-size:0.58rem; letter-spacing:3px; text-transform:uppercase; color:rgba(255,255,255,0.2); padding:12px 24px 6px; }
    .nav-item { display:flex; align-items:center; gap:12px; padding:11px 24px; color:rgba(255,255,255,0.45); font-size:0.85rem; text-decoration:none; transition:all 0.2s; position:relative; border-left:2px solid transparent; }
    .nav-item:hover { color:white; background:rgba(255,255,255,0.04); border-left-color:rgba(200,151,58,0.3); }
    .nav-item.active { color:var(--gold); background:rgba(200,151,58,0.07); border-left-color:var(--gold); }
    .nav-item i { width:18px; text-align:center; font-size:0.88rem; }
    .nav-badge { margin-left:auto; background:var(--rust); color:white; font-family:'IBM Plex Mono',monospace; font-size:0.6rem; padding:2px 7px; border-radius:10px; }
    .sidebar-footer { padding:20px 24px; border-top:1px solid rgba(255,255,255,0.06); }
    .btn-logout { width:100%; padding:10px; background:rgba(192,74,43,0.15); border:1px solid rgba(192,74,43,0.3); border-radius:6px; color:var(--rust); font-family:'IBM Plex Mono',monospace; font-size:0.7rem; letter-spacing:1.5px; text-transform:uppercase; cursor:pointer; transition:all 0.3s; display:flex; align-items:center; justify-content:center; gap:8px; }
    .btn-logout:hover { background:rgba(192,74,43,0.3); }
    .main { margin-left:var(--sidebar-w); flex:1; display:flex; flex-direction:column; min-height:100vh; }
    .topbar { height:64px; background:white; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; padding:0 32px; position:sticky; top:0; z-index:50; }
    .topbar-left h2 { font-family:'Bebas Neue',sans-serif; font-size:1.6rem; letter-spacing:1px; color:var(--ink); }
    .topbar-left p { font-size:0.75rem; color:var(--muted); margin-top:-2px; }
    .topbar-right { display:flex; align-items:center; gap:16px; }
    .topbar-icon-btn { width:36px; height:36px; border-radius:8px; background:var(--cream); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all 0.2s; color:var(--muted); position:relative; text-decoration:none; color:inherit; }
    .topbar-icon-btn:hover { background:white; color:var(--ink); border-color:var(--gold); }
    .notif-dot { position:absolute; top:6px; right:6px; width:7px; height:7px; background:var(--rust); border-radius:50%; border:1.5px solid white; }
    .topbar-date { font-family:'IBM Plex Mono',monospace; font-size:0.65rem; letter-spacing:1px; color:var(--muted); padding:6px 12px; background:var(--cream); border:1px solid var(--border); border-radius:6px; }
    .content { padding:32px; display:flex; flex-direction:column; gap:28px; }
    .stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }
    .stat-card { background:white; border:1px solid var(--border); border-radius:12px; padding:24px; display:flex; flex-direction:column; gap:4px; transition:all 0.3s; position:relative; overflow:hidden; }
    .stat-card::before { content:''; position:absolute; bottom:0; left:0; right:0; height:3px; }
    .stat-card:nth-child(1)::before { background:linear-gradient(to right,var(--teal),var(--teal-light)); }
    .stat-card:nth-child(2)::before { background:linear-gradient(to right,var(--gold),var(--gold-light)); }
    .stat-card:nth-child(3)::before { background:linear-gradient(to right,var(--rust),#e06040); }
    .stat-card:nth-child(4)::before { background:linear-gradient(to right,#7c3aed,#a855f7); }
    .stat-card:hover { transform:translateY(-3px); box-shadow:0 8px 30px rgba(0,0,0,0.08); }
    .stat-card-top { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:10px; }
    .stat-icon { width:40px; height:40px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; }
    .stat-change { font-family:'IBM Plex Mono',monospace; font-size:0.62rem; letter-spacing:1px; }
    .change-up { color:#22c55e; } .change-down { color:var(--rust); }
    .stat-val { font-family:'Bebas Neue',sans-serif; font-size:2.4rem; line-height:1; }
    .stat-label { font-size:0.78rem; color:var(--muted); }
    .dashboard-grid { display:grid; grid-template-columns:1.4fr 1fr; gap:24px; align-items:start; }
    .card { background:white; border:1px solid var(--border); border-radius:12px; overflow:hidden; }
    .card-header { padding:20px 24px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
    .card-header h3 { font-family:'DM Serif Display',serif; font-size:1.1rem; }
    .card-header p { font-size:0.75rem; color:var(--muted); }
    .card-link { font-family:'IBM Plex Mono',monospace; font-size:0.65rem; letter-spacing:1px; text-transform:uppercase; color:var(--teal); text-decoration:none; transition:color 0.2s; display:flex; align-items:center; gap:6px; }
    .card-link:hover { color:var(--gold); }
    .card-body { padding:24px; }
    .meal-row { display:grid; grid-template-columns:90px 1fr 1fr 1fr; gap:8px; padding:10px 24px; border-bottom:1px solid var(--border); align-items:center; }
    .meal-row:last-child { border-bottom:none; }
    .meal-row.today { background:rgba(200,151,58,0.04); }
    .today-tag { background:var(--gold); color:var(--ink); font-family:'IBM Plex Mono',monospace; font-size:0.55rem; letter-spacing:1px; text-transform:uppercase; padding:2px 6px; border-radius:3px; display:inline-block; margin-left:6px; }
    .meal-type { font-family:'IBM Plex Mono',monospace; font-size:0.6rem; letter-spacing:1.5px; text-transform:uppercase; color:var(--muted); }
    .meal-name { font-size:0.82rem; color:var(--ink); }
    .notice-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; margin-top:5px; }
    .nd-urgent { background:var(--rust); } .nd-info { background:var(--teal); } .nd-general { background:var(--gold); }
    .notice-list-item { display:flex; align-items:flex-start; gap:12px; padding:12px 0; border-bottom:1px solid var(--border); }
    .notice-list-item:last-child { border-bottom:none; }
    .notice-list-item h5 { font-size:0.85rem; font-weight:500; margin-bottom:2px; }
    .notice-list-item p { font-size:0.75rem; color:var(--muted); }
    .notice-list-item time { font-family:'IBM Plex Mono',monospace; font-size:0.6rem; color:var(--muted); margin-left:auto; flex-shrink:0; padding-top:2px; }
    .leave-form .form-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px; }
    .leave-form label { font-family:'IBM Plex Mono',monospace; font-size:0.6rem; letter-spacing:2px; text-transform:uppercase; color:var(--muted); display:block; margin-bottom:5px; }
    .leave-form input, .leave-form select, .leave-form textarea { width:100%; padding:10px 12px; border:1.5px solid var(--border); border-radius:5px; background:var(--cream); font-family:'Outfit',sans-serif; font-size:0.85rem; color:var(--ink); outline:none; transition:border-color 0.3s; }
    .leave-form input:focus, .leave-form select:focus, .leave-form textarea:focus { border-color:var(--gold); background:white; }
    .leave-form textarea { height:80px; resize:none; }
    .btn-sm { padding:10px 20px; border-radius:5px; border:none; cursor:pointer; font-family:'IBM Plex Mono',monospace; font-size:0.7rem; letter-spacing:1.5px; text-transform:uppercase; font-weight:600; transition:all 0.3s; }
    .btn-sm-primary { background:var(--ink); color:var(--gold); }
    .btn-sm-primary:hover { background:var(--gold); color:var(--ink); }
    .btn-sm-outline { background:transparent; border:1.5px solid var(--border); color:var(--muted); }
    .btn-sm-outline:hover { border-color:var(--ink); color:var(--ink); }
    .bill-card { border:1px solid var(--border); border-radius:8px; padding:16px; margin-bottom:12px; display:flex; align-items:center; justify-content:space-between; transition:all 0.2s; }
    .bill-card:hover { border-color:var(--gold); background:var(--cream); }
    .bill-card:last-child { margin-bottom:0; }
    .bill-month { font-weight:600; font-size:0.88rem; }
    .bill-amount { font-family:'IBM Plex Mono',monospace; font-size:1rem; font-weight:600; }
    .bill-status { padding:3px 10px; border-radius:20px; font-family:'IBM Plex Mono',monospace; font-size:0.6rem; letter-spacing:1px; text-transform:uppercase; }
    .bs-paid { background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; }
    .bs-due { background:#fef9ec; color:#b45309; border:1px solid #fde68a; }
    .bs-overdue { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
    .complaint-item { padding:14px 0; border-bottom:1px solid var(--border); display:flex; align-items:flex-start; gap:12px; }
    .complaint-item:last-child { border-bottom:none; }
    .complaint-icon { width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:0.9rem; }
    .ci-pending { background:#fef9ec; color:#b45309; } .ci-resolved { background:#f0fdf4; color:#16a34a; } .ci-inprog { background:#eff6ff; color:#2563eb; }
    .complaint-item h5 { font-size:0.85rem; font-weight:500; }
    .complaint-item p { font-size:0.75rem; color:var(--muted); margin-top:2px; }
    .complaint-status { margin-left:auto; flex-shrink:0; font-family:'IBM Plex Mono',monospace; font-size:0.58rem; letter-spacing:1px; text-transform:uppercase; padding:3px 8px; border-radius:3px; }
    .cs-pending { background:#fef9ec; color:#b45309; } .cs-resolved { background:#f0fdf4; color:#16a34a; } .cs-inprog { background:#eff6ff; color:#2563eb; }
    .toast { position:fixed; bottom:24px; right:24px; background:var(--ink); color:var(--gold); padding:14px 24px; border-radius:8px; font-family:'IBM Plex Mono',monospace; font-size:0.8rem; z-index:9999; transform:translateY(100px); opacity:0; transition:all 0.3s; }
    .toast.show { transform:translateY(0); opacity:1; }
    @media(max-width:1200px) { .dashboard-grid { grid-template-columns:1fr; } .stats-row { grid-template-columns:repeat(2,1fr); } }
    @media(max-width:768px) { .sidebar { transform:translateX(-100%); } .sidebar.open { transform:translateX(0); } .main { margin-left:0; } .stats-row { grid-template-columns:1fr 1fr; } .topbar { padding:0 16px; } .content { padding:20px 16px; } }
  </style>
</head>
<body>

<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursorRing"></div>

<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-logo">JBH</div>
    <div class="sidebar-title">
      <h3>JBH Portal</h3>
      <p>DEI Agra · 2024–25</p>
    </div>
  </div>
  <div class="sidebar-user">
    <div class="user-avatar"><?= strtoupper($initials) ?></div>
    <div class="user-info">
      <h4><?= htmlspecialchars($student['full_name']) ?></h4>
      <p><?= htmlspecialchars($student['student_id']) ?> · Room <?= htmlspecialchars($student['room_number'] ?? '-') ?></p>
      <div class="user-status"><span></span><p>Online</p></div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <p class="nav-section-label">Main</p>
    <a href="student.php" class="nav-item active"><i class="fas fa-th-large"></i> Dashboard</a>
    <a href="profile.php" class="nav-item"><i class="fas fa-user-circle"></i> My Profile</a>
    <a href="notices.php" class="nav-item"><i class="fas fa-bell"></i> Notices <?php if ($noticeCount > 0): ?><span class="nav-badge"><?= $noticeCount ?></span><?php endif; ?></a>
    <p class="nav-section-label">Mess</p>
    <a href="mess-menu.php" class="nav-item"><i class="fas fa-utensils"></i> Mess Menu</a>
    <a href="mess-bills.php" class="nav-item"><i class="fas fa-file-invoice-dollar"></i> Mess Bills <?php if ($dueBillsCount > 0): ?><span class="nav-badge"><?= $dueBillsCount ?></span><?php endif; ?></a>
    <a href="pay.php" class="nav-item"><i class="fas fa-credit-card"></i> Pay Online</a>
    <p class="nav-section-label">Services</p>
    <a href="complaints.php" class="nav-item"><i class="fas fa-tools"></i> Complaints</a>
    <a href="leave.php" class="nav-item"><i class="fas fa-calendar-check"></i> Leave Request</a>
    <p class="nav-section-label">Settings</p>
    <a href="../index.html" class="nav-item"><i class="fas fa-external-link-alt"></i> Main Website</a>
  </nav>
  <div class="sidebar-footer">
    <a href="../api/logout.php" class="btn-logout" style="text-decoration:none;color:inherit;display:flex;align-items:center;justify-content:center;"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
  </div>
</aside>

<div class="main">
  <div class="topbar">
    <div class="topbar-left">
      <h2>Dashboard</h2>
      <p>Welcome back, <?= htmlspecialchars(explode(' ',$student['full_name'])[0]) ?>. Here's your hostel overview.</p>
    </div>
    <div class="topbar-right">
      <div class="topbar-date" id="currentDate"></div>
      <a href="notices.php" class="topbar-icon-btn"><i class="fas fa-bell"></i><?php if ($noticeCount > 0): ?><span class="notif-dot"></span><?php endif; ?></a>
      <div class="topbar-icon-btn" style="background:linear-gradient(135deg,var(--teal),var(--gold));color:white;border-color:transparent;font-family:'Bebas Neue',sans-serif;font-size:0.95rem"><?= strtoupper($initials) ?></div>
    </div>
  </div>

  <div class="content">
    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-card-top">
          <div class="stat-icon" style="background:#f0fdfa;color:var(--teal)"><i class="fas fa-rupee-sign"></i></div>
          <span class="stat-change <?= $billStatus === 'paid' ? 'change-up' : 'change-down' ?>"><?= $billStatus === 'paid' ? '<i class="fas fa-check-circle"></i> Paid' : 'Due' ?></span>
        </div>
        <div class="stat-val"><?= $currentBillAmt ?></div>
        <div class="stat-label">Current Mess Bill (<?= date('M') ?>)</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-top">
          <div class="stat-icon" style="background:#fffbeb;color:var(--gold)"><i class="fas fa-calendar-day"></i></div>
          <span class="stat-change change-up"><?= date('t') - date('d') ?> days left</span>
        </div>
        <div class="stat-val"><?= date('d') ?></div>
        <div class="stat-label">Day of Month</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-top">
          <div class="stat-icon" style="background:#fef2f2;color:var(--rust)"><i class="fas fa-tools"></i></div>
          <span class="stat-change change-down"><?= $pendingCount ?> open</span>
        </div>
        <div class="stat-val"><?= str_pad($pendingCount, 2, '0', STR_PAD_LEFT) ?></div>
        <div class="stat-label">Pending Complaints</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-top">
          <div class="stat-icon" style="background:#f5f3ff;color:#7c3aed"><i class="fas fa-door-open"></i></div>
          <span class="stat-change">Room <?= htmlspecialchars($student['room_number'] ?? '-') ?></span>
        </div>
        <div class="stat-val" style="font-size:1.8rem"><?= htmlspecialchars($student['room_number'] ?? '-') ?></div>
        <div class="stat-label">Block <?= htmlspecialchars($student['block'] ?? 'A') ?> · <?= ucfirst($student['room_type'] ?? 'triple') ?> Sharing</div>
      </div>
    </div>

    <div class="dashboard-grid">
      <!-- Mess Menu -->
      <div class="card">
        <div class="card-header">
          <div><h3>This Week's Mess Menu</h3><p>Vegetarian menu · Updated weekly</p></div>
          <a href="mess-menu.php" class="card-link">Full Menu <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="card-body" style="padding:0">
          <div style="display:grid;grid-template-columns:90px 1fr 1fr 1fr;gap:0;padding:10px 24px;background:var(--cream)">
            <span class="meal-type">Day</span>
            <span class="meal-type">Breakfast</span>
            <span class="meal-type">Lunch</span>
            <span class="meal-type">Dinner</span>
          </div>
          <?php foreach ($menu as $m):
            $dow = (int)$m['day_of_week'];
            $isToday = ($dow === $today);
          ?>
          <div class="meal-row <?= $isToday ? 'today' : '' ?>">
            <span style="font-size:0.82rem;font-weight:<?= $isToday?'600':'400'?>"><?= $dayNames[$dow] ?><?= $isToday ? '<span class="today-tag">Today</span>' : '' ?></span>
            <span class="meal-name"><?= htmlspecialchars($m['breakfast'] ?? '-') ?></span>
            <span class="meal-name"><?= htmlspecialchars($m['lunch'] ?? '-') ?></span>
            <span class="meal-name"><?= htmlspecialchars($m['dinner'] ?? '-') ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Notices -->
      <div class="card">
        <div class="card-header">
          <div><h3>Latest Notices</h3><p>From Warden's Office</p></div>
          <a href="notices.php" class="card-link">All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="card-body">
          <?php foreach (array_slice($notices, 0, 4) as $n):
            $ndClass = $n['category'] === 'urgent' ? 'nd-urgent' : ($n['category'] === 'sports' || $n['category'] === 'maintenance' ? 'nd-info' : 'nd-general');
          ?>
          <div class="notice-list-item">
            <span class="notice-dot <?= $ndClass ?>"></span>
            <div>
              <h5><?= htmlspecialchars($n['title']) ?></h5>
              <p><?= htmlspecialchars(mb_substr($n['content'] ?? '', 0, 50)) ?>...</p>
            </div>
            <time><?= date('d M', strtotime($n['created_at'])) ?></time>
          </div>
          <?php endforeach; ?>
          <?php if (empty($notices)): ?><p style="color:var(--muted);font-size:0.85rem">No notices yet.</p><?php endif; ?>
        </div>
      </div>

      <!-- Mess Bills -->
      <div class="card">
        <div class="card-header">
          <div><h3>Mess Bills</h3><p>Last 4 months</p></div>
          <a href="pay.php" class="card-link">Pay Online <i class="fas fa-credit-card"></i></a>
        </div>
        <div class="card-body">
          <?php foreach ($messBills as $b):
            $bsClass = $b['status'] === 'paid' ? 'bs-paid' : ($b['status'] === 'overdue' ? 'bs-overdue' : 'bs-due');
            $parts = explode('-', $b['month_year']);
            $monthName = date('F', mktime(0,0,0,(int)$parts[1],1));
          ?>
          <div class="bill-card">
            <div>
              <div class="bill-month"><?= $monthName ?> <?= $parts[0] ?></div>
              <div style="font-size:0.75rem;color:var(--muted);margin-top:2px">Due: <?= $b['due_date'] ? date('d M Y', strtotime($b['due_date'])) : '-' ?></div>
            </div>
            <div style="text-align:right">
              <div class="bill-amount">₹<?= number_format($b['amount']) ?></div>
              <span class="bill-status <?= $bsClass ?>" style="margin-top:4px;display:inline-block"><?= ucfirst($b['status']) ?></span>
            </div>
          </div>
          <?php endforeach; ?>
          <?php if (empty($messBills)): ?><p style="color:var(--muted);font-size:0.85rem">No bills yet.</p><?php endif; ?>
        </div>
      </div>

      <!-- Leave + Complaints -->
      <div style="display:flex;flex-direction:column;gap:24px">
        <div class="card">
          <div class="card-header"><div><h3>Leave Request</h3><p>Apply for home leave</p></div></div>
          <div class="card-body leave-form">
            <form id="leaveForm" action="../api/leave.php" method="POST">
              <div class="form-row">
                <div><label>From Date</label><input type="date" name="from_date" required/></div>
                <div><label>To Date</label><input type="date" name="to_date" required/></div>
              </div>
              <div style="margin-bottom:12px"><label>Reason</label><textarea name="reason" placeholder="Briefly describe your reason for leave..."></textarea></div>
              <div style="display:flex;gap:10px">
                <button type="submit" class="btn-sm btn-sm-primary"><i class="fas fa-paper-plane"></i> Submit Request</button>
                <button type="reset" class="btn-sm btn-sm-outline">Cancel</button>
              </div>
            </form>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div><h3>My Complaints</h3><p>Maintenance requests</p></div>
            <a href="complaints.php?new=1" class="card-link">+ New</a>
          </div>
          <div class="card-body">
            <?php foreach ($myComplaints as $c):
              $ciClass = $c['status'] === 'resolved' ? 'ci-resolved' : ($c['status'] === 'in_progress' ? 'ci-inprog' : 'ci-pending');
              $csClass = 'cs-' . str_replace('_','',$c['status']);
              $icons = ['electrical'=>'fa-bolt','plumbing'=>'fa-faucet','furniture'=>'fa-couch','cleaning'=>'fa-broom','other'=>'fa-tools'];
              $icon = $icons[$c['category']] ?? 'fa-tools';
            ?>
            <div class="complaint-item">
              <div class="complaint-icon <?= $ciClass ?>"><i class="fas <?= $icon ?>"></i></div>
              <div>
                <h5><?= htmlspecialchars($c['subject']) ?></h5>
                <p>Submitted <?= date('d M Y', strtotime($c['created_at'])) ?></p>
              </div>
              <span class="complaint-status <?= $csClass ?>"><?= ucfirst(str_replace('_',' ',$c['status'])) ?></span>
            </div>
            <?php endforeach; ?>
            <?php if (empty($myComplaints)): ?><p style="color:var(--muted);font-size:0.85rem">No complaints yet.</p><?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
const c=document.getElementById('cursor'),r=document.getElementById('cursorRing');
let mx=0,my=0,rx=0,ry=0;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;});
function ac(){c.style.transform=`translate(${mx-4}px,${my-4}px)`;rx+=(mx-rx)*.12;ry+=(my-ry)*.12;r.style.transform=`translate(${rx-15}px,${ry-15}px)`;requestAnimationFrame(ac);}
ac();
document.getElementById('currentDate').textContent=new Date().toLocaleDateString('en-IN',{weekday:'short',day:'2-digit',month:'short',year:'numeric'});

document.getElementById('leaveForm')?.addEventListener('submit',async function(e){
  e.preventDefault();
  const btn=this.querySelector('button[type="submit"]');
  const orig=btn.innerHTML;
  btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Submitting...';
  btn.disabled=true;
  try {
    const fd=new FormData(this);
    const res=await fetch(this.action,{method:'POST',body:fd});
    const data=await res.json();
    const t=document.getElementById('toast');
    t.textContent=data.success?'Leave request submitted!':'Error: '+(data.message||'Failed');
    t.classList.add('show');
    setTimeout(()=>{t.classList.remove('show');},3000);
    if(data.success) this.reset();
  } catch(err){ document.getElementById('toast').textContent='Error submitting request'; document.getElementById('toast').classList.add('show'); }
  btn.innerHTML=orig;
  btn.disabled=false;
});
</script>
</body>
</html>
