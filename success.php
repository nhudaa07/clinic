<?php

session_start();

require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION["patient_id"])) {
    header("Location: " . BASE_URL . "login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<?php include 'includes/head.php'; ?>

<style>
    .success-page {
        min-height: 75vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 70px 20px;
        background: #f8fafc;
    }

    .success-card {
        width: 100%;
        max-width: 560px;
        background: #ffffff;
        border-radius: 18px;
        padding: 50px 40px;
        text-align: center;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.07);
    }

    .success-icon {
        width: 78px;
        height: 78px;
        margin: 0 auto 25px;
        border-radius: 50%;
        background: #e9f9f1;
        color: #18a56b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 34px;
    }

    .success-card h1 {
        margin: 0 0 12px;
        font-size: 30px;
        color: #172033;
        font-weight: 700;
    }

    .success-card h1 span {
        color: #18a56b;
    }

    .success-card p {
        margin: 0 auto 30px;
        max-width: 430px;
        color: #6b7280;
        font-size: 15px;
        line-height: 1.7;
    }

    .success-info {
        background: #f8fafc;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 28px;
        color: #475569;
        font-size: 14px;
    }

    .success-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 13px 24px;
        border-radius: 9px;
        background: #18a56b;
        color: #ffffff;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: 0.2s ease;
    }

    .success-btn:hover {
        background: #128a59;
        transform: translateY(-1px);
    }

    .secondary-link {
        display: block;
        margin-top: 18px;
        color: #64748b;
        text-decoration: none;
        font-size: 14px;
    }

    .secondary-link:hover {
        color: #18a56b;
    }

    @media (max-width: 600px) {

        .success-card {
            padding: 40px 25px;
        }

        .success-card h1 {
            font-size: 25px;
        }

    }
</style>

<body>

<?php include 'includes/header.php'; ?>


<section class="success-page">

    <div class="success-card">

        <div class="success-icon">
            <i class="fa-solid fa-check"></i>
        </div>

        <h1>
            Appointment <span>Booked!</span>
        </h1>

        <p>
            Your appointment request has been submitted successfully.
            We will process your appointment and keep you updated.
        </p>

        <div class="success-info">

            <i class="fa-solid fa-circle-info"></i>
            &nbsp;

            Your appointment status is currently
            <strong>Pending</strong>.

        </div>


        <a
            href="<?= BASE_URL ?>patient/patient_dashboard.php"
            class="success-btn"
        >

            Go to Dashboard

            <i class="fa-solid fa-arrow-right"></i>

        </a>


        <a
            href="<?= BASE_URL ?>appointment.php"
            class="secondary-link"
        >

            Book another appointment

        </a>

    </div>

</section>


<?php include 'includes/footer.php'; ?>

</body>
</html>