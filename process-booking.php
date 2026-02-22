<?php
session_start();
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// 1. LOAD ENVIRONMENT VARIABLES
// This maps the values in your .env file to the $_ENV superglobal [cite: 1, 2]
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$config = [
    'smtp_host'   => $_ENV['SMTP_HOST'],
    'smtp_port'   => $_ENV['SMTP_PORT'],
    'smtp_user'   => $_ENV['SMTP_USER'],
    'smtp_pass'   => $_ENV['SMTP_PASS'],
    'admin_email' => $_ENV['ADMIN_EMAIL'],
];

// 2. DATABASE CONNECTION
// Using credentials from .env for consistency 
$conn = new mysqli($_ENV['DB_SERVER'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_NAME']);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// 3. CAPTURE FORM DATA
$fullname          = $_POST['fullname'] ?? '';
$email             = $_POST['email'] ?? '';
$phone             = $_POST['phone'] ?? '';
$appointment_date  = $_POST['appointment_date'] ?? '';
$appointment_time  = $_POST['appointment_time'] ?? '';
$new_patient       = $_POST['new_patient'] ?? '';
$reason            = $_POST['reason'] ?? '';

// 4. SAVE TO DATABASE
$stmt = $conn->prepare("
    INSERT INTO appointments 
    (fullname, email, phone, appointment_date, appointment_time, new_patient, reason)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param("sssssss", $fullname, $email, $phone, $appointment_date, $appointment_time, $new_patient, $reason);
$stmt->execute();
$booking_id = $stmt->insert_id;
$stmt->close();
$conn->close();

// 5. EMAIL LOGIC
// If not a new patient, send the email immediately
if (strtolower($new_patient) !== "yes") {

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
        $mail->setFrom($config['smtp_user'], 'Dr Taylor GP Appointments');
        $mail->addAddress($config['admin_email']); 

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Appointment Booking (Existing Patient)';
        $mail->Body    = "
            <h2>Appointment Booking</h2>
            <p><strong>Name:</strong> $fullname</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Phone:</strong> $phone</p>
            <p><strong>Date:</strong> $appointment_date</p>
            <p><strong>Time:</strong> $appointment_time</p>
            <p><strong>Reason:</strong> $reason</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        // Log error but don't stop the user experience
        error_log("Mailer Error: {$mail->ErrorInfo}");
    }

    header("Location: thank-you.html");
    exit();
}

// If new patient, redirect to portal
header("Location: patient-portal.php?booking_id=" . $booking_id);
exit();
?>