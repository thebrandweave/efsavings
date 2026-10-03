<?php
session_start();
$menuPath = "../";
require_once("../../config/config.php");
require_once("../../config/JWT.php");

// Verify admin session or JWT token
if (!isset($_SESSION['admin_id'])) {
    if (isset($_COOKIE['admin_token'])) {
        $decoded = JWTManager::verifyToken($_COOKIE['admin_token']);
        if ($decoded && isset($decoded->admin_id)) {
            $_SESSION['admin_id'] = $decoded->admin_id;
            $_SESSION['admin_email'] = $decoded->email ?? '';
            $_SESSION['admin_role'] = $decoded->role ?? '';
        } else {
            header("Location: ../login.php");
            exit();
        }
    } else {
        header("Location: ../login.php");
        exit();
    }
}

if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div style='font-family: sans-serif; text-align: center; padding: 50px;'>Payment ID is required. <a href='index.php'>Back to Payments</a></div>";
    exit();
}

$paymentId = (int)$_GET['id'];

try {
    $database = new Database();
    $conn = $database->getConnection();

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
        WHERE p.PaymentID = ?
    ");
    $stmt->execute([$paymentId]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        echo "<div style='font-family: sans-serif; text-align: center; padding: 50px;'>Payment record #$paymentId not found. <a href='index.php'>Back to Payments</a></div>";
        exit();
    }

    $backUrl = 'index.php';
    include("../../components/receipt_voucher.php");
} catch (Throwable $e) {
    echo "<div style='font-family: sans-serif; text-align: center; padding: 50px;'>Error loading receipt: " . htmlspecialchars($e->getMessage()) . " <a href='index.php'>Back to Payments</a></div>";
    exit();
}
