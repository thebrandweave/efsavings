<?php
// Liya's Furniture & Electronics - Flagship Landing Page
// A Unit of Pro Gee Dee Ventures Pvt. Ltd.
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liya's Furniture & Electronics | Smart Monthly Savings Scheme</title>
    
    <!-- Favicon & Touch Icons -->
    <link rel="icon" type="image/png" href="./landing_assets/images/liyas_logo.png">
    <link rel="shortcut icon" type="image/png" href="./landing_assets/images/liyas_logo.png">
    <link rel="apple-touch-icon" href="./landing_assets/images/liyas_logo.png">

    <!-- SEO Meta Tags -->
    <meta name="description" content="Liya's Furniture & Electronics (A Unit of Pro Gee Dee Ventures Pvt. Ltd.) - Smart monthly savings schemes, luxury furniture, modular interiors, and cutting-edge home electronics.">
    <meta name="keywords" content="furniture savings scheme, electronics monthly plan, Liyas furniture, smart TV savings, refrigerator installments, sofa set savings, Pro Gee Dee Ventures, Bantwal, Karnataka">
    <meta name="author" content="Liya's Furniture & Electronics">
    <meta name="robots" content="index, follow">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Liya's Furniture & Electronics - Smart Living & Monthly Savings">
    <meta property="og:description" content="Upgrade your home with luxury furniture and smart electronics through easy monthly savings schemes and lucky draws.">
    <meta property="og:type" content="website">
    <meta property="og:image" content="./landing_assets/images/liyas_logo.png">
    <meta property="og:site_name" content="Liya's Furniture & Electronics">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & FontAwesome 6 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FurnitureStore",
        "name": "Liya's Furniture & Electronics",
        "description": "Smart monthly savings schemes for luxury furniture, home decor, and cutting-edge appliances.",
        "parentOrganization": {
            "@type": "Organization",
            "name": "Pro Gee Dee Ventures Pvt. Ltd."
        },
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "Ground Floor, Sri Mantame Complex, Near Soorya Infotech Park, Kurnadu Post, Mudipu Road",
            "addressLocality": "Bantwal",
            "addressRegion": "Karnataka",
            "postalCode": "574153",
            "addressCountry": "IN"
        },
        "telephone": "+91-99951-94472",
        "email": "info@efsavings.in"
    }
    </script>

    <style>
        /* ========================================================
           GLOBAL DESIGN SYSTEM - LIYA'S FURNITURE & ELECTRONICS
           Colors: Primary Magenta (#9B0090), Cobalt Blue (#0B5CAD)
           ======================================================== */
        :root {
            --brand-magenta: #9B0090;
            --brand-magenta-dark: #7b0073;
            --brand-magenta-light: #fae8f9;
            --brand-blue: #0B5CAD;
            --brand-blue-dark: #084887;
            --brand-blue-light: #e0f2fe;
            --brand-gradient: linear-gradient(135deg, #9B0090 0%, #0B5CAD 100%);
            --brand-gradient-hover: linear-gradient(135deg, #b300a7 0%, #0d6ecc 100%);
            --brand-gradient-soft: linear-gradient(135deg, rgba(155, 0, 144, 0.08) 0%, rgba(11, 92, 173, 0.08) 100%);
            
            --dark-bg: #0B0F19;
            --dark-surface: #111827;
            --dark-card: #1E293B;
            --light-bg: #F8FAFC;
            --light-surface: #FFFFFF;
            
            --text-heading: #0F172A;
            --text-body: #475569;
            --text-muted: #94A3B8;
            --border-subtle: #E2E8F0;
            
            --shadow-sm: 0 2px 6px rgba(15, 23, 42, 0.04);
            --shadow-md: 0 8px 24px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 16px 36px rgba(15, 23, 42, 0.12);
            --shadow-brand: 0 10px 28px rgba(155, 0, 144, 0.28);
            
            --radius-sm: 8px;
            --radius-md: 14px;
            --radius-lg: 20px;
            --radius-xl: 28px;
            
            --transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, sans-serif;
            background-color: var(--light-bg);
            color: var(--text-body);
            line-height: 1.65;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-heading);
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        a {
            text-decoration: none;
            transition: var(--transition);
        }

        /* Gradient Text Helper */
        .text-gradient {
            background: var(--brand-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .text-magenta {
            color: var(--brand-magenta) !important;
        }

        .text-blue {
            color: var(--brand-blue) !important;
        }

        /* Buttons */
        .btn-brand-primary {
            background: var(--brand-gradient);
            color: #ffffff !important;
            padding: 12px 28px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: var(--shadow-brand);
            transition: var(--transition);
            cursor: pointer;
        }

        .btn-brand-primary:hover {
            background: var(--brand-gradient-hover);
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(155, 0, 144, 0.4);
            color: #ffffff !important;
        }

        .btn-brand-outline {
            background: transparent;
            color: var(--brand-magenta) !important;
            padding: 11px 26px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            border: 2px solid var(--brand-magenta);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
        }

        .btn-brand-outline:hover {
            background: var(--brand-magenta);
            color: #ffffff !important;
            transform: translateY(-2px);
        }

        .btn-brand-white {
            background: #ffffff;
            color: var(--brand-magenta) !important;
            padding: 12px 28px;
            border-radius: var(--radius-sm);
            font-weight: 700;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .btn-brand-white:hover {
            background: #F8FAFC;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        /* Badges */
        .badge-brand {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--brand-gradient-soft);
            color: var(--brand-magenta);
            border: 1px solid rgba(155, 0, 144, 0.2);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* ========================================================
           HERO SECTION
           ======================================================== */
        .hero-section {
            position: relative;
            background: var(--dark-bg);
            color: #ffffff;
            overflow: hidden;
        }

        .hero-carousel {
            position: relative;
        }

        .hero-slide {
            min-height: 85vh;
            display: flex;
            align-items: center;
            padding: 100px 0;
            position: relative;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .hero-slide::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, rgba(11, 15, 25, 0.95) 0%, rgba(11, 15, 25, 0.8) 50%, rgba(11, 15, 25, 0.5) 100%);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 680px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(155, 0, 144, 0.2);
            border: 1px solid rgba(155, 0, 144, 0.4);
            color: #f5d0f2;
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 24px;
            backdrop-filter: blur(8px);
        }

        .hero-title {
            font-size: 3.5rem;
            line-height: 1.15;
            color: #ffffff;
            margin-bottom: 22px;
            font-weight: 800;
        }

        .hero-description {
            font-size: 1.15rem;
            color: #CBD5E1;
            margin-bottom: 34px;
            line-height: 1.7;
        }

        .hero-actions {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: center;
        }

        .hero-btn-outline {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #ffffff !important;
            padding: 12px 26px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            backdrop-filter: blur(8px);
            transition: var(--transition);
        }

        .hero-btn-outline:hover {
            background: rgba(255, 255, 255, 0.18);
            border-color: #ffffff;
            transform: translateY(-2px);
        }

        /* Carousel Navigation */
        .carousel-indicators [data-bs-target] {
            width: 32px;
            height: 4px;
            border-radius: 2px;
            background-color: rgba(255, 255, 255, 0.4);
            border: none;
            transition: var(--transition);
        }

        .carousel-indicators .active {
            width: 48px;
            background: var(--brand-gradient);
        }

        /* ========================================================
           STATS / QUICK TRUST BAR
           ======================================================== */
        .trust-bar {
            background: #ffffff;
            border-bottom: 1px solid var(--border-subtle);
            padding: 30px 0;
            box-shadow: var(--shadow-sm);
            position: relative;
            z-index: 10;
        }

        .trust-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 10px 15px;
        }

        .trust-icon {
            width: 52px;
            height: 52px;
            border-radius: var(--radius-md);
            background: var(--brand-gradient-soft);
            color: var(--brand-magenta);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
            transition: var(--transition);
        }

        .trust-item:hover .trust-icon {
            background: var(--brand-gradient);
            color: #ffffff;
            transform: scale(1.08) rotate(4deg);
        }

        .trust-text h4 {
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .trust-text p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin: 0;
        }

        /* ========================================================
           SAVINGS SCHEMES SECTION
           ======================================================== */
        .schemes-section {
            padding: 90px 0;
            background: #ffffff;
            position: relative;
        }

        .section-header {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 55px;
        }

        .section-header .section-tag {
            display: inline-block;
            color: var(--brand-magenta);
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 8px;
        }

        .section-header h2 {
            font-size: 2.5rem;
            margin-bottom: 16px;
        }

        .section-header p {
            color: var(--text-body);
            font-size: 1.05rem;
        }

        .scheme-card {
            background: #ffffff;
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            padding: 34px 28px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .scheme-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(155, 0, 144, 0.3);
        }

        .scheme-card.popular {
            border: 2px solid var(--brand-magenta);
            box-shadow: var(--shadow-brand);
        }

        .popular-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            background: var(--brand-gradient);
            color: #ffffff;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .scheme-header-box {
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-subtle);
        }

        .scheme-name {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .scheme-price {
            font-size: 2.4rem;
            font-weight: 800;
            color: var(--brand-magenta);
            line-height: 1;
        }

        .scheme-price span {
            font-size: 0.95rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .scheme-features {
            list-style: none;
            padding: 0;
            margin: 0 0 28px 0;
            flex-grow: 1;
        }

        .scheme-features li {
            font-size: 0.92rem;
            color: var(--text-body);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .scheme-features li i {
            color: var(--brand-blue);
            font-size: 0.9rem;
        }

        /* ========================================================
           CATEGORIES / BENTO SHOWCASE
           ======================================================== */
        .categories-section {
            padding: 90px 0;
            background: var(--light-bg);
        }

        .bento-grid-custom {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 24px;
        }

        .bento-box {
            position: relative;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            min-height: 280px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 30px;
            color: #ffffff;
            background-size: cover;
            background-position: center;
            text-decoration: none;
        }

        .bento-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, rgba(11, 15, 25, 0.1) 0%, rgba(11, 15, 25, 0.85) 100%);
            transition: var(--transition);
            z-index: 1;
        }

        .bento-box:hover::before {
            background: linear-gradient(180deg, rgba(155, 0, 144, 0.2) 0%, rgba(11, 15, 25, 0.92) 100%);
        }

        .bento-box:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
        }

        .bento-content {
            position: relative;
            z-index: 2;
        }

        .bento-tag {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #38bdf8;
            margin-bottom: 6px;
        }

        .bento-title {
            color: #ffffff;
            font-size: 1.45rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .bento-desc {
            font-size: 0.88rem;
            color: #CBD5E1;
            margin-bottom: 12px;
            line-height: 1.5;
        }

        .bento-link {
            font-size: 0.85rem;
            font-weight: 600;
            color: #f5d0f2;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .bento-link i {
            transition: transform 0.25s ease;
        }

        .bento-box:hover .bento-link i {
            transform: translateX(4px);
        }

        .col-span-8 { grid-column: span 8; }
        .col-span-4 { grid-column: span 4; }
        .col-span-6 { grid-column: span 6; }

        @media (max-width: 991px) {
            .col-span-8, .col-span-4, .col-span-6 {
                grid-column: span 12;
            }
        }

        /* ========================================================
           HOW IT WORKS SECTION
           ======================================================== */
        .how-section {
            padding: 90px 0;
            background: #ffffff;
        }

        .step-card {
            background: #ffffff;
            border-radius: var(--radius-lg);
            padding: 40px 30px;
            border: 1px solid var(--border-subtle);
            text-align: center;
            transition: var(--transition);
            position: relative;
            height: 100%;
        }

        .step-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-md);
            border-color: var(--brand-magenta);
        }

        .step-number {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--brand-gradient);
            color: #ffffff;
            font-size: 1.4rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: var(--shadow-brand);
        }

        .step-card h3 {
            font-size: 1.25rem;
            margin-bottom: 12px;
        }

        .step-card p {
            color: var(--text-body);
            font-size: 0.93rem;
            margin: 0;
        }

        /* ========================================================
           SAVINGS CALCULATOR TEASER
           ======================================================== */
        .calc-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #0B0F19 0%, #171E2E 100%);
            color: #ffffff;
            position: relative;
        }

        .calc-box {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-xl);
            padding: 44px;
            backdrop-filter: blur(12px);
        }

        .calc-slider-box {
            margin-bottom: 30px;
        }

        .calc-slider-box label {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            font-size: 1.05rem;
            margin-bottom: 12px;
            color: #CBD5E1;
        }

        .calc-range {
            width: 100%;
            height: 8px;
            border-radius: 4px;
            background: #334155;
            outline: none;
            accent-color: var(--brand-magenta);
        }

        .calc-result-card {
            background: var(--brand-gradient);
            border-radius: var(--radius-lg);
            padding: 36px 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(155, 0, 144, 0.4);
        }

        .calc-result-val {
            font-size: 2.8rem;
            font-weight: 800;
            line-height: 1;
            margin: 10px 0 16px;
        }

        /* ========================================================
           CONTINUOUS SMOOTH GALLERY
           ======================================================== */
        .gallery-section {
            padding: 85px 0;
            background: var(--light-bg);
            overflow: hidden;
        }

        .gallery-track {
            display: flex;
            gap: 20px;
            animation: scrollGallery 35s linear infinite;
            width: max-content;
        }

        .gallery-track:hover {
            animation-play-state: paused;
        }

        @keyframes scrollGallery {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        .gallery-card {
            width: 320px;
            height: 220px;
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            flex-shrink: 0;
            position: relative;
        }

        .gallery-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .gallery-card:hover img {
            transform: scale(1.08);
        }

        /* ========================================================
           VENTURES ECOSYSTEM
           ======================================================== */
        .ecosystem-section {
            padding: 80px 0;
            background: #ffffff;
            border-top: 1px solid var(--border-subtle);
        }

        .ecosystem-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 36px;
            margin-top: 40px;
        }

        .ecosystem-item {
            opacity: 0.75;
            filter: grayscale(1);
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border-radius: var(--radius-sm);
        }

        .ecosystem-item:hover {
            opacity: 1;
            filter: grayscale(0);
            transform: translateY(-3px);
            background: var(--light-bg);
        }

        .ecosystem-item img {
            height: 42px;
            width: auto;
            object-fit: contain;
        }

        /* ========================================================
           CTA BANNER
           ======================================================== */
        .cta-section {
            padding: 80px 0;
            background: var(--light-bg);
        }

        .cta-box {
            background: var(--brand-gradient);
            border-radius: var(--radius-xl);
            padding: 60px 50px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-brand);
        }

        .cta-box::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
        }

        .cta-box h2 {
            color: #ffffff;
            font-size: 2.6rem;
            margin-bottom: 16px;
        }

        .cta-box p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.15rem;
            max-width: 600px;
            margin-bottom: 32px;
        }

        /* ========================================================
           LOGIN MODAL
           ======================================================== */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(11, 15, 25, 0.75);
            backdrop-filter: blur(6px);
            z-index: 2000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 1;
        }

        .login-modal {
            background: #ffffff;
            padding: 36px;
            border-radius: var(--radius-lg);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 440px;
            position: relative;
            transform: scale(0.95);
            transition: var(--transition);
        }

        .modal-overlay.active .login-modal {
            transform: scale(1);
        }

        .modal-header-custom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-subtle);
        }

        .modal-header-custom h3 {
            font-size: 1.3rem;
            margin: 0;
        }

        .close-modal-btn {
            background: none;
            border: none;
            font-size: 1.3rem;
            color: var(--text-muted);
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .close-modal-btn:hover {
            color: var(--brand-magenta);
        }

        .login-options-grid {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .login-choice-card {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-subtle);
            color: var(--text-heading);
            background: #ffffff;
            transition: var(--transition);
        }

        .login-choice-card:hover {
            border-color: var(--brand-magenta);
            background: var(--brand-magenta-light);
            transform: translateX(4px);
        }

        .login-choice-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: var(--brand-gradient-soft);
            color: var(--brand-magenta);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .login-choice-card:hover .login-choice-icon {
            background: var(--brand-gradient);
            color: #ffffff;
        }

        .login-choice-info h4 {
            font-size: 1rem;
            margin: 0 0 2px 0;
        }

        .login-choice-info p {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin: 0;
        }

        /* Responsive Breakpoints */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.3rem;
            }
            .hero-description {
                font-size: 1rem;
            }
            .section-header h2 {
                font-size: 2rem;
            }
            .cta-box {
                padding: 40px 24px;
            }
            .cta-box h2 {
                font-size: 1.8rem;
            }
            .calc-box {
                padding: 24px;
            }
        }
    </style>
</head>

<body>

    <!-- Header Navigation -->
    <?php include '../components/navbar.php'; ?>

    <!-- Main Content -->
    <main>

        <!-- ========================================================
             HERO SECTION (CAROUSEL)
             ======================================================== -->
        <section class="hero-section">
            <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="4500">
                
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
                </div>

                <div class="carousel-inner">
                    
                    <!-- Slide 1: Brand Flagship -->
                    <div class="carousel-item active">
                        <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=1600&auto=format&fit=crop');">
                            <div class="container">
                                <div class="hero-content">
                                    <div class="hero-badge">
                                        <i class="fas fa-certificate"></i> A Unit of Pro Gee Dee Ventures Pvt. Ltd.
                                    </div>
                                    <h1 class="hero-title">
                                        Modern Living <br>
                                        <span class="text-gradient">Smart Savings.</span>
                                    </h1>
                                    <p class="hero-description">
                                        Furnish your dream home with premium luxury furniture and cutting-edge home electronics. Save flexibly every month with guaranteed redemption and exciting bumper draws!
                                    </p>
                                    <div class="hero-actions">
                                        <a href="#schemes" class="btn-brand-primary">
                                            <span>Explore Schemes</span>
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                        <a href="#categories" class="hero-btn-outline">
                                            <i class="fas fa-couch"></i>
                                            <span>View Collections</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 2: Designer Furniture -->
                    <div class="carousel-item">
                        <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?q=80&w=1600&auto=format&fit=crop');">
                            <div class="container">
                                <div class="hero-content">
                                    <div class="hero-badge">
                                        <i class="fas fa-couch"></i> Designer Living & Dining
                                    </div>
                                    <h1 class="hero-title">
                                        Luxury Furniture <br>
                                        <span class="text-gradient">Crafted For Life.</span>
                                    </h1>
                                    <p class="hero-description">
                                        From bespoke solid-wood sofas and ergonomic bedroom sets to modular dining tables, bring timeless comfort and architectural elegance into your home.
                                    </p>
                                    <div class="hero-actions">
                                        <a href="#categories" class="btn-brand-primary">
                                            <span>Browse Furniture</span>
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                        <a href="#calculator" class="hero-btn-outline">
                                            <i class="fas fa-calculator"></i>
                                            <span>Calculate Savings</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 3: Smart Electronics & Appliances -->
                    <div class="carousel-item">
                        <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1593784991095-a205069470b6?q=80&w=1600&auto=format&fit=crop');">
                            <div class="container">
                                <div class="hero-content">
                                    <div class="hero-badge">
                                        <i class="fas fa-tv"></i> High-End Electronics
                                    </div>
                                    <h1 class="hero-title">
                                        Smart Electronics <br>
                                        <span class="text-gradient">Next-Gen Comfort.</span>
                                    </h1>
                                    <p class="hero-description">
                                        Upgrade to 4K Ultra HD Smart TVs, energy-efficient inverter refrigerators, smart washing machines, and modern kitchen tech from top global brands.
                                    </p>
                                    <div class="hero-actions">
                                        <a href="#schemes" class="btn-brand-primary">
                                            <span>Join Electronics Scheme</span>
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                        <a href="javascript:void(0)" onclick="openLoginModal()" class="hero-btn-outline">
                                            <i class="fas fa-user-lock"></i>
                                            <span>Customer Portal</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 4: Monthly Draw Rewards -->
                    <div class="carousel-item">
                        <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1513151233558-d860c5398176?q=80&w=1600&auto=format&fit=crop');">
                            <div class="container">
                                <div class="hero-content">
                                    <div class="hero-badge">
                                        <i class="fas fa-trophy"></i> Monthly Bumper Draws
                                    </div>
                                    <h1 class="hero-title">
                                        Save Monthly & <br>
                                        <span class="text-gradient">Win Bumper Prizes.</span>
                                    </h1>
                                    <p class="hero-description">
                                        Every month, active scheme members participate in exciting lucky draws with opportunities to win luxury appliances, gold coins, and surprise bumper awards!
                                    </p>
                                    <div class="hero-actions">
                                        <a href="#schemes" class="btn-brand-primary">
                                            <span>Enroll Now</span>
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                        <a href="./about.php" class="hero-btn-outline">
                                            <i class="fas fa-info-circle"></i>
                                            <span>Learn How It Works</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </section>

        <!-- ========================================================
             TRUST & STATS BAR
             ======================================================== -->
        <section class="trust-bar">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-3 col-sm-6">
                        <div class="trust-item">
                            <div class="trust-icon"><i class="fas fa-wallet"></i></div>
                            <div class="trust-text">
                                <h4>From ₹500/Month</h4>
                                <p>Accessible & flexible schemes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="trust-item">
                            <div class="trust-icon"><i class="fas fa-gift"></i></div>
                            <div class="trust-text">
                                <h4>Monthly Lucky Draws</h4>
                                <p>Surprise prizes & rewards</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="trust-item">
                            <div class="trust-icon"><i class="fas fa-shield-alt"></i></div>
                            <div class="trust-text">
                                <h4>100% Guaranteed Value</h4>
                                <p>Zero interest, zero loss</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="trust-item">
                            <div class="trust-icon"><i class="fas fa-truck"></i></div>
                            <div class="trust-text">
                                <h4>Doorstep Delivery</h4>
                                <p>Safe delivery & warranty</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================================
             SCHEMES SHOWCASE SECTION
             ======================================================== -->
        <section class="schemes-section" id="schemes">
            <div class="container">
                <div class="section-header">
                    <span class="section-tag">Featured Savings Plans</span>
                    <h2>Choose Your Savings Scheme</h2>
                    <p>Select a plan that aligns with your home upgrade ambitions. Accumulate value month-by-month and enjoy guaranteed product settlement.</p>
                </div>

                <div class="row g-4 justify-content-center">
                    
                    <!-- Scheme 1: Silver -->
                    <div class="col-lg-3 col-md-6">
                        <div class="scheme-card">
                            <div class="scheme-header-box">
                                <h3 class="scheme-name">Silver Plan</h3>
                                <div class="scheme-price">₹500 <span>/ month</span></div>
                            </div>
                            <ul class="scheme-features">
                                <li><i class="fas fa-check-circle"></i> Small Kitchen Appliances</li>
                                <li><i class="fas fa-check-circle"></i> Soundbars & Speakers</li>
                                <li><i class="fas fa-check-circle"></i> Monthly Draw Entry</li>
                                <li><i class="fas fa-check-circle"></i> 100% Value Redemption</li>
                                <li><i class="fas fa-check-circle"></i> 12 / 24 Months Tenure</li>
                            </ul>
                            <button onclick="openLoginModal()" class="btn-brand-outline w-100 justify-content-center">
                                Join Plan
                            </button>
                        </div>
                    </div>

                    <!-- Scheme 2: Gold (Popular) -->
                    <div class="col-lg-3 col-md-6">
                        <div class="scheme-card popular">
                            <div class="popular-badge">Most Popular</div>
                            <div class="scheme-header-box">
                                <h3 class="scheme-name">Gold Plan</h3>
                                <div class="scheme-price">₹1,000 <span>/ month</span></div>
                            </div>
                            <ul class="scheme-features">
                                <li><i class="fas fa-check-circle"></i> 32" - 43" Smart LED TVs</li>
                                <li><i class="fas fa-check-circle"></i> Modern 3-Seater Sofas</li>
                                <li><i class="fas fa-check-circle"></i> Semi-Automatic Washers</li>
                                <li><i class="fas fa-check-circle"></i> 2x Monthly Draw Tickets</li>
                                <li><i class="fas fa-check-circle"></i> Priority Delivery</li>
                            </ul>
                            <button onclick="openLoginModal()" class="btn-brand-primary w-100 justify-content-center">
                                Join Plan
                            </button>
                        </div>
                    </div>

                    <!-- Scheme 3: Diamond -->
                    <div class="col-lg-3 col-md-6">
                        <div class="scheme-card">
                            <div class="scheme-header-box">
                                <h3 class="scheme-name">Diamond Plan</h3>
                                <div class="scheme-price">₹2,500 <span>/ month</span></div>
                            </div>
                            <ul class="scheme-features">
                                <li><i class="fas fa-check-circle"></i> 55" 4K Ultra HD Smart TVs</li>
                                <li><i class="fas fa-check-circle"></i> Double Door Refrigerators</li>
                                <li><i class="fas fa-check-circle"></i> Queen/King Bedroom Sets</li>
                                <li><i class="fas fa-check-circle"></i> 4x Monthly Draw Tickets</li>
                                <li><i class="fas fa-check-circle"></i> Free Installation & Support</li>
                            </ul>
                            <button onclick="openLoginModal()" class="btn-brand-outline w-100 justify-content-center">
                                Join Plan
                            </button>
                        </div>
                    </div>

                    <!-- Scheme 4: Platinum Executive -->
                    <div class="col-lg-3 col-md-6">
                        <div class="scheme-card">
                            <div class="scheme-header-box">
                                <h3 class="scheme-name">Platinum Plan</h3>
                                <div class="scheme-price">₹5,000 <span>/ month</span></div>
                            </div>
                            <ul class="scheme-features">
                                <li><i class="fas fa-check-circle"></i> Full Living Room Makeover</li>
                                <li><i class="fas fa-check-circle"></i> Side-by-Side Smart Fridges</li>
                                <li><i class="fas fa-check-circle"></i> Inverter ACs & Wash Towers</li>
                                <li><i class="fas fa-check-circle"></i> VIP Bumper Draw Access</li>
                                <li><i class="fas fa-check-circle"></i> Custom Woodwork Options</li>
                            </ul>
                            <button onclick="openLoginModal()" class="btn-brand-outline w-100 justify-content-center">
                                Join Plan
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ========================================================
             CATEGORIES / BENTO SHOWCASE
             ======================================================== -->
        <section class="categories-section" id="categories">
            <div class="container">
                <div class="section-header">
                    <span class="section-tag">Explore Collections</span>
                    <h2>Furniture & Electronics Catalogue</h2>
                    <p>Hand-selected premium furniture and top-tier consumer electronics curated to elevate every corner of your living space.</p>
                </div>

                <div class="bento-grid-custom">
                    
                    <!-- Bento 1: Living Room (8 col) -->
                    <div class="bento-box col-span-8" style="background-image: url('https://images.unsplash.com/photo-1586023492125-27b2c045efd7?q=80&w=1200&auto=format&fit=crop');">
                        <div class="bento-content">
                            <span class="bento-tag">Furniture Collection</span>
                            <h3 class="bento-title">Designer Living Rooms & Sofas</h3>
                            <p class="bento-desc">Sectional recliners, teakwood center tables, and luxurious fabric suites tailored for sophisticated family comfort.</p>
                            <span class="bento-link">Explore Living Room <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>

                    <!-- Bento 2: 4K Smart TVs (4 col) -->
                    <div class="bento-box col-span-4" style="background-image: url('https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?q=80&w=800&auto=format&fit=crop');">
                        <div class="bento-content">
                            <span class="bento-tag">Home Entertainment</span>
                            <h3 class="bento-title">Smart 4K UHD TVs</h3>
                            <p class="bento-desc">Cinematic displays and Dolby Atmos sound systems.</p>
                            <span class="bento-link">View TVs <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>

                    <!-- Bento 3: Kitchen Tech (4 col) -->
                    <div class="bento-box col-span-4" style="background-image: url('https://images.unsplash.com/photo-1556911220-e15b29be8c8f?q=80&w=800&auto=format&fit=crop');">
                        <div class="bento-content">
                            <span class="bento-tag">Kitchen Appliances</span>
                            <h3 class="bento-title">Modern Kitchen Tech</h3>
                            <p class="bento-desc">Smart refrigerators, microwaves, and modular kitchen accessories.</p>
                            <span class="bento-link">Browse Appliances <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>

                    <!-- Bento 4: Master Bedroom (4 col) -->
                    <div class="bento-box col-span-4" style="background-image: url('https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=800&auto=format&fit=crop');">
                        <div class="bento-content">
                            <span class="bento-tag">Bedroom Comfort</span>
                            <h3 class="bento-title">Master Bedroom Sets</h3>
                            <p class="bento-desc">King-size beds, ergonomic mattresses, and sliding wardrobes.</p>
                            <span class="bento-link">View Bedrooms <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>

                    <!-- Bento 5: Dining & Decor (4 col) -->
                    <div class="bento-box col-span-4" style="background-image: url('https://images.unsplash.com/photo-1617806118233-18e1de247200?q=80&w=800&auto=format&fit=crop');">
                        <div class="bento-content">
                            <span class="bento-tag">Dining & Decor</span>
                            <h3 class="bento-title">Solid Wood Dining Sets</h3>
                            <p class="bento-desc">Marble and solid wood 6-seater dining collections.</p>
                            <span class="bento-link">Explore Dining <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ========================================================
             HOW IT WORKS SECTION
             ======================================================== -->
        <section class="how-section" id="how-it-works">
            <div class="container">
                <div class="section-header">
                    <span class="section-tag">Effortless Journey</span>
                    <h2>How EF Savings Scheme Works</h2>
                    <p>Our monthly savings model empowers you to furnish your home smartly without loans, compound interest, or hidden credit charges.</p>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="step-card">
                            <div class="step-number">1</div>
                            <h3>Enroll in Your Plan</h3>
                            <p>Select your comfortable monthly installment (₹500 to ₹5,000) and register your account in under 2 minutes.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="step-card">
                            <div class="step-number">2</div>
                            <h3>Pay Monthly & Unlock Draws</h3>
                            <p>Make easy UPI or online payments each month. Receive instant digital receipts and automatic entry into monthly prize draws.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="step-card">
                            <div class="step-number">3</div>
                            <h3>Redeem Dream Products</h3>
                            <p>At maturity or upon draw win, redeem 100% of your accumulated value for your chosen furniture or electronic appliances.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================================
             SAVINGS CALCULATOR SECTION
             ======================================================== -->
        <section class="calc-section" id="calculator">
            <div class="container">
                <div class="row align-items-center g-5">
                    
                    <div class="col-lg-6">
                        <div class="badge-brand" style="background: rgba(155, 0, 144, 0.2); color: #f5d0f2; border-color: rgba(155, 0, 144, 0.4);">
                            <i class="fas fa-calculator"></i> Interactive Simulator
                        </div>
                        <h2 class="text-white mt-3 mb-3" style="font-size: 2.4rem;">
                            Plan Your Savings, <br>
                            <span class="text-gradient">See Your Future Home.</span>
                        </h2>
                        <p class="text-muted mb-4" style="color: #94A3B8 !important; font-size: 1.05rem;">
                            Adjust your monthly budget and duration to estimate your accumulated product purchasing power and eligible bumper draw perks.
                        </p>
                        
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <i class="fas fa-check-circle text-magenta fs-5"></i>
                                <span>Zero registration fees or processing charges</span>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <i class="fas fa-check-circle text-blue fs-5"></i>
                                <span>100% of money goes towards your furniture & electronics</span>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <i class="fas fa-check-circle text-magenta fs-5"></i>
                                <span>Exchangeable across our entire brand showroom</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="calc-box">
                            <div class="calc-slider-box">
                                <label>
                                    <span>Monthly Contribution:</span>
                                    <span class="text-magenta fw-bold" id="monthlyDisplay">₹1,000 / mo</span>
                                </label>
                                <input type="range" class="calc-range" id="monthlyRange" min="500" max="10000" step="500" value="1000" oninput="updateCalculator()">
                            </div>

                            <div class="calc-slider-box">
                                <label>
                                    <span>Savings Duration:</span>
                                    <span class="text-blue fw-bold" id="durationDisplay">12 Months</span>
                                </label>
                                <input type="range" class="calc-range" id="durationRange" min="6" max="24" step="6" value="12" oninput="updateCalculator()">
                            </div>

                            <div class="calc-result-card">
                                <div class="text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px; opacity: 0.9;">Total Product Value</div>
                                <div class="calc-result-val" id="totalResult">₹12,000</div>
                                <div class="small mb-3" style="opacity: 0.9;" id="drawsTicketDisplay">+ 12 Monthly Lucky Draw Entries Included</div>
                                <button onclick="openLoginModal()" class="btn-brand-white w-100 justify-content-center">
                                    Start This Plan
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ========================================================
             CONTINUOUS GALLERY SHOWCASE
             ======================================================== -->
        <section class="gallery-section" id="gallery">
            <div class="container text-center mb-4">
                <span class="section-tag" style="color: var(--brand-magenta); font-weight: 700; text-transform: uppercase;">Real Living Showrooms</span>
                <h2 class="mt-1">Experience Our Spaces & Delivery</h2>
            </div>
            
            <div class="gallery-track">
                <div class="gallery-card"><img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=600&auto=format&fit=crop" alt="Living Set"></div>
                <div class="gallery-card"><img src="https://images.unsplash.com/photo-1593784991095-a205069470b6?q=80&w=600&auto=format&fit=crop" alt="Smart TV"></div>
                <div class="gallery-card"><img src="https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=600&auto=format&fit=crop" alt="Bedroom Setup"></div>
                <div class="gallery-card"><img src="https://images.unsplash.com/photo-1556911220-e15b29be8c8f?q=80&w=600&auto=format&fit=crop" alt="Kitchen Decor"></div>
                <div class="gallery-card"><img src="https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?q=80&w=600&auto=format&fit=crop" alt="Luxury Sofa"></div>
                <div class="gallery-card"><img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?q=80&w=600&auto=format&fit=crop" alt="Center Table"></div>
                <!-- Duplicate for loop -->
                <div class="gallery-card"><img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=600&auto=format&fit=crop" alt="Living Set"></div>
                <div class="gallery-card"><img src="https://images.unsplash.com/photo-1593784991095-a205069470b6?q=80&w=600&auto=format&fit=crop" alt="Smart TV"></div>
                <div class="gallery-card"><img src="https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=600&auto=format&fit=crop" alt="Bedroom Setup"></div>
                <div class="gallery-card"><img src="https://images.unsplash.com/photo-1556911220-e15b29be8c8f?q=80&w=600&auto=format&fit=crop" alt="Kitchen Decor"></div>
            </div>
        </section>

        <!-- ========================================================
             PARENT GROUP / PRO GEE DEE VENTURES ECOSYSTEM
             ======================================================== -->
        <section class="ecosystem-section">
            <div class="container text-center">
                <div class="badge-brand mb-2">Corporate Lineage</div>
                <h3 class="mb-2">A Proud Unit of Pro Gee Dee Ventures Pvt. Ltd.</h3>
                <p class="text-muted" style="max-width: 600px; margin: 0 auto 30px;">
                    Liya's Furniture & Electronics operates under the corporate umbrella of Pro Gee Dee Ventures Pvt. Ltd., committed to delivering ethical excellence across retail, technology, and customer savings.
                </p>

                <div class="ecosystem-grid">
                    <a href="https://thebrandweave.com/" target="_blank" class="ecosystem-item" title="The Brand Weave">
                        <img src="landing_assets/images/gdlogo5.png" alt="The Brand Weave">
                    </a>
                    <a href="https://liyasgoldanddiamonds.com/" target="_blank" class="ecosystem-item" title="Liya's Gold & Diamonds">
                        <img src="landing_assets/images/gdlogo6.png" alt="Liya's Gold and Diamonds">
                    </a>
                    <a href="https://gdedutech.com/" target="_blank" class="ecosystem-item" title="GD EduTech">
                        <img src="landing_assets/images/gdlogo2.png" alt="GD EduTech">
                    </a>
                    <a href="https://liyasinternational.com/" target="_blank" class="ecosystem-item" title="Liya's International">
                        <img src="landing_assets/images/gdlogo3.webp" alt="Liya's International">
                    </a>
                    <a href="https://shop.goldendream.in/" target="_blank" class="ecosystem-item" title="Golden Dream">
                        <img src="landing_assets/images/gdlogo1.png" alt="Golden Dream">
                    </a>
                </div>
            </div>
        </section>

        <!-- ========================================================
             CTA BANNER
             ======================================================== -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-box">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h2>Ready to Furnish Your Dream Home?</h2>
                            <p>
                                Join thousands of satisfied families upgrading their living spaces with zero stress. Start saving from ₹500/month today and qualify for our upcoming bumper draw!
                            </p>
                            <div class="d-flex gap-3 flex-wrap">
                                <button onclick="openLoginModal()" class="btn-brand-white">
                                    <i class="fas fa-user-plus"></i> Join Savings Scheme
                                </button>
                                <a href="./about.php" class="hero-btn-outline" style="border-color: rgba(255,255,255,0.4);">
                                    <i class="fas fa-envelope"></i> Contact Advisors
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- ========================================================
         LOGIN MODAL
         ======================================================== -->
    <div class="modal-overlay" id="loginModal">
        <div class="login-modal">
            <div class="modal-header-custom">
                <div>
                    <h3 class="fw-bold">Sign In to Liya's</h3>
                    <p class="text-muted small m-0">Select your account portal</p>
                </div>
                <button class="close-modal-btn" onclick="closeLoginModal()" aria-label="Close Modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="login-options-grid">
                <a href="../customer" class="login-choice-card">
                    <div class="login-choice-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="login-choice-info">
                        <h4>Customer Portal</h4>
                        <p>Track subscriptions, payments & draws</p>
                    </div>
                </a>

                <a href="../promoter" class="login-choice-card">
                    <div class="login-choice-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="login-choice-info">
                        <h4>Promoter Portal</h4>
                        <p>Manage customer enrollments & referrals</p>
                    </div>
                </a>

                <a href="../admin" class="login-choice-card">
                    <div class="login-choice-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="login-choice-info">
                        <h4>Admin Console</h4>
                        <p>System management & financial operations</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Footer Component -->
    <?php include '../components/footer.php'; ?>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Page Script -->
    <script>
        // Modal functions
        function openLoginModal() {
            const modal = document.getElementById('loginModal');
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeLoginModal() {
            const modal = document.getElementById('loginModal');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        document.getElementById('loginModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLoginModal();
            }
        });

        // Interactive Calculator
        function updateCalculator() {
            const monthly = parseInt(document.getElementById('monthlyRange').value);
            const duration = parseInt(document.getElementById('durationRange').value);
            
            document.getElementById('monthlyDisplay').textContent = '₹' + monthly.toLocaleString('en-IN') + ' / mo';
            document.getElementById('durationDisplay').textContent = duration + ' Months';
            
            const total = monthly * duration;
            document.getElementById('totalResult').textContent = '₹' + total.toLocaleString('en-IN');
            document.getElementById('drawsTicketDisplay').textContent = '+ ' + duration + ' Monthly Lucky Draw Entries Included';
        }

        // Initialize carousel
        document.addEventListener('DOMContentLoaded', function() {
            const carousel = document.getElementById('heroCarousel');
            if (carousel) {
                new bootstrap.Carousel(carousel, {
                    interval: 4500,
                    ride: 'carousel',
                    wrap: true
                });
            }
        });
    </script>

</body>
</html>