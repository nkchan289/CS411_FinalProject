<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: signin.php");
    exit();
}

$servername = "34.71.99.108";
$dbusername = "root";
$dbpassword = "Kobeni_Higashiyama1234!";
$dbname = "socioeconomic_data";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $group_name = trim($_POST['group_name'] ?? '');
    $group_password = trim($_POST['group_password'] ?? '');
    $username = $_SESSION['username'];

    if (empty($group_name) || empty($group_password)) {
        $error_message = "Please enter both group name and password.";
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM user_groups WHERE group_name = ?");
        $stmt->bind_param("s", $group_name);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $error_message = "Group name already exists. Please choose another.";
        } else {
            $hashed_password = password_hash($group_password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO user_groups (group_name, group_password) VALUES (?, ?)");
            $stmt->bind_param("ss", $group_name, $hashed_password);

            if ($stmt->execute()) {
                $group_id = $stmt->insert_id;
                $stmt->close();

                $stmt2 = $conn->prepare("INSERT INTO group_memberships (Username, group_id) VALUES (?, ?)");
                $stmt2->bind_param("si", $username, $group_id);
                $stmt2->execute();
                $stmt2->close();

                $success_message = "Group created successfully! You have been added as a member.";
            } else {
                $error_message = "Failed to create group. Please try again.";
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Group</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0; padding: 0;
            background: linear-gradient(135deg, #a1c4fd, #c2e9fb);
            height: 100vh;
            display: flex; align-items: center; justify-content: center;
        }
        .container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.15);
            width: 100%; max-width: 380px;
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        p.error {
            color: #d93025;
            font-weight: bold;
            margin-bottom: 10px;
        }
        p.success {
            color: #1a7f2e;
            font-weight: bold;
            margin-bottom: 10px;
        }
        input[type=text], input[type=password] {
            width: 100%;
            padding: 12px;
            margin: 8px 0 20px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        button {
            background-color: #4a90e2;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #357ab8;
        }
        a {
            color: #4a90e2;
            text-decoration: none;
            font-weight: 600;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Create a New Group</h2>

    <?php if ($error_message): ?>
        <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
        <p><a href="menu.php">Back to Menu</a></p>
    <?php else: ?>
        <form method="post" action="">
            <input type="text" name="group_name" placeholder="Group Name" required>
            <input type="password" name="group_password" placeholder="Group Password" required>
            <button type="submit">Create Group</button>
        </form>
        <p><a href="menu.php">Cancel</a></p>
    <?php endif; ?>
</div>

</body>
</html>
