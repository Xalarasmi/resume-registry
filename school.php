<?php
session_start();
require_once "pdo.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['name'])) {
    echo json_encode([]);
    return;
}

$stmt = $pdo->prepare('SELECT name FROM Institution WHERE name LIKE :prefix');
$stmt->execute(array(':prefix' => $_REQUEST['term']."%"));

$retval = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $retval[] = $row['name'];
}

echo json_encode($retval, JSON_PRETTY_PRINT);
