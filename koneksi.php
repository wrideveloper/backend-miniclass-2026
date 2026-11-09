<?php

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_absensi';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log('[Database] ' . $e->getMessage());

    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database gagal.']);
    exit;
}

// Helper agar fungsi handler bisa langsung mengakses PDO tanpa "global $pdo"
function db(): PDO
{
    global $pdo;
    return $pdo;
}
