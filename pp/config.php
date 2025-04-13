<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ja šī nav `verify.php` lapa un lietotājs nav ielogojies, novirza uz `login.php`
if (!isset($_SESSION["user_id"]) && basename($_SERVER['PHP_SELF']) !== "verify.php") {
    header("Location: first.php");
    exit();
}

// Datubāzes pieslēguma dati
$servername = "localhost";
$username = "u547027111_mvg";
$password = "MVGskola1";
$dbname = "u547027111_mvg";

// Izveido savienojumu ar datubāzi
$conn = new mysqli($servername, $username, $password, $dbname);

// Pārbauda savienojumu
if ($conn->connect_error) {
    die("Savienojuma kļūda: " . $conn->connect_error);
}

// Nodrošina, ka datubāzē tiek izmantoti latviešu simboli
$conn->set_charset("utf8mb4");
?>
