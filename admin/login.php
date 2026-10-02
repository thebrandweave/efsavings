<?php
session_start();
$menuPath = "./";
// Include the auth middleware to check if user is already authenticated
require("./middleware/auth.php");

// Check if user is already authenticated via JWT token
if (isset($_COOKIE['admin_token'])) {
    $token = $_COOKIE['admin_token'];
    $decoded = JWTManager::verifyToken($token);

    if ($decoded) {
        // Restore session from token if expired
        if (!isset($_SESSION['admin_role'])) {
            $_SESSION['admin_id'] = $decoded->admin_id;
            $_SESSION['admin_email'] = $decoded->email;
            $_SESSION['admin_role'] = $decoded->role;
        }

        // User is already authenticated, redirect based on role
        if ($_SESSION['admin_role'] === 'Verifier') {
            header("Location: promoter/");
        } else {
            header("Location: dashboard/");
        }
        exit();
    }
}



// Database connection
require_once("../config/config.php");
$database = new Database();
$conn = $database->getConnection();

// Initialize variables
$email = $password = "";
$emailErr = $passwordErr = $loginErr = "";
$rememberMe = false;

// Check if the user has a remember me cookie
if (isset($_COOKIE['admin_remember']) && !isset($_SESSION['admin_id'])) {
    list($selector, $validator) = explode(':', $_COOKIE['admin_remember']);

    // Lookup the token in the database
    $stmt = $conn->prepare("SELECT a.AdminID, a.Name, a.Email, a.Role, a.Status, rt.token, rt.expires 
                           FROM RememberTokens rt 
                           JOIN Admins a ON rt.AdminID = a.AdminID 
                           WHERE rt.selector = ? AND rt.expires > NOW()");
    $stmt->execute([$selector]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && $result['Status'] === 'Active') {
        // Verify the token
        if (hash_equals(hash('sha256', base64_decode($validator)), $result['token'])) {
            // Set up the user session
            $_SESSION['admin_id'] = $result['AdminID'];
            $_SESSION['admin_name'] = $result['Name'];
            $_SESSION['admin_email'] = $result['Email'];
            $_SESSION['admin_role'] = $result['Role'];

            // Log the activity
            $action = "Logged in via remember me cookie";
            $stmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Admin', ?, ?)");
            $stmt->execute([$result['AdminID'], $action, $_SERVER['REMOTE_ADDR']]);

            // Regenerate the session ID to prevent session fixation
            session_regenerate_id(true);

            // After successful login, redirect based on role
            if ($result['Role'] === 'Verifier') {
                header("Location: promoter/");
            } else {
                header("Location: dashboard/");
            }
            exit();
        }
    }

    // If we get here, the cookie is invalid or expired, so clear it
    setcookie('admin_remember', '', time() - 3600, '/');
}

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate email
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = trim($_POST["email"]);
        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        }
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
    if (empty($emailErr) && empty($passwordErr)) {
        try {
            // Check if email exists and get admin info
            $stmt = $conn->prepare("SELECT AdminID, Name, Email, PasswordHash, Role, Status FROM Admins WHERE Email = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin) {
                // Verify password
                if (password_verify($password, $admin['PasswordHash'])) {
                    // Check if account is active
                    if ($admin['Status'] === 'Active') {
                        // Generate JWT token
                        require_once("../config/JWT.php");
                        $jwtToken = JWTManager::generateToken($admin['AdminID'], $admin['Email'], $admin['Role']);

                        // Set JWT token in cookie
                        setcookie(
                            'admin_token',
                            $jwtToken,
                            time() + 3600, // 1 hour
                            '/',
                            '',
                            true, // secure
                            true  // httponly
                        );

                        // Set up the user session
                        $_SESSION['admin_id'] = $admin['AdminID'];
                        $_SESSION['admin_name'] = $admin['Name'];
                        $_SESSION['admin_email'] = $admin['Email'];
                        $_SESSION['admin_role'] = $admin['Role'];

                        // Handle remember me
                        if ($rememberMe) {
                            // Create a new remember me token
                            $selector = bin2hex(random_bytes(8));
                            $validator = random_bytes(32);

                            // Store the token in the database
                            $stmt = $conn->prepare("INSERT INTO RememberTokens (AdminID, selector, token, expires) 
                                                   VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))
                                                   ON DUPLICATE KEY UPDATE 
                                                   token = VALUES(token), 
                                                   expires = VALUES(expires)");

                            $hashedValidator = hash('sha256', $validator);
                            $stmt->execute([$admin['AdminID'], $selector, $hashedValidator]);

                            // Set the cookie
                            setcookie(
                                'admin_remember',
                                $selector . ':' . base64_encode($validator),
                                time() + (30 * 24 * 60 * 60), // 30 days
                                '/',
                                '',
                                true, // secure
                                true  // httponly
                            );
                        }

                        // Log the activity
                        $action = "Logged in";
                        $stmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Admin', ?, ?)");
                        $stmt->execute([$admin['AdminID'], $action, $_SERVER['REMOTE_ADDR']]);

                        // Regenerate the session ID to prevent session fixation
                        session_regenerate_id(true);

                        // After successful login, redirect to dashboard
                        header("Location: dashboard/");
                        exit();
                    } else {
                        $loginErr = "Your account is inactive. Please contact the administrator.";
                    }
                } else {
                    $loginErr = "Invalid email or password.";
                }
            } else {
                $loginErr = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $loginErr = "An error occurred. Please try again later.";
            // Log the error
            error_log("Login error: " . $e->getMessage());
            echo  $e->getMessage();
        }
    }
}

// If the RememberTokens table doesn't exist, create it
// try {
//     $stmt = $conn->prepare("
//         CREATE TABLE IF NOT EXISTS RememberTokens (
//             id INT AUTO_INCREMENT PRIMARY KEY,
//             AdminID INT NOT NULL,
//             selector VARCHAR(16) NOT NULL,
//             token VARCHAR(64) NOT NULL,
//             expires TIMESTAMP NOT NULL,
//             UNIQUE KEY (selector),
//             FOREIGN KEY (AdminID) REFERENCES Admins(AdminID) ON DELETE CASCADE
//         )
//     ");
//     $stmt->execute();
// } catch (PDOException $e) {
//     Silently fail (table probably already exists)
// }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Console | Liya's Furniture & Electronics</title>
    <link rel="icon" type="image/png" href="../landing/landing_assets/images/liyas_logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-magenta: #9B0090;
            --brand-blue: #0B5CAD;
            --brand-gradient: linear-gradient(135deg, #9B0090 0%, #0B5CAD 100%);
            --brand-gradient-hover: linear-gradient(135deg, #b300a7 0%, #0d6ecc 100%);
            --dark-bg: #0B0F19;
            --dark-card: #111827;
            --border-subtle: rgba(255, 255, 255, 0.08);
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
            background: radial-gradient(circle at 10% 20%, rgba(155, 0, 144, 0.15) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, rgba(11, 92, 173, 0.15) 0%, transparent 40%),
                        linear-gradient(135deg, #0B0F19 0%, #151D2A 100%);
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
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), 0 0 30px rgba(155, 0, 144, 0.15);
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
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(155, 0, 144, 0.15);
            border: 1px solid rgba(155, 0, 144, 0.35);
            color: #f5d0f2;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 12px;
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
            box-shadow: 0 0 0 3px rgba(155, 0, 144, 0.25);
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
            color: #38bdf8;
            text-decoration: none;
            transition: color var(--transition-speed) ease;
        }

        .forgot-password:hover {
            color: #7dd3fc;
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
            box-shadow: 0 4px 18px rgba(155, 0, 144, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .login-btn:hover {
            background: var(--brand-gradient-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(155, 0, 144, 0.5);
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
            <div class="role-badge">
                <i class="fas fa-shield-halved"></i> Admin Console
            </div>
            <h1>Administrative Sign In</h1>
            <p>A Unit of Pro Gee Dee Ventures Pvt. Ltd.</p>
        </div>

        <div class="login-form">
            <?php if (!empty($loginErr)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <span><?php echo $loginErr; ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <span><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></span>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="email">Admin Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" placeholder="admin@efsavings.in" autocomplete="email">
                    <?php if (!empty($emailErr)): ?>
                        <div class="error-text"><?php echo $emailErr; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••••••" autocomplete="current-password">
                        <i class="password-toggle fas fa-eye-slash" id="toggle-password"></i>
                    </div>
                    <?php if (!empty($passwordErr)): ?>
                        <div class="error-text"><?php echo $passwordErr; ?></div>
                    <?php endif; ?>
                </div>

                <div class="remember-forgot">
                    <label class="remember-me">
                        <input type="checkbox" id="remember_me" name="remember_me" <?php if ($rememberMe) echo "checked"; ?>>
                        <span>Remember me</span>
                    </label>
                    <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Sign In to Console</span>
                </button>
            </form>

            <div class="login-footer">
                <p>&copy; <?php echo date('Y'); ?> Liya's Furniture & Electronics.<br>All rights reserved.</p>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('toggle-password');
        const passwordField = document.getElementById('password');

        if (togglePassword && passwordField) {
            togglePassword.addEventListener('click', function() {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }

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