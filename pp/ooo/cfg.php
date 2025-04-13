<?php
session_start();
unset($_SESSION['IR']);
$user = $_POST['user'];
$code = md5($_POST['password']);

include('db.php');
    $sql = "SELECT * FROM pp_users WHERE email = '$user' and pk = '$code'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        while($row = mysqli_fetch_assoc($result)) {
            $id = $row["user_id"];
        }
        $_SESSION['code'] = $id;
        session_start();
        $_SESSION['IR'] = TRUE;
        header("Location: index.php");
    }
    else {
        session_start();
        $_SESSION['IR'] = FALSE;
        header("Location: admin.php");
       
    }


?> 
