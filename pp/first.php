<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pazaudēto Mantu Stūris</title>
    <link rel='stylesheet' type='text/css' media='screen' href='first.css'>
</head>
<body>
    <div class="container">
        <h1>Pazaudēto Mantu Stūris</h1>
        <button onclick="window.location.href='login.php';">Pieslēgties</button>
        <button onclick="window.location.href='register.php';">Izveidot kontu</button>
    </div>
</body>
</html>
