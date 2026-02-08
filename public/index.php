<?php
require_once __DIR__ . '/../app/helpers/Auth.php';

if (is_logged_in()) {
    header("Location: /predict.php");
    exit;
}

header("Location: /login.php");
exit;
