<?php

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad(); // won't fatal if .env missing, but your creds will be empty

// Basic input grabbing + cleanup
$name        = trim($_POST['name'] ?? '');
$phone       = trim($_POST['phone'] ?? '');
$email       = trim($_POST['email'] ?? '');
$newCustomer = trim($_POST['new_customer'] ?? '');
$service     = trim($_POST['service'] ?? '');
$referral    = trim($_POST['referral'] ?? '');

// Minimal validation
if ($name === '' || $phone === '' || $email === '' || $newCustomer === '' || $service === '') {
  http_response_code(400);
  exit('Missing required fields.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  exit('Invalid email address.');
}

$mail = new PHPMailer(true);

try {
  // Server settings
  $mail->isSMTP();
  $mail->Host       = 'smtp.gmail.com';
  $mail->SMTPAuth   = true;
  $mail->Username   = $_ENV['SMTP_USER'] ?? '';
  $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mail->Port       = 587;

  // IMPORTANT: turn debug OFF in production
  $mail->SMTPDebug  = 0;

  if ($mail->Username === '' || $mail->Password === '') {
    throw new Exception('Missing SMTP_USER / SMTP_PASS. Check your .env on the server.');
  }

  // Recipients
  $mail->setFrom($mail->Username, 'BCCO Website');
  $mail->addAddress($mail->Username);
  $mail->addReplyTo($email, $name);

  // Content (escape user input)
  $esc = fn($v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

  $mail->isHTML(true);
  $mail->Subject = 'New Contact Form Submission';
  $mail->Body =
    "<strong>Name:</strong> {$esc($name)}<br>" .
    "<strong>Phone:</strong> {$esc($phone)}<br>" .
    "<strong>Email:</strong> {$esc($email)}<br>" .
    "<strong>New Customer:</strong> {$esc($newCustomer)}<br>" .
    "<strong>Service:</strong> {$esc($service)}<br>" .
    "<strong>Referral:</strong> {$esc($referral)}<br>";

  $mail->send();
  echo "Thank you! Your message has been sent.";
} catch (Exception $e) {
  http_response_code(500);
  echo "Oops! Something went wrong. Mailer Error: " . $mail->ErrorInfo;
}
