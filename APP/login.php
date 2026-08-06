<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if (!empty($user) && !empty($pass)) {
        try {
            // Check if admin user exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :u");
            $stmt->execute([':u' => $user]);
            $userData = $stmt->fetch();

            // Emergency / Default Login check for 'admin' & 'admin123'
            if ($user === 'admin' && $pass === 'admin123') {
                $newHash = password_hash('admin123', PASSWORD_DEFAULT);
                
                if (!$userData) {
                    // Create admin if missing
                    $ins = $pdo->prepare("INSERT INTO users (username, password, full_name, role) VALUES ('admin', :p, 'Cris Ian Laminero', 'Admin')");
                    $ins->execute([':p' => $newHash]);
                    $userId = $pdo->lastInsertId();
                } else {
                    // Update hash for existing admin
                    $upd = $pdo->prepare("UPDATE users SET password = :p WHERE username = 'admin'");
                    $upd->execute([':p' => $newHash]);
                    $userId = $userData['id'];
                }

                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = 'admin';
                $_SESSION['full_name'] = $userData['full_name'] ?? 'Cris Ian Laminero';
                
                session_write_close();
                header("Location: index.php");
                echo "<script>window.location.href = 'index.php';</script>";
                exit;
            } 
            
            // Standard verification for other users
            if ($userData && password_verify($pass, $userData['password'])) {
                $_SESSION['user_id'] = $userData['id'];
                $_SESSION['username'] = $userData['username'];
                $_SESSION['full_name'] = $userData['full_name'];

                session_write_close();
                header("Location: index.php");
                echo "<script>window.location.href = 'index.php';</script>";
                exit;
            } else {
                $error = "Invalid username or password!";
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - NEXUS AUTO</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
  
  <div class="saas-card shadow-lg p-5 rounded-4" style="max-width: 450px; width: 100%;">
    <div class="text-center mb-5">
      <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-block mb-3">
        <i class="bi bi-hexagon-fill text-primary fs-1"></i>
      </div>
      <h2 class="fw-bold m-0 text-white">NEXUS AUTO</h2>
      <p class="text-slate mt-2">Sign in to manage your inventory and orders</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger py-2 fs-7 rounded-3 d-flex align-items-center gap-2" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="username" name="username" placeholder="Username" required autocomplete="username">
        <label for="username" class="text-secondary">Username</label>
      </div>
      <div class="form-floating mb-4">
        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required autocomplete="current-password">
        <label for="password" class="text-secondary">Password</label>
      </div>
      <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 fw-bold fs-6">Sign In <i class="bi bi-arrow-right ms-2"></i></button>
    </form>
  </div>

</body>
</html>