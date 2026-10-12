<?php
session_start();
require_once "pdo.php";


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['profile_id'])) {
    $_SESSION['error'] = "Missing profile_id";
    header("Location: index.php");
    return;
}


$stmt = $pdo->prepare(
    "SELECT * FROM Profile
     WHERE profile_id = :pid AND user_id = :uid"
);

$stmt->execute([
    ':pid' => $_GET['profile_id'],
    ':uid' => $_SESSION['user_id']
]);

$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if ($profile === false) {
    $_SESSION['error'] = "Bad value for profile_id";
    header("Location: index.php");
    return;
}

$stmt = $pdo->prepare("SELECT * FROM Position WHERE profile_id = :pid ORDER BY `rank`");
$stmt->execute(array(":pid" => $_GET['profile_id']));
$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT Education.`year`, Institution.name
    FROM Education
    JOIN Institution ON Education.institution_id = Institution.institution_id
    WHERE Education.profile_id = :pid
    ORDER BY Education.`rank`");
$stmt->execute(array(":pid" => $_GET['profile_id']));
$educations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Resume Registry \| View Profile</title>
<?php require_once "head.php"; ?>
</head>
<body>
<div class="container">
<h1>Profile information</h1>

<p>First Name: <?= htmlentities($profile['first_name']) ?></p>
<p>Last Name: <?= htmlentities($profile['last_name']) ?></p>
<p>Email: <?= htmlentities($profile['email']) ?></p>
<p>Headline:<br><?= htmlentities($profile['headline']) ?></p>
<p>Summary:<br><?= htmlentities($profile['summary']) ?></p>

<h2>Education</h2>
<ul>
<?php
foreach ($educations as $edu) {
    echo "<li>";
    echo htmlentities($edu['year']) . ": ";
    echo htmlentities($edu['name']);
    echo "</li>\n";
}
?>
</ul>

<h2>Positions</h2>
<ul>
<?php
foreach ($positions as $pos) {
    echo "<li>";
    echo htmlentities($pos['year']) . ": ";
    echo htmlentities($pos['description']);
    echo "</li>\n";
}
?>
</ul>

<p><a href="index.php">Done</a></p>
</div>
</body>
</html>
