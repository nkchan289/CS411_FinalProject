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

$currentUser = $_SESSION['username'] ?? null;
if (!$currentUser) {
    die("You must be logged in to view this page.");
}

$groupStmt = $conn->prepare("SELECT group_id FROM group_memberships WHERE Username = ? LIMIT 1");
$groupStmt->bind_param("s", $currentUser);
$groupStmt->execute();
$groupStmt->bind_result($group_id);
$groupStmt->fetch();
$groupStmt->close();

if (!$group_id) {
    die("You are not part of any group.");
}

$groupNameStmt = $conn->prepare("SELECT group_name FROM user_groups WHERE group_id = ?");
$groupNameStmt->bind_param("i", $group_id);
$groupNameStmt->execute();
$groupNameStmt->bind_result($group_name);
$groupNameStmt->fetch();
$groupNameStmt->close();

$membersStmt = $conn->prepare("
    SELECT Username FROM group_memberships WHERE group_id = ? ORDER BY Username
");
$membersStmt->bind_param("i", $group_id);
$membersStmt->execute();
$membersResult = $membersStmt->get_result();
$members = [];
while ($row = $membersResult->fetch_assoc()) {
    $members[] = $row['Username'];
}
$membersStmt->close();

$favoritesStmt = $conn->prepare("
    SELECT zip_code FROM group_favorites WHERE group_id = ? ORDER BY zip_code
");
$favoritesStmt->bind_param("i", $group_id);
$favoritesStmt->execute();
$favoritesResult = $favoritesStmt->get_result();
$favorites = [];
while ($row = $favoritesResult->fetch_assoc()) {
    $favorites[] = $row['zip_code'];
}
$favoritesStmt->close();

$conn->close();

$msg = $_GET['msg'] ?? '';
$successMessage = '';
if ($msg === 'removed') {
    $successMessage = '✅ Favorite removed successfully.';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Group</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: linear-gradient(to right, #e6f0ff, #f5e6ff, #ffe6f0); /* merged pastel colors */
            color: #333;
            padding: 30px 0;
        }

        h1 {
            text-align: center;
            color: #355070;
            margin-bottom: 15px;
        }

        h2 {
            text-align: center;
            color: #5b5b8f;
            margin-bottom: 30px;
        }

        .container {
            display: flex;
            gap: 40px;
            justify-content: center;
            flex-wrap: wrap;
        }

        table {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-collapse: collapse;
            width: 300px;
            max-width: 100%;
        }

        th, td {
            padding: 14px 18px;
            border-bottom: 1px solid #e0e0e0;
            text-align: center;
        }

        th {
            background-color: #a3cef1;
            color: #1d3557;
            font-weight: 600;
            font-size: 1.1rem;
        }

        tr:hover {
            background-color: #dbe9f4;
        }

        td.zip-cell {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .remove-form {
            display: inline-block;
        }

        .remove-button {
            background-color: #ff6b6b;
            border: none;
            color: white;
            padding: 5px 10px;
            font-size: 0.9rem;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .remove-button:hover {
            background-color: #d94f4f;
        }

        .empty-msg {
            font-style: italic;
            color: #888;
            padding: 20px;
        }

        .success-msg {
            text-align: center;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0,128,0,0.1);
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .leave-group-form {
            text-align: center;
            margin-top: 30px;
        }
        .leave-group-button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-weight: 600;
        }
        .leave-group-button:hover {
            background-color: #a71d2a;
        }

        .back-btn {
            display: block;
            margin: 30px auto 0 auto;
            background: #355070;
            color: white;
            border: none;
            padding: 12px 28px;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            width: max-content;
            box-shadow: 0 4px 10px rgba(53,80,112,0.4);
            transition: background-color 0.3s ease;
        }

        .back-btn:hover {
            background-color: #264060;
        }
    </style>
</head>
<body>

<h1>Group Members & Favorites</h1>
<h2>Group: <?php echo htmlspecialchars($group_name); ?></h2>

<?php if ($successMessage): ?>
    <div class="success-msg"><?php echo htmlspecialchars($successMessage); ?></div>
<?php endif; ?>

<div class="container">

    <!-- Group Members -->
    <table>
        <thead>
            <tr><th>Group Members</th></tr>
        </thead>
        <tbody>
            <?php if (empty($members)): ?>
                <tr><td class="empty-msg">No members found.</td></tr>
            <?php else: ?>
                <?php foreach ($members as $member): ?>
                    <tr><td><?php echo htmlspecialchars($member); ?></td></tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Group Favorites -->
    <table>
        <thead>
            <tr><th>Group Favorites (ZIP Codes)</th></tr>
        </thead>
        <tbody>
            <?php if (empty($favorites)): ?>
                <tr><td class="empty-msg">No favorites added yet.</td></tr>
            <?php else: ?>
                <?php foreach ($favorites as $zip): ?>
                    <tr>
                        <td class="zip-cell">
                            <?php echo htmlspecialchars($zip); ?>
                            <form class="remove-form" method="POST" action="remove_group_favorite.php" onsubmit="return confirm('Remove this favorite?');">
                                <input type="hidden" name="zip_code" value="<?php echo htmlspecialchars($zip); ?>">
                                <button type="submit" class="remove-button" title="Remove Favorite">&times;</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</div>

<!-- Leave Group Button -->
<form method="post" action="leave_group.php" class="leave-group-form" onsubmit="return confirm('Are you sure you want to leave the group?');">
    <button type="submit" class="leave-group-button">Leave Group</button>
</form>

<a href="menu.php" class="back-btn">Back to Menu</a>

</body>
</html>
