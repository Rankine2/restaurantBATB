<?php
session_start();
include "db.php";

/* =========================
   ADMIN ACCESS CHECK
========================= */

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== "admin") {
    header("Location: login.php");
    exit();
}

/* =========================
   DELETE MENU ITEM
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item_id'])) {

    $id = intval($_POST['delete_item_id']);

    if ($id > 0) {
        mysqli_query(
            $conn,
            "DELETE FROM menu_items WHERE id = $id"
        );
    }

    header("Location: view_items.php");
    exit();
}

/* =========================
   GET MENU ITEMS
========================= */

$sql = "SELECT * FROM menu_items ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

$menu_items = [];

if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {
        $menu_items[] = $row;
    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Menu | Lutong Nayon</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>

        /* =========================
           RESET
        ========================= */

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
            background: #f6efe5;
            color: #382313;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* =========================
           HEADER
        ========================= */

        .header {
            height: 72px;
            width: 100%;

            position: fixed;
            top: 0;
            left: 0;

            z-index: 1000;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 28px;

            background: #382313;

            border-bottom: 3px solid #d97706;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 42px;
            height: 42px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #f97316;

            border-radius: 10px;

            font-size: 22px;
        }

        .brand-text h1 {
            color: #ffffff;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.1;
        }

        .brand-text span {
            color: #e8c9a0;
            font-size: 11px;
        }

        /* =========================
           HEADER RIGHT
        ========================= */

        .header-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logout {
            color: #ffffff;
            text-decoration: none;

            padding: 9px 17px;

            border: 1px solid rgba(255,255,255,0.25);

            border-radius: 8px;

            font-size: 13px;
            font-weight: 500;

            transition: 0.25s;
        }

        .logout:hover {
            background: #f97316;
            border-color: #f97316;
        }

        /* =========================
           HAMBURGER
        ========================= */

        .hamburger {
            display: none;

            width: 42px;
            height: 42px;

            align-items: center;
            justify-content: center;

            flex-direction: column;

            gap: 5px;

            cursor: pointer;

            border-radius: 8px;

            background:
                rgba(255,255,255,0.08);
        }

        .hamburger span {
            width: 21px;
            height: 2px;

            background: #ffffff;

            border-radius: 3px;
        }

        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {
            position: fixed;

            top: 72px;
            left: 0;

            width: 230px;
            height: calc(100vh - 72px);

            background: #4a2f1b;

            padding: 25px 14px;

            z-index: 900;

            border-right:
                1px solid rgba(255,255,255,0.08);

            transition: 0.3s ease;
        }

        .sidebar-title {
            color: #cdb18e;

            font-size: 10px;
            font-weight: 600;

            letter-spacing: 1.5px;

            margin:
                0 12px 12px;

            text-transform: uppercase;
        }

        .sidebar a {
            display: flex;

            align-items: center;

            gap: 12px;

            padding: 12px 14px;

            margin-bottom: 5px;

            color: #f5eadc;

            text-decoration: none;

            border-radius: 8px;

            font-size: 14px;
            font-weight: 500;

            transition: 0.25s;
        }

        .sidebar a:hover {
            background: #633e22;
            color: #ffffff;
        }

        .sidebar a.active {
            background: #f97316;
            color: #ffffff;
        }

        .nav-icon {
            width: 25px;

            text-align: center;

            font-size: 17px;
        }

        /* =========================
           MAIN
        ========================= */

        .main {
            margin-left: 230px;

            padding:
                105px 35px 45px;

            min-height: 100vh;
        }

        /* =========================
           PAGE TOP
        ========================= */

        .page-top {
            display: flex;

            align-items: flex-end;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 25px;
        }

        .page-title small {
            color: #9a7650;

            font-size: 11px;

            font-weight: 600;

            letter-spacing: 1px;
        }

        .page-title h2 {
            margin-top: 4px;

            color: #382313;

            font-size: 29px;

            font-weight: 700;
        }

        .page-title p {
            margin-top: 4px;

            color: #806c5b;

            font-size: 13px;
        }

        .item-count {
            padding:
                9px 14px;

            background: #fffaf3;

            border:
                1px solid #eadbc8;

            border-radius: 8px;

            color: #765a3e;

            font-size: 12px;
        }

        /* =========================
           MENU GRID
        ========================= */

        .card-container {
            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: 18px;
        }

        /* =========================
           MENU CARD
        ========================= */

        .card {
            position: relative;

            overflow: hidden;

            display: flex;

            flex-direction: column;

            background: #fffaf4;

            border:
                1px solid #eadbc8;

            border-radius: 12px;

            box-shadow:
                0 4px 12px
                rgba(67, 43, 22, 0.08);

            transition:
                transform 0.25s ease,
                box-shadow 0.25s ease;

        }

        .card:hover {
            transform: translateY(-3px);

            box-shadow:
                0 9px 20px
                rgba(67, 43, 22, 0.14);

            border-color: #d9b98f;
        }

        /* =========================
           IMAGE
        ========================= */

        .image-wrapper {
            position: relative;

            width: 100%;

            aspect-ratio: 1.45 / 1;

            overflow: hidden;

            background: #d7bd99;
        }

        .image-wrapper img {
            width: 100%;
            height: 100%;

            display: block;

            object-fit: cover;

            transition:
                transform 0.35s ease;
        }

        .card:hover .image-wrapper img {
            transform: scale(1.035);
        }

        /* =========================
           PRICE
        ========================= */

        .price-badge {
            position: absolute;

            top: 10px;
            right: 10px;

            background: #f97316;

            color: #ffffff;

            padding:
                6px 10px;

            border-radius: 7px;

            font-size: 13px;

            font-weight: 700;

            box-shadow:
                0 3px 8px
                rgba(0,0,0,0.20);
        }

        /* =========================
           CARD CONTENT
        ========================= */

        .card-content {
            padding:
                15px;

            display: flex;

            flex-direction: column;

            flex: 1;
        }

        .card h3 {
            color: #382313;

            font-size: 17px;

            font-weight: 600;

            line-height: 1.3;

            margin-bottom: 14px;
        }

        .card-meta {
            color: #8a7663;

            font-size: 11px;

            margin-bottom: 14px;
        }

        /* =========================
           CARD ACTIONS
        ========================= */

        .card-actions {
            display: flex;

            justify-content: flex-end;

            align-items: center;

            margin-top: auto;
        }

        .delete-form {
            margin: 0;
        }

        .delete-button {
            border: none;

            background: #fff0ed;

            color: #b42318;

            padding:
                8px 13px;

            border-radius: 7px;

            cursor: pointer;

            font-family: 'Poppins', sans-serif;

            font-size: 12px;

            font-weight: 600;

            transition: 0.25s;
        }

        .delete-button:hover {
            background: #dc2626;

            color: #ffffff;
        }

        /* =========================
           EMPTY STATE
        ========================= */

        .empty-state {
            grid-column: 1 / -1;

            text-align: center;

            background: #fffaf4;

            border:
                1px solid #eadbc8;

            border-radius: 12px;

            padding:
                60px 20px;

            box-shadow:
                0 4px 12px
                rgba(67,43,22,0.06);
        }

        .empty-icon {
            font-size: 38px;

            margin-bottom: 12px;
        }

        .empty-state h3 {
            color: #382313;

            font-size: 18px;

            margin-bottom: 5px;
        }

        .empty-state p {
            color: #8a7663;

            font-size: 12px;
        }

        /* =========================
           FOOTER
        ========================= */

        .footer {
            margin-left: 230px;

            padding: 20px;

            text-align: center;

            color: #927d68;

            background: #eee2d2;

            font-size: 11px;
        }

        /* =========================
           TABLET
        ========================= */

        @media (max-width: 1050px) {

            .card-container {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

        }

        @media (max-width: 950px) {

            .sidebar {
                left: -240px;

                width: 240px;
            }

            .sidebar.active {
                left: 0;
            }

            .hamburger {
                display: flex;
            }

            .main {
                margin-left: 0;

                padding:
                    100px 22px 35px;
            }

            .footer {
                margin-left: 0;
            }

        }

        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 600px) {

            .header {
                height: 64px;

                padding:
                    0 14px;
            }

            .sidebar {
                top: 64px;

                height:
                    calc(100vh - 64px);
            }

            .brand-icon {
                width: 36px;
                height: 36px;

                font-size: 18px;
            }

            .brand-text h1 {
                font-size: 15px;
            }

            .brand-text span {
                font-size: 9px;
            }

            .logout {
                padding:
                    8px 11px;

                font-size: 11px;
            }

            .main {
                padding:
                    84px 13px 30px;
            }

            .page-top {
                display: block;

                margin-bottom: 19px;
            }

            .page-title h2 {
                font-size: 23px;
            }

            .page-title p {
                font-size: 11px;
            }

            .item-count {
                display: inline-block;

                margin-top: 10px;

                font-size: 10px;
            }

            .card-container {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));

                gap: 10px;
            }

            .card {
                border-radius: 10px;
            }

            .image-wrapper {
                aspect-ratio: 1 / 0.92;
            }

            .price-badge {
                top: 7px;
                right: 7px;

                padding:
                    5px 7px;

                font-size: 10px;

                border-radius: 6px;
            }

            .card-content {
                padding:
                    10px;
            }

            .card h3 {
                font-size: 13px;

                line-height: 1.25;

                margin-bottom: 10px;
            }

            .card-meta {
                font-size: 9px;

                margin-bottom: 10px;
            }

            .delete-button {
                width: 100%;

                padding:
                    7px 5px;

                font-size: 10px;
            }

            .card-actions {
                width: 100%;
            }

            .delete-form {
                width: 100%;
            }

            .empty-state {
                padding:
                    45px 15px;
            }

        }

        /* =========================
           VERY SMALL PHONES
        ========================= */

        @media (max-width: 380px) {

            .main {
                padding-left: 9px;
                padding-right: 9px;
            }

            .card-container {
                gap: 8px;
            }

            .card-content {
                padding:
                    8px;
            }

            .card h3 {
                font-size: 12px;
            }

            .price-badge {
                font-size: 9px;

                padding:
                    4px 6px;
            }

            .delete-button {
                font-size: 9px;
            }

        }

    </style>

</head>

<body>


    <!-- =========================
         HEADER
    ========================= -->

    <header class="header">

        <div class="brand">

            <div class="brand-icon">
                🍲
            </div>

            <div class="brand-text">

                <h1>
                    Lutong Nayon
                </h1>

                <span>
                    ADMINISTRATION
                </span>

            </div>

        </div>


        <div class="header-right">

            <div
                class="hamburger"
                onclick="toggleMenu()"
                aria-label="Open menu"
            >
                <span></span>
                <span></span>
                <span></span>
            </div>

            <a
                href="logout.php"
                class="logout"
            >
                Logout
            </a>

        </div>

    </header>


    <!-- =========================
         SIDEBAR
    ========================= -->

    <aside
        class="sidebar"
        id="sidebar"
    >

        <div class="sidebar-title">
            Management
        </div>


        <a
            href="admin_dashboard.php"
        >
            <span class="nav-icon">
                ▦
            </span>

            Dashboard
        </a>


        <a
            href="view_items.php"
            class="active"
        >
            <span class="nav-icon">
                🍽
            </span>

            Manage Menu
        </a>


        <a
            href="view_order_items.php"
        >
            <span class="nav-icon">
                ▤
            </span>

            Orders
        </a>


        <a
            href="view_users.php"
        >
            <span class="nav-icon">
                ♙
            </span>

            Users
        </a>

    </aside>


    <!-- =========================
         MAIN CONTENT
    ========================= -->

    <main class="main">


        <!-- PAGE HEADER -->

        <section class="page-top">

            <div class="page-title">

                <small>
                    MENU MANAGEMENT
                </small>

                <h2>
                    Menu Items
                </h2>

                <p>
                    View and manage the dishes available in Lutong Nayon.
                </p>

            </div>


            <div class="item-count">

                <?php echo count($menu_items); ?>

                <?php echo count($menu_items) === 1 ? 'menu item' : 'menu items'; ?>

            </div>

        </section>


        <!-- =========================
             MENU CARDS
        ========================= -->

        <section class="card-container">


            <?php if (!empty($menu_items)): ?>


                <?php foreach ($menu_items as $item): ?>


                    <article class="card">


                        <!-- IMAGE -->

                        <div class="image-wrapper">

                            <img
                                src="image/<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>"
                                alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>"
                            >


                            <!-- PRICE -->

                            <div class="price-badge">

                                ₱<?php echo number_format(
                                    (float)$item['price'],
                                    2
                                ); ?>

                            </div>

                        </div>


                        <!-- CONTENT -->

                        <div class="card-content">


                            <h3>

                                <?php echo htmlspecialchars(
                                    $item['name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>

                            </h3>


                            <div class="card-meta">

                                Menu item #<?php echo (int)$item['id']; ?>

                            </div>


                            <!-- DELETE -->

                            <div class="card-actions">

                                <form
                                    method="POST"
                                    class="delete-form"
                                    onsubmit="return confirmDelete();"
                                >

                                    <input
                                        type="hidden"
                                        name="delete_item_id"
                                        value="<?php echo (int)$item['id']; ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="delete-button"
                                    >
                                        Delete
                                    </button>

                                </form>

                            </div>


                        </div>

                    </article>


                <?php endforeach; ?>


            <?php else: ?>


                <div class="empty-state">

                    <div class="empty-icon">
                        🍽️
                    </div>

                    <h3>
                        No menu items found
                    </h3>

                    <p>
                        There are currently no dishes in your menu.
                    </p>

                </div>


            <?php endif; ?>


        </section>


    </main>


    <!-- =========================
         FOOTER
    ========================= -->

    <footer class="footer">

        © <?php echo date("Y"); ?>

        Lutong Nayon · Admin Panel

    </footer>


    <!-- =========================
         JAVASCRIPT
    ========================= -->

    <script>

        function toggleMenu() {

            const sidebar =
                document.getElementById("sidebar");

            sidebar.classList.toggle("active");

        }


        function confirmDelete() {

            return confirm(
                "Are you sure you want to delete this menu item?"
            );

        }


        /* Close mobile sidebar after navigation */

        document
            .querySelectorAll(".sidebar a")
            .forEach(function(link) {

                link.addEventListener(
                    "click",
                    function() {

                        if (window.innerWidth <= 950) {

                            document
                                .getElementById("sidebar")
                                .classList
                                .remove("active");

                        }

                    }
                );

            });

    </script>


</body>

</html>