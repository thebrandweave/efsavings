<?php
require_once '../config/config.php';
require_once '../config/session_check.php';
$c_path = "../";
$current_page = "subscriptions";
// Get user data and validate session
$userData = checkSession();

// Get customer data
$database = new Database();
$db = $database->getConnection();
$stmt = $db->prepare("SELECT * FROM Customers WHERE CustomerID = ?");
$stmt->execute([$userData['customer_id']]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// Get all subscriptions with scheme details and payment information
$stmt = $db->prepare("
    SELECT 
        s.*,
        sch.SchemeName,
        sch.MonthlyPayment,
        sch.Description,
        sch.TotalPayments,
        sch.StartDate AS SchemeStartDate,
        (SELECT COUNT(*) 
         FROM Payments p 
         WHERE p.CustomerID = s.CustomerID 
         AND p.SchemeID = s.SchemeID 
         AND p.Status = 'Verified') as paid_installments,
        (SELECT COALESCE(SUM(p.Amount), 0) 
         FROM Payments p 
         WHERE p.CustomerID = s.CustomerID 
         AND p.SchemeID = s.SchemeID 
         AND p.Status = 'Verified') as total_amount_paid,
        (SELECT COUNT(*) 
         FROM Payments p 
         WHERE p.CustomerID = s.CustomerID 
         AND p.SchemeID = s.SchemeID) as total_installments,
        (SELECT COUNT(*) 
         FROM Payments p 
         WHERE p.CustomerID = s.CustomerID 
         AND p.SchemeID = s.SchemeID 
         AND p.Status = 'Pending') as pending_installments
    FROM Subscriptions s
    JOIN Schemes sch ON s.SchemeID = sch.SchemeID
    WHERE s.CustomerID = ?
    ORDER BY 
        CASE 
            WHEN s.RenewalStatus = 'Active' THEN 1
            ELSE 2
        END,
        s.StartDate DESC
");
$stmt->execute([$userData['customer_id']]);
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get next payment due date for active subscriptions
$stmt = $db->prepare("
    SELECT 
        p.CustomerID,
        p.SchemeID,
        MIN(p.SubmittedAt) as next_payment_date
    FROM Payments p
    WHERE p.CustomerID = ? 
    AND p.Status = 'Pending'
    GROUP BY p.CustomerID, p.SchemeID
");
$stmt->execute([$userData['customer_id']]);
$nextPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create a map of next payment dates
$nextPaymentMap = [];
foreach ($nextPayments as $payment) {
    $key = $payment['CustomerID'] . '_' . $payment['SchemeID'];
    $nextPaymentMap[$key] = $payment['next_payment_date'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Subscriptions - Liyas Gold Savings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --dark-bg: #0B0F19;
            --card-bg: #111827;
            --accent-brand: #9B0090;
            --accent-blue: #0B5CAD;
            --accent-green: #0B5CAD;
            --text-primary: rgba(255, 255, 255, 0.9);
            --text-secondary: rgba(255, 255, 255, 0.7);
            --border-color: rgba(255, 255, 255, 0.08);
        }

        body {
            background: var(--dark-bg);
            color: var(--text-primary);
            min-height: 100vh;
            margin: 0;
            font-family: 'Inter', sans-serif;
        }

        .subscriptions-container {
            padding: 24px;
            margin-top: 70px;
        }

        .subscription-header {
            background: linear-gradient(135deg, #9B0090 0%, #0B5CAD 100%);
            border-radius: 12px;
            padding: 40px 24px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .subscription-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 100%);
        }

        .subscription-header h2 {
            color: #fff;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
            position: relative;
        }

        .subscription-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            margin: 0;
            position: relative;
        }

        .subscription-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .subscription-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .subscription-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent-green), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .subscription-card:hover::before {
            opacity: 1;
        }

        .scheme-name {
            color: var(--text-primary);
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .scheme-name i {
            color: var(--accent-green);
        }

        .subscription-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin: 20px 0;
        }

        .detail-item {
           background: rgba(225, 225, 225, 0.1);
            padding: 16px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid var(--border-color);
        }

        .detail-label {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 8px;
        }

        .detail-value {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 18px;
        }

        .progress {
            height: 8px;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.1);
            margin: 16px 0;
        }

        .progress-bar {
            background: linear-gradient(90deg, #0B5CAD, #9B0090);
            border-radius: 4px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-active {
            background: rgba(11, 92, 173, 0.15);
            color: #7dd3fc;
        }

        .status-expired {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .status-cancelled {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }

        .btn-view,
        .btn-payment {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }

        .btn-view {
            background: rgba(11, 92, 173, 0.15);
            color: #7dd3fc;
            border: 1px solid rgba(11, 92, 173, 0.4);
        }

        .btn-view:hover {
            background: #0B5CAD;
            color: white;
        }

        .btn-payment {
            background: linear-gradient(135deg, #9B0090 0%, #0B5CAD 100%);
            color: white;
            border: none;
            box-shadow: 0 2px 6px rgba(155, 0, 144, 0.25);
        }

        .btn-payment:hover {
            background: linear-gradient(135deg, #b800aa 0%, #0d6ed0 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(155, 0, 144, 0.35);
        }

        .btn-maturity {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: #ffffff;
            border: none;
            box-shadow: 0 2px 8px rgba(217, 119, 6, 0.35);
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-maturity:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(217, 119, 6, 0.55);
            color: #ffffff;
        }

        .maturity-highlight-card {
            background: linear-gradient(135deg, rgba(217, 119, 6, 0.1) 0%, rgba(155, 0, 144, 0.08) 100%);
            border: 1px dashed rgba(217, 119, 6, 0.45);
            border-radius: 10px;
            padding: 16px 20px;
            margin: 18px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .maturity-badge {
            background: rgba(217, 119, 6, 0.2);
            color: #fbbf24;
            border: 1px solid rgba(217, 119, 6, 0.4);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }

        .empty-state i {
            font-size: 48px;
            color: var(--text-secondary);
            margin-bottom: 16px;
        }

        .empty-state h3 {
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .empty-state p {
            color: var(--text-secondary);
            margin-bottom: 24px;
        }

        .payment-due {
            color: #dc3545;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .payment-info {
          background: rgba(225, 225, 225, 0.1);
            border-radius: 8px;
            padding: 16px;
            margin-top: 16px;
            border: 1px solid var(--border-color);
        }

        .payment-info i {
            color: var(--accent-green);
            margin-right: 8px;
        }

        .subscription-tabs {
            margin-bottom: 24px;
            display: flex;
            gap: 12px;
        }

        .subscription-tabs .nav-link {
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .subscription-tabs .nav-link:hover {
            color: var(--text-primary);
              background: rgba(225, 225, 225, 0.1);
            border-color: var(--accent-green);

        }

        .subscription-tabs .nav-link.active {
            background: var(--accent-green);
            color: white;
            border-color: var(--accent-green);
        }

        @media (max-width: 768px) {
            .subscriptions-container {
                margin-left: 0;
                padding: 16px;
            }

            .subscription-header {
                padding: 30px 20px;
            }

            .subscription-card {
                padding: 20px;
            }

            .subscription-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include '../c_includes/sidebar.php'; ?>
    <?php include '../c_includes/topbar.php'; ?>

    <div class="main-content">
        <div class="subscriptions-container">
            <div class="container">
                <div class="subscription-header text-center">
                    <h2><i class="fas fa-list"></i> My Subscriptions</h2>
                    <p class="mb-0">Track all your active and past subscriptions</p>
                </div>

                <?php if (empty($subscriptions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h3>No Subscriptions Found</h3>
                        <p>You haven't subscribed to any schemes yet.</p>
                        <a href="schemes.php" class="btn btn-primary">
                            <i class="fas fa-gem"></i> Explore Schemes
                        </a>
                    </div>
                <?php else: ?>
                    <ul class="nav subscription-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#active">
                                <i class="fas fa-check-circle"></i> Active
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#completed">
                                <i class="fas fa-history"></i> Completed
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="active">
                            <?php foreach ($subscriptions as $subscription): ?>
                                <?php if ($subscription['RenewalStatus'] === 'Active'): ?>
                                    <div class="subscription-card">
                                        <div class="scheme-name">
                                            <?php echo htmlspecialchars($subscription['SchemeName']); ?>
                                        </div>

                                        <div class="subscription-details">
                                            <div class="detail-item">
                                                <div class="detail-label">Monthly Payment</div>
                                                <div class="detail-value">
                                                    ₹<?php echo number_format($subscription['MonthlyPayment'], 2); ?>
                                                </div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">Start Date</div>
                                                <div class="detail-value">
                                                    <?php echo date('M d, Y', strtotime($subscription['SchemeStartDate'])); ?>
                                                </div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">End Date</div>
                                                <div class="detail-value">
                                                    <?php
                                                        $schemeStart = new DateTime($subscription['SchemeStartDate']);
                                                        $monthsToAdd = max(0, ((int)$subscription['TotalPayments']) - 1);
                                                        $computedEnd = (clone $schemeStart)->modify('+' . $monthsToAdd . ' months');
                                                        $computedEnd->modify('last day of this month');
                                                        echo $computedEnd->format('M d, Y');
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">Status</div>
                                                <div class="detail-value">
                                                    <span class="status-badge status-active">
                                                        Active
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="progress">
                                            <?php
                                            $percentage = ($subscription['total_installments'] > 0)
                                                ? ($subscription['paid_installments'] / $subscription['total_installments']) * 100
                                                : 0;
                                            ?>
                                            <div class="progress-bar" role="progressbar"
                                                style="width: <?php echo $percentage; ?>%"
                                                aria-valuenow="<?php echo $percentage; ?>"
                                                aria-valuemin="0"
                                                aria-valuemax="100">
                                                <?php echo round($percentage); ?>%
                                            </div>
                                        </div>

                                        <?php
                                        $paidInstallments = (int)$subscription['paid_installments'];
                                        $totalPayments = (int)$subscription['TotalPayments'];
                                        if ($subscription['RenewalStatus'] === 'Active' && $paidInstallments < $totalPayments):
                                            $nextInstallmentNo = $paidInstallments + 1;
                                            $schemeStart = new DateTime($subscription['SchemeStartDate']);
                                            $targetMonth = (clone $schemeStart)->modify('+' . $paidInstallments . ' months');
                                            $stmtDrawNext = $db->prepare("SELECT DrawDate FROM Installments WHERE SchemeID = ? AND InstallmentNumber = ? LIMIT 1");
                                            $stmtDrawNext->execute([$subscription['SchemeID'], $nextInstallmentNo]);
                                            $drawNextStr = $stmtDrawNext->fetchColumn();
                                            $drawDay = $drawNextStr ? (int)(new DateTime($drawNextStr))->format('d') : (int)$schemeStart->format('d');
                                            $nextDue = DateTime::createFromFormat('Y-m-d', $targetMonth->format('Y-m-') . str_pad((string)$drawDay, 2, '0', STR_PAD_LEFT));
                                            if (!$nextDue) { $nextDue = $targetMonth; }
                                        ?>
                                            <div class="payment-info">
                                                <i class="fas fa-info-circle"></i>
                                                Next payment due on:
                                                <span class="payment-due">
                                                    <?php echo $nextDue->format('M d, Y'); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <div>
                                                <small class="text-muted">
                                                    <?php echo $subscription['paid_installments']; ?> of <?php echo $subscription['total_installments']; ?> installments paid
                                                </small>
                                            </div>
                                            <div>
                                                <?php if ($subscription['pending_installments'] > 0): ?>
                                                    <a href="../payments/add.php?scheme_id=<?php echo $subscription['SchemeID']; ?>"
                                                        class="btn btn-payment me-2">
                                                        <i class="fas fa-money-bill-wave"></i> Make Payment
                                                    </a>
                                                <?php endif; ?>
<!--                                                 <a href="view_benefits.php?scheme_id=<?php echo $subscription['SchemeID']; ?>"
                                                    class="btn btn-view">
                                                    <i class="fas fa-eye"></i> View Details
                                                </a> -->
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <div class="tab-pane fade" id="completed">
                            <?php foreach ($subscriptions as $subscription): ?>
                                <?php if ($subscription['RenewalStatus'] !== 'Active'): ?>
                                    <div class="subscription-card">
                                        <div class="scheme-name">
                                            <?php echo htmlspecialchars($subscription['SchemeName']); ?>
                                        </div>

                                        <div class="subscription-details">
                                            <div class="detail-item">
                                                <div class="detail-label">Monthly Payment</div>
                                                <div class="detail-value">
                                                    ₹<?php echo number_format($subscription['MonthlyPayment'], 2); ?>
                                                </div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">Start Date</div>
                                                <div class="detail-value">
                                                    <?php echo date('M d, Y', strtotime($subscription['SchemeStartDate'])); ?>
                                                </div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">End Date</div>
                                                <div class="detail-value">
<?php
    $schemeStart = new DateTime($subscription['SchemeStartDate']);
    $monthsToAdd = max(0, ((int)$subscription['TotalPayments']) - 1);
    $computedEnd = (clone $schemeStart)->modify('+' . $monthsToAdd . ' months');
    $computedEnd->modify('last day of this month');
    echo $computedEnd->format('M d, Y');
?>
                                                </div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">Status</div>
                                                <div class="detail-value">
                                                    <span class="status-badge status-<?php echo strtolower($subscription['RenewalStatus']); ?>">
                                                        <?php echo $subscription['RenewalStatus']; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="progress">
                                            <?php
                                            $percentage = ($subscription['total_installments'] > 0)
                                                ? ($subscription['paid_installments'] / $subscription['total_installments']) * 100
                                                : 0;
                                            ?>
                                            <div class="progress-bar" role="progressbar"
                                                style="width: <?php echo $percentage; ?>%"
                                                aria-valuenow="<?php echo $percentage; ?>"
                                                aria-valuemin="0"
                                                aria-valuemax="100">
                                                <?php echo round($percentage); ?>%
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <div>
                                                <small class="text-muted">
                                                    <?php echo $subscription['paid_installments']; ?> of <?php echo $subscription['total_installments']; ?> installments paid
                                                </small>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="maturity_certificate.php?id=<?php echo $subscription['SubscriptionID']; ?>"
                                                    target="_blank" class="btn btn-maturity">
                                                    <i class="fas fa-award"></i> View Maturity Certificate
                                                </a>
                                                <a href="view_benefits.php?scheme_id=<?php echo $subscription['SchemeID']; ?>"
                                                    class="btn btn-view">
                                                    <i class="fas fa-eye"></i> View Details
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Plan Maturity Details Section -->
                                        <div class="maturity-highlight-card">
                                            <div>
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <span class="maturity-badge">
                                                        <i class="fas fa-check-double"></i> Plan Matured
                                                    </span>
                                                    <span style="font-size: 12.5px; color: #cbd5e1;">
                                                        Voucher #<strong>MAT-<?php echo str_pad($subscription['SubscriptionID'], 6, '0', STR_PAD_LEFT); ?></strong>
                                                    </span>
                                                </div>
                                                <div style="font-size: 13.5px; color: rgba(255,255,255,0.85);">
                                                    Total Accumulated Savings: <strong style="color: #38bdf8;">₹<?php echo number_format($subscription['total_amount_paid'] ?: ($subscription['paid_installments'] * $subscription['MonthlyPayment']), 2); ?></strong>
                                                    &bull; Redeemable at Liya's Showroom for furniture & appliances.
                                                </div>
                                            </div>
                                            <a href="maturity_certificate.php?id=<?php echo $subscription['SubscriptionID']; ?>"
                                                target="_blank" class="btn btn-maturity btn-sm">
                                                <i class="fas fa-print"></i> Print Voucher
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
