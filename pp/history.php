<?php
session_start();
$servername = "localhost";
$username = "u547027111_mvg";
$password = "MVGskola1";
$dbname = "u547027111_mvg";

// Pieslēgšanās datubāzei
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Savienojuma kļūda: " . $conn->connect_error);
}

// Pārbauda, vai lietotājs ir admins
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: first.php");
    exit();
}

// Lapas parametri
$itemsPerPage = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $itemsPerPage;

// Filtru apstrāde
$whereClauses = [];
$filterParams = [];

if (!empty($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    $whereClauses[] = "pp_vesture.user_id = $user_id";
    $filterParams['user_id'] = $user_id;
}
if (!empty($_GET['action_type'])) {
    $action_type = $conn->real_escape_string($_GET['action_type']);
    $whereClauses[] = "pp_vesture.action_type = '$action_type'";
    $filterParams['action_type'] = $action_type;
}
if (!empty($_GET['date'])) {
    $date = $conn->real_escape_string($_GET['date']);
    $whereClauses[] = "DATE(pp_vesture.created_at) = '$date'";
    $filterParams['date'] = $date;
}

$whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

// Iegūst kopējo rindu skaitu
$countSql = "SELECT COUNT(*) AS total FROM pp_vesture $whereSql";
$countResult = $conn->query($countSql);
$totalItems = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Iegūst ierakstus ar LIMIT
$sql = "SELECT pp_vesture.*, pp_lietotaji.email 
        FROM pp_vesture 
        JOIN pp_lietotaji ON pp_vesture.user_id = pp_lietotaji.id 
        $whereSql
        ORDER BY pp_vesture.created_at DESC
        LIMIT $offset, $itemsPerPage";
$result = $conn->query($sql);

// Funkcija, lai atjaunotu URL ar filtru parametriem
function buildPageUrl($pageNum, $filterParams) {
    $filterParams['page'] = $pageNum;
    return '?' . http_build_query($filterParams);
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrācijas Vēsture</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <h2>Administrācijas Vēsture</h2>
    
    <!-- Filtru forma -->
    <form method="GET">
        <label for="user_id">Meklēt pēc lietotāja ID:</label>
        <input type="number" name="user_id" id="user_id" value="<?= isset($filterParams['user_id']) ? $filterParams['user_id'] : '' ?>">

        <label for="action_type">Darbība:</label>
        <select name="action_type">
            <option value="">Visas</option>
            <option value="added" <?= (isset($filterParams['action_type']) && $filterParams['action_type'] === 'added') ? 'selected' : '' ?>>Pievienots</option>
            <option value="edited" <?= (isset($filterParams['action_type']) && $filterParams['action_type'] === 'edited') ? 'selected' : '' ?>>Labots</option>
            <option value="deleted" <?= (isset($filterParams['action_type']) && $filterParams['action_type'] === 'deleted') ? 'selected' : '' ?>>Dzēsts</option>
        </select>

        <label for="date">Datums:</label>
        <input type="date" name="date" value="<?= isset($filterParams['date']) ? $filterParams['date'] : '' ?>">

        <button type="submit">Filtrēt</button>
    </form>

    <!-- Vēstures tabula -->
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Lietotājs</th>
            <th>Darbība</th>
            <th>Priekšmeta ID</th>
            <th>Detaļas</th>
            <th>Datums</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row["id"] ?></td>
                    <td><?= htmlspecialchars($row["email"]) ?></td>
                    <td><?= htmlspecialchars($row["action_type"]) ?></td>
                    <td><?= htmlspecialchars($row["item_id"] ?? "Nav") ?></td>
                    <td><?= htmlspecialchars($row["details"]) ?></td>
                    <td><?= $row["created_at"] ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6" style="text-align:center;">Nav rezultātu</td></tr>
        <?php endif; ?>
    </table>

    <!-- Page selector -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a class="page-link <?= ($i == $page) ? 'active' : '' ?>" href="<?= buildPageUrl($i, $filterParams) ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

    <br>
    <a href="admin.php">⬅️ Atpakaļ uz admina paneli</a>
</body>
</html>

<?php $conn->close(); ?>
