<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== "admin") {
    header("Location: login.php");
    exit();
}

if (!isset($_POST['order_id'])) {
    header("Location: view_order_items.php");
    exit();
}

$order_id = intval($_POST['order_id']);

$stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();

$stmt->close();
$conn->close();

header("Location: view_order_items.php");
exit();
