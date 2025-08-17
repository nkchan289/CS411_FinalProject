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
$slot = $_GET['slot'] ?? null;

if ($username && $slot) {
    $conn->query("UPDATE favorites SET $slot = NULL WHERE username = '".$conn->real_escape_string($username)."'");

    $result = $conn->query("SELECT * FROM favorites WHERE username = '".$conn->real_escape_string($username)."'");
    $row = $result->fetch_assoc();

    $favorites = [];
    for ($i = 1; $i <= 10; $i++) {
        if (!empty($row["favorite$i"])) {
            $favorites[] = $row["favorite$i"];
        }
    }

    $conn->query("UPDATE favorites SET 
        favorite1=NULL, favorite2=NULL, favorite3=NULL, favorite4=NULL, favorite5=NULL,
        favorite6=NULL, favorite7=NULL, favorite8=NULL, favorite9=NULL, favorite10=NULL
        WHERE username='".$conn->real_escape_string($username)."'");

    $i = 1;
    foreach ($favorites as $zip) {
        $conn->query("UPDATE favorites SET favorite$i='".$conn->real_escape_string($zip)."' WHERE username='".$conn->real_escape_string($username)."'");
        $i++;
    }
}

header("Location: favorites.php");
exit();
