<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];
    if (in_array($status, ['pending', 'in_progress', 'resolved', 'rejected'])) {
        $stmt = $pdo->prepare("UPDATE complaints SET status = ?, resolved_by = ?, resolved_at = IF(? = 'resolved', NOW(), NULL) WHERE id = ?");
        $stmt->execute([$status, $_SESSION['user_id'], $status, $id]);
        header('Location: admin-complaints.php?updated=1');
        exit;
    }
}

$complaints = $pdo->query("SELECT c.*, s.full_name, s.student_id, s.room_number FROM complaints c JOIN students s ON c.student_id = s.id ORDER BY c.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Complaints – Admin</title>
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
    .complaint-item{padding:16px 0;border-bottom:1px solid var(--border);}
    .complaint-item:last-child{border:none;}
    .badge{padding:3px 8px;border-radius:20px;font-size:0.65rem;text-transform:uppercase;display:inline-block;}
    .badge-pending{background:#fef9ec;color:#b45309;}
    .badge-resolved{background:#f0fdf4;color:#16a34a;}
    .badge-in_progress{background:#eff6ff;color:#2563eb;}
    .badge-rejected{background:#fef2f2;color:#dc2626;}
    select{padding:6px 12px;border:1px solid var(--border);border-radius:6px;font-size:0.8rem;}
    .btn{padding:8px 16px;border-radius:6px;border:none;cursor:pointer;font-size:0.75rem;font-family:'IBM Plex Mono';}
    .btn-sm{padding:4px 10px;}
    .btn-primary{background:var(--ink);color:var(--gold);}
    .alert{background:#f0fdf4;color:#166534;padding:12px;border-radius:6px;margin-bottom:20px;}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo">JBH</div>
  <a href="admin.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
  <a href="admin-notices.php" class="nav-item"><i class="fas fa-bell"></i> Notices</a>
  <a href="admin-complaints.php" class="nav-item active"><i class="fas fa-tools"></i> Complaints</a>
  <a href="admin-students.php" class="nav-item"><i class="fas fa-users"></i> Students</a>
  <a href="../api/logout.php" class="nav-item" style="margin-top:20px;color:#c04a2b"><i class="fas fa-sign-out-alt"></i> Logout</a>
</aside>
<div class="main">
  <?php if (isset($_GET['updated'])): ?><div class="alert">Complaint status updated!</div><?php endif; ?>
  <div class="card">
    <h2>All Complaints</h2>
    <?php foreach ($complaints as $c): ?>
    <div class="complaint-item">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
        <div>
          <strong><?= htmlspecialchars($c['subject']) ?></strong>
          <p style="font-size:0.9rem;color:var(--muted);margin:6px 0;"><?= htmlspecialchars($c['description'] ?? '') ?></p>
          <small><?= htmlspecialchars($c['full_name']) ?> (<?= $c['student_id'] ?>) · <?= $c['room_location'] ?? 'Room ' . $c['room_number'] ?> · <?= date('d M Y', strtotime($c['created_at'])) ?></small>
        </div>
        <form method="POST" style="display:flex;align-items:center;gap:8px;">
          <input type="hidden" name="update_status" value="1"/>
          <input type="hidden" name="id" value="<?= $c['id'] ?>"/>
          <span class="badge badge-<?= $c['status'] ?>"><?= ucfirst(str_replace('_',' ',$c['status'])) ?></span>
          <select name="status" onchange="this.form.submit()">
            <option value="pending" <?= $c['status']==='pending'?'selected':'' ?>>Pending</option>
            <option value="in_progress" <?= $c['status']==='in_progress'?'selected':'' ?>>In Progress</option>
            <option value="resolved" <?= $c['status']==='resolved'?'selected':'' ?>>Resolved</option>
            <option value="rejected" <?= $c['status']==='rejected'?'selected':'' ?>>Rejected</option>
          </select>
          <button type="submit" class="btn btn-primary btn-sm">Update</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($complaints)): ?><p style="color:var(--muted);">No complaints.</p><?php endif; ?>
  </div>
</div>
</body>
</html>
