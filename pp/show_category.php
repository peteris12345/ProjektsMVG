<?php
session_start();

// Pārbauda, vai lietotājs ir ielogojies
if (!isset($_SESSION["user_id"])) {
    header("Location: first.php");
    exit();
}

// Savienojums ar datubāzi
$conn = new mysqli("localhost", "u547027111_mvg", "MVGskola1", "u547027111_mvg");

// Pārbauda savienojumu
if ($conn->connect_error) {
    die("Savienojuma kļūda: " . $conn->connect_error);
}

// Lapas parametri
$itemsPerPage = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $itemsPerPage;

// Pārbauda, vai ir iedota kategorija
if (isset($_GET['category'])) {
    $category = $_GET['category'];

    // Iegūst kopējo mantu skaitu šai kategorijai
    $countSql = "SELECT COUNT(*) as total FROM uploads WHERE category = ?";
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param("s", $category);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalItems = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalItems / $itemsPerPage);
    $countStmt->close();

    // Iegūst mantas konkrētai lapai
    $sql = "SELECT title, file_path FROM uploads WHERE category = ? ORDER BY uploaded_at DESC LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $category, $offset, $itemsPerPage);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    echo "Nav izvēlēta neviena kategorija!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst(htmlspecialchars($category)); ?></title>
    <link rel='stylesheet' type='text/css' href='show_category.css'>
</head>
<body>
    <header>
        <h1><?php echo ucfirst(htmlspecialchars($category)); ?></h1>
    </header>

    <main>
        <div class="items-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="item-box">
                        <img src="<?php echo htmlspecialchars($row['file_path']); ?>" alt="Attēls">
                        <p class="item-name"><?php echo htmlspecialchars($row['title']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center;">Šajā kategorijā nav pievienotas mantas.</p>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a class="page-link <?php if ($i == $page) echo 'active'; ?>"
                       href="?category=<?php echo urlencode($category); ?>&page=<?php echo $i; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <a href="categories.php" class="back-button">Atpakaļ uz kategorijām</a>
    </footer>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
