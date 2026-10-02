<?php
// Determine dynamic relative path to landing directory
$scriptDir = dirname($_SERVER['PHP_SELF']);
$landingBase = '.';
if (strpos($scriptDir, '/shop') !== false) {
    $landingBase = '../landing';
} elseif (strpos($scriptDir, '/customer') !== false || strpos($scriptDir, '/admin') !== false) {
    $landingBase = '../landing';
}
$activePage = basename($_SERVER['PHP_SELF']);
?>
<style>
/* ========================================================
   LIYA'S FURNITURE & ELECTRONICS - MODERN NAVBAR STYLES
   Colors: Primary Magenta (#9B0090), Cobalt Blue (#0B5CAD)
   ======================================================== */
:root {
    --brand-magenta: #9B0090;
    --brand-magenta-dark: #7b0073;
    --brand-blue: #0B5CAD;
    --brand-blue-dark: #084887;
    --brand-gradient: linear-gradient(135deg, #9B0090 0%, #0B5CAD 100%);
    --brand-gradient-hover: linear-gradient(135deg, #b300a7 0%, #0d6ecc 100%);
    --dark-luxury: #0F172A;
    --bg-white-glass: rgba(255, 255, 255, 0.98);
    --border-subtle: rgba(15, 23, 42, 0.08);
    --text-main: #1E293B;
    --transition-smooth: all 0.3s cubic-bezier(0.25, 1, 0.5, 1);
}

.premium-header {
    position: sticky;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1050;
    background: var(--bg-white-glass);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06);
    transition: var(--transition-smooth);
}

/* Slim brand accent bar at the very top */
.header-accent-bar {
    width: 100%;
    height: 3px;
    background: var(--brand-gradient);
}

.premium-navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 28px;
    max-width: 1400px;
    margin: 0 auto;
    transition: var(--transition-smooth);
}

/* Brand Logo */
.premium-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    transition: var(--transition-smooth);
}

.premium-logo:hover {
    transform: translateY(-1px);
}

.logo-img {
    height: 48px;
    width: auto;
    max-width: 180px;
    object-fit: contain;
    transition: var(--transition-smooth);
}

/* Nav Links */
.nav-links-wrapper {
    display: flex;
    gap: 28px;
    align-items: center;
    margin: 0;
    padding: 0;
    list-style: none;
}

.nav-link-item {
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: none;
    color: var(--text-main);
    letter-spacing: 0.3px;
    position: relative;
    padding: 8px 4px;
    transition: var(--transition-smooth);
}

.nav-link-item::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 2.5px;
    background: var(--brand-gradient);
    border-radius: 4px;
    transition: var(--transition-smooth);
}

.nav-link-item:hover {
    color: var(--brand-magenta);
}

.nav-link-item:hover::after,
.nav-link-item.active::after {
    width: 100%;
}

.nav-link-item.active {
    color: var(--brand-magenta);
    font-weight: 700;
}

/* Right Actions */
.nav-actions-wrapper {
    display: flex;
    align-items: center;
    gap: 16px;
}

.country-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: #F1F5F9;
    border: 1px solid #E2E8F0;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #475569;
}

.country-pill .flag-icon {
    width: 20px;
    height: 14px;
    border-radius: 2px;
    object-fit: cover;
}

/* Premium Login CTA */
.premium-login-btn {
    position: relative;
    overflow: hidden;
    background: var(--brand-gradient);
    color: #ffffff;
    border: none;
    border-radius: 8px;
    padding: 10px 22px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(155, 0, 144, 0.3);
    transition: var(--transition-smooth);
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.premium-login-btn .btn-content {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #ffffff;
}

.premium-login-btn:hover {
    background: var(--brand-gradient-hover);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(155, 0, 144, 0.45);
    color: #ffffff;
}

/* Mobile Toggle Hamburger */
.premium-mobile-toggle {
    display: none;
    flex-direction: column;
    justify-content: center;
    gap: 5px;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 6px;
    width: 36px;
    height: 36px;
    border-radius: 6px;
}

.premium-mobile-toggle:hover {
    background: #F1F5F9;
}

.premium-mobile-toggle .bar {
    width: 22px;
    height: 2px;
    background-color: var(--dark-luxury);
    border-radius: 2px;
    transition: var(--transition-smooth);
}

.premium-mobile-toggle.active .bar:nth-child(1) {
    transform: translateY(7px) rotate(45deg);
}

.premium-mobile-toggle.active .bar:nth-child(2) {
    opacity: 0;
}

.premium-mobile-toggle.active .bar:nth-child(3) {
    transform: translateY(-7px) rotate(-45deg);
}

/* Responsive */
@media (max-width: 991px) {
    .premium-navbar {
        padding: 10px 18px;
    }
    
    .logo-img {
        height: 40px;
    }

    .nav-links-wrapper {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background: #ffffff;
        flex-direction: column;
        padding: 20px 24px;
        gap: 16px;
        border-bottom: 2px solid var(--brand-magenta);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-8px);
        transition: var(--transition-smooth);
    }

    .nav-links-wrapper.open {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .nav-link-item {
        width: 100%;
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .premium-mobile-toggle {
        display: flex;
    }

    .country-pill {
        display: none;
    }
}
</style>

<header class="premium-header">
    <div class="header-accent-bar"></div>
    <div class="container-fluid px-0">
        <nav class="premium-navbar">
            
            <!-- Left: Brand Logo (Liya's Furniture & Electronics) -->
            <div class="nav-brand-wrapper">
                <a href="<?php echo $landingBase; ?>/" class="premium-logo" title="Liya's Furniture & Electronics - A Unit of Pro Gee Dee Ventures Pvt. Ltd.">
                    <img src="<?php echo $landingBase; ?>/landing_assets/images/liyas_logo.png" alt="Liya's Furniture & Electronics Logo" class="logo-img">
                </a>
            </div>

            <!-- Center: Navigation Links -->
            <div class="nav-links-wrapper" id="navLinksMenu">
                <a href="<?php echo $landingBase; ?>/" class="nav-link-item <?php echo ($activePage == 'index.php' || $activePage == 'index' || $activePage == '') ? 'active' : ''; ?>">Home</a>
                <a href="<?php echo $landingBase; ?>/about.php" class="nav-link-item <?php echo ($activePage == 'about.php') ? 'active' : ''; ?>">About Us</a>
                <a href="<?php echo $landingBase; ?>/#schemes" class="nav-link-item">Savings Schemes</a>
                <a href="<?php echo $landingBase; ?>/#categories" class="nav-link-item">Collections</a>
                <a href="<?php echo $landingBase; ?>/career.php" class="nav-link-item <?php echo ($activePage == 'career.php') ? 'active' : ''; ?>">Careers</a>
                <a href="<?php echo $landingBase; ?>/#contact" class="nav-link-item">Contact Us</a>
            </div>

            <!-- Right: Country & Login CTA -->
            <div class="nav-actions-wrapper">
                <div class="country-pill">
                    <img src="<?php echo $landingBase; ?>/landing_assets/images/india.png" alt="India Flag" class="flag-icon">
                    <span class="country-name">India</span>
                </div>
                
                <button class="premium-login-btn" onclick="openLoginModal()" aria-label="Customer Login">
                    <div class="btn-content">
                        <i class="far fa-user-circle"></i>
                        <span>Login</span>
                    </div>
                </button>

                <button class="premium-mobile-toggle" aria-label="Toggle Menu" onclick="toggleMobileMenu()">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
            </div>

        </nav>
    </div>
</header>

<script>
    function toggleMobileMenu() {
        const toggleBtn = document.querySelector('.premium-mobile-toggle');
        if (toggleBtn) {
            toggleBtn.classList.toggle('active');
        }
        const navLinks = document.getElementById('navLinksMenu');
        if (navLinks) {
            navLinks.classList.toggle('open');
        }
    }
</script>