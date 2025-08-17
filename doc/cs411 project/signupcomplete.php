<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Sign Up Complete</title>
    <style>
        /* Reset some default styles */
        * {
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            user-select: none;
            color: #4a4a4a;
        }

        .container {
            background: #fff;
            padding: 40px 50px;
            border-radius: 20px;
            box-shadow: 0 12px 25px rgba(253, 160, 133, 0.4);
            text-align: center;
            max-width: 400px;
            width: 90%;
            animation: fadeInUp 0.8s ease forwards;
        }

        h2 {
            font-size: 2.4rem;
            margin-bottom: 20px;
            color: #ff6f91;
            font-weight: 700;
            letter-spacing: 1.1px;
        }

        p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            color: #777;
            line-height: 1.5;
        }

        button {
            background: #ff6f91;
            color: white;
            border: none;
            padding: 14px 30px;
            font-size: 1.1rem;
            border-radius: 30px;
            cursor: pointer;
            box-shadow: 0 6px 15px rgba(255, 111, 145, 0.5);
            transition: background-color 0.3s ease, transform 0.2s ease;
            user-select: none;
        }

        button:hover {
            background: #ff497b;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 73, 123, 0.6);
        }

        button:active {
            transform: translateY(1px);
            box-shadow: 0 4px 12px rgba(255, 73, 123, 0.4);
        }

        a {
            text-decoration: none;
        }

        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(40px);
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
        <h2>Sign Up Complete!</h2>
        <p>Thank you for registering. Your account has been successfully created.</p>
        <a href="signin.php"><button type="button">Go to Sign In</button></a>
    </div>
</body>
</html>
