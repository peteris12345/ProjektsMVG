<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'config.php';

// Neļauj kešot lapu
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['user_id'])) {
    header("Location: first.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Dzēšana
if (isset($_POST['delete_item'])) {
    $item_id = $_POST['item_id'];
    $stmt = $conn->prepare("DELETE FROM uploads WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $item_id, $user_id);
    if ($stmt->execute()) {
        $action_type = "deleted";
        $details = "Dzēsts priekšmets ID: $item_id";
        $stmt = $conn->prepare("INSERT INTO pp_vesture (user_id, action_type, item_id, details) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $user_id, $action_type, $item_id, $details);
        $stmt->execute();
    }
    header("Location: profils.php");
    exit();
}

// Rediģēšana
if (isset($_POST['edit_item'])) {
    $item_id = $_POST['item_id'];
    $new_name = $_POST['item_name'];
    $new_category = $_POST['item_category'];

    if (!empty($_FILES['item_image']['name'])) {
        $image_path = 'bildes/' . basename($_FILES['item_image']['name']);
        move_uploaded_file($_FILES['item_image']['tmp_name'], $image_path);
        $stmt = $conn->prepare("UPDATE uploads SET title = ?, category = ?, file_path = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssii", $new_name, $new_category, $image_path, $item_id, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE uploads SET title = ?, category = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssii", $new_name, $new_category, $item_id, $user_id);
    }

    if ($stmt->execute()) {
        $action_type = "edited";
        $details = "Labots priekšmets ID: $item_id";
        $stmt = $conn->prepare("INSERT INTO pp_vesture (user_id, action_type, item_id, details) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $user_id, $action_type, $item_id, $details);
        $stmt->execute();
    }
    header("Location: profils.php");
    exit();
}

// Lietotāja mantas
$stmt = $conn->prepare("SELECT id, title, category, file_path FROM uploads WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);

// Kategorijas
$categories = ["Cits", "Viedierīces", "Apavi", "Somas", "Dokumenti", "Atslēgas", "Apģērbs", "Klades", "Pulksteņi", "Naudas maki"];
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mans profils</title>
    <link rel="stylesheet" href="profils.css">
    <script>
        let editing = false;

        function toggleEdit(itemId) {
            if (editing) return;
            editing = true;
            document.getElementById('view_' + itemId).style.display = 'none';
            document.getElementById('edit_' + itemId).style.display = 'block';
            document.querySelectorAll('.edit-btn').forEach(btn => btn.disabled = true);
        }

        function cancelEdit(itemId) {
            editing = false;
            document.getElementById('view_' + itemId).style.display = 'block';
            document.getElementById('edit_' + itemId).style.display = 'none';
            document.querySelectorAll('.edit-btn').forEach(btn => btn.disabled = false);
        }

        function confirmDelete(itemId) {
            if (confirm("Vai tiešām vēlaties dzēst šo mantu?")) {
                document.getElementById('delete_form_' + itemId).submit();
            }
        }
    </script>
</head>
<body>
    <h2>Mans profils</h2>
    <a href="logout.php">Iziet no profila</a>

    <h3>Manas pievienotās mantas:</h3>
    <div class="item-container">
        <?php foreach ($items as $item): ?>
            <div class="item-box">
                <div id="view_<?= $item['id'] ?>">
                    <img src="<?= htmlspecialchars($item['file_path']) ?>" alt="Mantas attēls" class="item-image">
                    <p><?= htmlspecialchars($item['title']) ?> - <?= htmlspecialchars($item['category']) ?></p>
                    <button class="edit-btn" onclick="toggleEdit(<?= $item['id'] ?>)">Labot</button>
                    <button class="delete-btn" onclick="confirmDelete(<?= $item['id'] ?>)">Dzēst</button>
                    <form id="delete_form_<?= $item['id'] ?>" method="post">
                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                        <input type="hidden" name="delete_item" value="1">
                    </form>
                </div>
                <div id="edit_<?= $item['id'] ?>" style="display: none;">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                        <input type="text" name="item_name" value="<?= htmlspecialchars($item['title']) ?>">
                        <select name="item_category">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category ?>" <?= $item['category'] == $category ? 'selected' : '' ?>><?= $category ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="file" name="item_image">
                        <button type="submit" name="edit_item">Saglabāt</button>
                        <button type="button" onclick="cancelEdit(<?= $item['id'] ?>)">Atcelt</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <a href="main.php" class="back-button">Uz sākumlapu</a>

    <!-- ✅ JavaScript aizsardzība pret atpakaļ pogu -->
    <script>
        // Ja lapa tiek ielādēta no keša (back poga), pārlādē no servera
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || performance.getEntriesByType("navigation")[0].type === "back_forward") {
                location.reload();
            }
        });

        // Pārbauda sesiju, ja lapu mēģina ielādēt ar "Back"
        if (!window.performance || performance.navigation.type === 2) {
            fetch('check_session.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.loggedIn) {
                        window.location.href = 'first.php';
                    }
                });
        }
    </script>
</body>
</html>
