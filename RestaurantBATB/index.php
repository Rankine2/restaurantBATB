<?php

session_start();
include "db.php";

/* =========================================================
   GET MENU ITEMS
========================================================= */

$sql = "SELECT * FROM menu_items ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
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

<title>Lutong Nayon | Home</title>

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

    color: #ffffff;

    background:
        linear-gradient(
            rgba(55, 34, 18, 0.38),
            rgba(55, 34, 18, 0.38)
        ),
        url('image/bannerrt.jpg')
        center center / cover fixed no-repeat;

    overflow-x: hidden;
}


/* =========================================================
   NAVBAR
========================================================= */

.navbar {

    width: 100%;

    position: sticky;

    top: 0;

    z-index: 1000;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 14px 6%;

    background:
        rgba(30, 20, 12, 0.92);

    border-bottom:
        1px solid rgba(255,255,255,0.12);

    box-shadow:
        0 5px 20px rgba(0,0,0,0.25);

    backdrop-filter: blur(12px);
}


/* Logo */

.nav-logo {

    color: #ffffff;

    text-decoration: none;

    font-size: 1.35rem;

    font-weight: 700;

    white-space: nowrap;
}

.nav-logo span {

    color: #f97316;
}


/* Navigation links */

.nav-links {

    display: flex;

    align-items: center;

    gap: 8px;

    list-style: none;
}

.nav-links li {
    list-style: none;
}

.nav-links a {

    display: block;

    color: #ffffff;

    text-decoration: none;

    font-size: 0.92rem;

    font-weight: 500;

    padding: 9px 13px;

    border-radius: 9px;

    transition: 0.25s ease;
}

.nav-links a:hover {

    background: rgba(249,115,22,0.15);

    color: #f97316;
}


/* =========================================================
   HERO / HEADER
========================================================= */

.header {

    position: relative;

    width: min(1100px, 92%);

    margin: 35px auto 25px;

    min-height: 330px;

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    text-align: center;

    padding: 55px 20px;

    overflow: hidden;

    border-radius: 28px;

    background:
        linear-gradient(
            rgba(30, 18, 10, 0.62),
            rgba(30, 18, 10, 0.72)
        );

    border:
        1px solid rgba(255,255,255,0.12);

    box-shadow:
        0 20px 50px rgba(0,0,0,0.30);

    backdrop-filter: blur(4px);
}


/* Small label */

.hero-label {

    display: inline-block;

    margin-bottom: 12px;

    padding: 7px 15px;

    border-radius: 50px;

    background:
        rgba(249,115,22,0.18);

    border:
        1px solid rgba(249,115,22,0.35);

    color: #ffb47a;

    font-size: 0.8rem;

    font-weight: 600;

    letter-spacing: 1px;

    text-transform: uppercase;
}


.header h1 {

    position: relative;

    z-index: 2;

    font-size:
        clamp(2.2rem, 6vw, 4.3rem);

    font-weight: 700;

    line-height: 1.1;

    color: #ffffff;

    text-shadow:
        0 5px 20px rgba(0,0,0,0.7);

    animation:
        floatText 3s ease-in-out infinite;
}


.header h1 span {

    color: #f97316;
}


.header p {

    position: relative;

    z-index: 2;

    max-width: 650px;

    margin-top: 15px;

    color: #f5f5f5;

    font-size:
        clamp(0.9rem, 2vw, 1.2rem);

    line-height: 1.7;

    text-shadow:
        0 3px 12px rgba(0,0,0,0.7);
}


/* Hero button */

.hero-button {

    position: relative;

    z-index: 3;

    display: inline-block;

    margin-top: 25px;

    padding: 12px 25px;

    border-radius: 12px;

    background: #f97316;

    color: #ffffff;

    text-decoration: none;

    font-size: 0.95rem;

    font-weight: 600;

    box-shadow:
        0 7px 20px rgba(249,115,22,0.25);

    transition: 0.25s ease;
}

.hero-button:hover {

    background: #ea580c;

    transform: translateY(-2px);

    box-shadow:
        0 10px 25px rgba(249,115,22,0.35);
}


/* =========================================================
   STEAM
========================================================= */

.steam {

    position: absolute;

    top: 45px;

    width: 6px;

    height: 70px;

    border-radius: 50%;

    background:
        linear-gradient(
            to top,
            rgba(255,255,255,0.35),
            rgba(255,255,255,0)
        );

    animation:
        rise 3s infinite;

    opacity: 0;
}

.steam.one {
    left: 45%;
    animation-delay: 0s;
}

.steam.two {
    left: 50%;
    animation-delay: 0.7s;
}

.steam.three {
    left: 55%;
    animation-delay: 1.4s;
}

@keyframes rise {

    0% {

        transform:
            translateY(40px)
            scaleX(1);

        opacity: 0;
    }

    30% {
        opacity: 0.8;
    }

    100% {

        transform:
            translateY(-120px)
            scaleX(1.4);

        opacity: 0;
    }
}


@keyframes floatText {

    0% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-5px);
    }

    100% {
        transform: translateY(0);
    }
}


/* =========================================================
   MENU SECTION
========================================================= */

.menu-section {

    width: min(1200px, 92%);

    margin: 0 auto;

    padding:
        15px 0 60px;
}


/* Section heading */

.menu-heading {

    text-align: center;

    margin-bottom: 28px;
}

.menu-heading h2 {

    font-size:
        clamp(1.7rem, 4vw, 2.5rem);

    font-weight: 700;

    color: #ffffff;

    text-shadow:
        0 4px 15px rgba(0,0,0,0.6);
}

.menu-heading h2 span {
    color: #f97316;
}

.menu-heading p {

    margin-top: 6px;

    color: #eeeeee;

    font-size: 0.9rem;
}


/* =========================================================
   PRODUCT GRID
========================================================= */

.product_grid {

    display: grid;

    /*
       Desktop:
       4 cards
    */

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 22px;

    width: 100%;
}


/* =========================================================
   FOOD CARD
========================================================= */

.card {

    position: relative;

    width: 100%;

    overflow: hidden;

    display: flex;

    flex-direction: column;

    background:
        rgba(235, 207, 165, 0.96);

    border:
        1px solid rgba(255,255,255,0.35);

    border-radius: 18px;

    box-shadow:
        0 10px 28px rgba(35,20,10,0.32);

    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease;

    animation:
        cardAppear 0.5s ease both;
}

.card:hover {

    transform: translateY(-7px);

    box-shadow:
        0 18px 38px rgba(35,20,10,0.42);
}


/* Card animation */

@keyframes cardAppear {

    from {

        opacity: 0;

        transform:
            translateY(15px);
    }

    to {

        opacity: 1;

        transform:
            translateY(0);
    }
}


/* =========================================================
   FOOD IMAGE
========================================================= */

.card-image {

    position: relative;

    width: 100%;

    aspect-ratio: 4 / 3;

    overflow: hidden;

    background: #d8c09c;
}

.card-image img {

    width: 100%;

    height: 100%;

    object-fit: cover;

    display: block;

    transition:
        transform 0.35s ease;
}

.card:hover .card-image img {

    transform: scale(1.06);
}


/* =========================================================
   PRICE
========================================================= */

.price-tag {

    position: absolute;

    top: 10px;

    right: 10px;

    z-index: 5;

    padding: 6px 10px;

    border-radius: 10px;

    background: #10b981;

    color: #ffffff;

    font-size: 0.82rem;

    font-weight: 700;

    box-shadow:
        0 4px 12px rgba(0,0,0,0.25);
}


/* =========================================================
   CARD CONTENT
========================================================= */

.card-content {

    display: flex;

    flex-direction: column;

    align-items: center;

    text-align: center;

    padding: 14px 12px 15px;

    flex: 1;
}


.card h3 {

    color: #3b2617;

    font-size: 1rem;

    font-weight: 700;

    line-height: 1.3;

    margin-bottom: 11px;

    display: -webkit-box;

    -webkit-line-clamp: 2;

    -webkit-box-orient: vertical;

    overflow: hidden;
}


/* =========================================================
   ORDER BUTTON
========================================================= */

.order-button {

    width: 100%;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 10px 8px;

    border-radius: 10px;

    background: #f97316;

    color: #ffffff;

    text-decoration: none;

    font-size: 0.85rem;

    font-weight: 600;

    transition: 0.25s ease;

    box-shadow:
        0 4px 10px rgba(0,0,0,0.18);
}

.order-button:hover {

    background: #ea580c;

    transform: translateY(-2px);
}


/* =========================================================
   FOOTER
========================================================= */

.footer {

    width: 100%;

    padding: 25px 15px;

    text-align: center;

    background:
        rgba(30,20,12,0.86);

    border-top:
        1px solid rgba(255,255,255,0.10);

    color: #dddddd;

    font-size: 0.8rem;
}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 1000px) {

    .product_grid {

        grid-template-columns:
            repeat(3, minmax(0, 1fr));

        gap: 18px;
    }

    .header {

        width: 94%;

        min-height: 300px;
    }
}


/* =========================================================
   SMALL TABLET / LARGE PHONE
========================================================= */

@media (max-width: 760px) {

    body {

        background-attachment: scroll;
    }


    /* Navbar */

    .navbar {

        padding:
            12px 4%;

        flex-direction: column;

        gap: 10px;
    }

    .nav-logo {

        font-size: 1.25rem;
    }

    .nav-links {

        width: 100%;

        justify-content: center;

        gap: 3px;
    }

    .nav-links a {

        padding:
            7px 9px;

        font-size:
            0.78rem;
    }


    /* Hero */

    .header {

        width: 94%;

        margin:
            18px auto;

        min-height:
            290px;

        padding:
            45px 18px;

        border-radius:
            22px;
    }


    .header h1 {

        font-size:
            clamp(2rem, 10vw, 3rem);
    }

    .header p {

        font-size:
            0.88rem;

        line-height:
            1.6;
    }


    /* Menu */

    .menu-section {

        width: 94%;

        padding-bottom:
            45px;
    }


    .menu-heading {

        margin-bottom:
            20px;
    }


    /*
       IMPORTANT:
       2 FOOD CARDS PER ROW
       ON MOBILE
    */

    .product_grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap:
            13px;
    }


    .card {

        border-radius:
            15px;
    }


    .card-image {

        aspect-ratio:
            1 / 0.86;
    }


    .card-content {

        padding:
            11px 9px 11px;
    }


    .card h3 {

        font-size:
            0.85rem;

        margin-bottom:
            9px;
    }


    .order-button {

        padding:
            9px 5px;

        font-size:
            0.75rem;

        border-radius:
            8px;
    }


    .price-tag {

        top: 7px;

        right: 7px;

        padding:
            5px 7px;

        font-size:
            0.7rem;

        border-radius:
            8px;
    }
}


/* =========================================================
   SMALL PHONES
========================================================= */

@media (max-width: 420px) {

    .navbar {

        padding:
            11px 10px;
    }


    .nav-logo {

        font-size:
            1.15rem;
    }


    .nav-links {

        width:
            100%;

        display:
            grid;

        grid-template-columns:
            repeat(3, 1fr);

        gap:
            5px;
    }


    .nav-links a {

        text-align:
            center;

        padding:
            7px 3px;

        font-size:
            0.72rem;
    }


    .header {

        min-height:
            260px;

        padding:
            38px 15px;

        margin-top:
            15px;
    }


    .header h1 {

        font-size:
            2rem;
    }


    .header p {

        font-size:
            0.8rem;
    }


    .hero-label {

        font-size:
            0.65rem;

        padding:
            6px 11px;
    }


    .hero-button {

        padding:
            10px 18px;

        font-size:
            0.8rem;
    }


    .menu-heading h2 {

        font-size:
            1.55rem;
    }


    .menu-heading p {

        font-size:
            0.76rem;
    }


    .product_grid {

        /*
           Keep TWO columns even
           on small phones.
        */

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap:
            10px;
    }


    .card {

        border-radius:
            13px;
    }


    .card-content {

        padding:
            9px 7px 10px;
    }


    .card h3 {

        font-size:
            0.78rem;

        min-height:
            32px;
    }


    .order-button {

        font-size:
            0.7rem;

        padding:
            8px 4px;
    }


    .price-tag {

        font-size:
            0.64rem;

        padding:
            4px 6px;
    }


    .footer {

        font-size:
            0.7rem;
    }
}


/* =========================================================
   VERY SMALL PHONES
========================================================= */

@media (max-width: 340px) {

    .product_grid {

        gap:
            8px;
    }


    .card h3 {

        font-size:
            0.72rem;
    }


    .order-button {

        font-size:
            0.65rem;
    }


    .price-tag {

        font-size:
            0.58rem;
    }
}

</style>

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar">

    <a
        href="index.php"
        class="nav-logo"
    >
        🍽️ <span>Lutong</span> Nayon
    </a>


    <ul class="nav-links">

        <li>
            <a href="index.php">
                Home
            </a>
        </li>


        <?php if (!isset($_SESSION['user_id'])) { ?>

            <li>
                <a href="login.php">
                    Login
                </a>
            </li>

            <li>
                <a href="register.php">
                    Register
                </a>
            </li>

        <?php } else { ?>

            <li>
                <a href="user_dashboard.php">
                    Dashboard
                </a>
            </li>

            <li>
                <a href="logout.php">
                    Logout
                </a>
            </li>

        <?php } ?>

    </ul>

</nav>



<!-- =====================================================
     HERO
===================================================== -->

<header class="header">

    <div class="hero-label">
        Authentic Filipino Cuisine
    </div>


    <h1>
        🍽️ Lutong <span>Nayon</span>
    </h1>


    <p>
        Home-cooked Filipino dishes made with love,
        bringing the warmth and flavor of home
        straight to your table.
    </p>


    <?php if (isset($_SESSION['user_id'])) { ?>

        <a
            href="user_dashboard.php"
            class="hero-button"
        >
            View Our Menu
        </a>

    <?php } else { ?>

        <a
            href="login.php"
            class="hero-button"
        >
            Order Now
        </a>

    <?php } ?>


    <!-- Steam -->

    <div class="steam one"></div>

    <div class="steam two"></div>

    <div class="steam three"></div>

</header>



<!-- =====================================================
     MENU
===================================================== -->

<section class="menu-section">


    <div class="menu-heading">

        <h2>
            Our <span>Menu</span>
        </h2>

        <p>
            Discover our delicious Filipino favorites
        </p>

    </div>



    <div class="product_grid">


        <?php while ($row = mysqli_fetch_assoc($result)) { ?>


            <div class="card">


                <!-- IMAGE -->

                <div class="card-image">

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
                        loading="lazy"
                    >


                    <!-- PRICE -->

                    <div class="price-tag">

                        ₱<?= number_format(
                            (float)$row['price'],
                            2
                        ); ?>

                    </div>

                </div>



                <!-- CONTENT -->

                <div class="card-content">


                    <h3>

                        <?= htmlspecialchars(
                            $row['name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>

                    </h3>



                    <?php if (isset($_SESSION['user_id'])) { ?>

                        <a
                            href="order_item.php?menu_id=<?= (int)$row['id']; ?>"
                            class="order-button"
                        >
                            🛒 Order Now
                        </a>

                    <?php } else { ?>

                        <a
                            href="login.php"
                            class="order-button"
                        >
                            🔐 Order Now
                        </a>

                    <?php } ?>


                </div>

            </div>


        <?php } ?>


    </div>

</section>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="footer">

    © <?= date('Y'); ?> Lutong Nayon.
    All rights reserved.

</footer>


</body>

</html>