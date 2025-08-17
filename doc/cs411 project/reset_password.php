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

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username']) && !isset($_POST['new_password'])) {
        $username = $_POST['username'];

        $stmt = $conn->prepare("SELECT username FROM userdatabase WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $_SESSION['reset_username'] = $username;
        } else {
            $error_message = "Username not found.";
        }

        $stmt->close();

    } elseif (isset($_POST['new_password'])) {
        if (!isset($_SESSION['reset_username'])) {
            $error_message = "Session expired. Please try again.";
        } else {
            $username = $_SESSION['reset_username'];
            $new_password = $_POST['new_password'];

            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE userdatabase SET Passwords = ? WHERE username = ?");
            $stmt->bind_param("ss", $hashed_password, $username);

            if ($stmt->execute()) {
                $success_message = "✅ Password updated successfully! You can now <a href='signin.php'>sign in</a>.";
                unset($_SESSION['reset_username']);
            } else {
                $error_message = "Failed to update password. Please try again.";
            }

            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        /* Background */
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #a1c4fd, #c2e9fb);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Container */
        .reset-container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 380px;
            text-align: center;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        label {
            font-weight: 600;
            display: block;
            margin: 10px 0 5px;
            text-align: left;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1.5px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 6px #4a90e255;
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

        p {
            font-size: 0.95rem;
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

<div class="reset-container">
    <h2>🔑 Reset Your Password</h2>

    <?php if ($error_message) { ?>
        <p class="error"><?php echo $error_message; ?></p>
    <?php } ?>

    <?php if ($success_message) { ?>
        <p class="success"><?php echo $success_message; ?></p>
    <?php } ?>

    <?php if (!isset($_SESSION['reset_username'])) { ?>
        <!-- Step 1: Username input -->
        <form action="reset_password.php" method="POST">
            <label for="username">Enter your username:</label>
            <input type="text" name="username" placeholder="Your username..." required>
            <button type="submit">Submit</button>
        </form>
    <?php } else { ?>
        <!-- Step 2: New password input -->
        <form action="reset_password.php" method="POST">
            <label for="new_password">Enter new password:</label>
            <input type="password" name="new_password" placeholder="New password..." required>
            <button type="submit">Update Password</button>
        </form>
    <?php } ?>
</div>

</body>
</html>
