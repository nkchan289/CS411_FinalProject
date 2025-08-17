<?php
session_start();

$servername = "34.71.99.108";
$username = "root";
$password = "Kobeni_Higashiyama1234!";
$dbname = "socioeconomic_data";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$currentUser = $_SESSION['username'] ?? null;
if (!$currentUser) {
    die("You must be logged in to add favorites.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $locationCode = $_POST['zipcode'] ?? ''; // holds ZIP or FIPS code
    $locationType = $_POST['location_type'] ?? 'zipcode'; // 'zipcode' or 'fips'
    $favorite_type = $_POST['favorite_type'] ?? 'personal';

    if ($locationType === 'zipcode') {
        if (!preg_match('/^\d{5}$/', $locationCode)) {
            header("Location: results.php?msg=invalid");
            exit();
        }
    } elseif ($locationType === 'fips') {
        $locationCode = strtolower($locationCode);
        if (strpos($locationCode, 'c') === 0) {
            $locationCode = substr($locationCode, 1);
        }
        if (!preg_match('/^\d+$/', $locationCode)) {
            header("Location: results.php?msg=invalid");
            exit();
        }
    } else {
        header("Location: results.php?msg=invalid");
        exit();
    }

    function redirectWithMsg($locationType, $locationCode, $msg) {
        if ($locationType === 'zipcode') {
            header("Location: results.php?zipcode=$locationCode&msg=$msg");
        } else {
            header("Location: results.php?fips=$locationCode&msg=$msg");
        }
        exit();
    }

    if ($favorite_type === 'personal') {
        if ($locationType !== 'zipcode') {
            redirectWithMsg($locationType, $locationCode, 'invalid_personal');
        }

        $checkUser = $conn->prepare("SELECT * FROM favorites WHERE username = ?");
        $checkUser->bind_param("s", $currentUser);
        $checkUser->execute();
        $result = $checkUser->get_result();

        if ($result->num_rows == 0) {
            $insertUser = $conn->prepare("INSERT INTO favorites (username) VALUES (?)");
            $insertUser->bind_param("s", $currentUser);
            $insertUser->execute();
            $insertUser->close();
        }
        $checkUser->close();

        $checkZip = $conn->prepare("
            SELECT * FROM favorites 
            WHERE username = ? AND (
                favorite1 = ? OR favorite2 = ? OR favorite3 = ? OR favorite4 = ? OR favorite5 = ? OR
                favorite6 = ? OR favorite7 = ? OR favorite8 = ? OR favorite9 = ? OR favorite10 = ?
            )
        ");
        $checkZip->bind_param("sssssssssss", $currentUser, $locationCode, $locationCode, $locationCode, $locationCode, $locationCode, $locationCode, $locationCode, $locationCode, $locationCode, $locationCode);
        $checkZip->execute();
        $zipResult = $checkZip->get_result();

        if ($zipResult->num_rows > 0) {
            $checkZip->close();
            redirectWithMsg($locationType, $locationCode, 'already_added');
        }
        $checkZip->close();

        $favRowRes = $conn->query("SELECT * FROM favorites WHERE username = '" . $conn->real_escape_string($currentUser) . "'");
        $favRow = $favRowRes->fetch_assoc();

        for ($i = 1; $i <= 10; $i++) {
            if (empty($favRow["favorite$i"])) {
                $stmt = $conn->prepare("UPDATE favorites SET favorite$i = ? WHERE username = ?");
                $stmt->bind_param("ss", $locationCode, $currentUser);
                $stmt->execute();
                $stmt->close();

                redirectWithMsg($locationType, $locationCode, 'added');
            }
        }

        redirectWithMsg($locationType, $locationCode, 'full');
    }
    elseif ($favorite_type === 'group') {
        $groupStmt = $conn->prepare("SELECT group_id FROM group_memberships WHERE Username = ? LIMIT 1");
        $groupStmt->bind_param("s", $currentUser);
        $groupStmt->execute();
        $groupStmt->bind_result($group_id);
        $groupStmt->fetch();
        $groupStmt->close();

        if (!$group_id) {
            redirectWithMsg($locationType, $locationCode, 'no_group');
        }

        if ($locationType === 'zipcode') {
            $checkFavorite = $conn->prepare("SELECT * FROM group_favorites WHERE group_id = ? AND zip_code = ?");
            $checkFavorite->bind_param("is", $group_id, $locationCode);
        } else {
            $checkFavorite = $conn->prepare("SELECT * FROM group_favorites WHERE group_id = ? AND fips_code = ?");
            $checkFavorite->bind_param("is", $group_id, $locationCode);
        }
        $checkFavorite->execute();
        $checkFavoriteResult = $checkFavorite->get_result();

        if ($checkFavoriteResult->num_rows > 0) {
            $checkFavorite->close();
            redirectWithMsg($locationType, $locationCode, 'already_added');
        }
        $checkFavorite->close();

        $countFavG = $conn->prepare("SELECT COUNT(*) as cnt FROM group_favorites WHERE group_id = ?");
        $countFavG->bind_param("i", $group_id);
        $countFavG->execute();
        $countFavG->bind_result($favCount);
        $countFavG->fetch();
        $countFavG->close();

        if ($favCount >= 10) {
            redirectWithMsg($locationType, $locationCode, 'full');
        }

        if ($locationType === 'zipcode') {
            $insertGroup = $conn->prepare("INSERT INTO group_favorites (group_id, zip_code) VALUES (?, ?)");
            $insertGroup->bind_param("is", $group_id, $locationCode);
        } else {
            $insertGroup = $conn->prepare("INSERT INTO group_favorites (group_id, fips_code) VALUES (?, ?)");
            $insertGroup->bind_param("is", $group_id, $locationCode);
        }
        $insertGroup->execute();
        $insertGroup->close();

        redirectWithMsg($locationType, $locationCode, 'added');
    }
    else {
        redirectWithMsg($locationType, $locationCode, 'invalid');
    }
}

$conn->close();
?>
