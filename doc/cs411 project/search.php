<?php
if (isset($_GET['zipcode']) && preg_match('/^\d{5}$/', $_GET['zipcode'])) {
    header("Location: results.php?zipcode=" . urlencode($_GET['zipcode']));
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Zipcode Lookup</title>
  <style>
    @keyframes fadeSlideUp {
      0% {
        opacity: 0;
        transform: translateY(20px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes buttonBounce {
      0%, 100% {
        transform: translateY(0);
      }
      50% {
        transform: translateY(-5px);
      }
    }

    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #b22234 0%, #3c3b6e 100%);
      color: white;
      height: 100vh;
      margin: 0;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 20px;
      animation: fadeSlideUp 1s ease forwards;
    }
    h1 {
      font-size: 2.5rem;
      margin-bottom: 30px;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
      animation: fadeSlideUp 1.2s ease forwards;
    }
    form {
      background-color: white;
      border-radius: 10px;
      padding: 25px 30px;
      box-shadow: 0 6px 12px rgba(0,0,0,0.2);
      display: flex;
      gap: 15px;
      align-items: center;
      max-width: 400px;
      width: 100%;
      animation: fadeSlideUp 1.4s ease forwards;
    }
    input[type="text"] {
      flex-grow: 1;
      padding: 12px 15px;
      border: 2px solid #3c3b6e;
      border-radius: 8px;
      font-size: 1.1rem;
      outline: none;
      transition: border-color 0.3s ease;
    }
    input[type="text"]:focus {
      border-color: #b22234;
      box-shadow: 0 0 8px #b22234;
    }
    button[type="submit"] {
      background-color: #b22234;
      color: white;
      border: none;
      padding: 12px 25px;
      font-size: 1.1rem;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
      box-shadow: 0 4px 6px rgba(178, 34, 52, 0.5);
      transition: background-color 0.3s ease;
      animation: fadeSlideUp 1.6s ease forwards;
    }
    button[type="submit"]:hover {
      background-color: #7f1c26;
      animation: buttonBounce 0.6s ease;
    }
    a button {
      margin-top: 25px;
      background-color: #3c3b6e;
      padding: 12px 30px;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: bold;
      border: none;
      color: white;
      cursor: pointer;
      box-shadow: 0 4px 6px rgba(60,59,110,0.6);
      transition: background-color 0.3s ease;
      animation: fadeSlideUp 1.8s ease forwards;
    }
    a button:hover {
      background-color: #26254a;
      animation: buttonBounce 0.6s ease;
    }
  </style>

  <script>
    function validateForm(event) {
      const zipcodeInput = document.forms["zipcodeForm"]["zipcode"].value.trim();
      if (zipcodeInput === "") {
        event.preventDefault();
        alert("Please input a ZIP code.");
        return false;
      }
      return true;
    }
  </script>
</head>
<body>
  <h1>Enter ZIP Code</h1>
  <form name="zipcodeForm" action="results.php" method="get" novalidate onsubmit="return validateForm(event)">
    <input type="text" name="zipcode" required pattern="\d{5}" maxlength="5" placeholder="Enter 5-digit ZIP Code" title="Please enter a 5-digit ZIP code">
    <button type="submit">Search</button>
  </form>

  <a href="menu.php"><button type="button">Back To Menu</button></a>
</body>
</html>
