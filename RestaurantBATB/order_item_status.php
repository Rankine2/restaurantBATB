<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != "admin") {
    header("Location: ../login.php");
    exit();
}

if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
    header("Location: view_order_items.php");
    exit();
}

$order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
$status   = mysqli_real_escape_string($conn, $_POST['status']);

$sql = "UPDATE orders SET status='$status' WHERE id='$order_id'";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error updating order status: " . mysqli_error($conn));
}

header("Location: view_order_items.php?updated=1");
exit();
?>
