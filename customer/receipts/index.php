<?php
require_once '../config/config.php';
require_once '../config/session_check.php';

$c_path = "../";
$current_page = "receipts";

// Validate customer session
$userData = checkSession();
$customerId = $userData['customer_id'];

try {
    $database = new Database();
    $db = $database->getConnection();

    // Fetch verified payments for the customer
    $stmt = $db->prepare("
        SELECT 
            p.*,
            s.SchemeName,
            s.MonthlyPayment,
            i.InstallmentNumber
        FROM Payments p
        JOIN Schemes s ON p.SchemeID = s.SchemeID
        LEFT JOIN Installments i ON p.InstallmentID = i.InstallmentID
        WHERE p.CustomerID = ? AND p.Status = 'Verified'
        ORDER BY p.VerifiedAt DESC, p.SubmittedAt DESC
    ");
    $stmt->execute([$customerId]);
    $receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Summary statistics
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_receipts,
            SUM(Amount) as total_amount,
            COUNT(DISTINCT SchemeID) as total_schemes,
            MAX(COALESCE(VerifiedAt, SubmittedAt)) as latest_date
        FROM Payments
        WHERE CustomerID = ? AND Status = 'Verified'
    ");
    $stmt->execute([$customerId]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Payment Receipts | Liya's Furniture & Electronics</title>
    <link rel="icon" type="image/png" href="../uploads/liyas_logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --dark-bg: #071220;
            --card-bg: #0c1e34;
            --card-border: rgba(2, 132, 199, 0.22);
            --accent-brand: #0284c7;
            --accent-gold: #f59e0b;
            --text-primary: rgba(255, 255, 255, 0.95);
            --text-secondary: rgba(255, 255, 255, 0.7);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #071220;
            color: var(--text-primary);
            min-height: 100vh;
        }

        .main-content {
            margin-left: 250px;
            padding: 95px 25px 40px;
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 85px 15px 30px;
            }
        }

        .page-header {
            margin-bottom: 25px;
        }

        .page-header h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-header h2 i {
            color: #38bdf8;
        }

        .page-header p {
            color: var(--text-secondary);
            font-size: 14px;
            margin: 0;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .stat-icon.receipts {
            background: rgba(2, 132, 199, 0.15);
            color: #38bdf8;
            border: 1px solid rgba(2, 132, 199, 0.3);
        }

        .stat-icon.amount {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .stat-icon.schemes {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .stat-info h3 {
            font-size: 22px;
            font-weight: 800;
            margin: 0 0 4px;
            color: #fff;
        }

        .stat-info span {
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* Receipts Section Card */
        .receipts-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        }

        .receipts-card-header {
            padding: 18px 22px;
            border-bottom: 1px solid rgba(2, 132, 199, 0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .receipts-card-title {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .receipt-search {
            width: 250px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 13.5px;
            color: #fff;
            outline: none;
        }

        .receipt-search:focus {
            border-color: #0284c7;
        }

        /* Table */
        .table-responsive {
            margin: 0;
        }

        .receipts-table {
            width: 100%;
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
            color: var(--text-primary);
        }

        .receipts-table th {
            background: rgba(255, 255, 255, 0.03);
            color: #94a3b8;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 14px 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            font-weight: 600;
        }

        .receipts-table td {
            padding: 16px 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 14px;
            vertical-align: middle;
        }

        .receipts-table tr:hover td {
            background: rgba(2, 132, 199, 0.05);
        }

        .receipt-id-badge {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            background: rgba(2, 132, 199, 0.15);
            color: #38bdf8;
            border: 1px solid rgba(2, 132, 199, 0.35);
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 13px;
            display: inline-block;
        }

        .scheme-name-cell {
            font-weight: 600;
            color: #fff;
        }

        .inst-badge {
            font-size: 11.5px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .amount-cell {
            font-weight: 700;
            color: #34d399;
            font-size: 15px;
        }

        .badge-verified {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
            padding: 3px 9px;
            border-radius: 12px;
            font-size: 11.5px;
            font-weight: 600;
        }

        .btn-view-receipt {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            border: none;
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.3);
        }

        .btn-view-receipt:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.5);
            color: #ffffff;
        }

        /* Empty state */
        .empty-receipts {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-receipts i {
            font-size: 55px;
            color: rgba(2, 132, 199, 0.4);
            margin-bottom: 16px;
        }

        .empty-receipts h4 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #fff;
        }

        .empty-receipts p {
            color: var(--text-secondary);
            font-size: 14px;
            max-width: 440px;
            margin: 0 auto 20px;
        }
    </style>
</head>
<body>

    <!-- Sidebar Component -->
    <?php include '../c_includes/sidebar.php'; ?>

    <!-- Topbar Component -->
    <?php include '../c_includes/topbar.php'; ?>

    <!-- Main Content Area -->
    <div class="main-content">
        <div class="page-header">
            <h2><i class="fas fa-file-invoice-dollar"></i> Official Payment Receipts</h2>
            <p>Official verified payment receipts for your Liya's Furniture & Electronics savings schemes.</p>
        </div>

        <!-- Summary Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon receipts">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($summary['total_receipts'] ?? 0); ?></h3>
                    <span>Approved Receipts</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon amount">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="stat-info">
                    <h3>₹<?php echo number_format($summary['total_amount'] ?? 0, 2); ?></h3>
                    <span>Total Amount Paid</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon schemes">
                    <i class="fas fa-gem"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($summary['total_schemes'] ?? 0); ?></h3>
                    <span>Enrolled Schemes</span>
                </div>
            </div>
        </div>

        <!-- Receipts Table Card -->
        <div class="receipts-card">
            <div class="receipts-card-header">
                <h3 class="receipts-card-title">
                    <i class="fas fa-list-check" style="color: #38bdf8;"></i> Receipts Archive
                </h3>
                <input type="text" id="receiptSearchInput" class="receipt-search" placeholder="Search receipt or scheme...">
            </div>

            <?php if (empty($receipts)): ?>
                <div class="empty-receipts">
                    <i class="fas fa-receipt"></i>
                    <h4>No Receipts Available Yet</h4>
                    <p>Your official receipts are generated and securely stored here automatically as soon as your scheme installments are verified by our accounts desk.</p>
                    <a href="../payments/add.php" class="btn btn-view-receipt">
                        <i class="fas fa-plus-circle"></i> Submit Payment
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="receipts-table" id="receiptsTable">
                        <thead>
                            <tr>
                                <th>Receipt No.</th>
                                <th>Date Issued</th>
                                <th>Scheme & Installment</th>
                                <th>Amount</th>
                                <th>Reference / UTR</th>
                                <th>Status</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($receipts as $r): ?>
                                <tr>
                                    <td>
                                        <span class="receipt-id-badge">RCP-<?php echo str_pad($r['PaymentID'], 6, '0', STR_PAD_LEFT); ?></span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: #fff;">
                                            <?php echo date('M d, Y', strtotime($r['VerifiedAt'] ?? $r['SubmittedAt'])); ?>
                                        </div>
                                        <div style="font-size: 11.5px; color: #94a3b8;">
                                            <?php echo date('h:i A', strtotime($r['VerifiedAt'] ?? $r['SubmittedAt'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="scheme-name-cell"><?php echo htmlspecialchars($r['SchemeName']); ?></div>
                                        <div class="inst-badge">
                                            Installment #<?php echo htmlspecialchars($r['InstallmentNumber'] ?: '1'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="amount-cell">₹<?php echo number_format($r['Amount'], 2); ?></span>
                                    </td>
                                    <td>
                                        <span style="font-family: 'Courier New', monospace; font-size: 13px; color: #e2e8f0;">
                                            <?php echo htmlspecialchars($r['UTRNumber'] ?: 'Cash / Direct'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-verified">
                                            <i class="fas fa-check-circle"></i> Verified
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="receipt.php?id=<?php echo $r['PaymentID']; ?>" target="_blank" class="btn-view-receipt" title="View & Print Official Receipt">
                                            <i class="fas fa-file-invoice"></i> View Receipt
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side quick filter
        const searchInput = document.getElementById('receiptSearchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const term = this.value.toLowerCase();
                const rows = document.querySelectorAll('#receiptsTable tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                });
            });
        }
    </script>
</body>
</html>
