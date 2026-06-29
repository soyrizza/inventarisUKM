<?php
// ============================================================
// Koneksi Database — PDO + Prepared Statement
// Jika gagal, exception dilempar ke bootstrap.php
// ============================================================

 $host     = 'localhost';
 $dbname   = 'db_inventaris_ukm';
 $username = 'root';
 $password = 'root';

 $pdo = new PDO(
    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
    $username,
    $password,
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
    ]
);