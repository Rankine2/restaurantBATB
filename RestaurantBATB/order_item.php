<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please+login+first");
    exit();
}

if ($_SESSION['user_type'] !== "user") {
    header("Location: index.php?added_message=" . urlencode("Only users can place orders"));
    exit();
}

if (!isset($_GET['menu_id']) || !is_numeric($_GET['menu_id'])) {
    header("Location: index.php?added_message=" . urlencode("Invalid menu item"));
    exit();
}

$user_id = $_SESSION['user_id'];
$menu_id = intval($_GET['menu_id']);
$status = "pending";

$check = mysqli_query($conn, "SELECT id FROM menu_items WHERE id = $menu_id LIMIT 1");
if (mysqli_num_rows($check) === 0) {
    header("Location: index.php?added_message=" . urlencode("Menu item not found"));
    exit();
}


$sql = "INSERT INTO orders (customer_id, item_id, status) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iis", $user_id, $menu_id, $status);

if (mysqli_stmt_execute($stmt)) {
    header("Location: index.php?added_message=" . urlencode("Order added successfully"));
    exit();
} else {
    header("Location: index.php?added_message=" . urlencode("Order failed: " . mysqli_error($conn)));
    exit();
}
?>
