<!DOCTYPE html>
<html>
<head>
    <title>Account Creation Page</title>
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

        .signup-container {
            background: #ffffff;
            width: 380px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.1);
            text-align: center;
            animation: fadeIn 0.6s ease-in-out;
        }

        p {
            font-size: 18px;
            margin-bottom: 20px;
            color: #444;
        }

        label {
            display: block;
            text-align: left;
            margin-top: 15px;
            font-weight: bold;
            font-size: 14px;
            color: #555;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #a3cef1;
            outline: none;
        }

        button {
            margin-top: 25px;
            width: 100%;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            border-radius: 8px;
            background-color: #a3cef1;
            color: #1d3557;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #86bce5;
        }

        .go-home-btn {
            margin-top: 20px;
            width: 100%;
            background-color: #ffb3c6;
            color: #5a1f2b;
            box-shadow: 0 4px 6px rgba(255, 179, 198, 0.4);
        }

        .go-home-btn:hover {
            background-color: #ff99af;
            box-shadow: 0 6px 8px rgba(255, 153, 175, 0.6);
        }

        a {
            text-decoration: none;
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <p>Welcome New User 🌸<br>Please input your email and a password that is at least 10 characters long</p>
        <form action="signup_process.php" method="POST">
            <label for="username">Enter your Email:</label>
            <input type="text" name="username" placeholder="Your Email here" required>

            <label for="password">Enter your Password:</label>
            <input type="password" name="password" placeholder="Your Password here" minlength="10" required>

            <button type="submit">Sign Up</button>
        </form>

        <a href="signin.php"><button type="button" class="go-home-btn">Go Home</button></a>
    </div>
</body>
</html>
