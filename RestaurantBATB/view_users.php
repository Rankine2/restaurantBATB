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
   GET USERS
========================= */
$sql = "SELECT * FROM users ORDER BY id ASC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

/* Count users */
$user_count = mysqli_num_rows($result);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Users | Lutong Nayon</title>

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
            position: fixed;
            top: 0;
            left: 0;

            width: 100%;
            height: 72px;

            background: #382313;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 28px;

            z-index: 1000;

            border-bottom: 3px solid #d97706;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.15);
        }


        /* =========================
           BRAND
        ========================= */

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

            border-radius: 9px;

            font-size: 21px;
        }

        .brand-text h1 {
            color: #ffffff;

            font-size: 19px;
            font-weight: 700;

            line-height: 1.1;
        }

        .brand-text span {
            color: #d9b98f;

            font-size: 10px;
            letter-spacing: 1px;
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

            border-radius: 7px;

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

            border-radius: 7px;

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

            border-radius: 7px;

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

            font-size: 16px;
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
            margin-top: 5px;

            color: #806c5b;

            font-size: 13px;
        }


        /* =========================
           USER COUNT
        ========================= */

        .user-count {
            padding: 9px 14px;

            background: #fffaf3;

            border: 1px solid #eadbc8;

            border-radius: 7px;

            color: #765a3e;

            font-size: 12px;

            white-space: nowrap;
        }

        .user-count strong {
            color: #382313;

            font-weight: 700;
        }


        /* =========================
           TABLE CARD
        ========================= */

        .table-card {
            background: #fffaf4;

            border:
                1px solid #eadbc8;

            border-radius: 11px;

            box-shadow:
                0 4px 12px
                rgba(67, 43, 22, 0.08);

            overflow: hidden;
        }


        /* =========================
           TABLE TOP
        ========================= */

        .table-top {
            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 17px 20px;

            border-bottom:
                1px solid #eadbc8;

            background: #fffaf4;
        }

        .table-top h3 {
            color: #382313;

            font-size: 15px;

            font-weight: 600;
        }

        .table-top span {
            color: #927d68;

            font-size: 11px;
        }


        /* =========================
           TABLE
        ========================= */

        .table-wrapper {
            width: 100%;

            overflow-x: auto;
        }

        table {
            width: 100%;

            border-collapse: collapse;

            min-width: 650px;
        }

        thead {
            background: #f1e4d3;
        }

        th {
            padding: 13px 18px;

            color: #6d5138;

            font-size: 11px;

            font-weight: 600;

            letter-spacing: 0.4px;

            text-transform: uppercase;

            text-align: left;

            border-bottom:
                1px solid #dfccb4;
        }

        td {
            padding: 14px 18px;

            color: #4f3927;

            font-size: 13px;

            border-bottom:
                1px solid #eee1d2;

            vertical-align: middle;
        }

        tbody tr {
            transition: 0.2s;
        }

        tbody tr:hover {
            background: #fcf5ec;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }


        /* =========================
           ID
        ========================= */

        .user-id {
            color: #9a7650;

            font-size: 12px;

            font-weight: 600;
        }


        /* =========================
           USER NAME
        ========================= */

        .user-info {
            display: flex;

            align-items: center;

            gap: 11px;
        }

        .avatar {
            width: 36px;
            height: 36px;

            display: flex;

            align-items: center;
            justify-content: center;

            flex-shrink: 0;

            background: #ead3b5;

            color: #5b371d;

            border-radius: 50%;

            font-size: 13px;

            font-weight: 700;
        }

        .user-name {
            color: #382313;

            font-size: 13px;

            font-weight: 600;
        }


        /* =========================
           EMAIL
        ========================= */

        .email {
            color: #806c5b;

            font-size: 12px;
        }


        /* =========================
           ROLE BADGE
        ========================= */

        .role-badge {
            display: inline-block;

            padding: 5px 9px;

            border-radius: 6px;

            font-size: 10px;

            font-weight: 600;

            text-transform: capitalize;
        }

        .role-admin {
            background: #fce7d6;

            color: #b45309;
        }

        .role-user {
            background: #e9f3ed;

            color: #32704b;
        }


        /* =========================
           EMPTY STATE
        ========================= */

        .empty-state {
            text-align: center;

            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 35px;

            margin-bottom: 10px;
        }

        .empty-state h3 {
            color: #382313;

            font-size: 17px;

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

                font-size: 17px;
            }

            .brand-text h1 {
                font-size: 15px;
            }

            .brand-text span {
                font-size: 8px;
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

                margin-bottom: 18px;
            }

            .page-title h2 {
                font-size: 23px;
            }

            .page-title p {
                font-size: 11px;
            }

            .user-count {
                display: inline-block;

                margin-top: 10px;

                font-size: 10px;
            }

            .table-card {
                border-radius: 9px;
            }

            .table-top {
                padding:
                    14px 13px;
            }

            .table-top h3 {
                font-size: 13px;
            }

            .table-top span {
                font-size: 9px;
            }

            th,
            td {
                padding:
                    12px 13px;
            }

            .avatar {
                width: 32px;
                height: 32px;

                font-size: 11px;
            }

            .user-name {
                font-size: 12px;
            }

            .email {
                font-size: 11px;
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


        <a href="view_order_items.php">

            <span class="nav-icon">
                ▤
            </span>

            Orders

        </a>


        <a
            href="view_users.php"
            class="active"
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
                    USER MANAGEMENT
                </small>

                <h2>
                    Registered Users
                </h2>

                <p>
                    View the customers and administrators registered in Lutong Nayon.
                </p>

            </div>


            <div class="user-count">

                <strong>
                    <?php echo $user_count; ?>
                </strong>

                <?php echo ($user_count === 1) ? 'registered user' : 'registered users'; ?>

            </div>

        </section>



        <!-- USER TABLE -->

        <section class="table-card">


            <div class="table-top">

                <h3>
                    User Accounts
                </h3>

                <span>
                    Lutong Nayon database
                </span>

            </div>


            <?php if ($user_count > 0): ?>

                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    ID
                                </th>

                                <th>
                                    User
                                </th>

                                <th>
                                    Email
                                </th>

                                <th>
                                    Account Type
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php while ($row = mysqli_fetch_assoc($result)): ?>

                                <?php

                                $name = htmlspecialchars(
                                    $row['name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );

                                $email = htmlspecialchars(
                                    $row['email'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );

                                $id = (int)$row['id'];

                                /*
                                 * Use the first letter of the user's name
                                 * as the avatar.
                                 */
                                $initial = strtoupper(
                                    substr(
                                        trim($row['name']),
                                        0,
                                        1
                                    )
                                );

                                /*
                                 * Check available user type field.
                                 */
                                $role = isset($row['user_type'])
                                    ? $row['user_type']
                                    : (isset($row['type'])
                                        ? $row['type']
                                        : 'user');

                                $role_display = htmlspecialchars(
                                    $role,
                                    ENT_QUOTES,
                                    'UTF-8'
                                );

                                $role_class =
                                    strtolower($role) === 'admin'
                                    ? 'role-admin'
                                    : 'role-user';

                                ?>

                                <tr>

                                    <!-- ID -->

                                    <td data-label="ID">

                                        <span class="user-id">
                                            #<?php echo $id; ?>
                                        </span>

                                    </td>


                                    <!-- NAME -->

                                    <td data-label="Name">

                                        <div class="user-info">

                                            <div class="avatar">
                                                <?php echo htmlspecialchars($initial); ?>
                                            </div>

                                            <div class="user-name">
                                                <?php echo $name; ?>
                                            </div>

                                        </div>

                                    </td>


                                    <!-- EMAIL -->

                                    <td data-label="Email">

                                        <span class="email">
                                            <?php echo $email; ?>
                                        </span>

                                    </td>


                                    <!-- ROLE -->

                                    <td data-label="Account Type">

                                        <span
                                            class="role-badge <?php echo $role_class; ?>"
                                        >
                                            <?php echo $role_display; ?>
                                        </span>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty-state">

                    <div class="empty-icon">
                        👥
                    </div>

                    <h3>
                        No users found
                    </h3>

                    <p>
                        There are currently no registered users in the system.
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