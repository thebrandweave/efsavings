<?php
require_once '../config/config.php';
require_once '../config/session_check.php';

// Validate customer session
$userData = checkSession();
$customerId = $userData['customer_id'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div style='font-family: sans-serif; text-align: center; padding: 50px;'>Subscription ID is required. <a href='index.php'>Back to Subscriptions</a></div>";
    exit();
}

$subscriptionId = (int)$_GET['id'];

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Query subscription
    $stmt = $conn->prepare("
        SELECT 
            s.*,
            c.Name as CustomerName, c.CustomerUniqueID, c.Contact, c.Email, c.Address,
            sch.SchemeName, sch.MonthlyPayment, sch.TotalPayments, sch.Description as SchemeDescription,
            (SELECT COUNT(*) FROM Payments p WHERE p.CustomerID = s.CustomerID AND p.SchemeID = s.SchemeID AND p.Status = 'Verified') as paid_installments,
            (SELECT COALESCE(SUM(p.Amount), 0) FROM Payments p WHERE p.CustomerID = s.CustomerID AND p.SchemeID = s.SchemeID AND p.Status = 'Verified') as TotalSaved
        FROM Subscriptions s
        JOIN Customers c ON s.CustomerID = c.CustomerID
        JOIN Schemes sch ON s.SchemeID = sch.SchemeID
        WHERE s.SubscriptionID = ? AND s.CustomerID = ?
    ");
    $stmt->execute([$subscriptionId, $customerId]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$subscription) {
        echo "<div style='font-family: sans-serif; text-align: center; padding: 50px;'>Subscription not found or you do not have permission to view it. <a href='index.php'>Back to Subscriptions</a></div>";
        exit();
    }

    $backUrl = 'index.php';
    include("../../components/maturity_voucher.php");
} catch (Exception $e) {
    echo "<div style='font-family: sans-serif; text-align: center; padding: 50px;'>Error loading certificate: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit();
}
