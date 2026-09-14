<?php

session_start();
require_once __DIR__ . "/db.php";

/* =========================================================
   LOGIN CHECK
========================================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* =========================================================
   PREVENT ADMIN ACCESS
========================================================= */

if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === "admin") {
    header("Location: admin_dashboard.php");
    exit();
}

/* =========================================================
   CART
========================================================= */

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* =========================================================
   AJAX REQUESTS
========================================================= */

if (
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
) {

    $uid = (int)$_SESSION['user_id'];

    /* =====================================================
       SET TABLE
    ===================================================== */

    if (isset($_POST['set_table'])) {

        $table = (int)($_POST['table_number'] ?? 0);

        if ($table < 1 || $table > 20) {
            echo json_encode([
                'success' => false,
                'message' => 'Please select a valid table.'
            ]);
            exit();
        }

        $stmt = mysqli_prepare(
            $conn,
            "SELECT COUNT(*) AS cnt
             FROM orders
             WHERE table_number = ?
             AND status = 'pending'"
        );

        mysqli_stmt_bind_param($stmt, "i", $table);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        if ((int)$row['cnt'] > 0) {

            echo json_encode([
                'success' => false,
                'message' => 'This table is already occupied.'
            ]);

        } else {

            $_SESSION['table_number'] = $table;

            echo json_encode([
                'success' => true,
                'table_number' => $table
            ]);
        }

        exit();
    }

    /* =====================================================
       UPDATE CART
    ===================================================== */

    if (isset($_POST['update_cart'])) {

        if (!empty($_POST['quantities'])) {

            foreach ($_POST['quantities'] as $id => $qty) {

                $id = (int)$id;
                $qty = max(1, (int)$qty);

                if (isset($_SESSION['cart'][$id])) {
                    $_SESSION['cart'][$id]['quantity'] = $qty;
                }
            }
        }

        $total_price = 0;
        $total_items = 0;

        foreach ($_SESSION['cart'] as $item) {

            $quantity = (int)$item['quantity'];
            $price = (float)$item['price'];

            $total_items += $quantity;
            $total_price += $price * $quantity;
        }

        echo json_encode([
            'success' => true,
            'cart_count' => $total_items,
            'total_price' => $total_price
        ]);

        exit();
    }

    /* =====================================================
       REMOVE ITEM
    ===================================================== */

    if (isset($_POST['remove'])) {

        $id = (int)$_POST['remove'];

        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
        }

        $total_items = 0;
        $total_price = 0;

        foreach ($_SESSION['cart'] as $item) {

            $quantity = (int)$item['quantity'];
            $price = (float)$item['price'];

            $total_items += $quantity;
            $total_price += $price * $quantity;
        }

        echo json_encode([
            'success' => true,
            'cart_count' => $total_items,
            'total_price' => $total_price
        ]);

        exit();
    }

    /* =====================================================
       CHECKOUT
    ===================================================== */

    if (isset($_POST['checkout_all'])) {

        if (!isset($_SESSION['table_number'])) {

            echo json_encode([
                'success' => false,
                'message' => 'Please select a table first.'
            ]);

            exit();
        }

        if (empty($_SESSION['cart'])) {

            echo json_encode([
                'success' => false,
                'message' => 'Your cart is empty.'
            ]);

            exit();
        }

        $table = (int)$_SESSION['table_number'];

        /* Check if table became occupied */

        $stmt = mysqli_prepare(
            $conn,
            "SELECT COUNT(*) AS cnt
             FROM orders
             WHERE table_number = ?
             AND status = 'pending'"
        );

        mysqli_stmt_bind_param($stmt, "i", $table);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        if ((int)$row['cnt'] > 0) {

            unset($_SESSION['table_number']);

            echo json_encode([
                'success' => false,
                'message' => 'This table is already occupied. Please select another table.'
            ]);

            exit();
        }

        /* Insert cart items */

        foreach ($_SESSION['cart'] as $id => $item) {

            $item_id = (int)$id;
            $quantity = max(1, (int)$item['quantity']);

            for ($i = 0; $i < $quantity; $i++) {

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO orders
                    (customer_id, item_id, table_number, status)
                    VALUES (?, ?, ?, 'pending')"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "iii",
                    $uid,
                    $item_id,
                    $table
                );

                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }

        /* Clear cart */

        $_SESSION['cart'] = [];

        unset($_SESSION['table_number']);

        /* Get occupied tables */

        $occupied_tables = [];

        $res = mysqli_query(
            $conn,
            "SELECT DISTINCT table_number
             FROM orders
             WHERE status = 'pending'"
        );

        if ($res) {

            while ($row = mysqli_fetch_assoc($res)) {

                $occupied_tables[] = (int)$row['table_number'];
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'Checkout successful!',
            'occupied_tables' => $occupied_tables,
            'cart_count' => 0,
            'total_price' => 0
        ]);

        exit();
    }

    exit();
}

/* =========================================================
   GET OCCUPIED TABLES
========================================================= */

$occupied_tables = [];

$res = mysqli_query(
    $conn,
    "SELECT DISTINCT table_number
     FROM orders
     WHERE status = 'pending'"
);

if ($res) {

    while ($row = mysqli_fetch_assoc($res)) {

        $occupied_tables[] = (int)$row['table_number'];
    }
}

/* =========================================================
   CHECK CURRENT TABLE
========================================================= */

if (
    isset($_SESSION['table_number']) &&
    in_array((int)$_SESSION['table_number'], $occupied_tables)
) {
    unset($_SESSION['table_number']);
}

/* =========================================================
   CART TOTALS
========================================================= */

$total_items = 0;
$total_price = 0;

foreach ($_SESSION['cart'] as $item) {

    $quantity = (int)$item['quantity'];
    $price = (float)$item['price'];

    $total_items += $quantity;
    $total_price += $price * $quantity;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, maximum-scale=1.0"
>

<title>Cart | Lutong Nayon</title>

<link
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
    rel="stylesheet"
>

<style>

/* =========================================================
   GLOBAL
========================================================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {

    font-family: 'Poppins', sans-serif;

    min-height: 100vh;

    color: #3b2617;

    background:
        linear-gradient(
            rgba(76, 48, 25, 0.32),
            rgba(76, 48, 25, 0.32)
        ),
        url('image/bannerrt.jpg')
        center center / cover fixed no-repeat;
}

/* =========================================================
   HEADER
========================================================= */

.header {

    width: 100%;

    padding: 34px 20px 30px;

    text-align: center;

    background:
        linear-gradient(
            rgba(35, 23, 13, 0.90),
            rgba(35, 23, 13, 0.72)
        );

    border-bottom:
        2px solid rgba(255,255,255,0.12);
}

.header h1 {

    color: #f97316;

    font-size: clamp(2rem, 6vw, 3.5rem);

    font-weight: 700;

    text-shadow:
        0 4px 15px rgba(0,0,0,0.45);
}

.header p {

    color: #fff;

    margin-top: 7px;

    font-size: 14px;

    opacity: 0.92;
}

/* =========================================================
   MAIN CONTAINER
========================================================= */

.main-container {

    width: min(1050px, 94%);

    margin: auto;

    padding: 25px 0 50px;
}

/* =========================================================
   NAVIGATION
========================================================= */

.navbar {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 12px;

    padding: 13px 16px;

    margin-bottom: 20px;

    background:
        rgba(31, 41, 55, 0.94);

    border:
        1px solid rgba(255,255,255,0.10);

    border-radius: 15px;

    box-shadow:
        0 8px 25px rgba(0,0,0,0.25);
}

.navbar a {

    text-decoration: none;

    color: #fff;

    font-size: 14px;

    font-weight: 500;

    padding: 9px 13px;

    border-radius: 9px;

    transition: 0.25s;
}

.navbar a:hover {

    background: rgba(255,255,255,0.10);

    color: #f97316;
}

.cart-status {

    display: flex;

    align-items: center;

    gap: 8px;

    color: #fff;

    font-size: 14px;

    font-weight: 600;
}

.cart-icon {

    width: 36px;

    height: 36px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #f97316;

    border-radius: 50%;

    font-size: 17px;
}

/* =========================================================
   PAGE TITLE
========================================================= */

.section-title {

    text-align: center;

    margin: 5px 0 22px;
}

.section-title h2 {

    color: #fff;

    font-size: clamp(1.4rem, 4vw, 2rem);

    text-shadow:
        0 3px 10px rgba(0,0,0,0.45);
}

.section-title p {

    color: #fff;

    font-size: 13px;

    opacity: 0.9;

    margin-top: 3px;
}

/* =========================================================
   CART BOX
========================================================= */

.cart-box {

    background:
        rgba(235, 207, 165, 0.95);

    border-radius: 20px;

    padding: 22px;

    box-shadow:
        0 12px 35px rgba(40,25,15,0.30);

    border:
        1px solid rgba(255,255,255,0.35);
}

/* =========================================================
   TABLE SECTION
========================================================= */

.table-section {

    background:
        rgba(255,255,255,0.45);

    padding: 18px;

    border-radius: 15px;

    margin-bottom: 20px;

    border:
        1px solid rgba(120,80,40,0.15);
}

.table-title {

    color: #382313;

    font-size: 16px;

    font-weight: 700;

    margin-bottom: 5px;
}

.table-description {

    color: #6b4b32;

    font-size: 12px;

    margin-bottom: 14px;
}

/* =========================================================
   TABLE GRID
========================================================= */

.table-grid {

    display: grid;

    grid-template-columns:
        repeat(10, 1fr);

    gap: 8px;
}

.table-grid button {

    min-height: 43px;

    border: none;

    border-radius: 9px;

    color: #fff;

    font-family: 'Poppins', sans-serif;

    font-size: 12px;

    font-weight: 700;

    cursor: pointer;

    transition:
        transform 0.2s,
        box-shadow 0.2s;
}

.table-grid button:hover:not(:disabled) {

    transform: translateY(-2px);

    box-shadow:
        0 4px 10px rgba(0,0,0,0.18);
}

.available {

    background: #10b981;
}

.occupied {

    background: #dc2626;

    cursor: not-allowed;
}

.selected {

    background: #f97316;

    box-shadow:
        0 0 0 3px rgba(249,115,22,0.25);
}

/* =========================================================
   TABLE ACTION
========================================================= */

.table-action {

    display: flex;

    align-items: center;

    gap: 12px;

    margin-top: 15px;

    flex-wrap: wrap;
}

.set-table-btn {

    border: none;

    background: #f97316;

    color: #fff;

    padding: 10px 18px;

    border-radius: 9px;

    font-family: 'Poppins', sans-serif;

    font-weight: 600;

    cursor: pointer;

    transition: 0.2s;
}

.set-table-btn:hover {

    background: #ea580c;

    transform: translateY(-1px);
}

.table-status {

    color: #4b3322;

    font-size: 13px;

    font-weight: 600;
}

/* =========================================================
   MESSAGE
========================================================= */

.message {

    display: none;

    padding: 12px 14px;

    border-radius: 10px;

    margin-bottom: 15px;

    font-size: 13px;

    font-weight: 600;
}

.message.success {

    display: block;

    color: #065f46;

    background: #d1fae5;
}

.message.error {

    display: block;

    color: #991b1b;

    background: #fee2e2;
}

/* =========================================================
   CART ITEMS
========================================================= */

.cart-items {

    display: flex;

    flex-direction: column;

    gap: 12px;
}

/* =========================================================
   CART CARD
========================================================= */

.cart-card {

    display: grid;

    grid-template-columns:
        minmax(180px, 2fr)
        1fr
        100px
        110px;

    align-items: center;

    gap: 15px;

    padding: 16px;

    background:
        rgba(255,255,255,0.62);

    border:
        1px solid rgba(120,80,40,0.14);

    border-radius: 14px;

    transition:
        transform 0.2s,
        box-shadow 0.2s;
}

.cart-card:hover {

    transform: translateY(-2px);

    box-shadow:
        0 7px 18px rgba(70,45,20,0.13);
}

/* =========================================================
   ITEM NAME
========================================================= */

.item-name {

    color: #382313;

    font-size: 15px;

    font-weight: 700;

    line-height: 1.3;
}

.item-label {

    display: block;

    color: #80664e;

    font-size: 11px;

    margin-top: 3px;

    font-weight: 400;
}

/* =========================================================
   PRICE
========================================================= */

.item-price {

    color: #6b4b32;

    font-size: 14px;

    font-weight: 600;
}

/* =========================================================
   QUANTITY
========================================================= */

.quantity-input {

    width: 70px;

    height: 40px;

    border:
        2px solid #c8a978;

    border-radius: 9px;

    background: #fff;

    color: #332015;

    text-align: center;

    font-family: 'Poppins', sans-serif;

    font-size: 14px;

    font-weight: 600;
}

.quantity-input:focus {

    outline: none;

    border-color: #f97316;
}

/* =========================================================
   REMOVE
========================================================= */

.remove-btn {

    border: none;

    background: #dc2626;

    color: #fff;

    padding: 9px 12px;

    border-radius: 8px;

    font-family: 'Poppins', sans-serif;

    font-size: 12px;

    font-weight: 600;

    cursor: pointer;

    transition: 0.2s;
}

.remove-btn:hover {

    background: #b91c1c;

    transform: translateY(-1px);
}

/* =========================================================
   EMPTY CART
========================================================= */

.empty-cart {

    text-align: center;

    padding: 45px 20px;

    background:
        rgba(255,255,255,0.45);

    border-radius: 14px;
}

.empty-cart-icon {

    font-size: 42px;

    margin-bottom: 8px;
}

.empty-cart h3 {

    color: #382313;

    font-size: 18px;

    margin-bottom: 5px;
}

.empty-cart p {

    color: #80664e;

    font-size: 13px;
}

/* =========================================================
   CART SUMMARY
========================================================= */

.cart-summary {

    margin-top: 20px;

    padding: 18px;

    background:
        rgba(56,35,19,0.94);

    border-radius: 15px;

    color: #fff;
}

.summary-top {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 10px;

    margin-bottom: 15px;
}

.summary-label {

    color: #ddd;

    font-size: 14px;
}

.total-price {

    color: #fff;

    font-size: 23px;

    font-weight: 700;
}

/* =========================================================
   ACTION BUTTONS
========================================================= */

.cart-actions {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 10px;
}

.update-btn,
.checkout-btn {

    border: none;

    padding: 12px;

    border-radius: 9px;

    color: #fff;

    font-family: 'Poppins', sans-serif;

    font-size: 14px;

    font-weight: 600;

    cursor: pointer;

    transition: 0.2s;
}

.update-btn {

    background: #2563eb;
}

.update-btn:hover {

    background: #1d4ed8;

    transform: translateY(-1px);
}

.checkout-btn {

    background: #10b981;
}

.checkout-btn:hover {

    background: #059669;

    transform: translateY(-1px);
}

/* =========================================================
   POPUP
========================================================= */

#cart-popup {

    position: fixed;

    left: 50%;

    bottom: 25px;

    transform: translateX(-50%) translateY(20px);

    z-index: 9999;

    display: none;

    background: #10b981;

    color: #fff;

    padding: 12px 20px;

    border-radius: 11px;

    font-size: 13px;

    font-weight: 600;

    box-shadow:
        0 8px 25px rgba(0,0,0,0.3);
}

/* =========================================================
   FOOTER
========================================================= */

.footer {

    text-align: center;

    padding: 22px 15px;

    background:
        rgba(35,23,13,0.78);

    color: #eee;

    font-size: 12px;
}

/* =========================================================
   TABLET
========================================================= */

@media (max-width: 850px) {

    .table-grid {

        grid-template-columns:
            repeat(5, 1fr);
    }

    .cart-card {

        grid-template-columns:
            2fr 1fr 90px 100px;
    }
}

/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 600px) {

    body {

        background-attachment: scroll;
    }

    .header {

        padding: 27px 15px 24px;
    }

    .header h1 {

        font-size: 2rem;
    }

    .header p {

        font-size: 12px;
    }

    .main-container {

        width: 92%;

        padding:
            16px 0 35px;
    }

    /* -------------------------------
       MOBILE NAVBAR
    -------------------------------- */

    .navbar {

        display: grid;

        grid-template-columns:
            1fr 1fr;

        gap: 8px;

        padding: 10px;

        border-radius: 13px;
    }

    .cart-status {

        padding-left: 4px;

        font-size: 12px;
    }

    .cart-icon {

        width: 32px;

        height: 32px;

        font-size: 15px;
    }

    .navbar a {

        text-align: center;

        font-size: 12px;

        padding: 8px 6px;
    }

    .navbar a:last-child {

        grid-column:
            1 / -1;

        background:
            rgba(255,255,255,0.08);
    }

    /* -------------------------------
       TITLE
    -------------------------------- */

    .section-title {

        margin-bottom: 15px;
    }

    .section-title h2 {

        font-size: 1.45rem;
    }

    .section-title p {

        font-size: 11px;
    }

    /* -------------------------------
       CART BOX
    -------------------------------- */

    .cart-box {

        padding: 13px;

        border-radius: 16px;
    }

    /* -------------------------------
       TABLE SECTION
    -------------------------------- */

    .table-section {

        padding: 13px;

        border-radius: 12px;
    }

    .table-title {

        font-size: 14px;
    }

    .table-description {

        font-size: 11px;
    }

    /* -------------------------------
       TABLE GRID
    -------------------------------- */

    .table-grid {

        grid-template-columns:
            repeat(4, 1fr);

        gap: 7px;
    }

    .table-grid button {

        min-height: 39px;

        font-size: 11px;

        border-radius: 8px;
    }

    .table-action {

        flex-direction: column;

        align-items: stretch;

        gap: 8px;
    }

    .set-table-btn {

        width: 100%;

        padding: 10px;
    }

    .table-status {

        text-align: center;

        font-size: 11px;
    }

    /* -------------------------------
       CART CARD
    -------------------------------- */

    .cart-items {

        gap: 10px;
    }

    .cart-card {

        display: grid;

        grid-template-columns:
            1fr auto;

        gap: 10px;

        padding: 14px;

        border-radius: 13px;
    }

    .item-name {

        font-size: 14px;
    }

    .item-label {

        font-size: 10px;
    }

    .item-price {

        grid-column:
            1 / 2;

        font-size: 13px;
    }

    .quantity-area {

        grid-column:
            2 / 3;

        grid-row:
            1 / 3;

        display: flex;

        align-items: center;

        justify-content: center;
    }

    .quantity-input {

        width: 58px;

        height: 38px;
    }

    .remove-area {

        grid-column:
            1 / -1;
    }

    .remove-btn {

        width: 100%;

        padding: 9px;
    }

    /* -------------------------------
       SUMMARY
    -------------------------------- */

    .cart-summary {

        padding: 14px;

        border-radius: 13px;
    }

    .summary-top {

        margin-bottom: 12px;
    }

    .summary-label {

        font-size: 12px;
    }

    .total-price {

        font-size: 20px;
    }

    .cart-actions {

        grid-template-columns:
            1fr;

        gap: 8px;
    }

    .update-btn,
    .checkout-btn {

        width: 100%;

        padding: 11px;
    }

    /* -------------------------------
       POPUP
    -------------------------------- */

    #cart-popup {

        left: 12px;

        right: 12px;

        bottom: 18px;

        transform:
            translateY(20px);

        text-align: center;

        border-radius: 10px;
    }

    .footer {

        font-size: 10px;

        padding: 18px 10px;
    }
}

/* =========================================================
   VERY SMALL PHONES
========================================================= */

@media (max-width: 380px) {

    .main-container {

        width: 94%;
    }

    .header h1 {

        font-size: 1.75rem;
    }

    .header p {

        font-size: 11px;
    }

    .table-grid {

        grid-template-columns:
            repeat(4, 1fr);
    }

    .table-grid button {

        min-height: 36px;

        font-size: 10px;
    }

    .cart-box {

        padding: 10px;
    }

    .cart-card {

        padding: 12px;
    }

    .item-name {

        font-size: 13px;
    }
}

</style>

</head>

<body>

<!-- ======================================================
     HEADER
====================================================== -->

<header class="header">

    <h1>🛒 Your Cart</h1>

    <p>
        Review your order before checking out
    </p>

</header>


<main class="main-container">

<!-- ======================================================
     NAVIGATION
====================================================== -->

<nav class="navbar">

    <div class="cart-status">

        <div class="cart-icon">
            🛒
        </div>

        <span id="navbar-cart-count">
            <?= $total_items ?>
            <?= $total_items == 1 ? 'item' : 'items' ?>
        </span>

    </div>


    <a href="user_dashboard.php">
        🍽 Back to Menu
    </a>


    <a href="logout.php">
        Logout
    </a>

</nav>


<!-- ======================================================
     TITLE
====================================================== -->

<div class="section-title">

    <h2>
        Your Order
    </h2>

    <p>
        Select your table and review your selected dishes
    </p>

</div>


<!-- ======================================================
     CART BOX
====================================================== -->

<div class="cart-box">


<!-- ======================================================
     TABLE SELECTION
====================================================== -->

<section class="table-section">

    <div class="table-title">
        🪑 Choose Your Table
    </div>

    <div class="table-description">
        Green = Available &nbsp; • &nbsp;
        Orange = Selected &nbsp; • &nbsp;
        Red = Occupied
    </div>


    <div class="table-grid">

        <?php

        for ($i = 1; $i <= 20; $i++):

            $isOccupied =
                in_array($i, $occupied_tables);

            $selected =
                isset($_SESSION['table_number']) &&
                (int)$_SESSION['table_number'] === $i &&
                !$isOccupied;

            $status_class = 'available';

            if ($isOccupied) {
                $status_class = 'occupied';
            }

            if ($selected) {
                $status_class = 'selected';
            }

        ?>

            <button
                type="button"
                id="table-<?= $i ?>"
                class="<?= $status_class ?>"
                <?= ($isOccupied && !$selected) ? 'disabled' : '' ?>
                onclick="selectTable(<?= $i ?>)"
            >
                T<?= $i ?>
            </button>

        <?php endfor; ?>

    </div>


    <input
        type="hidden"
        id="table_number_input"
        value="<?= isset($_SESSION['table_number']) ? (int)$_SESSION['table_number'] : '' ?>"
    >


    <div class="table-action">

        <button
            type="button"
            class="set-table-btn"
            onclick="ajaxSetTable()"
        >
            ✓ Set Table
        </button>


        <span
            id="current_table_status"
            class="table-status"
        >

            <?php if (isset($_SESSION['table_number'])): ?>

                ✓ Table
                <?= (int)$_SESSION['table_number'] ?>
                is set

            <?php else: ?>

                No table selected

            <?php endif; ?>

        </span>

    </div>

</section>


<!-- ======================================================
     CHECKOUT MESSAGE
====================================================== -->

<div
    id="checkout_message"
    class="message"
></div>


<!-- ======================================================
     CART ITEMS
====================================================== -->

<div
    id="cart_items"
    class="cart-items"
>

<?php if (!empty($_SESSION['cart'])): ?>


    <?php foreach ($_SESSION['cart'] as $id => $item): ?>

        <?php

        $itemName =
            htmlspecialchars(
                $item['name'],
                ENT_QUOTES,
                'UTF-8'
            );

        $itemPrice =
            (float)$item['price'];

        $itemQuantity =
            (int)$item['quantity'];

        ?>

        <div
            class="cart-card"
            data-item-id="<?= (int)$id ?>"
        >


            <!-- ITEM NAME -->

            <div>

                <div class="item-name">

                    <?= $itemName ?>

                </div>

                <span class="item-label">
                    Filipino dish
                </span>

            </div>


            <!-- PRICE -->

            <div class="item-price">

                ₱<?= number_format(
                    $itemPrice,
                    2
                ) ?>

            </div>


            <!-- QUANTITY -->

            <div class="quantity-area">

                <input
                    type="number"
                    class="quantity-input"
                    name="quantities[<?= (int)$id ?>]"
                    value="<?= $itemQuantity ?>"
                    min="1"
                    max="99"
                    aria-label="Quantity for <?= $itemName ?>"
                >

            </div>


            <!-- REMOVE -->

            <div class="remove-area">

                <button
                    class="remove-btn"
                    type="button"
                    onclick="ajaxRemoveItem(<?= (int)$id ?>, this)"
                >
                    🗑 Remove
                </button>

            </div>


        </div>

    <?php endforeach; ?>


    <!-- ==================================================
         SUMMARY
    ================================================== -->

    <div class="cart-summary">

        <div class="summary-top">

            <span class="summary-label">
                Total Order
            </span>

            <span
                id="total_price"
                class="total-price"
            >
                ₱<?= number_format(
                    $total_price,
                    2
                ) ?>
            </span>

        </div>


        <div class="cart-actions">

            <button
                type="button"
                class="update-btn"
                onclick="ajaxUpdateCart()"
            >
                ↻ Update Cart
            </button>


            <button
                type="button"
                class="checkout-btn"
                onclick="ajaxCheckout()"
            >
                ✓ Checkout Order
            </button>

        </div>

    </div>


<?php else: ?>


    <!-- ==================================================
         EMPTY CART
    ================================================== -->

    <div class="empty-cart">

        <div class="empty-cart-icon">
            🛒
        </div>

        <h3>
            Your cart is empty
        </h3>

        <p>
            Choose some delicious Filipino dishes from our menu.
        </p>

    </div>


<?php endif; ?>

</div>

</div>

</main>


<!-- ======================================================
     SUCCESS POPUP
====================================================== -->

<div id="cart-popup">
    ✓ Cart updated successfully!
</div>


<!-- ======================================================
     FOOTER
====================================================== -->

<footer class="footer">

    © <?= date('Y') ?>
    Lutong Nayon.
    All rights reserved.

</footer>


<script>

/* =========================================================
   SELECT TABLE
========================================================= */

function selectTable(table) {

    document
        .querySelectorAll('.table-grid button')
        .forEach(function(button) {

            if (!button.disabled) {

                button.classList.remove('selected');

            }

        });


    const button =
        document.getElementById('table-' + table);


    if (button && !button.disabled) {

        button.classList.add('selected');

    }


    const input =
        document.getElementById(
            'table_number_input'
        );


    if (input) {

        input.value = table;

    }

}


/* =========================================================
   AJAX POST
========================================================= */

function ajaxPost(data, callback) {

    fetch(
        'cart.php',
        {
            method: 'POST',

            headers: {
                'X-Requested-With':
                    'XMLHttpRequest'
            },

            body: data
        }
    )
    .then(function(response) {

        if (!response.ok) {

            throw new Error(
                'Server returned an error.'
            );

        }

        return response.json();

    })
    .then(function(data) {

        callback(data);

    })
    .catch(function(error) {

        console.error(
            'AJAX Error:',
            error
        );

        alert(
            'Something went wrong. Please refresh the page and try again.'
        );

    });

}


/* =========================================================
   SET TABLE
========================================================= */

function ajaxSetTable() {

    const input =
        document.getElementById(
            'table_number_input'
        );


    if (!input || !input.value) {

        alert(
            'Please select a table first.'
        );

        return;

    }


    const data =
        new FormData();


    data.append(
        'set_table',
        '1'
    );


    data.append(
        'table_number',
        input.value
    );


    ajaxPost(
        data,
        function(response) {

            const status =
                document.getElementById(
                    'current_table_status'
                );


            if (response.success) {

                status.textContent =
                    '✓ Table ' +
                    response.table_number +
                    ' is set';

            } else {

                status.textContent =
                    response.message;

                alert(
                    response.message
                );

            }

        }
    );

}


/* =========================================================
   UPDATE CART
========================================================= */

function ajaxUpdateCart() {

    const quantities = {};


    document
        .querySelectorAll(
            '.quantity-input'
        )
        .forEach(function(input) {

            const match =
                input.name.match(
                    /quantities\[(\d+)\]/
                );


            if (match) {

                quantities[match[1]] =
                    input.value;

            }

        });


    const data =
        new FormData();


    data.append(
        'update_cart',
        '1'
    );


    for (
        const id in quantities
    ) {

        data.append(
            'quantities[' + id + ']',
            quantities[id]
        );

    }


    ajaxPost(
        data,
        function(response) {

            if (response.success) {

                updateCartCount(
                    response.cart_count
                );


                document
                    .getElementById(
                        'total_price'
                    )
                    .textContent =
                    '₱' +
                    parseFloat(
                        response.total_price
                    ).toFixed(2);


                showPopup(
                    '✓ Cart updated successfully!'
                );

            }

        }
    );

}


/* =========================================================
   REMOVE ITEM
========================================================= */

function ajaxRemoveItem(id, button) {

    const data =
        new FormData();


    data.append(
        'remove',
        id
    );


    ajaxPost(
        data,
        function(response) {

            if (response.success) {

                const card =
                    button.closest(
                        '.cart-card'
                    );


                if (card) {

                    card.remove();

                }


                updateCartCount(
                    response.cart_count
                );


                const total =
                    document.getElementById(
                        'total_price'
                    );


                if (total) {

                    total.textContent =
                        '₱' +
                        parseFloat(
                            response.total_price
                        ).toFixed(2);

                }


                if (
                    response.cart_count == 0
                ) {

                    document
                        .getElementById(
                            'cart_items'
                        )
                        .innerHTML = `

                            <div class="empty-cart">

                                <div class="empty-cart-icon">
                                    🛒
                                </div>

                                <h3>
                                    Your cart is empty
                                </h3>

                                <p>
                                    Choose some delicious Filipino dishes from our menu.
                                </p>

                            </div>

                    `;

                }


                showPopup(
                    '✓ Item removed from cart'
                );

            }

        }
    );

}


/* =========================================================
   CHECKOUT
========================================================= */

function ajaxCheckout() {

    const data =
        new FormData();


    data.append(
        'checkout_all',
        '1'
    );


    ajaxPost(
        data,
        function(response) {

            const message =
                document.getElementById(
                    'checkout_message'
                );


            if (response.success) {

                message.textContent =
                    response.message;

                message.className =
                    'message success';


                /* Update occupied tables */

                document
                    .querySelectorAll(
                        '.table-grid button'
                    )
                    .forEach(function(button) {

                        const tableNumber =
                            parseInt(
                                button.textContent
                                    .replace('T', '')
                            );


                        if (
                            response
                                .occupied_tables
                                .includes(
                                    tableNumber
                                )
                        ) {

                            button
                                .classList
                                .remove(
                                    'available',
                                    'selected'
                                );

                            button
                                .classList
                                .add(
                                    'occupied'
                                );

                            button.disabled =
                                true;

                        }

                    });


                /* Clear cart */

                document
                    .getElementById(
                        'cart_items'
                    )
                    .innerHTML = `

                        <div class="empty-cart">

                            <div class="empty-cart-icon">
                                ✓
                            </div>

                            <h3>
                                Order placed successfully!
                            </h3>

                            <p>
                                Thank you for ordering from Lutong Nayon.
                            </p>

                        </div>

                `;


                updateCartCount(0);


                const total =
                    document.getElementById(
                        'total_price'
                    );


                if (total) {

                    total.textContent =
                        '₱0.00';

                }


                const tableStatus =
                    document.getElementById(
                        'current_table_status'
                    );


                if (tableStatus) {

                    tableStatus.textContent =
                        'No table selected';

                }


                const tableInput =
                    document.getElementById(
                        'table_number_input'
                    );


                if (tableInput) {

                    tableInput.value = '';

                }


                showPopup(
                    '✓ Checkout successful!'
                );

            } else {

                message.textContent =
                    response.message;

                message.className =
                    'message error';


                alert(
                    response.message
                );

            }

        }
    );

}


/* =========================================================
   UPDATE CART COUNT
========================================================= */

function updateCartCount(count) {

    const element =
        document.getElementById(
            'navbar-cart-count'
        );


    if (!element) {
        return;
    }


    element.textContent =
        count +
        (
            count === 1
                ? ' item'
                : ' items'
        );

}


/* =========================================================
   POPUP
========================================================= */

function showPopup(text) {

    const popup =
        document.getElementById(
            'cart-popup'
        );


    popup.textContent =
        text;


    popup.style.display =
        'block';


    setTimeout(
        function() {

            popup.style.display =
                'none';

        },
        1700
    );

}

</script>

</body>

</html>