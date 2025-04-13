<?php
session_start();

// Neļauj kešot šo lapu
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION['user_id'])) {
    header("Location: first.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategorijas</title>
    <link rel='stylesheet' type='text/css' media='screen' href='categories.css'>
</head>
<body>
    <h1>Kategorijas</h1>
    
    <div class="categories-container">
        <a href="show_category.php?category=atslēgas" class="category-button">Atslēgas</a>
        <a href="show_category.php?category=viedierīces" class="category-button">Viedierīces</a>
        <a href="show_category.php?category=dokumenti" class="category-button">Dokumenti</a>
        <a href="show_category.php?category=apģērbs" class="category-button">Apģērbs</a>
        <a href="show_category.php?category=apavi" class="category-button">Apavi</a>
        <a href="show_category.php?category=klades" class="category-button">Klades</a>
        <a href="show_category.php?category=somas" class="category-button">Somas</a>
        <a href="show_category.php?category=pulksteņi" class="category-button">Pulksteņi</a>
        <a href="show_category.php?category=naudas%20maki" class="category-button">Naudas maki</a>
        <a href="show_category.php?category=cits" class="category-button">Cits</a>
    </div>

    <!-- Centrēta "Uz sākumlapu" poga -->
    <div class="centered-back-button">
        <a href="main.php" class="back-button">Uz sākumlapu</a>
    </div>

</body>
</html>
