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

$username = $_SESSION['username'] ?? null;

if (!$username) {
    header("Location: menu.php");
    exit();
}

$stmt = $conn->prepare("SELECT group_id FROM group_memberships WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($group_id);
$stmt->fetch();
$stmt->close();

if (!$group_id) {
    header("Location: menu.php");
    exit();
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("DELETE FROM group_memberships WHERE Username = ? AND group_id = ?");
    $stmt->bind_param("si", $username, $group_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) FROM group_memberships WHERE group_id = ?");
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $stmt->bind_result($member_count);
    $stmt->fetch();
    $stmt->close();

    if ($member_count == 0) {
        $stmt = $conn->prepare("DELETE FROM group_favorites WHERE group_id = ?");
        $stmt->bind_param("i", $group_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM user_groups WHERE group_id = ?");
        $stmt->bind_param("i", $group_id);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();

    $conn->close();

    header("Location: menu.php?msg=left_group_success");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $conn->close();
    die("Failed to leave group: " . $e->getMessage());
}
?>
