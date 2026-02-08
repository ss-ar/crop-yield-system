<?php


declare(strict_types=1);

$DB_HOST = '10.0.0.4';
$DB_NAME = 'crop_yield_dss';
$DB_USER = 'cropuser';
$DB_PASS = 'StrongPass123!';

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die("Database connection failed.");
}
