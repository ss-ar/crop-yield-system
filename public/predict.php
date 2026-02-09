<?php
require_once __DIR__ . '/../app/helpers/Auth.php';
require_login();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Predict Yield | Crop Yield DSS</title>
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
        <li class="nav-item"><a class="nav-link active" href="/predict.php">Predict</a></li>
        <li class="nav-item"><a class="nav-link" href="/history.php">History</a></li>
        <li class="nav-item"><a class="nav-link" href="/logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-9">

      <div class="card shadow-sm border-0">
        <div class="card-body p-4">
          <h3 class="mb-1">Crop Yield Prediction</h3>
          <p class="text-muted mb-4">Enter farm and seasonal details to estimate expected yield.</p>

          <form method="post" action="/result.php">
            <div class="row g-3">

              <!-- Basic identifiers -->
              <div class="col-md-4">
                <label class="form-label">Crop Type</label>
                <select name="crop_type" class="form-select" required>
                  <option value="">Select crop</option>
                  <option value="maize">Maize</option>
                  <option value="beans">Beans</option>
                  <option value="rice">Rice</option>
                  <option value="cassava">Cassava</option>
                  <option value="matooke">Matooke</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Season</label>
                <input type="text" name="season" class="form-control" placeholder="e.g. 2026A / 1st Rains" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">District</label>
                <input type="text" name="district" class="form-control" value="Kawanda" required>
                <!-- <div class="form-text">For prototype, we focus on one district (editable).</div> -->
              </div>

              <hr class="my-2">

              <!-- Farm details -->
              <div class="col-md-4">
                <label class="form-label">Farm Size (acres)</label>
                <input type="number" step="0.01" min="0.01" name="farm_size_acres" class="form-control" placeholder="e.g. 2.5" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">Soil Type</label>
                <select name="soil_type" class="form-select" required>
                  <option value="">Select soil</option>
                  <option value="loam">Loam</option>
                  <option value="clay">Clay</option>
                  <option value="sandy">Sandy</option>
                  <option value="silt">Silt</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Seed Type</label>
                <select name="seed_type" class="form-select" required>
                  <option value="">Select seed</option>
                  <option value="local">Local</option>
                  <option value="improved">Improved</option>
                  <option value="hybrid">Hybrid</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <hr class="my-2">

              <!-- Weather / inputs -->
              <div class="col-md-4">
                <label class="form-label">Seasonal Rainfall (mm)</label>
                <input type="number" step="0.01" min="0" name="rainfall_mm" class="form-control" placeholder="e.g. 850" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">Average Temperature (°C)</label>
                <input type="number" step="0.01" min="0" name="avg_temp_c" class="form-control" placeholder="e.g. 24.5" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">Fertilizer Used (kg)</label>
                <input type="number" step="0.01" min="0" name="fertilizer_kg" class="form-control" value="0" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">Irrigation</label>
                <select name="irrigation" class="form-select" required>
                  <option value="no" selected>No</option>
                  <option value="yes">Yes</option>
                </select>
              </div>

              <div class="col-12 d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-success px-4">Predict Yield</button>
                <button type="reset" class="btn btn-outline-secondary">Clear</button>
              </div>

            </div>
          </form>
        </div>
      </div>

      <p class="text-muted small mt-3 mb-0">
        Tip: Use realistic values for rainfall, temperature and fertilizer for a better estimate.
      </p>

    </div>
  </div>
</div>

<script src="/assets/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
