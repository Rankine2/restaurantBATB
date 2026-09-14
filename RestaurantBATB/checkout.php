<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: view_items.php");
    exit();
}

$item_id     = $_POST['item_id'];
$item_name   = $_POST['item_name'];
$item_price  = $_POST['item_price'];
$table_number = $_POST['table_number'];
$customer_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "INSERT INTO orders (customer_id, item_id, table_number, status)
     VALUES (?, ?, ?, 'pending')"
);
$stmt->bind_param("iii", $customer_id, $item_id, $table_number);
$stmt->execute();

$order_id = $stmt->insert_id;

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Confirmation | Lutong Nayon</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
body {
    font-family: "Poppins", sans-serif;
    background: #f3f4f6;
    padding: 40px;
    margin: 0;
}

/* Container for confirmation card */
.container {
    max-width: 600px;
    margin: auto;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    text-align: center;
}

/* Heading */
h1 {
    color: #111827;
    margin-bottom: 20px;
}

/* Paragraphs */
p {
    font-size: 18px;
    margin: 10px 0;
}

/* Back to Menu Button */
a {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 20px;
    background: #10b981;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    transition: 0.3s;
}

a:hover {
    background: #059669;
}

/* Item image */
img {
    width: 150px;
    border-radius: 10px;
    margin-bottom: 20px;
    border: 3px solid #e5e7eb;
}
</style>
</head>
<body>

<!-- Confirmation Card -->
<div class="container">
    <h1>✅ Order Placed Successfully!</h1>

    <!-- Item Image -->
    <img src="image/<?php echo htmlspecialchars($item_name); ?>.jpg" alt="<?php echo htmlspecialchars($item_name); ?>">

    <!-- Order Details -->
    <p><strong>Item:</strong> <?php echo htmlspecialchars($item_name); ?></p>
    <p><strong>Price:</strong> ₱<?php echo number_format($item_price, 2); ?></p>
    <p><strong>Order ID:</strong> <?php echo $order_id; ?></p>
    <p><strong>Status:</strong> Pending</p>

    <!-- Back to Menu Button -->
    <a href="view_items.php">Back to Menu</a>
</div>

</body>
</html>
