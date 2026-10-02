<?php
session_start();

$menuPath = "../";
$currentPage = "promoters";

require_once("../../config/config.php");
require_once("../middleware/auth.php");
verifyAuth();

$database = new Database();
$conn = $database->getConnection();

$promoterId = isset($_GET['id']) ? intval($_GET['id']) : (isset($_GET['delete']) ? intval($_GET['delete']) : 0);

if ($promoterId <= 0) {
    $_SESSION['error_message'] = "No valid promoter ID provided.";
    header("Location: index.php");
    exit();
}

try {
    $conn->beginTransaction();

    // Fetch promoter info
    $stmt = $conn->prepare("SELECT Name, PromoterUniqueID FROM Promoters WHERE PromoterID = ?");
    $stmt->execute([$promoterId]);
    $promoter = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$promoter) {
        $_SESSION['error_message'] = "Promoter not found.";
        header("Location: index.php");
        exit();
    }

    // Log the activity
    $action = "Deleted promoter account: {$promoter['Name']} ({$promoter['PromoterUniqueID']})";
    $stmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Admin', ?, ?)");
    $stmt->execute([$_SESSION['admin_id'], $action, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);

    // Delete promoter wallet if exists
    $stmt = $conn->prepare("DELETE FROM PromoterWallet WHERE PromoterUniqueID = ?");
    $stmt->execute([$promoter['PromoterUniqueID']]);

    // Delete promoter
    $stmt = $conn->prepare("DELETE FROM Promoters WHERE PromoterID = ?");
    $stmt->execute([$promoterId]);

    $conn->commit();
    $_SESSION['success_message'] = "Promoter '{$promoter['Name']}' deleted successfully.";

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $_SESSION['error_message'] = "Failed to delete promoter: " . $e->getMessage();
}

$redirect = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'promoterlist') !== false 
    ? '../promoterlist/index.php' 
    : 'index.php';

header("Location: " . $redirect);
exit();
