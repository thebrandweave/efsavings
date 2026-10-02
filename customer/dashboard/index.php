<?php
// Maintenance check - must be first
require_once '../../utils/maintenance_check.php';

require_once '../config/config.php';
require_once '../config/session_check.php';
$c_path = "../";
$current_page = "dashboard";
// Get user data and validate session
$userData = checkSession();

// Get customer data
$database = new Database();
$db = $database->getConnection();
$stmt = $db->prepare("SELECT * FROM Customers WHERE CustomerID = ?");
$stmt->execute([$userData['customer_id']]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// Get total balance
$stmt = $db->prepare("
    SELECT SUM(BalanceAmount) as total_balance 
    FROM Balances 
    WHERE UserID = ? AND UserType = 'Customer'
");
$stmt->execute([$userData['customer_id']]);
$balance_result = $stmt->fetch(PDO::FETCH_ASSOC);
$available_balance = $balance_result['total_balance'] ?? 0;

// Get current month winners
$stmt = $db->prepare("
    SELECT w.*, s.SchemeName 
    FROM Winners w
    JOIN Subscriptions sub ON w.UserID = sub.CustomerID
    JOIN Schemes s ON sub.SchemeID = s.SchemeID
    WHERE w.UserID = ? 
    AND w.UserType = 'Customer'
    AND MONTH(w.WinningDate) = MONTH(CURRENT_DATE())
    AND YEAR(w.WinningDate) = YEAR(CURRENT_DATE())
    ORDER BY w.WinningDate DESC
");
$stmt->execute([$userData['customer_id']]);
$current_month_winners = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get next payment due
$stmt = $db->prepare("
    SELECT 
        sub.SchemeID,
        s.SchemeName,
        s.MonthlyPayment,
        s.StartDate AS SchemeStartDate,
        s.TotalPayments,
        (SELECT COUNT(*) FROM Payments p 
            WHERE p.CustomerID = sub.CustomerID 
              AND p.SchemeID = sub.SchemeID 
              AND p.Status = 'Verified') AS paid_installments
    FROM Subscriptions sub
    JOIN Schemes s ON sub.SchemeID = s.SchemeID
    WHERE sub.CustomerID = ? 
      AND sub.RenewalStatus = 'Active'
");
$stmt->execute([$userData['customer_id']]);
$activeSubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$next_payment = null;
$earliestDue = null;
foreach ($activeSubs as $sub) {
    $paidInstallments = (int)$sub['paid_installments'];
    $totalPayments = (int)$sub['TotalPayments'];
    if ($paidInstallments >= $totalPayments) {
        continue;
    }
    $nextInstallmentNo = $paidInstallments + 1;
    $schemeStart = new DateTime($sub['SchemeStartDate']);
    $targetMonth = (clone $schemeStart)->modify('+' . $paidInstallments . ' months');
    $stmtDraw = $db->prepare("SELECT DrawDate FROM Installments WHERE SchemeID = ? AND InstallmentNumber = ? LIMIT 1");
    $stmtDraw->execute([$sub['SchemeID'], $nextInstallmentNo]);
    $drawStr = $stmtDraw->fetchColumn();
    $drawDay = $drawStr ? (int)(new DateTime($drawStr))->format('d') : (int)$schemeStart->format('d');
    $due = DateTime::createFromFormat('Y-m-d', $targetMonth->format('Y-m-') . str_pad((string)$drawDay, 2, '0', STR_PAD_LEFT));
    if (!$due) { $due = (clone $targetMonth)->modify('last day of this month'); }

    if ($earliestDue === null || $due < $earliestDue) {
        $earliestDue = $due;
        $next_payment = [
            'SchemeName' => $sub['SchemeName'],
            'MonthlyPayment' => $sub['MonthlyPayment'],
            'NextDueDate' => $due->format('Y-m-d'),
        ];
    }
}

// Get total active subscriptions
$stmt = $db->prepare("
    SELECT COUNT(*) as total_subscriptions
    FROM Subscriptions
    WHERE CustomerID = ? AND RenewalStatus = 'Active'
");
$stmt->execute([$userData['customer_id']]);
$subscription_count = $stmt->fetch(PDO::FETCH_ASSOC)['total_subscriptions'];

// Get total prizes won
$stmt = $db->prepare("
    SELECT COUNT(*) as total_prizes
    FROM Winners
    WHERE UserID = ? AND UserType = 'Customer'
");
$stmt->execute([$userData['customer_id']]);
$total_prizes = $stmt->fetch(PDO::FETCH_ASSOC)['total_prizes'];

// Get pending withdrawals
$stmt = $db->prepare("
    SELECT COUNT(*) as pending_withdrawals
    FROM Withdrawals
    WHERE UserID = ? AND UserType = 'Customer' AND Status = 'Pending'
");
$stmt->execute([$userData['customer_id']]);
$pending_withdrawals = $stmt->fetch(PDO::FETCH_ASSOC)['pending_withdrawals'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard | Liya's Furniture & Electronics</title>
    <link rel="icon" type="image/png" href="../../landing/landing_assets/images/liyas_logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --dark-bg: #0B0F19;
            --card-bg: #111827;
            --accent-brand: #9B0090;
            --accent-blue: #0B5CAD;
            --brand-gradient: linear-gradient(135deg, #9B0090 0%, #0B5CAD 100%);
            --text-primary: #F8FAFC;
            --text-secondary: #94A3B8;
            --card-hover: #1E293B;
            --border-color: rgba(255, 255, 255, 0.08);
        }

        body {
            background: var(--dark-bg);
            color: var(--text-primary);
            min-height: 100vh;
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .dashboard-container {
            padding: 24px;
            margin-top: 70px;
            transition: margin-left 0.3s ease;
            max-width: 1600px;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #9B0090 0%, #0B5CAD 100%);
            border-radius: 14px;
            padding: 24px 28px;
            margin-bottom: 24px;
            box-shadow: 0 10px 30px rgba(155, 0, 144, 0.25);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .dashboard-header h2 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 4px;
            color: #ffffff;
        }

        .dashboard-header p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }

        .stats-card {
            background: var(--card-bg);
            border-radius: 14px;
            padding: 20px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
            transition: all 0.3s ease;
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .stats-card:hover {
            transform: translateY(-3px);
            border-color: rgba(155, 0, 144, 0.35);
            box-shadow: 0 8px 25px rgba(155, 0, 144, 0.15);
        }

        .stats-icon-wrap {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .stats-card:nth-child(1) .stats-icon-wrap {
            background: rgba(155, 0, 144, 0.15);
            color: #f5d0f2;
            border: 1px solid rgba(155, 0, 144, 0.3);
        }

        .stats-card:nth-child(2) .stats-icon-wrap {
            background: rgba(11, 92, 173, 0.15);
            color: #7dd3fc;
            border: 1px solid rgba(11, 92, 173, 0.3);
        }

        .stats-card:nth-child(3) .stats-icon-wrap {
            background: rgba(16, 185, 129, 0.15);
            color: #86efac;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .stats-card:nth-child(4) .stats-icon-wrap {
            background: rgba(245, 158, 11, 0.15);
            color: #fde68a;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .stats-info h5 {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--text-secondary);
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .stats-info h3 {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
        }

        .info-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .info-card {
            background: var(--card-bg);
            border-radius: 14px;
            padding: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
        }

        .info-card h4 {
            color: var(--text-primary);
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-card h4 i {
            color: #d946ef;
        }

        .winner-item {
            padding: 14px 16px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.03);
            margin-bottom: 12px;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .winner-item:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(155, 0, 144, 0.3);
        }

        .winner-item h5 {
            color: var(--text-primary);
            font-size: 14.5px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .badge-brand {
            background: var(--brand-gradient);
            color: #fff;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .payment-due-date {
            font-size: 26px;
            font-weight: 700;
            color: #38bdf8;
            margin: 12px 0;
        }

        .payment-amount {
            color: var(--text-secondary);
            font-size: 15px;
            font-weight: 500;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-state i {
            font-size: 36px;
            color: var(--accent-brand);
            margin-bottom: 16px;
            opacity: 0.8;
        }

        .empty-state h3 {
            color: var(--text-primary);
            font-size: 16px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .empty-state p {
            color: var(--text-secondary);
            margin: 0;
            font-size: 14px;
        }

        @media (max-width: 1200px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                margin-left: 0;
                padding: 12px;
            }
            .stats-row {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            .info-cards {
                grid-template-columns: 1fr;
            }
            .dashboard-header {
                padding: 16px;
            }
        }

        @media (max-width: 480px) {
            .dashboard-header {
                padding: 16px;
            }
            .stats-card {
                padding: 14px;
            }
            .info-card {
                padding: 16px;
            }
        }
    </style>
</head>

<body>
    <?php include '../c_includes/sidebar.php'; ?>
    <?php include '../c_includes/topbar.php'; ?>

    <div class="main-content">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <div>
                    <h2><i class="fas fa-chart-line"></i> Customer Dashboard</h2>
                    <p>Welcome back, <?php echo htmlspecialchars($userData['customer_name']); ?>!</p>
                </div>
                <div>
                    <a href="../payments/add.php" class="btn btn-sm" style="background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.4); padding: 9px 18px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-plus-circle"></i> Add Payment
                    </a>
                </div>
            </div>

            <div class="stats-row">
                <div class="stats-card">
                    <div class="stats-icon-wrap"><i class="fas fa-wallet"></i></div>
                    <div class="stats-info">
                        <h5>Available Balance</h5>
                        <h3>₹<?php echo number_format($available_balance, 2); ?></h3>
                    </div>
                </div>
                <div class="stats-card">
                    <div class="stats-icon-wrap"><i class="fas fa-calendar-check"></i></div>
                    <div class="stats-info">
                        <h5>Active Subscriptions</h5>
                        <h3><?php echo $subscription_count; ?></h3>
                    </div>
                </div>
                <div class="stats-card">
                    <div class="stats-icon-wrap"><i class="fas fa-trophy"></i></div>
                    <div class="stats-info">
                        <h5>Total Prizes Won</h5>
                        <h3><?php echo $total_prizes; ?></h3>
                    </div>
                </div>
                <div class="stats-card">
                    <div class="stats-icon-wrap"><i class="fas fa-clock"></i></div>
                    <div class="stats-info">
                        <h5>Pending Withdrawals</h5>
                        <h3><?php echo $pending_withdrawals; ?></h3>
                    </div>
                </div>
            </div>

            <div class="info-cards">
                <div class="info-card">
                    <h4><i class="fas fa-calendar-alt"></i> Next Payment Due</h4>
                    <?php if ($next_payment): ?>
                        <div class="payment-info">
                            <h5><?php echo htmlspecialchars($next_payment['SchemeName']); ?></h5>
                            <div class="payment-due-date">
                                <?php echo date('d M Y', strtotime($next_payment['NextDueDate'])); ?>
                            </div>
                            <div class="payment-amount">
                                Amount: ₹<?php echo number_format($next_payment['MonthlyPayment'], 2); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <h3>No Active Subscriptions</h3>
                            <p>You don't have any active subscriptions.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="info-card">
                    <h4><i class="fas fa-trophy"></i> Recent Winners</h4>
                    <?php
                    // Update the SQL query to get recent winners instead of current month
                    $stmt = $db->prepare("
                        SELECT w.*, s.SchemeName, w.WinningDate 
                        FROM Winners w
                        JOIN Subscriptions sub ON w.UserID = sub.CustomerID
                        JOIN Schemes s ON sub.SchemeID = s.SchemeID
                        WHERE w.UserID = ? 
                        AND w.UserType = 'Customer'
                        ORDER BY w.WinningDate DESC
                        LIMIT 5
                    ");
                    $stmt->execute([$userData['customer_id']]);
                    $recent_winners = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($recent_winners)): ?>
                        <?php foreach ($recent_winners as $winner): ?>
                            <div class="winner-item">
                                <h5><?php echo htmlspecialchars($winner['SchemeName']); ?></h5>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge-brand"><?php echo $winner['PrizeType']; ?></span>
                                    <small style="color: var(--text-secondary);">
                                        <?php echo date('d M Y', strtotime($winner['WinningDate'])); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-trophy"></i>
                            <h3>No Winners Yet</h3>
                            <p>Keep participating to win prizes!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function adjustLayout() {
            const windowWidth = window.innerWidth;
            const container = document.querySelector('.dashboard-container');

            // if (windowWidth <= 768) {
            //     container.style.marginLeft = '70px';
            // } else {
            //     container.style.marginLeft = '220px';
            // }
        }

        window.addEventListener('load', adjustLayout);
        window.addEventListener('resize', adjustLayout);
    </script>
</body>

</html>