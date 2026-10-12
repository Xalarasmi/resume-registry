<?php
$config = require __DIR__ . '/config.php';

$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4",
    $config['username'],
    $config['password']
);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
