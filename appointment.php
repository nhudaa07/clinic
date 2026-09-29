<?php

session_start();
require_once __DIR__ . '/includes/config.php';

require_once __DIR__ . '/includes/db.php';


// ========================================
// CHECK LOGIN
// ========================================

if (!isset($_SESSION["patient_id"])) {

    header("Location: " . BASE_URL . "login.php");
    exit;

}

$patient_id = $_SESSION["patient_id"];


// ========================================
// GET PATIENT INFORMATION
// ========================================

$stmt = $conn->prepare(
    "SELECT id, name, email, phone
     FROM patients
     WHERE id = ?"
);

$stmt->bind_param("i", $patient_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    session_unset();
    session_destroy();

    header("Location: " . BASE_URL . "login.php");
    exit;

}

$patient = $result->fetch_assoc();

$stmt->close();


// ========================================
// GET DOCTORS
// ========================================

$doctors = [];

$doctor_result = $conn->query(
    "SELECT id, name, specialization
     FROM doctors
     ORDER BY name ASC"
);

if ($doctor_result) {

    while ($doctor = $doctor_result->fetch_assoc()) {

        $doctors[] = $doctor;

    }

}


// ========================================
// MESSAGE
// ========================================

$error = "";


// ========================================
// BOOK APPOINTMENT
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $doctor_id = intval($_POST["doctor"] ?? 0);
    $appointment_date = $_POST["date"] ?? "";
    $appointment_time = $_POST["time"] ?? "";
    $message = trim($_POST["message"] ?? "");


    // ========================================
    // BASIC VALIDATION
    // ========================================

    if (
        $doctor_id <= 0 ||
        empty($appointment_date) ||
        empty($appointment_time)
    ) {

        $error = "Please fill in all required fields.";

    } else {


        // ========================================
        // CHECK DOCTOR EXISTS
        // ========================================

        $doctor_stmt = $conn->prepare(
            "SELECT id
             FROM doctors
             WHERE id = ?"
        );

        $doctor_stmt->bind_param("i", $doctor_id);
        $doctor_stmt->execute();

        $doctor_result = $doctor_stmt->get_result();

        if ($doctor_result->num_rows !== 1) {

            $error = "Invalid doctor selected.";

        }

        $doctor_stmt->close();


        // ========================================
        // CHECK DATE IS NOT IN THE PAST
        // ========================================

        if (empty($error)) {

            $today = date("Y-m-d");

            if ($appointment_date < $today) {

                $error = "Please select a future date.";

            }

        }


        // ========================================
        // CHECK TIME SLOT
        // ========================================

        if (empty($error)) {

            $check_stmt = $conn->prepare(
                "SELECT id
                 FROM appointments
                 WHERE doctor_id = ?
                 AND appointment_date = ?
                 AND appointment_time = ?
                 AND status != 'Cancelled'"
            );

            $check_stmt->bind_param(
                "iss",
                $doctor_id,
                $appointment_date,
                $appointment_time
            );

            $check_stmt->execute();

            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {

                $error = "This time slot is already booked. Please choose another time.";

            }

            $check_stmt->close();

        }


        // ========================================
        // INSERT APPOINTMENT
        // ========================================

        if (empty($error)) {

            $insert_stmt = $conn->prepare(
                "INSERT INTO appointments
                (
                    patient_id,
                    doctor_id,
                    appointment_date,
                    appointment_time,
                    message,
                    status
                )
                VALUES (?, ?, ?, ?, ?, 'Pending')"
            );

            $insert_stmt->bind_param(
                "iisss",
                $patient_id,
                $doctor_id,
                $appointment_date,
                $appointment_time,
                $message
            );


            if ($insert_stmt->execute()) {

                // Redirect to success page
                header("Location: " . BASE_URL . "success.php");
                exit;

            } else {

                $error = "Something went wrong. Please try again.";

            }

            $insert_stmt->close();

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<?php include 'includes/head.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/appointment.css">

<body>

<?php include 'includes/header.php'; ?>


<!-- ================================
     APPOINTMENT PAGE
================================ -->

<section class="appointment-page">

    <div class="container">


        <!-- PAGE HEADING -->

        <div class="appointment-heading">

            <div class="section-label">
                BOOK APPOINTMENT
            </div>

            <h1 class="section-title">
                Schedule Your <strong>Appointment</strong>
            </h1>

            <p class="section-description">
                Choose your preferred doctor, date and time to book your
                appointment with MediBook.
            </p>

        </div>


        <div class="appointment-layout">


            <!-- ================================
                 LEFT SIDE
            ================================= -->

            <div class="appointment-info">

                <div class="appointment-info-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>


                <h2>
                    Your Health,<br>
                    <span>Our Priority</span>
                </h2>


                <p>
                    Book an appointment with a qualified doctor at a time
                    that works best for you.
                </p>


                <div class="appointment-features">


                    <div class="appointment-feature">

                        <i class="fa-solid fa-user-doctor"></i>

                        <div>

                            <strong>
                                Qualified Doctors
                            </strong>

                            <span>
                                Experienced healthcare professionals
                            </span>

                        </div>

                    </div>


                    <div class="appointment-feature">

                        <i class="fa-solid fa-clock"></i>

                        <div>

                            <strong>
                                Flexible Schedule
                            </strong>

                            <span>
                                Choose a convenient date and time
                            </span>

                        </div>

                    </div>


                    <div class="appointment-feature">

                        <i class="fa-solid fa-shield-heart"></i>

                        <div>

                            <strong>
                                Trusted Care
                            </strong>

                            <span>
                                Safe and reliable healthcare service
                            </span>

                        </div>

                    </div>


                </div>

            </div>


            <!-- ================================
                 RIGHT SIDE
            ================================= -->

            <div class="appointment-card">

                <h2>
                    Book an Appointment
                </h2>

                <p class="appointment-card-subtitle">
                    Fill in the information below
                </p>


                <!-- ERROR MESSAGE -->

                <?php if (!empty($error)): ?>

                    <div class="auth-message error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>

                <?php endif; ?>


                <form action="" method="POST">


                    <!-- NAME -->

                    <div class="input-group">

                        <label for="name">
                            Full Name
                        </label>

                        <div class="input-box">

                            <i class="fa-regular fa-user"></i>

                            <input
                                type="text"
                                id="name"
                                value="<?php echo htmlspecialchars($patient["name"]); ?>"
                                readonly
                            >

                        </div>

                    </div>


                    <!-- EMAIL -->

                    <div class="input-group">

                        <label for="email">
                            Email Address
                        </label>

                        <div class="input-box">

                            <i class="fa-regular fa-envelope"></i>

                            <input
                                type="email"
                                id="email"
                                value="<?php echo htmlspecialchars($patient["email"]); ?>"
                                readonly
                            >

                        </div>

                    </div>


                    <!-- PHONE -->

                    <div class="input-group">

                        <label for="phone">
                            Phone Number
                        </label>

                        <div class="input-box">

                            <i class="fa-solid fa-phone"></i>

                            <input
                                type="tel"
                                id="phone"
                                value="<?php echo htmlspecialchars($patient["phone"]); ?>"
                                readonly
                            >

                        </div>

                    </div>


                    <!-- DOCTOR + DATE -->

                    <div class="input-row">


                        <!-- DOCTOR -->

                        <div class="input-group">

                            <label for="doctor">
                                Select Doctor
                            </label>

                            <div class="input-box">

                                <i class="fa-solid fa-user-doctor"></i>

                                <select
                                    id="doctor"
                                    name="doctor"
                                    required
                                >

                                    <option value="">
                                        Choose a doctor
                                    </option>


                                    <?php foreach ($doctors as $doctor): ?>

                                        <option
                                            value="<?php echo $doctor["id"]; ?>"
                                            <?php
                                            if (
                                                isset($_POST["doctor"]) &&
                                                $_POST["doctor"] == $doctor["id"]
                                            ) {
                                                echo "selected";
                                            }
                                            ?>
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $doctor["name"]
                                            );
                                            ?>

                                            -

                                            <?php
                                            echo htmlspecialchars(
                                                $doctor["specialization"]
                                            );
                                            ?>

                                        </option>

                                    <?php endforeach; ?>


                                </select>

                            </div>

                        </div>


                        <!-- DATE -->

                        <div class="input-group">

                            <label for="date">
                                Appointment Date
                            </label>

                            <div class="input-box">

                                <i class="fa-regular fa-calendar"></i>

                                <input
                                    type="date"
                                    id="date"
                                    name="date"
                                    min="<?php echo date('Y-m-d'); ?>"
                                    value="<?php echo htmlspecialchars($_POST["date"] ?? ""); ?>"
                                    required
                                >

                            </div>

                        </div>

                    </div>


                    <!-- TIME -->

                    <div class="input-group">

                        <label for="time">
                            Preferred Time
                        </label>

                        <div class="input-box">

                            <i class="fa-regular fa-clock"></i>

                            <select
                                id="time"
                                name="time"
                                required
                            >

                                <option value="">
                                    Choose a time
                                </option>

                                <option value="09:00:00">
                                    09:00 AM
                                </option>

                                <option value="10:00:00">
                                    10:00 AM
                                </option>

                                <option value="11:00:00">
                                    11:00 AM
                                </option>

                                <option value="14:00:00">
                                    02:00 PM
                                </option>

                                <option value="15:00:00">
                                    03:00 PM
                                </option>

                                <option value="16:00:00">
                                    04:00 PM
                                </option>

                            </select>

                        </div>

                    </div>


                    <!-- MESSAGE -->

                    <div class="input-group">

                        <label for="message">
                            Additional Message
                        </label>

                        <div class="textarea-box">

                            <i class="fa-regular fa-message"></i>

                            <textarea
                                id="message"
                                name="message"
                                rows="4"
                                placeholder="Describe your problem or any additional information..."
                            ><?php echo htmlspecialchars($_POST["message"] ?? ""); ?></textarea>

                        </div>

                    </div>


                    <!-- SUBMIT -->

                    <button
                        type="submit"
                        class="appointment-submit-btn"
                    >

                        Book Appointment

                        <i class="fa-solid fa-arrow-right"></i>

                    </button>


                </form>

            </div>

        </div>

    </div>

</section>


<?php include 'includes/footer.php'; ?>

</body>
</html>
