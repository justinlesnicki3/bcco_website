<?php

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Load .env (must be in the same folder as this file)
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$mail = new PHPMailer(true);
$mail->SMTPDebug = 2; // set to 0 after testing
$mail->Debugoutput = 'html';

try {
    // Basic input safety (prevents "Undefined index" issues)
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $newCustomer = $_POST['new_customer'] ?? '';
    $service = $_POST['service'] ?? '';
    $referral = $_POST['referral'] ?? '';

    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USER'] ?? '';
    $mail->Password = $_ENV['SMTP_PASS'] ?? '';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Recipients
    $mail->setFrom($_ENV['SMTP_USER'], 'BCCO Website');
    if ($email !== '') {
        $mail->addReplyTo($email, $name);
    }
    $mail->addAddress('jbasiorka@teambcco.com');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'New Contact Form Submission';
    $mail->Body = "
        <strong>Name:</strong> {$name}<br>
        <strong>Phone:</strong> {$phone}<br>
        <strong>Email:</strong> {$email}<br>
        <strong>New Customer:</strong> {$newCustomer}<br>
        <strong>Service:</strong> {$service}<br>
        <strong>Referral:</strong> {$referral}<br>
    ";

    $mail->send();
    echo "Thank you! Your message has been sent.";
} catch (Exception $e) {
    echo "Oops! Something went wrong. Mailer Error: {$mail->ErrorInfo}";
}
