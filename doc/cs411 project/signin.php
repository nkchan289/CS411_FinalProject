<?php
session_start();

$servername = "34.71.99.108";
$dbusername = "root";
$dbpassword = "Kobeni_Higashiyama1234!";
$dbname = "socioeconomic_data";

$error_message = "";
$show_forgot_button = false;

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT Passwords, failed_attempts FROM userdatabase WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password, $failed_attempts);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $resetStmt = $conn->prepare("UPDATE userdatabase SET failed_attempts = 0 WHERE username = ?");
            $resetStmt->bind_param("s", $username);
            $resetStmt->execute();
            $resetStmt->close();

            $_SESSION['username'] = $username;
            header("Location: menu.php");
            exit();
        } else {
            $incStmt = $conn->prepare("UPDATE userdatabase SET failed_attempts = failed_attempts + 1 WHERE username = ?");
            $incStmt->bind_param("s", $username);
            $incStmt->execute();
            $incStmt->close();

            $error_message = "❌ Incorrect password!";

            if (($failed_attempts + 1) >= 3) {
                $show_forgot_button = true;
            }
        }
    } else {
        $error_message = "❌ Username not found!";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Sign In Page</title>
    <style>
        /* Reset and basics */
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #a8edea, #fed6e3);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            user-select: none;
            color: #444;
        }
        .container {
            background: #fff;
            border-radius: 20px;
            padding: 40px 45px;
            max-width: 420px;
            width: 90%;
            box-shadow: 0 15px 40px rgba(255, 105, 180, 0.3);
            text-align: center;
            animation: slideUpFade 0.7s ease forwards;
            border-top: 6px solid #ff69b4;
        }
        h2 {
            font-size: 2rem;
            color: #ff4081;
            margin-bottom: 25px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        label {
            display: block;
            text-align: left;
            margin-top: 18px;
            font-weight: 600;
            color: #333;
            font-size: 14.5px;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            margin-top: 6px;
            border-radius: 10px;
            border: 2px solid #ffb6c1;
            font-size: 16px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            font-weight: 500;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #ff4081;
            box-shadow: 0 0 8px 2px #ff408166;
        }
        button {
            margin-top: 30px;
            width: 100%;
            padding: 14px;
            font-size: 18px;
            font-weight: 700;
            background: #ff4081;
            color: white;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            box-shadow: 0 6px 15px rgba(255, 64, 129, 0.4);
            transition: background-color 0.3s ease, transform 0.2s ease;
            user-select: none;
        }
        button:hover {
            background-color: #e23370;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(226, 51, 112, 0.6);
        }
        button:active {
            transform: translateY(1px);
            box-shadow: 0 4px 12px rgba(226, 51, 112, 0.4);
        }
        .buttons-row {
            margin-top: 22px;
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }
        .forgot-btn,
        .signup-btn {
            flex: 1;
            background-color: #ff6f91;
            box-shadow: 0 5px 15px rgba(255, 111, 145, 0.4);
            border-radius: 30px;
            border: none;
            color: white;
            font-weight: 700;
            padding: 12px 0;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            user-select: none;
        }
        .forgot-btn:hover,
        .signup-btn:hover {
            background-color: #e05275;
            box-shadow: 0 8px 20px rgba(224, 82, 117, 0.6);
            transform: translateY(-2px);
        }
        .forgot-btn:active,
        .signup-btn:active {
            transform: translateY(1px);
            box-shadow: 0 4px 12px rgba(224, 82, 117, 0.4);
        }
        .error-message {
            color: #ff1744;
            font-weight: 700;
            margin-top: 18px;
            font-size: 14.5px;
            user-select: text;
        }
        a {
            text-decoration: none;
        }
        @keyframes slideUpFade {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome! Please sign in or create an account.</h2>

        <?php if (!empty($error_message)) : ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form action="signin.php" method="POST" autocomplete="off">
            <label for="username">Username:</label>
            <input type="text" name="username" placeholder="Your username here" required />

            <label for="password">Password:</label>
            <input type="password" name="password" placeholder="Your password here" required />

            <button type="submit">Sign In</button>
        </form>

        <div class="buttons-row">
            <?php if ($show_forgot_button) : ?>
                <a href="reset_password.php"><button type="button" class="forgot-btn">Forgot Password?</button></a>
            <?php endif; ?>
            <a href="signuppage.php"><button type="button" class="signup-btn">Sign Up</button></a>
        </div>
    </div>
</body>
</html>

