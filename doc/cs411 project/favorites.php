<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: signin.php");
    exit();
}

$username = $_SESSION['username'];

$servername = "34.71.99.108";
$dbusername = "root";
$dbpassword = "Kobeni_Higashiyama1234!";
$dbname = "socioeconomic_data";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT * FROM favorites WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $insert = $conn->prepare("INSERT INTO favorites (username) VALUES (?)");
    $insert->bind_param("s", $username);
    $insert->execute();
    $insert->close();

    $stmt->execute();
    $result = $stmt->get_result();
}

$favorites_row = $result->fetch_assoc();

$success = "";

if (isset($_GET['remove_slot'])) {
    $remove_slot = $_GET['remove_slot'];
    if (preg_match('/^favorite([1-9]|10)$/', $remove_slot)) {  
        $update = $conn->prepare("UPDATE favorites SET $remove_slot = NULL WHERE username = ?");
        $update->bind_param("s", $username);
        $update->execute();
        $update->close();

        $stmt->execute();
        $result = $stmt->get_result();
        $favorites_row = $result->fetch_assoc();

        $favorites = [];
        for ($i = 1; $i <= 10; $i++) {
            if (!empty($favorites_row["favorite$i"])) {
                $favorites[] = $favorites_row["favorite$i"];
            }
        }

        $reset = $conn->prepare("UPDATE favorites SET 
            favorite1=NULL, favorite2=NULL, favorite3=NULL, favorite4=NULL, favorite5=NULL,
            favorite6=NULL, favorite7=NULL, favorite8=NULL, favorite9=NULL, favorite10=NULL
            WHERE username = ?");
        $reset->bind_param("s", $username);
        $reset->execute();
        $reset->close();

        $slot = 1;
        foreach ($favorites as $zip) {
            $col = "favorite$slot";
            $update = $conn->prepare("UPDATE favorites SET $col = ? WHERE username = ?");
            $update->bind_param("ss", $zip, $username);
            $update->execute();
            $update->close();
            $slot++;
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $favorites_row = $result->fetch_assoc();

        $success = "Favorite removed.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Favorites</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('https://tse2.mm.bing.net/th/id/OIP.Ep_R-IbKdskJkFFJoSV1vwHaEK?rs=1&pid=ImgDetMain&o=7&rm=3');
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            position: relative;
            margin: 0;
            padding: 40px;
            color: #333;
            min-height: 100vh;
            z-index: 0;
        }
        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(255, 255, 255, 0.85);
            z-index: -1;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #222;
            text-shadow: 1px 1px 3px rgba(255,255,255,0.9);
        }

        p.success {
            color: #2a7a2a;
            font-weight: bold;
            text-align: center;
        }

        table {
            margin: 0 auto 30px auto;
            border-collapse: collapse;
            width: 90%;
            max-width: 700px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        th, td {
            padding: 12px 20px;
            text-align: center;
        }
        th {
            background-color: #007BFF;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
        }
        tr:nth-child(even) {
            background-color: #f7f9fc;
        }

        form {
            max-width: 400px;
            margin: 0 auto 40px auto;
            background: white;
            padding: 20px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
        }
        label {
            flex-basis: 100%;
            font-weight: 600;
            margin-bottom: 6px;
            text-align: center;
        }
        input[type="text"] {
            flex-grow: 1;
            padding: 10px 12px;
            font-size: 1rem;
            border: 1.5px solid #ccc;
            border-radius: 6px;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus {
            outline: none;
            border-color: #007BFF;
            box-shadow: 0 0 6px #007BFFaa;
        }
        button[type="submit"] {
            flex-basis: 100%;
            max-width: 220px;
            margin: 0 auto;
            padding: 12px 25px;
            background-color: #007BFF;
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button[type="submit"]:hover {
            background-color: #0056b3;
        }
        button.back-btn {
            display: block;
            margin: 0 auto 40px auto;
            background: white;
            border: 2px solid #007BFF;
            color: #007BFF;
            border-radius: 6px;
            padding: 10px 25px;
            font-weight: 600;
            cursor: pointer;
            max-width: 220px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        button.back-btn:hover {
            background-color: #007BFF;
            color: white;
        }
    </style>
</head>
<body>
<h1>Your Favorite ZIP Codes, <?php echo htmlspecialchars($username); ?></h1>

<?php if ($success): ?>
    <p class="success"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<table>
    <tr>
        <th>Favorite Slot</th>
        <th>ZIP Code</th>
        <th>Action</th>
    </tr>
    <?php
    $hasFavorites = false;
    for ($i = 1; $i <= 10; $i++):
        $fav = $favorites_row["favorite$i"];
        if (!empty($fav)):
            $hasFavorites = true;
            ?>
            <tr>
                <td>Favorite <?php echo $i; ?></td>
                <td><?php echo htmlspecialchars($fav); ?></td>
                <td><a href="?remove_slot=favorite<?php echo $i; ?>" onclick="return confirm('Remove this favorite?');">Remove</a></td>
            </tr>
        <?php
        endif;
    endfor;

    if (!$hasFavorites):
        ?>
        <tr><td colspan="3">No favorites added yet.</td></tr>
    <?php endif; ?>
</table>

<h2 style="text-align:center; color:#222;">Search for ZIP Code Data</h2>
<form method="get" action="search.php">
    <label for="zipcode">ZIP Code:</label>
    <input type="text" name="zipcode" id="zipcode" maxlength="5" pattern="\d{5}" required placeholder="Enter 5-digit ZIP code">
    <button type="submit">Search</button>
</form>

<button class="back-btn" onclick="window.location.href='menu.php'">Back to Menu</button>
</body>
</html>

