<?php
require_once '../config/config.php';
require_once '../config/session_check.php';

// Validate customer session
$userData = checkSession();
$customerId = $userData['customer_id'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div style='font-family: sans-serif; text-align: center; padding: 50px;'>Payment ID is required. <a href='index.php'>Back to Receipts</a></div>";
    exit();
}

$paymentId = (int)$_GET['id'];

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Query payment only for this logged-in customer
    $stmt = $conn->prepare("
        SELECT p.*,
            c.Name as CustomerName, c.CustomerUniqueID, c.Contact as CustomerContact, c.Email as CustomerEmail, c.Address as CustomerAddress,
            s.SchemeName, s.MonthlyPayment,
            i.InstallmentNumber,
            a.Name as VerifierName
        FROM Payments p
        LEFT JOIN Customers c ON p.CustomerID = c.CustomerID
        LEFT JOIN Schemes s ON p.SchemeID = s.SchemeID
        LEFT JOIN Installments i ON p.InstallmentID = i.InstallmentID
        LEFT JOIN Admins a ON p.AdminID = a.AdminID
        WHERE p.PaymentID = ? AND p.CustomerID = ?
    ");
    $stmt->execute([$paymentId, $customerId]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        echo "<div style='font-family: sans-serif; text-align: center; padding: 50px;'>Receipt not found or you do not have permission to view this receipt. <a href='index.php'>Back to Receipts</a></div>";
        exit();
    }

    if ($payment['Status'] !== 'Verified') {
        echo "<div style='font-family: sans-serif; text-align: center; padding: 50px; background: #0f172a; color: #fff;'>
            <h2 style='color: #f59e0b; margin-bottom: 10px;'>Receipt Pending Verification</h2>
            <p style='color: #94a3b8; margin-bottom: 20px;'>Your payment is currently <strong>" . htmlspecialchars($payment['Status']) . "</strong>. Official payment receipts are issued automatically once payment is verified by our accounts team.</p>
            <a href='index.php' style='color: #38bdf8; text-decoration: none;'>&larr; Back to Receipts</a>
        </div>";
        exit();
    }

    $backUrl = 'index.php';
    include("../../components/receipt_voucher.php");
} catch (Exception $e) {
    echo "<div style='font-family: sans-serif; text-align: center; padding: 50px;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit();
}
