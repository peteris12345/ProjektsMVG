<?php
require 'config.php';

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = trim($_GET['email']);
    $token = trim($_GET['token']);

    $stmt = $conn->prepare("SELECT id, email_verified FROM pp_lietotaji WHERE email = ? AND verification_token = ?");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $email_verified);
        $stmt->fetch();

        if ($email_verified == 1) {
            header("Location: login.php?message=already_verified");
            exit();
        } else {
            $update_stmt = $conn->prepare("UPDATE pp_lietotaji SET email_verified = 1, verification_token = NULL WHERE id = ?");
            $update_stmt->bind_param("i", $id);
            if ($update_stmt->execute()) {
                // Automātiski pieslēdz lietotāju
                $_SESSION["user_id"] = $id;
                $_SESSION["user_email"] = $email;
                header("Location: main.php");
                exit();
            } else {
                header("Location: login.php?message=error");
                exit();
            }
        }
    } else {
        header("Location: login.php?message=invalid_link");
        exit();
    }
} else {
    header("Location: login.php?message=missing_params");
    exit();
}
?>
