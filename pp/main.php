<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION['user_id'])) {
    header("Location: first.php");
    exit();
}

$servername = "localhost";
$username = "u547027111_mvg";
$password = "MVGskola1";
$dbname = "u547027111_mvg";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Savienojuma kļūda: " . $conn->connect_error);
}

// Lapu skaits
$itemsPerPage = 16;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// Kopējais mantu skaits
$countSql = "SELECT COUNT(*) AS total FROM uploads";
$countResult = $conn->query($countSql);
$totalItems = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Galvenais vaicājums
$sql = "SELECT title, file_path, category FROM uploads ORDER BY uploaded_at DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offset, $itemsPerPage);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Mantu Saraksts</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="main.css">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Navigācijas josla -->
    <div class="navbar">
        <input type="text" placeholder="Meklēt pēc nosaukuma" class="search-bar" id="searchInput">
        <button class="nav-button" onclick="window.location.href='upload.php';">Pievienot Mantu</button>
        <button class="nav-button" onclick="window.location.href='categories.php';">Kategorijas</button>
        <button class="nav-button" onclick="window.location.href='profils.php';">Mans Profils</button>
    </div>

    <!-- Mantu saraksts -->
    <div class="item-container" id="itemsContainer">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='item " . htmlspecialchars($row["category"]) . "'>";
                echo "<div class='item-label'>" . htmlspecialchars($row["title"]) . "</div>";
                echo "<div class='item-image'><img src='" . htmlspecialchars($row["file_path"]) . "' alt='Mantas attēls'></div>";
                echo "</div>";
            }
        } else {
            echo "<p id='noResults'>Nav pievienota neviena manta.</p>";
        }

        $conn->close();
        ?>
    </div>

    <!-- Nav rezultātu ziņa -->
    <p id="noResults" style="display: none;">Nav atrasta neviena manta.</p>

    <!-- Lapu izvēlētājs -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a class="page-link <?php if ($i == $page) echo 'active'; ?>" href="?page=<?php echo $i; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

    <!-- JavaScript filtrēšana -->
    <script>
    const searchInput = document.getElementById("searchInput");
    const items = document.querySelectorAll(".item");
    const noResults = document.getElementById("noResults");

    searchInput.addEventListener("input", function () {
        const query = this.value.toLowerCase();
        let found = false;

        items.forEach(item => {
            const title = item.querySelector(".item-label").textContent.toLowerCase();
            if (title.includes(query)) {
                item.style.display = "block";
                found = true;
            } else {
                item.style.display = "none";
            }
        });

        noResults.style.display = found ? "none" : "block";
    });
    </script>

</body>
</html>
