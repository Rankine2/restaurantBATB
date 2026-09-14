<?php

date_default_timezone_set('Asia/Manila');

session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/smtp_mailer.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');

    // -----------------------------------------
    // CHECK EMAIL
    // -----------------------------------------

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        // -----------------------------------------
        // FIND USER
        // -----------------------------------------

        $stmt = $conn->prepare(
            "SELECT id, name, email
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        $user = $result->fetch_assoc();

        $stmt->close();

        if (!$user) {

            $error = "No account was found with that email address.";

        } else {

            // -----------------------------------------
            // DELETE OLD RESET CODES
            // -----------------------------------------

            $delete = $conn->prepare(
                "DELETE FROM password_resets
                 WHERE email = ?"
            );

            $delete->bind_param("s", $email);

            $delete->execute();

            $delete->close();


            // -----------------------------------------
            // GENERATE 6-DIGIT OTP
            // -----------------------------------------

            try {

                $otp = (string) random_int(100000, 999999);

            } catch (Exception $e) {

                $error = "Unable to generate a verification code.";

                $otp = null;
            }


            if ($otp !== null) {

                // -----------------------------------------
                // OTP EXPIRATION
                // 10 MINUTES FROM NOW
                // -----------------------------------------

                $expiresAt = date(
                    'Y-m-d H:i:s',
                    time() + (10 * 60)
                );


                // -----------------------------------------
                // SAVE OTP TO DATABASE
                // -----------------------------------------

                $insert = $conn->prepare(
                    "INSERT INTO password_resets
                    (user_id, email, code, expires_at)
                    VALUES (?, ?, ?, ?)"
                );

                $insert->bind_param(
                    "isss",
                    $user['id'],
                    $email,
                    $otp,
                    $expiresAt
                );


                if ($insert->execute()) {

                    $insert->close();


                    // -----------------------------------------
                    // SEND OTP EMAIL
                    // -----------------------------------------

                    $mailResult = sendOTPEmail(
                        $email,
                        $user['name'],
                        $otp
                    );


                    if ($mailResult['success']) {

                        // -----------------------------------------
                        // SAVE EMAIL IN SESSION
                        // -----------------------------------------

                        $_SESSION['reset_email'] = $email;

                        header(
                            "Location: reset_password.php"
                        );

                        exit;

                    } else {

                        // -----------------------------------------
                        // REMOVE OTP IF EMAIL FAILED
                        // -----------------------------------------

                        $cleanup = $conn->prepare(
                            "DELETE FROM password_resets
                             WHERE email = ?"
                        );

                        $cleanup->bind_param(
                            "s",
                            $email
                        );

                        $cleanup->execute();

                        $cleanup->close();


                        $error =
                            "The reset code could not be emailed."
                            . "<br><br>Details: "
                            . htmlspecialchars(
                                $mailResult['message'],
                                ENT_QUOTES,
                                'UTF-8'
                            );
                    }

                } else {

                    $insert->close();

                    $error =
                        "Unable to create the password reset code.";
                }
            }
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

    <title>Forgot Password - Lutong Nayon</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;

            font-family: Arial, sans-serif;

            background: #dcc59e;
        }

        .container {
            width: 90%;
            max-width: 450px;

            background: white;

            padding: 35px;

            border-radius: 15px;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.15);
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 14px;

            border: 1px solid #ccc;

            border-radius: 8px;

            font-size: 16px;

            margin-bottom: 20px;
        }

        input:focus {
            outline: none;
            border-color: #8b4513;
        }

        button {
            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 8px;

            background: #8b4513;

            color: white;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;
        }

        button:hover {
            background: #6f350f;
        }

        .error {
            background: #fde2e2;

            color: #a52a2a;

            padding: 15px;

            border-radius: 8px;

            margin-bottom: 20px;

            text-align: center;

            line-height: 1.5;
        }

        .info {
            background: #f7f2e8;

            color: #555;

            padding: 12px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-size: 14px;

            line-height: 1.5;
        }

        .back {
            display: block;

            text-align: center;

            margin-top: 20px;

            color: #8b4513;

            text-decoration: none;
        }

        .back:hover {
            text-decoration: underline;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>Forgot Password?</h1>

    <p class="subtitle">
        Enter your email and we'll send you a 6-digit reset code.
    </p>


    <?php if ($error): ?>

        <div class="error">
            <?= $error ?>
        </div>

    <?php endif; ?>


    <div class="info">
        Your verification code will be valid for
        <strong>10 minutes</strong>.
    </div>


    <form method="POST">

        <label for="email">
            Email Address
        </label>

        <input
            type="email"
            id="email"
            name="email"
            placeholder="Enter your Gmail address"
            required
            autocomplete="email"
        >

        <button type="submit">
            Send Reset Code
        </button>

    </form>


    <a
        class="back"
        href="login.php"
    >
        ← Back to Login
    </a>

</div>

</body>

</html>