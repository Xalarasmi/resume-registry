<?php
session_start();
require_once "pdo.php";
require_once "util.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
}

if (isset($_POST['cancel'])) {
    header("Location: index.php");
    return;
}

if (isset($_POST['email']) && isset($_POST['pass'])) {
    if (strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1) {
        $_SESSION['error'] = "Email and password are required";
        header("Location: login.php");
        return;
    }

    if (strpos($_POST['email'], '@') === false) {
        $_SESSION['error'] = "Email must have an at-sign (@)";
        header("Location: login.php");
        return;
    }

    $stmt = $pdo->prepare('SELECT user_id, name, password FROM users WHERE email = :em');
    $stmt->execute([':em' => $_POST['email']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row !== false && password_verify($_POST['pass'], $row['password'])) {
        session_regenerate_id(true);
        $_SESSION['name'] = $row['name'];
        $_SESSION['user_id'] = $row['user_id'];
        header("Location: index.php");
        return;
    } else {
        $_SESSION['error'] = "Invalid email or password";
        header("Location: login.php");
        return;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Resume Registry \| Login</title>
<script>
function doValidate() {
    console.log('Validating...');
    try {
        email = document.getElementById('email').value;
        pw = document.getElementById('id_1723').value;

        if (email == null || email == "" || pw == null || pw == "") {
            alert("Both fields must be filled out");
            return false;
        }

        if (email.indexOf('@') == -1) {
            alert("Invalid email address");
            return false;
        }

        return true;
    } catch(e) {
        return false;
    }
}
</script>
</head>
<body>
<h1>Please Log In</h1>

<?php
if (isset($_SESSION['error'])) {
    echo '<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n";
    unset($_SESSION['error']);
}
?>

<form method="POST">
<input type="hidden" name="csrf_token"
    value="<?= htmlentities(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
<p>Email:
<input type="text" name="email" id="email"></p>
<p>Password:
<input type="password" name="pass" id="id_1723"></p>
<input type="submit" onclick="return doValidate();" value="Log In">
<input type="submit" name="cancel" value="Cancel">
</form>
</body>
</html>
