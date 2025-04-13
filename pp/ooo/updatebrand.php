<?php
$name = $_POST["name"];
$country = $_POST["country"];
$id = $_POST["id"];

include('db.php');

$sql = "UPDATE pp_brands SET name = '$name', country = '$country' WHERE brand_id = '$id'";

if (mysqli_query($conn, $sql)) {
  echo "New record created successfully";
} else {
  echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}

mysqli_close($conn);
header ("location: index.php");
//echo "$name, $country, $id";
?>
