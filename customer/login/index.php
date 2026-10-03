<?php
require_once '../config/session_check.php';

// If user is already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: ../dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login - Liya's Furniture & Electronics</title>
    <link rel="icon" type="image/png" href="../uploads/liyas_logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --dark-bg: #071220;
            --card-bg: #0c1e34;
            --accent-brand: #0284c7;
            --accent-blue: #0369a1;
            --brand-gradient: linear-gradient(135deg, #5b70a3 0%, #000000 100%);
            --text-primary: rgba(255, 255, 255, 0.9);
            --text-secondary: rgba(255, 255, 255, 0.7);
            --border-color: rgba(2, 132, 199, 0.22);
        }

        body {
            background: linear-gradient(135deg, #5b70a3 0%, #000000 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }

        .login-container {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            padding: 40px;
            width: 100%;
            max-width: 420px;
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--brand-gradient);
        }

        .logo {
            text-align: center;
            margin-bottom: 26px;
        }

        .logo img {
            max-height: 52px;
            width: auto;
            margin-bottom: 12px;
        }

        .logo h1 {
            color: #ffffff;
            font-weight: 700;
            margin-bottom: 6px;
            font-size: 22px;
        }

        .logo p {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .form-label {
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px;
            color: var(--text-primary);
            transition: all 0.3s ease;
            text-transform:uppercase;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--accent-brand);
            box-shadow: 0 0 0 3px rgba(155, 0, 144, 0.25);
            color: #ffffff;
        }

        .form-control::placeholder {
            color: var(--text-secondary);
             text-transform:none;

        }

        .btn-login {
            background: var(--brand-gradient);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.5s ease;
            color: white;
            /* box-shadow: 0 4px 15px rgba(155, 0, 144, 0.3); */
        }

        .btn-login:hover {
            transform: translateY(-2px);
            /* box-shadow: 0 8px 25px rgba(155, 0, 144, 0.45); */
            background: linear-gradient(135deg, #000000 0%, #113c66 100%);
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
        }

        .form-check-input {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: var(--border-color);
        }

        .form-check-input:checked {
            background-color: var(--accent-brand);
            border-color: var(--accent-brand);
        }

        .signup-link {
            text-align: center;
            margin-top: 20px;
            color: var(--text-secondary);
        }

        .signup-link a {
            color: #38bdf8;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .signup-link a:hover {
            color: #7dd3fc;
            text-decoration: underline;
        }

        .error-message {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }

        .position-relative i {
            color: #94a3b8;
            transition: all 0.3s ease;
        }

        .position-relative i:hover {
            color: #322929;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 20px;
                padding: 30px 20px;
            }

            .logo h1 {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <?php include '../c_includes/loader.php'; ?>
    <div class="login-container">
        <div class="logo">
            <img src="../../landing/landing_assets/images/liyas_logo_white.png"" alt="Liya's Furniture & Electronics Logo">
            <div style="margin-top: 6px; margin-bottom: 8px;">
              
            </div>
            <h1 style="font-size:1.35rem; font-weight:700; color:#fff; margin-bottom: 4px;">Customer Sign In</h1>
            <p class="text-muted small m-0" style="color: var(--text-secondary) !important;">A Unit of Pro Gee Dee Ventures Pvt. Ltd.</p>
        </div>

        <div class="error-message" id="errorMessage"></div>

        <form id="loginForm" action="process_login.php" method="POST">
            <div class="mb-3">
                <label for="customerId" class="form-label">Customer ID</label>
                <input type="text" class="form-control" id="customerId" name="customerId"
                    placeholder="Enter your customer ID" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="position-relative">
                    <input type="password"  style="text-transform:none;" class="form-control" id="password" name="password"
                        placeholder="Enter your password" required>
                    <i class="fas fa-eye-slash position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer;"
                        onclick="togglePassword()"></i>
                </div>
            </div>

            <div class="mb-3 remember-me">
                <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe">
                <label class="form-check-label" for="rememberMe">Remember me for 30 days</label>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </div>
        </form>

        <div class="signup-link">
            <!-- <p class="mb-1" style="font-size: 0.9rem;">Don't have an account? <a href="../signup/">Sign up here</a></p> -->
            <p class="mb-0" style="font-size: 0.85rem;"><a href="../../landing/index.php" style="color: var(--text-secondary);"><i class="fas fa-arrow-left"></i> Return to Homepage</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      function togglePassword() {
    const passwordInput = document.getElementById("password");
    const toggleIcon = document.querySelector(".fa-eye, .fa-eye-slash");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");
    } else {
        passwordInput.type = "password";
        toggleIcon.classList.remove("fa-eye");
        toggleIcon.classList.add("fa-eye-slash");
    }
}
        // Check for error message in URL
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        if (error) {
            const errorMessage = document.getElementById('errorMessage');
            errorMessage.textContent = decodeURIComponent(error);
            errorMessage.style.display = 'block';
        }
    </script>
</body>

</html>