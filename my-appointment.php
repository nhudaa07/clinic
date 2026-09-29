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
// GET MY APPOINTMENTS
// ========================================

$stmt = $conn->prepare(
    "SELECT
        a.id,
        a.appointment_date,
        a.appointment_time,
        a.message,
        a.status,
        d.name AS doctor_name,
        d.specialization
     FROM appointments a
     INNER JOIN doctors d
        ON a.doctor_id = d.id
     WHERE a.patient_id = ?
     ORDER BY a.appointment_date DESC,
              a.appointment_time DESC"
);

$stmt->bind_param("i", $patient_id);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<?php include __DIR__ . '/includes/head.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/my_appointments.css">

<body>

<?php include __DIR__ . '/includes/header.php'; ?>


<!-- ========================================
     MY APPOINTMENTS
======================================== -->

<section class="my-appointments-page">

    <div class="container">


        <!-- HEADING -->

        <div class="my-appointments-heading">

            <div class="section-label">
                MY APPOINTMENTS
            </div>

            <h1>
                My <strong>Appointments</strong>
            </h1>

            <p>
                View all your booked appointments with MediBook.
            </p>

        </div>


        <!-- APPOINTMENT CARD -->

        <div class="my-appointments-card">

            <?php if ($result->num_rows > 0): ?>

                <div class="appointments-table-wrapper">

                    <table class="appointments-table">

                        <thead>

                            <tr>

                                <th>
                                    Doctor
                                </th>

                                <th>
                                    Specialization
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Time
                                </th>

                                <th>
                                    Message
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php while ($appointment = $result->fetch_assoc()): ?>

                                <tr>

                                    <!-- DOCTOR -->

                                    <td>

                                        <div class="doctor-name">
                                            <?= htmlspecialchars(
                                                $appointment["doctor_name"]
                                            ) ?>
                                        </div>

                                    </td>


                                    <!-- SPECIALIZATION -->

                                    <td>

                                        <div class="doctor-specialization">
                                            <?= htmlspecialchars(
                                                $appointment["specialization"]
                                            ) ?>
                                        </div>

                                    </td>


                                    <!-- DATE -->

                                    <td>

                                        <div class="appointment-date">

                                            <?= date(
                                                "d M Y",
                                                strtotime(
                                                    $appointment["appointment_date"]
                                                )
                                            ) ?>

                                        </div>

                                    </td>


                                    <!-- TIME -->

                                    <td>

                                        <div class="appointment-time">

                                            <?= date(
                                                "h:i A",
                                                strtotime(
                                                    $appointment["appointment_time"]
                                                )
                                            ) ?>

                                        </div>

                                    </td>


                                    <!-- MESSAGE -->

                                    <td>

                                        <?php if (!empty($appointment["message"])): ?>

                                            <?= htmlspecialchars(
                                                $appointment["message"]
                                            ) ?>

                                        <?php else: ?>

                                            —

                                        <?php endif; ?>

                                    </td>


                                    <!-- STATUS -->

                                    <td>

                                        <?php
                                        $status = strtolower(
                                            $appointment["status"]
                                        );
                                        ?>

                                        <span class="status <?= htmlspecialchars($status) ?>">

                                            <?= htmlspecialchars(
                                                $appointment["status"]
                                            ) ?>

                                        </span>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <!-- NO APPOINTMENTS -->

                <div class="no-appointments">

                    <i class="fa-regular fa-calendar-xmark"></i>

                    <h2>
                        No Appointments Found
                    </h2>

                    <p>
                        You haven't booked any appointments yet.
                    </p>

                    <a
                        href="<?= BASE_URL ?>appointment.php"
                        class="book-appointment-btn"
                    >
                        Book an Appointment
                    </a>

                </div>


            <?php endif; ?>

        </div>

    </div>

</section>


<?php include __DIR__ . '/includes/footer.php'; ?>


</body>
</html>

<?php

$stmt->close();
$conn->close();

?>