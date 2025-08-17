<?php
$servername = "34.71.99.108";
$username = "root";
$password = "Kobeni_Higashiyama1234!";  
$dbname = "socioeconomic_data";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$zipcode = '';
$results = null;
$error = '';

if (isset($_GET['zipcode'])) {
    $zipcode = $conn->real_escape_string($_GET['zipcode']);

    if (!empty($zipcode)) {
        $sql = "
        SELECT DISTINCT
  f.County_Name,
  f.State,
  p.Value AS population_2023,
  l.Value AS labor_force_2023,
  g.percentChange2023 AS gdp_change_2023,
  e.Value AS education_metric
FROM zipcode_data z
JOIN fips_to_counties f ON z.FIPS = f.FIPS_Code
LEFT JOIN population_estimate p 
  ON f.FIPS_Code = p.FIPStxt AND p.Attribute = 'POP_ESTIMATE_2023'
LEFT JOIN labor_force_data l 
  ON f.FIPS_Code = CAST(l.FIPS_Code AS UNSIGNED) AND l.Attribute = 'Civilian_labor_force_2023'
LEFT JOIN gdp_by_county g 
  ON f.County_Name = g.countyName
LEFT JOIN educationtest e 
  ON f.FIPS_Code = e.FIPSCode AND e.Attribute = 'Bachelor''s degree or higher; 2008-12'
WHERE z.ZIP = '$zipcode'
LIMIT 15
        ";

        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $results = $result->fetch_assoc();
        } else {
            $error = "No data found for zipcode $zipcode";
        }
    } else {
        $error = "Please enter a zipcode.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Zipcode Lookup</title>
</head>
<body>
    <h1>Lookup Socioeconomic Data by Zipcode</h1>
    <form method="get" action="">
        Enter Zipcode: <input type="text" name="zipcode" value="<?php echo htmlspecialchars($zipcode); ?>" required>
        <input type="submit" value="Search">
    </form>

    <?php if ($error): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if ($results): ?>
        <h2>Results for Zipcode: <?php echo htmlspecialchars($zipcode); ?></h2>
        <ul>
            <li><strong>County:</strong> <?php echo htmlspecialchars($results['County_Name']); ?></li>
            <li><strong>State:</strong> <?php echo htmlspecialchars($results['State']); ?></li>
            <li><strong>Population 2023:</strong> <?php echo htmlspecialchars($results['population_2023']); ?></li>
            <li><strong>Labor Force 2023:</strong> <?php echo htmlspecialchars($results['labor_force_2023']); ?></li>
            <li><strong>GDP Change 2023:</strong> <?php echo htmlspecialchars($results['gdp_change_2023']); ?>%</li>
            <li><strong>Bachelor's Degree or Higher:</strong> <?php echo htmlspecialchars($results['education_metric']); ?>%</li>
        </ul>
    <?php endif; ?>

</body>
</html>
