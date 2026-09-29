<!DOCTYPE html>
<html lang="en">
    <?php include 'includes/head.php'; ?>

<body>

<?php include 'includes/header.php'; ?>

    <section class="hero">

        <div class="hero-content">

            <div class="hero-badge">
                <i class="fa-solid fa-heart-pulse"></i>
                Simple & Convenient Healthcare
            </div>

            <h1>
                BOOK YOUR<br>
                <span>APPOINTMENT!</span>
            </h1>

            <p>
                Find the right doctor, choose a convenient schedule,
                and manage your clinic appointments easily with MediBook.
            </p>

            <div class="hero-buttons">

                <a href="<?= BASE_URL ?>appointment.php" class="hero-btn">
                    Book an Appointment
                    <i class="fa-solid fa-arrow-right"></i>
                </a>

                <a href="doctor/doctor.php" class="hero-outline-btn">
                    Find a Doctor
                </a>

            </div>

        </div>

    </section>


    <!-- ================= HOW IT WORKS ================= -->

    <section class="appointment" id="how-it-works">

        <div class="container">

            <div class="section-label">
                HOW IT WORKS
            </div>

            <h2 class="section-title">
                DISCOVER THE <strong>ONLINE</strong> APPOINTMENT!
            </h2>

            <p class="section-description">
                MediBook makes booking a clinic appointment simple,
                fast, and convenient.
            </p>


            <div class="steps">

                <!-- STEP 1 -->

                <div class="step">

                    <div class="step-number">
                        01
                    </div>

                    <div class="step-icon">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>

                    <h4>
                        FIND A DOCTOR
                    </h4>

                    <p>
                        Search for doctors by name or specialization
                        and find the right doctor for your needs.
                    </p>

                </div>


                <!-- STEP 2 -->

                <div class="step">

                    <div class="step-number">
                        02
                    </div>

                    <div class="step-icon">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>

                    <h4>
                        CHOOSE DATE & TIME
                    </h4>

                    <p>
                        Check the doctor's availability and select
                        a convenient date and appointment time.
                    </p>

                </div>


                <!-- STEP 3 -->

                <div class="step">

                    <div class="step-number">
                        03
                    </div>

                    <div class="step-icon">
                        <i class="fa-regular fa-clipboard"></i>
                    </div>

                    <h4>
                        BOOK APPOINTMENT
                    </h4>

                    <p>
                        Confirm your appointment and manage your
                        upcoming visits from your patient dashboard.
                    </p>

                </div>

            </div>


            <a href="<?= BASE_URL ?>appointment.php" class="pink-btn">
                Get Started
                <i class="fa-solid fa-arrow-right"></i>
            </a>

        </div>

    </section>


    <!-- ================= ABOUT ================= -->

    <section class="about-section" id="about">

        <div class="container about-content">


            <!-- Illustration -->

            <div class="medical-illustration">

                <div class="medical-circle">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>

                <div class="medical-plus plus-one">
                    <i class="fa-solid fa-plus"></i>
                </div>

                <div class="medical-plus plus-two">
                    <i class="fa-solid fa-plus"></i>
                </div>

                <div class="medical-dot"></div>

            </div>


            <!-- Text -->

            <div class="about-text">

                <div class="app-small">
                    ABOUT MEDIBOOK
                </div>

                <h2>
                    Your Health,
                    <span>Our Priority!</span>
                </h2>

                <p>
                    MediBook is a clinic appointment management system
                    designed to make healthcare appointments easier for
                    patients, doctors, and clinic administrators.
                </p>

                <div class="feature-list">

                    <div class="feature-item">
                        <i class="fa-solid fa-circle-check"></i>
                        Easy online appointment booking
                    </div>

                    <div class="feature-item">
                        <i class="fa-solid fa-circle-check"></i>
                        Find doctors by specialization
                    </div>

                    <div class="feature-item">
                        <i class="fa-solid fa-circle-check"></i>
                        Manage appointments in one place
                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- ================= DOCTORS ================= -->

    

    <!-- ================= CTA ================= -->

    <section class="cta-section">

        <div class="container">

            <div class="cta-icon">
                <i class="fa-solid fa-calendar-check"></i>
            </div>

            <h2>
                Ready to book your appointment?
            </h2>

            <p>
                Create your account and start managing your appointments today.
            </p>

            <a href="<?= BASE_URL ?>register.php" class="hero-btn">
                Create Patient Account
                <i class="fa-solid fa-arrow-right"></i>
            </a>

        </div>

    </section>


    <!-- ================= FOOTER ================= -->

  <?php include 'includes/footer.php'; ?>
</body>
</html>