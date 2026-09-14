<?php
session_start();
include "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Only admin can access
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== "admin") {
    header("Location: user_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Lutong Nayon</title>

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

            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
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
           LOGOUT
        ========================= */

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

            border-right: 1px solid rgba(255,255,255,0.08);

            transition: 0.3s ease;
        }

        .sidebar-title {
            color: #cdb18e;
            font-size: 10px;
            font-weight: 600;

            letter-spacing: 1.5px;

            margin: 0 12px 12px;
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
            background: rgba(255,255,255,0.08);
        }

        .hamburger span {
            width: 21px;
            height: 2px;

            background: #ffffff;
            border-radius: 3px;
        }

        /* =========================
           MAIN
        ========================= */

        .main {
            margin-left: 230px;
            padding: 105px 35px 40px;

            min-height: 100vh;
        }

        /* =========================
           WELCOME
        ========================= */

        .welcome {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;

            margin-bottom: 28px;
        }

        .welcome-text small {
            color: #9a7650;
            font-size: 12px;
        }

        .welcome-text h2 {
            margin-top: 3px;

            color: #382313;

            font-size: 30px;
            font-weight: 700;
        }

        .welcome-text p {
            margin-top: 4px;

            color: #806c5b;
            font-size: 13px;
        }

        .date-box {
            color: #765a3e;
            background: #fffaf3;

            border: 1px solid #eadbc8;
            border-radius: 8px;

            padding: 9px 14px;

            font-size: 12px;
        }

        /* =========================
           DASHBOARD CARDS
        ========================= */

        .cards {
            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: 18px;

            margin-bottom: 30px;
        }

        .card {
            position: relative;

            display: block;

            padding: 22px;

            min-height: 150px;

            background: #fffaf4;

            border: 1px solid #eadbc8;
            border-radius: 12px;

            text-decoration: none;

            color: inherit;

            box-shadow: 0 4px 12px rgba(67, 43, 22, 0.08);

            transition: 0.25s ease;
        }

        .card:hover {
            transform: translateY(-3px);

            box-shadow:
                0 9px 20px rgba(67, 43, 22, 0.13);

            border-color: #d9b98f;
        }

        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .card-icon {
            width: 43px;
            height: 43px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 9px;

            background: #fff0df;

            font-size: 20px;
        }

        .card-arrow {
            color: #b39a7e;
            font-size: 17px;
        }

        .card h3 {
            margin-top: 20px;

            color: #382313;

            font-size: 18px;
            font-weight: 600;
        }

        .card p {
            margin-top: 3px;

            color: #8a7663;

            font-size: 12px;
        }

        /* =========================
           QUICK ACCESS
        ========================= */

        .section-title {
            margin-bottom: 12px;

            color: #382313;

            font-size: 17px;
            font-weight: 600;
        }

        .quick-box {
            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 15px;
        }

        .quick-link {
            display: flex;
            align-items: center;
            gap: 13px;

            padding: 15px 17px;

            background: #ffffff;

            border: 1px solid #eadbc8;
            border-radius: 10px;

            text-decoration: none;

            color: #4b3522;

            transition: 0.25s;
        }

        .quick-link:hover {
            background: #fff7ec;
            border-color: #d9b98f;
        }

        .quick-icon {
            width: 38px;
            height: 38px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #f7ead9;

            border-radius: 8px;

            font-size: 17px;
        }

        .quick-link strong {
            display: block;

            font-size: 13px;
            font-weight: 600;
        }

        .quick-link span {
            display: block;

            margin-top: 2px;

            color: #8c7864;
            font-size: 11px;
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

        @media (max-width: 950px) {

            .sidebar {
                left: -240px;
            }

            .sidebar.active {
                left: 0;
            }

            .hamburger {
                display: flex;
            }

            .header {
                padding: 0 18px;
            }

            .brand-text h1 {
                font-size: 17px;
            }

            .main {
                margin-left: 0;
                padding: 100px 22px 35px;
            }

            .footer {
                margin-left: 0;
            }

            .cards {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }
        }

        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 600px) {

            .header {
                height: 64px;
            }

            .sidebar {
                top: 64px;
                height: calc(100vh - 64px);
                width: 245px;
            }

            .main {
                padding:
                    85px 14px 30px;
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
                padding: 8px 11px;
                font-size: 11px;
            }

            .welcome {
                display: block;
                margin-bottom: 20px;
            }

            .welcome-text h2 {
                font-size: 24px;
            }

            .welcome-text p {
                font-size: 11px;
            }

            .date-box {
                display: inline-block;
                margin-top: 12px;
                font-size: 10px;
            }

            .cards {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));

                gap: 10px;
            }

            .card {
                min-height: 135px;
                padding: 15px;
                border-radius: 10px;
            }

            .card-icon {
                width: 37px;
                height: 37px;
                font-size: 17px;
            }

            .card h3 {
                margin-top: 15px;
                font-size: 14px;
            }

            .card p {
                font-size: 10px;
            }

            .card-arrow {
                font-size: 14px;
            }

            .section-title {
                font-size: 15px;
                margin-top: 5px;
            }

            .quick-box {
                grid-template-columns: 1fr;
                gap: 9px;
            }

            .quick-link {
                padding: 12px;
            }
        }

        /* =========================
           VERY SMALL PHONES
        ========================= */

        @media (max-width: 380px) {

            .brand-text span {
                display: none;
            }

            .logout {
                padding: 7px 9px;
            }

            .main {
                padding-left: 10px;
                padding-right: 10px;
            }

            .cards {
                gap: 8px;
            }

            .card {
                padding: 12px;
                min-height: 125px;
            }

            .card h3 {
                font-size: 13px;
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
                <h1>Lutong Nayon</h1>
                <span>ADMINISTRATION</span>
            </div>

        </div>

        <div style="display:flex; align-items:center; gap:10px;">

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
            class="active"
        >
            <span class="nav-icon">▦</span>
            Dashboard
        </a>

        <a href="view_items.php">
            <span class="nav-icon">🍽</span>
            Manage Menu
        </a>

        <a href="view_order_items.php">
            <span class="nav-icon">▤</span>
            Orders
        </a>

        <a href="view_users.php">
            <span class="nav-icon">♙</span>
            Users
        </a>

    </aside>


    <!-- =========================
         MAIN CONTENT
    ========================= -->

    <main class="main">

        <!-- Welcome -->

        <section class="welcome">

            <div class="welcome-text">

                <small>
                    ADMIN PANEL
                </small>

                <h2>
                    Welcome, Admin
                </h2>

                <p>
                    Manage your Lutong Nayon system from here.
                </p>

            </div>

            <div class="date-box">
                <?php echo date("F d, Y"); ?>
            </div>

        </section>


        <!-- Dashboard Cards -->

        <section class="cards">

            <!-- Menu -->

            <a
                href="view_items.php"
                class="card"
            >

                <div class="card-top">

                    <div class="card-icon">
                        🍽️
                    </div>

                    <div class="card-arrow">
                        →
                    </div>

                </div>

                <h3>
                    Menu Items
                </h3>

                <p>
                    Add, edit and manage dishes
                </p>

            </a>


            <!-- Orders -->

            <a
                href="view_order_items.php"
                class="card"
            >

                <div class="card-top">

                    <div class="card-icon">
                        🧾
                    </div>

                    <div class="card-arrow">
                        →
                    </div>

                </div>

                <h3>
                    Orders
                </h3>

                <p>
                    View and manage customer orders
                </p>

            </a>


            <!-- Users -->

            <a
                href="view_users.php"
                class="card"
            >

                <div class="card-top">

                    <div class="card-icon">
                        👤
                    </div>

                    <div class="card-arrow">
                        →
                    </div>

                </div>

                <h3>
                    Users
                </h3>

                <p>
                    Manage registered customers
                </p>

            </a>

        </section>


        <!-- Quick Access -->

        <h3 class="section-title">
            Quick Access
        </h3>

        <section class="quick-box">

            <a
                href="view_items.php"
                class="quick-link"
            >

                <div class="quick-icon">
                    +
                </div>

                <div>
                    <strong>
                        Manage Menu
                    </strong>

                    <span>
                        Add or update Filipino dishes
                    </span>
                </div>

            </a>


            <a
                href="view_order_items.php"
                class="quick-link"
            >

                <div class="quick-icon">
                    🧾
                </div>

                <div>
                    <strong>
                        Check Orders
                    </strong>

                    <span>
                        Review customer orders
                    </span>
                </div>

            </a>

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

        // Close sidebar after clicking a link on mobile
        document.querySelectorAll(".sidebar a").forEach(function(link) {

            link.addEventListener("click", function() {

                if (window.innerWidth <= 950) {

                    document
                        .getElementById("sidebar")
                        .classList.remove("active");

                }

            });

        });

    </script>

</body>
</html>