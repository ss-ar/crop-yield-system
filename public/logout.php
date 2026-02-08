<?php
require_once __DIR__ . '/../app/helpers/Auth.php';
logout();
header("Location: /login.php");
exit;
