<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

// Handle add student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $sid = trim($_POST['student_id'] ?? '');
    $name = trim($_POST['full_name'] ?? '');
    $pass = $_POST['password'] ?? 'password';
    $room = trim($_POST['room_number'] ?? '');
    $block = $_POST['block'] ?? 'A';
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if (!empty($sid) && !empty($name)) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO students (student_id, password, full_name, email, phone, room_number, block) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$sid, $hash, $name, $email, $phone, $room, $block]);
            header('Location: admin-students.php?added=1');
            exit;
        } catch (PDOException $e) {}
    }
}

$students = $pdo->query("SELECT * FROM students WHERE is_active = 1 ORDER BY student_id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Students – Admin</title>
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
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
    .form-group{margin-bottom:16px;}
    .form-group label{display:block;font-family:'IBM Plex Mono';font-size:0.65rem;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:6px;}
    .form-group input,.form-group select{width:100%;padding:12px;border:1.5px solid var(--border);border-radius:6px;}
    .btn{padding:12px 24px;border-radius:6px;border:none;cursor:pointer;font-family:'IBM Plex Mono';font-size:0.75rem;text-transform:uppercase;font-weight:600;}
    .btn-primary{background:var(--ink);color:var(--gold);}
    table{width:100%;border-collapse:collapse;}
    th,td{padding:12px;text-align:left;border-bottom:1px solid var(--border);}
    th{font-family:'IBM Plex Mono';font-size:0.65rem;letter-spacing:2px;text-transform:uppercase;color:var(--muted);}
    .alert{background:#f0fdf4;color:#166534;padding:12px;border-radius:6px;margin-bottom:20px;}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo">JBH</div>
  <a href="admin.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
  <a href="admin-notices.php" class="nav-item"><i class="fas fa-bell"></i> Notices</a>
  <a href="admin-complaints.php" class="nav-item"><i class="fas fa-tools"></i> Complaints</a>
  <a href="admin-students.php" class="nav-item active"><i class="fas fa-users"></i> Students</a>
  <a href="admin-leave.php" class="nav-item"><i class="fas fa-calendar-check"></i> Leave</a>
  <a href="../api/logout.php" class="nav-item" style="margin-top:20px;color:#c04a2b"><i class="fas fa-sign-out-alt"></i> Logout</a>
</aside>
<div class="main">
  <?php if (isset($_GET['added'])): ?><div class="alert">Student added successfully!</div><?php endif; ?>
  <div class="card">
    <h2>Add New Student</h2>
    <form method="POST">
      <input type="hidden" name="add_student" value="1"/>
      <div class="form-row">
        <div class="form-group">
          <label>Student ID</label>
          <input type="text" name="student_id" required placeholder="e.g. DEI-2K24-CS-001"/>
        </div>
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="full_name" required placeholder="Full name"/>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Room Number</label>
          <input type="text" name="room_number" placeholder="e.g. 114"/>
        </div>
        <div class="form-group">
          <label>Block</label>
          <select name="block">
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="C">C</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" placeholder="student@dei.ac.in"/>
        </div>
        <div class="form-group">
          <label>Phone</label>
          <input type="tel" name="phone" placeholder="10-digit mobile"/>
        </div>
      </div>
      <div class="form-group">
        <label>Password (default: password)</label>
        <input type="text" name="password" placeholder="password" value="password"/>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Student</button>
    </form>
  </div>
  <div class="card">
    <h2>All Students</h2>
    <table>
      <thead><tr><th>Student ID</th><th>Name</th><th>Room</th><th>Block</th><th>Email</th></tr></thead>
      <tbody>
        <?php foreach ($students as $s): ?>
        <tr>
          <td><?= htmlspecialchars($s['student_id']) ?></td>
          <td><?= htmlspecialchars($s['full_name']) ?></td>
          <td><?= htmlspecialchars($s['room_number'] ?? '-') ?></td>
          <td><?= htmlspecialchars($s['block'] ?? 'A') ?></td>
          <td><?= htmlspecialchars($s['email'] ?? '-') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if (empty($students)): ?><p style="color:var(--muted);">No students.</p><?php endif; ?>
  </div>
</div>
</body>
</html>
