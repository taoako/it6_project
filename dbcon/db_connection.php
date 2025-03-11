<?php
// db_connection.php
$servername = "localhost";
$username = "root";
$password = "";
$database = "it6_proj";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
