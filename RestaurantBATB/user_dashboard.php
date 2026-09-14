<?php

session_start();
require_once __DIR__ . "/db.php";

/* =========================================================
   CHECK LOGIN
========================================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* =========================================================
   PREVENT ADMIN FROM ACCESSING USER DASHBOARD
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
   ADD TO CART
========================================================= */

if (isset($_POST['add_to_cart'])) {

    $id = (int)($_POST['item_id'] ?? 0);
    $qty = max(1, (int)($_POST['quantity'] ?? 1));

    $name = trim($_POST['item_name'] ?? '');
    $price = (float)($_POST['item_price'] ?? 0);

    if ($id > 0 && $name !== '' && $price >= 0) {

        if (!isset($_SESSION['cart'][$id])) {

            $_SESSION['cart'][$id] = [
                "name" => $name,
                "price" => $price,
                "quantity" => $qty
            ];

        } else {

            $_SESSION['cart'][$id]['quantity'] += $qty;
        }
    }

    /* =====================================================
       AJAX RESPONSE
    ===================================================== */

    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) {

        $cartCount = 0;

        foreach ($_SESSION['cart'] as $item) {
            $cartCount += (int)$item['quantity'];
        }

        header('Content-Type: application/json');

        echo json_encode([
            "success" => true,
            "cart_count" => $cartCount
        ]);

        exit();
    }

    header("Location: user_dashboard.php");
    exit();
}

/* =========================================================
   GET MENU ITEMS
========================================================= */

$sql = "SELECT * FROM menu_items ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

$all_items = [];

if ($result) {
    $all_items = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/* =========================================================
   CART COUNT
========================================================= */

$cart_count = 0;

foreach ($_SESSION['cart'] as $item) {
    $cart_count += (int)$item['quantity'];
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

    <title>Menu | Lutong Nayon</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>

        /* =====================================================
           RESET
        ===================================================== */

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

            color: #ffffff;

            background:
                linear-gradient(
                    rgba(76, 48, 25, 0.30),
                    rgba(76, 48, 25, 0.30)
                ),
                url('image/bannerrt.jpg')
                center center / cover fixed no-repeat;

            overflow-x: hidden;
        }


        /* =====================================================
           HEADER
        ===================================================== */

        .header {

            width: 100%;

            padding: 32px 20px 28px;

            text-align: center;

            background:
                linear-gradient(
                    rgba(35, 23, 13, 0.90),
                    rgba(35, 23, 13, 0.68)
                );

            border-bottom:
                1px solid rgba(255,255,255,0.12);

            box-shadow:
                0 5px 20px rgba(0,0,0,0.20);
        }

        .header h1 {

            font-size: clamp(2rem, 5vw, 4rem);

            font-weight: 700;

            line-height: 1.15;

            color: #f97316;

            text-shadow:
                0 4px 15px rgba(0,0,0,0.50);

            margin-bottom: 8px;
        }

        .header p {

            font-size: clamp(0.85rem, 2vw, 1.2rem);

            font-weight: 400;

            color: #ffffff;

            opacity: 0.95;
        }


        /* =====================================================
           MAIN CONTAINER
        ===================================================== */

        .main-container {

            width: min(1200px, 94%);

            margin: 0 auto;

            padding: 28px 0 50px;
        }


        /* =====================================================
           NAVIGATION
        ===================================================== */

        .navbar {

            width: 100%;

            display: grid;

            grid-template-columns: 1fr auto 1fr;

            align-items: center;

            gap: 12px;

            padding: 12px 15px;

            margin-bottom: 28px;

            background:
                rgba(31, 41, 55, 0.94);

            border:
                1px solid rgba(255,255,255,0.10);

            border-radius: 15px;

            box-shadow:
                0 8px 25px rgba(0,0,0,0.28);

            backdrop-filter: blur(8px);
        }


        /* =====================================================
           CART STATUS
        ===================================================== */

        .cart-status {

            display: flex;

            align-items: center;

            gap: 8px;

            font-size: 15px;

            font-weight: 500;
        }

        .cart-icon {

            width: 38px;

            height: 38px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #f97316;

            border-radius: 50%;

            font-size: 18px;

            flex-shrink: 0;
        }

        .cart-count {
            color: #ffffff;
            white-space: nowrap;
        }


        /* =====================================================
           CART BUTTON
        ===================================================== */

        .cart-link {

            display: inline-flex;

            justify-content: center;

            align-items: center;

            gap: 6px;

            padding: 10px 22px;

            background: #f97316;

            color: #ffffff;

            text-decoration: none;

            border-radius: 10px;

            font-weight: 600;

            font-size: 14px;

            transition: all 0.25s ease;
        }

        .cart-link:hover {

            background: #ea580c;

            transform: translateY(-2px);

            box-shadow:
                0 5px 12px rgba(0,0,0,0.20);
        }


        /* =====================================================
           LOGOUT
        ===================================================== */

        .logout-link {

            justify-self: end;

            padding: 9px 16px;

            color: #ffffff;

            text-decoration: none;

            font-weight: 500;

            border-radius: 9px;

            transition: all 0.25s ease;
        }

        .logout-link:hover {

            background:
                rgba(255,255,255,0.10);

            color: #f97316;
        }


        /* =====================================================
           MENU HEADING
        ===================================================== */

        .menu-heading {

            text-align: center;

            margin-bottom: 24px;
        }

        .menu-heading h2 {

            font-size: clamp(1.6rem, 4vw, 2.3rem);

            font-weight: 700;

            color: #ffffff;

            text-shadow:
                0 3px 10px rgba(0,0,0,0.50);
        }

        .menu-heading p {

            margin-top: 5px;

            color: #ffffff;

            font-size: 14px;

            opacity: 0.92;
        }


        /* =====================================================
           MENU GRID - DESKTOP
        ===================================================== */

        .card-container {

            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: 24px;
        }


        /* =====================================================
           MENU CARD
        ===================================================== */

        .card {

            position: relative;

            overflow: hidden;

            display: flex;

            flex-direction: column;

            background:
                rgba(235, 207, 165, 0.95);

            border-radius: 18px;

            border:
                1px solid rgba(255,255,255,0.35);

            box-shadow:
                0 8px 22px rgba(55, 35, 18, 0.30);

            transition:
                transform 0.25s ease,
                box-shadow 0.25s ease;
        }

        .card:hover {

            transform: translateY(-5px);

            box-shadow:
                0 15px 35px rgba(35, 20, 10, 0.38);
        }


        /* =====================================================
           IMAGE
        ===================================================== */

        .card-image-wrapper {

            position: relative;

            width: 100%;

            aspect-ratio: 1.55 / 1;

            overflow: hidden;

            background: #c9a878;
        }

        .card img {

            width: 100%;

            height: 100%;

            object-fit: cover;

            display: block;

            transition:
                transform 0.4s ease;
        }

        .card:hover img {

            transform: scale(1.04);
        }


        /* =====================================================
           PRICE BADGE
        ===================================================== */

        .price-badge {

            position: absolute;

            top: 12px;

            right: 12px;

            z-index: 2;

            background: #10b981;

            color: #ffffff;

            padding: 7px 12px;

            border-radius: 11px;

            font-size: 15px;

            font-weight: 700;

            box-shadow:
                0 4px 10px rgba(0,0,0,0.25);
        }


        /* =====================================================
           CARD CONTENT
        ===================================================== */

        .card-content {

            padding: 15px 16px 16px;

            display: flex;

            flex-direction: column;

            min-height: 115px;
        }

        .card h3 {

            color: #382313;

            font-size: 1.18rem;

            font-weight: 700;

            line-height: 1.25;

            margin-bottom: 13px;
        }


        /* =====================================================
           ADD TO CART FORM
        ===================================================== */

        .card form {

            margin-top: auto;

            display: flex;

            align-items: center;

            gap: 8px;
        }


        /* =====================================================
           QUANTITY
        ===================================================== */

        .card input[type="number"] {

            width: 60px;

            height: 41px;

            padding: 5px;

            border:
                2px solid #c8a978;

            border-radius: 9px;

            background: #ffffff;

            color: #332015;

            text-align: center;

            font-family: 'Poppins', sans-serif;

            font-size: 14px;

            font-weight: 600;

            flex-shrink: 0;
        }

        .card input[type="number"]:focus {

            outline: none;

            border-color: #f97316;

            box-shadow:
                0 0 0 2px rgba(249,115,22,0.12);
        }


        /* =====================================================
           ADD BUTTON
        ===================================================== */

        .add-button {

            flex: 1;

            height: 41px;

            border: none;

            border-radius: 9px;

            background: #f97316;

            color: #ffffff;

            font-family: 'Poppins', sans-serif;

            font-size: 14px;

            font-weight: 600;

            cursor: pointer;

            transition: all 0.25s ease;
        }

        .add-button:hover {

            background: #ea580c;

            transform: translateY(-1px);
        }

        .add-button:active {

            transform: scale(0.97);
        }


        /* =====================================================
           EMPTY MENU
        ===================================================== */

        .no-menu {

            grid-column: 1 / -1;

            padding: 50px 20px;

            text-align: center;

            background:
                rgba(40, 25, 15, 0.80);

            border-radius: 16px;
        }

        .no-menu h3 {

            font-size: 1.4rem;

            margin-bottom: 8px;
        }

        .no-menu p {

            color: #dddddd;
        }


        /* =====================================================
           CART POPUP
        ===================================================== */

        #cart-popup {

            position: fixed;

            top: 22px;

            right: 22px;

            z-index: 9999;

            display: none;

            background: #10b981;

            color: #ffffff;

            padding: 13px 20px;

            border-radius: 12px;

            font-size: 14px;

            font-weight: 600;

            box-shadow:
                0 8px 25px rgba(0,0,0,0.30);

            animation:
                popupIn 0.25s ease;
        }

        @keyframes popupIn {

            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }


        /* =====================================================
           FOOTER
        ===================================================== */

        .footer {

            text-align: center;

            padding: 24px 15px;

            background:
                rgba(35, 23, 13, 0.78);

            color: #eeeeee;

            font-size: 13px;
        }


        /* =====================================================
           TABLET
        ===================================================== */

        @media (max-width: 950px) {

            .card-container {

                grid-template-columns:
                    repeat(2, minmax(0, 1fr));

                gap: 18px;
            }

            .card-image-wrapper {

                aspect-ratio: 1.45 / 1;
            }

            .main-container {

                width: 94%;
            }
        }


        /* =====================================================
           MOBILE
           2 CARDS PER ROW
        ===================================================== */

        @media (max-width: 600px) {

            body {

                background-attachment: scroll;
            }

            .header {

                padding: 25px 12px 22px;
            }

            .header h1 {

                font-size: 2rem;

                line-height: 1.15;
            }

            .header p {

                font-size: 0.82rem;

                line-height: 1.4;
            }

            .main-container {

                width: 94%;

                padding:
                    17px 0 35px;
            }


            /* ===============================
               MOBILE NAVBAR
            =============================== */

            .navbar {

                grid-template-columns:
                    1fr auto;

                gap: 8px;

                padding: 10px;

                margin-bottom: 22px;

                border-radius: 13px;
            }

            .cart-status {

                font-size: 13px;
            }

            .cart-icon {

                width: 34px;

                height: 34px;

                font-size: 16px;
            }

            .cart-link {

                padding: 9px 12px;

                font-size: 12px;

                white-space: nowrap;
            }

            .logout-link {

                grid-column: 1 / -1;

                width: 100%;

                text-align: center;

                padding: 8px;

                background:
                    rgba(255,255,255,0.07);

                font-size: 12px;
            }


            /* ===============================
               MENU TITLE
            =============================== */

            .menu-heading {

                margin-bottom: 17px;
            }

            .menu-heading h2 {

                font-size: 1.55rem;
            }

            .menu-heading p {

                font-size: 11px;
            }


            /* ===============================
               TWO COLUMNS ON MOBILE
            =============================== */

            .card-container {

                grid-template-columns:
                    repeat(2, minmax(0, 1fr));

                gap: 12px;
            }


            /* ===============================
               MOBILE CARD
            =============================== */

            .card {

                border-radius: 13px;

                box-shadow:
                    0 5px 15px rgba(55, 35, 18, 0.28);
            }

            .card:hover {

                transform: none;
            }


            /* ===============================
               MOBILE IMAGE
            =============================== */

            .card-image-wrapper {

                aspect-ratio: 1 / 0.92;

                height: auto;
            }

            .card:hover img {

                transform: none;
            }


            /* ===============================
               MOBILE PRICE
            =============================== */

            .price-badge {

                top: 7px;

                right: 7px;

                padding: 5px 8px;

                border-radius: 8px;

                font-size: 11px;
            }


            /* ===============================
               MOBILE CONTENT
            =============================== */

            .card-content {

                padding:
                    10px 10px 11px;

                min-height: 92px;
            }

            .card h3 {

                font-size: 0.88rem;

                line-height: 1.2;

                margin-bottom: 9px;

                min-height: 34px;

                display: flex;

                align-items: center;
            }


            /* ===============================
               MOBILE FORM
            =============================== */

            .card form {

                gap: 5px;
            }

            .card input[type="number"] {

                width: 43px;

                height: 34px;

                border-radius: 7px;

                font-size: 12px;

                padding: 2px;
            }

            .add-button {

                height: 34px;

                border-radius: 7px;

                font-size: 11px;

                padding: 0 5px;
            }


            /* ===============================
               MOBILE POPUP
            =============================== */

            #cart-popup {

                top: 12px;

                left: 12px;

                right: 12px;

                text-align: center;

                padding: 11px 14px;

                font-size: 12px;

                border-radius: 10px;
            }


            /* ===============================
               FOOTER
            =============================== */

            .footer {

                padding: 20px 10px;

                font-size: 11px;
            }
        }


        /* =====================================================
           VERY SMALL PHONES
           STILL 2 COLUMNS
        ===================================================== */

        @media (max-width: 380px) {

            .header h1 {

                font-size: 1.7rem;
            }

            .header p {

                font-size: 0.75rem;
            }

            .main-container {

                width: 95%;
            }

            .card-container {

                gap: 9px;
            }

            .card {

                border-radius: 11px;
            }

            .card-image-wrapper {

                aspect-ratio: 1 / 0.90;
            }

            .card-content {

                padding:
                    8px 8px 9px;
            }

            .card h3 {

                font-size: 0.78rem;

                min-height: 31px;
            }

            .price-badge {

                font-size: 10px;

                padding: 4px 6px;
            }

            .card input[type="number"] {

                width: 38px;

                height: 31px;

                font-size: 11px;
            }

            .add-button {

                height: 31px;

                font-size: 10px;
            }
        }

    </style>

</head>

<body>


<!-- =====================================================
     HEADER
===================================================== -->

<header class="header">

    <h1>
        Menus of Lutong Nayon
    </h1>

    <p>
        Home-cooked Filipino dishes made with love
    </p>

</header>


<!-- =====================================================
     MAIN
===================================================== -->

<main class="main-container">


    <!-- =================================================
         NAVIGATION
    ================================================== -->

    <nav class="navbar">


        <!-- CART COUNT -->

        <div class="cart-status">

            <div class="cart-icon">
                🛒
            </div>

            <span
                class="cart-count"
                id="cart-count"
            >
                <?= $cart_count ?>
                <?= $cart_count == 1 ? 'item' : 'items' ?>
            </span>

        </div>


        <!-- GO TO CART -->

        <a
            href="cart.php"
            class="cart-link"
        >
            🛒 Go to Cart
        </a>


        <!-- LOGOUT -->

        <a
            href="logout.php"
            class="logout-link"
        >
            Logout
        </a>

    </nav>


    <!-- =================================================
         MENU HEADING
    ================================================== -->

    <div class="menu-heading">

        <h2>
            Our Menu
        </h2>

        <p>
            Choose your favorite Filipino dishes
        </p>

    </div>


    <!-- =================================================
         MENU CARDS
    ================================================== -->

    <div class="card-container">


        <?php if (!empty($all_items)): ?>


            <?php foreach ($all_items as $row): ?>


                <div class="card">


                    <!-- FOOD IMAGE -->

                    <div class="card-image-wrapper">

                        <img
                            src="image/<?= htmlspecialchars(
                                $row['image'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>"
                            alt="<?= htmlspecialchars(
                                $row['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>"
                        >


                        <!-- PRICE -->

                        <div class="price-badge">

                            ₱<?= number_format(
                                (float)$row['price'],
                                2
                            ); ?>

                        </div>

                    </div>


                    <!-- CARD CONTENT -->

                    <div class="card-content">


                        <!-- FOOD NAME -->

                        <h3>

                            <?= htmlspecialchars(
                                $row['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>

                        </h3>


                        <!-- ADD TO CART -->

                        <form
                            method="post"
                            class="add-to-cart-form"
                        >

                            <input
                                type="hidden"
                                name="add_to_cart"
                                value="1"
                            >

                            <input
                                type="hidden"
                                name="item_id"
                                value="<?= (int)$row['id']; ?>"
                            >

                            <input
                                type="hidden"
                                name="item_name"
                                value="<?= htmlspecialchars(
                                    $row['name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>"
                            >

                            <input
                                type="hidden"
                                name="item_price"
                                value="<?= htmlspecialchars(
                                    $row['price'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>"
                            >


                            <!-- QUANTITY -->

                            <input
                                type="number"
                                name="quantity"
                                value="1"
                                min="1"
                                max="99"
                                aria-label="Quantity"
                            >


                            <!-- ADD BUTTON -->

                            <button
                                type="submit"
                                class="add-button"
                            >
                                Add
                            </button>

                        </form>

                    </div>

                </div>


            <?php endforeach; ?>


        <?php else: ?>


            <div class="no-menu">

                <h3>
                    No menu items available
                </h3>

                <p>
                    Please check back later.
                </p>

            </div>


        <?php endif; ?>


    </div>

</main>


<!-- =====================================================
     SUCCESS POPUP
===================================================== -->

<div id="cart-popup">
    ✓ Item added to cart!
</div>


<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="footer">

    © <?= date('Y'); ?>
    Lutong Nayon.
    All rights reserved.

</footer>


<!-- =====================================================
     JAVASCRIPT
===================================================== -->

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {


        const forms =
            document.querySelectorAll(
                '.add-to-cart-form'
            );


        const cartCount =
            document.getElementById(
                'cart-count'
            );


        const popup =
            document.getElementById(
                'cart-popup'
            );


        forms.forEach(
            function (form) {


                form.addEventListener(
                    'submit',
                    function (event) {


                        event.preventDefault();


                        const formData =
                            new FormData(form);


                        fetch(
                            'user_dashboard.php',
                            {
                                method: 'POST',

                                headers: {
                                    'X-Requested-With':
                                        'XMLHttpRequest'
                                },

                                body: formData
                            }
                        )


                        .then(
                            function (response) {

                                if (!response.ok) {

                                    throw new Error(
                                        'Network response was not OK'
                                    );

                                }

                                return response.json();

                            }
                        )


                        .then(
                            function (data) {


                                if (data.success) {


                                    /* =========================
                                       UPDATE CART COUNT
                                    ========================= */

                                    cartCount.textContent =
                                        data.cart_count +
                                        (
                                            data.cart_count === 1
                                                ? ' item'
                                                : ' items'
                                        );


                                    /* =========================
                                       SHOW POPUP
                                    ========================= */

                                    popup.style.display =
                                        'block';


                                    /* =========================
                                       HIDE POPUP
                                    ========================= */

                                    setTimeout(
                                        function () {

                                            popup.style.display =
                                                'none';

                                        },
                                        1600
                                    );


                                    /* =========================
                                       RESET QUANTITY
                                    ========================= */

                                    const quantityInput =
                                        form.querySelector(
                                            'input[name="quantity"]'
                                        );


                                    if (quantityInput) {

                                        quantityInput.value = 1;

                                    }

                                }

                            }
                        )


                        .catch(
                            function (error) {

                                console.error(
                                    'Cart error:',
                                    error
                                );

                                /*
                                 * If AJAX fails,
                                 * reload the page.
                                 */

                                window.location.reload();

                            }
                        );

                    }
                );

            }
        );

    }
);

</script>


</body>

</html>