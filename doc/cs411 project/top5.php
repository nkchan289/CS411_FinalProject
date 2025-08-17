<?php
$servername = "34.71.99.108";
$username = "root";
$password = "Kobeni_Higashiyama1234!";
$dbname = "socioeconomic_data";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$state = $_GET['state'] ?? '';
$attribute = $_GET['attribute'] ?? '';

$result = null;

if (!empty($state) && !empty($attribute)) {
    $stmt = $conn->prepare("CALL GetTop5CountiesByAttribute(?, ?)");
    $stmt->bind_param("ss", $state, $attribute);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Top 5 Counties</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/e/ea/American_flag.jpg/960px-American_flag.jpg');
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            margin: 0;
            padding: 30px;
            color: #333;
            position: relative;
            z-index: 0;
        }
        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255, 255, 255, 0.85);
            z-index: -1;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }
        form {
            max-width: 450px;
            margin: 0 auto 30px auto;
            background: white;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }
        label {
            flex-basis: 100%;
            font-weight: 600;
            margin-bottom: 5px;
        }
        input[type="text"],
        select {
            flex-grow: 1;
            padding: 10px 12px;
            border: 1.5px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus,
        select:focus {
            outline: none;
            border-color: #007BFF;
            box-shadow: 0 0 6px #007BFFaa;
        }
        button[type="submit"] {
            background-color: #007BFF;
            border: none;
            color: white;
            padding: 12px 25px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            flex-basis: 100%;
            max-width: 200px;
            margin: 0 auto;
            display: block;
        }
        button[type="submit"]:hover {
            background-color: #0056b3;
        }
        .back-btn {
            display: block;
            width: max-content;
            margin: 0 auto 30px auto;
            padding: 10px 20px;
            font-weight: 600;
            border-radius: 6px;
            border: 2px solid #007BFF;
            background: white;
            color: #007BFF;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
            text-align: center;
            text-decoration: none;
        }
        .back-btn:hover {
            background-color: #007BFF;
            color: white;
        }
        table {
            border-collapse: collapse;
            width: 80%;
            max-width: 700px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 14px 20px;
            text-align: center;
        }
        th {
            background-color: #007BFF;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        p.no-data {
            text-align: center;
            font-style: italic;
            color: #555;
            margin-top: 40px;
            font-size: 1.1rem;
        }
        @media (max-width: 600px) {
            form {
                padding: 15px;
            }
            input[type="text"], select, button[type="submit"] {
                font-size: 0.9rem;
            }
            table {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <h1>Find Top 5 Counties</h1>
    <form method="get" novalidate>
        <label for="state">Choose a State:</label>
        <input type="text" id="state" name="state" placeholder="e.g. California" required>

        <label for="attribute">Choose an Attribute:</label>
        <select id="attribute" name="attribute" required>
            <option value="population">Population</option>
            <option value="gdp">GDP</option>
            <option value="education">Education</option>
            <option value="labor_force">Labor Force</option>
        </select>

        <button type="submit">Show Top 5</button>
    </form>

    <a href="menu.php" class="back-btn">← Back To Menu</a>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php
            $valueKey = '';
            if ($attribute === "population") {
                $valueKey = "population";
            } elseif ($attribute === "gdp") {
                $valueKey = "gdp_change";
            } elseif ($attribute === "education") {
                $valueKey = "education_metric";
            } elseif ($attribute === "labor_force") {
                $valueKey = "labor_force";
            }
        ?>
        <table>
            <tr>
                <th>County Name</th>
                <th>State</th>
                <th>
                    <?php
                    if ($attribute === "population") echo "Population (2023)";
                    elseif ($attribute === "gdp") echo "Percent Change (2023)";
                    elseif ($attribute === "education") echo "Bachelor's Degree or Higher (%)";
                    elseif ($attribute === "labor_force") echo "Civilian Labor Force (2023)";
                    else echo "Value";
                    ?>
                </th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['County_Name']); ?></td>
                    <td><?php echo htmlspecialchars($row['State']); ?></td>
                    <td><?php echo htmlspecialchars($row[$valueKey]); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php elseif (!empty($state) && !empty($attribute)): ?>
        <p class="no-data">No data found for this selection.</p>
    <?php endif; ?>
</body>
</html>
