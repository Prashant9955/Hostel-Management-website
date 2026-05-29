<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();
$userName = $_SESSION['user_name'];
$initials = implode('', array_map(fn($w) => $w[0], explode(' ', $userName, 2)));

// Stats
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students WHERE is_active = 1")->fetchColumn();
$pendingComplaints = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status IN ('pending','in_progress')")->fetchColumn();
$unpaidBills = $pdo->query("SELECT COUNT(*) FROM mess_bills WHERE status = 'pending'")->fetchColumn();
$pendingLeave = $pdo->query("SELECT COUNT(*) FROM leave_applications WHERE status = 'pending'")->fetchColumn();

// Recent notices
$notices = $pdo->query("SELECT * FROM notices ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Recent complaints
$complaints = $pdo->query("SELECT c.*, s.full_name, s.student_id FROM complaints c JOIN students s ON c.student_id = s.id ORDER BY c.created_at DESC LIMIT 8")->fetchAll();

// Leave applications
$leaves = $pdo->query("SELECT l.*, s.full_name, s.student_id, s.room_number FROM leave_applications l JOIN students s ON l.student_id = s.id WHERE l.status = 'pending' ORDER BY l.created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Panel – JBH Hostel</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Serif+Display&family=IBM+Plex+Mono&family=Outfit&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    :root { --ink:#0b0c0e; --gold:#c8973a; --teal:#1a6b6b; --cream:#faf6ee; --muted:#8a8070; --border:#d4c8b0; --sidebar-w:260px; --rust:#c04a2b; }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Outfit',sans-serif;background:var(--cream);color:var(--ink);display:flex;min-height:100vh;}
    .sidebar{width:var(--sidebar-w);min-height:100vh;background:var(--ink);padding:24px 0;position:fixed;left:0;top:0;}
    .sidebar-logo{width:40px;height:40px;background:rgba(200,151,58,0.15);border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:'Bebas Neue';color:var(--gold);margin:0 24px 20px;font-size:14px;}
    .sidebar-user{padding:20px 24px;border-bottom:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;gap:12px;}
    .user-avatar{width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,var(--teal),var(--gold));display:flex;align-items:center;justify-content:center;color:white;font-family:'Bebas Neue';font-size:1rem;}
    .user-info h4{color:white;font-size:0.9rem;}
    .user-info p{font-size:0.7rem;color:rgba(255,255,255,0.4);}
    .nav-item{display:flex;align-items:center;gap:12px;padding:11px 24px;color:rgba(255,255,255,0.5);font-size:0.85rem;text-decoration:none;transition:all 0.2s;}
    .nav-item:hover,.nav-item.active{color:var(--gold);background:rgba(200,151,58,0.08);}
    .nav-badge{margin-left:auto;background:var(--rust);color:white;font-size:0.6rem;padding:2px 7px;border-radius:10px;}
    .main{margin-left:var(--sidebar-w);flex:1;padding:32px;}
    .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:32px;}
    .topbar h1{font-family:'Bebas Neue';font-size:2rem;letter-spacing:1px;}
    .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:32px;}
    .stat-card{background:white;border:1px solid var(--border);border-radius:12px;padding:24px;}
    .stat-card h3{font-family:'Bebas Neue';font-size:2.5rem;color:var(--gold);}
    .stat-card p{font-size:0.85rem;color:var(--muted);margin-top:4px;}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:24px;}
    .card{background:white;border:1px solid var(--border);border-radius:12px;overflow:hidden;}
    .card-header{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;}
    .card-header h2{font-family:'DM Serif Display';font-size:1.2rem;}
    .card-link{font-size:0.75rem;color:var(--teal);text-decoration:none;}
    .card-body{padding:20px;}
    .list-item{padding:12px 0;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;}
    .list-item:last-child{border:none;}
    .list-item small{color:var(--muted);font-size:0.75rem;}
    .badge{padding:3px 8px;border-radius:20px;font-size:0.65rem;text-transform:uppercase;}
    .badge-pending{background:#fef9ec;color:#b45309;}
    .badge-resolved{background:#f0fdf4;color:#16a34a;}
    .badge-inprog{background:#eff6ff;color:#2563eb;}
    .btn{padding:8px 16px;border-radius:6px;border:none;cursor:pointer;font-size:0.75rem;font-family:'IBM Plex Mono';}
    .btn-sm{padding:4px 10px;font-size:0.65rem;}
    .btn-approve{background:#22c55e;color:white;}
    .btn-reject{background:var(--rust);color:white;}
    a{color:var(--teal);}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo">JBH</div>
  <div class="sidebar-user">
    <div class="user-avatar"><?= strtoupper($initials) ?></div>
    <div class="user-info">
      <h4><?= htmlspecialchars($userName) ?></h4>
      <p><?= ucfirst($_SESSION['user_role']) ?></p>
    </div>
  </div>
  <a href="admin.php" class="nav-item active"><i class="fas fa-th-large"></i> Dashboard</a>
  <a href="admin-notices.php" class="nav-item"><i class="fas fa-bell"></i> Notices</a>
  <a href="admin-complaints.php" class="nav-item"><i class="fas fa-tools"></i> Complaints <?php if ($pendingComplaints > 0): ?><span class="nav-badge"><?= $pendingComplaints ?></span><?php endif; ?></a>
  <a href="admin-students.php" class="nav-item"><i class="fas fa-users"></i> Students</a>
  <a href="admin-mess.php" class="nav-item"><i class="fas fa-utensils"></i> Mess Bills</a>
  <a href="admin-leave.php" class="nav-item"><i class="fas fa-calendar-check"></i> Leave <?php if ($pendingLeave > 0): ?><span class="nav-badge"><?= $pendingLeave ?></span><?php endif; ?></a>
  <a href="../api/logout.php" class="nav-item" style="margin-top:20px;color:var(--rust)"><i class="fas fa-sign-out-alt"></i> Logout</a>
</aside>

<div class="main">
  <div class="topbar">
    <h1>Admin Dashboard</h1>
    <p style="color:var(--muted);">Welcome, <?= htmlspecialchars($userName) ?></p>
  </div>

  <div class="stats">
    <div class="stat-card">
      <h3><?= $totalStudents ?></h3>
      <p>Total Students</p>
    </div>
    <div class="stat-card">
      <h3><?= $pendingComplaints ?></h3>
      <p>Pending Complaints</p>
    </div>
    <div class="stat-card">
      <h3><?= $unpaidBills ?></h3>
      <p>Unpaid Mess Bills</p>
    </div>
    <div class="stat-card">
      <h3><?= $pendingLeave ?></h3>
      <p>Pending Leave Requests</p>
    </div>
  </div>

  <div class="grid">
    <div class="card">
      <div class="card-header">
        <h2>Recent Complaints</h2>
        <a href="admin-complaints.php" class="card-link">View All →</a>
      </div>
      <div class="card-body">
        <?php foreach (array_slice($complaints, 0, 5) as $c): ?>
        <div class="list-item">
          <div>
            <strong><?= htmlspecialchars($c['subject']) ?></strong>
            <br><small><?= htmlspecialchars($c['full_name']) ?> · Room <?= htmlspecialchars($c['room_location'] ?? '-') ?></small>
          </div>
          <span class="badge badge-<?= str_replace('_','',$c['status']) ?>"><?= ucfirst(str_replace('_',' ',$c['status'])) ?></span>
        </div>
        <?php endforeach; ?>
        <?php if (empty($complaints)): ?><p style="color:var(--muted);">No complaints.</p><?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h2>Pending Leave Requests</h2>
        <a href="admin-leave.php" class="card-link">View All →</a>
      </div>
      <div class="card-body">
        <?php foreach ($leaves as $l): ?>
        <div class="list-item">
          <div>
            <strong><?= htmlspecialchars($l['full_name']) ?></strong>
            <br><small><?= $l['from_date'] ?> to <?= $l['to_date'] ?> · <?= htmlspecialchars(mb_substr($l['reason'] ?? '', 0, 40)) ?>...</small>
          </div>
          <div>
            <a href="admin-leave.php?approve=<?= $l['id'] ?>" class="btn btn-approve btn-sm">Approve</a>
            <a href="admin-leave.php?reject=<?= $l['id'] ?>" class="btn btn-reject btn-sm">Reject</a>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($leaves)): ?><p style="color:var(--muted);">No pending leave requests.</p><?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h2>Recent Notices</h2>
        <a href="admin-notices.php" class="card-link">Add New →</a>
      </div>
      <div class="card-body">
        <?php foreach ($notices as $n): ?>
        <div class="list-item">
          <div>
            <strong><?= htmlspecialchars($n['title']) ?></strong>
            <br><small><?= date('d M Y', strtotime($n['created_at'])) ?> · <?= ucfirst($n['category']) ?></small>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($notices)): ?><p style="color:var(--muted);">No notices.</p><?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h2>Quick Actions</h2>
      </div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:12px;">
        <a href="admin-notices.php?new=1" class="btn" style="background:var(--ink);color:var(--gold);text-decoration:none;text-align:center;"><i class="fas fa-plus"></i> Add Notice</a>
        <a href="admin-complaints.php" class="btn" style="background:var(--cream);color:var(--ink);text-decoration:none;text-align:center;border:1px solid var(--border);"><i class="fas fa-tools"></i> Manage Complaints</a>
        <a href="admin-students.php" class="btn" style="background:var(--cream);color:var(--ink);text-decoration:none;text-align:center;border:1px solid var(--border);"><i class="fas fa-user-plus"></i> Add Student</a>
        <a href="../index.html" class="btn" style="background:var(--cream);color:var(--ink);text-decoration:none;text-align:center;border:1px solid var(--border);"><i class="fas fa-external-link-alt"></i> Main Website</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>
