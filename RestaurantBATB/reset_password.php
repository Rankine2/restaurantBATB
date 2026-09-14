<?php

date_default_timezone_set('Asia/Manila');

session_start();

require_once __DIR__ . '/db.php';

$error = '';
$success = '';


// ---------------------------------------------------------
// CHECK SESSION
// ---------------------------------------------------------

if (!isset($_SESSION['reset_email'])) {

    header("Location: forgot_password.php");

    exit;
}

$email = $_SESSION['reset_email'];


// ---------------------------------------------------------
// PROCESS FORM
// ---------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $otp = trim($_POST['otp'] ?? '');

    $newPassword = $_POST['new_password'] ?? '';

    $confirmPassword = $_POST['confirm_password'] ?? '';


    // -----------------------------------------------------
    // CHECK OTP FORMAT
    // -----------------------------------------------------

    if (!preg_match('/^[0-9]{6}$/', $otp)) {

        $error = "Please enter the 6-digit verification code.";

    }

    // -----------------------------------------------------
    // CHECK PASSWORD
    // -----------------------------------------------------

    elseif (strlen($newPassword) < 6) {

        $error =
            "Your new password must contain at least 6 characters.";

    }

    elseif ($newPassword !== $confirmPassword) {

        $error = "The passwords do not match.";

    }

    else {

        // -------------------------------------------------
        // GET MOST RECENT OTP
        // -------------------------------------------------

        $stmt = $conn->prepare(
            "SELECT
                id,
                user_id,
                email,
                code,
                expires_at
             FROM password_resets
             WHERE email = ?
             ORDER BY id DESC
             LIMIT 1"
        );

        $stmt->bind_param(
            "s",
            $email
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $reset = $result->fetch_assoc();

        $stmt->close();


        // -------------------------------------------------
        // OTP DOES NOT EXIST
        // -------------------------------------------------

        if (!$reset) {

            $error =
                "No reset code was found. Please request a new code.";

        }

        else {

            // -------------------------------------------------
            // CHECK EXPIRATION
            // -------------------------------------------------

            $currentTime = time();

            $expirationTime = strtotime(
                $reset['expires_at']
            );


            if ($expirationTime === false) {

                $error =
                    "There was a problem checking the code expiration.";

            }

            elseif ($currentTime > $expirationTime) {

                // ---------------------------------------------
                // DELETE EXPIRED OTP
                // ---------------------------------------------

                $delete = $conn->prepare(
                    "DELETE FROM password_resets
                     WHERE id = ?"
                );

                $delete->bind_param(
                    "i",
                    $reset['id']
                );

                $delete->execute();

                $delete->close();


                $error =
                    "Your reset code has expired. "
                    . "Please request a new code.";

            }

            // -------------------------------------------------
            // CHECK OTP
            // -------------------------------------------------

            elseif (!hash_equals(
                (string) $reset['code'],
                (string) $otp
            )) {

                $error =
                    "Incorrect reset code. Please check your email "
                    . "and try again.";

            }

            else {

                // -------------------------------------------------
                // OTP IS VALID
                // -------------------------------------------------

                // Hash the new password
                $hashedPassword = password_hash(
                    $newPassword,
                    PASSWORD_DEFAULT
                );


                // -------------------------------------------------
                // UPDATE USER PASSWORD
                // -------------------------------------------------

                $update = $conn->prepare(
                    "UPDATE users
                     SET password = ?
                     WHERE id = ?"
                );

                $update->bind_param(
                    "si",
                    $hashedPassword,
                    $reset['user_id']
                );


                if ($update->execute()) {

                    $update->close();


                    // -------------------------------------------------
                    // DELETE USED OTP
                    // -------------------------------------------------

                    $delete = $conn->prepare(
                        "DELETE FROM password_resets
                         WHERE id = ?"
                    );

                    $delete->bind_param(
                        "i",
                        $reset['id']
                    );

                    $delete->execute();

                    $delete->close();


                    // -------------------------------------------------
                    // REMOVE RESET SESSION
                    // -------------------------------------------------

                    unset($_SESSION['reset_email']);


                    // -------------------------------------------------
                    // SUCCESS
                    // -------------------------------------------------

                    $success =
                        "Your password has been successfully reset.";


                } else {

                    $update->close();

                    $error =
                        "Unable to update your password. "
                        . "Please try again.";
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

    <title>Reset Password - Lutong Nayon</title>

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

        .email {
            text-align: center;

            color: #666;

            margin-bottom: 25px;

            word-break: break-word;
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

            margin-bottom: 18px;
        }

        input:focus {
            outline: none;

            border-color: #8b4513;
        }

        .otp {
            text-align: center;

            font-size: 24px;

            letter-spacing: 8px;

            font-weight: bold;
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

            margin-top: 5px;
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

        .success {
            background: #e3f7e8;

            color: #236b35;

            padding: 15px;

            border-radius: 8px;

            margin-bottom: 20px;

            text-align: center;

            line-height: 1.5;
        }

        .timer {
            text-align: center;

            margin: 10px 0 20px;

            color: #666;
        }

        .timer strong {
            color: #8b4513;
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

    <h1>Reset Password</h1>


    <?php if ($success): ?>

        <div class="success">

            <?= htmlspecialchars(
                $success,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

            <br><br>

            <a
                href="login.php"
                style="color:#236b35;"
            >
                Go to Login
            </a>

        </div>

    <?php else: ?>


        <p class="email">

            Code sent to:

            <strong>
                <?= htmlspecialchars(
                    $email,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </strong>

        </p>


        <?php if ($error): ?>

            <div class="error">

                <?= $error ?>

            </div>

        <?php endif; ?>


        <div class="timer">

            Code expires in:

            <strong id="timer">
                10:00
            </strong>

        </div>


        <form method="POST">

            <label for="otp">
                6-Digit Verification Code
            </label>

            <input
                type="text"
                id="otp"
                name="otp"
                class="otp"
                maxlength="6"
                pattern="[0-9]{6}"
                inputmode="numeric"
                placeholder="000000"
                required
                autocomplete="one-time-code"
            >


            <label for="new_password">
                New Password
            </label>

            <input
                type="password"
                id="new_password"
                name="new_password"
                minlength="6"
                placeholder="Enter new password"
                required
                autocomplete="new-password"
            >


            <label for="confirm_password">
                Confirm New Password
            </label>

            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                minlength="6"
                placeholder="Confirm new password"
                required
                autocomplete="new-password"
            >


            <button type="submit">
                Reset Password
            </button>

        </form>


        <a
            class="back"
            href="forgot_password.php"
        >
            Request a New Code
        </a>

    <?php endif; ?>

</div>


<script>

// ---------------------------------------------------------
// 10 MINUTE DISPLAY TIMER
// ---------------------------------------------------------

let timeLeft = 10 * 60;

const timerElement =
    document.getElementById('timer');


function updateTimer() {

    if (!timerElement) {
        return;
    }

    const minutes =
        Math.floor(timeLeft / 60);

    const seconds =
        timeLeft % 60;


    timerElement.textContent =
        String(minutes).padStart(2, '0')
        + ':'
        + String(seconds).padStart(2, '0');


    if (timeLeft > 0) {

        timeLeft--;

        setTimeout(updateTimer, 1000);

    } else {

        timerElement.textContent =
            'Expired';
    }
}


updateTimer();

</script>

</body>

</html>