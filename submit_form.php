<?php

// Show fewer errors to the user (but still log)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Load Composer deps (.env)
require __DIR__ . '/vendor/autoload.php';

// Load PHPMailer manually (your old working structure)
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Pull credentials
$smtpUser = $_ENV['SMTP_USER'] ?? '';
$smtpPass = $_ENV['SMTP_PASS'] ?? '';

if ($smtpUser === '' || $smtpPass === '') {
    http_response_code(500);
    exit('Missing SMTP_USER or SMTP_PASS in .env');
}

// Get form inputs
$name        = trim($_POST['name'] ?? '');
$phone       = trim($_POST['phone'] ?? '');
$email       = trim($_POST['email'] ?? '');
$newCustomer = trim($_POST['new_customer'] ?? '');
$service     = trim($_POST['service'] ?? '');
$referral    = trim($_POST['referral'] ?? '');

// Escape helper
function esc($v) {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

$mail = new PHPMailer(true);

try {

    // === YOUR LAST WORKING SMTP BLOCK ===
    $mail->isSMTP();
    $mail->Host       = 'bccochicago.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUser;
    $mail->Password   = $smtpPass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
    $mail->Port       = 465;
    $mail->Timeout    = 15;

    // Debug to error_log ONLY
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function($str, $level) {
        error_log("SMTP DEBUG ($level): $str");
    };
    // ====================================


    // Recipients — BACK TO ORIGINAL
    $mail->setFrom($smtpUser, 'BCCO Website');
    $mail->addAddress($smtpUser); // SENDS TO contact@bccochicago.com
    $mail->addReplyTo($email, $name);

    // Email body
    $mail->isHTML(true);
    $mail->Subject = 'New Contact Form Submission';
    $mail->Body =
        "<strong>Name:</strong> " . esc($name) . "<br>" .
        "<strong>Phone:</strong> " . esc($phone) . "<br>" .
        "<strong>Email:</strong> " . esc($email) . "<br>" .
        "<strong>New Customer:</strong> " . esc($newCustomer) . "<br>" .
        "<strong>Service:</strong> " . esc($service) . "<br>" .
        "<strong>Referral:</strong> " . esc($referral) . "<br>";

    $mail->send();

    echo "Thank you! Your message has been sent.";

} catch (Exception $e) {
    error_log("Mailer error: " . $e->getMessage());
    echo "Oops! Something went wrong. Please try again later.";
}
