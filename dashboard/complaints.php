<?php
require_once __DIR__ . '/../includes/auth.php';
requireStudent();
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$userId]);
$student = $stmt->fetch();
$initials = implode('', array_map(fn($w) => $w[0], explode(' ', $student['full_name'], 2)));

$complaints = $pdo->prepare("SELECT * FROM complaints WHERE student_id = ? ORDER BY created_at DESC");
$complaints->execute([$userId]);
$list = $complaints->fetchAll();

$showForm = isset($_GET['new']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Complaints – JBH Portal</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Serif+Display&family=IBM+Plex+Mono&family=Outfit&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    :root { --ink:#0b0c0e; --gold:#c8973a; --teal:#1a6b6b; --cream:#faf6ee; --muted:#8a8070; --border:#d4c8b0; --sidebar-w:260px; --rust:#c04a2b; }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Outfit',sans-serif;background:var(--cream);color:var(--ink);display:flex;min-height:100vh;}
    .sidebar{width:var(--sidebar-w);min-height:100vh;background:var(--ink);padding:24px 0;position:fixed;left:0;top:0;}
    .sidebar-logo{width:40px;height:40px;background:rgba(200,151,58,0.15);border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:'Bebas Neue';color:var(--gold);margin:0 24px 20px;}
    .nav-item{display:flex;align-items:center;gap:12px;padding:11px 24px;color:rgba(255,255,255,0.5);font-size:0.85rem;text-decoration:none;transition:all 0.2s;}
    .nav-item:hover,.nav-item.active{color:var(--gold);}
    .main{margin-left:var(--sidebar-w);flex:1;padding:32px;}
    .card{background:white;border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:24px;}
    .card h2{font-family:'DM Serif Display';font-size:1.4rem;margin-bottom:20px;}
    .form-group{margin-bottom:16px;}
    .form-group label{display:block;font-family:'IBM Plex Mono';font-size:0.65rem;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:6px;}
    .form-group input,.form-group select,.form-group textarea{width:100%;padding:12px;border:1.5px solid var(--border);border-radius:6px;font-family:'Outfit';font-size:0.9rem;}
    .btn{padding:12px 24px;border-radius:6px;border:none;cursor:pointer;font-family:'IBM Plex Mono';font-size:0.75rem;letter-spacing:1px;text-transform:uppercase;font-weight:600;}
    .btn-primary{background:var(--ink);color:var(--gold);}
    .btn-primary:hover{background:var(--gold);color:var(--ink);}
    .complaint-item{display:flex;align-items:flex-start;gap:16px;padding:16px 0;border-bottom:1px solid var(--border);}
    .complaint-item:last-child{border:none;}
    .status{padding:3px 10px;border-radius:20px;font-size:0.65rem;font-family:'IBM Plex Mono';text-transform:uppercase;}
    .status-pending{background:#fef9ec;color:#b45309;}
    .status-resolved{background:#f0fdf4;color:#16a34a;}
    .status-in_progress{background:#eff6ff;color:#2563eb;}
    a{color:var(--teal);text-decoration:none;}
    a:hover{color:var(--gold);}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo">JBH</div>
  <a href="student.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
  <a href="complaints.php" class="nav-item active"><i class="fas fa-tools"></i> Complaints</a>
  <a href="../api/logout.php" class="nav-item" style="margin-top:20px;color:var(--rust)"><i class="fas fa-sign-out-alt"></i> Logout</a>
</aside>
<div class="main">
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
      <h2>Maintenance Complaints</h2>
      <?php if (!$showForm): ?>
      <a href="?new=1" class="btn btn-primary"><i class="fas fa-plus"></i> New Complaint</a>
      <?php else: ?>
      <a href="complaints.php" class="btn" style="background:var(--cream);color:var(--muted)">← Back</a>
      <?php endif; ?>
    </div>

    <?php if ($showForm): ?>
    <form id="complaintForm">
      <div class="form-group">
        <label>Subject</label>
        <input type="text" name="subject" placeholder="e.g. Bulb not working in Room 114" required/>
      </div>
      <div class="form-group">
        <label>Category</label>
        <select name="category">
          <option value="electrical">Electrical</option>
          <option value="plumbing">Plumbing</option>
          <option value="furniture">Furniture</option>
          <option value="cleaning">Cleaning</option>
          <option value="other">Other</option>
        </select>
      </div>
      <div class="form-group">
        <label>Location / Room</label>
        <input type="text" name="room_location" placeholder="e.g. Room 114, Block A" value="Room <?= htmlspecialchars($student['room_number'] ?? '') ?>"/>
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea name="description" rows="4" placeholder="Describe the issue in detail..."></textarea>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Complaint</button>
    </form>
    <?php else: ?>
    <div id="complaintList">
      <?php foreach ($list as $c): ?>
      <div class="complaint-item">
        <div>
          <strong><?= htmlspecialchars($c['subject']) ?></strong>
          <p style="font-size:0.85rem;color:var(--muted);margin-top:4px;"><?= htmlspecialchars($c['description'] ?? '') ?></p>
          <p style="font-size:0.75rem;color:var(--muted);margin-top:8px;"><?= date('d M Y', strtotime($c['created_at'])) ?> · <?= ucfirst($c['category']) ?></p>
        </div>
        <span class="status status-<?= str_replace('_','',$c['status']) ?>"><?= ucfirst(str_replace('_',' ',$c['status'])) ?></span>
      </div>
      <?php endforeach; ?>
      <?php if (empty($list)): ?><p style="color:var(--muted);">No complaints yet. <a href="?new=1">Submit one</a>.</p><?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if ($showForm): ?>
<script>
document.getElementById('complaintForm').addEventListener('submit',async function(e){
  e.preventDefault();
  const btn=this.querySelector('button[type="submit"]');
  btn.disabled=true;
  btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Submitting...';
  const fd=new FormData(this);
  const res=await fetch('../api/complaint.php',{method:'POST',body:fd});
  const data=await res.json();
  if(data.success){alert('Complaint submitted successfully!');window.location.href='complaints.php';}
  else{alert(data.message||'Error');btn.disabled=false;btn.innerHTML='<i class="fas fa-paper-plane"></i> Submit Complaint';}
});
</script>
<?php endif; ?>
</body>
</html>
