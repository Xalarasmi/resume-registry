<?php
session_start();
require_once "pdo.php";
require_once "util.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Resume Registry</title>
</head>
<body>
<h1>Resume Registry</h1>

<?php
if (isset($_SESSION['success'])) {
    echo '<p style="color: green;">'.htmlentities($_SESSION['success'])."</p>\n";
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n";
    unset($_SESSION['error']);
}

if (!isset($_SESSION['name'])) {
    echo '<p><a href="login.php">Please log in</a></p>';
} else {
   echo '<form method="POST" action="logout.php">';
   echo '<input type="hidden" name="csrf_token" value="' .
   htmlentities(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
   echo '<button type="submit">Logout</button>';
   echo '</form>';
    echo '<p><a href="add.php">Add New Entry</a></p>';
}


$stmt = $pdo->prepare(
    "SELECT profile_id, user_id, first_name, last_name, headline
     FROM Profile
     WHERE user_id = :uid"
);

$stmt->execute([
    ':uid' => $_SESSION['user_id']
]);

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (count($rows) == 0) {
    echo "<p>No rows found</p>";
} else {
    echo '<table border="1">'."\n";
    echo "<tr><th>Name</th><th>Headline</th>";
    if (isset($_SESSION['name'])) echo "<th>Action</th>";
    echo "</tr>\n";

    foreach ($rows as $row) {
        echo "<tr><td>";
        echo '<a href="view.php?profile_id='.htmlentities($row['profile_id']).'">';
        echo htmlentities($row['first_name'].' '.$row['last_name']);
        echo "</a>";
        echo "</td><td>";
        echo htmlentities($row['headline']);
        echo "</td>";

        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']) {
            echo "<td>";
            echo '<a href="edit.php?profile_id='.htmlentities($row['profile_id']).'">Edit</a> / ';
            echo '<a href="delete.php?profile_id='.htmlentities($row['profile_id']).'">Delete</a>';
            echo "</td>";
        } elseif (isset($_SESSION['name'])) {
            echo "<td></td>";
        }

        echo "</tr>\n";
    }

    echo "</table>\n";
}
?>
</body>
</html>
