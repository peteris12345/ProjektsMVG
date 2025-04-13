<?php
session_start();
$servername = "localhost";
$username = "u547027111_mvg";
$password = "MVGskola1";
$dbname = "u547027111_mvg";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Savienojuma kļūda: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $confirm_email = trim($_POST["confirm_email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($email !== $confirm_email) {
        $message = "❌ E-pasti nesakrīt!";
    } elseif ($password !== $confirm_password) {
        $message = "❌ Paroles nesakrīt!";
    } elseif (!preg_match("/^[a-zA-Z0-9._%+-]+@marupe\.edu\.lv$/", $email)) {
        $message = "❌ Atļauti tikai skolas e-pasti (@marupe.edu.lv)!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(50)); // Verifikācijas kods

        // Pārbaudām, vai e-pasts jau eksistē
        $stmt = $conn->prepare("SELECT id FROM pp_lietotaji WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "❌ Šis e-pasts jau ir reģistrēts!";
        } else {
            $stmt = $conn->prepare("INSERT INTO pp_lietotaji (email, password, email_verified, verification_token) VALUES (?, ?, 0, ?)");
            $stmt->bind_param("sss", $email, $hashed_password, $token);

            if ($stmt->execute()) {
                $verification_link = "https://mvg.lv/pp/verify.php?email=" . urlencode($email) . "&token=" . $token;
                $subject = "📧 Apstiprini savu e-pastu";
                $message_body = "Sveiki! Lai aktivizētu savu kontu, lūdzu klikšķini uz šīs saites:\n\n$verification_link";
                
                mail($email, $subject, $message_body, "From: noreply@mvg.lv");

                $message = "✅ Reģistrācija veiksmīga! Pārbaudi savu e-pastu, lai apstiprinātu kontu.";
            } else {
                $message = "❌ Reģistrācijas kļūda! Mēģini vēlreiz.";
            }
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Izveidot kontu</title>
    <link rel="stylesheet" type="text/css" media="screen" href="auth.css">
</head>
<body>
    <div class="register-container">
        <h2>Izveidot kontu</h2>
        <?php if (!empty($message)) echo "<p class='error'>$message</p>"; ?>
        <form method="post" action="register.php">
            <input type="email" name="email" placeholder="E-pasts" required>
            <input type="email" name="confirm_email" placeholder="Atkārtoti ievadiet e-pastu" required>
            <input type="password" name="password" placeholder="Parole" required>
            <input type="password" name="confirm_password" placeholder="Atkārtoti ievadiet paroli" required>
            <button type="submit">Reģistrēties</button>
        </form>
        <a href="first.php" class="back-link">Atpakaļ</a>
    </div>
</body>
</html>
