<?php

session_start();

require_once __DIR__ . "/db.php";

$error_message = "";

if (isset($_POST['submit'])) {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // -----------------------------------------
    // VALIDATE INPUT
    // -----------------------------------------

    if (empty($email) || empty($password)) {

        $error_message = "Please enter your email and password.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error_message = "Please enter a valid email address.";

    } else {

        // -----------------------------------------
        // FIND USER
        // -----------------------------------------

        $stmt = $conn->prepare(
            "SELECT id, name, email, password, type
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        if (!$stmt) {

            $error_message = "Database error: " . $conn->error;

        } else {

            $stmt->bind_param("s", $email);

            $stmt->execute();

            $result = $stmt->get_result();

            // -----------------------------------------
            // USER FOUND
            // -----------------------------------------

            if ($result->num_rows > 0) {

                $row = $result->fetch_assoc();

                $storedPassword = $row['password'];

                $passwordCorrect = false;

                // -----------------------------------------
                // CHECK HASHED PASSWORD
                // -----------------------------------------

                if (password_verify($password, $storedPassword)) {

                    $passwordCorrect = true;

                }

                // -----------------------------------------
                // SUPPORT OLD PLAIN-TEXT PASSWORDS
                //
                // This allows accounts created before
                // the password reset system to still login.
                // -----------------------------------------

                elseif ($password === $storedPassword) {

                    $passwordCorrect = true;

                    // -----------------------------------------
                    // AUTOMATICALLY CONVERT OLD PASSWORD
                    // TO A SECURE HASH
                    // -----------------------------------------

                    $newHash = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $updatePassword = $conn->prepare(
                        "UPDATE users
                         SET password = ?
                         WHERE id = ?"
                    );

                    if ($updatePassword) {

                        $updatePassword->bind_param(
                            "si",
                            $newHash,
                            $row['id']
                        );

                        $updatePassword->execute();

                        $updatePassword->close();
                    }
                }

                // -----------------------------------------
                // PASSWORD CORRECT
                // -----------------------------------------

                if ($passwordCorrect) {

                    // Regenerate session ID for security
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $row['id'];

                    $_SESSION['user_name'] = $row['name'];

                    $_SESSION['user_email'] = $row['email'];

                    $_SESSION['user_type'] = $row['type'];

                    // -----------------------------------------
                    // REDIRECT BASED ON ACCOUNT TYPE
                    // -----------------------------------------

                    if ($row['type'] === "admin") {

                        header("Location: admin_dashboard.php");

                        exit();

                    } elseif ($row['type'] === "user") {

                        header("Location: user_dashboard.php");

                        exit();

                    } else {

                        $error_message =
                            "Your account type is not recognized.";
                    }

                } else {

                    $error_message =
                        "Password is wrong!";
                }

            } else {

                $error_message =
                    "Email not found!";
            }

            $stmt->close();
        }
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

<title>Login | Lutong Nayon</title>

<link
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
    rel="stylesheet"
>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {

    background:
        url('image/bannerrt.jpg')
        center/cover
        no-repeat;

    min-height: 100vh;

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    color: #fff;

    overflow-x: hidden;

}

/* -----------------------------------------
   HEADER
----------------------------------------- */

.header {

    text-align: center;

    margin-bottom: 40px;

    position: relative;

    z-index: 2;

}

.header h1 {

    font-size: 4rem;

    font-weight: 700;

    text-shadow:
        0 4px 25px rgba(0, 0, 0, 0.7);

    animation:
        floatText 3s ease-in-out infinite;

}

.header p {

    font-size: 1.6rem;

    font-weight: 500;

    opacity: 0.95;

    text-shadow:
        0 3px 15px rgba(0, 0, 0, 0.6);

    animation:
        fadeInText 2s ease-in-out infinite alternate;

}

/* -----------------------------------------
   ANIMATIONS
----------------------------------------- */

@keyframes floatText {

    0% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-10px);
    }

    100% {
        transform: translateY(0);
    }

}

@keyframes fadeInText {

    0% {
        opacity: 0.7;
    }

    100% {
        opacity: 1;
    }

}

/* -----------------------------------------
   LOGIN CARD
----------------------------------------- */

.login-card {

    width: 380px;

    background:
        rgba(255, 255, 255, 0.08);

    backdrop-filter:
        blur(12px);

    -webkit-backdrop-filter:
        blur(12px);

    padding: 35px 30px;

    border-radius: 15px;

    box-shadow:
        0 10px 30px
        rgba(0, 0, 0, 0.4);

    text-align: center;

    position: relative;

    z-index: 2;

}

/* -----------------------------------------
   STEAM EFFECT
----------------------------------------- */

.login-card::before,
.login-card::after,
.steam-inner {

    content: "";

    position: absolute;

    width: 6px;

    height: 60px;

    background:
        linear-gradient(
            to top,
            rgba(255,255,255,0.4),
            rgba(255,255,255,0)
        );

    border-radius: 50%;

    animation:
        rise 2.5s infinite;

}

.login-card::before {

    left: 20%;

    top: -40px;

    animation-delay: 0s;

}

.login-card::after {

    left: 50%;

    top: -40px;

    animation-delay: 0.5s;

}

.steam-inner {

    left: 80%;

    top: -40px;

    animation-delay: 1s;

}

@keyframes rise {

    0% {

        transform:
            translateY(0)
            scaleX(1);

        opacity: 0;

    }

    50% {

        opacity: 1;

    }

    100% {

        transform:
            translateY(-120px)
            scaleX(1.3);

        opacity: 0;

    }

}

/* -----------------------------------------
   FORM
----------------------------------------- */

.login-card h2 {

    margin-bottom: 12px;

    color: #fff;

    font-weight: 600;

}

.login-card input {

    width: 100%;

    padding: 13px;

    margin-top: 15px;

    border-radius: 8px;

    border: 1px solid #d1d5db;

    font-size: 15px;

    transition: all 0.3s;

    background:
        rgba(255,255,255,0.1);

    color: #fff;

}

.login-card input::placeholder {

    color: #e0e0e0;

}

.login-card input:focus {

    border-color: #f97316;

    box-shadow:
        0 0 6px
        rgba(249, 115, 22, 0.5);

    outline: none;

}

/* -----------------------------------------
   LOGIN BUTTON
----------------------------------------- */

.login-card button {

    width: 100%;

    padding: 14px;

    margin-top: 22px;

    background: #f97316;

    border: none;

    color: white;

    font-size: 16px;

    border-radius: 8px;

    font-weight: 600;

    cursor: pointer;

    transition: 0.3s;

}

.login-card button:hover {

    background: #ea580c;

    transform: translateY(-2px);

    box-shadow:
        0 4px 12px
        rgba(0, 0, 0, 0.25);

}

/* -----------------------------------------
   LINKS
----------------------------------------- */

.switch-auth {

    margin-top: 15px;

    font-size: 14px;

}

.switch-auth a {

    color: #f97316;

    font-weight: 600;

    text-decoration: none;

}

.switch-auth a:hover {

    text-decoration: underline;

}

/* -----------------------------------------
   ERROR MESSAGE
----------------------------------------- */

.error-message {

    color: #991b1b;

    background: #fee2e2;

    padding: 12px;

    border-radius: 8px;

    margin-top: 15px;

    font-weight: 600;

    font-size: 14px;

}

/* -----------------------------------------
   RESPONSIVE
----------------------------------------- */

@media(max-width: 480px) {

    .header h1 {

        font-size: 2.5rem;

    }

    .header p {

        font-size: 1.1rem;

    }

    .login-card {

        width: 90%;

        padding: 25px;

    }

}

</style>

</head>

<body>

<!-- HEADER -->

<div class="header">

    <h1>🍽 Lutong Nayon</h1>

    <p>
        Home-cooked Filipino dishes made with love
    </p>

</div>


<!-- LOGIN CARD -->

<div class="login-card">

    <h2>
        Welcome Back
    </h2>


    <!-- STEAM ANIMATION -->

    <div class="steam-inner"></div>


    <!-- ERROR MESSAGE -->

    <?php if (!empty($error_message)): ?>

        <div class="error-message">

            <?php
            echo htmlspecialchars(
                $error_message,
                ENT_QUOTES,
                'UTF-8'
            );
            ?>

        </div>

    <?php endif; ?>


    <!-- LOGIN FORM -->

    <form
        action=""
        method="post"
    >

        <input
            type="email"
            name="email"
            placeholder="Email Address"
            required
            autocomplete="email"
            value="<?php
                echo htmlspecialchars(
                    $_POST['email'] ?? '',
                    ENT_QUOTES,
                    'UTF-8'
                );
            ?>"
        >


        <input
            type="password"
            name="password"
            placeholder="Password"
            required
            autocomplete="current-password"
        >


        <button
            type="submit"
            name="submit"
        >
            Login
        </button>

    </form>


    <!-- FORGOT PASSWORD -->

    <div class="switch-auth">

        <a href="forgot_password.php">
            Forgot Password?
        </a>

    </div>


    <!-- REGISTER -->

    <div class="switch-auth">

        Don't have an account?

        <a href="register.php">
            Register
        </a>

    </div>

</div>

</body>

</html>