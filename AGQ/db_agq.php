<?php
$servername = "localhost";
$username = "root";
$pass = "";
$dbase = "agq_database";

$conn = new mysqli($servername, $username, $pass, $dbase);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
