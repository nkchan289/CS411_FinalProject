<?php
session_start();

$servername = "34.71.99.108";
$dbusername = "root";
$dbpassword = "Kobeni_Higashiyama1234!";
$dbname = "socioeconomic_data";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$currentUser = $_SESSION['username'] ?? null;
if (!$currentUser) {
    die("You must be logged in to remove favorites.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zip_code = $_POST['zip_code'] ?? '';

    if (empty($zip_code)) {
        header("Location: mygroup.php?msg=invalid");
        exit();
    }

    // Get user's group_id
    $groupStmt = $conn->prepare("SELECT group_id FROM group_memberships WHERE Username = ? LIMIT 1");
    $groupStmt->bind_param("s", $currentUser);
    $groupStmt->execute();
    $groupStmt->bind_result($group_id);
    $groupStmt->fetch();
    $groupStmt->close();

    if (!$group_id) {
        die("You are not part of any group.");
    }

    // Delete favorite from group_favorites table
    $deleteStmt = $conn->prepare("DELETE FROM group_favorites WHERE group_id = ? AND zip_code = ?");
    $deleteStmt->bind_param("is", $group_id, $zip_code);
    $deleteStmt->execute();

    if ($deleteStmt->affected_rows > 0) {
        $deleteStmt->close();
        $conn->close();
        header("Location: mygroup.php?msg=removed");
        exit();
    } else {
        $deleteStmt->close();
        $conn->close();
        header("Location: mygroup.php?msg=not_found");
        exit();
    }
}

$conn->close();
header("Location: mygroup.php");
exit();
