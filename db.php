<?php

$servername = "localhost";
$username = "root";
$password = "mysql";
$dbname = "348_project";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
