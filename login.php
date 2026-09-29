<?php

session_start();

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // Check empty fields
    if (empty($email) || empty($password)) {

        $error = "Email and password are required.";

    } else {

        // Find user by email
        $stmt = $conn->prepare(
            "SELECT user_id, name, email, password, role
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            // Check password
            if (password_verify($password, $user["password"])) {

                // Common session data
                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];
                $_SESSION["role"] = $user["role"];

                // =========================
                // ADMIN LOGIN
                // =========================
                if ($user["role"] === "admin") {

                    $_SESSION["admin_id"] = $user["user_id"];

                    $stmt->close();

                    header(
                        "Location: " . BASE_URL . "dashboard/admin_dashboard.php"
                    );
                    exit;
                }

                // =========================
                // PATIENT LOGIN
                // =========================
                elseif ($user["role"] === "patient") {

                    /*
                     * IMPORTANT:
                     * patient dashboard uses patients.id,
                     * not users.user_id.
                     */

                    $patient_stmt = $conn->prepare(
                        "SELECT id
                         FROM patients
                         WHERE email = ?
                         LIMIT 1"
                    );

                    $patient_stmt->bind_param("s", $user["email"]);
                    $patient_stmt->execute();

                    $patient_result = $patient_stmt->get_result();

                    if ($patient_result->num_rows === 1) {

                        $patient = $patient_result->fetch_assoc();

                        // Use patients table ID
                        $_SESSION["patient_id"] = $patient["id"];

                        $patient_stmt->close();
                        $stmt->close();

                        header(
                            "Location: " . BASE_URL . "patient/patient_dashboard.php"
                        );
                        exit;

                    } else {

                        $patient_stmt->close();

                        // Remove sessions if patient profile doesn't exist
                        session_unset();
                        session_destroy();

                        $error = "Patient profile not found.";
                    }

                } else {

                    $stmt->close();

                    session_unset();
                    session_destroy();

                    $error = "Invalid user role.";
                }

            } else {

                $error = "Invalid email or password.";
            }

        } else {

            $error = "Invalid email or password.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<?php include 'includes/head.php'; ?>

<body>

<?php include 'includes/header.php'; ?>


<div class="auth-container">

    <div class="auth-card">

        <div class="auth-icon">
            <i class="fa-solid fa-user-doctor"></i>
        </div>

        <h1>Welcome Back</h1>

        <p class="auth-subtitle">
            Login to your MediBook account
        </p>


        <?php if (isset($_GET["registered"])): ?>

            <div class="auth-message success">
                Registration successful! Please login.
            </div>

        <?php endif; ?>


        <?php if (!empty($error)): ?>

            <div class="auth-message error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <form action="" method="POST">

            <div class="input-group">

                <label for="email">
                    Email Address
                </label>

                <div class="input-box">

                    <i class="fa-regular fa-envelope"></i>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        required
                    >

                </div>

            </div>


            <div class="input-group">

                <label for="password">
                    Password
                </label>

                <div class="input-box">

                    <i class="fa-solid fa-lock"></i>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >

                </div>

            </div>


            <div class="form-options">

                <label class="remember">

                    <input
                        type="checkbox"
                        name="remember"
                    >

                    Remember me

                </label>


                <a href="#">
                    Forgot Password?
                </a>

            </div>


            <button
                type="submit"
                class="auth-btn"
            >

                Login

                <i class="fa-solid fa-arrow-right"></i>

            </button>

        </form>


        <p class="auth-footer">

            Don't have an account?

            <a href="<?= BASE_URL ?>register.php">
                Create an account
            </a>

        </p>

    </div>

</div>


<?php include 'includes/footer.php'; ?>

</body>
</html>