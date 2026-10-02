<?php 
include("../admin/components/loader.php");
require_once("../config/config.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Liya's Furniture & Electronics | Pro Gee Dee Ventures</title>
    
    <link rel="icon" type="image/png" href="./landing_assets/images/liyas_logo.png">
    <link rel="shortcut icon" type="image/png" href="./landing_assets/images/liyas_logo.png">
    <link rel="apple-touch-icon" href="./landing_assets/images/liyas_logo.png">
    
    <meta name="description" content="Discover the story and mission of Liya's Furniture & Electronics (A Unit of Pro Gee Dee Ventures Pvt. Ltd.). Making luxury home living affordable through smart monthly savings.">
    <meta name="keywords" content="about Liyas furniture, furniture savings scheme, electronics monthly installments, Pro Gee Dee Ventures">
    <meta name="author" content="Liya's Furniture & Electronics">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --brand-magenta: #9B0090;
            --brand-magenta-dark: #7b0073;
            --brand-magenta-light: #fae8f9;
            --brand-blue: #0B5CAD;
            --brand-blue-dark: #084887;
            --brand-gradient: linear-gradient(135deg, #9B0090 0%, #0B5CAD 100%);
            --dark-bg: #0B0F19;
            --light-bg: #F8FAFC;
            --text-heading: #0F172A;
            --text-body: #475569;
            --text-muted: #94A3B8;
            --border-subtle: #E2E8F0;
            --transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #ffffff;
            color: var(--text-body);
            line-height: 1.65;
            overflow-x: hidden;
        }

        h1, h2, h3, h4 {
            color: var(--text-heading);
            font-weight: 700;
        }

        .text-gradient {
            background: var(--brand-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-brand-primary {
            background: var(--brand-gradient);
            color: #ffffff !important;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            box-shadow: 0 8px 24px rgba(155, 0, 144, 0.3);
            transition: var(--transition);
        }

        .btn-brand-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(155, 0, 144, 0.45);
        }

        /* Hero */
        .about-hero {
            padding: 90px 0 80px;
            background: linear-gradient(135deg, rgba(155, 0, 144, 0.04) 0%, rgba(11, 92, 173, 0.04) 100%);
            border-bottom: 1px solid var(--border-subtle);
        }

        .hero-badge-about {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(155, 0, 144, 0.1);
            color: var(--brand-magenta);
            border: 1px solid rgba(155, 0, 144, 0.25);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 18px;
        }

        .about-hero h1 {
            font-size: 3rem;
            line-height: 1.2;
            margin-bottom: 20px;
        }

        .feature-box {
            background: #ffffff;
            border: 1px solid var(--border-subtle);
            border-radius: 16px;
            padding: 32px 26px;
            transition: var(--transition);
            height: 100%;
        }

        .feature-box:hover {
            transform: translateY(-6px);
            border-color: var(--brand-magenta);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        }

        .feature-icon-wrapper {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(155, 0, 144, 0.1) 0%, rgba(11, 92, 173, 0.1) 100%);
            color: var(--brand-magenta);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 20px;
        }

        /* 3-Step Section */
        .step-block {
            display: flex;
            gap: 20px;
            margin-bottom: 24px;
        }

        .step-num-badge {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--brand-gradient);
            color: #ffffff;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(155, 0, 144, 0.3);
        }

        /* MD Section */
        .md-card {
            background: #ffffff;
            border: 1px solid var(--border-subtle);
            border-radius: 24px;
            padding: 50px 40px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
        }

        .md-quote {
            font-size: 1.4rem;
            font-weight: 500;
            color: #1E293B;
            line-height: 1.6;
            margin-bottom: 24px;
            position: relative;
        }

        /* FAQ */
        .faq-item {
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            margin-bottom: 14px;
            overflow: hidden;
            background: #ffffff;
            transition: var(--transition);
        }

        .faq-question {
            padding: 18px 24px;
            font-weight: 600;
            color: var(--text-heading);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-question i {
            color: var(--brand-magenta);
            transition: transform 0.3s ease;
        }

        .faq-answer {
            display: none;
            padding: 0 24px 18px;
            color: var(--text-body);
            font-size: 0.95rem;
            border-top: 1px solid #f1f5f9;
            padding-top: 14px;
        }

        .faq-item.active .faq-answer {
            display: block;
        }

        .faq-item.active .faq-question i {
            transform: rotate(180deg);
        }
    </style>
</head>

<body>

    <!-- Header Navigation -->
    <?php include '../components/navbar.php'; ?>

    <!-- About Hero -->
    <section class="about-hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="hero-badge-about">
                        <i class="fas fa-gem"></i> A Unit of Pro Gee Dee Ventures Pvt. Ltd.
                    </div>
                    <h1>
                        Empowering Homes with <br>
                        <span class="text-gradient">Smart Living & Savings.</span>
                    </h1>
                    <p class="lead mb-4" style="color: #475569;">
                        Liya's Furniture & Electronics was founded with a singular vision: to make high-end home furnishing and the latest electronic appliances accessible to every family through structured, reward-packed monthly savings schemes.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="./#schemes" class="btn-brand-primary">
                            <span>Explore Schemes</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="javascript:void(0)" onclick="openLoginModal()" class="btn btn-outline-dark px-4 py-2 fw-semibold" style="border-radius:8px;">
                            Customer Login
                        </a>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div style="border-radius: 24px; overflow: hidden; box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12);">
                        <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=1200&auto=format&fit=crop" alt="Liya's Furniture Showcase" class="img-fluid w-100" style="height: 380px; object-fit: cover;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Liya's -->
    <section class="py-5" style="background: #ffffff;">
        <div class="container py-4">
            <div class="text-center max-w-700 mx-auto mb-5">
                <span class="text-uppercase fw-bold" style="color: var(--brand-magenta); letter-spacing: 1px; font-size: 0.85rem;">Why Choose Us</span>
                <h2 class="mt-2 mb-3">Redefining Home Ownership</h2>
                <p class="text-muted">A customer-first approach that turns monthly pocket savings into lifelong comfort and pride.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon-wrapper"><i class="fas fa-shield-alt"></i></div>
                        <h4>100% Value Guarantee</h4>
                        <p class="text-muted mb-0">Every single rupee you contribute is completely safeguarded and directly convertible into your favorite furniture or electronics.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon-wrapper"><i class="fas fa-trophy"></i></div>
                        <h4>Monthly Lucky Draws</h4>
                        <p class="text-muted mb-0">Active scheme subscribers enjoy monthly bumper draws with chances to win smart appliances, gold coins, and bonus products.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon-wrapper"><i class="fas fa-couch"></i></div>
                        <h4>Designer Craftsmanship</h4>
                        <p class="text-muted mb-0">From premium solid teakwood sofas to 4K Smart TVs, we partner with top manufacturers for uncompromising quality and longevity.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3 Easy Steps -->
    <section class="py-5" style="background: var(--light-bg); border-top: 1px solid var(--border-subtle); border-bottom: 1px solid var(--border-subtle);">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="text-uppercase fw-bold" style="color: var(--brand-magenta); font-size: 0.85rem; letter-spacing: 1px;">Getting Started</span>
                    <h2 class="mt-2 mb-4">Start Saving in 3 Simple Steps</h2>

                    <div class="step-block">
                        <div class="step-num-badge">1</div>
                        <div>
                            <h5 class="mb-1">Select Your Scheme</h5>
                            <p class="text-muted mb-0">Monthly installment plan of ₹1000 based on your home upgrade timeline.</p>
                        </div>
                    </div>

                    <div class="step-block">
                        <div class="step-num-badge">2</div>
                        <div>
                            <h5 class="mb-1">Pay Monthly & Track Progress</h5>
                            <p class="text-muted mb-0">Use our seamless online customer portal or visit our showroom to make monthly contributions and view draw status.</p>
                        </div>
                    </div>

                    <div class="step-block">
                        <div class="step-num-badge">3</div>
                        <div>
                            <h5 class="mb-1">Win Exciting Prizes</h5>
                            <p class="text-muted mb-0">Active members get automatic entries into monthly draws to win premium furniture, electronics, and exclusive rewards.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 text-center">
                    <img src="./landing_assets/images/register.png" alt="Register with Liya's" class="img-fluid" style="max-height: 380px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.06);">
                </div>
            </div>
        </div>
    </section>


    <!-- Frequently Asked Questions -->
    <section class="py-5" style="background: var(--light-bg); border-top: 1px solid var(--border-subtle);">
        <div class="container py-4">
            <div class="text-center max-w-700 mx-auto mb-5">
                <span class="text-uppercase fw-bold" style="color: var(--brand-magenta); font-size: 0.85rem; letter-spacing: 1px;">Help & Support</span>
                <h2 class="mt-2 mb-3">Frequently Asked Questions</h2>
                <p class="text-muted">Common questions about Liya's Furniture & Electronics monthly schemes and redemptions.</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    
                    <div class="faq-item active">
                        <div class="faq-question">
                            <span>What is Liya's Furniture & Electronics savings scheme?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            It is a structured monthly savings plan where members contribute a fixed sum each month ₹1000 At maturity or upon winning monthly draws, 100% of your accumulated contributions are redeemable for luxury furniture, modular interiors, or smart home appliances from our showroom.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span>How do the monthly lucky draws work?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Active scheme members who have paid their monthly installments on time receive draw entries. In each monthly draw, lucky winners receive exciting bonus appliances, electronics, or prize items as part of the scheme rewards!
                        </div>
                    </div>

                    <div class="faq-item">
                 
                        <div class="faq-answer">
                            Yes! You have complete freedom to select from our comprehensive showroom collection, including sofa sets, dining tables, bedroom suites, 4K Smart TVs, refrigerators, washing machines, and kitchen appliances.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span>How can I make monthly payments?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            You can easily pay via UPI, Google Pay, PhonePe, Net Banking, or Credit/Debit card through your online customer dashboard, or pay in person at our customer service centers.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Is there any hidden deduction or interest fee?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            No. There are absolutely zero registration charges, zero interest fees, and no hidden deductions. 100% of your deposited money goes towards your selected products.
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>


    <!-- Footer Component -->
    <?php include '../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // FAQ toggle
        document.querySelectorAll('.faq-question').forEach(q => {
            q.addEventListener('click', () => {
                const item = q.parentElement;
                item.classList.toggle('active');
            });
        });

        // Modal
        function openLoginModal() {
            const m = document.getElementById('loginModal');
            if (m) {
                m.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }
        function closeLoginModal() {
            const m = document.getElementById('loginModal');
            if (m) {
                m.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
    </script>

</body>
</html>