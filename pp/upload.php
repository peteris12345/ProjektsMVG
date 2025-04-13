<?php
session_start();

// Neļauj kešot šo lapu
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $category = isset($_POST["category"]) ? trim($_POST["category"]) : "";
    $user_id = $_SESSION["user_id"]; // Pievienojam lietotāja ID

    if (empty($category)) {
        echo "<script>alert('Lūdzu, izvēlies kategoriju!'); window.history.back();</script>";
        exit;
    }

    if (!empty($title) && !empty($category) && isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
        $file_extension = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
        $allowed_types = ["jpg", "jpeg", "png", "gif"];

        if (!in_array($file_extension, $allowed_types)) {
            echo "<script>alert('Tikai JPG, JPEG, PNG un GIF faili ir atļauti!'); window.history.back();</script>";
            exit;
        }

        // Nodrošina unikālu faila nosaukumu
        $unique_name = uniqid() . "." . $file_extension;
        $target_dir = "bildes/";
        $target_file = $target_dir . $unique_name;

        // Sagatavo SQL vaicājumu priekšmeta pievienošanai
        $stmt = $conn->prepare("INSERT INTO uploads (title, file_name, file_path, category, user_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $title, $unique_name, $target_file, $category, $user_id);

        if ($stmt->execute()) {
            $last_inserted_id = $conn->insert_id; // Iegūst pēdējā ievietotā ieraksta ID

            // Tikai tagad pārvietojam failu
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                
                // **Pievieno ierakstu vēstures tabulā**
                $history_text = "Pievienots jauns priekšmets ID: " . $last_inserted_id;
                $stmt_history = $conn->prepare("INSERT INTO pp_vesture (user_id, action_type, item_id, details) VALUES (?, 'added', ?, ?)");
                $stmt_history->bind_param("iis", $user_id, $last_inserted_id, $history_text);
                $stmt_history->execute();
                $stmt_history->close();

                echo "<script>alert('Faila augšupielāde veiksmīga!'); window.location.href = 'main.php';</script>";
            } else {
                echo "<script>alert('Kļūda augšupielādējot failu!'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('Kļūda saglabājot datubāzē: " . $stmt->error . "'); window.history.back();</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Lūdzu, aizpildi visus laukus un pievieno attēlu!'); window.history.back();</script>";
    }
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Augšupielādēt Mantu</title>
    <link rel="stylesheet" type="text/css" href="upload.css">
</head>
<body>

<form action="upload.php" method="POST" enctype="multipart/form-data">
    <!-- Nosaukuma Lauks -->
    <input type="text" name="title" class="upload-title" placeholder="Ievadi nosaukumu" required>

    <!-- Attēla augšupielādes laukums -->
    <label for="file-upload" class="upload-box">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black">
            <path d="M12 2L6 8h3v6h6V8h3l-6-6zm-6 16v2h12v-2H6z"/>
        </svg>
        <p>Pievieno Bildi</p>
    </label>
    <input type="file" id="file-upload" name="file" accept="image/*" required>
    <span id="fileName">Nav pievienots neviens attēls</span>
    <!-- Kategorijas Izvēle -->
    <select name="category" class="category-dropdown" required>
        <option value="" disabled selected>Izvēlies atbilstošo kategoriju</option>
        <option value="atslegas">Atslēgas</option>
        <option value="apgerbi">Apģērbi</option>
        <option value="somas">Somas</option>
        <option value="klades">Klades</option>
        <option value="apavi">Apavi</option>
        <option value="pulksteni">Pulksteņi</option>
        <option value="dokumenti">Dokumenti</option>
        <option value="viedierices">Viedierīces</option>
        <option value="naudas maki">Naudas maki</option>
        <option value="cits">Cits</option>
    </select>
    <!-- Turpināt poga -->
    <button type="submit" class="continue-button">Turpināt</button>
    <a href="main.php" class="back-button">Uz sākumlapu</a>

</form>
</body>
<script>
document.getElementById("file-upload").addEventListener("change", function() {
    let fileName = this.files.length > 0 ? this.files[0].name : "Nav pievienots neviens attēls";
    document.getElementById("fileName").textContent = fileName;
});
</script>
</html>
