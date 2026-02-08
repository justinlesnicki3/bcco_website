<?php

// Show errors while testing (optional)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Only allow POST
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  http_response_code(405);
  exit('Method Not Allowed');
}

/**
 * Load Composer deps (dotenv)
 * Requires: public_html/vendor/autoload.php
 */
require __DIR__ . '/vendor/autoload.php';

/**
 * Load PHPMailer manually from src
 * Requires: public_html/PHPMailer/src/{PHPMailer.php,SMTP.php,Exception.php}
 */
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Grab inputs
$name        = trim($_POST['name'] ?? '');
$phone       = trim($_POST['phone'] ?? '');
$email       = trim($_POST['email'] ?? '');
$newCustomer = trim($_POST['new_customer'] ?? '');
$service     = trim($_POST['service'] ?? '');
$referral    = trim($_POST['referral'] ?? '');

// Validate required
if ($name === '' || $phone === '' || $email === '' || $newCustomer === '' || $service === '') {
  http_response_code(400);
  exit('Missing required fields.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  exit('Invalid email address.');
}

// Ensure env vars exist
if (empty($_ENV['SMTP_USER']) || empty($_ENV['SMTP_PASS'])) {
  http_response_code(500);
  exit('Server misconfigured: missing SMTP_USER/SMTP_PASS in .env');
}

// Escape helper (no arrow function needed)
function esc($v) {
  return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

$mail = new PHPMailer(true);

try {
  // === YOUR “WORKING” SMTP BLOCK ===
  $mail->isSMTP();
  $mail->Host       = 'bccochicago.com';
  $mail->SMTPAuth   = true;
  $mail->Username   = $_ENV['SMTP_USER'];
  $mail->Password   = $_ENV['SMTP_PASS'];

  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
  $mail->Port       = 465;

  $mail->Timeout = 15;
  $mail->SMTPDebug = 2;
  $mail->Debugoutput = function($str, $level) {
      error_log("SMTP DEBUG ($level): $str");
  };
  // ================================

  // Recipients
  $mail->setFrom($_ENV['SMTP_USER'], 'BCCO Website');
  $mail->addAddress('jbasiorka@teambcco.com');   // <-- where you want submissions
  $mail->addReplyTo($email, $name);

  // Content
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
  http_response_code(500);
  // Show a minimal message to the browser
  echo "Mailer Error: " . $mail->ErrorInfo;
  // And log the real exception
  error_log("Mailer Exception: " . $e->getMessage());
}
