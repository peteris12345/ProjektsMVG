<?php
session_start();
if ($_SESSION['IR'] == FALSE)
{
    header("Location: admin.php");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Page Title</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel='stylesheet' type='text/css' media='screen' href='main.css'>
    <script src='main.js'></script>
</head>
<body>
    <?php
        include('db.php');
        $sql = "SELECT * FROM pp_brands";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
          while($row = mysqli_fetch_assoc($result)) {
           $name = $row["name"];
           $country = $row["country"];
           $id = $row["brand_id"];
           ?>
                <form action='updatebrand.php' method='POST'>
                    <div class='input-group mb-3'>
                        <input name='name' class="input-group-text" value='<?php echo "$name"; ?>'>
                        <input name='country' class="input-group-text" value='<?php echo "$country"; ?>'>
                        <button value='<?php echo "$id"; ?>' name='id' class="btn btn-success">UPDATE</button>
                    </div>
                </form>
            <?php
          }
        } else {
          echo "0 results";
        }
      
        mysqli_close($conn);
    ?>
    <div id='brandinsert'>
        <form action='brandinsert.php' method='POST'>
            <input name='brand' class="input-group-text" placeholder='Brand name'/>
            <input name='country' class="input-group-text" placeholder='Brand country'/>
            <button class="btn btn-danger">ADD</button>
        </form>
    </div>
</body>
</html>
