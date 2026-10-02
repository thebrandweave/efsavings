<?php
// Determine dynamic relative path to landing directory
$scriptDir = dirname($_SERVER['PHP_SELF']);
$landingBase = '.';
if (strpos($scriptDir, '/shop') !== false) {
    $landingBase = '../landing';
} elseif (strpos($scriptDir, '/customer') !== false || strpos($scriptDir, '/admin') !== false) {
    $landingBase = '../landing';
}
?>
<style>
/* ========================================================
   LIYA'S FURNITURE & ELECTRONICS - MODERN FOOTER STYLES
   Colors: Primary Magenta (#9B0090), Cobalt Blue (#0B5CAD)
   ======================================================== */
.modern-footer {
    background: #0B0F19;
    color: #F8FAFC;
    padding: 70px 0 0;
    position: relative;
    overflow: hidden;
    max-width: 100vw !important;
}

.modern-footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(90deg, #9B0090 0%, #0B5CAD 100%);
}

.footer-content {
    display: grid;
    grid-template-columns: 1.4fr 1fr 1fr 1.2fr;
    gap: 40px;
    max-width: 1320px;
    margin: 0 auto;
    padding: 0 24px;
}

.footer-section {
    margin-bottom: 30px;
}

.footer-logo-img {
    height: 52px;
    width: auto;
    max-width: 220px;
    margin-bottom: 18px;
    object-fit: contain;
}

.footer-section p {
    color: #94A3B8;
    line-height: 1.7;
    font-size: 0.93rem;
    margin-bottom: 20px;
}

.unit-badge {
    display: inline-block;
    background: rgba(155, 0, 144, 0.15);
    border: 1px solid rgba(155, 0, 144, 0.3);
    color: #f0abfc;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 4px;
    margin-bottom: 16px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.footer-section h3 {
    color: #FFFFFF;
    font-size: 1.15rem;
    font-weight: 700;
    margin-bottom: 22px;
    position: relative;
    padding-bottom: 12px;
}

.footer-section h3::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 36px;
    height: 2.5px;
    background: linear-gradient(90deg, #9B0090, #0B5CAD);
    border-radius: 2px;
}

.footer-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-section ul li {
    margin-bottom: 12px;
}

.footer-section ul li a {
    color: #94A3B8;
    text-decoration: none;
    font-size: 0.93rem;
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
    gap: 10px;
}

.footer-section ul li a i {
    font-size: 0.75rem;
    color: #9B0090;
    transition: transform 0.25s ease;
}

.footer-section ul li a:hover {
    color: #38BDF8;
    transform: translateX(4px);
}

.footer-section ul li a:hover i {
    color: #38BDF8;
    transform: translateX(2px);
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    color: #94A3B8;
    font-size: 0.9rem;
    line-height: 1.5;
}

.contact-item i {
    color: #9B0090;
    font-size: 1rem;
    margin-top: 3px;
    min-width: 18px;
}

.social-links {
    display: flex;
    gap: 12px;
    margin-top: 18px;
}

.social-links a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.05);
    color: #CBD5E1;
    font-size: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.08);
    transition: all 0.3s ease;
    text-decoration: none;
}

.social-links a:hover {
    background: linear-gradient(135deg, #9B0090, #0B5CAD);
    color: #ffffff;
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(155, 0, 144, 0.4);
}

.copyright-bar {
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    padding: 24px 20px;
    margin-top: 50px;
    background: #070A11;
}

.copyright-content {
    max-width: 1320px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    color: #64748B;
    font-size: 0.85rem;
}

.copyright-content a {
    color: #94A3B8;
    text-decoration: none;
    transition: color 0.2s ease;
}

.copyright-content a:hover {
    color: #9B0090;
}

@media (max-width: 991px) {
    .footer-content {
        grid-template-columns: 1fr 1fr;
        gap: 32px;
    }
}

@media (max-width: 576px) {
    .footer-content {
        grid-template-columns: 1fr;
        gap: 28px;
    }
    .copyright-content {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<footer class="modern-footer" id="contact">
    <div class="footer-content">
        <!-- Brand Info -->
        <div class="footer-section">
            <a href="<?php echo $landingBase; ?>/">
                <img src="<?php echo $landingBase; ?>/landing_assets/images/liyas_logo_white.png" alt="Liya's Furniture & Electronics Logo" class="footer-logo-img">
            </a>
            <div class="unit-badge">A Unit of Pro Gee Dee Ventures Pvt. Ltd.</div>
            <p>
                Experience premium home living with smart monthly savings schemes for luxury furniture, modular interiors, and cutting-edge home electronics. Save easily, win monthly rewards, and furnish your dream home with zero hassle.
            </p>
            <div class="social-links">
                <a href="https://www.instagram.com/pro_gd_ventures_pvt/" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="https://www.youtube.com/@goldendream23" target="_blank" title="YouTube"><i class="fab fa-youtube"></i></a>
                <a href="https://linkedin.com/company/goldendream" target="_blank" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="<?php echo $landingBase; ?>/"><i class="fas fa-chevron-right"></i> Home</a></li>
                <li><a href="<?php echo $landingBase; ?>/about.php"><i class="fas fa-chevron-right"></i> About Us</a></li>
                <li><a href="<?php echo $landingBase; ?>/#schemes"><i class="fas fa-chevron-right"></i> Savings Schemes</a></li>
                <li><a href="<?php echo $landingBase; ?>/#categories"><i class="fas fa-chevron-right"></i> Furniture & Electronics</a></li>
                <li><a href="<?php echo $landingBase; ?>/career.php"><i class="fas fa-chevron-right"></i> Careers</a></li>
                <li><a href="javascript:void(0)" onclick="openLoginModal()"><i class="fas fa-chevron-right"></i> Customer Login</a></li>
            </ul>
        </div>

        <!-- Collections & Schemes -->
        <div class="footer-section">
            <h3>Our Offerings</h3>
            <ul>
                <li><a href="<?php echo $landingBase; ?>/#categories"><i class="fas fa-chevron-right"></i> Luxury Living & Sofas</a></li>
                <li><a href="<?php echo $landingBase; ?>/#categories"><i class="fas fa-chevron-right"></i> 4K Smart TVs & Audio</a></li>
                <li><a href="<?php echo $landingBase; ?>/#categories"><i class="fas fa-chevron-right"></i> Inverter Refrigerators & ACs</a></li>
                <li><a href="<?php echo $landingBase; ?>/#categories"><i class="fas fa-chevron-right"></i> Master Bedroom Sets</a></li>
                <li><a href="<?php echo $landingBase; ?>/#schemes"><i class="fas fa-chevron-right"></i> Monthly Draw Rewards</a></li>
                <li><a href="<?php echo $landingBase; ?>/#schemes"><i class="fas fa-chevron-right"></i> Bumper Prize Draws</a></li>
            </ul>
        </div>

        <!-- Contact Info -->
        <div class="footer-section">
            <h3>Contact Us</h3>
            <div class="contact-info">
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Ground Floor, Sri Mantame Complex, Near Soorya Infotech Park, Mudipu Road, Kurnadu, Bantwal - 574153, Karnataka, India</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone-alt"></i>
                    <span>+91 99951 94472 / +91 81057 53472</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>info@efsavings.in</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <span>Monday - Sunday: 9:30 AM - 7:00 PM</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Copyright -->
    <div class="copyright-bar">
        <div class="copyright-content">
            <div>
                &copy; <?php echo date('Y'); ?> <strong>Liya's Furniture & Electronics</strong>. A Unit of Pro Gee Dee Ventures Pvt. Ltd. All rights reserved.
            </div>
            <div>
                <a href="<?php echo $landingBase; ?>/about.php">Privacy & Terms</a> &bull; 
                <a href="<?php echo $landingBase; ?>/#contact">Support</a>
            </div>
        </div>
    </div>
</footer>