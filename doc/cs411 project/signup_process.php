<?php

$servername = "34.71.99.108";
$dbusername = "root";    
$dbpassword = "Kobeni_Higashiyama1234!";
$dbname = "socioeconomic_data"; 

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check_stmt = $conn->prepare("SELECT username FROM userdatabase WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Sign Up Error</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background: linear-gradient(to right, #e6f0ff, #f5e6ff, #ffe6f0);
                    margin: 0;
                    padding: 0;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                }
                .error-container {
                    background: #ffffff;
                    padding: 30px;
                    border-radius: 12px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                    text-align: center;
                    width: 350px;
                    animation: fadeIn 0.6s ease-in-out;
                }
                .error-container h2 {
                    color: #dc3545;
                    margin-bottom: 15px;
                }
                .error-container p {
                    font-size: 1.1rem;
                    color: #555;
                    margin-bottom: 20px;
                }
                .back-btn {
                    background-color: #a3cef1;
                    color: #1d3557;
                    border: none;
                    padding: 10px 20px;
                    font-size: 1rem;
                    font-weight: 600;
                    border-radius: 6px;
                    cursor: pointer;
                    transition: background-color 0.3s ease;
                }
                .back-btn:hover {
                    background-color: #86bce5;
                }
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(-10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h2>❌ Oops!</h2>
                <p>That username is already taken.<br>Please choose a different one.</p>
                <button class="back-btn" onclick="window.location.href='signuppage.php'">Back to Sign Up</button>
            </div>
        </body>
        </html>
        <?php
    } else {
        $stmt = $conn->prepare("INSERT INTO userdatabase (username, passwords) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            $fav_stmt = $conn->prepare("INSERT INTO favorites (Username) VALUES (?)");
            $fav_stmt->bind_param("s", $username);
            $fav_stmt->execute();
            $fav_stmt->close();
            header("Location: signupcomplete.php");
            exit();
        } else {
            echo "❌ Error: Could not create account.";
        }

        $stmt->close();
    }

    $check_stmt->close();
}

$conn->close();
?>
