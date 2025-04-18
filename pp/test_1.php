<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Page Title</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel='stylesheet' type='text/css' media='screen' href='login_test.css'>
    <script src='main.js'></script>
</head>
<body>
<h1>PAZAUDĒTO MANTU STŪRIS</h1>
<div class="container">
        <div class="login-box">
            <div class="profile-icon">
                <img src="bildes/monkey.jpg" alt="User Icon">
            </div>
            <form action="authenticate.php" method="POST">
                <div class="input-group">
                    <label for="email">E-pasts</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="password">Parole</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Pieslēgties</button>
            </form>
            <button onclick="window.location.href='register.php';">Izveidot kontu</button>
        </div>
    </div>
</body>
</html>
