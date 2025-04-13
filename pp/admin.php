<?php
session_start();
$conn = new mysqli("localhost", "u547027111_mvg", "MVGskola1", "u547027111_mvg");

if ($conn->connect_error) {
    die("Savienojuma kļūda: " . $conn->connect_error);
}

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: first.php");
    exit;
}

// Pārbauda, vai admins ir veicis dzēšanas darbību
if (isset($_GET["delete_item"])) {
    $item_id = intval($_GET["delete_item"]);
    $stmt = $conn->prepare("DELETE FROM uploads WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    if ($stmt->execute()) {
        // Pievieno vēstures ierakstu
        $admin_id = $_SESSION["user_id"];
        $history_text = "Dzēsts priekšmets ID: " . $item_id;
        $stmt_history = $conn->prepare("INSERT INTO pp_vesture (user_id, action_type, item_id, details) VALUES (?, 'deleted', ?, ?)");
        $stmt_history->bind_param("iis", $admin_id, $item_id, $history_text);
        $stmt_history->execute();
        $stmt_history->close();
    }
    header("Location: admin.php");
    exit;
}

// Pārbauda, vai admins ir bloķējis lietotāju
if (isset($_GET["block_user"])) {
    $user_id = intval($_GET["block_user"]);
    $stmt = $conn->prepare("UPDATE pp_lietotaji SET email_verified = 0 WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $admin_id = $_SESSION["user_id"];
        $history_text = "Bloķēts lietotājs ID: " . $user_id;
        $stmt_history = $conn->prepare("INSERT INTO pp_vesture (user_id, action_type, item_id, details) VALUES (?, 'blocked', ?, ?)");
        $stmt_history->bind_param("iis", $admin_id, $user_id, $history_text);
        $stmt_history->execute();
        $stmt_history->close();
    }
    header("Location: admin.php");
    exit;
}

// Pārbauda, vai admins ir dzēsis lietotāju
if (isset($_GET["delete_user"])) {
    $user_id = intval($_GET["delete_user"]);
    $stmt = $conn->prepare("DELETE FROM pp_lietotaji WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $admin_id = $_SESSION["user_id"];
        $history_text = "Dzēsts lietotājs ID: " . $user_id;
        $stmt_history = $conn->prepare("INSERT INTO pp_vesture (user_id, action_type, item_id, details) VALUES (?, 'deleted_user', ?, ?)");
        $stmt_history->bind_param("iis", $admin_id, $user_id, $history_text);
        $stmt_history->execute();
        $stmt_history->close();
    }
    header("Location: admin.php");
    exit;
}

// Paginācijas iestatījumi
$mantas_per_page = 4;
$lietotaji_per_page = 4;

$page_mantas = isset($_GET['page_mantas']) ? intval($_GET['page_mantas']) : 1;
$start_mantas = ($page_mantas - 1) * $mantas_per_page;

$page_lietotaji = isset($_GET['page_lietotaji']) ? intval($_GET['page_lietotaji']) : 1;
$start_lietotaji = ($page_lietotaji - 1) * $lietotaji_per_page;

// Iegūst mantas ar limitu
$sql_mantas = "SELECT * FROM uploads LIMIT $start_mantas, $mantas_per_page";
$result_mantas = $conn->query($sql_mantas);

// Iegūst mantu kopskaitu
$total_mantas = $conn->query("SELECT COUNT(*) as count FROM uploads")->fetch_assoc()['count'];
$total_mantas_pages = ceil($total_mantas / $mantas_per_page);

// Iegūst lietotājus ar limitu
$sql_lietotaji = "SELECT * FROM pp_lietotaji LIMIT $start_lietotaji, $lietotaji_per_page";
$result_lietotaji = $conn->query($sql_lietotaji);

// Iegūst lietotāju kopskaitu
$total_lietotaji = $conn->query("SELECT COUNT(*) as count FROM pp_lietotaji")->fetch_assoc()['count'];
$total_lietotaji_pages = ceil($total_lietotaji / $lietotaji_per_page);
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admina panelis</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<h2>Admina panelis</h2>

<h3>Lietotāju pievienotās mantas</h3>
<table>
    <tr>
        <th>ID</th>
        <th>Nosaukums</th>
        <th>Kategorija</th>
        <th>Attēls</th>
        <th>Darbības</th>
    </tr>
    <?php while ($row = $result_mantas->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row["id"]; ?></td>
            <td><?php echo htmlspecialchars($row["title"]); ?></td>
            <td><?php echo htmlspecialchars($row["category"]); ?></td>
            <td><img src="<?php echo htmlspecialchars($row["file_path"]); ?>" class="item-image"></td>
            <td>
                <a href="admin.php?delete_item=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Vai tiešām vēlaties dzēst šo priekšmetu?');">Dzēst</a>
            </td>
        </tr>
    <?php } ?>
</table>

<div class="pagination">
    <?php for ($i = 1; $i <= $total_mantas_pages; $i++) { ?>
        <a href="admin.php?page_mantas=<?php echo $i; ?>" class="page-link"><?php echo $i; ?></a>
    <?php } ?>
</div>

<h3>Lietotāji</h3>
<table>
    <tr>
        <th>ID</th>
        <th>E-pasts</th>
        <th>Loma</th>
        <th>Statuss</th>
        <th>Darbības</th>
    </tr>
    <?php while ($row = $result_lietotaji->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row["id"]; ?></td>
            <td><?php echo htmlspecialchars($row["email"]); ?></td>
            <td><?php echo $row["role"]; ?></td>
            <td><?php echo ($row["email_verified"] == 1) ? "Aktīvs" : "Bloķēts"; ?></td>
            <td>
                <?php if ($row["role"] !== "admin") { ?>
                    <a href="admin.php?block_user=<?php echo $row['id']; ?>" class="block-btn">Bloķēt</a> |
                    <a href="admin.php?delete_user=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Vai tiešām vēlaties dzēst šo lietotāju?');">Dzēst</a>
                <?php } else { ?>
                    Admins
                <?php } ?>
            </td>
        </tr>
    <?php } ?>
</table>

<a href="main.php" class="back-btn">Uz sākumu</a> | 
<a href="logout.php" class="logout-btn">Iziet</a> |
<a href="history.php" class="history-btn">Uz vēsturi</a>
</body>
</html>
