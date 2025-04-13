<?php
$name = $_POST["brand"];
$country = $_POST["country"];

include('db.php');

$sql = "INSERT INTO pp_brands (brand_id, name, country)
VALUES (NULL, '$name', '$country')";

if (mysqli_query($conn, $sql)) {
  echo "New record created successfully";
} else {
  echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}

mysqli_close($conn);
header ("location: index.php");
//echo "$name, $country";
?>
