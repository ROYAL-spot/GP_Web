<?php
// 1. Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Load .env - Ensure this file is NOT in your public GitHub repo
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// 2. Database Connection (Prepared Statements used throughout)
$conn = new mysqli(
    $_ENV['DB_SERVER'] ?? 'localhost',
    $_ENV['DB_USERNAME'] ?? 'root',
    $_ENV['DB_PASSWORD'] ?? '',
    $_ENV['DB_NAME'] ?? 'GP_web_db'
);

if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("A system error occurred. Please try again later.");
}

// 3. Data Sanitization
$name       = filter_var(trim($_POST['fullname'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS);
$email      = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phone      = filter_var(trim($_POST['phone'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS);
$newPatient = $_POST['new_patient'] ?? 'no';
$date       = $_POST['appointment_date'] ?? '';
$time       = $_POST['appointment_time'] ?? '';
$reason     = filter_var(trim($_POST['reason'] ?? 'General Consultation'), FILTER_SANITIZE_SPECIAL_CHARS);

// 4. Server-Side Validation
if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid input. Please check your name and email.");
}

// Check for past dates
if (strtotime($date) < strtotime(date('Y-m-d'))) {
    die("Appointments cannot be booked for past dates.");
}

// 5. Database Insertion
$sql = "INSERT INTO appointments (fullname, email, phone, appointment_date, appointment_time, new_patient, reason) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $name, $email, $phone, $date, $time, $newPatient, $reason);

if ($stmt->execute()) {
    // 6. Secure Email Notification
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USERNAME'];
        $mail->Password   = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV['SMTP_PORT'];

        $mail->setFrom($_ENV['SMTP_USERNAME'], 'Dr Taylor GP Clinic');
        $mail->addAddress($email, $name); // Send confirmation to patient
        $mail->addBCC($_ENV['SMTP_USERNAME']); // Copy to doctor

        $mail->isHTML(true);
        $mail->Subject = "Appointment Confirmation - Dr Taylor GP";
        $mail->Body    = "<h2>Hello $name</h2><p>Your appointment request for <strong>$date at $time</strong> has been received.</p>";

        $mail->send();
        header("Location: thank-you.html");
        exit();
    } catch (Exception $e) {
        error_log("Mail Error: " . $mail->ErrorInfo);
        // Still redirect to thank you because DB record was created
        header("Location: thank-you.html"); 
    }
}
$stmt->close();
$conn->close();
?>