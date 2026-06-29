<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();

// Sudah login? Langsung arahkan ke dashboard
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

 $error = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_start();
    require_once __DIR__ . '/config/database.php';
    ob_end_clean();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id_user'    => $user['id_user'],
                'nama'       => $user['nama'],
                'username'   => $user['username'],
                'role'       => $user['role']
            ];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau password salah';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Inventaris UKM</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
<link rel="stylesheet" href="assets/css/style.css">
<style>
    body {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        background: #020202;
    }
    .login-card {
        background: rgba(38, 38, 38, 0.95);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 1.5rem;
        padding: 2.5rem;
        width: 100%;
        max-width: 400px;
        margin: 1rem;
        animation: scaleIn 0.5s cubic-bezier(0.16,1,0.3,1) forwards;
    }
    .login-input {
        background: rgba(30, 30, 30, 0.9);
        border: 1px solid rgba(255,255,255,0.1);
        color: #e5e5e5;
        transition: all 0.2s ease;
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        outline: none;
    }
    .login-input:focus {
        border-color: rgba(163,230,53,0.4);
        box-shadow: 0 0 0 3px rgba(163,230,53,0.08);
    }
    .login-input::placeholder { color: #525252; }
    .login-btn {
        background: #65a30d;
        color: #000;
        font-weight: 600;
        width: 100%;
        padding: 0.75rem;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        box-shadow: 0 0 20px rgba(163,230,53,0.15);
        cursor: pointer;
        border: none;
    }
    .login-btn:hover {
        background: #84cc16;
        transform: scale(1.02);
        box-shadow: 0 0 30px rgba(163,230,53,0.3);
    }
    .error-box {
        background: rgba(239,68,68,0.1);
        border: 1px solid rgba(239,68,68,0.25);
        color: #f87171;
        padding: 0.625rem 1rem;
        border-radius: 0.75rem;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
</style>
</head>
<body class="grid-bg">

<div class="login-card">
    <!-- Logo -->
    <div class="flex items-center justify-center gap-3 mb-8">
        <div class="w-11 h-11 rounded-xl bg-lime-400/10 border border-lime-400/20 flex items-center justify-center">
            <span class="iconify text-lime-400 text-2xl" data-icon="lucide:package"></span>
        </div>
        <div>
            <h1 class="text-base font-semibold text-neutral-100" style="font-family:-apple-system,BlinkMacSystemFont,'SF Pro Display',sans-serif">Inventaris UKM</h1>
            <p class="text-[10px] text-neutral-500 font-medium tracking-widest uppercase">Management System</p>
        </div>
    </div>

    <!-- Error Message -->
    <?php if ($error !== ''): ?>
    <div class="error-box mb-5">
        <span class="iconify text-base flex-shrink-0" data-icon="lucide:alert-circle"></span>
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="POST" action="login.php" class="space-y-4">
        <div>
            <label class="text-xs text-neutral-400 mb-1.5 block" style="color:#d4d4d4">Username</label>
            <div class="relative">
                <span class="iconify absolute left-3 top-1/2 -translate-y-1/2 text-neutral-500" data-icon="lucide:user"></span>
                <input type="text" name="username" class="login-input pl-10" placeholder="Masukkan username" required autofocus
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
        </div>
        <div>
            <label class="text-xs mb-1.5 block" style="color:#d4d4d4">Password</label>
            <div class="relative">
                <span class="iconify absolute left-3 top-1/2 -translate-y-1/2 text-neutral-500" data-icon="lucide:lock"></span>
                <input type="password" name="password" class="login-input pl-10" placeholder="Masukkan password" required>
            </div>
        </div>
        <button type="submit" class="login-btn flex items-center justify-center gap-2 mt-2">
            <span class="iconify" data-icon="lucide:log-in"></span>
            Masuk
        </button>
    </form>

    <!-- Info -->
    <div class="mt-6 pt-5 border-t border-white/5">
        <p class="text-[11px] text-center" style="color:#a3a3a3">
            Demo: username <span class="text-lime-400 font-mono">admin</span> &nbsp;password <span class="text-lime-400 font-mono">admin123</span>
        </p>
    </div>
</div>

</body>
</html>