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

if (!isset($_REQUEST['profile_id'])) {
    $_SESSION['error'] = "Missing profile_id";
    header("Location: index.php");
    return;
}

if (isset($_POST['first_name']) && isset($_POST['last_name']) &&
    isset($_POST['email']) && isset($_POST['headline']) &&
    isset($_POST['summary']) && isset($_POST['profile_id'])) {

    $msg = validateProfile();
    if ($msg !== true) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=".$_POST['profile_id']);
        return;
    }

    $msg = validatePos();
    if ($msg !== true) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=".$_POST['profile_id']);
        return;
    }

    $msg = validateEdu();
    if ($msg !== true) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=".$_POST['profile_id']);
        return;
    }


    try {
        $pdo->beginTransaction();

        // Check that the profile belongs to the logged-in user
        $stmt = $pdo->prepare(
            'SELECT profile_id FROM Profile
             WHERE profile_id = :pid AND user_id = :uid
             FOR UPDATE'
        );

        $stmt->execute([
            ':pid' => $_POST['profile_id'],
            ':uid' => $_SESSION['user_id']
        ]);

        if ($stmt->fetch(PDO::FETCH_ASSOC) === false) {
            $pdo->rollBack();
            $_SESSION['error'] = "Could not update profile";
            header("Location: index.php");
            return;
        }

        // Update profile information
        $stmt = $pdo->prepare(
            'UPDATE Profile SET
             first_name = :fn, last_name = :ln,
             email = :em, headline = :he, summary = :su
             WHERE profile_id = :pid AND user_id = :uid'
        );

        $stmt->execute([
            ':fn' => $_POST['first_name'],
            ':ln' => $_POST['last_name'],
            ':em' => $_POST['email'],
            ':he' => $_POST['headline'],
            ':su' => $_POST['summary'],
            ':pid' => $_POST['profile_id'],
            ':uid' => $_SESSION['user_id']
        ]);

        // Replace old positions and education
        $stmt = $pdo->prepare(
            'DELETE FROM Position WHERE profile_id = :pid'
        );
        $stmt->execute([':pid' => $_POST['profile_id']]);

        $stmt = $pdo->prepare(
            'DELETE FROM Education WHERE profile_id = :pid'
        );
        $stmt->execute([':pid' => $_POST['profile_id']]);

        insertPositions($pdo, $_POST['profile_id']);
        insertEducations($pdo, $_POST['profile_id']);

        // Save all changes together
        $pdo->commit();

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log($e->getMessage());

        $_SESSION['error'] = "Could not save profile changes";
        header("Location: index.php");
        return;
    }

    $_SESSION['success'] = "Profile updated";
    header("Location: index.php");
    return;
}

$stmt = $pdo->prepare("SELECT * FROM Profile
    WHERE profile_id = :pid AND user_id = :uid");
$stmt->execute(array(
    ":pid" => $_REQUEST['profile_id'],
    ":uid" => $_SESSION['user_id']
));

$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if ($profile === false) {
    $_SESSION['error'] = "Could not load profile";
    header("Location: index.php");
    return;
}

$stmt = $pdo->prepare("SELECT * FROM Position WHERE profile_id = :pid ORDER BY `rank`");
$stmt->execute(array(":pid" => $_REQUEST['profile_id']));
$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT Education.`year`, Institution.name
    FROM Education
    JOIN Institution ON Education.institution_id = Institution.institution_id
    WHERE Education.profile_id = :pid
    ORDER BY Education.`rank`");
$stmt->execute(array(":pid" => $_REQUEST['profile_id']));
$educations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Resume Registry \| Edit Profile</title>
<?php require_once "head.php"; ?>
</head>
<body>
<div class="container">
<h1>Editing Profile</h1>

<?php
if (isset($_SESSION['error'])) {
    echo '<p style="color:red">'.htmlentities($_SESSION['error'])."</p>\n";
    unset($_SESSION['error']);
}
?>

<form method="POST">
<input type="hidden" name="csrf_token"
    value="<?= htmlentities(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
<p>First Name:
<input type="text" name="first_name" size="60" value="<?= htmlentities($profile['first_name']) ?>"></p>
<p>Last Name:
<input type="text" name="last_name" size="60" value="<?= htmlentities($profile['last_name']) ?>"></p>
<p>Email:
<input type="text" name="email" size="30" value="<?= htmlentities($profile['email']) ?>"></p>
<p>Headline:<br>
<input type="text" name="headline" size="80" value="<?= htmlentities($profile['headline']) ?>"></p>
<p>Summary:<br>
<textarea name="summary" rows="8" cols="80"><?= htmlentities($profile['summary']) ?></textarea></p>

<p>Education: <input type="button" id="addEdu" value="+"></p>
<div id="edu_fields">
<?php
$countEdu = 0;
foreach ($educations as $edu) {
    $countEdu++;
    echo '<div id="edu'.$countEdu.'">';
    echo '<p>Year: <input type="text" name="edu_year'.$countEdu.'" value="'.htmlentities($edu['year']).'"> ';
    echo '<input type="button" value="-" onclick="$(\'#edu'.$countEdu.'\').remove(); return false;"><br>';
    echo 'School: <input type="text" size="80" name="edu_school'.$countEdu.'" class="school" value="'.htmlentities($edu['name']).'">';
    echo "</p></div>\n";
}
?>
</div>

<p>Position: <input type="button" id="addPos" value="+"></p>
<div id="position_fields">
<?php
$countPos = 0;
foreach ($positions as $pos) {
    $countPos++;
    echo '<div id="position'.$countPos.'">';
    echo '<p>Year: <input type="text" name="year'.$countPos.'" value="'.htmlentities($pos['year']).'"> ';
    echo '<input type="button" value="-" onclick="$(\'#position'.$countPos.'\').remove(); return false;"></p>';
    echo '<textarea name="desc'.$countPos.'" rows="8" cols="80">'.htmlentities($pos['description']).'</textarea>';
    echo "</div>\n";
}
?>
</div>

<input type="hidden" name="profile_id" value="<?= htmlentities($profile['profile_id']) ?>">

<p>
<input type="submit" value="Save">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>
</div>

<script>
countPos = <?= $countPos ?>;
countEdu = <?= $countEdu ?>;

$(document).ready(function() {
    $('.school').autocomplete({ source: "school.php" });

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
