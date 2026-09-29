<?php

// ========================================
// CONFIG + DATABASE
// ========================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

session_start();


// ========================================
// CHECK LOGIN
// ========================================

if (!isset($_SESSION["patient_id"])) {
    header("Location: " . BASE_URL . "login.php");
    exit;
}

$patient_id = $_SESSION["patient_id"];
$patient_name = $_SESSION["patient_name"] ?? "Patient";


// ========================================
// CANCEL APPOINTMENT
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cancel_appointment"])) {

    $appointment_id = (int) $_POST["appointment_id"];

    $cancel_stmt = $conn->prepare(
        "UPDATE appointments
         SET status = 'Cancelled'
         WHERE id = ?
         AND patient_id = ?
         AND status IN ('Pending', 'Confirmed')"
    );

    $cancel_stmt->bind_param("ii", $appointment_id, $patient_id);
    $cancel_stmt->execute();

    $cancel_stmt->close();

    header("Location: " . $_SERVER["PHP_SELF"]);
    exit;
}


// ========================================
// GET PATIENT INFORMATION
// ========================================

$patient_stmt = $conn->prepare(
    "SELECT id, name, email, phone
     FROM patients
     WHERE id = ?"
);

$patient_stmt->bind_param("i", $patient_id);
$patient_stmt->execute();

$patient_result = $patient_stmt->get_result();

if ($patient_result->num_rows !== 1) {

    session_unset();
    session_destroy();

    header("Location: " . BASE_URL . "login.php");
    exit;
}

$patient = $patient_result->fetch_assoc();

$patient_stmt->close();

$patient_name = $patient["name"];


// ========================================
// TOTAL APPOINTMENTS
// ========================================

$total_stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM appointments
     WHERE patient_id = ?"
);

$total_stmt->bind_param("i", $patient_id);
$total_stmt->execute();

$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();

$total_appointments = $total_row["total"];

$total_stmt->close();


// ========================================
// UPCOMING APPOINTMENTS
// ========================================

$upcoming_stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM appointments
     WHERE patient_id = ?
     AND status IN ('Pending', 'Confirmed')
     AND (
         appointment_date > CURDATE()
         OR (
             appointment_date = CURDATE()
             AND appointment_time >= CURTIME()
         )
     )"
);

$upcoming_stmt->bind_param("i", $patient_id);
$upcoming_stmt->execute();

$upcoming_result = $upcoming_stmt->get_result();
$upcoming_row = $upcoming_result->fetch_assoc();

$upcoming_appointments = $upcoming_row["total"];

$upcoming_stmt->close();


// ========================================
// COMPLETED APPOINTMENTS
// ========================================

$completed_stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM appointments
     WHERE patient_id = ?
     AND status = 'Completed'"
);

$completed_stmt->bind_param("i", $patient_id);
$completed_stmt->execute();

$completed_result = $completed_stmt->get_result();
$completed_row = $completed_result->fetch_assoc();

$completed_appointments = $completed_row["total"];

$completed_stmt->close();


// ========================================
// GET UPCOMING APPOINTMENTS
// ========================================

$appointments = [];

$appointment_stmt = $conn->prepare(
    "SELECT
        appointments.id,
        appointments.appointment_date,
        appointments.appointment_time,
        appointments.status,
        doctors.name AS doctor_name,
        doctors.specialization
     FROM appointments
     INNER JOIN doctors
        ON appointments.doctor_id = doctors.id
     WHERE appointments.patient_id = ?
     AND appointments.status IN ('Pending', 'Confirmed')
     AND (
         appointments.appointment_date > CURDATE()
         OR (
             appointments.appointment_date = CURDATE()
             AND appointments.appointment_time >= CURTIME()
         )
     )
     ORDER BY
        appointments.appointment_date ASC,
        appointments.appointment_time ASC
     LIMIT 5"
);

$appointment_stmt->bind_param("i", $patient_id);
$appointment_stmt->execute();

$appointment_result = $appointment_stmt->get_result();

while ($appointment = $appointment_result->fetch_assoc()) {
    $appointments[] = $appointment;
}

$appointment_stmt->close();

?>

<!DOCTYPE html>

<html lang="en">

<?php require_once __DIR__ . '/../includes/head.php'; ?>

<body>
<style>
    /* ========================================
   CANCEL APPOINTMENT BUTTON
======================================== */

.cancel-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;

    padding: 8px 14px;

    border: 1px solid #f1b5b5;
    border-radius: 7px;

    background: #fff5f5;
    color: #dc3545;

    font-size: 13px;
    font-weight: 600;

    cursor: pointer;

    transition: all 0.2s ease;
}


/* HOVER */

.cancel-btn:hover {
    background: #dc3545;
    border-color: #dc3545;
    color: #ffffff;

    transform: translateY(-1px);
}


/* CLICK */

.cancel-btn:active {
    transform: translateY(0);
}


/* ICON */

.cancel-btn i {
    font-size: 12px;
}
</style>

<!-- ========================================
     TOP NAVBAR
======================================== -->

<header class="patient-navbar">

    <!-- LOGO -->

    <a href="<?= BASE_URL ?>index.php" class="logo">

        <div class="logo-icon">
            <i class="fa-solid fa-plus"></i>
        </div>

        MediBook

    </a>


    <!-- MENU -->

    <nav class="patient-menu">

        <a
            href="<?= BASE_URL ?>patient/patient_dashboard.php"
            class="active"
        >

            <i class="fa-solid fa-house"></i>

            Dashboard

        </a>


        <a href="<?= BASE_URL ?>my-appointment.php">

            <i class="fa-solid fa-calendar-check"></i>

            Appointments

        </a>

    </nav>


    <!-- PROFILE MENU -->

    <div class="patient-profile">

        <button class="profile-button">

            <div class="user-avatar">
                <i class="fa-solid fa-user"></i>
            </div>

            <div class="profile-info">

                <strong>
                    <?php
                    echo htmlspecialchars($patient_name);
                    ?>
                </strong>

                <span>
                    Patient
                </span>

            </div>

            <i class="fa-solid fa-chevron-down profile-arrow"></i>

        </button>


        <div class="profile-dropdown">

            <a href="#">

                <i class="fa-regular fa-user"></i>

                My Profile

            </a>


            <a href="#">

                <i class="fa-solid fa-gear"></i>

                Settings

            </a>


            <div class="dropdown-divider"></div>


            <a
                href="<?= BASE_URL ?>logout.php"
                class="logout"
            >

                <i class="fa-solid fa-arrow-right-from-bracket"></i>

                Logout

            </a>

        </div>

    </div>

</header>


<!-- ========================================
     MAIN CONTENT
======================================== -->

<main class="patient-main">


    <!-- PAGE HEADER -->

    <section class="patient-header">

        <div>

            <h1>
                Patient Dashboard
            </h1>

            <p>

                Welcome back,
                <?php echo htmlspecialchars($patient_name); ?>!
                Manage your appointments easily.

            </p>

        </div>


        <br>


        <a
            href="<?= BASE_URL ?>appointment.php"
            class="dashboard-btn"
        >

            <i class="fa-solid fa-plus"></i>

            Book Appointment

        </a>

    </section>


    <br>


    <!-- ========================================
         STATS
    ======================================== -->

    <section class="dashboard-stats">


        <!-- TOTAL -->

        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-solid fa-calendar-check"></i>

            </div>


            <div>

                <span>
                    Total Appointments
                </span>

                <h2>
                    <?php echo $total_appointments; ?>
                </h2>

            </div>

        </div>


        <!-- UPCOMING -->

        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-regular fa-clock"></i>

            </div>


            <div>

                <span>
                    Upcoming
                </span>

                <h2>
                    <?php echo $upcoming_appointments; ?>
                </h2>

            </div>

        </div>


        <!-- COMPLETED -->

        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-solid fa-check"></i>

            </div>


            <div>

                <span>
                    Completed
                </span>

                <h2>
                    <?php echo $completed_appointments; ?>
                </h2>

            </div>

        </div>


    </section>


    <!-- ========================================
         BOOK APPOINTMENT
    ======================================== -->

    <section class="dashboard-section">


        <div class="section-heading">

            <div>

                <h2>
                    Book an Appointment
                </h2>

                <p>
                    Find a doctor and schedule your visit.
                </p>

            </div>

        </div>


        <div class="booking-card">

            <div class="booking-icon">

                <i class="fa-solid fa-user-doctor"></i>

            </div>


            <div class="booking-content">

                <h3>
                    Need to see a doctor?
                </h3>

                <p>

                    Browse available doctors and choose a convenient
                    date and time for your appointment.

                </p>

            </div>


            <a
                href="<?= BASE_URL ?>appointment/appointment.php"
                class="dashboard-btn"
            >

                Find a Doctor

                <i class="fa-solid fa-arrow-right"></i>

            </a>

        </div>

    </section>


    <!-- ========================================
         UPCOMING APPOINTMENTS
    ======================================== -->

    <section class="dashboard-section">


        <div class="section-heading">

            <div>

                <h2>
                    Upcoming Appointments
                </h2>

                <p>
                    Your scheduled appointments
                </p>

            </div>


            <a
                href="<?= BASE_URL ?>appointment/appointment.php"
                class="view-all"
            >

                Book New

            </a>


        </div>


        <div class="appointment-table-wrapper">


            <table class="dashboard-table">


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
                            Status
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php if (count($appointments) > 0): ?>


                    <?php foreach ($appointments as $appointment): ?>


                        <tr>


                            <!-- DOCTOR -->

                            <td>

                                <div class="doctor-info">

                                    <div class="doctor-avatar">

                                        <i class="fa-solid fa-user-doctor"></i>

                                    </div>


                                    <strong>

                                        <?php

                                        echo htmlspecialchars(
                                            $appointment["doctor_name"]
                                        );

                                        ?>

                                    </strong>


                                </div>

                            </td>


                            <!-- SPECIALIZATION -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $appointment["specialization"]
                                );

                                ?>

                            </td>


                            <!-- DATE -->

                            <td>

                                <?php

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $appointment["appointment_date"]
                                    )
                                );

                                ?>

                            </td>


                            <!-- TIME -->

                            <td>

                                <?php

                                echo date(
                                    "h:i A",
                                    strtotime(
                                        $appointment["appointment_time"]
                                    )
                                );

                                ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <?php

                                $status_class = strtolower(
                                    $appointment["status"]
                                );

                                ?>

                                <span
                                    class="status <?php echo htmlspecialchars($status_class); ?>"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $appointment["status"]
                                    );

                                    ?>

                                </span>

                            </td>


                            <!-- CANCEL -->

                            <td>

                                <?php if (
                                    $appointment["status"] === "Pending" ||
                                    $appointment["status"] === "Confirmed"
                                ): ?>

                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to cancel this appointment?');"
                                        style="margin:0;"
                                    >

                                        <input
                                            type="hidden"
                                            name="appointment_id"
                                            value="<?php echo $appointment["id"]; ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="cancel_appointment"
                                            class="cancel-btn"
                                        >

                                            <i class="fa-solid fa-xmark"></i>

                                            Cancel

                                        </button>

                                    </form>

                                <?php else: ?>

                                    <span>-</span>

                                <?php endif; ?>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="6"
                            style="text-align:center; padding:30px;"
                        >

                            No upcoming appointments.

                            <br><br>


                            <a
                                href="<?= BASE_URL ?>appointment.php"
                                class="dashboard-btn"
                            >

                                Book Your First Appointment

                                <i class="fa-solid fa-arrow-right"></i>

                            </a>

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>


            </table>


        </div>

    </section>


</main>


<!-- ========================================
     DROPDOWN SCRIPT
======================================== -->

<script>

    const profileButton =
        document.querySelector(".profile-button");

    const profileDropdown =
        document.querySelector(".profile-dropdown");


    profileButton.addEventListener("click", function () {

        profileDropdown.classList.toggle("show");

    });


    document.addEventListener("click", function (event) {

        if (!event.target.closest(".patient-profile")) {

            profileDropdown.classList.remove("show");

        }

    });

</script>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>


</body>

</html>