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

if (isset($_POST['first_name']) && isset($_POST['last_name']) &&
    isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) {

    $msg = validateProfile();
    if ($msg !== true) {
        $_SESSION['error'] = $msg;
        header("Location: add.php");
        return;
    }

    $msg = validatePos();
    if ($msg !== true) {
        $_SESSION['error'] = $msg;
        header("Location: add.php");
        return;
    }

    $msg = validateEdu();
    if ($msg !== true) {
        $_SESSION['error'] = $msg;
        header("Location: add.php");
        return;
    }


    try {
        $pdo->beginTransaction();

        // Create the profile
        $stmt = $pdo->prepare(
            'INSERT INTO Profile
            (user_id, first_name, last_name, email, headline, summary)
            VALUES (:uid, :fn, :ln, :em, :he, :su)'
        );

        $stmt->execute([
            ':uid' => $_SESSION['user_id'],
            ':fn' => $_POST['first_name'],
            ':ln' => $_POST['last_name'],
            ':em' => $_POST['email'],
            ':he' => $_POST['headline'],
            ':su' => $_POST['summary']
        ]);

        $profile_id = $pdo->lastInsertId();

        // Add positions and education
        insertPositions($pdo, $profile_id);
        insertEducations($pdo, $profile_id);

        // Save all changes together
        $pdo->commit();

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log($e->getMessage());

        $_SESSION['error'] = "Could not create profile";
        header("Location: add.php");
        exit;
    }


    $_SESSION['success'] = "Profile added";
    header("Location: index.php");
    return;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Resume Registry \| Add Profile</title>
<?php require_once "head.php"; ?>
</head>
<body>
<div class="container">
<h1>Add Profile</h1>

<?php
if (isset($_SESSION['error'])) {
    echo '<p style="color:red">'.htmlentities($_SESSION['error'])."</p>\n";
    unset($_SESSION['error']);
}
?>

<form method="POST">
<input type="hidden" name="csrf_token"
    value="<?= htmlentities(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
<p>First Name: <input type="text" name="first_name" size="60"></p>
<p>Last Name: <input type="text" name="last_name" size="60"></p>
<p>Email: <input type="text" name="email" size="30"></p>
<p>Headline:<br>
<input type="text" name="headline" size="80"></p>
<p>Summary:<br>
<textarea name="summary" rows="8" cols="80"></textarea></p>

<p>Education: <input type="button" id="addEdu" value="+"></p>
<div id="edu_fields"></div>

<p>Position: <input type="button" id="addPos" value="+"></p>
<div id="position_fields"></div>

<p>
<input type="submit" value="Add">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>
</div>

<script>
countPos = 0;
countEdu = 0;

$(document).ready(function() {
    $('#addPos').click(function(event) {
        event.preventDefault();

        if (countPos >= 9) {
            alert("Maximum of nine position entries exceeded");
            return;
        }

        countPos++;

        $('#position_fields').append(
            '<div id="position' + countPos + '"> \
            <p>Year: <input type="text" name="year' + countPos + '" value=""> \
            <input type="button" value="-" onclick="$(\'#position' + countPos + '\').remove(); return false;"></p> \
            <textarea name="desc' + countPos + '" rows="8" cols="80"></textarea> \
            </div>'
        );
    });

    $('#addEdu').click(function(event) {
        event.preventDefault();

        if (countEdu >= 9) {
            alert("Maximum of nine education entries exceeded");
            return;
        }

        countEdu++;

        $('#edu_fields').append(
            '<div id="edu' + countEdu + '"> \
            <p>Year: <input type="text" name="edu_year' + countEdu + '" value=""> \
            <input type="button" value="-" onclick="$(\'#edu' + countEdu + '\').remove(); return false;"><br> \
            School: <input type="text" size="80" name="edu_school' + countEdu + '" class="school" value=""> \
            </p></div>'
        );

        $('.school').autocomplete({ source: "school.php" });
    });
});
</script>
</body>
</html>
