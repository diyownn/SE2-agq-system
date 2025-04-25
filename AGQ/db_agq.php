<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$servername = $_ENV['DB_SERVERNAME'];
$username = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];
$dbase = $_ENV['DB_DATABASE'];

$conn = new mysqli($servername, $username, $pass, $dbase);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>
