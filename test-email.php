<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Load environment variables 
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$mail = new PHPMailer(true);

try {
    // Server settings using your .env values 
    $mail->isSMTP();
    $mail->Host       = $_ENV['SMTP_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['SMTP_USER']; 
    $mail->Password   = $_ENV['SMTP_PASS']; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
    $mail->Port       = $_ENV['SMTP_PORT'];

    // Recipients
    $mail->setFrom($_ENV['SMTP_USER'], 'Connection Test');
    $mail->addAddress($_ENV['ADMIN_EMAIL']); 

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'SMTP Configuration Test';
    $mail->Body    = '<h1>Success!</h1><p>If you are reading this, your PHP script is successfully communicating with Gmail SMTP.</p>';

    $mail->send();
    echo 'Message has been sent successfully. Check your inbox at: ' . $_ENV['ADMIN_EMAIL'];
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>