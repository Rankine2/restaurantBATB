<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $id  = $_POST['item_id'];
    $qty = max(1, (int)$_POST['quantity']);

    if (!isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] = [
            'name'     => $_POST['item_name'],
            'price'    => $_POST['item_price'],
            'quantity' => $qty
        ];
    } else {
        $_SESSION['cart'][$id]['quantity'] += $qty;
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        $cart_count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $cart_count += $item['quantity'];
        }
        echo json_encode(['success' => true, 'cart_count' => $cart_count]);
        exit;
    }

    header("Location: menu.php");
    exit();
}

$all_items = [];
$result = mysqli_query($conn, "SELECT * FROM menu_items");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $all_items[] = $row;
    }
}

$cart_count = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Menu | Lutong Nayon</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
body {
    margin: 0;
    font-family: Poppins, sans-serif;
    background: url('image/bannerrt.jpg') center/cover fixed;
    color: #fff;
}

/* header */
.header {
    text-align: center;
    padding: 25px;
}

/* navb */
.nav {
    max-width: 900px;
    margin: 15px auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* cGrid */
.cards {
    max-width: 900px;
    margin: auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

/* single-c */
.card {
    background: rgba(255,255,255,0.08);
    border-radius: 15px;
    overflow: hidden;
    position: relative;
}

.card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
}

/* price tags */
.price {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #10b981;
    padding: 6px 12px;
    border-radius: 12px;
    font-weight: 700;
}

/* card-c */
.card-content {
    padding: 15px;
}

button {
    background: #f97316;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

input {
    width: 55px;
}

a {
    color: #38bdf8;
    text-decoration: none;
    font-weight: 600;
}
</style>

<!-- add to cart(ajax) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.add-form').forEach(function(form){
        form.addEventListener('submit', function(e){
            e.preventDefault();
            const formData = new FormData(form);
            fetch('menu.php', {
                method: 'POST',
                headers: {'X-Requested-With':'XMLHttpRequest'},
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('cartCount').innerText = data.cart_count;
                    alert('Item added to cart!');
                }
            });
        });
    });
});
</script>

</head>
<body>

<!-- header -->
<div class="header">
    <h1>🍽 Menu</h1>
</div>

<!-- nav -->
<div class="nav">
    <span>🛒 <span id="cartCount"><?php echo $cart_count; ?></span></span>
    <a href="cart.php">Go to Cart</a>
</div>

<!-- menu items -->
<div class="cards">
<?php foreach($all_items as $row): ?>
    <div class="card">
        <div class="price">₱<?php echo number_format($row['price'],2); ?></div>
        <img src="image/<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
        <div class="card-content">
            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
            
            <!-- add to cart form -->
            <form class="add-form" method="post">
                <input type="hidden" name="add_to_cart" value="1">
                <input type="hidden" name="item_id" value="<?php echo $row['id']; ?>">
                <input type="hidden" name="item_name" value="<?php echo $row['name']; ?>">
                <input type="hidden" name="item_price" value="<?php echo $row['price']; ?>">
                <input type="number" name="quantity" value="1" min="1">
                <button type="submit">Add</button>
            </form>
        </div>
    </div>
<?php endforeach; ?>
</div>

</body>
</html>
