<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header>

    <div class="container navbar">

        <a href="<?= BASE_URL ?>index.php" class="logo">

            <div class="logo-icon">
                <i class="fa-solid fa-plus"></i>
            </div>

            MediBook

        </a>


        <nav class="nav-links">

            <a href="<?= BASE_URL ?>index.php" class="active">
                Home
            </a>

            <a href="<?= BASE_URL ?>index.php#how-it-works">
                How It Works
            </a>

            <a href="<?= BASE_URL ?>doctor/doctor.php">
                Doctors
            </a>

            <a href="<?= BASE_URL ?>index.php#about">
                About
            </a>

        </nav>


        <div class="nav-actions">

            <?php if (isset($_SESSION["patient_id"])): ?>

                <!-- Logged In -->

                <a
                    href="<?= BASE_URL ?>patient/patient_dashboard.php"
                    class="login-link"
                >
                    Dashboard
                </a>

                <a
                    href="<?= BASE_URL ?>logout.php"
                    class="register-btn"
                >
                    Logout
                </a>


            <?php else: ?>

                <!-- Not Logged In -->

                <a
                    href="<?= BASE_URL ?>login.php"
                    class="login-link"
                >
                    Login
                </a>

                <a
                    href="<?= BASE_URL ?>register.php"
                    class="register-btn"
                >
                    Register
                </a>

            <?php endif; ?>

        </div>

    </div>

</header>