s<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== "admin") {
    header("Location: login.php");
    exit();
}

if (!isset($_POST['table_number'])) {
    header("Location: view_order_items.php");
    exit();
}

$table_number = intval($_POST['table_number']);

$check = $conn->prepare("
    SELECT COUNT(*) 
    FROM orders 
    WHERE table_number = ? AND LOWER(status) != 'delivered'
");
$check->bind_param("i", $table_number);
$check->execute();
$check->bind_result($notDelivered);
$check->fetch();
$check->close();

if ($notDelivered > 0) {
    header("Location: view_order_items.php");
    exit();
}

$stmt = $conn->prepare("DELETE FROM orders WHERE table_number = ?");
$stmt->bind_param("i", $table_number);
$stmt->execute();

$stmt->close();
$conn->close();

header("Location: view_order_items.php");
exit();
