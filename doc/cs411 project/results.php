<?php
session_start();

$servername = "34.71.99.108";
$username = "root";
$password = "Kobeni_Higashiyama1234!";
$dbname = "socioeconomic_data";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is in a group
$user_group_id = null;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $stmtGroup = $conn->prepare("SELECT group_id FROM group_memberships WHERE Username = ?");
    $stmtGroup->bind_param("s", $username);
    $stmtGroup->execute();
    $stmtGroup->bind_result($group_id);
    if ($stmtGroup->fetch()) {
        $user_group_id = $group_id;
    }
    $stmtGroup->close();
}

$zipcode = null;
$fips = null;

if (isset($_GET['zipcode']) && preg_match('/^\d{5}$/', $_GET['zipcode'])) {
    $zipcode = $_GET['zipcode'];
} elseif (isset($_GET['fips'])) {
    $fips = $_GET['fips'];
    if (strtolower($fips[0]) === 'c') {
        $fips = substr($fips, 1);
    }
    if (!preg_match('/^\d+$/', $fips)) {
        die("Invalid FIPS code.");
    }
} else {
    die("No valid ZIP code or FIPS code provided.");
}

if ($zipcode !== null) {
    $stmt = $conn->prepare("
        SELECT DISTINCT
          f.County_Name,
          f.State,
          p.Value AS population_2023,
          l.Value AS labor_force_2023,
          g.percent_change_3 AS gdp_change_2023,
          e.Value AS education_metric
        FROM zipcode_data z
        JOIN fips_to_counties f ON z.FIPS = f.FIPS_Code
        LEFT JOIN population_estimate p 
          ON f.FIPS_Code = p.FIPStxt AND p.Attribute = 'POP_ESTIMATE_2023'
        LEFT JOIN labor_force_data l 
          ON f.FIPS_Code = CAST(l.FIPS_Code AS UNSIGNED) AND l.Attribute = 'Civilian_labor_force_2023'
        LEFT JOIN gdp_by_county g 
          ON f.County_Name = g.county AND f.State = g.state
        LEFT JOIN educationtest e 
          ON f.FIPS_Code = e.FIPSCode AND e.Attribute = 'Bachelor''s degree or higher; 2008-12'
        WHERE z.ZIP = ?
    ;");
    $stmt->bind_param("s", $zipcode);
} else {
    $stmt = $conn->prepare("
        SELECT DISTINCT
          f.County_Name,
          f.State,
          p.Value AS population_2023,
          l.Value AS labor_force_2023,
          g.percent_change_3 AS gdp_change_2023,
          e.Value AS education_metric
        FROM fips_to_counties f
        LEFT JOIN population_estimate p 
          ON f.FIPS_Code = p.FIPStxt AND p.Attribute = 'POP_ESTIMATE_2023'
        LEFT JOIN labor_force_data l 
          ON f.FIPS_Code = CAST(l.FIPS_Code AS UNSIGNED) AND l.Attribute = 'Civilian_labor_force_2023'
        LEFT JOIN gdp_by_county g 
          ON f.County_Name = g.county AND f.State = g.state
        LEFT JOIN educationtest e 
          ON f.FIPS_Code = e.FIPSCode AND e.Attribute = 'Bachelor''s degree or higher; 2008-12'
        WHERE f.FIPS_Code = ?
    ;");
    $stmt->bind_param("s", $fips);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Results for <?php echo $zipcode !== null ? "ZIP $zipcode" : "FIPS $fips"; ?></title>
  <style>
    /* Background GIF with overlay */
    body {
      font-family: Arial, sans-serif;
      padding: 30px;
      margin: 0;
      min-height: 100vh;
      color: #333;
      background: url('https://cdn.dribbble.com/userupload/42118026/file/original-e688b31cb4b49bb7ffdde72f075f306c.gif') no-repeat center center fixed;
      background-size: cover;
      position: relative;
      z-index: 0;
    }
    body::before {
      content: "";
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background-color: rgba(255, 255, 255, 0.4);
      z-index: -1;
    }
    h1 {
      text-align: center;
      margin-bottom: 20px;
    }
    table {
      width: 100%;
      max-width: 900px;
      margin: 0 auto 20px auto;
      border-collapse: collapse;
      background: white;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      border-radius: 6px;
      overflow: hidden;
    }
    th, td {
      padding: 12px 15px;
      text-align: center;
      border-bottom: 1px solid #ddd;
    }
    th {
      background-color: #007BFF;
      color: white;
      font-weight: 600;
    }
    tr:hover {
      background-color: #f1f9ff;
    }
    p.message {
      text-align: center;
      font-weight: 600;
      max-width: 900px;
      margin: 10px auto;
      padding: 10px;
      border-radius: 5px;
    }
    p.message.success {
      color: #155724;
      background-color: #d4edda;
      border: 1px solid #c3e6cb;
    }
    p.message.warning {
      color: #856404;
      background-color: #fff3cd;
      border: 1px solid #ffeeba;
    }
    p.message.error {
      color: #721c24;
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
    }
    form {
      text-align: center;
      margin-bottom: 30px;
    }
    button {
      background-color: #007BFF;
      border: none;
      color: white;
      padding: 12px 24px;
      font-size: 1rem;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #0056b3;
    }
    .back-btn {
      display: block;
      width: max-content;
      margin: 0 auto;
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
    select {
      padding: 8px 12px;
      border-radius: 6px;
      border: 1.5px solid #007BFF;
      font-size: 1rem;
      color: #007BFF;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-left: 10px;
      vertical-align: middle;
      background-color: white;
    }
    select:hover, select:focus {
      border-color: #0056b3;
      outline: none;
      box-shadow: 0 0 6px #0056b3aa;
    }
    .fav-form-container {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-top: 15px;
      gap: 10px;
    }
    label[for="favorite_type"] {
      font-weight: 600;
      color: #007BFF;
      user-select: none;
    }
  </style>
</head>
<body>
  <h1>Socioeconomic Data for <?php echo $zipcode !== null ? "ZIP Code: " . htmlspecialchars($zipcode) : "FIPS Code: " . htmlspecialchars($fips); ?></h1>

  <?php if (isset($_GET['msg'])): ?>
    <?php
      $msgClass = "success";
      $msgText = "";
      if ($_GET['msg'] === 'added') {
          $msgText = "✅ Location has been added to your favorites!";
          $msgClass = "success";
      } elseif ($_GET['msg'] === 'already_added') {
          $msgText = "⚠️ This location is already in your favorites.";
          $msgClass = "warning";
      } elseif ($_GET['msg'] === 'full') {
          $msgText = "❌ You have reached the maximum number of favorites (10). Remove some to add more.";
          $msgClass = "error";
      } elseif ($_GET['msg'] === 'invalid') {
          $msgText = "❌ Invalid ZIP or FIPS code.";
          $msgClass = "error";
      } elseif ($_GET['msg'] === 'invalid_personal') {
          $msgText = "❌ You can only add ZIP codes to your personal favorites.";
          $msgClass = "error";
      } elseif ($_GET['msg'] === 'no_group') {
          $msgText = "❌ You are not in a group to add group favorites.";
          $msgClass = "error";
      }
    ?>
    <p class="message <?php echo $msgClass; ?>"><?php echo $msgText; ?></p>
  <?php endif; ?>

  <?php if ($result->num_rows > 0): ?>
    <table>
      <thead>
        <tr>
          <th>County Name</th>
          <th>State</th>
          <th>Population (2023)</th>
          <th>Labor Force (2023)</th>
          <th>GDP Change (2023)</th>
          <th>Bachelor's 2023</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['County_Name']); ?></td>
          <td><?php echo htmlspecialchars($row['State']); ?></td>
          <td><?php echo $row['population_2023'] !== null ? number_format($row['population_2023']) : 'N/A'; ?></td>
          <td><?php echo $row['labor_force_2023'] !== null ? number_format($row['labor_force_2023']) : 'N/A'; ?></td>
          <td><?php echo $row['gdp_change_2023'] !== null ? htmlspecialchars($row['gdp_change_2023']) . '%' : 'N/A'; ?></td>
          <td><?php echo $row['education_metric'] !== null ? htmlspecialchars($row['education_metric']) : 'N/A'; ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <?php if (isset($_SESSION['username']) && $zipcode !== null): ?>
      <form method="POST" action="add_favorite.php" class="fav-form-container">
          <input type="hidden" name="zipcode" value="<?php echo htmlspecialchars($zipcode); ?>">
          <input type="hidden" name="location_type" value="zipcode">
          
          <label for="favorite_type">Save to:</label>
          <select name="favorite_type" id="favorite_type" required>
              <option value="personal">My Favorites</option>
              <?php if ($user_group_id): ?>
                  <option value="group">Group Favorites</option>
              <?php endif; ?>
          </select>
          
          <button type="submit">Add to Favorites</button>
      </form>
    <?php endif; ?>

  <?php else: ?>
    <p style="text-align:center; font-style: italic;">No data found for the selected location.</p>
  <?php endif; ?>

  <a href="menu.php" class="back-btn">Back to Menu</a>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
