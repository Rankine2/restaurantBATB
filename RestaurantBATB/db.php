<?php

$server = "localhost";
$user = "root";
$password = "";
$dbname = "restaurantBATB";

$conn = new mysqli(
    $server,
    $user,
    $password,
    $dbname
);

if ($conn->connect_error) {

    die(
        "Database connection failed: "
        . $conn->connect_error
    );
}

$conn->set_charset("utf8mb4");

?>