<?php
session_start();

$menuPath = "../../";
$currentPage = "schemes";

require_once("../../../config/config.php");
require_once("../../middleware/auth.php");
verifyAuth();

$database = new Database();
$conn = $database->getConnection();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "No installment ID provided.";
    header("Location: ../index.php");
    exit();
}

$installmentId = intval($_GET['id']);

try {
    // Fetch installment details
    $stmt = $conn->prepare("SELECT SchemeID, InstallmentNumber, InstallmentName FROM Installments WHERE InstallmentID = ?");
    $stmt->execute([$installmentId]);
    $installment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$installment) {
        $_SESSION['error_message'] = "Installment not found.";
        header("Location: ../index.php");
        exit();
    }

    $schemeId = $installment['SchemeID'];

    // Check if there are payments associated with this installment
    $stmt = $conn->prepare("SELECT COUNT(*) FROM Payments WHERE InstallmentID = ?");
    $stmt->execute([$installmentId]);
    $paymentCount = (int)$stmt->fetchColumn();

    if ($paymentCount > 0) {
        $_SESSION['error_message'] = "Cannot delete installment: {$paymentCount} payment(s) are already linked to this installment.";
        header("Location: index.php?id=" . $installmentId);
        exit();
    }

    $conn->beginTransaction();

    // Log the activity
    $action = "Deleted installment #{$installment['InstallmentNumber']} ({$installment['InstallmentName']}) for Scheme ID: {$schemeId}";
    $stmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Admin', ?, ?)");
    $stmt->execute([$_SESSION['admin_id'], $action, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);

    // Delete the installment
    $stmt = $conn->prepare("DELETE FROM Installments WHERE InstallmentID = ?");
    $stmt->execute([$installmentId]);

    $conn->commit();

    $_SESSION['success_message'] = "Installment #{$installment['InstallmentNumber']} deleted successfully.";
    header("Location: ../view.php?id=" . $schemeId);
    exit();

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $_SESSION['error_message'] = "Failed to delete installment: " . $e->getMessage();
    header("Location: index.php?id=" . $installmentId);
    exit();
}
