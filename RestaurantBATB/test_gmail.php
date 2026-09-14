<?php
require_once __DIR__ . '/smtp_mailer.php';

$config = require __DIR__ . '/mail_config.php';

$result = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = trim($_POST['email'] ?? '');

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $result = '<div class="error">Enter a valid recipient email.</div>';
    } else {
        try {
            $otp = (string) random_int(100000, 999999);
            $body = '<h2>Lutong Nayon Gmail Test</h2>'
                  . '<p>Your test OTP is:</p>'
                  . '<h1 style="letter-spacing:8px">' . htmlspecialchars($otp) . '</h1>'
                  . '<p>If you received this message, Gmail SMTP is working.</p>';

            smtp_send_gmail($config, $to, 'Lutong Nayon Gmail Test', $body);
            $result = '<div class="success">Test email sent. Check the recipient inbox and Spam folder.</div>';
        } catch (Throwable $e) {
            $result = '<div class="error"><strong>SMTP failed:</strong><br>'
                    . nl2br(htmlspecialchars($e->getMessage())) . '</div>';
        }
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Gmail SMTP Test</title>
<style>body{font-family:Arial;max-width:600px;margin:60px auto;padding:20px}input,button{width:100%;padding:12px;margin:8px 0;box-sizing:border-box}button{cursor:pointer}.success{background:#dcfce7;padding:12px}.error{background:#fee2e2;padding:12px}</style>
</head><body>
<h2>Gmail SMTP Test</h2>
<p>This page tests the Gmail settings in <code>mail_config.php</code>.</p>
<?= $result ?>
<form method="post">
<input type="email" name="email" placeholder="Recipient email" required>
<button type="submit">Send Test Email</button>
</form>
<p><a href="forgot_password.php">Back to Forgot Password</a></p>
</body></html>
