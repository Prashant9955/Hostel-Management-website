<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

// Handle add notice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_notice'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = $_POST['category'] ?? 'general';
    if (!empty($title)) {
        $stmt = $pdo->prepare("INSERT INTO notices (title, content, category, created_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $content, $category, $_SESSION['user_id']]);
        header('Location: admin-notices.php?added=1');
        exit;
    }
}

$notices = $pdo->query("SELECT * FROM notices ORDER BY created_at DESC")->fetchAll();
$userName = $_SESSION['user_name'];
$initials = implode('', array_map(fn($w) => $w[0], explode(' ', $userName, 2)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Notices – Admin</title>
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
    .form-group{margin-bottom:16px;}
    .form-group label{display:block;font-family:'IBM Plex Mono';font-size:0.65rem;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:6px;}
    .form-group input,.form-group select,.form-group textarea{width:100%;padding:12px;border:1.5px solid var(--border);border-radius:6px;}
    .btn{padding:12px 24px;border-radius:6px;border:none;cursor:pointer;font-family:'IBM Plex Mono';font-size:0.75rem;text-transform:uppercase;font-weight:600;}
    .btn-primary{background:var(--ink);color:var(--gold);}
    .notice-item{padding:16px 0;border-bottom:1px solid var(--border);}
    .notice-item:last-child{border:none;}
    .notice-item small{color:var(--muted);}
    .alert{background:#f0fdf4;color:#166534;padding:12px;border-radius:6px;margin-bottom:20px;}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo">JBH</div>
  <a href="admin.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
  <a href="admin-notices.php" class="nav-item active"><i class="fas fa-bell"></i> Notices</a>
  <a href="admin-complaints.php" class="nav-item"><i class="fas fa-tools"></i> Complaints</a>
  <a href="admin-students.php" class="nav-item"><i class="fas fa-users"></i> Students</a>
  <a href="../api/logout.php" class="nav-item" style="margin-top:20px;color:#c04a2b"><i class="fas fa-sign-out-alt"></i> Logout</a>
</aside>
<div class="main">
  <?php if (isset($_GET['added'])): ?><div class="alert">Notice added successfully!</div><?php endif; ?>
  <div class="card">
    <h2>Add New Notice</h2>
    <form method="POST">
      <input type="hidden" name="add_notice" value="1"/>
      <div class="form-group">
        <label>Title</label>
        <input type="text" name="title" required placeholder="Notice title"/>
      </div>
      <div class="form-group">
        <label>Content</label>
        <textarea name="content" rows="4" placeholder="Notice content..."></textarea>
      </div>
      <div class="form-group">
        <label>Category</label>
        <select name="category">
          <option value="general">General</option>
          <option value="urgent">Urgent</option>
          <option value="mess">Mess</option>
          <option value="sports">Sports</option>
          <option value="maintenance">Maintenance</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Notice</button>
    </form>
  </div>
  <div class="card">
    <h2>All Notices</h2>
    <?php foreach ($notices as $n): ?>
    <div class="notice-item">
      <strong><?= htmlspecialchars($n['title']) ?></strong>
      <p style="font-size:0.9rem;margin:8px 0;"><?= nl2br(htmlspecialchars($n['content'] ?? '')) ?></p>
      <small><?= date('d M Y H:i', strtotime($n['created_at'])) ?> · <?= ucfirst($n['category']) ?></small>
    </div>
    <?php endforeach; ?>
    <?php if (empty($notices)): ?><p style="color:var(--muted);">No notices yet.</p><?php endif; ?>
  </div>
</div>
</body>
</html>
