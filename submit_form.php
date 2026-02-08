<?php
// submit_form.php

// Always log errors server-side (don’t show to visitors)
ini_set('log_errors', 1);
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Only allow POST
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  http_response_code(405);
  exit('Method Not Allowed');
}

/**
 * 1) Load dotenv via Composer
 *    (vendor/ must exist and contain autoload.php + vlucas/phpdotenv)
 */
require __DIR__ . '/vendor/autoload.php';

/**
 * 2) Load PHPMailer manually (because you have PHPMailer/src/* on server)
 *    Folder structure assumed:
 *    public_html/PHPMailer/src/PHPMailer.php, SMTP.php, Exception.php
 */
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load environment variables (.env in same folder)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// ---- Grab + sanitize inputs ----
$name        = trim($_POST['name'] ?? '');
$phone       = trim($_POST['phone'] ?? '');
$email       = trim($_POST['email'] ?? '');
$newCustomer = trim($_POST['new_customer'] ?? '');
$service     = trim($_POST['service'] ?? '');
$referral    = trim($_POST['referral'] ?? '');

// ---- Validate ----
if ($name === '' || $phone === '' || $email === '' || $newCustomer === '' || $service === '') {
  http_response_code(400);
  exit('Missing required fields.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  exit('Invalid email address.');
}

// ---- SMTP creds from .env ----
$smtpUser = $_ENV['SMTP_USER'] ?? '';
$smtpPass = $_ENV['SMTP_PASS'] ?? '';

if ($smtpUser === '' || $smtpPass === '') {
  http_response_code(500);
  exit('Server misconfigured: missing SMTP_USER/SMTP_PASS.');
}

// ---- Where submissions should go ----
$toAddress = 'jbasiorka@teambcco.com';

// Escape for HTML email body
function esc($v) {
  return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

$mail = new PHPMailer(true);

try {
  // ---- SMTP Settings (based on your cPanel screenshot) ----
  // Outgoing server: bccochicago.com
  // SMTP port: 465
  // Encryption: SSL/TLS
  $mail->isSMTP();
  $mail->Host       = 'bccochicago.com';
  $mail->SMTPAuth   = true;
  $mail->Username   = $smtpUser;
  $mail->Password   = $smtpPass;
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL/TLS for 465
  $mail->Port       = 465;

  // Prevent “loads forever”
  $mail->Timeout = 15;

  // Debugging (writes to error_log only if SMTP_DEBUG=1 in .env)
  $smtpDebug = ($_ENV['SMTP_DEBUG'] ?? '0') === '1';
  if ($smtpDebug) {
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function ($str, $level) {
      error_log("SMTP DEBUG ($level): $str");
    };
  } else {
    $mail->SMTPDebug = 0;
  }

  // ---- Email Headers ----
  $mail->setFrom($smtpUser, 'BCCO Website');
  $mail->addAddress($toAddress);          // <-- sends to your real email
  $mail->addReplyTo($email, $name);       // replies go to the customer

  // ---- Content ----
  $mail->isHTML(true);
  $mail->Subject = 'New Contact Form Submission';

  $mail->Body =
    "<strong>Name:</strong> " . esc($name) . "<br>" .
    "<strong>Phone:</strong> " . esc($phone) . "<br>" .
    "<strong>Email:</strong> " . esc($email) . "<br>" .
    "<strong>New Customer:</strong> " . esc($newCustomer) . "<br>" .
    "<strong>Service:</strong> " . esc($service) . "<br>" .
    "<strong>Referral:</strong> " . esc($referral) . "<br>";

  $mail->AltBody =
    "Name: $name\n" .
    "Phone: $phone\n" .
    "Email: $email\n" .
    "New Customer: $newCustomer\n" .
    "Service: $service\n" .
    "Referral: $referral\n";

  $mail->send();

  // Redirect back to homepage (optional)
  // Change this to your contact page if you want
  header('Location: /?sent=1');
  exit;

} catch (Exception $e) {
  error_log("Mailer ErrorInfo: " . $mail->ErrorInfo);
  error_log("Mailer Exception: " . $e->getMessage());

  http_response_code(500);
  // Keep response minimal for visitors
  echo "Oops! Something went wrong. Please try again later.";
  exit;
}
