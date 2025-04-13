<?php
session_start();
$conn = new mysqli("localhost", "u547027111_mvg", "MVGskola1", "u547027111_mvg");

if ($conn->connect_error) {
    die("Savienojuma kļūda: " . $conn->connect_error);
}

$error = "";

// Pārbauda, vai ir kāds ziņojums no verify.php
if (isset($_GET['message'])) {
    $messages = [
        "already_verified" => "Tavs e-pasts jau ir apstiprināts. Vari pieslēgties!",
        "error" => "Kļūda verifikācijas procesā. Mēģini vēlreiz.",
        "invalid_link" => "Šis verifikācijas links nav derīgs.",
        "missing_params" => "Nepareizi parametri verifikācijas linkā."
    ];
    $error = $messages[$_GET['message']] ?? "";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!preg_match("/@marupe\.edu\.lv$/", $email)) {
        $error = "Atļauts tikai @marupe.edu.lv domēna e-pasts!";
    } else {
        $stmt = $conn->prepare("SELECT id, password, email_verified, role FROM pp_lietotaji WHERE email = ?");
        if (!$stmt) {
            die("SQL kļūda: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_password, $email_verified, $role);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                if ($email_verified == 1) {
                    $_SESSION["user_id"] = $id;
                    $_SESSION["role"] = $role;

                    if ($role === "admin") {
                        header("Location: admin.php");
                    } else {
                        header("Location: main.php");
                    }
                    exit;
                } else {
                    $error = "Lūdzu, apstiprini savu e-pastu!";
                }
            } else {
                $error = "Nepareizs e-pasts vai parole!";
            }
        } else {
            $error = "Nepareizs e-pasts vai parole!";
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
    <title>Pieslēgties</title>
    <link rel="stylesheet" type="text/css" media="screen" href="auth.css">
</head>
<body>

<div class="register-container">
    <h2>Pieslēgties</h2>
    
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

    <form action="login.php" method="post">
        <input type="email" name="email" placeholder="E-pasts" required>
        <input type="password" name="password" placeholder="Parole" required>
        <button type="submit">Pieslēgties</button>
    </form>

    <a class="back-link" href="first.php">Atpakaļ</a>
</div>

</body>
</html>
