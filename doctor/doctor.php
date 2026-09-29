<?php
require_once __DIR__ . '/../includes/config.php';

$conn = new mysqli(
    "localhost",
    "root",
    "",
    "clinic_db"
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

require_once __DIR__ . '/../includes/head.php';

$query = "SELECT * FROM doctors";
$result = $conn->query($query);
?>

<body>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>doctor/doctor.css">

<main class="doctors-page">

    <section class="doctors-hero">
        <div class="container">
            <h1>Our Doctors</h1>
            <p>Meet our experienced and trusted healthcare professionals.</p>
        </div>
    </section>

    <section class="doctors-section">
        <div class="container">

            <div class="doctors-grid">

                <?php while ($doctor = $result->fetch_assoc()): ?>

                    <div class="doctor-card">

                        <div class="doctor-image">
                            <img
                                src="<?= BASE_URL ?>img/d1.png"
                                alt="<?= htmlspecialchars($doctor['name']) ?>"
                            >
                        </div>

                        <div class="doctor-info">

                            <h3>
                                <?= htmlspecialchars($doctor['name']) ?>
                            </h3>

                            <p class="specialization">
                                <?= htmlspecialchars($doctor['specialization']) ?>
                            </p>

                            <p class="doctor-description">
                                Experienced healthcare professional
                                specializing in
                                <?= htmlspecialchars($doctor['specialization']) ?>.
                            </p>

                            <a
                                href="<?= BASE_URL ?>appointment.php"
                                class="appointment-btn"
                            >
                                Book Appointment
                            </a>

                        </div>

                    </div>

                <?php endwhile; ?>

            </div>

        </div>
    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>