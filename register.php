<?php

// Configuration
require_once __DIR__ . '/includes/config.php';

// Database connection
require_once __DIR__ . '/includes/db.php';


$message = "";
$message_type = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form data
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";


    // =========================
    // VALIDATION
    // =========================

    // Check password match
    if ($password !== $confirm_password) {

        $message = "Passwords do not match.";
        $message_type = "error";

    }

    // Check password length
    elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
        $message_type = "error";

    }

    else {

        // =========================
        // CHECK EMAIL
        // =========================

        $stmt = $conn->prepare(
            "SELECT user_id
             FROM users
             WHERE email = ?"
        );

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();


        if ($result->num_rows > 0) {

            $message = "An account with this email already exists.";
            $message_type = "error";

        }

        else {

            // =========================
            // HASH PASSWORD
            // =========================

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            // =========================
            // INSERT INTO USERS
            // =========================

            $stmt = $conn->prepare(
                "INSERT INTO users
                (name, email, password, role)
                VALUES (?, ?, ?, 'patient')"
            );

            $stmt->bind_param(
                "sss",
                $name,
                $email,
                $hashed_password
            );


            if ($stmt->execute()) {

                // Get newly created user ID
                $user_id = $conn->insert_id;


                // =========================
                // INSERT INTO PATIENTS
                // =========================

                $stmt = $conn->prepare(
                    "INSERT INTO patients
                    (name, email, phone, password)
                    VALUES (?, ?, ?, ?)"
                );

                $stmt->bind_param(
                    "ssss",
                    $name,
                    $email,
                    $phone,
                    $hashed_password
                );


                if ($stmt->execute()) {

                    // =========================
                    // SUCCESS
                    // =========================

                    header(
                        "Location: " . BASE_URL . "login.php?registered=1"
                    );

                    exit;

                }

                else {

                    // Patient insert failed
                    $message = "Patient profile creation failed.";
                    $message_type = "error";
                }

            }

            else {

                // User insert failed
                $message = "Registration failed. Please try again.";
                $message_type = "error";
            }
        }


        $stmt->close();
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<?php require_once __DIR__ . '/includes/head.php'; ?>


<body>


<?php require_once __DIR__ . '/includes/header.php'; ?>


<div class="auth-container">

    <div class="auth-card register-card">


        <div class="auth-icon">

            <i class="fa-solid fa-user-plus"></i>

        </div>


        <h1>
            Create Account
        </h1>


        <p class="auth-subtitle">

            Register as a patient on MediBook

        </p>



        <?php if (!empty($message)): ?>

            <div class="auth-message <?php echo $message_type; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>



        <form action="" method="POST">


            <!-- Full Name -->

            <div class="input-group">

                <label for="name">
                    Full Name
                </label>


                <div class="input-box">

                    <i class="fa-regular fa-user"></i>


                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Enter your full name"
                        value="<?php echo htmlspecialchars($name ?? ''); ?>"
                        required
                    >

                </div>

            </div>



            <!-- Email -->

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
                        value="<?php echo htmlspecialchars($email ?? ''); ?>"
                        required
                    >

                </div>

            </div>



            <!-- Phone -->

            <div class="input-group">

                <label for="phone">
                    Phone Number
                </label>


                <div class="input-box">

                    <i class="fa-solid fa-phone"></i>


                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        placeholder="Enter your phone number"
                        value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                        required
                    >

                </div>

            </div>



            <!-- Password Row -->

            <div class="input-row">


                <!-- Password -->

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
                            placeholder="Password"
                            required
                        >

                    </div>

                </div>



                <!-- Confirm Password -->

                <div class="input-group">

                    <label for="confirm_password">
                        Confirm Password
                    </label>


                    <div class="input-box">

                        <i class="fa-solid fa-lock"></i>


                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Confirm password"
                            required
                        >

                    </div>

                </div>


            </div>



            <!-- Submit -->

            <button
                type="submit"
                class="auth-btn"
            >

                Create Account

                <i class="fa-solid fa-arrow-right"></i>

            </button>


        </form>



        <!-- Login Link -->

        <p class="auth-footer">

            Already have an account?


            <a href="<?= BASE_URL ?>login.php">

                Login here

            </a>

        </p>


    </div>

</div>



<?php require_once __DIR__ . '/includes/footer.php'; ?>


</body>

</html>