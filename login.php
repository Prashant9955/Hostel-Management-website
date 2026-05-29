<?php
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) {
    $role = $_SESSION['user_role'];
    if ($role === 'student') header('Location: dashboard/student.php');
    else header('Location: dashboard/admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login – JBH Portal | DEI Agra</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Serif+Display:ital@0;1&family=IBM+Plex+Mono:wght@400;600&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    :root { --ink:#0b0c0e; --paper:#f5f0e8; --gold:#c8973a; --gold-light:#e8b85a; --rust:#c04a2b; --teal:#1a6b6b; --cream:#faf6ee; --muted:#8a8070; --border:#d4c8b0; }
    *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
    body { min-height:100vh; display:grid; grid-template-columns: 1fr 1fr; font-family:'Outfit',sans-serif; background:var(--ink); overflow-x:hidden; cursor:none; }
    .cursor { width:8px;height:8px;background:var(--gold);border-radius:50%;position:fixed;top:0;left:0;pointer-events:none;z-index:9999;mix-blend-mode:screen; }
    .cursor-ring { width:32px;height:32px;border:1px solid var(--gold);border-radius:50%;position:fixed;top:0;left:0;pointer-events:none;z-index:9998;transition:transform 0.2s cubic-bezier(.25,.8,.25,1);mix-blend-mode:screen; }
    .left-panel { position:relative; overflow:hidden; display:flex;flex-direction:column;justify-content:space-between; padding: 48px; background: linear-gradient(160deg, #0a1628 0%, #0d3333 50%, #1a0d08 100%); }
    .left-panel::before { content:''; position:absolute; inset:0; background: radial-gradient(ellipse at 70% 40%, rgba(200,151,58,0.12) 0%, transparent 60%); pointer-events:none; }
    .grid-lines { position:absolute; inset:0; pointer-events:none; background-image: linear-gradient(rgba(200,151,58,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(200,151,58,0.04) 1px, transparent 1px); background-size: 50px 50px; }
    .left-top { position:relative; z-index:1; }
    .back-link { display:inline-flex; align-items:center; gap:10px; color:rgba(255,255,255,0.4); text-decoration:none; font-family:'IBM Plex Mono',monospace; font-size:0.7rem; letter-spacing:2px; text-transform:uppercase; transition:color 0.3s; margin-bottom:48px; display:block; }
    .back-link:hover { color:var(--gold); }
    .brand-badge { width:52px; height:52px; background:rgba(200,151,58,0.15); border:1px solid rgba(200,151,58,0.3); border-radius:10px; display:flex; align-items:center; justify-content:center; font-family:'Bebas Neue',sans-serif; color:var(--gold); font-size:18px; letter-spacing:1px; margin-bottom:20px; }
    .brand-name { font-family:'DM Serif Display',serif; font-size:2.4rem; color:white; line-height:1.1; margin-bottom:8px; }
    .brand-name em { color:var(--gold); font-style:italic; }
    .brand-sub { font-family:'IBM Plex Mono',monospace; font-size:0.7rem; color:rgba(255,255,255,0.3); letter-spacing:3px; text-transform:uppercase; }
    .left-center { position:relative; z-index:1; display:flex; flex-direction:column; align-items:center; }
    .orbit-container { position:relative; width:260px; height:260px; display:flex; align-items:center; justify-content:center; }
    .orbit-ring { position:absolute; border-radius:50%; border:1px solid rgba(200,151,58,0.15); }
    .orbit-ring:nth-child(1) { width:100%; height:100%; animation:spin 20s linear infinite; }
    .orbit-ring:nth-child(2) { width:70%; height:70%; animation:spin 14s linear infinite reverse; }
    .orbit-ring:nth-child(3) { width:40%; height:40%; animation:spin 8s linear infinite; }
    .orbit-dot { position:absolute; width:8px; height:8px; border-radius:50%; background:var(--gold); box-shadow:0 0 12px var(--gold); }
    .orbit-ring:nth-child(1) .orbit-dot { top:-4px; left:calc(50% - 4px); }
    .orbit-ring:nth-child(2) .orbit-dot { top:-4px; right:20%; background:var(--teal); box-shadow:0 0 12px var(--teal); }
    .orbit-ring:nth-child(3) .orbit-dot { bottom:-4px; left:calc(50% - 4px); background:var(--rust); box-shadow:0 0 12px var(--rust); }
    .orbit-center { position:relative; z-index:2; text-align:center; background:rgba(10,15,25,0.8); border:1px solid rgba(200,151,58,0.2); border-radius:50%; width:120px; height:120px; display:flex; align-items:center; justify-content:center; flex-direction:column; backdrop-filter:blur(10px); }
    .orbit-center .big-icon { font-size:2.2rem; }
    .orbit-center p { font-family:'IBM Plex Mono',monospace; font-size:0.55rem; letter-spacing:2px; color:rgba(255,255,255,0.4); margin-top:4px; text-transform:uppercase; }
    @keyframes spin { from{transform:rotate(0)} to{transform:rotate(360deg)} }
    .left-tagline { text-align:center; margin-top:28px; }
    .left-tagline h3 { font-family:'DM Serif Display',serif; font-size:1.4rem; color:white; margin-bottom:8px; }
    .left-tagline p { font-size:0.82rem; color:rgba(255,255,255,0.35); line-height:1.7; max-width:280px; margin:0 auto; }
    .left-bottom { position:relative; z-index:1; }
    .role-chips { display:flex; gap:10px; flex-wrap:wrap; }
    .role-chip { padding:6px 14px; border-radius:20px; font-family:'IBM Plex Mono',monospace; font-size:0.62rem; letter-spacing:1.5px; text-transform:uppercase; display:flex; align-items:center; gap:6px; }
    .chip-student { background:rgba(26,107,107,0.3); color:var(--teal-light,#2a9090); border:1px solid rgba(26,107,107,0.5); }
    .chip-warden { background:rgba(200,151,58,0.2); color:var(--gold); border:1px solid rgba(200,151,58,0.3); }
    .chip-parent { background:rgba(255,255,255,0.07); color:rgba(255,255,255,0.4); border:1px solid rgba(255,255,255,0.1); }
    .right-panel { background:var(--cream); display:flex; flex-direction:column; align-items:center; justify-content:center; padding: 60px 8%; position:relative; overflow:hidden; }
    .right-panel::before { content:''; position:absolute; bottom:-100px; right:-100px; width:400px; height:400px; background:radial-gradient(circle, rgba(200,151,58,0.06) 0%, transparent 70%); pointer-events:none; }
    .login-box { width:100%; max-width:420px; position:relative; z-index:1; }
    .login-header { margin-bottom:40px; }
    .login-header h2 { font-family:'Bebas Neue',sans-serif; font-size:3rem; line-height:0.9; letter-spacing:2px; color:var(--ink); margin-bottom:8px; }
    .login-header h2 span { color:var(--gold); }
    .login-header p { font-size:0.88rem; color:var(--muted); line-height:1.6; }
    .role-tabs { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-bottom:32px; background:white; padding:6px; border-radius:8px; border:1px solid var(--border); }
    .role-tab { padding:10px 8px; border-radius:5px; border:none; background:transparent; cursor:pointer; font-family:'IBM Plex Mono',monospace; font-size:0.62rem; letter-spacing:1.5px; text-transform:uppercase; color:var(--muted); transition:all 0.25s; display:flex; align-items:center; justify-content:center; gap:6px; }
    .role-tab.active { background:var(--ink); color:var(--gold); box-shadow:0 2px 12px rgba(0,0,0,0.15); }
    .role-tab:hover:not(.active) { background:var(--cream); color:var(--ink); }
    .form-group { margin-bottom:20px; }
    .form-group label { display:block; font-family:'IBM Plex Mono',monospace; font-size:0.63rem; letter-spacing:2px; text-transform:uppercase; color:var(--muted); margin-bottom:7px; }
    .input-wrap { position:relative; }
    .input-wrap i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--muted); font-size:0.85rem; pointer-events:none; transition:color 0.3s; }
    .input-wrap input { width:100%; padding:13px 16px 13px 42px; border:1.5px solid var(--border); border-radius:6px; background:white; color:var(--ink); font-family:'Outfit',sans-serif; font-size:0.92rem; transition:all 0.3s; outline:none; }
    .input-wrap input:focus { border-color:var(--gold); background:var(--cream); }
    .input-wrap:focus-within i { color:var(--gold); }
    .input-action { position:absolute; right:14px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--muted); font-size:0.85rem; transition:color 0.3s; }
    .input-action:hover { color:var(--ink); }
    .form-options { display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; margin-top:-6px; }
    .checkbox-label { display:flex; align-items:center; gap:8px; font-size:0.82rem; color:var(--muted); cursor:pointer; }
    .checkbox-label input[type="checkbox"] { width:16px; height:16px; accent-color:var(--gold); cursor:pointer; }
    .forgot-link { font-family:'IBM Plex Mono',monospace; font-size:0.68rem; letter-spacing:1px; color:var(--teal); text-decoration:none; text-transform:uppercase; transition:color 0.3s; }
    .forgot-link:hover { color:var(--gold); }
    .btn-login { width:100%; padding:15px; background:var(--ink); color:var(--gold); border:2px solid var(--ink); border-radius:6px; font-family:'IBM Plex Mono',monospace; font-size:0.8rem; letter-spacing:2px; text-transform:uppercase; cursor:pointer; font-weight:600; transition:all 0.3s; display:flex; align-items:center; justify-content:center; gap:12px; position:relative; overflow:hidden; }
    .btn-login::before { content:''; position:absolute; inset:0; background:var(--gold); transform:translateX(-100%); transition:transform 0.4s cubic-bezier(.77,0,.175,1); }
    .btn-login:hover::before { transform:translateX(0); }
    .btn-login:hover { color:var(--ink); }
    .btn-login span { position:relative; z-index:1; display:flex; align-items:center; gap:12px; }
    .login-divider { text-align:center; margin:24px 0; position:relative; color:var(--border); font-family:'IBM Plex Mono',monospace; font-size:0.65rem; letter-spacing:3px; }
    .login-divider::before, .login-divider::after { content:''; position:absolute; top:50%; height:1px; background:var(--border); width:calc(50% - 30px); }
    .login-divider::before { left:0; }
    .login-divider::after { right:0; }
    .alt-logins { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .alt-btn { padding:11px; border:1.5px solid var(--border); border-radius:6px; background:white; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; font-family:'IBM Plex Mono',monospace; font-size:0.65rem; letter-spacing:1px; text-transform:uppercase; color:var(--muted); transition:all 0.3s; }
    .alt-btn:hover { border-color:var(--gold); color:var(--ink); background:var(--cream); }
    .alt-btn i { font-size:1rem; }
    .login-footer { margin-top:32px; text-align:center; font-size:0.8rem; color:var(--muted); }
    .login-footer a { color:var(--teal); text-decoration:none; font-weight:500; }
    .login-footer a:hover { color:var(--gold); }
    .field-error { display:flex; align-items:center; gap:6px; color:var(--rust); font-size:0.72rem; margin-top:5px; font-family:'IBM Plex Mono',monospace; opacity:0; transform:translateY(-4px); transition:all 0.3s; }
    .field-error.show { opacity:1; transform:translateY(0); }
    .field-error.msg { margin-bottom:12px; }
    @keyframes spin-load { to{transform:rotate(360deg)} }
    .spinner { width:16px; height:16px; border:2px solid rgba(200,151,58,0.3); border-top-color:var(--gold); border-radius:50%; animation:spin-load 0.7s linear infinite; display:none; }
    .loading .spinner { display:block; }
    .loading .btn-text { display:none; }
    .notice-bar { background:rgba(200,151,58,0.08); border:1px solid rgba(200,151,58,0.2); border-radius:6px; padding:12px 16px; margin-bottom:24px; display:flex; align-items:flex-start; gap:10px; }
    .notice-bar i { color:var(--gold); margin-top:1px; flex-shrink:0; }
    .notice-bar p { font-size:0.75rem; color:#6b5500; line-height:1.5; }
    @media(max-width:768px) { body { grid-template-columns:1fr; } .left-panel { display:none; } .right-panel { padding:40px 6%; min-height:100vh; } }
  </style>
</head>
<body>

<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursorRing"></div>

<div class="left-panel">
  <div class="grid-lines"></div>
  <div class="left-top">
    <a href="index.html" class="back-link">← Back to Website</a>
    <div class="left-brand">
      <div class="brand-badge">JBH</div>
      <div class="brand-name">Junior Boys<br><em>Hostel</em></div>
      <div class="brand-sub">DEI Agra · Student Portal</div>
    </div>
  </div>
  <div class="left-center">
    <div class="orbit-container">
      <div class="orbit-ring"><div class="orbit-dot"></div></div>
      <div class="orbit-ring"><div class="orbit-dot"></div></div>
      <div class="orbit-ring"><div class="orbit-dot"></div></div>
      <div class="orbit-center"><span class="big-icon">🏠</span><p>JBH Portal</p></div>
    </div>
    <div class="left-tagline">
      <h3>Your Home,<br>Your Hub</h3>
      <p>Access your profile, mess bills, complaints, and notices — all in one place.</p>
    </div>
  </div>
  <div class="left-bottom">
    <p style="font-family:'IBM Plex Mono',monospace;font-size:0.62rem;letter-spacing:2px;color:rgba(255,255,255,0.2);text-transform:uppercase;margin-bottom:12px">Portal Access For</p>
    <div class="role-chips">
      <span class="role-chip chip-student"><i class="fas fa-graduation-cap"></i> Students</span>
      <span class="role-chip chip-warden"><i class="fas fa-user-shield"></i> Warden</span>
      <span class="role-chip chip-parent"><i class="fas fa-users"></i> Parents</span>
    </div>
  </div>
</div>

<div class="right-panel">
  <div class="login-box">
    <div class="login-header">
      <h2>SIGN <span>IN</span></h2>
      <p>Access your hostel portal. Enter your credentials below to continue.</p>
    </div>

    <div class="notice-bar">
      <i class="fas fa-info-circle"></i>
      <p><strong>Demo:</strong> Student: <code>DEI-2K23-CS-042</code> &nbsp;·&nbsp; Admin: <code>admin</code> &nbsp;·&nbsp; Pass: <code>password</code></p>
    </div>

    <div class="role-tabs">
      <button type="button" class="role-tab active" data-role="student"><i class="fas fa-graduation-cap"></i> Student</button>
      <button type="button" class="role-tab" data-role="warden"><i class="fas fa-user-shield"></i> Warden</button>
      <button type="button" class="role-tab" data-role="admin"><i class="fas fa-cog"></i> Admin</button>
    </div>

    <div class="field-error msg show" id="loginError" style="display:none;opacity:0;margin-bottom:12px"></div>

    <form id="loginForm">
      <input type="hidden" id="roleInput" name="role" value="student"/>
      <div class="form-group">
        <label id="idLabel">Student ID</label>
        <div class="input-wrap">
          <i class="fas fa-id-card"></i>
          <input type="text" id="userId" name="userId" placeholder="e.g. DEI-2K23-CS-042" autocomplete="username"/>
          <div class="field-error" id="userError"><i class="fas fa-exclamation-circle"></i> Please enter your ID</div>
        </div>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div class="input-wrap" style="position:relative">
          <i class="fas fa-lock"></i>
          <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password"/>
          <button type="button" class="input-action" onclick="togglePassword()"><i class="fas fa-eye" id="eyeIcon"></i></button>
        </div>
        <div class="field-error" id="passError"><i class="fas fa-exclamation-circle"></i> Please enter your password</div>
      </div>
      <div class="form-options">
        <label class="checkbox-label"><input type="checkbox" id="remember"/> Remember me</label>
        <a href="#" class="forgot-link">Forgot Password?</a>
      </div>
      <button type="submit" class="btn-login" id="loginBtn">
        <span class="btn-text"><i class="fas fa-sign-in-alt"></i> Sign In to Portal</span>
        <div class="spinner"></div>
      </button>
    </form>

    <div class="login-divider">or continue with</div>
    <div class="alt-logins">
      <button type="button" class="alt-btn"><i class="fas fa-university" style="color:var(--teal)"></i> DEI SSO</button>
      <button type="button" class="alt-btn"><i class="fas fa-qrcode" style="color:var(--gold)"></i> QR Login</button>
    </div>
    <div class="login-footer">
      <p>New boarder? <a href="index.html#contact">Request Account Access</a></p>
      <p style="margin-top:10px;font-size:0.72rem;color:rgba(0,0,0,0.3)">
        <a href="index.html" style="color:rgba(0,0,0,0.35)">← Return to Website</a> · <a href="#" style="color:rgba(0,0,0,0.35)">Help</a>
      </p>
    </div>
  </div>
</div>

<script>
const cursor=document.getElementById('cursor'),ring=document.getElementById('cursorRing');
let mx=0,my=0,rx=0,ry=0;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;});
function ac(){cursor.style.transform=`translate(${mx-4}px,${my-4}px)`;rx+=(mx-rx)*.12;ry+=(my-ry)*.12;ring.style.transform=`translate(${rx-16}px,${ry-16}px)`;requestAnimationFrame(ac);}
ac();

document.querySelectorAll('.role-tab').forEach(t=>{
  t.addEventListener('click',function(){
    document.querySelectorAll('.role-tab').forEach(x=>x.classList.remove('active'));
    this.classList.add('active');
    const r=this.dataset.role;
    document.getElementById('roleInput').value=r;
    const labels={student:'Student ID',warden:'Warden ID',admin:'Admin Username'};
    const ph={student:'e.g. DEI-2K23-CS-042',warden:'e.g. warden',admin:'e.g. admin'};
    document.getElementById('idLabel').textContent=labels[r];
    document.getElementById('userId').placeholder=ph[r];
  });
});

function togglePassword(){
  const p=document.getElementById('password'),i=document.getElementById('eyeIcon');
  p.type=p.type==='password'?'text':'password';
  i.className=p.type==='password'?'fas fa-eye':'fas fa-eye-slash';
}

document.getElementById('loginForm').addEventListener('submit',async function(e){
  e.preventDefault();
  const uid=document.getElementById('userId').value.trim();
  const pwd=document.getElementById('password').value.trim();
  let valid=true;
  document.getElementById('userError').classList.toggle('show',!uid);
  document.getElementById('passError').classList.toggle('show',!pwd);
  document.getElementById('loginError').style.display='none';
  if(!uid||!pwd) return;

  const btn=document.getElementById('loginBtn');
  btn.classList.add('loading');

  const formData=new FormData(this);
  try {
    const res=await fetch('api/login.php',{method:'POST',body:formData});
    const data=await res.json();
    if(data.success){
      btn.classList.remove('loading');
      btn.querySelector('.btn-text').innerHTML='<i class="fas fa-check"></i> Login Successful!';
      btn.style.background='#22c55e'; btn.style.borderColor='#22c55e'; btn.style.color='white';
      setTimeout(()=>window.location.href=data.redirect||'dashboard/student.php',800);
    } else {
      btn.classList.remove('loading');
      const errEl=document.getElementById('loginError');
      errEl.textContent=data.message||'Login failed. Please try again.';
      errEl.style.display='block';
      errEl.style.opacity=1;
    }
  } catch(err){
    btn.classList.remove('loading');
    document.getElementById('loginError').textContent='Connection error. Please try again.';
    document.getElementById('loginError').style.display='block';
  }
});
</script>
</body>
</html>
