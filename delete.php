<?php
session_start();
require_once "pdo.php";
require_once "util.php";

if (!isset($_SESSION['user_id'])) {
    die("ACCESS DENIED");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
}

if (isset($_POST['cancel'])) {
    header("Location: index.php");
    return;
}

if (isset($_POST['delete']) && isset($_POST['profile_id'])) {
    $stmt = $pdo->prepare("DELETE FROM Profile
        WHERE profile_id = :pid AND user_id = :uid");
    $stmt->execute(array(
        ":pid" => $_POST['profile_id'],
        ":uid" => $_SESSION['user_id']
    ));

    $_SESSION['success'] = "Profile deleted";
    header("Location: index.php");
    return;
}

if (!isset($_GET['profile_id'])) {
    $_SESSION['error'] = "Missing profile_id";
    header("Location: index.php");
    return;
}

$stmt = $pdo->prepare("SELECT profile_id, first_name, last_name FROM Profile
    WHERE profile_id = :pid AND user_id = :uid");
$stmt->execute(array(
    ":pid" => $_GET['profile_id'],
    ":uid" => $_SESSION['user_id']
));

$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row === false) {
    $_SESSION['error'] = "Could not load profile";
    header("Location: index.php");
    return;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Resume Registry \| Delete Profile</title>
</head>
<body>
<h1>Delete Profile</h1>

<p>Confirm deleting <?= htmlentities($row['first_name'].' '.$row['last_name']) ?></p>

<form method="POST">
<input type="hidden" name="csrf_token"
    value="<?= htmlentities(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
<input type="hidden" name="profile_id" value="<?= htmlentities($row['profile_id']) ?>">
<input type="submit" name="delete" value="Delete">
<input type="submit" name="cancel" value="Cancel">
</form>
</body>
</html>
