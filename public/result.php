<?php
require_once __DIR__ . '/../app/helpers/Auth.php';
require_login();

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/services/PredictionService.php';
require_once __DIR__ . '/../app/services/RecommendationService.php';

function post_str(string $key, int $maxLen = 120): string {
    $v = trim((string)($_POST[$key] ?? ''));
    if ($v === '') return '';
    if (mb_strlen($v) > $maxLen) $v = mb_substr($v, 0, $maxLen);
    return $v;
}

function post_float(string $key): ?float {
    if (!isset($_POST[$key])) return null;
    $v = trim((string)$_POST[$key]);
    if ($v === '' || !is_numeric($v)) return null;
    return (float)$v;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /predict.php");
    exit;
}

// Collect & validate
$input = [
    'crop_type'       => post_str('crop_type', 50),
    'season'          => post_str('season', 30),
    'district'        => post_str('district', 80),
    'soil_type'       => post_str('soil_type', 20),
    'seed_type'       => post_str('seed_type', 20),
    'irrigation'      => post_str('irrigation', 10),
    'farm_size_acres' => post_float('farm_size_acres'),
    'rainfall_mm'     => post_float('rainfall_mm'),
    'avg_temp_c'      => post_float('avg_temp_c'),
    'fertilizer_kg'   => post_float('fertilizer_kg') ?? 0.0,
];

$errors = [];
$requiredStrings = ['crop_type','season','district','soil_type','seed_type','irrigation'];
foreach ($requiredStrings as $k) {
    if ($input[$k] === '') $errors[] = "Missing field: {$k}";
}
$requiredNums = ['farm_size_acres','rainfall_mm','avg_temp_c'];
foreach ($requiredNums as $k) {
    if ($input[$k] === null) $errors[] = "Invalid number: {$k}";
}
if ($input['farm_size_acres'] !== null && $input['farm_size_acres'] <= 0) $errors[] = "Farm size must be greater than 0.";
if ($input['rainfall_mm'] !== null && $input['rainfall_mm'] < 0) $errors[] = "Rainfall cannot be negative.";
if ($input['fertilizer_kg'] !== null && $input['fertilizer_kg'] < 0) $errors[] = "Fertilizer cannot be negative.";

if ($errors) {
    // Simple error page (Bootstrap)
    ?>
    <!doctype html>
    <html lang="en">
    <head>
      <meta charset="utf-8">
      <title>Error | Crop Yield DSS</title>
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link href="/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-lg-7">
          <div class="card shadow-sm border-0">
            <div class="card-body p-4">
              <h4 class="mb-3">Please fix these issues</h4>
              <ul class="mb-4">
                <?php foreach ($errors as $e): ?>
                  <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
              </ul>
              <a class="btn btn-secondary" href="/predict.php">Back to form</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// Run prediction
$output = PredictionService::predict($input);

// Recommendations
$tips = RecommendationService::generate($input, $output);
$recommendationsText = implode(" ", $tips);

// Save to DB
$userId = current_user_id();

$stmt = $pdo->prepare("
    INSERT INTO predictions
    (user_id, crop_type, district, season, farm_size_acres, soil_type,
     rainfall_mm, avg_temp_c, fertilizer_kg, irrigation, seed_type,
     predicted_yield_tons, predicted_yield_tpa, risk_level, recommendations)
    VALUES
    (:user_id, :crop_type, :district, :season, :farm_size_acres, :soil_type,
     :rainfall_mm, :avg_temp_c, :fertilizer_kg, :irrigation, :seed_type,
     :predicted_yield_tons, :predicted_yield_tpa, :risk_level, :recommendations)
");

$stmt->execute([
    ':user_id'              => $userId,
    ':crop_type'            => $input['crop_type'],
    ':district'             => $input['district'],
    ':season'               => $input['season'],
    ':farm_size_acres'      => $input['farm_size_acres'],
    ':soil_type'            => $input['soil_type'],
    ':rainfall_mm'          => $input['rainfall_mm'],
    ':avg_temp_c'           => $input['avg_temp_c'],
    ':fertilizer_kg'        => $input['fertilizer_kg'],
    ':irrigation'           => $input['irrigation'],
    ':seed_type'            => $input['seed_type'],
    ':predicted_yield_tons' => $output['predicted_yield_tons'],
    ':predicted_yield_tpa'  => $output['predicted_yield_tpa'],
    ':risk_level'           => $output['risk_level'],
    ':recommendations'      => $recommendationsText,
]);


// Display result (Bootstrap)
$riskBadge = match ($output['risk_level']) {
    'low'    => 'success',
    'medium' => 'warning',
    'high'   => 'danger',
    default  => 'secondary',
};
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Result | Crop Yield DSS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="/predict.php">🌱 Crop Yield DSS</a>
    <div class="ms-auto d-flex gap-2">
      <a class="btn btn-outline-light btn-sm" href="/predict.php">New Prediction</a>
      <a class="btn btn-outline-light btn-sm" href="/history.php">History</a>
      <a class="btn btn-light btn-sm" href="/logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-9">

      <div class="card shadow-sm border-0 mb-3">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
              <h3 class="mb-1">Prediction Result</h3>
              <div class="text-muted">
                <?php echo htmlspecialchars(ucfirst($input['crop_type'])); ?> •
                <?php echo htmlspecialchars($input['district']); ?> •
                <?php echo htmlspecialchars($input['season']); ?>
              </div>
            </div>
            <span class="badge bg-<?php echo $riskBadge; ?> fs-6">
              Risk: <?php echo htmlspecialchars(ucfirst($output['risk_level'])); ?>
            </span>
          </div>

          <hr class="my-4">

          <div class="row g-3">
            <div class="col-md-6">
              <div class="p-3 bg-white border rounded-3">
                <div class="text-muted">Predicted Yield (tons/acre)</div>
                <div class="display-6 mb-0"><?php echo htmlspecialchars((string)$output['predicted_yield_tpa']); ?></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 bg-white border rounded-3">
                <div class="text-muted">Predicted Total Yield (tons)</div>
                <div class="display-6 mb-0"><?php echo htmlspecialchars((string)$output['predicted_yield_tons']); ?></div>
              </div>
            </div>
          </div>

          <h5 class="mt-4">Recommendations</h5>
          <ul class="mb-0">
            <?php foreach ($tips as $t): ?>
              <li><?php echo htmlspecialchars($t); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-body p-4">
          <h6 class="mb-3">Inputs Summary</h6>
          <div class="row g-2 small">
            <div class="col-md-4"><strong>Farm size:</strong> <?php echo htmlspecialchars((string)$input['farm_size_acres']); ?> acres</div>
            <div class="col-md-4"><strong>Soil:</strong> <?php echo htmlspecialchars($input['soil_type']); ?></div>
            <div class="col-md-4"><strong>Seed:</strong> <?php echo htmlspecialchars($input['seed_type']); ?></div>
            <div class="col-md-4"><strong>Rainfall:</strong> <?php echo htmlspecialchars((string)$input['rainfall_mm']); ?> mm</div>
            <div class="col-md-4"><strong>Avg temp:</strong> <?php echo htmlspecialchars((string)$input['avg_temp_c']); ?> °C</div>
            <div class="col-md-4"><strong>Fertilizer:</strong> <?php echo htmlspecialchars((string)$input['fertilizer_kg']); ?> kg</div>
            <div class="col-md-4"><strong>Irrigation:</strong> <?php echo htmlspecialchars($input['irrigation']); ?></div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="/assets/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
