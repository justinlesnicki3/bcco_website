<?php

require __DIR__ . '/vendor/autoload.php'; // loads PHPMailer + dotenv via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$mail = new PHPMailer(true);
$mail->SMTPDebug = 2; // set to 0 after testing
$mail->Debugoutput = 'html';

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USER'];
    $mail->Password = $_ENV['SMTP_PASS'];
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom($_ENV['SMTP_USER'], 'BCCO Website');
    $mail->addReplyTo($_POST['email'], $_POST['name']);
    $mail->addAddress('jbasiorka@teambcco.com');

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
