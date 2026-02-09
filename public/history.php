<?php
require_once __DIR__ . '/../app/helpers/Auth.php';
require_login();

require_once __DIR__ . '/../app/config/database.php';

$userId = current_user_id();

// ---- Filters (optional) ----
$crop   = trim((string)($_GET['crop'] ?? ''));
$season = trim((string)($_GET['season'] ?? ''));
$district = trim((string)($_GET['district'] ?? ''));

// For prototype: show all users if admin, otherwise show only own records
$isAdmin = (($_SESSION['role'] ?? '') === 'admin');

$where = [];
$params = [];

if (!$isAdmin) {
    $where[] = "p.user_id = :user_id";
    $params[':user_id'] = $userId;
}

if ($crop !== '') {
    $where[] = "p.crop_type = :crop";
    $params[':crop'] = $crop;
}

if ($season !== '') {
    $where[] = "p.season = :season";
    $params[':season'] = $season;
}

if ($district !== '') {
    $where[] = "p.district = :district";
    $params[':district'] = $district;
}

$whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

// ---- Export CSV (same filters) ----
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $sqlExport = "
    SELECT
      p.created_at,
      u.username,
      p.crop_type,
      p.district,
      p.season,
      p.farm_size_acres,
      p.soil_type,
      p.rainfall_mm,
      p.avg_temp_c,
      p.fertilizer_kg,
      p.irrigation,
      p.seed_type,
      p.predicted_yield_tpa,
      p.predicted_yield_tons,
      p.risk_level,
      p.recommendations
    FROM predictions p
    LEFT JOIN users u ON u.id = p.user_id
    {$whereSql}
    ORDER BY p.created_at DESC
    ";

    $stmt = $pdo->prepare($sqlExport);
    $stmt->execute($params);

    $filename = "crop_yield_history_" . date("Y-m-d_H-i") . ".csv";

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    $out = fopen('php://output', 'w');

    // CSV header row
    fputcsv($out, [
        'created_at','username','crop_type','district','season',
        'farm_size_acres','soil_type','rainfall_mm','avg_temp_c','fertilizer_kg',
        'irrigation','seed_type',
        'predicted_yield_tpa','predicted_yield_tons','risk_level','recommendations'
    ]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, $row);
    }

    fclose($out);
    exit;
}

// ---- Fetch predictions ----
$sql = "
SELECT
  p.id, p.crop_type, p.district, p.season,
  p.farm_size_acres, p.soil_type, p.rainfall_mm, p.avg_temp_c, p.fertilizer_kg, p.irrigation, p.seed_type,
  p.predicted_yield_tpa, p.predicted_yield_tons, p.risk_level,
  p.created_at,
  u.username
FROM predictions p
LEFT JOIN users u ON u.id = p.user_id
{$whereSql}
ORDER BY p.created_at DESC
LIMIT 200
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// ---- For filter dropdowns ----
// We’ll load distinct crop + season + district values for quick filtering
$distinctCrop = $pdo->query("SELECT DISTINCT crop_type FROM predictions ORDER BY crop_type")->fetchAll();
$distinctSeason = $pdo->query("SELECT DISTINCT season FROM predictions ORDER BY season")->fetchAll();
$distinctDistrict = $pdo->query("SELECT DISTINCT district FROM predictions ORDER BY district")->fetchAll();

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
  <title>History | Crop Yield DSS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="/predict.php">🌱 Crop Yield DSS</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="/predict.php">Predict</a></li>
        <li class="nav-item"><a class="nav-link active" href="/history.php">History</a></li>
        <li class="nav-item"><a class="nav-link" href="/logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-4">

  <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
    <div>
      <h3 class="mb-1">Prediction History</h3>
      <div class="text-muted">
        <?php if ($isAdmin): ?>
          Viewing all records (Admin)
        <?php else: ?>
          Viewing your records
        <?php endif; ?>
      </div>
    </div>
    <div class="d-flex gap-2">
  <a class="btn btn-success" href="/predict.php">+ New Prediction</a>

  <?php
    // Build export link while keeping filters
    $qs = $_GET;
    $qs['export'] = 'csv';
    $exportUrl = '/history.php?' . http_build_query($qs);
  ?>
  <a class="btn btn-outline-primary" href="<?php echo htmlspecialchars($exportUrl); ?>">
    Export CSV
  </a>
</div>

  </div>

  <div class="card shadow-sm border-0 mb-3">
    <div class="card-body p-3">
      <form class="row g-2" method="get">
        <div class="col-md-4">
          <label class="form-label small mb-1">Crop</label>
          <select name="crop" class="form-select">
            <option value="">All crops</option>
            <?php foreach ($distinctCrop as $c): $val = (string)$c['crop_type']; ?>
              <option value="<?php echo h($val); ?>" <?php echo ($val === $crop) ? 'selected' : ''; ?>>
                <?php echo h(ucfirst($val)); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label small mb-1">Season</label>
          <select name="season" class="form-select">
            <option value="">All seasons</option>
            <?php foreach ($distinctSeason as $s): $val = (string)$s['season']; ?>
              <option value="<?php echo h($val); ?>" <?php echo ($val === $season) ? 'selected' : ''; ?>>
                <?php echo h($val); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label small mb-1">District</label>
          <select name="district" class="form-select">
            <option value="">All districts</option>
            <?php foreach ($distinctDistrict as $d): $val = (string)$d['district']; ?>
              <option value="<?php echo h($val); ?>" <?php echo ($val === $district) ? 'selected' : ''; ?>>
                <?php echo h($val); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12 d-flex gap-2 mt-2">
          <button class="btn btn-primary">Apply Filters</button>
          <a class="btn btn-outline-secondary" href="/history.php">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm border-0">
    <div class="card-body p-0">

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <?php if ($isAdmin): ?><th>User</th><?php endif; ?>
              <th>Crop</th>
              <th>District</th>
              <th>Season</th>
              <th class="text-end">TPA</th>
              <th class="text-end">Total (tons)</th>
              <th>Risk</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$rows): ?>
              <tr>
                <td colspan="<?php echo $isAdmin ? 9 : 8; ?>" class="text-center text-muted py-4">
                  No predictions found.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($rows as $r): ?>
                <tr>
                  <td class="small text-muted"><?php echo h((string)$r['created_at']); ?></td>
                  <?php if ($isAdmin): ?>
                    <td><?php echo h((string)($r['username'] ?? '—')); ?></td>
                  <?php endif; ?>
                  <td><?php echo h(ucfirst((string)$r['crop_type'])); ?></td>
                  <td><?php echo h((string)$r['district']); ?></td>
                  <td><?php echo h((string)$r['season']); ?></td>
                  <td class="text-end"><?php echo h((string)$r['predicted_yield_tpa']); ?></td>
                  <td class="text-end"><?php echo h((string)$r['predicted_yield_tons']); ?></td>
                  <td>
                    <span class="badge bg-<?php echo risk_badge((string)$r['risk_level']); ?>">
                      <?php echo h(ucfirst((string)$r['risk_level'])); ?>
                    </span>
                  </td>
                  <td>
                    <a class="btn btn-sm btn-outline-primary" href="/view.php?id=<?php echo (int)$r['id']; ?>">
                        View
                    </a>
                    </td>

                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>

  <p class="text-muted small mt-3 mb-0">
    Showing up to 200 latest records.
  </p>

</div>

<script src="/assets/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
