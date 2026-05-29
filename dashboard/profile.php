<?php
require_once __DIR__ . '/../includes/auth.php';
requireStudent();
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();
$initials = implode('', array_map(fn($w) => $w[0], explode(' ', $student['full_name'], 2)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Profile – JBH Portal</title>
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
    .card{background:white;border:1px solid var(--border);border-radius:12px;padding:32px;}
    .profile-header{display:flex;align-items:center;gap:24px;margin-bottom:32px;}
    .avatar{width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--teal),var(--gold));display:flex;align-items:center;justify-content:center;font-family:'Bebas Neue';font-size:2rem;color:white;}
    .profile-info h1{font-family:'DM Serif Display';font-size:1.8rem;}
    .profile-info p{color:var(--muted);}
    .profile-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;}
    .profile-item label{display:block;font-family:'IBM Plex Mono';font-size:0.65rem;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:4px;}
    .profile-item p{font-size:1rem;}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo">JBH</div>
  <a href="student.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
  <a href="profile.php" class="nav-item active"><i class="fas fa-user-circle"></i> Profile</a>
  <a href="../api/logout.php" class="nav-item" style="margin-top:20px;color:#c04a2b"><i class="fas fa-sign-out-alt"></i> Logout</a>
</aside>
<div class="main">
  <div class="card">
    <div class="profile-header">
      <div class="avatar"><?= strtoupper($initials) ?></div>
      <div class="profile-info">
        <h1><?= htmlspecialchars($student['full_name']) ?></h1>
        <p><?= htmlspecialchars($student['student_id']) ?> · Room <?= htmlspecialchars($student['room_number'] ?? '-') ?>, Block <?= htmlspecialchars($student['block'] ?? 'A') ?></p>
      </div>
    </div>
    <div class="profile-grid">
      <div class="profile-item"><label>Student ID</label><p><?= htmlspecialchars($student['student_id']) ?></p></div>
      <div class="profile-item"><label>Room</label><p><?= htmlspecialchars($student['room_number'] ?? '-') ?> · <?= ucfirst($student['room_type'] ?? 'triple') ?> Sharing</p></div>
      <div class="profile-item"><label>Course</label><p><?= htmlspecialchars($student['course'] ?? '-') ?></p></div>
      <div class="profile-item"><label>Year</label><p><?= htmlspecialchars($student['year'] ?? '-') ?></p></div>
      <div class="profile-item"><label>Department</label><p><?= htmlspecialchars($student['department'] ?? '-') ?></p></div>
      <div class="profile-item"><label>Email</label><p><?= htmlspecialchars($student['email'] ?? '-') ?></p></div>
      <div class="profile-item"><label>Phone</label><p><?= htmlspecialchars($student['phone'] ?? '-') ?></p></div>
      <div class="profile-item"><label>Guardian</label><p><?= htmlspecialchars($student['guardian_name'] ?? '-') ?> · <?= htmlspecialchars($student['guardian_phone'] ?? '-') ?></p></div>
    </div>
  </div>
</div>
</body>
</html>
