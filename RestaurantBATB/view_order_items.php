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
   DELETE ALL ORDERS FOR TABLE
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_table_number'])) {

    $table_number = intval($_POST['delete_table_number']);

    if ($table_number > 0) {
        mysqli_query(
            $conn,
            "DELETE FROM orders WHERE table_number = $table_number"
        );
    }

    header("Location: view_order_items.php");
    exit();
}

/* =========================
   GET ORDERS
========================= */

$sql = "
    SELECT
        users.name AS user_name,
        menu_items.name AS item_name,
        menu_items.image AS item_image,
        menu_items.price AS item_price,
        orders.id AS order_id,
        orders.status,
        orders.table_number
    FROM orders
    JOIN users
        ON orders.customer_id = users.id
    JOIN menu_items
        ON orders.item_id = menu_items.id
    ORDER BY orders.table_number, orders.id DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$orders_by_table = [];

while ($row = mysqli_fetch_assoc($result)) {
    $orders_by_table[$row['table_number']][] = $row;
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

    <title>Orders | Lutong Nayon</title>

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

            font-size: 21px;
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

            letter-spacing: 0.5px;
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

            background: rgba(255,255,255,0.08);
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
           PAGE HEADER
        ========================= */

        .page-top {
            display: flex;

            align-items: flex-end;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 30px;
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
            margin-top: 5px;

            color: #806c5b;

            font-size: 13px;
        }


        .order-count {
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
           TABLE SECTION
        ========================= */

        .table-section {
            margin-bottom: 35px;
        }


        .table-header {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            margin-bottom: 15px;

            padding-bottom: 12px;

            border-bottom:
                1px solid #e5d4bf;
        }


        .table-title {
            display: flex;

            align-items: center;

            gap: 10px;
        }


        .table-icon {
            width: 38px;
            height: 38px;

            display: flex;

            align-items: center;
            justify-content: center;

            background: #f97316;

            color: #ffffff;

            border-radius: 9px;

            font-size: 18px;
        }


        .table-title h2 {
            color: #382313;

            font-size: 19px;

            font-weight: 600;
        }


        .table-title span {
            display: block;

            color: #927d68;

            font-size: 11px;

            margin-top: 1px;
        }


        /* =========================
           DELETE TABLE BUTTON
        ========================= */

        .delete-table-form {
            margin: 0;
        }


        .delete-table-btn {
            border: 1px solid #f1c5bc;

            background: #fff4f1;

            color: #b42318;

            padding:
                8px 13px;

            border-radius: 7px;

            cursor: pointer;

            font-family: 'Poppins', sans-serif;

            font-size: 11px;

            font-weight: 600;

            transition: 0.25s;
        }


        .delete-table-btn:hover {
            background: #dc2626;

            border-color: #dc2626;

            color: #ffffff;
        }


        /* =========================
           ORDER GRID
        ========================= */

        .card-container {
            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: 18px;
        }


        /* =========================
           ORDER CARD
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
           ORDER NUMBER
        ========================= */

        .order-number {
            position: absolute;

            top: 10px;
            left: 10px;

            padding:
                5px 9px;

            background:
                rgba(56,35,19,0.90);

            color: #ffffff;

            border-radius: 6px;

            font-size: 10px;

            font-weight: 600;
        }


        /* =========================
           CARD CONTENT
        ========================= */

        .card-content {
            padding: 15px;

            display: flex;

            flex-direction: column;

            flex: 1;
        }


        .card h3 {
            color: #382313;

            font-size: 17px;

            font-weight: 600;

            line-height: 1.3;

            margin-bottom: 10px;
        }


        /* =========================
           CUSTOMER
        ========================= */

        .customer {
            display: flex;

            align-items: center;

            gap: 8px;

            margin-bottom: 13px;

            color: #806c5b;

            font-size: 11px;
        }


        .customer-icon {
            width: 26px;
            height: 26px;

            display: flex;

            align-items: center;
            justify-content: center;

            background: #f3e4d1;

            border-radius: 50%;

            font-size: 12px;
        }


        /* =========================
           ORDER INFO
        ========================= */

        .order-info {
            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 8px;

            margin-bottom: 14px;
        }


        .info-box {
            background: #f8efe4;

            border-radius: 7px;

            padding: 9px;
        }


        .info-label {
            display: block;

            color: #a0876f;

            font-size: 9px;

            text-transform: uppercase;

            letter-spacing: 0.5px;

            margin-bottom: 2px;
        }


        .info-value {
            color: #4a2f1b;

            font-size: 12px;

            font-weight: 600;
        }


        /* =========================
           STATUS
        ========================= */

        .status-row {
            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 13px;
        }


        .status-label {
            color: #806c5b;

            font-size: 11px;
        }


        .status-badge {
            padding:
                5px 9px;

            border-radius: 20px;

            font-size: 10px;

            font-weight: 600;

            text-transform: capitalize;
        }


        .status-pending {
            background: #fff4d6;

            color: #a16207;
        }


        .status-delivered {
            background: #e7f6ec;

            color: #16713a;
        }


        .status-other {
            background: #eee7df;

            color: #665344;
        }


        /* =========================
           ACTIONS
        ========================= */

        .card-actions {
            display: flex;

            align-items: stretch;

            gap: 7px;

            margin-top: auto;
        }


        .status-form {
            display: flex;

            flex: 1;

            gap: 6px;
        }


        select {
            flex: 1;

            min-width: 0;

            padding:
                8px 8px;

            background: #ffffff;

            color: #4a2f1b;

            border:
                1px solid #dfcdb8;

            border-radius: 7px;

            outline: none;

            font-family: 'Poppins', sans-serif;

            font-size: 10px;

            cursor: pointer;
        }


        select:focus {
            border-color: #f97316;
        }


        button {
            font-family: 'Poppins', sans-serif;
        }


        .update-btn {
            border: none;

            background: #f97316;

            color: #ffffff;

            padding:
                8px 12px;

            border-radius: 7px;

            cursor: pointer;

            font-size: 10px;

            font-weight: 600;

            transition: 0.25s;
        }


        .update-btn:hover {
            background: #ea580c;
        }


        .delete-form {
            margin: 0;
        }


        .delete-btn {
            border: 1px solid #f1c5bc;

            background: #fff4f1;

            color: #b42318;

            padding:
                8px 11px;

            border-radius: 7px;

            cursor: pointer;

            font-size: 10px;

            font-weight: 600;

            transition: 0.25s;
        }


        .delete-btn:hover {
            background: #dc2626;

            color: #ffffff;

            border-color: #dc2626;
        }


        /* =========================
           EMPTY STATE
        ========================= */

        .empty-state {
            background: #fffaf4;

            border:
                1px solid #eadbc8;

            border-radius: 12px;

            padding:
                65px 20px;

            text-align: center;

            box-shadow:
                0 4px 12px
                rgba(67,43,22,0.06);
        }


        .empty-icon {
            font-size: 42px;

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

        @media (max-width: 1100px) {

            .card-container {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

        }


        /* =========================
           MOBILE SIDEBAR
        ========================= */

        @media (max-width: 950px) {

            .sidebar {
                left: -240px;

                width: 240px;

                top: 0;

                height: 100vh;

                padding-top: 90px;
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
           SMALL TABLET
        ========================= */

        @media (max-width: 700px) {

            .card-container {
                grid-template-columns: 1fr;
            }


            .page-top {
                align-items: flex-start;

                flex-direction: column;
            }


            .table-header {
                align-items: flex-start;

                flex-direction: column;
            }


            .delete-table-form {
                width: 100%;
            }


            .delete-table-btn {
                width: 100%;
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

                padding-top: 20px;
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


            .page-title h2 {
                font-size: 23px;
            }


            .page-title p {
                font-size: 11px;
            }


            .order-count {
                font-size: 10px;
            }


            .table-title h2 {
                font-size: 16px;
            }


            .table-title span {
                font-size: 9px;
            }


            .card {
                border-radius: 10px;
            }


            .image-wrapper {
                aspect-ratio: 1.5 / 1;
            }


            .card-content {
                padding: 12px;
            }


            .card h3 {
                font-size: 14px;
            }


            .customer {
                font-size: 10px;
            }


            .info-label {
                font-size: 8px;
            }


            .info-value {
                font-size: 11px;
            }


            .card-actions {
                flex-direction: column;
            }


            .status-form {
                width: 100%;
            }


            .delete-form {
                width: 100%;
            }


            .delete-btn {
                width: 100%;
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


            .card-content {
                padding: 10px;
            }


            .table-icon {
                width: 34px;
                height: 34px;
            }


            .table-title h2 {
                font-size: 15px;
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


        <a href="admin_dashboard.php">

            <span class="nav-icon">
                ▦
            </span>

            Dashboard

        </a>


        <a href="view_items.php">

            <span class="nav-icon">
                🍽
            </span>

            Manage Menu

        </a>


        <a
            href="view_order_items.php"
            class="active"
        >

            <span class="nav-icon">
                ▤
            </span>

            Orders

        </a>


        <a href="view_users.php">

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
                    ORDER MANAGEMENT
                </small>

                <h2>
                    Customer Orders
                </h2>

                <p>
                    Monitor and manage orders from each table.
                </p>

            </div>


            <div class="order-count">

                <?php

                $total_orders = 0;

                foreach ($orders_by_table as $table_orders) {
                    $total_orders += count($table_orders);
                }

                echo $total_orders;

                echo $total_orders === 1
                    ? " order"
                    : " orders";

                ?>

            </div>

        </section>



        <!-- =========================
             ORDERS
        ========================= -->

        <?php if (!empty($orders_by_table)): ?>


            <?php foreach ($orders_by_table as $table => $orders): ?>


                <section class="table-section">


                    <!-- TABLE HEADER -->

                    <div class="table-header">

                        <div class="table-title">

                            <div class="table-icon">
                                🪑
                            </div>

                            <div>

                                <h2>
                                    Table
                                    <?php echo htmlspecialchars($table); ?>
                                </h2>

                                <span>
                                    <?php echo count($orders); ?>

                                    <?php echo count($orders) === 1
                                        ? "order"
                                        : "orders"; ?>

                                    for this table
                                </span>

                            </div>

                        </div>


                        <form
                            action=""
                            method="post"
                            class="delete-table-form"

                            onsubmit="return confirm(
                                'Delete ALL orders for Table <?php echo htmlspecialchars($table); ?>?'
                            );"
                        >

                            <input
                                type="hidden"
                                name="delete_table_number"
                                value="<?php echo (int)$table; ?>"
                            >

                            <button
                                type="submit"
                                class="delete-table-btn"
                            >
                                Delete All Orders
                            </button>

                        </form>

                    </div>



                    <!-- ORDER CARDS -->

                    <div class="card-container">


                        <?php foreach ($orders as $row): ?>


                            <?php

                            $status = strtolower(
                                trim($row['status'])
                            );

                            if ($status === "pending") {

                                $status_class =
                                    "status-pending";

                            } elseif ($status === "delivered") {

                                $status_class =
                                    "status-delivered";

                            } else {

                                $status_class =
                                    "status-other";

                            }

                            ?>


                            <article class="card">


                                <!-- IMAGE -->

                                <div class="image-wrapper">

                                    <img
                                        src="image/<?php
                                            echo htmlspecialchars(
                                                $row['item_image'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                        ?>"
                                        alt="<?php
                                            echo htmlspecialchars(
                                                $row['item_name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                        ?>"
                                    >


                                    <div class="order-number">

                                        ORDER #
                                        <?php
                                            echo (int)$row['order_id'];
                                        ?>

                                    </div>

                                </div>



                                <!-- CARD CONTENT -->

                                <div class="card-content">


                                    <h3>

                                        <?php
                                            echo htmlspecialchars(
                                                $row['item_name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                        ?>

                                    </h3>



                                    <!-- CUSTOMER -->

                                    <div class="customer">

                                        <div class="customer-icon">
                                            👤
                                        </div>

                                        <span>

                                            <?php
                                                echo htmlspecialchars(
                                                    $row['user_name'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                );
                                            ?>

                                        </span>

                                    </div>



                                    <!-- ORDER INFO -->

                                    <div class="order-info">


                                        <div class="info-box">

                                            <span class="info-label">
                                                Price
                                            </span>

                                            <span class="info-value">

                                                ₱<?php

                                                echo number_format(
                                                    (float)$row['item_price'],
                                                    2
                                                );

                                                ?>

                                            </span>

                                        </div>


                                        <div class="info-box">

                                            <span class="info-label">
                                                Table
                                            </span>

                                            <span class="info-value">

                                                <?php
                                                    echo htmlspecialchars(
                                                        $row['table_number']
                                                    );
                                                ?>

                                            </span>

                                        </div>


                                    </div>



                                    <!-- STATUS -->

                                    <div class="status-row">

                                        <span class="status-label">
                                            Current status
                                        </span>


                                        <span
                                            class="status-badge <?php
                                                echo $status_class;
                                            ?>"
                                        >

                                            <?php
                                                echo htmlspecialchars(
                                                    ucfirst($status)
                                                );
                                            ?>

                                        </span>

                                    </div>



                                    <!-- ACTIONS -->

                                    <div class="card-actions">


                                        <!-- UPDATE STATUS -->

                                        <form
                                            action="order_item_status.php"
                                            method="post"
                                            class="status-form"
                                        >

                                            <input
                                                type="hidden"
                                                name="order_id"
                                                value="<?php
                                                    echo (int)$row['order_id'];
                                                ?>"
                                            >


                                            <select
                                                name="status"
                                                aria-label="Order status"
                                            >

                                                <option
                                                    value="pending"

                                                    <?php

                                                    echo $status === "pending"
                                                        ? "selected"
                                                        : "";

                                                    ?>
                                                >
                                                    Pending
                                                </option>


                                                <option
                                                    value="delivered"

                                                    <?php

                                                    echo $status === "delivered"
                                                        ? "selected"
                                                        : "";

                                                    ?>
                                                >
                                                    Delivered
                                                </option>

                                            </select>


                                            <button
                                                type="submit"
                                                class="update-btn"
                                            >
                                                Update
                                            </button>

                                        </form>



                                        <!-- DELETE ORDER -->

                                        <form
                                            action="delete_order.php"
                                            method="post"
                                            class="delete-form"

                                            onsubmit="return confirm(
                                                'Delete this order?'
                                            );"
                                        >

                                            <input
                                                type="hidden"
                                                name="order_id"
                                                value="<?php
                                                    echo (int)$row['order_id'];
                                                ?>"
                                            >


                                            <button
                                                type="submit"
                                                class="delete-btn"
                                            >
                                                Delete
                                            </button>

                                        </form>


                                    </div>


                                </div>

                            </article>


                        <?php endforeach; ?>


                    </div>

                </section>


            <?php endforeach; ?>


        <?php else: ?>


            <!-- EMPTY STATE -->

            <div class="empty-state">

                <div class="empty-icon">
                    🧾
                </div>

                <h3>
                    No orders yet
                </h3>

                <p>
                    Customer orders will appear here once they are placed.
                </p>

            </div>


        <?php endif; ?>


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


        /* Close sidebar after selecting a page on mobile */

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