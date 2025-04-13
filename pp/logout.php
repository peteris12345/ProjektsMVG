<?php
session_start();

// Dzēš visus sesijas datus
$_SESSION = [];
session_unset();
session_destroy();

// Atspējo pārlūkprogrammas kešošanu
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Novirza uz sākumlapu
header("Location: first.php");
exit();
?>
