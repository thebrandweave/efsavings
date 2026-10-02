<?php
session_start();

// If promoter is already logged in, redirect to dashboard
if (isset($_SESSION['promoter_id'])) {
    header("Location: dashboard/index.php");
    exit();
}

// Database connection
require_once("../config/config.php");
$database = new Database();
$conn = $database->getConnection();

// Initialize variables
$contact = $password = "";
$contactErr = $passwordErr = $loginErr = "";
$rememberMe = false;

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate contact
    if (empty($_POST["contact"])) {
        $contactErr = "Contact number is required";
    } else {
        $contact = trim($_POST["contact"]);
    }

    // Validate password
    if (empty($_POST["password"])) {
        $passwordErr = "Password is required";
    } else {
        $password = $_POST["password"];
    }

    // Check remember me
    $rememberMe = isset($_POST["remember_me"]);

    // Proceed if no validation errors
    if (empty($contactErr) && empty($passwordErr)) {
        try {
            // Check if contact exists and get promoter info
            $stmt = $conn->prepare("SELECT PromoterID, Name, Contact, Email, PasswordHash, Status FROM Promoters WHERE Contact = ?");
            $stmt->execute([$contact]);
            $promoter = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($promoter) {
                // Check if password hash exists
                if (empty($promoter['PasswordHash']) || is_null($promoter['PasswordHash'])) {
                    $loginErr = "Password not set. Please contact administrator to reset your password.";
                } else {
                    // Verify password
                    if (password_verify($password, $promoter['PasswordHash'])) {
                        // Check if account is active
                        if ($promoter['Status'] === 'Active') {
                            // Set up the user session
                            $_SESSION['promoter_id'] = $promoter['PromoterID'];
                            $_SESSION['promoter_name'] = $promoter['Name'];
                            $_SESSION['promoter_contact'] = $promoter['Contact'];
                            $_SESSION['promoter_email'] = $promoter['Email'];

                            // Log the activity
                            $action = "Logged in";
                            $stmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Promoter', ?, ?)");
                            $stmt->execute([$promoter['PromoterID'], $action, $_SERVER['REMOTE_ADDR']]);

                            // Regenerate the session ID to prevent session fixation
                            session_regenerate_id(true);

                            // Redirect to dashboard
                            header("Location: dashboard/index.php");
                            exit();
                        } else {
                            $loginErr = "Your account is inactive. Please contact the administrator.";
                        }
                    } else {
                        $loginErr = "Invalid contact number or password";
                    }
                }
            } else {
                $loginErr = "Invalid contact number or password";
            }
        } catch (PDOException $e) {
            $loginErr = "An error occurred. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promoter Portal | Liya's Furniture & Electronics</title>
    <link rel="icon" type="image/png" href="../landing/landing_assets/images/liyas_logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-magenta: #1045b9;
            --brand-blue: #055296;
            --brand-gradient: linear-gradient(135deg, #c099bd 0%, #0b5cad 100%);
            --brand-gradient-hover: linear-gradient(135deg, #b085ac 0%, #094a8c 100%);
            --dark-bg: #07101c;
            --dark-card: #0a1521;
            --border-subtle: rgba(192, 153, 189, 0.25);
            --text-primary: #F8FAFC;
            --text-secondary: #94A3B8;
            --border-radius: 14px;
            --transition-speed: 0.3s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at 10% 20%, rgba(255, 255, 255, 0.12) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, rgba(11, 92, 173, 0.22) 0%, transparent 40%),
                        linear-gradient(135deg, #c099bd  0%, #0b5cad 100%);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .login-container {
            width: 100%;
            max-width: 440px;
            background: var(--dark-card);
            border-radius: var(--border-radius);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), 0 0 30px rgba(11, 92, 173, 0.2);
            border: 1px solid var(--border-subtle);
            overflow: hidden;
            position: relative;
        }

        .login-top-bar {
            height: 4px;
            width: 100%;
            background: var(--brand-gradient);
        }

        .login-header {
            padding: 36px 30px 20px;
            text-align: center;
        }

        .logo-box {
            display: flex;
            justify-content: center;
            margin-bottom: 16px;
        }

        .logo-box img {
            max-height: 52px;
            width: auto;
            object-fit: contain;
            transition: all var(--transition-speed) ease;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, rgba(192, 153, 189, 0.2) 0%, rgba(11, 92, 173, 0.25) 100%);
            border: 1px solid rgba(192, 153, 189, 0.4);
            color: #d8c2d6;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 10px;
        }

        .login-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 4px;
        }

        .login-header p {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .login-form {
            padding: 10px 32px 36px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #E2E8F0;
            font-size: 13.5px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-subtle);
            border-radius: 10px;
            font-size: 14px;
            color: #ffffff;
            transition: all var(--transition-speed) ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--brand-magenta);
            box-shadow: 0 0 0 3px rgba(11, 92, 173, 0.25);
            outline: none;
            color: #ffffff;
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-secondary);
            transition: color var(--transition-speed) ease;
        }

        .password-toggle:hover {
            color: var(--brand-magenta);
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            font-size: 13px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
            cursor: pointer;
        }

        .remember-me input {
            accent-color: var(--brand-magenta);
            width: 15px;
            height: 15px;
            cursor: pointer;
        }

        .forgot-password {
            color: #a6b7ff;
            text-decoration: none;
            transition: color var(--transition-speed) ease;
        }

        .forgot-password:hover {
            color: #c099bd;
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            padding: 13px 18px;
            background: var(--brand-gradient);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
            box-shadow: 0 4px 18px rgba(11, 92, 173, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .login-btn:hover {
            background: var(--brand-gradient-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(11, 92, 173, 0.5);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .error-text {
            color: #f87171;
            font-size: 12px;
            margin-top: 6px;
        }

        .alert {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 13.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger {
            background-color: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .alert-success {
            background-color: rgba(34, 197, 94, 0.15);
            color: #86efac;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        @media (max-width: 480px) {
            .login-container {
                max-width: 100%;
            }

            .login-header {
                padding: 24px 20px 16px;
            }

            .login-form {
                padding: 10px 20px 24px;
            }
        }
    </style>
</head>

<body>
    <?php include("./components/loader.php"); ?>
    <div class="login-container">
        <div class="login-top-bar"></div>
        <div class="login-header">
            <div class="logo-box">
                <img src="../landing/landing_assets/images/liyas_logo_white.png" alt="Liya's Furniture & Electronics Logo">
            </div>
          
            <h1>Promoter Sign In</h1>
            <p>A Unit of Pro Gee Dee Ventures Pvt. Ltd.</p>
        </div>

        <div class="login-form">
            <?php if (!empty($loginErr)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <span><?php echo $loginErr; ?></span>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="contact">Contact Number</label>
                    <input type="text" id="contact" name="contact" class="form-control"
                        value="<?php echo htmlspecialchars($contact); ?>"
                        placeholder="Enter registered mobile number"
                        autocomplete="tel">
                    <?php if (!empty($contactErr)): ?>
                        <div class="error-text"><?php echo $contactErr; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="••••••••••••"
                            autocomplete="current-password">
                        <i class="password-toggle fas fa-eye-slash" id="toggle-password"></i>
                    </div>
                    <?php if (!empty($passwordErr)): ?>
                        <div class="error-text"><?php echo $passwordErr; ?></div>
                    <?php endif; ?>
                </div>

                <div class="remember-forgot">
                    <label class="remember-me">
                        <input type="checkbox" id="remember_me" name="remember_me"
                            <?php if ($rememberMe) echo "checked"; ?>>
                        <span>Remember me</span>
                    </label>
                    <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Sign In 
                </button>
            </form>

            <div class="login-footer">
                <p>&copy; <?php echo date('Y'); ?> Liya's Furniture & Electronics. All rights reserved.</p>
                <div style="margin-top: 10px;">
                    <a href="../landing/index.php" style="color: var(--text-secondary); text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fas fa-arrow-left"></i> Return to Homepage
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('toggle-password');
        const passwordField = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
            this.classList.toggle('fa-eye');
        });

        // Auto-fade alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 500);
            }, 5000);
        });
    </script>
</body>

</html>