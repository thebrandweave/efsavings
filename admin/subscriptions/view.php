<?php
session_start();
require_once("../middleware/auth.php");
verifyAuth();

// Database connection
require_once("../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

// Get subscription ID from URL
$subscriptionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($subscriptionId <= 0) {
    header("Location: index.php");
    exit();
}

// Fetch subscription details
$query = "SELECT s.*, c.Name as CustomerName, c.CustomerUniqueID, c.Contact, c.Email, c.Address,
          sch.SchemeName, sch.Description as SchemeDescription,
          sch.MonthlyPayment as Amount, sch.TotalPayments,
          (SELECT COUNT(*) FROM Payments p WHERE p.CustomerID = s.CustomerID AND p.SchemeID = s.SchemeID AND p.Status = 'Verified') as paid_installments,
          (SELECT COALESCE(SUM(p.Amount), 0) FROM Payments p WHERE p.CustomerID = s.CustomerID AND p.SchemeID = s.SchemeID AND p.Status = 'Verified') as total_saved
          FROM Subscriptions s
          JOIN Customers c ON s.CustomerID = c.CustomerID
          JOIN Schemes sch ON s.SchemeID = sch.SchemeID
          WHERE s.SubscriptionID = :id";

$stmt = $conn->prepare($query);
$stmt->bindParam(':id', $subscriptionId);
$stmt->execute();
$subscription = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$subscription) {
    header("Location: index.php");
    exit();
}

// Calculate days remaining
$endDate = new DateTime($subscription['EndDate']);
$today = new DateTime();
$daysRemaining = $today->diff($endDate)->days;
$isExpired = $endDate < $today;

// Format dates
$startDate = date('d M Y', strtotime($subscription['StartDate']));
$endDate = date('d M Y', strtotime($subscription['EndDate']));
$createdAt = date('d M Y H:i', strtotime($subscription['CreatedAt']));
$updatedAt = date('d M Y H:i', strtotime($subscription['UpdatedAt']));

// Calculate duration in months
$start = new DateTime($subscription['StartDate']);
$end = new DateTime($subscription['EndDate']);
$interval = $start->diff($end);
$duration = ($interval->y * 12) + $interval->m;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Subscription - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #9B0090;
            --secondary-color: #0B5CAD;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }

        body {
            background-color: #f8f9fc;
        }

        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.35rem;
            margin-bottom: 0;
        }

        .status-badge {
            padding: 0.5em 1em;
            border-radius: 0.35rem;
            font-weight: 600;
        }

        .status-active {
            background-color: rgba(28, 200, 138, 0.1);
            color: var(--success-color);
        }

        .status-expired {
            background-color: rgba(231, 74, 59, 0.1);
            color: var(--danger-color);
        }

        .status-cancelled {
            background-color: rgba(133, 135, 150, 0.1);
            color: var(--secondary-color);
        }

        .info-item {
            padding: 1rem;
            border-bottom: 1px solid #e3e6f0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--secondary-color);
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-weight: 600;
            color: var(--dark-color);
        }

        .days-remaining {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .btn-back {
            color: var(--secondary-color);
            text-decoration: none;
        }

        .btn-back:hover {
            color: var(--primary-color);
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Subscription Details</h5>
                        <div class="d-flex align-items-center gap-2">
                            <a href="maturity_certificate.php?id=<?php echo $subscriptionId; ?>" target="_blank" class="btn btn-sm" style="background: linear-gradient(135deg, #d97706, #b45309); color: white; font-weight: 600;">
                                <i class="fas fa-award"></i> Maturity Voucher
                            </a>
                            <a href="index.php" class="btn-back">
                                <i class="fas fa-arrow-left"></i> Back to Subscriptions
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">Subscription ID</div>
                                    <div class="info-value">#<?php echo $subscription['SubscriptionID']; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">Status</div>
                                    <div class="status-badge status-<?php echo strtolower($subscription['RenewalStatus']); ?>">
                                        <?php echo $subscription['RenewalStatus']; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">Customer Name</div>
                                    <div class="info-value"><?php echo htmlspecialchars($subscription['CustomerName']); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">Contact</div>
                                    <div class="info-value"><?php echo htmlspecialchars($subscription['Contact']); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">Email</div>
                                    <div class="info-value"><?php echo htmlspecialchars($subscription['Email']); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">Scheme</div>
                                    <div class="info-value"><?php echo htmlspecialchars($subscription['SchemeName']); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">Start Date</div>
                                    <div class="info-value"><?php echo $startDate; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">End Date</div>
                                    <div class="info-value"><?php echo $endDate; ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">Monthly Amount</div>
                                    <div class="info-value">₹<?php echo number_format($subscription['Amount'], 2); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">Duration</div>
                                    <div class="info-value"><?php echo $duration; ?> months</div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">Created At</div>
                                    <div class="info-value"><?php echo $createdAt; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">Last Updated</div>
                                    <div class="info-value"><?php echo $updatedAt; ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="info-item">
                                    <div class="info-label">Scheme Description</div>
                                    <div class="info-value"><?php echo htmlspecialchars($subscription['SchemeDescription']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                $targetPayments = !empty($subscription['TotalPayments']) ? (int)$subscription['TotalPayments'] : 12;
                $paidCount = (int)$subscription['paid_installments'];
                $isPlanMatured = ($paidCount >= $targetPayments) || ($subscription['RenewalStatus'] === 'Expired');
                $totalCorpus = (float)($subscription['total_saved'] ?: ($paidCount * $subscription['Amount']));
                $targetCorpus = $targetPayments * (float)$subscription['Amount'];
                ?>
                <div class="card mb-4" style="border: 2px solid #d97706; overflow: hidden;">
                    <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, rgba(217, 119, 6, 0.12), rgba(155, 0, 144, 0.08));">
                        <h5 class="mb-0" style="color: #92400e; font-weight: 700;">
                            <i class="fas fa-award text-warning me-2"></i> Plan Maturity Details
                        </h5>
                        <?php if ($isPlanMatured): ?>
                            <span class="badge" style="background: #d97706; color: white; padding: 6px 12px; font-size: 13px; font-weight: 700;">
                                <i class="fas fa-check-circle"></i> MATURED (100% Complete)
                            </span>
                        <?php else: ?>
                            <span class="badge" style="background: #0B5CAD; color: white; padding: 6px 12px; font-size: 13px;">
                                <i class="fas fa-spinner fa-spin me-1"></i> In Progress (<?php echo $paidCount; ?>/<?php echo $targetPayments; ?>)
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="info-item">
                                    <div class="info-label">Maturity Date</div>
                                    <div class="info-value" style="font-size: 1.1rem; color: #0f172a;"><?php echo $endDate; ?></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-item">
                                    <div class="info-label">Accumulated Savings (Corpus)</div>
                                    <div class="info-value" style="font-size: 1.25rem; font-weight: 800; color: #9B0090;">
                                        ₹<?php echo number_format($totalCorpus, 2); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-item">
                                    <div class="info-label">Target Scheme Corpus</div>
                                    <div class="info-value" style="font-size: 1.1rem; color: #0B5CAD;">
                                        ₹<?php echo number_format($targetCorpus, 2); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">Installments Verification Progress</div>
                                    <div class="info-value">
                                        <?php echo $paidCount; ?> of <?php echo $targetPayments; ?> Installments Paid & Verified
                                        <div class="progress mt-2" style="height: 10px;">
                                            <div class="progress-bar" style="width: <?php echo min(100, round(($paidCount / $targetPayments) * 100)); ?>%; background: linear-gradient(135deg, #0B5CAD, #9B0090);"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">Showroom Redemption Voucher</div>
                                    <div class="info-value">
                                        Voucher Ref: <strong>MAT-<?php echo str_pad($subscriptionId, 6, '0', STR_PAD_LEFT); ?></strong>
                                        <div class="mt-2">
                                            <a href="maturity_certificate.php?id=<?php echo $subscriptionId; ?>" target="_blank" class="btn btn-sm" style="background: linear-gradient(135deg, #d97706, #b45309); color: white; font-weight: 600;">
                                                <i class="fas fa-file-invoice"></i> Open Official Redemption Certificate
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning mb-0" style="background: rgba(217, 119, 6, 0.08); border-color: rgba(217, 119, 6, 0.3); color: #92400e; font-size: 13.5px;">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Showroom Redemption Note:</strong>
                            <?php if ($isPlanMatured): ?>
                                This savings scheme has reached full maturity. The subscriber is eligible to redeem 100% of accumulated savings (₹<?php echo number_format($totalCorpus, 2); ?>) for furniture & electronics at Liya's Showroom.
                            <?php else: ?>
                                Plan is currently active. Once all <?php echo $targetPayments; ?> installments are verified, the full maturity voucher becomes redeemable.
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Subscription Status</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="days-remaining mb-3">
                            <?php if ($isExpired): ?>
                                <span class="text-danger">Subscription Expired</span>
                            <?php else: ?>
                                <?php echo $daysRemaining; ?> days remaining
                            <?php endif; ?>
                        </div>
                        <div class="progress mb-3" style="height: 20px;">
                            <?php
                            $totalDays = (new DateTime($subscription['StartDate']))->diff(new DateTime($subscription['EndDate']))->days;
                            $progress = $isExpired ? 100 : (($totalDays - $daysRemaining) / $totalDays) * 100;
                            ?>
                            <div class="progress-bar bg-primary" role="progressbar"
                                style="width: <?php echo $progress; ?>%"
                                aria-valuenow="<?php echo $progress; ?>"
                                aria-valuemin="0"
                                aria-valuemax="100">
                            </div>
                        </div>
                        <div class="text-muted">
                            <?php if ($isExpired): ?>
                                This subscription ended on <?php echo $endDate; ?>
                            <?php else: ?>
                                Valid until <?php echo $endDate; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>