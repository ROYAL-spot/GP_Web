<?php
session_start();
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;
use TCPDF; 

// 1. LOAD ENVIRONMENT VARIABLES
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
$conn = new mysqli($_ENV['DB_SERVER'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_NAME']);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// 3. CAPTURE PORTAL DATA
$booking_id = $_POST['booking_id'] ?? null;
if (!$booking_id) {
    die("Missing booking ID.");
}

$fullname   = $_POST['fullname'] ?? '';
$idnumber   = $_POST['idnumber'] ?? '';
$dob        = $_POST['dob'] ?? '';
$phone      = $_POST['phone'] ?? '';
$email      = $_POST['email'] ?? '';
$address    = $_POST['address'] ?? '';
$allergies  = $_POST['allergies'] ?? '';
$medication = $_POST['medication'] ?? '';
$conditions = $_POST['conditions'] ?? '';
$consent    = isset($_POST['consent']) ? "Yes" : "No";
$medicalaid = $_POST['medicalaid'] ?? '';
$membership = $_POST['membership'] ?? '';
$privatepay = $_POST['privatepay'] ?? '';

if (empty($dob)) $dob = null;

// 4. INSERT INTO PATIENT_PORTAL TABLE
$stmt = $conn->prepare("
    INSERT INTO patient_portal 
    (booking_id, fullname, idnumber, dob, phone, email, address,
    allergies, medication, conditions, consent_given,
    medicalaid, membership, privatepay)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "issssssssissss",
    $booking_id, $fullname, $idnumber, $dob, $phone, $email, $address,
    $allergies, $medication, $conditions, $consent,
    $medicalaid, $membership, $privatepay
);

$stmt->execute();
$stmt->close();

// 5. FETCH ORIGINAL APPOINTMENT DETAILS FOR THE EMAIL body
$stmt2 = $conn->prepare("SELECT appointment_date, appointment_time, reason FROM appointments WHERE id=?");
$stmt2->bind_param("i", $booking_id);
$stmt2->execute();
$result = $stmt2->get_result();
$appointment = $result->fetch_assoc();
$stmt2->close();
$conn->close();

// 6. PDF GENERATION (TCPDF)
$pdf = new TCPDF();
$pdf->SetCreator('Dr Taylor GP');
$pdf->SetTitle('Patient Medical Form');
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 11);

$html = "
    <h2 style='color: #2c7be5;'>New Patient Medical Form</h2>
    <p><strong>Name:</strong> $fullname</p>
    <p><strong>ID:</strong> $idnumber</p>
    <p><strong>DOB:</strong> $dob</p>
    <p><strong>Phone:</strong> $phone</p>
    <p><strong>Email:</strong> $email</p>
    <p><strong>Address:</strong> $address</p>
    <hr>
    <h3>Medical History</h3>
    <p><strong>Allergies:</strong> $allergies</p>
    <p><strong>Current Medication:</strong> $medication</p>
    <p><strong>Chronic Conditions:</strong> $conditions</p>
    <hr>
    <h3>Insurance & Consent</h3>
    <p><strong>Medical Aid:</strong> $medicalaid</p>
    <p><strong>Membership Number:</strong> $membership</p>
    <p><strong>Private Pay:</strong> $privatepay</p>
    <p><strong>Consent Given:</strong> $consent</p>
";

$pdf->writeHTML($html);
$pdf_file = __DIR__ . "/patient_$booking_id.pdf";
$pdf->Output($pdf_file, 'F'); // Save to server temporarily

// 7. EMAIL WITH PDF ATTACHMENT
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

    
    $mail->setFrom($config['smtp_user'], 'Dr Taylor GP Portal');
    $mail->addAddress($config['admin_email']); 

    $mail->isHTML(true);
    $mail->Subject = "New Patient Registration: $fullname";

    $mail->Body = "
        <h2>Detailed Patient Registration</h2>
        <p>A new patient has completed the portal registration.</p>
        <p><strong>Appointment Date:</strong> {$appointment['appointment_date']}</p>
        <p><strong>Requested Time:</strong> {$appointment['appointment_time']}</p>
        <hr>
        <p>Please find the full medical history and consent form attached as a PDF.</p>
    ";

    $mail->addAttachment($pdf_file);
    $mail->send();

    // Optional: Delete the PDF from the server after sending
    unlink($pdf_file);

} catch (Exception $e) {
    error_log("Portal Mailer Error: {$mail->ErrorInfo}");
}

header("Location: thank-you.html");
exit();
?>