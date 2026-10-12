<?php
function validateProfile() {
    if (strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 ||
        strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1 ||
        strlen($_POST['summary']) < 1) {
        return "All fields are required";
    }

    if (strpos($_POST['email'], '@') === false) {
        return "Email address must contain @";
    }

    return true;
}

function validatePos() {
    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['year'.$i])) continue;
        if (!isset($_POST['desc'.$i])) continue;

        if (strlen($_POST['year'.$i]) < 1 || strlen($_POST['desc'.$i]) < 1) {
            return "All fields are required";
        }

        if (!is_numeric($_POST['year'.$i])) {
            return "Year must be numeric";
        }
    }
    return true;
}

function validateEdu() {
    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['edu_year'.$i])) continue;
        if (!isset($_POST['edu_school'.$i])) continue;

        if (strlen($_POST['edu_year'.$i]) < 1 || strlen($_POST['edu_school'.$i]) < 1) {
            return "All fields are required";
        }

        if (!is_numeric($_POST['edu_year'.$i])) {
            return "Year must be numeric";
        }
    }
    return true;
}

function insertPositions($pdo, $profile_id) {
    $rank = 1;
    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['year'.$i])) continue;
        if (!isset($_POST['desc'.$i])) continue;

        $stmt = $pdo->prepare('INSERT INTO Position
            (profile_id, `rank`, `year`, description)
            VALUES (:pid, :rank, :year, :desc)');

        $stmt->execute(array(
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $_POST['year'.$i],
            ':desc' => $_POST['desc'.$i]
        ));
        $rank++;
    }
}

function insertEducations($pdo, $profile_id) {
    $rank = 1;
    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['edu_year'.$i])) continue;
        if (!isset($_POST['edu_school'.$i])) continue;

        $year = $_POST['edu_year'.$i];
        $school = $_POST['edu_school'.$i];

        $stmt = $pdo->prepare('SELECT institution_id FROM Institution WHERE name = :name');
        $stmt->execute(array(':name' => $school));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            $stmt = $pdo->prepare('INSERT INTO Institution (name) VALUES (:name)');
            $stmt->execute(array(':name' => $school));
            $institution_id = $pdo->lastInsertId();
        } else {
            $institution_id = $row['institution_id'];
        }

        $stmt = $pdo->prepare('INSERT INTO Education
            (profile_id, institution_id, `rank`, `year`)
            VALUES (:pid, :iid, :rank, :year)');

        $stmt->execute(array(
            ':pid' => $profile_id,
            ':iid' => $institution_id,
            ':rank' => $rank,
            ':year' => $year
        ));
        $rank++;
    }
}

function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken() {
    if (
        !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
        !is_string($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        http_response_code(403);
        exit("Invalid CSRF token");
    }
}
