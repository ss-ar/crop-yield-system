<?php
require_once __DIR__ . '/../app/helpers/Auth.php';
require_login();

require_once __DIR__ . '/../app/config/database.php';

$isAdmin = (($_SESSION['role'] ?? '') === 'admin');
$userId  = current_user_id();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: /history.php");
    exit;
}

// Build secure query: if not admin, enforce ownership
$sql = "
SELECT
  p.*,
  u.username
FROM predictions p
LEFT JOIN users u ON u.id = p.user_id
WHERE p.id = :id
";

$params = [':id' => $id];

if (!$isAdmin) {
    $sql .= " AND p.user_id = :user_id";
    $params[':user_id'] = $userId;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$row = $stmt->fetch();

if (!$row) {
    // Not found OR not allowed
    http_response_code(404);
    $message = $isAdmin ? "Record not found." : "Record not found or you do not have permission.";
} else {
    $message = '';
}

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function risk_badge(string $risk): string {
    return match ($risk) {
        'low' => 'success',
        'medium' => 'warning',
        'high' => 'danger',
        default => 'secondary',
    };
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Prediction Details | Crop Yield DSS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="/predict.php">🌱 Crop Yield DSS</a>
    <div class="ms-auto d-flex gap-2">
      <a class="btn btn-outline-light btn-sm" href="/history.php">Back to History</a>
      <a class="btn btn-light btn-sm" href="/logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-9">

      <?php if ($message): ?>
        <div class="alert alert-danger"><?php echo h($message); ?></div>
      <?php else: ?>

        <div class="card shadow-sm border-0 mb-3">
          <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
              <div>
                <h3 class="mb-1">Prediction Details</h3>
                <div class="text-muted">
                  ID #<?php echo (int)$row['id']; ?> •
                  <?php echo h((string)$row['created_at']); ?>
                  <?php if ($isAdmin): ?>
                    • User: <?php echo h((string)($row['username'] ?? '—')); ?>
                  <?php endif; ?>
                </div>
              </div>
              <span class="badge bg-<?php echo risk_badge((string)$row['risk_level']); ?> fs-6">
                Risk: <?php echo h(ucfirst((string)$row['risk_level'])); ?>
              </span>
            </div>

            <hr class="my-4">

            <div class="row g-3">
              <div class="col-md-6">
                <div class="p-3 bg-white border rounded-3">
                  <div class="text-muted">Predicted Yield (tons/acre)</div>
                  <div class="display-6 mb-0"><?php echo h((string)$row['predicted_yield_tpa']); ?></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 bg-white border rounded-3">
                  <div class="text-muted">Predicted Total Yield (tons)</div>
                  <div class="display-6 mb-0"><?php echo h((string)$row['predicted_yield_tons']); ?></div>
                </div>
              </div>
            </div>

            <h5 class="mt-4">Recommendations</h5>
            <div class="alert alert-info mb-0">
              <?php echo nl2br(h((string)$row['recommendations'])); ?>
            </div>
          </div>
        </div>

        <div class="card shadow-sm border-0">
          <div class="card-body p-4">
            <h5 class="mb-3">Inputs Used</h5>

            <div class="row g-2">
              <div class="col-md-4"><strong>Crop:</strong> <?php echo h(ucfirst((string)$row['crop_type'])); ?></div>
              <div class="col-md-4"><strong>District:</strong> <?php echo h((string)$row['district']); ?></div>
              <div class="col-md-4"><strong>Season:</strong> <?php echo h((string)$row['season']); ?></div>

              <div class="col-md-4"><strong>Farm size:</strong> <?php echo h((string)$row['farm_size_acres']); ?> acres</div>
              <div class="col-md-4"><strong>Soil type:</strong> <?php echo h((string)$row['soil_type']); ?></div>
              <div class="col-md-4"><strong>Seed type:</strong> <?php echo h((string)$row['seed_type']); ?></div>

              <div class="col-md-4"><strong>Rainfall:</strong> <?php echo h((string)$row['rainfall_mm']); ?> mm</div>
              <div class="col-md-4"><strong>Avg temp:</strong> <?php echo h((string)$row['avg_temp_c']); ?> °C</div>
              <div class="col-md-4"><strong>Fertilizer:</strong> <?php echo h((string)$row['fertilizer_kg']); ?> kg</div>

              <div class="col-md-4"><strong>Irrigation:</strong> <?php echo h((string)$row['irrigation']); ?></div>
            </div>
          </div>
        </div>

      <?php endif; ?>

    </div>
  </div>
</div>

<script src="/assets/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
