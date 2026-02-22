<?php
session_start();

$conn = new mysqli("localhost", "root", "", "gp_web_db");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$booking_id = $_GET['booking_id'] ?? null;
$fullname = "";

/* ==============================
   FETCH details FROM appointments
============================== */

$booking_id = $_GET['booking_id'] ?? null;

$fullname = "";
$phone = "";
$email = "";

if ($booking_id) {

    $stmt = $conn->prepare("
        SELECT fullname, phone, email 
        FROM appointments 
        WHERE id = ?
    ");

    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $fullname = $row['fullname'];
        $phone    = $row['phone'];
        $email    = $row['email'];
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patient Portal | Dr Taylor GP</title>
<link rel="stylesheet" href="assets/js/css/styles.css">

<style>
.progress-bar {
  width: 100%;
  height: 8px;
  background: #eee;
  border-radius: 20px;
  margin-bottom: 20px;
}
.progress-fill {
  height: 8px;
  width: 33%;
  background: #2c7be5;
  border-radius: 20px;
  transition: 0.4s;
}
.step { display: none; }
.step.active { display: block; }
</style>
</head>
<body>

<nav class="navbar">
  <a href="index.html" class="logo">Dr. Taylor GP</a>
  <ul class="nav-links">
    <li><a href="index.html">Home</a></li>
    <li><a href="about.html">About</a></li>
    <li><a href="booking.html">Book Appointment</a></li>
    <li><a href="patient-portal.php">Patient Portal</a></li>
    <li><a href="contact.html">Contact</a></li>
  </ul>
  <div class="hamburger" id="hamburger">
    <span></span><span></span><span></span>
  </div>
</nav>

<div class="mobile-menu" id="mobileMenu">
  <div class="close-btn" id="closeMenu">&times;</div>
  <ul>
    <li><a href="index.html">Home</a></li>
    <li><a href="about.html">About</a></li>
    <li><a href="booking.html">Book Appointment</a></li>
    <li><a href="patient-portal.php">Patient Portal</a></li>
    <li><a href="contact.html">Contact</a></li>
  </ul>
</div>

<section class="section">
<h2>Patient Portal</h2>

<div class="progress-bar">
  <div class="progress-fill" id="progressFill"></div>
</div>

<form action="process-portal.php" method="POST" id="multiForm">

<input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking_id) ?>">

<!-- STEP 1 -->
<div class="step active">
<h3>Step 1 of 3 — Personal Information</h3>

<input type="text" name="fullname" required placeholder="Full Name" 
       value="<?= htmlspecialchars($fullname) ?>" required>

<input type="text" name="idnumber" placeholder="ID Number" required pattern="[0-9]{13}" title="Enter a 13-digit ID number"  >
<input type="date" name="dob" required  title="Enter your date of birth">

<input type="tel" name="phone" required
pattern="[0-9]{10}"
value="<?= htmlspecialchars($phone) ?>">

<input type="email" name="email" required
value="<?= htmlspecialchars($email) ?>">
<input type="text" name="address"placeholder="Address" required>

<button type="button" data-next>Next</button>
</div>

<!-- STEP 2 -->
<div class="step">
<h3>Step 2 of 3 — Medical Information</h3>

<textarea name="allergies" placeholder="Allergies" required></textarea>
<textarea name="medication" placeholder="Current Medication" required></textarea>
<textarea name="conditions" placeholder="Chronic Conditions" required></textarea>

<button type="button" data-prev>Back</button>
<button type="button" data-next>Next</button>
</div>

<!-- STEP 3 -->
<div class="step">
<h3>Step 3 of 3 — Consent & Insurance</h3>

<label>
<input type="checkbox" name="consent" value="1" required>
I consent to processing of my medical information.
</label>

<input type="text" name="medicalaid" placeholder="Medical Aid">
<input type="text" name="membership" placeholder="Membership Number" required title="Enter membership number"  >

<select name="privatepay" required>
<option value="">Private Pay?</option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select>

<button type="button" data-prev>Back</button>
<button type="submit">Submit</button>
</div>

</form>
</section>

<script src="assets/js/main.js"></script>
</body>
</html>