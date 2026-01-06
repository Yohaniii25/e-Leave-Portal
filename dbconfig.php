<?php
$servername = "localhost"; 
$username = "pannalaps_leave"; 
$password = "&974TNJ~d]Nz2{k_"; 
$dbname = "pannalaps_leave"; 

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
