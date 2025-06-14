<?php

require 'vendor/autoload.php'; // Loads dotenv & PHPMailer

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

$mail = new PHPMailer(true);
$mail->SMTPDebug = 2; // or 3 for more detail
$mail->Debugoutput = 'html';

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';            // ✅ Gmail SMTP
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USER'];
    $mail->Password = $_ENV['SMTP_PASS'];
    $mail->SMTPSecure = 'tls';                // ✅ TLS for port 587
    $mail->Port = 587;

    // Recipients
    $mail->setFrom($_ENV['SMTP_USER'], 'BCCO Website');
    $mail->addReplyTo($_POST['email'], $_POST['name']);             // ✅ Customer's info
    $mail->addAddress($_ENV['SMTP_USER']);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'New Contact Form Submission';
    $mail->Body = "
        <strong>Name:</strong> {$_POST['name']}<br>
        <strong>Phone:</strong> {$_POST['phone']}<br>
        <strong>Email:</strong> {$_POST['email']}<br>
        <strong>New Customer:</strong> {$_POST['new_customer']}<br>
        <strong>Service:</strong> {$_POST['service']}<br>
        <strong>Referral:</strong> {$_POST['referral']}<br>
    ";

        $mail->send();
    echo "Thank you! Your message has been sent.";
} catch (Exception $e) {
    echo "Oops! Something went wrong. Mailer Error: {$mail->ErrorInfo}";
}
