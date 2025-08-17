<?php
session_start();

// Database connection
$servername = "34.71.99.108";
$dbusername = "root";
$dbpassword = "Kobeni_Higashiyama1234!";
$dbname = "socioeconomic_data";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_group_id = null;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT group_id FROM group_memberships WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($group_id);
    if ($stmt->fetch()) {
        $user_group_id = $group_id;
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Main Menu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 50px 0 0 0;
            position: relative;
            min-height: 100vh;
            background-image: url('https://www.avanse.com/blogs/images/Blog_15_june_2023.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: #222;
            user-select: none;
        }
        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255, 255, 255, 0.75);
            z-index: -1;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 40px;
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        #svg-map {
            flex: 1 1 700px;
            max-width: 700px;
            height: 600px;
            border: 2px solid #ccc;
            border-radius: 8px;
            background: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            touch-action: none;
        }
        #svg-map svg {
            width: 100%;
            height: 100%;
            pointer-events: all;
        }
        #svg-map path.county:hover {
            fill: #ffcc00 !important;
            cursor: pointer;
        }
        #svg-map path.highlighted {
            fill: red !important;
            stroke: #cc0000 !important;
            stroke-width: 2 !important;
        }
        #svg-map path.state-border {
            stroke: black !important;
            stroke-width: 2 !important;
            fill: none !important;
            pointer-events: none;
            cursor: default;
        }
        .buttons {
            flex: 0 1 300px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: flex-start;
            user-select: none;
        }
        .buttons p {
            font-size: 1.1rem;
            margin-bottom: 20px;
            font-weight: 600;
        }
        button {
            width: 100%;
            height: 50px;
            margin: 10px 0;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            user-select: none;
        }
        button:hover {
            background-color: #0056b3;
            box-shadow: 0 6px 8px rgba(0,0,0,0.15);
        }
        .logout {
            background-color: #dc3545;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(220,53,69,0.4);
        }
        .logout:hover {
            background-color: #a71d2a;
            box-shadow: 0 6px 8px rgba(167,29,42,0.6);
        }
        .signin {
            background-color: #28a745;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(40,167,69,0.4);
        }
        .signin:hover {
            background-color: #1e7e34;
            box-shadow: 0 6px 8px rgba(30,126,52,0.6);
        }
        h1 {
            margin-bottom: 10px;
            text-align: center;
            user-select: text;
        }
        .search-bar {
            width: 100%;
            text-align: center;
            margin-bottom: 10px;
        }
        .search-bar input {
            padding: 8px;
            width: 300px;
            border-radius: 6px;
            border: 1px solid #ccc;
            text-align: center;
            font-size: 16px;
        }
    </style>
</head>
<body>

<?php if (isset($_SESSION['username'])): ?>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>, to Moving Masters!</h1>
<?php else: ?>
    <h1>Welcome, Guest!</h1>
<?php endif; ?>

<!-- Search Bar -->
<div class="search-bar">
    <input type="text" id="fipsSearch" placeholder="Enter FIPS code here">
</div>

<div class="container">

    <div id="svg-map">
        <?php include 'Usa_counties_large.svg'; ?>
    </div>

    <div class="buttons">
        <p>Please choose an option:</p>

        <button onclick="window.location.href='search.php'">Find ZIP Code Data</button>

        <?php if (isset($_SESSION['username'])): ?>
            <button onclick="window.location.href='favorites.php'">My Favorites</button>
            <button onclick="window.location.href='top5.php'">Top 5 Counties</button>

            <?php if ($user_group_id): ?>
                <button onclick="window.location.href='mygroup.php'">My Group</button>
            <?php else: ?>
                <button onclick="window.location.href='create_group.php'">Create Group</button>
                <button onclick="window.location.href='join_groups.php'">Join Group</button>
            <?php endif; ?>

            <form action="logout.php" method="post" style="width: 100%; margin-top: 10px;">
                <button type="submit" class="logout">Log Out</button>
            </form>

        <?php else: ?>
            <button onclick="window.location.href='signin.php'" class="signin">Sign In</button>
        <?php endif; ?>
    </div>

</div>

<script>
    const svgMap = document.querySelector('#svg-map svg');

    // Handle dragging
    let viewBox = svgMap.getAttribute('viewBox');
    if (!viewBox) {
        const width = svgMap.clientWidth;
        const height = svgMap.clientHeight;
        svgMap.setAttribute('viewBox', `0 0 ${width} ${height}`);
        viewBox = svgMap.getAttribute('viewBox');
    }
    let [x, y, width, height] = viewBox.split(' ').map(Number);
    let isDragging = false;
    let dragStart = { x: 0, y: 0 };

    svgMap.addEventListener('mousedown', e => {
        isDragging = true;
        dragStart.x = e.clientX;
        dragStart.y = e.clientY;
    });
    svgMap.addEventListener('mouseup', e => {
        isDragging = false;
    });
    svgMap.addEventListener('mouseleave', e => {
        isDragging = false;
    });
    svgMap.addEventListener('mousemove', e => {
        if (!isDragging) return;
        let dx = (dragStart.x - e.clientX) * (width / svgMap.clientWidth);
        let dy = (dragStart.y - e.clientY) * (height / svgMap.clientHeight);
        x += dx;
        y += dy;
        dragStart.x = e.clientX;
        dragStart.y = e.clientY;
        svgMap.setAttribute('viewBox', `${x} ${y} ${width} ${height}`);
    });

    // Handle zooming
    svgMap.addEventListener('wheel', e => {
        e.preventDefault();
        const zoomFactor = 1.1;
        const mouseX = e.offsetX;
        const mouseY = e.offsetY;

        const svgPointX = x + (mouseX / svgMap.clientWidth) * width;
        const svgPointY = y + (mouseY / svgMap.clientHeight) * height;

        if (e.deltaY < 0) {
            width /= zoomFactor;
            height /= zoomFactor;
        } else {
            width *= zoomFactor;
            height *= zoomFactor;
        }

        x = svgPointX - (mouseX / svgMap.clientWidth) * width;
        y = svgPointY - (mouseY / svgMap.clientHeight) * height;

        svgMap.setAttribute('viewBox', `${x} ${y} ${width} ${height}`);
    });

    // Setup county paths
    document.querySelectorAll('#svg-map path').forEach(path => {
        const id = path.id || '';
        if (id.toLowerCase().includes('border') || id.toLowerCase().includes('state') || id.toLowerCase().includes('separator')) {
            path.classList.add('state-border');
            path.style.pointerEvents = 'none';
            return;
        }
        path.classList.add('county');
        path.addEventListener('click', () => {
            let fips = id || path.getAttribute('data-fips') || path.getAttribute('name');
            if (!fips) {
                alert("No FIPS code found for this county.");
                return;
            }
            // Strip leading 'c' or 'C'
            if (fips.startsWith('c') || fips.startsWith('C')) {
                fips = fips.substring(1);
            }
            window.location.href = 'results.php?fips=' + encodeURIComponent(fips);
        });
    });

    // Search bar input for highlighting counties by FIPS prefix (ignoring leading 'c')
    document.getElementById('fipsSearch').addEventListener('input', function() {
        const searchVal = this.value.trim();
        // Clear highlights
        document.querySelectorAll('#svg-map path.highlighted').forEach(path => {
            path.classList.remove('highlighted');
        });
        if (searchVal === "") return;

        // Highlight all counties whose FIPS (without leading 'c') starts with searchVal
        document.querySelectorAll('#svg-map path.county').forEach(path => {
            let fips = path.id || path.getAttribute('data-fips') || "";
            if (fips.startsWith('c') || fips.startsWith('C')) {
                fips = fips.substring(1);
            }
            if (fips.startsWith(searchVal)) {
                path.classList.add('highlighted');
            }
        });
    });
</script>

</body>
</html>
