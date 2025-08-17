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

$username = $_SESSION['username'] ?? '';

if (!$username) {
    header("Location: signin.php");
    exit();
}

$groups = [];
$result = $conn->query("SELECT group_id, group_name, group_password FROM user_groups ORDER BY group_name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $groups[] = [
            'group_id' => $row['group_id'],
            'group_name' => $row['group_name'],
            'group_password_hash' => $row['group_password'] 
        ];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_group_id = $_POST['group_id'] ?? '';
    $input_password = $_POST['group_password'] ?? '';

    if (empty($input_group_id) || empty($input_password)) {
        $error_message = "Please select a group and enter the password.";
    } else {
        $selected_group = null;
        foreach ($groups as $g) {
            if ($g['group_id'] == $input_group_id) {
                $selected_group = $g;
                break;
            }
        }
        if (!$selected_group) {
            $error_message = "Selected group not found.";
        } else {
            if (!password_verify($input_password, $selected_group['group_password_hash'])) {
                $error_message = "Incorrect password for the group.";
            } else {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM group_memberships WHERE Username = ? AND group_id = ?");
                $stmt->bind_param("si", $username, $input_group_id);
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                $stmt->close();

                if ($count > 0) {
                    $error_message = "You are already a member of this group.";
                } else {
                    $stmt = $conn->prepare("INSERT INTO group_memberships (Username, group_id) VALUES (?, ?)");
                    $stmt->bind_param("si", $username, $input_group_id);
                    if ($stmt->execute()) {
                        $success_message = "Successfully joined the group!";
                    } else {
                        $error_message = "Failed to join the group. Please try again.";
                    }
                    $stmt->close();
                }
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Join Group</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0; padding: 0;
            background: linear-gradient(135deg, #a1c4fd, #c2e9fb);
            height: 100vh;
            display: flex; align-items: center; justify-content: center;
        }
        .container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.15);
            width: 100%; max-width: 400px;
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
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
            margin-top: 10px;
        }
        button:hover {
            background-color: #357ab8;
        }
        .autocomplete-suggestions {
            border: 1px solid #ccc;
            max-height: 150px;
            overflow-y: auto;
            background: white;
            position: absolute;
            width: 100%;
            z-index: 1000;
            border-radius: 0 0 6px 6px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .autocomplete-suggestion {
            padding: 10px;
            cursor: pointer;
            text-align: left;
        }
        .autocomplete-suggestion:hover {
            background-color: #f0f0f0;
        }
        .autocomplete-container {
            position: relative;
            width: 100%;
            margin-bottom: 15px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #444;
        }
        input[type=text], input[type=password] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1rem;
            box-sizing: border-box;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Join User Group</h2>

    <?php if ($error_message): ?>
        <p class="error"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <p class="success"><?= htmlspecialchars($success_message) ?></p>
        <p><a href="menu.php">Back to Menu</a></p>
    <?php else: ?>

        <form method="POST" autocomplete="off" onsubmit="return validateGroupSelection()">

            <div class="autocomplete-container">
                <label for="group_search">Search Group:</label>
                <input type="text" id="group_search" name="group_search" placeholder="Type group name" required>
                <input type="hidden" id="group_id" name="group_id" required>
                <div id="autocomplete-list" class="autocomplete-suggestions"></div>
            </div>

            <label for="group_password">Group Password:</label>
            <input type="password" name="group_password" id="group_password" placeholder="Enter group password" required />

            <button type="submit">Join Group</button>
        </form>

        <p><a href="menu.php">Back to Menu</a></p>

    <?php endif; ?>
</div>

<script>
    const groups = <?= json_encode(array_map(function($g) {
        return ['group_id' => $g['group_id'], 'group_name' => $g['group_name']];
    }, $groups), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;

    const input = document.getElementById('group_search');
    const hiddenInput = document.getElementById('group_id');
    const autocompleteList = document.getElementById('autocomplete-list');

    input.addEventListener('input', function() {
        const val = this.value.toLowerCase();
        autocompleteList.innerHTML = '';
        hiddenInput.value = '';

        if (!val) return;

        const matches = groups.filter(g => g.group_name.toLowerCase().startsWith(val));

        matches.slice(0, 10).forEach(group => {
            const item = document.createElement('div');
            item.classList.add('autocomplete-suggestion');
            item.textContent = group.group_name;
            item.dataset.groupId = group.group_id;

            item.addEventListener('click', () => {
                input.value = group.group_name;
                hiddenInput.value = group.group_id;
                autocompleteList.innerHTML = '';
            });

            autocompleteList.appendChild(item);
        });
    });

    document.addEventListener('click', function(e) {
        if (e.target !== input) {
            autocompleteList.innerHTML = '';
        }
    });

    function validateGroupSelection() {
        if (!hiddenInput.value) {
            alert('Please select a group from the dropdown.');
            return false;
        }
        return true;
    }
</script>

</body>
</html>
