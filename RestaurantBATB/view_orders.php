<?php 
session_start();
include "db.php";

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] == "user") {

        $user_id = $_SESSION["user_id"];
        $sql = "SELECT 
                    users.id AS user_id, 
                    users.name AS user_name, 
                    users.email, 
                    users.address, 
                    users.phone, 

                    menu_items.id AS item_id, 
                    menu_items.image AS item_image, 
                    menu_items.name AS item_name, 
                    menu_items.price AS item_price, 
                    menu_items.category AS item_category, 

                    orders.id AS order_id,
                    orders.status 

                FROM orders 
                JOIN users ON orders.customer_id = users.id
                JOIN menu_items ON orders.item_id = menu_items.id
                WHERE orders.customer_id='$user_id'";

        $result = mysqli_query($conn, $sql);

        if (!$result) {
            echo "Error!: {$conn->error}";
        }

    } elseif ($_SESSION['user_type'] == "admin") {
        header("Location: ../admin_dashboard.php");
        exit();
    }

} else {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Orders</title>


<link href="https://fonts.googleapis.com/css2?family=Poppins:wght:300;400;500;600&display=swap" rel="stylesheet">

<style>
    * {
        margin: 0;
        padding: 0;
        font-family: "Poppins", sans-serif;
        box-sizing: border-box;
    }

    body {
        background: #f3f4f6;
    }

    
    .header {
        background: #1f2937;
        padding: 20px 40px;
        color: white;
        text-align: right;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    .header a {
        background: #dc2626;
        padding: 10px 20px;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        transition: 0.3s;
        font-size: 15px;
    }

    .header a:hover {
        background: #b91c1c;
    }

    
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        height: 100%;
        width: 220px;
        background: #111827;
        padding-top: 100px;
        box-shadow: 3px 0 10px rgba(0,0,0,0.3);
    }

    .sidebar a {
        display: block;
        padding: 15px 20px;
        color: #d1d5db;
        font-size: 15px;
        text-decoration: none;
        transition: 0.3s;
    }

    .sidebar a:hover {
        background: #1f2937;
        color: #fff;
        padding-left: 28px;
    }

    
    .main {
        margin-left: 220px;
        padding: 40px;
    }

    .main h1 {
        font-size: 26px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #111827;
    }

    .table-container {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        overflow-x: auto;
    }

    table {
        width: 100%;
        min-width: 1100px;
        border-collapse: collapse;
    }

    thead {
        background: #1f2937;
        color: white;
    }

    th, td {
        padding: 12px 14px;
        text-align: center;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px;
    }

    tbody tr:hover {
        background: #f9fafb;
    }

    img {
        width: 120px;
        border-radius: 10px;
        border: 2px solid #e5e7eb;
    }

</style>
</head>
<body>


<div class="header">
    <a href="logout.php">Logout</a>
</div>


<div class="sidebar">
    <a href="user_dashboard.php">🏠 User Dashboard</a>
    <a href="view_orders.php">📦 View Orders</a>
</div>


<div class="main">
    <h1>📦 My Orders</h1>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Customer ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Item ID</th>
                    <th>Item</th>
                    <th>Image</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Order ID</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?= $row['user_id']; ?></td>
                    <td><?= $row['user_name']; ?></td>
                    <td><?= $row['email']; ?></td>
                    

                    <td><?= $row['item_id']; ?></td>
                    <td><?= $row['item_name']; ?></td>
                    <td><img src="image/<?= $row['item_image']; ?>"></td>
                    <td><?= $row['item_category']; ?></td>
                    <td>₱<?= number_format($row['item_price'], 2); ?></td>

                    <td><?= $row['order_id']; ?></td>
                    <td><?= ucfirst($row['status']); ?></td>
                </tr>
                <?php } ?>
            </tbody>

        </table>
    </div>
</div>

</body>
</html>
