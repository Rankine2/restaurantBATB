<?php
/**
 * Gmail SMTP mailer - NO Composer / NO PHPMailer required.
 * Requires PHP OpenSSL to be enabled in XAMPP.
 */

function smtp_read_response($socket)
{
    $response = '';

    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (strlen($line) >= 4 && $line[3] === ' ') {
            break;
        }
    }

    if ($response === '') {
        throw new RuntimeException('No response received from Gmail SMTP server.');
    }

    return $response;
}

function smtp_expect($socket, $expectedCodes)
{
    $response = smtp_read_response($socket);
    $code = (int) substr(trim($response), 0, 3);

    if (!in_array($code, $expectedCodes, true)) {
        throw new RuntimeException('Gmail SMTP error: ' . trim($response));
    }

    return $response;
}

function smtp_command($socket, $command, $expectedCodes)
{
    fwrite($socket, $command . "\r\n");
    return smtp_expect($socket, $expectedCodes);
}

function smtp_send_gmail($config, $to, $subject, $htmlBody)
{
    if (!extension_loaded('openssl')) {
        throw new RuntimeException('PHP OpenSSL is disabled. Enable extension=openssl in C:\\xampp\\php\\php.ini and restart Apache.');
    }

    $username  = trim($config['username'] ?? '');
    $password  = trim($config['password'] ?? '');
    $fromEmail = trim($config['from_email'] ?? '');
    $fromName  = trim($config['from_name'] ?? 'Lutong Nayon');

    if ($username === '' || $password === '' || $fromEmail === '') {
        throw new RuntimeException('Gmail settings are incomplete in mail_config.php.');
    }

    if (strpos($username, 'YOUR_') === 0 || strpos($password, 'YOUR_') === 0) {
        throw new RuntimeException('Replace the Gmail placeholders in mail_config.php with your Gmail address and Google App Password.');
    }

    if (!filter_var($username, FILTER_VALIDATE_EMAIL) || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('The Gmail sender address in mail_config.php is not valid.');
    }

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('The recipient email address is not valid.');
    }

    $host = $config['host'] ?? 'smtp.gmail.com';
    $port = (int) ($config['port'] ?? 587);
    $timeout = 20;

    $socket = @stream_socket_client(
        'tcp://' . $host . ':' . $port,
        $errno,
        $errstr,
        $timeout,
        STREAM_CLIENT_CONNECT
    );

    if (!$socket) {
        throw new RuntimeException('Could not connect to Gmail SMTP: ' . $errstr . ' (' . $errno . '). Check internet connection/firewall and port 587.');
    }

    stream_set_timeout($socket, $timeout);

    try {
        smtp_expect($socket, [220]);
        smtp_command($socket, 'EHLO localhost', [250]);
        smtp_command($socket, 'STARTTLS', [220]);

        $cryptoOk = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        if ($cryptoOk !== true) {
            throw new RuntimeException('TLS could not be started. Make sure OpenSSL is enabled in XAMPP.');
        }

        smtp_command($socket, 'EHLO localhost', [250]);
        smtp_command($socket, 'AUTH LOGIN', [334]);
        smtp_command($socket, base64_encode($username), [334]);
        smtp_command($socket, base64_encode($password), [235]);
        smtp_command($socket, 'MAIL FROM:<' . $fromEmail . '>', [250]);
        smtp_command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
        smtp_command($socket, 'DATA', [354]);

        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';

        $headers = [
            'From: ' . $encodedFromName . ' <' . $fromEmail . '>',
            'To: <' . $to . '>',
            'Subject: ' . $encodedSubject,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];

        $body = preg_replace("/\r\n|\r|\n/", "\r\n", $htmlBody);
        $body = preg_replace('/^\./m', '..', $body);

        $message = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
        smtp_command($socket, $message, [250]);
        smtp_command($socket, 'QUIT', [221]);
    } finally {
        fclose($socket);
    }
}

/**
 * This is the function your forgot_password.php calls.
 */
function sendOTPEmail($toEmail, $toName, $otp)
{
    $config = require __DIR__ . '/mail_config.php';

    $safeName = htmlspecialchars($toName ?: 'Customer', ENT_QUOTES, 'UTF-8');
    $safeOtp = htmlspecialchars($otp, ENT_QUOTES, 'UTF-8');

    $htmlBody = '
        <div style="font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:25px;border:1px solid #ddd;border-radius:10px;color:#222">
            <h2 style="text-align:center">Lutong Nayon</h2>
            <h3>Password Reset Request</h3>
            <p>Hello ' . $safeName . ',</p>
            <p>Your 6-digit password reset code is:</p>
            <div style="font-size:32px;font-weight:bold;letter-spacing:8px;padding:18px;text-align:center;background:#f5f5f5;border-radius:8px">' . $safeOtp . '</div>
            <p>This code expires in <strong>15 minutes</strong>.</p>
            <p>If you did not request a password reset, you can ignore this email.</p>
            <p>— Lutong Nayon</p>
        </div>';

    try {
        smtp_send_gmail(
            $config,
            $toEmail,
            'Your Lutong Nayon Password Reset Code',
            $htmlBody
        );

        return [
            'success' => true,
            'message' => 'OTP sent successfully.'
        ];
    } catch (Throwable $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
?>
