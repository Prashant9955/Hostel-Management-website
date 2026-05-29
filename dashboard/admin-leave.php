<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

// Handle approve/reject
if (isset($_GET['approve']) || isset($_GET['reject'])) {
    $id = (int)($_GET['approve'] ?? $_GET['reject']);
    $status = isset($_GET['approve']) ? 'approved' : 'rejected';
    $stmt = $pdo->prepare("UPDATE leave_applications SET status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $_SESSION['user_id'], $id]);
    header('Location: admin-leave.php?updated=1');
    exit;
}

$leaves = $pdo->query("SELECT l.*, s.full_name, s.student_id, s.room_number FROM leave_applications l JOIN students s ON l.student_id = s.id ORDER BY l.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Leave Requests – Admin</title>
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
    .card{background:white;border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:24px;}
    .card h2{font-family:'DM Serif Display';margin-bottom:20px;}
    .leave-item{padding:16px 0;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;}
    .leave-item:last-child{border:none;}
    .btn{padding:8px 16px;border-radius:6px;border:none;cursor:pointer;font-size:0.75rem;text-decoration:none;display:inline-block;}
    .btn-approve{background:#22c55e;color:white;}
    .btn-reject{background:#dc2626;color:white;}
    .badge{padding:3px 8px;border-radius:20px;font-size:0.65rem;text-transform:uppercase;}
    .badge-pending{background:#fef9ec;color:#b45309;}
    .badge-approved{background:#f0fdf4;color:#16a34a;}
    .badge-rejected{background:#fef2f2;color:#dc2626;}
    .alert{background:#f0fdf4;color:#166534;padding:12px;border-radius:6px;margin-bottom:20px;}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo">JBH</div>
  <a href="admin.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
  <a href="admin-notices.php" class="nav-item"><i class="fas fa-bell"></i> Notices</a>
  <a href="admin-complaints.php" class="nav-item"><i class="fas fa-tools"></i> Complaints</a>
  <a href="admin-students.php" class="nav-item"><i class="fas fa-users"></i> Students</a>
  <a href="admin-leave.php" class="nav-item active"><i class="fas fa-calendar-check"></i> Leave</a>
  <a href="../api/logout.php" class="nav-item" style="margin-top:20px;color:#c04a2b"><i class="fas fa-sign-out-alt"></i> Logout</a>
</aside>
<div class="main">
  <?php if (isset($_GET['updated'])): ?><div class="alert">Leave request updated!</div><?php endif; ?>
  <div class="card">
    <h2>Leave Applications</h2>
    <?php foreach ($leaves as $l): ?>
    <div class="leave-item">
      <div>
        <strong><?= htmlspecialchars($l['full_name']) ?></strong> (<?= $l['student_id'] ?>)
        <p style="font-size:0.9rem;color:var(--muted);margin:4px 0;"><?= $l['from_date'] ?> to <?= $l['to_date'] ?></p>
        <p style="font-size:0.85rem;"><?= htmlspecialchars($l['reason'] ?? '-') ?></p>
        <small><?= date('d M Y', strtotime($l['created_at'])) ?></small>
      </div>
      <div style="display:flex;gap:8px;align-items:center;">
        <span class="badge badge-<?= $l['status'] ?>"><?= ucfirst($l['status']) ?></span>
        <?php if ($l['status'] === 'pending'): ?>
        <a href="?approve=<?= $l['id'] ?>" class="btn btn-approve">Approve</a>
        <a href="?reject=<?= $l['id'] ?>" class="btn btn-reject">Reject</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($leaves)): ?><p style="color:var(--muted);">No leave applications.</p><?php endif; ?>
  </div>
</div>
</body>
</html>
