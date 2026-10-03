<?php
// Get user data from session


// Get customer data with profile image
$top_statementdatabase = new database();
$top_statementdb = $top_statementdatabase->getConnection();
$top_statement = $top_statementdb->prepare("SELECT ProfileImageURL FROM Customers WHERE CustomerID = ?");
$top_statement->execute([$userData['customer_id']]);
$customer = $top_statement->fetch(PDO::FETCH_ASSOC);

// Get unread notifications count
$top_statement = $top_statementdb->prepare("SELECT COUNT(*) as count FROM Notifications WHERE UserID = ? AND UserType = 'Customer' AND IsRead = 0");
$top_statement->execute([$userData['customer_id']]);
$notificationCount = $top_statement->fetch(PDO::FETCH_ASSOC)['count'];

// Get current page name for active state
$current_page = basename($_SERVER['PHP_SELF']);

// Set default avatar if no profile image
// Normalize profile image path so it loads correctly across pages
$profileImageUrl = '';
$stored = isset($customer['ProfileImageURL']) ? $customer['ProfileImageURL'] : '';
if (!empty($stored)) {
    // If the path is relative like "uploads/profile_images/...", prefix the editProfile directory under profile
    if (preg_match('~^uploads/|^profile_images/|^uploads\\/~', $stored)) {
        $profileImageUrl = $c_path . 'profile/editProfile/' . ltrim($stored, '/');
    } else {
        // absolute URL or already correct
        $profileImageUrl = $stored;
    }
}
if (empty($profileImageUrl)) {
    $profileImageUrl = $c_path . 'uploads/default-avatar.png';
}
?>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">


<nav class="topbar">
    <div class="topbar-content">
        <div style="display: flex; align-items: center; gap: 12px;">
            <h1 class="topbar-title"><?php echo ucfirst(str_replace('.php', '', $current_page)); ?></h1>
            <span class="topbar-role-badge badge-customer"><i class="fas fa-gem"></i> Customer</span>
        </div>
        <div class="topbar-actions">
            <div class="notification-icon">
                <i class="fas fa-bell"></i>
                <?php if ($notificationCount > 0): ?>
                    <span class="notification-badge"><?php echo $notificationCount; ?></span>
                <?php endif; ?>
            </div>
            <div class="user-profile" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?php echo htmlspecialchars($profileImageUrl); ?>" alt="User Avatar" class="user-avatar">
                <span class="user-name"> <?php echo htmlspecialchars($userData['customer_name']); ?></span>
            </div>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?php echo $c_path; ?>profile"><i class="fas fa-user"></i> Profile</a></li>
                <!-- <li><a class="dropdown-item" href="<?php echo $c_path; ?>settings"><i class="fas fa-cog"></i> Settings</a></li> -->
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="<?php echo $c_path; ?>logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<style>

    *{
        font-family: 'Poppins', sans-serif;
    }
    .topbar {
        background: #071220;
        padding: 15px 24px;
        position: fixed;
        top: 0;
        right: 0;
        left: 250px;
        z-index: 999;
        border-bottom: 1px solid rgba(2, 132, 199, 0.22);
        border-top: 3px solid #0284C7;
        height: 70px;
        
    }

    .topbar-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 100%;
    }

    .topbar-title {
        color: #fff;
        font-size: 20px;
        font-weight: 600;
        margin: 0;
    }

    .topbar-role-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .badge-customer {
        background: rgba(2, 132, 199, 0.15);
        color: #38bdf8;
        border: 1px solid rgba(2, 132, 199, 0.35);
    }

    .topbar-actions {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .notification-icon {
        position: relative;
        color: rgba(255, 255, 255, 0.7);
        font-size: 1.2rem;
        cursor: pointer;
        padding: 8px;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .notification-icon:hover {
        background: rgba(2, 132, 199, 0.15);
        color: #38bdf8;
    }

    .notification-badge {
        position: absolute;
        top: 0;
        right: 0;
        background: #0284C7 !important;
        color: #fff;
        font-size: 0.7rem;
        padding: 2px 6px;
        border-radius: 50%;
        border: 2px solid #071220;
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        padding: 6px 12px;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .user-profile:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .user-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #0284C7;
        background-color: #0c1e34;
    }

    .user-name {
        color: rgba(255, 255, 255, 0.9);
        font-weight: 400;
        font-size: 14px;
    }

    .dropdown-menu {
        background: #0c1e34;
        border: 1px solid rgba(2, 132, 199, 0.22);
        border-radius: 8px;
        padding: 8px;
        min-width: 180px;
        margin-top: 8px;
    }

    .dropdown-item {
        color: rgba(255, 255, 255, 0.7);
        padding: 8px 16px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.2s ease;
    }

    .dropdown-item:hover {
        background: rgba(255, 255, 255, 0.05);
        color: #fff;
    }

    .dropdown-item i {
        font-size: 16px;
        color: rgba(255, 255, 255, 0.7);
    }

    .dropdown-divider {
        border-color: rgba(255, 255, 255, 0.05);
        margin: 8px 0;
    }

    @media (max-width: 768px) {
        .topbar {
            left: 70px;
            padding: 15px 16px;
        }

        .topbar-title {
            font-size: 18px;
        }

        .user-name {
            display: none;
        }

        .user-profile {
            padding: 6px;
        }
    }

    /* Align topbar with full-width content on tablets/phones */
    @media (max-width: 992px) {
        .topbar {
            left: 0 !important;
        }
    }
</style>