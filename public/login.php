<?php
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/Auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, password_hash, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['role'] = $user['role'];
        header("Location: /predict.php");
        exit;
    }

    $error = 'Invalid username or password';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login | Crop Yield DSS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <link href="/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh;">

<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
      <div class="card shadow-sm border-0">
        <div class="card-body p-4">
          <h3 class="text-center mb-3">🌱 Crop Yield DSS</h3>
          <p class="text-center text-muted mb-4">Login to continue</p>

          <?php if ($error): ?>
            <div class="alert alert-danger py-2">
              <?php echo htmlspecialchars($error); ?>
            </div>
          <?php endif; ?>

          <form method="post" autocomplete="off">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>

            <div class="d-grid mt-4">
              <button class="btn btn-success">Login</button>
            </div>
          </form>

        </div>
      </div>
      <p class="text-center text-muted small mt-3">
        Precision Agriculture Decision Support System
      </p>
    </div>
  </div>
</div>


<script src="/assets/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
