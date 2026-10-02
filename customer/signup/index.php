<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Liya's Furniture & Electronics</title>
    <link rel="icon" type="image/png" href="../../landing/landing_assets/images/liyas_logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-magenta: #9B0090;
            --brand-blue: #0B5CAD;
            --brand-gradient: linear-gradient(135deg, #9B0090 0%, #0B5CAD 100%);
            --brand-gradient-hover: linear-gradient(135deg, #b300a7 0%, #0d6ecc 100%);
            --dark-bg: #0B0F19;
            --card-bg: #111827;
            --text-primary: #F8FAFC;
            --text-secondary: #94A3B8;
            --border-color: rgba(255, 255, 255, 0.08);
            --border-radius: 16px;
        }

        body {
            background: radial-gradient(circle at 10% 20%, rgba(155, 0, 144, 0.15) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, rgba(11, 92, 173, 0.15) 0%, transparent 40%),
                        linear-gradient(135deg, #0B0F19 0%, #151D2A 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            padding: 2.5rem 1.5rem;
            color: var(--text-primary);
        }

        .signup-wrapper {
            display: flex;
            align-items: stretch;
            gap: 2rem;
            max-width: 1080px;
            width: 100%;
        }

        .signup-container {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), 0 0 30px rgba(155, 0, 144, 0.12);
            padding: 2.5rem;
            width: 100%;
            max-width: 540px;
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .signup-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--brand-gradient);
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
            padding: 4px 14px;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .signup-container h2 {
            color: var(--text-primary);
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }

        .info-panel {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2.5rem;
            width: 100%;
            max-width: 460px;
            border: 1px solid var(--border-color);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }

        .info-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #0B5CAD 0%, #9B0090 100%);
        }

        .info-panel h2 {
            color: var(--text-primary);
            font-size: 1.45rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .info-panel .corp-sub {
            color: var(--text-secondary);
            font-size: 0.85rem;
            margin-bottom: 2rem;
            display: block;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 1.1rem;
            margin-bottom: 1.6rem;
            color: var(--text-primary);
        }

        .info-icon {
            background: rgba(155, 0, 144, 0.15);
            border: 1px solid rgba(155, 0, 144, 0.3);
            color: #f5d0f2;
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .info-item:nth-child(2) .info-icon {
            background: rgba(11, 92, 173, 0.15);
            border-color: rgba(11, 92, 173, 0.3);
            color: #7dd3fc;
        }

        .info-item:nth-child(3) .info-icon {
            background: rgba(155, 0, 144, 0.15);
            border-color: rgba(155, 0, 144, 0.3);
            color: #f5d0f2;
        }

        .info-item:nth-child(4) .info-icon {
            background: rgba(11, 92, 173, 0.15);
            border-color: rgba(11, 92, 173, 0.3);
            color: #7dd3fc;
        }

        .info-content h3 {
            color: var(--text-primary);
            font-size: 1.05rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .info-content p {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin: 0;
            line-height: 1.45;
        }

        .form-floating {
            margin-bottom: 1.15rem;
        }

        .form-floating label {
            color: var(--text-secondary);
            padding: 1rem 0.85rem;
        }

        .form-floating>.form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1rem 0.85rem;
            color: #ffffff;
            height: calc(3.4rem + 2px);
            transition: all 0.3s ease;
        }

        .form-floating>.form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--brand-magenta);
            box-shadow: 0 0 0 0.2rem rgba(155, 0, 144, 0.25);
            color: #ffffff;
        }

        .form-floating>.form-control::placeholder {
            color: var(--text-secondary);
        }

        .form-floating>.form-control:focus~label,
        .form-floating>.form-control:not(:placeholder-shown)~label {
            color: #f5d0f2;
            transform: scale(0.85) translateY(-0.55rem) translateX(0.15rem);
        }

        .btn-signup {
            background: var(--brand-gradient);
            border: none;
            border-radius: 10px;
            padding: 0.85rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            color: white;
            width: 100%;
            box-shadow: 0 4px 18px rgba(155, 0, 144, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(155, 0, 144, 0.5);
            background: var(--brand-gradient-hover);
            color: white;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-secondary);
            transition: all 0.3s ease;
            z-index: 10;
        }

        .password-toggle:hover {
            color: var(--brand-magenta);
        }

        .text-center a {
            color: #38bdf8;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .text-center a:hover {
            color: #7dd3fc;
            text-decoration: underline;
        }

        @media (max-width: 992px) {
            .signup-wrapper {
                flex-direction: column;
                align-items: center;
            }

            .info-panel,
            .signup-container {
                max-width: 100%;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 1.25rem;
            }

            .signup-container,
            .info-panel {
                padding: 1.75rem 1.25rem;
            }
        }
    </style>
</head>

<body>
    <div class="signup-wrapper">
        <div class="signup-container">
            <div class="text-center mb-3">
                <img src="../../landing/landing_assets/images/liyas_logo_white.png" alt="Liya's Furniture & Electronics" style="max-height: 48px; width: auto; object-fit: contain;">
            </div>
            <div class="text-center mb-2">
                <span class="role-badge"><i class="fas fa-user-plus"></i> Customer Registration</span>
            </div>
            <div class="text-center mb-4">
                <h2>Create Account</h2>
                <p class="text-muted" style="color: var(--text-secondary) !important; font-size: 0.9rem;">Join Liya's Furniture & Electronics Savings Club</p>
            </div>

            <form id="signupForm" action="process_signup.php" method="POST" enctype="multipart/form-data">
                <div class="form-floating">
                    <input type="text" class="form-control" id="fullName" name="fullName" placeholder="Full Name" required>
                    <label for="fullName">Full Name</label>
                </div>

                <div class="form-floating">
                    <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" placeholder="Phone Number" required>
                    <label for="phoneNumber">Mobile Number (10 Digits)</label>
                </div>

                <div class="form-floating position-relative">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">Create Password</label>
                    <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
                </div>

                <div class="form-floating position-relative">
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required>
                    <label for="confirmPassword">Confirm Password</label>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-signup">
                        <i class="fas fa-arrow-right"></i> Register & Start Saving
                    </button>
                </div>

                <div class="text-center mt-3">
                    <p class="mb-1" style="color: var(--text-secondary); font-size: 0.9rem;">Already have an account? <a href="../login/">Login here</a></p>
                    <p class="mb-0" style="font-size: 0.85rem;"><a href="../../landing/index.php" style="color: var(--text-secondary);"><i class="fas fa-arrow-left"></i> Return to Homepage</a></p>
                </div>
            </form>
        </div>

        <div class="info-panel">
            <h2>Why Choose Liya's?</h2>
            <span class="corp-sub">A Unit of Pro Gee Dee Ventures Pvt. Ltd.</span>

            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-couch"></i>
                </div>
                <div class="info-content">
                    <h3>Premium Furniture & Gadgets</h3>
                    <p>Redeem your savings for luxury home furniture, 4K TVs, refrigerators, and electronics with special member discounts.</p>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <div class="info-content">
                    <h3>100% Safe & Transparent</h3>
                    <p>Every rupee saved is tracked in real time through your customer portal with instant automated digital receipts.</p>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-gift"></i>
                </div>
                <div class="info-content">
                    <h3>Exciting Prizes & Lucky Draws</h3>
                    <p>Participate in periodic lucky draws to win gold tokens, LED smart TVs, premium appliances, and bumper mega-prizes.</p>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <div class="info-content">
                    <h3>Dedicated Member Support</h3>
                    <p>Our prompt customer support team and doorstep assistance ensure a seamless, worry-free savings journey.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const toggleIcon = document.querySelector('.password-toggle');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                confirmPasswordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                confirmPasswordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>

</html>