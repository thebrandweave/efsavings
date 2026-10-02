<?php
session_start();

$menuPath = "../";
$currentPage = "admins";

// Authentication check
require_once("../middleware/auth.php");
verifyAuth();

// Database connection
require_once("../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

$currentAdminId = $_SESSION['admin_id'] ?? 0;

// Handle Delete Admin
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $targetAdminId = (int)$_GET['delete'];

    if ($targetAdminId === (int)$currentAdminId) {
        $_SESSION['error_message'] = "You cannot delete your own admin account.";
    } else {
        try {
            $conn->beginTransaction();

            // Fetch admin name before delete
            $stmt = $conn->prepare("SELECT Name FROM Admins WHERE AdminID = ?");
            $stmt->execute([$targetAdminId]);
            $adminToDelete = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($adminToDelete) {
                // Log activity
                $action = "Deleted admin account: " . $adminToDelete['Name'];
                $stmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Admin', ?, ?)");
                $stmt->execute([$currentAdminId, $action, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);

                // Delete admin
                $stmt = $conn->prepare("DELETE FROM Admins WHERE AdminID = ?");
                $stmt->execute([$targetAdminId]);

                $conn->commit();
                $_SESSION['success_message'] = "Admin '" . htmlspecialchars($adminToDelete['Name']) . "' deleted successfully.";
            } else {
                $conn->rollBack();
                $_SESSION['error_message'] = "Admin not found.";
            }
        } catch (PDOException $e) {
            $conn->rollBack();
            $_SESSION['error_message'] = "Failed to delete admin: " . $e->getMessage();
        }
    }
    header("Location: index.php");
    exit();
}

// Handle Status Toggle (activate / deactivate)
if (isset($_GET['status']) && !empty($_GET['status']) && isset($_GET['id']) && !empty($_GET['id'])) {
    $targetAdminId = (int)$_GET['id'];
    $newStatus = $_GET['status'] === 'activate' ? 'Active' : 'Inactive';

    if ($targetAdminId === (int)$currentAdminId && $newStatus === 'Inactive') {
        $_SESSION['error_message'] = "You cannot deactivate your own admin account.";
    } else {
        try {
            $conn->beginTransaction();

            $action = "Changed admin status to " . $newStatus . " for Admin ID: " . $targetAdminId;
            $stmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Admin', ?, ?)");
            $stmt->execute([$currentAdminId, $action, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);

            $stmt = $conn->prepare("UPDATE Admins SET Status = ? WHERE AdminID = ?");
            $stmt->execute([$newStatus, $targetAdminId]);

            $conn->commit();
            $_SESSION['success_message'] = "Admin status updated to {$newStatus} successfully.";
        } catch (PDOException $e) {
            $conn->rollBack();
            $_SESSION['error_message'] = "Failed to update admin status: " . $e->getMessage();
        }
    }
    header("Location: index.php");
    exit();
}

// Search and filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$roleFilter = isset($_GET['role']) ? trim($_GET['role']) : '';
$statusFilter = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : '';

// Build query
$whereClauses = [];
$params = [];

if (!empty($search)) {
    $whereClauses[] = "(Name LIKE :search OR Email LIKE :search OR AdminID = :searchId)";
    $params[':search'] = "%{$search}%";
    $params[':searchId'] = is_numeric($search) ? (int)$search : 0;
}

if (!empty($roleFilter) && in_array($roleFilter, ['SuperAdmin', 'Verifier'])) {
    $whereClauses[] = "Role = :role";
    $params[':role'] = $roleFilter;
}

if (!empty($statusFilter) && in_array($statusFilter, ['Active', 'Inactive'])) {
    $whereClauses[] = "Status = :status";
    $params[':status'] = $statusFilter;
}

$whereSql = !empty($whereClauses) ? " WHERE " . implode(" AND ", $whereClauses) : "";

// Count for pagination
$countStmt = $conn->prepare("SELECT COUNT(*) FROM Admins" . $whereSql);
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();

// Pagination setup
$recordsPerPage = 10;
$totalPages = max(1, (int)ceil($totalRecords / $recordsPerPage));
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $recordsPerPage;

// Fetch admins
$query = "SELECT AdminID, Name, Email, Role, Status, CreatedAt FROM Admins" . $whereSql . " ORDER BY AdminID DESC LIMIT :offset, :limit";
$stmt = $conn->prepare($query);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Summary Stats
$statsStmt = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN Status = 'Active' THEN 1 ELSE 0 END) as active_count,
    SUM(CASE WHEN Role = 'SuperAdmin' THEN 1 ELSE 0 END) as super_count,
    SUM(CASE WHEN Role = 'Verifier' THEN 1 ELSE 0 END) as verifier_count
FROM Admins");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Helper for Initials
function getAdminInitials($name) {
    $parts = explode(' ', trim($name));
    if (count($parts) >= 2) {
        return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
    }
    return strtoupper(substr($name, 0, 2));
}

// Include sidebar & topbar
include("../components/sidebar.php");
include("../components/topbar.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management | Liya's Furniture & Electronics</title>
    <link rel="icon" type="image/png" href="../../landing/landing_assets/images/liyas_logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-primary: #9B0090;
            --brand-secondary: #0B5CAD;
            --brand-obsidian: #0B0F19;
            --brand-slate: #111827;
            --brand-gradient: linear-gradient(135deg, #9B0090 0%, #0B5CAD 100%);
            --brand-gradient-hover: linear-gradient(135deg, #b800aa 0%, #0d6ed0 100%);
            --text-dark: #1e293b;
            --text-medium: #475569;
            --text-light: #94a3b8;
            --bg-card: #ffffff;
            --bg-body: #f8fafc;
            --border-light: #e2e8f0;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow-subtle: 0 4px 20px -2px rgba(11, 15, 25, 0.05);
            --shadow-elevated: 0 10px 25px -5px rgba(11, 15, 25, 0.08);
            --transition-smooth: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-dark);
            margin: 0;
            padding: 0;
        }

        .admins-container {
            padding: 24px 28px 40px;
            max-width: 1400px;
            margin: 0 auto;
            font-family: 'Poppins', sans-serif;
        }

        /* Page Header */
        .page-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .header-title-box h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--brand-obsidian);
            margin: 0 0 4px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-title-box h1 i {
            background: var(--brand-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header-title-box p {
            margin: 0;
            font-size: 13px;
            color: var(--text-medium);
        }

        .header-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn-add-admin {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--brand-gradient);
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(155, 0, 144, 0.25);
            transition: var(--transition-smooth);
            border: none;
            cursor: pointer;
        }

        .btn-add-admin:hover {
            background: var(--brand-gradient-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(155, 0, 144, 0.35);
            color: #ffffff;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: var(--shadow-subtle);
            transition: var(--transition-smooth);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-elevated);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .stat-icon.total {
            background: rgba(155, 0, 144, 0.1);
            color: var(--brand-primary);
        }

        .stat-icon.active {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .stat-icon.super {
            background: rgba(11, 92, 173, 0.1);
            color: var(--brand-secondary);
        }

        .stat-icon.verifier {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .stat-details h3 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            color: var(--brand-obsidian);
        }

        .stat-details p {
            margin: 2px 0 0 0;
            font-size: 13px;
            color: var(--text-medium);
            font-weight: 500;
        }

        /* Main Card */
        .main-card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-subtle);
            overflow: hidden;
        }

        /* Filters Bar */
        .filters-bar {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border-light);
            background: #ffffff;
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: center;
            justify-content: space-between;
        }

        .filters-form {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            flex: 1;
        }

        .search-input-box {
            position: relative;
            flex: 1;
            min-width: 240px;
            max-width: 380px;
        }

        .search-input-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 14px;
        }

        .search-input-box input {
            width: 100%;
            padding: 9px 14px 9px 38px;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            font-size: 13px;
            color: var(--text-dark);
            background: #f8fafc;
            outline: none;
            transition: var(--transition-smooth);
        }

        .search-input-box input:focus {
            background: #ffffff;
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(155, 0, 144, 0.1);
        }

        .filter-select {
            padding: 9px 14px;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            font-size: 13px;
            color: var(--text-dark);
            background: #f8fafc;
            outline: none;
            cursor: pointer;
            transition: var(--transition-smooth);
            min-width: 140px;
        }

        .filter-select:focus {
            background: #ffffff;
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(155, 0, 144, 0.1);
        }

        .btn-filter {
            background: var(--brand-secondary);
            color: #ffffff;
            border: none;
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: var(--transition-smooth);
        }

        .btn-filter:hover {
            background: #094a8f;
        }

        .btn-reset {
            background: transparent;
            color: var(--text-medium);
            border: 1px solid var(--border-light);
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: var(--transition-smooth);
        }

        .btn-reset:hover {
            background: #f1f5f9;
            color: var(--danger);
            border-color: #fca5a5;
        }

        /* Alert notifications */
        .alert-box {
            margin: 18px 22px 0;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 13.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-box.success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-box.error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Table */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .admins-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13.5px;
        }

        .admins-table th {
            background: #f8fafc;
            color: var(--text-medium);
            font-weight: 600;
            padding: 14px 18px;
            border-bottom: 1px solid var(--border-light);
            white-space: nowrap;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            font-size: 12px;
        }

        .admins-table td {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
            color: var(--text-dark);
        }

        .admins-table tbody tr {
            transition: var(--transition-smooth);
        }

        .admins-table tbody tr:hover {
            background-color: rgba(155, 0, 144, 0.02);
        }

        .admins-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* User Info Cell */
        .user-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--brand-gradient);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 6px rgba(155, 0, 144, 0.2);
            flex-shrink: 0;
        }

        .user-name-box {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: var(--brand-obsidian);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .you-badge {
            background: rgba(155, 0, 144, 0.1);
            color: var(--brand-primary);
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
        }

        .user-email {
            font-size: 12px;
            color: var(--text-light);
        }

        /* Badges */
        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .role-badge.superadmin {
            background: rgba(155, 0, 144, 0.12);
            color: var(--brand-primary);
            border: 1px solid rgba(155, 0, 144, 0.2);
        }

        .role-badge.verifier {
            background: rgba(11, 92, 173, 0.12);
            color: var(--brand-secondary);
            border: 1px solid rgba(11, 92, 173, 0.2);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pill.active {
            background: #dcfce7;
            color: #15803d;
        }

        .status-pill.active .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #16a34a;
            box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.2);
        }

        .status-pill.inactive {
            background: #fee2e2;
            color: #b91c1c;
        }

        .status-pill.inactive .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #dc2626;
        }

        /* Actions */
        .action-buttons {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            text-decoration: none;
            border: 1px solid transparent;
            transition: var(--transition-smooth);
            cursor: pointer;
        }

        .btn-action.edit {
            background: rgba(11, 92, 173, 0.08);
            color: var(--brand-secondary);
            border-color: rgba(11, 92, 173, 0.2);
        }

        .btn-action.edit:hover {
            background: var(--brand-secondary);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .btn-action.deactivate {
            background: rgba(245, 158, 11, 0.08);
            color: var(--warning);
            border-color: rgba(245, 158, 11, 0.2);
        }

        .btn-action.deactivate:hover {
            background: var(--warning);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .btn-action.activate {
            background: rgba(16, 185, 129, 0.08);
            color: var(--success);
            border-color: rgba(16, 185, 129, 0.2);
        }

        .btn-action.activate:hover {
            background: var(--success);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .btn-action.delete {
            background: rgba(239, 68, 68, 0.08);
            color: var(--danger);
            border-color: rgba(239, 68, 68, 0.2);
        }

        .btn-action.delete:hover {
            background: var(--danger);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .btn-action.disabled {
            opacity: 0.35;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Empty State */
        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }

        .empty-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: rgba(155, 0, 144, 0.06);
            color: var(--brand-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 14px;
        }

        .empty-state h3 {
            margin: 0 0 6px 0;
            font-size: 16px;
            font-weight: 600;
            color: var(--brand-obsidian);
        }

        .empty-state p {
            margin: 0;
            font-size: 13px;
            color: var(--text-medium);
        }

        /* Pagination */
        .pagination-container {
            padding: 16px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid var(--border-light);
            flex-wrap: wrap;
            gap: 12px;
        }

        .pagination-info {
            font-size: 13px;
            color: var(--text-medium);
        }

        .pagination-links {
            display: flex;
            gap: 6px;
            align-items: center;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .pagination-links a,
        .pagination-links span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 10px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            border: 1px solid var(--border-light);
            color: var(--text-dark);
            background: #ffffff;
            transition: var(--transition-smooth);
        }

        .pagination-links a:hover {
            border-color: var(--brand-primary);
            color: var(--brand-primary);
            background: rgba(155, 0, 144, 0.03);
        }

        .pagination-links .active-page {
            background: var(--brand-gradient);
            color: #ffffff;
            border-color: transparent;
            box-shadow: 0 2px 6px rgba(155, 0, 144, 0.25);
        }

        .pagination-links .disabled-link {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }

        @media (max-width: 768px) {
            .admins-container {
                padding: 16px;
            }

            .filters-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .filters-form {
                flex-direction: column;
                align-items: stretch;
            }

            .search-input-box {
                max-width: 100%;
            }

            .filter-select {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="content-wrapper">
        <div class="admins-container">
            <!-- Header -->
            <div class="page-header-row">
                <div class="header-title-box">
                    <h1><i class="fas fa-user-shield"></i> Admin Management</h1>
                    <p>Manage system administrators, roles, permissions and account security</p>
                </div>
                <div class="header-actions">
                    <a href="add/" class="btn-add-admin">
                        <i class="fas fa-plus-circle"></i> Add New Admin
                    </a>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-users-gear"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['total'] ?? 0); ?></h3>
                        <p>Total Admins</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon active">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['active_count'] ?? 0); ?></h3>
                        <p>Active Accounts</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon super">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['super_count'] ?? 0); ?></h3>
                        <p>Super Admins</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon verifier">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['verifier_count'] ?? 0); ?></h3>
                        <p>Verifiers</p>
                    </div>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="main-card">
                <!-- Filters -->
                <div class="filters-bar">
                    <form action="" method="GET" class="filters-form">
                        <div class="search-input-box">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Search by name, email, or ID..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>

                        <select name="role" class="filter-select">
                            <option value="">All Roles</option>
                            <option value="SuperAdmin" <?php echo ($roleFilter === 'SuperAdmin') ? 'selected' : ''; ?>>SuperAdmin</option>
                            <option value="Verifier" <?php echo ($roleFilter === 'Verifier') ? 'selected' : ''; ?>>Verifier</option>
                        </select>

                        <select name="status_filter" class="filter-select">
                            <option value="">All Status</option>
                            <option value="Active" <?php echo ($statusFilter === 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo ($statusFilter === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>

                        <button type="submit" class="btn-filter">
                            <i class="fas fa-filter"></i> Filter
                        </button>

                        <?php if (!empty($search) || !empty($roleFilter) || !empty($statusFilter)): ?>
                            <a href="index.php" class="btn-reset">
                                <i class="fas fa-rotate-left"></i> Reset
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Flash Messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert-box success">
                        <i class="fas fa-circle-check"></i>
                        <span><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert-box error">
                        <i class="fas fa-circle-exclamation"></i>
                        <span><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="admins-table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>Admin User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th style="width: 140px; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($admins) > 0): ?>
                                <?php foreach ($admins as $admin): ?>
                                    <tr>
                                        <td><strong>#<?php echo $admin['AdminID']; ?></strong></td>
                                        <td>
                                            <div class="user-cell">
                                                <div class="user-avatar">
                                                    <?php echo getAdminInitials($admin['Name']); ?>
                                                </div>
                                                <div class="user-name-box">
                                                    <span class="user-name">
                                                        <?php echo htmlspecialchars($admin['Name']); ?>
                                                        <?php if ((int)$admin['AdminID'] === (int)$currentAdminId): ?>
                                                            <span class="you-badge">You</span>
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="color: var(--text-medium);">
                                                <i class="fas fa-envelope" style="color: var(--text-light); margin-right: 4px; font-size: 11px;"></i>
                                                <?php echo htmlspecialchars($admin['Email']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($admin['Role'] === 'SuperAdmin'): ?>
                                                <span class="role-badge superadmin">
                                                    <i class="fas fa-shield-halved"></i> SuperAdmin
                                                </span>
                                            <?php else: ?>
                                                <span class="role-badge verifier">
                                                    <i class="fas fa-check-double"></i> Verifier
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($admin['Status'] === 'Active'): ?>
                                                <span class="status-pill active">
                                                    <span class="dot"></span> Active
                                                </span>
                                            <?php else: ?>
                                                <span class="status-pill inactive">
                                                    <span class="dot"></span> Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span style="color: var(--text-medium); font-size: 12.5px;">
                                                <i class="far fa-calendar" style="color: var(--text-light); margin-right: 4px;"></i>
                                                <?php echo date('M d, Y', strtotime($admin['CreatedAt'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons" style="justify-content: center;">
                                                <!-- Edit -->
                                                <a href="edit.php?id=<?php echo $admin['AdminID']; ?>" class="btn-action edit" title="Edit Admin">
                                                    <i class="fas fa-pen-to-square"></i>
                                                </a>

                                                <!-- Status Toggle -->
                                                <?php if ((int)$admin['AdminID'] === (int)$currentAdminId): ?>
                                                    <span class="btn-action disabled" title="You cannot change your own status">
                                                        <i class="fas fa-ban"></i>
                                                    </span>
                                                <?php else: ?>
                                                    <?php if ($admin['Status'] === 'Active'): ?>
                                                        <a href="index.php?status=deactivate&id=<?php echo $admin['AdminID']; ?>" 
                                                           class="btn-action deactivate" 
                                                           title="Deactivate Admin"
                                                           onclick="return confirm('Are you sure you want to deactivate this admin account?');">
                                                            <i class="fas fa-user-slash"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="index.php?status=activate&id=<?php echo $admin['AdminID']; ?>" 
                                                           class="btn-action activate" 
                                                           title="Activate Admin"
                                                           onclick="return confirm('Are you sure you want to activate this admin account?');">
                                                            <i class="fas fa-user-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endif; ?>

                                                <!-- Delete -->
                                                <?php if ((int)$admin['AdminID'] === (int)$currentAdminId): ?>
                                                    <span class="btn-action disabled" title="You cannot delete your own account">
                                                        <i class="fas fa-trash-can"></i>
                                                    </span>
                                                <?php else: ?>
                                                    <a href="index.php?delete=<?php echo $admin['AdminID']; ?>" 
                                                       class="btn-action delete" 
                                                       title="Delete Admin"
                                                       onclick="return confirm('Are you sure you want to delete this admin account? This action cannot be undone.');">
                                                        <i class="fas fa-trash-can"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-user-shield"></i>
                                            </div>
                                            <h3>No Administrators Found</h3>
                                            <p>No admins match your search or filter criteria.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <?php
                    $queryParams = [];
                    if (!empty($search)) $queryParams['search'] = $search;
                    if (!empty($roleFilter)) $queryParams['role'] = $roleFilter;
                    if (!empty($statusFilter)) $queryParams['status_filter'] = $statusFilter;
                    function buildPageUrl($pageNumber, $queryParams) {
                        $params = array_merge($queryParams, ['page' => $pageNumber]);
                        return '?' . http_build_query($params);
                    }
                    ?>
                    <div class="pagination-container">
                        <div class="pagination-info">
                            Showing <strong><?php echo min(($page - 1) * $recordsPerPage + 1, $totalRecords); ?></strong> to <strong><?php echo min($page * $recordsPerPage, $totalRecords); ?></strong> of <strong><?php echo $totalRecords; ?></strong> admins
                        </div>
                        <ul class="pagination-links">
                            <?php if ($page > 1): ?>
                                <li><a href="<?php echo buildPageUrl($page - 1, $queryParams); ?>"><i class="fas fa-chevron-left"></i></a></li>
                            <?php else: ?>
                                <li><span class="disabled-link"><i class="fas fa-chevron-left"></i></span></li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <li><span class="active-page"><?php echo $i; ?></span></li>
                                <?php else: ?>
                                    <li><a href="<?php echo buildPageUrl($i, $queryParams); ?>"><?php echo $i; ?></a></li>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li><a href="<?php echo buildPageUrl($page + 1, $queryParams); ?>"><i class="fas fa-chevron-right"></i></a></li>
                            <?php else: ?>
                                <li><span class="disabled-link"><i class="fas fa-chevron-right"></i></span></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
