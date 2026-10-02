<?php 
require_once  $c_path.'c_includes/loader.php';
?>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand-wrapper">
            <div class="side-logo">
                <img src="<?php echo $c_path; ?>uploads/liyas_logo_white.png" alt="Liya's Furniture & Electronics" style="max-height: 40px; max-width: 170px; object-fit: contain;">
            </div>
            <div class="portal-badge badge-customer">
                <i class="fas fa-gem"></i>
                <span>CUSTOMER PORTAL</span>
            </div>
        </div>
        <button class="sidebar-close-btn" id="sidebarCloseBtn" style="display:none;" aria-label="Close Sidebar">&times;</button>
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>" href="<?php echo $c_path; ?>dashboard">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'profile' ? 'active' : ''; ?>" href="<?php echo $c_path; ?>profile">
                <i class="fas fa-user"></i>
                <span>My Profile</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'schemes' ? 'active' : ''; ?>" href="<?php echo $c_path; ?>schemes">
                <i class="fas fa-gift"></i>
                <span>Schemes</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'subscriptions' ? 'active' : ''; ?>" href="<?php echo $c_path; ?>subscriptions">
                <i class="fas fa-calendar-check"></i>
                <span>My Subscriptions</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'payments' ? 'active' : ''; ?>" href="<?php echo $c_path; ?>payments">
                <i class="fas fa-money-bill-wave"></i>
                <span>Payments</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'receipts' ? 'active' : ''; ?>" href="<?php echo $c_path; ?>receipts">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Receipts</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'winners' ? 'active' : ''; ?>" href="<?php echo $c_path; ?>winners">
                <i class="fas fa-trophy"></i>
                <span>Winners</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'withdrawals' ? 'active' : ''; ?>" href="<?php echo $c_path; ?>withdrawals">
                <i class="fas fa-wallet"></i>
                <span>Withdrawals</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'notifications' ? 'active' : ''; ?>" href="<?php echo $c_path; ?>notifications">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
                <span class="badge bg-danger notification-badge">0</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'payment_qr' ? 'active' : ''; ?>" href="<?php echo $c_path; ?>payment_qr">
                <i class="fas fa-qrcode"></i>
                <span>Payment QR</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'support' ? 'active' : ''; ?>" href="<?php echo $c_path; ?>support">
                <i class="fas fa-headset"></i>
                <span>Support & Contact</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="<?php echo $c_path; ?>logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>

<button class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Open Sidebar">
    <i class="fas fa-bars"></i>
</button>

<style>
    .sidebar {
        width: 250px;
        height: 100vh;
        background: linear-gradient(180deg, #071220 0%, #0c1e34 50%, #071220 100%);
        color: #fff;
        position: fixed;
        left: 0;
        top: 0;
        overflow-y: auto;
        transition: all 0.3s ease;
        z-index: 1200;
        border-right: 1px solid rgba(2, 132, 199, 0.28);
        box-shadow: 4px 0 25px rgba(0, 0, 0, 0.5);
    }

    .sidebar-header {
        padding: 16px 15px;
        text-align: center;
        background: transparent;
        border-bottom: 1px solid rgba(2, 132, 199, 0.18);
        min-height: 80px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .sidebar-brand-wrapper {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
        overflow: hidden;
    }

    .portal-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        padding: 3px 9px;
        border-radius: 20px;
        white-space: nowrap;
        transition: all 0.3s ease;
    }

    .badge-customer {
        background: rgba(2, 132, 199, 0.18);
        border: 1px solid rgba(2, 132, 199, 0.45);
        color: #38bdf8;
        box-shadow: 0 0 10px rgba(2, 132, 199, 0.25);
    }

    .sidebar-header .side-logo h3 {
        color: #fff;
        margin: 0;
        font-size: 18px !important;
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    .sidebar::-webkit-scrollbar {
        width: 0;
    }

    .sidebar .nav {
        padding: 10px 0;
    }

    .sidebar .nav-link {
        color: rgba(255, 255, 255, 0.75);
        padding: 12px 16px;
        display: flex;
        align-items: center;
        transition: all 0.2s ease;
        position: relative;
        border-radius: 6px;
        margin: 2px 8px;
        width: calc(100% - 16px);
    }

    .sidebar .nav-link:hover {
        color: #38bdf8;
        background: rgba(2, 132, 199, 0.15);
        transform: translateX(0);
    }

    .sidebar .nav-link.active {
        color: #fff;
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);
        border-left: 3px solid #f59e0b;
    }

    .sidebar .nav-link i {
        width: 20px;
        margin-right: 12px;
        font-size: 1.1rem;
        color: rgba(255, 255, 255, 0.75);
        filter: none;
    }

    .sidebar .nav-link:hover i {
        color: #38bdf8;
    }

    .sidebar .nav-link.active i {
        color: #fff;
    }

    .notification-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        border-radius: 50%;
        background: #0284c7 !important;
        color: #fff;
        box-shadow: 0 0 8px rgba(2, 132, 199, 0.5);
    }

    /* Responsive Sidebar */
    @media (max-width: 768px) {
        .sidebar {
            width: 70px;
        }
        .sidebar.open {
            width: 220px !important;
        }
        /* Hide text labels when sidebar is collapsed on phones */
        .sidebar:not(.open) .nav-link span {
            display: none;
        }
        .sidebar:not(.open) .nav-link {
            justify-content: center;
            padding: 12px 0;
        }
        .sidebar:not(.open) .nav-link i {
            margin-right: 0;
            font-size: 1.2rem;
        }
        .sidebar:not(.open) .notification-badge {
            display: none;
        }
        .sidebar:not(.open) .sidebar-header .side-logo h3 {
            display: none;
        }
        .sidebar:not(.open) .portal-badge {
            display: none !important;
        }
    }

    /* Main Content Adjustment */
    .main-content {
        margin-left: 250px;
        padding: 20px;
        transition: all 0.3s ease;
        background: #1A1D21;
        /* Match sidebar background */
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
        }
    }

    /* Use full width content when sidebar is off-canvas on tablets/phones */
    @media (max-width: 992px) {
        .main-content {
            margin-left: 0 !important;
        }
    }

    .sidebar-toggle-btn {
        display: none;
        position: fixed;
        top: 18px;
        left: 18px;
        z-index: 1100;
        background: #0284C7;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 22px;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    .sidebar-close-btn {
        display: none;
        position: absolute;
        top: 16px;
        right: 16px;
        background: transparent;
        color: #fff;
        border: none;
        font-size: 28px;
        cursor: pointer;
        z-index: 1200;
    }
    @media (max-width: 992px) {
        .sidebar {
            left: -260px;
            transition: left 0.3s cubic-bezier(.4,0,.2,1);
            width: 250px;
            box-shadow: 2px 0 8px rgba(0,0,0,0.15);
        }
        .sidebar.open {
            left: 0;
        }
        .sidebar-toggle-btn {
            display: block;
        }
        .sidebar-close-btn {
            display: block !important;
        }
        body.sidebar-open {
            overflow: hidden;
        }
        .sidebar-overlay {
            display: block;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.3);
            z-index: 1099;
        }
    }
    @media (max-width: 768px) {
        .sidebar {
            width: 220px;
        }
    }
</style>
<div class="sidebar-overlay" id="sidebarOverlay" style="display:none;"></div>
<script>
    const sidebar = document.getElementById('sidebar');
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    function openSidebar() {
        sidebar.classList.add('open');
        sidebarOverlay.style.display = 'block';
        document.body.classList.add('sidebar-open');
    }
    function closeSidebar() {
        sidebar.classList.remove('open');
        sidebarOverlay.style.display = 'none';
        document.body.classList.remove('sidebar-open');
    }
    sidebarToggleBtn.addEventListener('click', openSidebar);
    sidebarCloseBtn.addEventListener('click', closeSidebar);
    sidebarOverlay.addEventListener('click', closeSidebar);
    // Hide sidebar on resize if > 992px
    window.addEventListener('resize', function() {
        if(window.innerWidth > 992) closeSidebar();
    });
    // Close sidebar when a nav link is clicked (for mobile navigation)
    document.querySelectorAll('.sidebar .nav-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            if (window.innerWidth <= 992) {
                closeSidebar();
                window.location = this.href;
            }
        });
    });
</script>