<?php

// Show errors temporarily while testing (turn off later)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1) Load Composer deps (dotenv)
require __DIR__ . '/vendor/autoload.php';

// 2) Load PHPMailer manually from src/
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 3) Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Basic input grabbing + cleanup
$name        = trim($_POST['name'] ?? '');
$phone       = trim($_POST['phone'] ?? '');
$email       = trim($_POST['email'] ?? '');
$newCustomer = trim($_POST['new_customer'] ?? '');
$service     = trim($_POST['service'] ?? 'website-contact');
$referral    = trim($_POST['referral'] ?? '');
$message     = trim($_POST['message'] ?? '');

// Minimal validation (works for BOTH forms)
if ($name === '' || $phone === '' || $email === '' || $newCustomer === '') {
  http_response_code(400);
  exit('Missing required fields.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  exit('Invalid email address.');
}

// Escape helper (define ONCE)
$esc = function ($v) {
  return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
};

$mail = new PHPMailer(true);

try {
  // Server settings
  $mail->isSMTP();
  $mail->Host       = 'bccochicago.com';
  $mail->SMTPAuth   = true;
  $mail->AuthType   = 'LOGIN';

  // Load and trim env vars safely
  $smtpUser = trim($_ENV['SMTP_USER'] ?? '');
  $smtpPass = trim($_ENV['SMTP_PASS'] ?? '');

  $mail->Username   = $smtpUser;
  $mail->Password   = $smtpPass;

  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
  $mail->Port       = 465;
  $mail->Timeout    = 15;

  // Debug while testing (set to 0 later)
  $mail->SMTPDebug  = 2;
  $mail->Debugoutput = function ($str, $level) {
    error_log("SMTP DEBUG ($level): $str");
  };

  if ($mail->Username === '' || $mail->Password === '') {
    throw new Exception('Missing SMTP_USER / SMTP_PASS. Check your .env on the server.');
  }

  // Recipients
  $mail->setFrom($mail->Username, 'BCCO Website');
  $mail->addAddress($mail->Username);
  $mail->addReplyTo($email, $name);

  // Content
  $mail->isHTML(true);
  $mail->Subject = 'New Contact Form Submission';

  // ✅ Build BODY first, then assign once
  $body =
    "<strong>Name:</strong> " . $esc($name) . "<br>" .
    "<strong>Phone:</strong> " . $esc($phone) . "<br>" .
    "<strong>Email:</strong> " . $esc($email) . "<br>" .
    "<strong>New Customer:</strong> " . $esc($newCustomer) . "<br>" .
    "<strong>Service:</strong> " . $esc($service) . "<br>";

  if ($referral !== '') {
    $body .= "<strong>Referral:</strong> " . $esc($referral) . "<br>";
  }

  if ($message !== '') {
    $body .= "<strong>Message:</strong><br>" . nl2br($esc($message)) . "<br>";
  }

  $mail->Body = $body;

  $mail->send();
  echo "Thank you! Your message has been sent.";

} catch (Exception $e) {
  http_response_code(500);
  echo "Oops! Something went wrong. Mailer Error: " . $mail->ErrorInfo . " | " . $e->getMessage();
}
