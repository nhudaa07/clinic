<?php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================================================
   LOGIN CHECK
========================================================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "login.php");
    exit;
}

/* =========================================================
   ADMIN ROLE CHECK
========================================================= */

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

require_once __DIR__ . '/../includes/head.php';


$message = "";
$message_type = "";


/* =========================================================
   REDIRECT WITH MESSAGE
========================================================= */

function redirectMessage($message, $type = "success")
{
    header(
        "Location: " . $_SERVER["PHP_SELF"] .
        "?message=" . urlencode($message) .
        "&type=" . urlencode($type)
    );
    exit;
}


if (isset($_GET["message"])) {
    $message = $_GET["message"];
    $message_type = $_GET["type"] ?? "success";
}


/* =========================================================
   HANDLE POST ACTIONS
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";


    /* =====================================================
       ADD DOCTOR
    ===================================================== */

    if ($action === "add_doctor") {

        $name = trim($_POST["name"] ?? "");
        $specialization = trim($_POST["specialization"] ?? "");
        $email = trim($_POST["email"] ?? "");

        if ($name === "" || $specialization === "" || $email === "") {
            redirectMessage(
                "Please fill all doctor information.",
                "error"
            );
        }

        $stmt = $conn->prepare(
            "INSERT INTO doctors (name, specialization, email)
             VALUES (?, ?, ?)"
        );

        $stmt->bind_param(
            "sss",
            $name,
            $specialization,
            $email
        );

        if ($stmt->execute()) {
            $stmt->close();
            redirectMessage("Doctor added successfully.");
        } else {
            $stmt->close();
            redirectMessage("Failed to add doctor.", "error");
        }
    }


    /* =====================================================
       UPDATE DOCTOR
    ===================================================== */

    if ($action === "update_doctor") {

        $doctor_id = intval($_POST["doctor_id"] ?? 0);
        $name = trim($_POST["name"] ?? "");
        $specialization = trim($_POST["specialization"] ?? "");
        $email = trim($_POST["email"] ?? "");

        if (
            $doctor_id <= 0 ||
            $name === "" ||
            $specialization === "" ||
            $email === ""
        ) {
            redirectMessage(
                "Please fill all doctor information.",
                "error"
            );
        }

        $stmt = $conn->prepare(
            "UPDATE doctors
             SET name = ?, specialization = ?, email = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "sssi",
            $name,
            $specialization,
            $email,
            $doctor_id
        );

        if ($stmt->execute()) {
            $stmt->close();
            redirectMessage(
                "Doctor information updated successfully."
            );
        } else {
            $stmt->close();
            redirectMessage(
                "Failed to update doctor.",
                "error"
            );
        }
    }


    /* =====================================================
       DELETE DOCTOR
    ===================================================== */

    if ($action === "delete_doctor") {

        $doctor_id = intval($_POST["doctor_id"] ?? 0);

        if ($doctor_id <= 0) {
            redirectMessage("Invalid doctor.", "error");
        }

        $check = $conn->prepare(
            "SELECT COUNT(*) AS total
             FROM appointments
             WHERE doctor_id = ?"
        );

        $check->bind_param("i", $doctor_id);
        $check->execute();

        $result = $check->get_result();
        $row = $result->fetch_assoc();

        $check->close();

        if ($row["total"] > 0) {
            redirectMessage(
                "This doctor has appointments and cannot be deleted.",
                "error"
            );
        }

        $stmt = $conn->prepare(
            "DELETE FROM doctors WHERE id = ?"
        );

        $stmt->bind_param("i", $doctor_id);

        if ($stmt->execute()) {
            $stmt->close();
            redirectMessage("Doctor deleted successfully.");
        } else {
            $stmt->close();
            redirectMessage(
                "Failed to delete doctor.",
                "error"
            );
        }
    }


    /* =====================================================
       CONFIRM APPOINTMENT
    ===================================================== */

    if ($action === "confirm_appointment") {

        $appointment_id =
            intval($_POST["appointment_id"] ?? 0);

        $stmt = $conn->prepare(
            "UPDATE appointments
             SET status = 'Confirmed'
             WHERE id = ?"
        );

        $stmt->bind_param("i", $appointment_id);

        if ($stmt->execute()) {
            $stmt->close();
            redirectMessage("Appointment confirmed.");
        } else {
            $stmt->close();
            redirectMessage(
                "Failed to confirm appointment.",
                "error"
            );
        }
    }


    /* =====================================================
       CANCEL APPOINTMENT
    ===================================================== */

    if ($action === "cancel_appointment") {

        $appointment_id =
            intval($_POST["appointment_id"] ?? 0);

        $stmt = $conn->prepare(
            "UPDATE appointments
             SET status = 'Cancelled'
             WHERE id = ?"
        );

        $stmt->bind_param("i", $appointment_id);

        if ($stmt->execute()) {
            $stmt->close();
            redirectMessage("Appointment cancelled.");
        } else {
            $stmt->close();
            redirectMessage(
                "Failed to cancel appointment.",
                "error"
            );
        }
    }
}


/* =========================================================
   GET DOCTORS
========================================================= */

$doctors = [];

$result = $conn->query(
    "SELECT id, name, specialization, email
     FROM doctors
     ORDER BY id DESC"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
}


/* =========================================================
   GET APPOINTMENTS
========================================================= */

$appointments = [];

$result = $conn->query(
    "SELECT
        appointments.id,
        appointments.appointment_date,
        appointments.appointment_time,
        appointments.status,
        patients.name AS patient_name,
        doctors.name AS doctor_name

     FROM appointments

     INNER JOIN patients
        ON appointments.patient_id = patients.id

     INNER JOIN doctors
        ON appointments.doctor_id = doctors.id

     ORDER BY
        appointments.appointment_date DESC,
        appointments.appointment_time DESC"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}


/* =========================================================
   STATISTICS
========================================================= */

$total_doctors = count($doctors);
$total_appointments = count($appointments);

$pending_appointments = 0;
$confirmed_appointments = 0;

foreach ($appointments as $appointment) {

    if ($appointment["status"] === "Pending") {
        $pending_appointments++;
    }

    if ($appointment["status"] === "Confirmed") {
        $confirmed_appointments++;
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<body>

<style>

/* =========================================================
   BASIC
========================================================= */

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: #f7f9fb;
    color: #222;
    font-family: Arial, Helvetica, sans-serif;
}

.admin-container {
    width: 92%;
    max-width: 1200px;
    margin: 0 auto;
}


/* =========================================================
   HEADER
========================================================= */

.admin-header {
    background: #ffffff;
    border-bottom: 1px solid #e5e5e5;
}

.admin-header-inner {
    max-width: 1200px;
    width: 92%;
    margin: auto;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.logo {
    font-size: 22px;
    font-weight: 700;
    color: #1976d2;
}

.logout-btn {
    text-decoration: none;
    background: #dc3545;
    color: #ffffff;
    padding: 9px 16px;
    border-radius: 5px;
    font-size: 14px;
}

.logout-btn:hover {
    background: #c82333;
}


/* =========================================================
   PAGE TITLE
========================================================= */

.page-title {
    padding: 30px 0 20px;
}

.page-title h1 {
    margin: 0 0 7px;
    font-size: 28px;
}

.page-title p {
    margin: 0;
    color: #777;
}


/* =========================================================
   MESSAGE
========================================================= */

.message {
    padding: 12px 15px;
    margin-bottom: 20px;
    border-radius: 5px;
    font-size: 14px;
}

.message.success {
    background: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
}

.message.error {
    background: #ffebee;
    color: #c62828;
    border: 1px solid #ffcdd2;
}


/* =========================================================
   STATISTICS
========================================================= */

.stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 30px;
}

.stat-box {
    background: #ffffff;
    border: 1px solid #e5e5e5;
    border-radius: 6px;
    padding: 18px;
}

.stat-box h3 {
    margin: 0 0 8px;
    font-size: 14px;
    font-weight: 500;
    color: #777;
}

.stat-number {
    font-size: 25px;
    font-weight: 700;
}


/* =========================================================
   SECTION
========================================================= */

.section {
    background: #ffffff;
    border: 1px solid #e5e5e5;
    border-radius: 6px;
    margin-bottom: 30px;
    overflow: hidden;
}

.section-header {
    padding: 18px 20px;
    border-bottom: 1px solid #e5e5e5;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.section-header h2 {
    margin: 0;
    font-size: 19px;
}


/* =========================================================
   BUTTONS
========================================================= */

.btn {
    border: none;
    border-radius: 4px;
    padding: 8px 13px;
    font-size: 13px;
    cursor: pointer;
}

.btn-primary {
    background: #1976d2;
    color: #ffffff;
}

.btn-primary:hover {
    background: #1565c0;
}

.btn-edit {
    background: #f0f0f0;
    color: #333;
}

.btn-edit:hover {
    background: #e2e2e2;
}

.btn-delete {
    background: #dc3545;
    color: #ffffff;
}

.btn-delete:hover {
    background: #c82333;
}

.btn-confirm {
    background: #28a745;
    color: #ffffff;
}

.btn-confirm:hover {
    background: #218838;
}

.btn-cancel {
    background: #dc3545;
    color: #ffffff;
}

.btn-cancel:hover {
    background: #c82333;
}


/* =========================================================
   TABLE
========================================================= */

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 13px 15px;
    text-align: left;
    border-bottom: 1px solid #eeeeee;
    font-size: 14px;
}

th {
    background: #fafafa;
    font-weight: 600;
    color: #555;
}

tr:last-child td {
    border-bottom: none;
}


/* =========================================================
   STATUS
========================================================= */

.status {
    display: inline-block;
    padding: 5px 9px;
    border-radius: 4px;
    font-size: 12px;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-confirmed {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.status-completed {
    background: #d1ecf1;
    color: #0c5460;
}


/* =========================================================
   ACTIONS
========================================================= */

.actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.inline-form {
    display: inline;
}


/* =========================================================
   EMPTY
========================================================= */

.empty {
    text-align: center;
    padding: 30px;
    color: #888;
}


/* =========================================================
   MODAL
========================================================= */

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.45);
    align-items: center;
    justify-content: center;
    z-index: 999;
}

.modal.active {
    display: flex;
}

.modal-box {
    background: #ffffff;
    width: 90%;
    max-width: 450px;
    border-radius: 7px;
    padding: 22px;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
}

.close-btn {
    border: none;
    background: none;
    font-size: 25px;
    cursor: pointer;
    color: #777;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-size: 14px;
    font-weight: 600;
}

.form-group input {
    width: 100%;
    padding: 10px 11px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
}

.form-group input:focus {
    outline: none;
    border-color: #1976d2;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 20px;
}

.btn-secondary {
    background: #eeeeee;
    color: #333;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

    .stats {
        grid-template-columns: 1fr;
    }

    .admin-header-inner {
        height: 60px;
    }

    .page-title h1 {
        font-size: 23px;
    }

    th,
    td {
        white-space: nowrap;
    }
}

</style>


<!-- =====================================================
     HEADER
===================================================== -->

<header class="admin-header">

    <div class="admin-header-inner">

        <div class="logo">
            MediBook
        </div>

        <a
            href="<?= BASE_URL ?>logout.php"
            class="logout-btn"
        >
            Logout
        </a>

    </div>

</header>


<!-- =====================================================
     MAIN
===================================================== -->

<main class="admin-container">


    <div class="page-title">

        <h1>Admin Dashboard</h1>

        <p>
            Manage doctors and appointments.
        </p>

    </div>


    <!-- MESSAGE -->

    <?php if ($message !== ""): ?>

        <div class="message <?= htmlspecialchars($message_type) ?>">

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>


    <!-- =================================================
         STATISTICS
    ================================================= -->

    <div class="stats">

        <div class="stat-box">

            <h3>Total Doctors</h3>

            <div class="stat-number">
                <?= $total_doctors ?>
            </div>

        </div>


        <div class="stat-box">

            <h3>Total Appointments</h3>

            <div class="stat-number">
                <?= $total_appointments ?>
            </div>

        </div>


        <div class="stat-box">

            <h3>Pending Appointments</h3>

            <div class="stat-number">
                <?= $pending_appointments ?>
            </div>

        </div>

    </div>


    <!-- =================================================
         DOCTORS
    ================================================= -->

    <section class="section">

        <div class="section-header">

            <h2>Doctors</h2>

            <button
                type="button"
                class="btn btn-primary"
                onclick="openAddDoctorModal()"
            >
                + Add Doctor
            </button>

        </div>


        <?php if (count($doctors) > 0): ?>

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>Doctor</th>
                            <th>Specialization</th>
                            <th>Email</th>
                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($doctors as $doctor): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($doctor["name"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($doctor["specialization"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($doctor["email"]) ?>
                            </td>

                            <td>

                                <div class="actions">

                                    <!-- EDIT -->

                                    <button
                                        type="button"
                                        class="btn btn-edit"
                                        onclick='openEditDoctorModal(
                                            <?= json_encode($doctor["id"]) ?>,
                                            <?= json_encode($doctor["name"]) ?>,
                                            <?= json_encode($doctor["specialization"]) ?>,
                                            <?= json_encode($doctor["email"]) ?>
                                        )'
                                    >
                                        Edit
                                    </button>


                                    <!-- DELETE -->

                                    <form
                                        method="POST"
                                        class="inline-form"
                                        onsubmit="return confirm('Are you sure you want to delete this doctor?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="delete_doctor"
                                        >

                                        <input
                                            type="hidden"
                                            name="doctor_id"
                                            value="<?= $doctor["id"] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-delete"
                                        >
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="empty">
                No doctors found.
            </div>

        <?php endif; ?>

    </section>


    <!-- =================================================
         APPOINTMENTS
    ================================================= -->

    <section class="section">

        <div class="section-header">

            <h2>Appointments</h2>

        </div>


        <?php if (count($appointments) > 0): ?>

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($appointments as $appointment): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($appointment["patient_name"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($appointment["doctor_name"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($appointment["appointment_date"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($appointment["appointment_time"]) ?>
                            </td>

                            <td>

                                <?php

                                $status = $appointment["status"];

                                $status_class = "status-pending";

                                if ($status === "Confirmed") {
                                    $status_class = "status-confirmed";
                                }

                                if ($status === "Cancelled") {
                                    $status_class = "status-cancelled";
                                }

                                if ($status === "Completed") {
                                    $status_class = "status-completed";
                                }

                                ?>

                                <span class="status <?= $status_class ?>">
                                    <?= htmlspecialchars($status) ?>
                                </span>

                            </td>


                            <td>

                                <div class="actions">

                                    <?php if ($status === "Pending"): ?>

                                        <!-- CONFIRM -->

                                        <form
                                            method="POST"
                                            class="inline-form"
                                        >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="confirm_appointment"
                                            >

                                            <input
                                                type="hidden"
                                                name="appointment_id"
                                                value="<?= $appointment["id"] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-confirm"
                                            >
                                                Confirm
                                            </button>

                                        </form>


                                        <!-- CANCEL -->

                                        <form
                                            method="POST"
                                            class="inline-form"
                                            onsubmit="return confirm('Cancel this appointment?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="cancel_appointment"
                                            >

                                            <input
                                                type="hidden"
                                                name="appointment_id"
                                                value="<?= $appointment["id"] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-cancel"
                                            >
                                                Cancel
                                            </button>

                                        </form>


                                    <?php elseif ($status === "Confirmed"): ?>


                                        <!-- CANCEL CONFIRMED -->

                                        <form
                                            method="POST"
                                            class="inline-form"
                                            onsubmit="return confirm('Cancel this appointment?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="cancel_appointment"
                                            >

                                            <input
                                                type="hidden"
                                                name="appointment_id"
                                                value="<?= $appointment["id"] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-cancel"
                                            >
                                                Cancel
                                            </button>

                                        </form>


                                    <?php else: ?>

                                        <span
                                            style="color:#888;font-size:13px;"
                                        >
                                            No action
                                        </span>

                                    <?php endif; ?>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="empty">
                No appointments found.
            </div>

        <?php endif; ?>

    </section>

</main>



<!-- =====================================================
     ADD DOCTOR MODAL
===================================================== -->

<div
    class="modal"
    id="addDoctorModal"
    onclick="closeModalOutside(event, 'addDoctorModal')"
>

    <div
        class="modal-box"
        onclick="event.stopPropagation()"
    >

        <div class="modal-header">

            <h2>Add Doctor</h2>

            <button
                type="button"
                class="close-btn"
                onclick="closeModal('addDoctorModal')"
            >
                &times;
            </button>

        </div>


        <form method="POST">

            <input
                type="hidden"
                name="action"
                value="add_doctor"
            >


            <div class="form-group">

                <label>
                    Doctor Name
                </label>

                <input
                    type="text"
                    name="name"
                    placeholder="Enter doctor name"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Specialization
                </label>

                <input
                    type="text"
                    name="specialization"
                    placeholder="e.g. Cardiology"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    placeholder="doctor@example.com"
                    required
                >

            </div>


            <div class="modal-actions">

                <button
                    type="button"
                    class="btn btn-secondary"
                    onclick="closeModal('addDoctorModal')"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Add Doctor
                </button>

            </div>

        </form>

    </div>

</div>



<!-- =====================================================
     EDIT DOCTOR MODAL
===================================================== -->

<div
    class="modal"
    id="editDoctorModal"
    onclick="closeModalOutside(event, 'editDoctorModal')"
>

    <div
        class="modal-box"
        onclick="event.stopPropagation()"
    >

        <div class="modal-header">

            <h2>Edit Doctor</h2>

            <button
                type="button"
                class="close-btn"
                onclick="closeModal('editDoctorModal')"
            >
                &times;
            </button>

        </div>


        <form method="POST">

            <input
                type="hidden"
                name="action"
                value="update_doctor"
            >

            <input
                type="hidden"
                name="doctor_id"
                id="editDoctorId"
            >


            <div class="form-group">

                <label>
                    Doctor Name
                </label>

                <input
                    type="text"
                    name="name"
                    id="editDoctorName"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Specialization
                </label>

                <input
                    type="text"
                    name="specialization"
                    id="editDoctorSpecialization"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    id="editDoctorEmail"
                    required
                >

            </div>


            <div class="modal-actions">

                <button
                    type="button"
                    class="btn btn-secondary"
                    onclick="closeModal('editDoctorModal')"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Update
                </button>

            </div>

        </form>

    </div>

</div>



<script>

/* =========================================================
   ADD DOCTOR MODAL
========================================================= */

function openAddDoctorModal() {

    document
        .getElementById("addDoctorModal")
        .classList.add("active");
}


/* =========================================================
   EDIT DOCTOR MODAL
========================================================= */

function openEditDoctorModal(
    id,
    name,
    specialization,
    email
) {

    document.getElementById("editDoctorId").value = id;

    document.getElementById("editDoctorName").value = name;

    document.getElementById("editDoctorSpecialization").value =
        specialization;

    document.getElementById("editDoctorEmail").value = email;

    document
        .getElementById("editDoctorModal")
        .classList.add("active");
}


/* =========================================================
   CLOSE MODAL
========================================================= */

function closeModal(id) {

    document
        .getElementById(id)
        .classList.remove("active");
}


/* =========================================================
   CLOSE OUTSIDE
========================================================= */

function closeModalOutside(event, id) {

    if (event.target.id === id) {
        closeModal(id);
    }
}


/* =========================================================
   ESC KEY
========================================================= */

document.addEventListener("keydown", function(event) {

    if (event.key === "Escape") {

        closeModal("addDoctorModal");

        closeModal("editDoctorModal");
    }

});

</script>


</body>
</html>