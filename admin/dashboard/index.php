<?php
// Maintenance check - must be first
require_once '../../utils/maintenance_check.php';

session_start();


$menuPath = "../";
$currentPage = "dashboard";

// Database connection
require_once("../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

// Get stats data for any dynamic period
function getStats($conn, $startDate = null, $endDate = null)
{
    $stats = [];

    // Set default date range if not provided
    if (!$startDate) {
        $startDate = date('Y-m-01 00:00:00');
    }
    if (!$endDate) {
        $endDate = date('Y-m-t 23:59:59');
    }

    $startTs = strtotime($startDate);
    $endTs = strtotime($endDate);
    $diffTs = max(86400, $endTs - $startTs);
    $prevEndTs = $startTs - 1;
    $prevStartTs = $prevEndTs - $diffTs;
    $prevStartDate = date('Y-m-d H:i:s', $prevStartTs);
    $prevEndDate = date('Y-m-d H:i:s', $prevEndTs);

    // Total Customers registered in this period
    $query = "SELECT COUNT(*) as total FROM Customers WHERE Status = 'Active' AND CreatedAt BETWEEN :startDate AND :endDate";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['customers'] = $result['total'] ?? 0;

    // Previous period customer count for comparison
    $query = "SELECT COUNT(*) as prev_period FROM Customers WHERE Status = 'Active' AND CreatedAt BETWEEN :startDate AND :endDate";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':startDate', $prevStartDate);
    $stmt->bindParam(':endDate', $prevEndDate);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $prevCustomers = $result['prev_period'] ?? 0;

    if ($prevCustomers > 0) {
        $stats['customers_growth'] = round((($stats['customers'] - $prevCustomers) / $prevCustomers) * 100, 1);
    } else {
        $stats['customers_growth'] = $stats['customers'] > 0 ? 100 : 0;
    }

    // Total Revenue - Only count verified payments in this period
    $query = "SELECT COALESCE(SUM(Amount), 0) as total 
              FROM Payments 
              WHERE Status = 'Verified' 
              AND SubmittedAt BETWEEN :startDate AND :endDate";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['revenue'] = $result['total'] ?? 0;

    // Previous period revenue
    $query = "SELECT COALESCE(SUM(Amount), 0) as prev_period 
              FROM Payments 
              WHERE Status = 'Verified' 
              AND SubmittedAt BETWEEN :startDate AND :endDate";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':startDate', $prevStartDate);
    $stmt->bindParam(':endDate', $prevEndDate);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $prevRevenue = $result['prev_period'] ?? 0;

    if ($prevRevenue > 0) {
        $stats['revenue_growth'] = round((($stats['revenue'] - $prevRevenue) / $prevRevenue) * 100, 1);
    } else {
        $stats['revenue_growth'] = $stats['revenue'] > 0 ? 100 : 0;
    }

    // Active Schemes (Overall active schemes count)
    $query = "SELECT COUNT(*) as total FROM Schemes WHERE Status = 'Active'";
    $stmt = $conn->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['schemes'] = $result['total'] ?? 0;

    // New schemes created in period
    $query = "SELECT COUNT(*) as new_schemes FROM Schemes WHERE Status = 'Active' AND CreatedAt BETWEEN :startDate AND :endDate";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['new_schemes'] = $result['new_schemes'] ?? 0;

    // Total Payments - Only count verified payments in this period
    $query = "SELECT COUNT(*) as total 
              FROM Payments 
              WHERE Status = 'Verified' 
              AND SubmittedAt BETWEEN :startDate AND :endDate";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['payments'] = $result['total'] ?? 0;

    // Previous period payments
    $query = "SELECT COUNT(*) as prev_period 
              FROM Payments 
              WHERE Status = 'Verified' 
              AND SubmittedAt BETWEEN :startDate AND :endDate";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':startDate', $prevStartDate);
    $stmt->bindParam(':endDate', $prevEndDate);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $prevPayments = $result['prev_period'] ?? 0;

    if ($prevPayments > 0) {
        $stats['payments_growth'] = round((($stats['payments'] - $prevPayments) / $prevPayments) * 100, 1);
    } else {
        $stats['payments_growth'] = $stats['payments'] > 0 ? 100 : 0;
    }

    return $stats;
}

// Get filtered payments with customer and scheme details
function getFilteredPayments($conn, $startDate, $endDate, $limit = 50)
{
    $query = "SELECT p.PaymentID, p.Amount, p.Status, p.SubmittedAt, p.VerifiedAt, p.UTRNumber,
                     c.Name as CustomerName, c.CustomerUniqueID, c.CustomerID,
                     s.SchemeName
              FROM Payments p 
              JOIN Customers c ON p.CustomerID = c.CustomerID 
              JOIN Schemes s ON p.SchemeID = s.SchemeID 
              WHERE p.SubmittedAt BETWEEN :startDate AND :endDate
              ORDER BY p.SubmittedAt DESC 
              LIMIT :limit";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get summary aggregates for the filtered payments period
function getFilteredPaymentsSummary($conn, $startDate, $endDate)
{
    $query = "SELECT 
                COUNT(*) as total_count,
                COALESCE(SUM(Amount), 0) as total_amount,
                SUM(CASE WHEN Status = 'Verified' THEN 1 ELSE 0 END) as verified_count,
                COALESCE(SUM(CASE WHEN Status = 'Verified' THEN Amount ELSE 0 END), 0) as verified_amount,
                SUM(CASE WHEN Status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
                COALESCE(SUM(CASE WHEN Status = 'Pending' THEN Amount ELSE 0 END), 0) as pending_amount,
                SUM(CASE WHEN Status = 'Rejected' THEN 1 ELSE 0 END) as rejected_count,
                COALESCE(SUM(CASE WHEN Status = 'Rejected' THEN Amount ELSE 0 END), 0) as rejected_amount
              FROM Payments 
              WHERE SubmittedAt BETWEEN :startDate AND :endDate";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->execute();

    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    return [
        'total_count' => (int)($res['total_count'] ?? 0),
        'total_amount' => (float)($res['total_amount'] ?? 0),
        'verified_count' => (int)($res['verified_count'] ?? 0),
        'verified_amount' => (float)($res['verified_amount'] ?? 0),
        'pending_count' => (int)($res['pending_count'] ?? 0),
        'pending_amount' => (float)($res['pending_amount'] ?? 0),
        'rejected_count' => (int)($res['rejected_count'] ?? 0),
        'rejected_amount' => (float)($res['rejected_amount'] ?? 0)
    ];
}

// Get scheme-wise breakdown for any filtered date range
function getFilteredSchemeBreakdown($conn, $startDate, $endDate)
{
    $stmt = $conn->prepare("
        SELECT 
            s.SchemeID,
            s.SchemeName,
            s.MonthlyPayment,
            COUNT(p.PaymentID) as payment_count,
            COALESCE(SUM(p.Amount), 0) as scheme_total,
            COUNT(DISTINCT p.CustomerID) as paying_customers
        FROM Schemes s
        LEFT JOIN Payments p ON s.SchemeID = p.SchemeID AND p.Status = 'Verified' AND p.SubmittedAt BETWEEN :startDate AND :endDate
        GROUP BY s.SchemeID, s.SchemeName, s.MonthlyPayment
        HAVING scheme_total > 0
        ORDER BY scheme_total DESC
    ");
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get recent payments (legacy helper fallback)
function getRecentPayments($conn, $limit = 5)
{
    $query = "SELECT p.PaymentID, p.Amount, p.Status, p.SubmittedAt, p.VerifiedAt, 
              c.Name as CustomerName, s.SchemeName 
              FROM Payments p 
              JOIN Customers c ON p.CustomerID = c.CustomerID 
              JOIN Schemes s ON p.SchemeID = s.SchemeID 
              ORDER BY p.SubmittedAt DESC 
              LIMIT :limit";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get recent activity with customer support
function getRecentActivity($conn, $limit = 7)
{
    $query = "SELECT a.Action, a.CreatedAt, a.UserType, a.UserID,
              CASE
                  WHEN a.UserType = 'Admin' THEN adm.Name
                  WHEN a.UserType = 'Promoter' THEN p.Name
                  WHEN a.UserType = 'Customer' THEN c.Name
                  ELSE 'System User'
              END as UserName
              FROM ActivityLogs a
              LEFT JOIN Admins adm ON a.UserType = 'Admin' AND a.UserID = adm.AdminID
              LEFT JOIN Promoters p ON a.UserType = 'Promoter' AND a.UserID = p.PromoterID
              LEFT JOIN Customers c ON a.UserType = 'Customer' AND a.UserID = c.CustomerID
              ORDER BY a.CreatedAt DESC 
              LIMIT :limit";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Comprehensive Collections Report Function (Daily / Monthly / Overall)
function getCollectionsReport($conn, $dailyDate = null, $monthlyDate = null)
{
    if (!$dailyDate) {
        $dailyDate = date('Y-m-d');
    }
    if (!$monthlyDate) {
        $monthlyDate = date('Y-m');
    }

    $yesterdayDate = date('Y-m-d', strtotime($dailyDate . ' -1 day'));
    $monthStart = $monthlyDate . '-01';
    $monthEnd = date('Y-m-t', strtotime($monthStart));
    $prevMonthStart = date('Y-m-01', strtotime($monthStart . ' -1 month'));
    $prevMonthEnd = date('Y-m-t', strtotime($monthStart . ' -1 month'));

    $report = [];

    // 1. DAILY COLLECTIONS (Selected Date)
    $stmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(Amount), 0) as total_amount,
            COUNT(*) as total_count
        FROM Payments 
        WHERE Status = 'Verified' AND DATE(SubmittedAt) = ?
    ");
    $stmt->execute([$dailyDate]);
    $report['daily_verified'] = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(Amount), 0) as pending_amount,
            COUNT(*) as pending_count
        FROM Payments 
        WHERE Status = 'Pending' AND DATE(SubmittedAt) = ?
    ");
    $stmt->execute([$dailyDate]);
    $report['daily_pending'] = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(Amount), 0) as total_amount,
            COUNT(*) as total_count
        FROM Payments 
        WHERE Status = 'Verified' AND DATE(SubmittedAt) = ?
    ");
    $stmt->execute([$yesterdayDate]);
    $report['yesterday_verified'] = $stmt->fetch(PDO::FETCH_ASSOC);

    $todayAmt = (float)$report['daily_verified']['total_amount'];
    $yestAmt = (float)$report['yesterday_verified']['total_amount'];
    if ($yestAmt > 0) {
        $report['daily_growth'] = round((($todayAmt - $yestAmt) / $yestAmt) * 100, 1);
    } else {
        $report['daily_growth'] = $todayAmt > 0 ? 100 : 0;
    }

    // Daily payments list
    $stmt = $conn->prepare("
        SELECT p.PaymentID, p.Amount, p.Status, p.SubmittedAt, p.UTRNumber,
               c.Name as CustomerName, c.CustomerUniqueID, s.SchemeName
        FROM Payments p
        JOIN Customers c ON p.CustomerID = c.CustomerID
        JOIN Schemes s ON p.SchemeID = s.SchemeID
        WHERE DATE(p.SubmittedAt) = ?
        ORDER BY p.SubmittedAt DESC
    ");
    $stmt->execute([$dailyDate]);
    $report['daily_list'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. MONTHLY COLLECTIONS (Selected Month)
    $stmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(Amount), 0) as total_amount,
            COUNT(*) as total_count,
            COUNT(DISTINCT CustomerID) as unique_customers
        FROM Payments 
        WHERE Status = 'Verified' AND SubmittedAt BETWEEN ? AND ?
    ");
    $stmt->execute([$monthStart . ' 00:00:00', $monthEnd . ' 23:59:59']);
    $report['monthly_verified'] = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(Amount), 0) as total_amount,
            COUNT(*) as total_count
        FROM Payments 
        WHERE Status = 'Verified' AND SubmittedAt BETWEEN ? AND ?
    ");
    $stmt->execute([$prevMonthStart . ' 00:00:00', $prevMonthEnd . ' 23:59:59']);
    $report['prev_month_verified'] = $stmt->fetch(PDO::FETCH_ASSOC);

    $currMonthAmt = (float)$report['monthly_verified']['total_amount'];
    $prevMonthAmt = (float)$report['prev_month_verified']['total_amount'];
    if ($prevMonthAmt > 0) {
        $report['monthly_growth'] = round((($currMonthAmt - $prevMonthAmt) / $prevMonthAmt) * 100, 1);
    } else {
        $report['monthly_growth'] = $currMonthAmt > 0 ? 100 : 0;
    }

    // Monthly day-by-day collection breakdown
    $stmt = $conn->prepare("
        SELECT 
            DATE(SubmittedAt) as collection_date,
            COUNT(*) as payment_count,
            COALESCE(SUM(Amount), 0) as daily_total
        FROM Payments 
        WHERE Status = 'Verified' AND SubmittedAt BETWEEN ? AND ?
        GROUP BY DATE(SubmittedAt)
        ORDER BY collection_date DESC
    ");
    $stmt->execute([$monthStart . ' 00:00:00', $monthEnd . ' 23:59:59']);
    $report['monthly_daily_breakdown'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Monthly Scheme-wise breakdown
    $stmt = $conn->prepare("
        SELECT 
            s.SchemeName,
            COUNT(p.PaymentID) as payment_count,
            COALESCE(SUM(p.Amount), 0) as scheme_total
        FROM Schemes s
        JOIN Payments p ON s.SchemeID = p.SchemeID AND p.Status = 'Verified' AND p.SubmittedAt BETWEEN ? AND ?
        GROUP BY s.SchemeID, s.SchemeName
        ORDER BY scheme_total DESC
    ");
    $stmt->execute([$monthStart . ' 00:00:00', $monthEnd . ' 23:59:59']);
    $report['monthly_scheme_breakdown'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. OVERALL COLLECTIONS (All Time)
    $stmt = $conn->query("
        SELECT 
            COALESCE(SUM(Amount), 0) as total_amount,
            COUNT(*) as total_count,
            COUNT(DISTINCT CustomerID) as total_customers,
            COUNT(DISTINCT SchemeID) as total_schemes
        FROM Payments 
        WHERE Status = 'Verified'
    ");
    $report['overall_verified'] = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->query("
        SELECT 
            COALESCE(SUM(Amount), 0) as pending_amount,
            COUNT(*) as pending_count
        FROM Payments 
        WHERE Status = 'Pending'
    ");
    $report['overall_pending'] = $stmt->fetch(PDO::FETCH_ASSOC);

    // Scheme-wise Overall Breakdown
    $stmt = $conn->query("
        SELECT 
            s.SchemeID,
            s.SchemeName,
            s.MonthlyPayment,
            COUNT(p.PaymentID) as payment_count,
            COALESCE(SUM(p.Amount), 0) as scheme_total,
            COUNT(DISTINCT p.CustomerID) as paying_customers
        FROM Schemes s
        LEFT JOIN Payments p ON s.SchemeID = p.SchemeID AND p.Status = 'Verified'
        GROUP BY s.SchemeID, s.SchemeName, s.MonthlyPayment
        ORDER BY scheme_total DESC
    ");
    $report['overall_scheme_breakdown'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $report;
}

// Get today's registered customers
function getTodayCustomers($conn, $date = null)
{
    if (!$date) {
        $date = date('Y-m-d');
    }

    $query = "SELECT CustomerID, CustomerUniqueID, Name, Contact, Email, CreatedAt 
              FROM Customers 
              WHERE DATE(CreatedAt) = :date 
              ORDER BY CreatedAt DESC";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':date', $date);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get today's payments
function getTodayPayments($conn, $date = null)
{
    if (!$date) {
        $date = date('Y-m-d');
    }
    $query = "SELECT p.PaymentID, p.Amount, p.Status, p.SubmittedAt, 
                     c.Name as CustomerName, s.SchemeName
              FROM Payments p
              JOIN Customers c ON p.CustomerID = c.CustomerID
              JOIN Schemes s ON p.SchemeID = s.SchemeID
              WHERE DATE(p.SubmittedAt) = :date
              ORDER BY p.SubmittedAt DESC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':date', $date);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get today's payments totals
function getTodayPaymentsTotals($conn, $date = null)
{
    if (!$date) {
        $date = date('Y-m-d');
    }
    $query = "SELECT COUNT(*) as total_count, COALESCE(SUM(Amount),0) as total_amount FROM Payments WHERE DATE(SubmittedAt) = :date";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':date', $date);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Determine active period filter
// Options: 'today' (this day), 'month' (this month), 'year' (this year), 'custom' (from date to date)
$activePeriod = $_GET['period'] ?? null;
if (!$activePeriod) {
    if (isset($_GET['payment_date']) && !isset($_GET['month'])) {
        $activePeriod = 'today';
    } elseif (isset($_GET['from_date']) && isset($_GET['to_date'])) {
        $activePeriod = 'custom';
    } elseif (isset($_GET['year']) && !isset($_GET['month'])) {
        $activePeriod = 'year';
    } else {
        $activePeriod = 'month';
    }
}
if (!in_array($activePeriod, ['today', 'month', 'year', 'custom', 'overall'])) {
    $activePeriod = 'month';
}

$todayStr = date('Y-m-d');
$currentMonthStr = date('Y-m');
$currentYearStr = date('Y');

$selectedDay = !empty($_GET['day']) ? $_GET['day'] : (!empty($_GET['payment_date']) ? $_GET['payment_date'] : $todayStr);
$selectedMonth = !empty($_GET['month']) ? $_GET['month'] : $currentMonthStr;
$selectedYear = !empty($_GET['year']) ? (int)$_GET['year'] : (int)$currentYearStr;
$fromDate = !empty($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$toDate = !empty($_GET['to_date']) ? $_GET['to_date'] : $todayStr;

// Validate formats
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDay)) {
    $selectedDay = $todayStr;
}
if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
    $selectedMonth = $currentMonthStr;
}
if ($selectedYear < 2020 || $selectedYear > 2035) {
    $selectedYear = (int)$currentYearStr;
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) {
    $fromDate = date('Y-m-01');
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
    $toDate = $todayStr;
}
if ($fromDate > $toDate) {
    $temp = $fromDate;
    $fromDate = $toDate;
    $toDate = $temp;
}

$selectedCustomerDate = isset($_GET['customer_date']) ? $_GET['customer_date'] : $todayStr;
$selectedDate = $selectedCustomerDate;
$selectedPaymentDate = $selectedDay;

switch ($activePeriod) {
    case 'today':
        $filterStartDate = $selectedDay . ' 00:00:00';
        $filterEndDate = $selectedDay . ' 23:59:59';
        $periodLabel = ($selectedDay === $todayStr) ? 'Today (' . date('d M Y', strtotime($selectedDay)) . ')' : date('d M Y (D)', strtotime($selectedDay));
        $statPeriodText = ($selectedDay === $todayStr) ? 'today' : 'on ' . date('d M', strtotime($selectedDay));
        break;

    case 'year':
        $filterStartDate = $selectedYear . '-01-01 00:00:00';
        $filterEndDate = $selectedYear . '-12-31 23:59:59';
        $periodLabel = 'Year ' . $selectedYear;
        $statPeriodText = 'in ' . $selectedYear;
        break;

    case 'custom':
        $filterStartDate = $fromDate . ' 00:00:00';
        $filterEndDate = $toDate . ' 23:59:59';
        $periodLabel = date('d M Y', strtotime($fromDate)) . ' to ' . date('d M Y', strtotime($toDate));
        $statPeriodText = 'in selected range';
        break;

    case 'overall':
        $filterStartDate = '2020-01-01 00:00:00';
        $filterEndDate = '2035-12-31 23:59:59';
        $periodLabel = 'All-Time Overall';
        $statPeriodText = 'all-time';
        break;

    case 'month':
    default:
        $activePeriod = 'month';
        $filterStartDate = $selectedMonth . '-01 00:00:00';
        $filterEndDate = date('Y-m-t 23:59:59', strtotime($filterStartDate));
        $periodLabel = date('F Y', strtotime($filterStartDate));
        $statPeriodText = 'this month';
        break;
}

// Pre-initialize month navigation
$currentMonthDate = new DateTime($selectedMonth . '-01');
$prevMonth = (clone $currentMonthDate)->modify('-1 month')->format('Y-m');
$nextMonth = (clone $currentMonthDate)->modify('+1 month')->format('Y-m');
$dateRangeText = $periodLabel;

// Try to fetch stats with date range
try {
    $stats = getStats($conn, $filterStartDate, $filterEndDate);
    $filteredPayments = getFilteredPayments($conn, $filterStartDate, $filterEndDate, 50);
    $filteredPaymentsSummary = getFilteredPaymentsSummary($conn, $filterStartDate, $filterEndDate);
    $filteredSchemeBreakdown = getFilteredSchemeBreakdown($conn, $filterStartDate, $filterEndDate);
    $recentPayments = $filteredPayments;
    $recentActivity = getRecentActivity($conn);

    // Collections Report (Daily, Monthly, Overall)
    $collectionsReport = getCollectionsReport($conn, $selectedPaymentDate, $selectedMonth);

    // Get today's customers
    $todayCustomers = getTodayCustomers($conn, $selectedCustomerDate);
    // Get today's payments
    $todayPayments = getTodayPayments($conn, $selectedPaymentDate);
    $todayPaymentsTotals = getTodayPaymentsTotals($conn, $selectedPaymentDate);
} catch (PDOException $e) {
    // If tables don't exist yet, use sample data
    $stats = [
        'customers' => 0,
        'customers_growth' => 0,
        'revenue' => 0,
        'revenue_growth' => 0,
        'schemes' => 0,
        'new_schemes' => 0,
        'payments' => 0,
        'payments_growth' => 0
    ];
    $recentPayments = [];
    $filteredPayments = [];
    $filteredPaymentsSummary = ['total_count' => 0, 'total_amount' => 0, 'verified_count' => 0, 'verified_amount' => 0, 'pending_count' => 0, 'pending_amount' => 0, 'rejected_count' => 0, 'rejected_amount' => 0];
    $filteredSchemeBreakdown = [];
    $recentActivity = [];
    $todayCustomers = [];
    $todayPayments = [];
    $todayPaymentsTotals = ['total_count' => 0, 'total_amount' => 0];

    // If in development, show the error
    if (ini_get('display_errors')) {
        echo "<div style='color:red; padding:10px; background:#ffeeee; border:1px solid #ff0000;'>";
        echo "Database Error: " . $e->getMessage();
        echo "<br>Note: This error is only shown because display_errors is enabled.";
        echo "</div>";
    }
}

// Format revenue for display
function formatAmount($amount)
{
    if ($amount >= 100000) {
        return '₹' . number_format($amount / 100000, 1) . 'L';
    } elseif ($amount >= 1000) {
        return '₹' . number_format($amount / 1000, 1) . 'K';
    } else {
        return '₹' . number_format($amount, 0);
    }
}

// Get initials from name
function getInitials($name)
{
    $words = explode(' ', $name);
    $initials = '';

    if (count($words) >= 2) {
        $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    } else {
        $initials = strtoupper(substr($name, 0, 2));
    }

    return $initials;
}

// Format date for display
function formatDate($date)
{
    if (empty($date)) return '-';
    $datetime = new DateTime($date);
    return $datetime->format('M d, Y');
}

// Format activity message
function formatActivity($action, $userName)
{
    $actionLower = strtolower($action);

    if (strpos($actionLower, 'customer') !== false && strpos($actionLower, 'add') !== false) {
        return "<strong>New customer</strong> added by {$userName}";
    } elseif (strpos($actionLower, 'customer') !== false && strpos($actionLower, 'edit') !== false) {
        return "<strong>Customer updated</strong> by {$userName}";
    } elseif (strpos($actionLower, 'payment') !== false && strpos($actionLower, 'verify') !== false) {
        return "<strong>Payment verified</strong> by {$userName}";
    } elseif (strpos($actionLower, 'payment') !== false && strpos($actionLower, 'reject') !== false) {
        return "<strong>Payment rejected</strong> by {$userName}";
    } elseif (strpos($actionLower, 'promoter') !== false && strpos($actionLower, 'add') !== false) {
        return "<strong>New promoter</strong> added by {$userName}";
    } elseif (strpos($actionLower, 'scheme') !== false && strpos($actionLower, 'add') !== false) {
        return "<strong>New scheme</strong> created by {$userName}";
    } elseif (strpos($actionLower, 'winner') !== false) {
        return "<strong>Winner announced</strong> by {$userName}";
    } else {
        return "<strong>{$action}</strong> by {$userName}";
    }
}

// Convert time to "X time ago" format
function timeAgo($datetime)
{
    if (empty($datetime)) return '-';
    $now = new DateTime();
    $ago = new DateTime($datetime);
    
    $diffSeconds = $now->getTimestamp() - $ago->getTimestamp();
    if ($diffSeconds < 60 && $diffSeconds >= -30) {
        return 'Just now';
    }

    $diff = $now->diff($ago);

    if ($diff->y > 0) {
        return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    } elseif ($diff->m > 0) {
        return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    } elseif ($diff->d > 0) {
        if ($diff->d == 1) {
            return 'Yesterday';
        }
        return $diff->d . ' days ago';
    } elseif ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    } elseif ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    } else {
        return 'Just now';
    }
}

// Add a new function to format time with IST
function formatTime($datetime)
{
    if (empty($datetime)) return '-';
    $date = new DateTime($datetime);
    return $date->format('h:i A');
}

// Add a new function to format datetime with IST
function formatDateTime($datetime)
{
    if (empty($datetime)) return '-';
    $date = new DateTime($datetime);
    return $date->format('M d, Y h:i A');
}

// Get Admin info (normally would come from session)
$adminName = $_SESSION['admin_name'] ?? 'Admin User';
$adminRole = $_SESSION['admin_role'] ?? 'Administrator';

// Include header and sidebar
include("../components/sidebar.php");
include("../components/topbar.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Liya's Furniture & Electronics</title>
    <link rel="icon" type="image/png" href="../../landing/landing_assets/images/liyas_logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        /* Fix for layout collisions and improve responsiveness */
        .dashboard-container {
            padding: 20px;
            max-width: 100%;
        }

        /* Ensure proper spacing between each section */
        .recent-payments {
            margin-bottom: 30px;
            clear: both;
            /* Prevent any floating elements from affecting layout */
            overflow: hidden;
            /* Contain any overflowing content */
        }

        /* Make the payments table more responsive */
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            overflow-x: auto;
            display: block;
            max-width: 100%;
        }

        @media (min-width: 992px) {
            .payments-table {
                display: table;
            }
        }

        /* Make sure table cells don't shrink too much */
        .payments-table th,
        .payments-table td {
            min-width: 100px;
            padding: 12px 15px;
            text-align: left;
            white-space: nowrap;
        }

        /* Customer cell can be more flexible */
        .payments-table td:first-child {
            min-width: 150px;
        }

        /* Add horizontal scrolling for the table on small screens */
        @media (max-width: 768px) {
            .recent-payments {
                overflow-x: auto;
            }
        }

        /* Fix bottom section layout */
        .bottom-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
            margin-bottom: 25px;
            clear: both;
        }

        /* Ensure content wrapper follows a proper box model */
        .content-wrapper {
            box-sizing: border-box;
            padding: 15px;
        }

        /* Add a clearfix for any floating elements */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .date-range {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .date-nav-btn {
            color: var(--primary-color);
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .date-nav-btn:hover {
            background: rgba(58, 123, 213, 0.1);
            transform: scale(1.1);
        }

        .current-month {
            font-size: 16px;
            font-weight: 500;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .current-month i {
            color: var(--primary-color);
        }

        /* Today's Customers Section Styles */
        .today-customers {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--secondary-color);
            margin: 0;
        }

        .date-filter input[type="date"] {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            color: var(--secondary-color);
        }

        .customers-table {
            width: 100%;
            border-collapse: collapse;
        }

        .customers-table th,
        .customers-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .customers-table th {
            font-weight: 600;
            color: var(--secondary-color);
            background: #f8f9fa;
        }

        .customers-table .customer-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .customers-table .customer-avatar {
            width: 32px;
            height: 32px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .section-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }

            .customers-table {
                display: block;
                overflow-x: auto;
            }
        }

        /* Today's Payments Section Styles */
        .today-payments {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .today-payments .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .today-payments .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--secondary-color);
            margin: 0;
        }

        .today-payments .date-filter input[type="date"] {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            color: var(--secondary-color);
        }

        .today-payments .payments-table {
            width: 100%;
            border-collapse: collapse;
        }

        .today-payments .payments-table th,
        .today-payments .payments-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .today-payments .payments-table th {
            font-weight: 600;
            color: var(--secondary-color);
            background: #f8f9fa;
        }

        .today-payments .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .today-payments .section-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }

            .today-payments .payments-table {
                display: block;
                overflow-x: auto;
            }
        }

        /* Collections Report Hub Styles */
        .collections-report-section {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            margin: 25px 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        .collections-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .collections-title {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        .collections-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .collection-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px 22px;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
            transition: all 0.25s ease;
        }

        .collection-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.07);
        }

        .collection-card.card-daily { border-top: 4px solid #0B5CAD; }
        .collection-card.card-monthly { border-top: 4px solid #9B0090; }
        .collection-card.card-overall { border-top: 4px solid #d97706; }

        .collection-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .collection-card-label {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #64748b;
        }

        .collection-card-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .card-daily .collection-card-icon { background: rgba(11, 92, 173, 0.12); color: #0B5CAD; }
        .card-monthly .collection-card-icon { background: rgba(155, 0, 144, 0.12); color: #9B0090; }
        .card-overall .collection-card-icon { background: rgba(217, 119, 6, 0.12); color: #d97706; }

        .collection-amount {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }

        .collection-sub {
            font-size: 13px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .growth-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
        }

        .growth-pos { background: rgba(16, 185, 129, 0.12); color: #059669; }
        .growth-neg { background: rgba(239, 68, 68, 0.12); color: #dc2626; }
        .growth-neutral { background: rgba(100, 116, 139, 0.12); color: #475569; }

        /* Report Tabs Navigation */
        .report-tabs-nav {
            display: flex;
            gap: 10px;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 20px;
            overflow-x: auto;
        }

        .report-tab-btn {
            background: transparent;
            border: none;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .report-tab-btn:hover {
            color: #0B5CAD;
        }

        .report-tab-btn.active {
            color: #9B0090;
            border-bottom-color: #9B0090;
        }

        .report-tab-pane {
            display: none;
        }

        .report-tab-pane.active {
            display: block;
        }

        .share-bar {
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 4px;
        }

        .share-fill {
            height: 100%;
            background: linear-gradient(135deg, #9B0090, #0B5CAD);
            border-radius: 3px;
        }

        /* ========================================================
           PREMIUM PAYMENTS OVERVIEW & PERIOD FILTER STYLES
           ======================================================== */
        .payments-overview-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 24px;
            margin: 25px 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        .payments-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .payments-header-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #9B0090 0%, #0B5CAD 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(155, 0, 144, 0.25);
        }

        .payments-section-title {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .payments-section-subtitle {
            font-size: 13px;
            color: #64748b;
            display: block;
            margin-top: 2px;
        }

        .view-all-link {
            background: #f8fafc;
            color: #0B5CAD;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 7px 14px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .view-all-link:hover {
            background: #0B5CAD;
            color: #ffffff;
            border-color: #0B5CAD;
        }

        /* Filter Segmented Pills */
        .filter-segmented-bar {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .filter-pill-btn {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            padding: 9px 18px;
            border-radius: 30px;
            font-size: 13.5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .filter-pill-btn:hover {
            background: #eef2ff;
            color: #0B5CAD;
            border-color: #cbd5e1;
        }

        .filter-pill-btn.active {
            background: linear-gradient(135deg, #9B0090 0%, #0B5CAD 100%);
            color: #ffffff;
            border-color: transparent;
            box-shadow: 0 4px 14px rgba(155, 0, 144, 0.3);
        }

        /* Dynamic Controls Tray */
        .filter-controls-tray {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 18px;
            margin-bottom: 22px;
        }

        .filter-tray-label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .filter-tray-input,
        .filter-tray-select {
            padding: 7px 13px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 13.5px;
            color: #0f172a;
            background: #ffffff;
            outline: none;
            font-family: inherit;
            transition: all 0.2s ease;
        }

        .filter-tray-input:focus,
        .filter-tray-select:focus {
            border-color: #9B0090;
            box-shadow: 0 0 0 3px rgba(155, 0, 144, 0.1);
        }

        .filter-tray-hint {
            font-size: 12px;
            color: #64748b;
            font-style: italic;
            margin-left: 6px;
        }

        .btn-tray-reset {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #64748b;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s ease;
        }

        .btn-tray-reset:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .btn-tray-apply {
            background: linear-gradient(135deg, #9B0090, #0B5CAD);
            color: #ffffff;
            border: none;
            border-radius: 7px;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 3px 10px rgba(11, 92, 173, 0.25);
            transition: all 0.2s ease;
        }

        .btn-tray-apply:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(11, 92, 173, 0.35);
        }

        /* 4 KPI Summary Chips */
        .kpi-chips-strip {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }

        .kpi-chip {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
        }

        .kpi-chip:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }

        .kpi-chip-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }

        .chip-icon-verified { background: rgba(16, 185, 129, 0.12); color: #059669; }
        .chip-icon-total { background: rgba(11, 92, 173, 0.12); color: #0B5CAD; }
        .chip-icon-pending { background: rgba(217, 119, 6, 0.12); color: #d97706; }
        .chip-icon-range { background: rgba(155, 0, 144, 0.12); color: #9B0090; }

        .kpi-chip-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 2px;
        }

        .kpi-chip-value {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }

        .kpi-chip-sub {
            font-size: 11.5px;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* Badges & Elements in Table */
        .customer-avatar-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #9B0090, #0B5CAD);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .customer-uid-tag {
            font-size: 11px;
            background: #f1f5f9;
            color: #475569;
            padding: 1px 6px;
            border-radius: 4px;
            font-weight: 600;
            font-family: monospace;
            display: inline-block;
        }

        .scheme-name-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            color: #1e293b;
        }

        .payment-amount-text {
            font-weight: 800;
            color: #0f172a;
            font-size: 14.5px;
        }

        .utr-code-badge {
            background: #f1f5f9;
            color: #334155;
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 12px;
            border: 1px solid #e2e8f0;
            font-family: monospace;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-verified .status-dot { background: #059669; }
        .status-pending .status-dot { background: #d97706; }
        .status-rejected .status-dot { background: #dc2626; }

        .receipt-action-btn {
            background: rgba(155, 0, 144, 0.1);
            color: #9B0090;
            border: 1px solid rgba(155, 0, 144, 0.25);
            padding: 5px 11px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s ease;
        }

        .receipt-action-btn:hover {
            background: #9B0090;
            color: #ffffff;
        }

        .review-action-btn {
            background: rgba(11, 92, 173, 0.1);
            color: #0B5CAD;
            border: 1px solid rgba(11, 92, 173, 0.25);
            padding: 5px 11px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s ease;
        }

        .review-action-btn:hover {
            background: #0B5CAD;
            color: #ffffff;
        }

        /* Empty State */
        .payments-empty-state {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #94a3b8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 14px;
        }

        .empty-title {
            font-size: 16px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 6px;
        }

        .empty-desc {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 16px;
        }

        .empty-btns {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-empty-reset {
            background: #9B0090;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 7px 14px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-empty-all {
            background: #f8fafc;
            color: #0B5CAD;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 7px 14px;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-toggle-details {
            background: #f8fafc;
            color: #0B5CAD;
            border: 1px solid #cbd5e1;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-toggle-details:hover {
            background: #0B5CAD;
            color: #ffffff;
            border-color: #0B5CAD;
        }

        .btn-toggle-details.active {
            background: #9B0090;
            color: #ffffff;
            border-color: #9B0090;
        }

        .scheme-contribution-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 20px;
        }

        .scheme-contribution-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .scheme-pill-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 10px;
            transition: all 0.2s ease;
        }

        .scheme-pill-item:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>

<body class="">
    <div class="content-wrapper">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h1 class="dashboard-title">Dashboard Overview</h1>
                        <p class="text-muted mb-0" style="font-size: 13px;">Overview and payments monitor for <?php echo htmlspecialchars($periodLabel); ?></p>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="date-range">
                            <?php if ($activePeriod === 'month'): ?>
                                <a href="?period=month&month=<?php echo $prevMonth; ?>" class="date-nav-btn" title="Previous Month">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            <span class="current-month">
                                <i class="fas fa-calendar-alt"></i>
                                <?php echo htmlspecialchars($periodLabel); ?>
                            </span>
                            <?php if ($activePeriod === 'month'): ?>
                                <a href="?period=month&month=<?php echo $nextMonth; ?>" class="date-nav-btn" title="Next Month">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        <a href="#payments-section" class="btn btn-sm" style="border: 1px solid #9B0090; color: #9B0090; border-radius: 8px; font-weight: 600; padding: 7px 14px; background: white; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fas fa-filter"></i> Filter Payments
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon customers-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-title">Total Customers</div>
                    <div class="stat-value"><?php echo number_format($stats['customers']); ?></div>
                    <div class="stat-change <?php echo $stats['customers_growth'] >= 0 ? 'positive-change' : 'negative-change'; ?>">
                        <i class="fas fa-arrow-<?php echo $stats['customers_growth'] >= 0 ? 'up' : 'down'; ?>"></i>
                        <span><?php echo abs($stats['customers_growth']); ?>% <?php echo $statPeriodText; ?></span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon revenue-icon">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <div class="stat-title">Total Revenue</div>
                    <div class="stat-value"><?php echo formatAmount($stats['revenue']); ?></div>
                    <div class="stat-change <?php echo $stats['revenue_growth'] >= 0 ? 'positive-change' : 'negative-change'; ?>">
                        <i class="fas fa-arrow-<?php echo $stats['revenue_growth'] >= 0 ? 'up' : 'down'; ?>"></i>
                        <span><?php echo abs($stats['revenue_growth']); ?>% <?php echo $statPeriodText; ?></span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon schemes-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div class="stat-title">Active Schemes</div>
                    <div class="stat-value"><?php echo $stats['schemes']; ?></div>
                    <div class="stat-change positive-change">
                        <i class="fas fa-arrow-up"></i>
                        <span><?php echo $stats['new_schemes']; ?> new <?php echo $statPeriodText; ?></span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon payments-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="stat-title">New Payments</div>
                    <div class="stat-value"><?php echo number_format($stats['payments']); ?></div>
                    <div class="stat-change <?php echo $stats['payments_growth'] >= 0 ? 'positive-change' : 'negative-change'; ?>">
                        <i class="fas fa-arrow-<?php echo $stats['payments_growth'] >= 0 ? 'up' : 'down'; ?>"></i>
                        <span><?php echo abs($stats['payments_growth']); ?>% <?php echo $statPeriodText; ?></span>
                    </div>
                </div>
            </div>

            <!-- Today's Customers Section -->
            <div class="today-customers">
                <div class="section-header">
                    <h3 class="section-title">Today's Registered Customers</h3>
                    <div class="date-filter">
                        <form method="GET" class="date-form">
                            <input type="hidden" name="month" value="<?php echo $selectedMonth; ?>">
                            <input type="date" name="customer_date" value="<?php echo $selectedDate; ?>" onchange="this.form.submit()">
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="customers-table">
                        <thead>
                            <tr>
                                <th>Customer ID</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Registration Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($todayCustomers) > 0): ?>
                                <?php foreach ($todayCustomers as $customer): ?>
                                    <tr>
                                        <td><?php echo $customer['CustomerUniqueID']; ?></td>
                                        <td>
                                            <div class="customer-cell">
                                                <div class="customer-avatar">
                                                    <?php echo getInitials($customer['Name']); ?>
                                                </div>
                                                <?php echo htmlspecialchars($customer['Name']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($customer['Contact']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['Email']); ?></td>
                                        <td><?php echo formatDateTime($customer['CreatedAt']); ?></td>
                                        <td>
                                            <a href="<?php echo $menuPath; ?>customers/view.php?id=<?php echo $customer['CustomerID']; ?>" class="action-btn custom-tooltip" data-tooltip="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="no-data">No customers registered on this date</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ========================================================
                 COLLECTIONS REPORTS HUB (Daily / Monthly / Overall)
                 ======================================================== -->
            <div class="collections-report-section">
                <div class="collections-header">
                    <h2 class="collections-title">
                        <i class="fas fa-file-invoice-dollar"></i> Collections Report
                    </h2>
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted small">
                            <i class="fas fa-sync-alt text-primary"></i> Real-time Collection Analytics
                        </span>
                    </div>
                </div>

                <!-- 3 Highlight Overview Cards -->
                <div class="collections-cards-grid">
                    <!-- 1. Daily Collection Card -->
                    <div class="collection-card card-daily">
                        <div class="collection-card-header">
                            <span class="collection-card-label">Daily Collection (<?php echo date('d M Y', strtotime($selectedPaymentDate)); ?>)</span>
                            <div class="collection-card-icon">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                        </div>
                        <div class="collection-amount">
                            ₹<?php echo number_format($collectionsReport['daily_verified']['total_amount'], 2); ?>
                        </div>
                        <div class="collection-sub">
                            <span><strong><?php echo $collectionsReport['daily_verified']['total_count']; ?></strong> verified payments</span>
                            <?php if ($collectionsReport['daily_growth'] > 0): ?>
                                <span class="growth-badge growth-pos">
                                    <i class="fas fa-arrow-up"></i> +<?php echo $collectionsReport['daily_growth']; ?>% vs yesterday
                                </span>
                            <?php elseif ($collectionsReport['daily_growth'] < 0): ?>
                                <span class="growth-badge growth-neg">
                                    <i class="fas fa-arrow-down"></i> <?php echo $collectionsReport['daily_growth']; ?>% vs yesterday
                                </span>
                            <?php else: ?>
                                <span class="growth-badge growth-neutral">Same as yesterday</span>
                            <?php endif; ?>
                        </div>
                        <?php if ((int)$collectionsReport['daily_pending']['pending_count'] > 0): ?>
                            <div style="margin-top: 8px; font-size: 12px; color: #d97706;">
                                <i class="fas fa-clock"></i> <?php echo $collectionsReport['daily_pending']['pending_count']; ?> pending verification (₹<?php echo number_format($collectionsReport['daily_pending']['pending_amount'], 2); ?>)
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- 2. Monthly Collection Card -->
                    <div class="collection-card card-monthly">
                        <div class="collection-card-header">
                            <span class="collection-card-label">Monthly Collection (<?php echo date('F Y', strtotime($selectedMonth . '-01')); ?>)</span>
                            <div class="collection-card-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                        <div class="collection-amount">
                            ₹<?php echo number_format($collectionsReport['monthly_verified']['total_amount'], 2); ?>
                        </div>
                        <div class="collection-sub">
                            <span><strong><?php echo $collectionsReport['monthly_verified']['total_count']; ?></strong> payments (<?php echo $collectionsReport['monthly_verified']['unique_customers']; ?> subscribers)</span>
                            <?php if ($collectionsReport['monthly_growth'] > 0): ?>
                                <span class="growth-badge growth-pos">
                                    <i class="fas fa-arrow-up"></i> +<?php echo $collectionsReport['monthly_growth']; ?>% vs last mo.
                                </span>
                            <?php elseif ($collectionsReport['monthly_growth'] < 0): ?>
                                <span class="growth-badge growth-neg">
                                    <i class="fas fa-arrow-down"></i> <?php echo $collectionsReport['monthly_growth']; ?>% vs last mo.
                                </span>
                            <?php else: ?>
                                <span class="growth-badge growth-neutral">Consistent with last mo.</span>
                            <?php endif; ?>
                        </div>
                        <div style="margin-top: 8px; font-size: 12px; color: #64748b;">
                            <i class="fas fa-chart-line"></i> Daily Average: ₹<?php echo number_format((float)$collectionsReport['monthly_verified']['total_amount'] / max(1, (int)date('d')), 0); ?> / day
                        </div>
                    </div>

                    <!-- 3. Overall Collection Card -->
                    <div class="collection-card card-overall">
                        <div class="collection-card-header">
                            <span class="collection-card-label">Overall Collection (All-Time)</span>
                            <div class="collection-card-icon">
                                <i class="fas fa-vault"></i>
                            </div>
                        </div>
                        <div class="collection-amount">
                            ₹<?php echo number_format($collectionsReport['overall_verified']['total_amount'], 2); ?>
                        </div>
                        <div class="collection-sub">
                            <span><strong><?php echo number_format($collectionsReport['overall_verified']['total_count']); ?></strong> total verified transactions</span>
                        </div>
                        <div style="margin-top: 8px; font-size: 12px; color: #64748b; display: flex; justify-content: space-between;">
                            <span><i class="fas fa-users"></i> <?php echo number_format($collectionsReport['overall_verified']['total_customers']); ?> paying customers</span>
                            <span><i class="fas fa-layer-group"></i> <?php echo $collectionsReport['overall_verified']['total_schemes']; ?> active schemes</span>
                        </div>
                    </div>
                </div>

                <!-- ========================================================
                     COLLECTIONS FILTER TOOLBAR ("from here make it like a filter than showing all the details from it")
                     ======================================================== -->
                <div id="collections-filter-section" style="padding-top: 10px;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <div style="font-size: 14.5px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-filter text-primary"></i> Filter Collections by Period:
                        </div>
                        <span class="badge" style="background: rgba(155, 0, 144, 0.1); color: #9B0090; font-weight: 600; font-size: 12.5px; padding: 6px 12px; border-radius: 20px;">
                            <i class="fas fa-calendar-check"></i> Active View: <?php echo htmlspecialchars($periodLabel); ?>
                        </span>
                    </div>

                    <!-- Modern Filter Segmented Pills -->
                    <div class="filter-segmented-bar">
                        <button type="button" class="filter-pill-btn <?php echo $activePeriod === 'today' ? 'active' : ''; ?>" onclick="selectPeriod('today')">
                            <i class="fas fa-calendar-day"></i> This Day / Today
                        </button>
                        <button type="button" class="filter-pill-btn <?php echo $activePeriod === 'month' ? 'active' : ''; ?>" onclick="selectPeriod('month')">
                            <i class="fas fa-calendar-alt"></i> This Month
                        </button>
                        <button type="button" class="filter-pill-btn <?php echo $activePeriod === 'year' ? 'active' : ''; ?>" onclick="selectPeriod('year')">
                            <i class="fas fa-calendar"></i> This Year
                        </button>
                        <button type="button" class="filter-pill-btn <?php echo $activePeriod === 'custom' ? 'active' : ''; ?>" onclick="selectPeriod('custom')">
                            <i class="fas fa-sliders-h"></i> Custom Date Range
                        </button>
                        <button type="button" class="filter-pill-btn <?php echo $activePeriod === 'overall' ? 'active' : ''; ?>" onclick="selectPeriod('overall')">
                            <i class="fas fa-vault"></i> All-Time Overall
                        </button>
                    </div>

                    <!-- Dynamic Controls Tray for Selected Filter -->
                    <div class="filter-controls-tray">
                        <form method="GET" id="collectionsFilterForm" action="index.php#collections-filter-section" class="d-flex align-items-center justify-content-between flex-wrap gap-3 w-100 mb-0">
                            <input type="hidden" name="period" id="periodInput" value="<?php echo htmlspecialchars($activePeriod); ?>">
                            <input type="hidden" name="customer_date" value="<?php echo htmlspecialchars($selectedCustomerDate); ?>">

                            <!-- Control 1: Day Filter -->
                            <div class="period-control-panel <?php echo $activePeriod === 'today' ? 'd-flex' : 'd-none'; ?> align-items-center gap-2 flex-wrap" id="panelToday">
                                <label class="filter-tray-label"><i class="fas fa-calendar-day text-primary"></i> Select Day:</label>
                                <input type="date" name="day" id="inputDay" value="<?php echo htmlspecialchars($selectedDay); ?>" onchange="submitFilterForm()" class="filter-tray-input">
                                <?php if ($selectedDay !== $todayStr): ?>
                                    <button type="button" class="btn-tray-reset" onclick="resetToToday()">
                                        <i class="fas fa-undo"></i> Reset to Today
                                    </button>
                                <?php endif; ?>
                                <span class="filter-tray-hint">Showing single day collections</span>
                            </div>

                            <!-- Control 2: Month Filter -->
                            <div class="period-control-panel <?php echo $activePeriod === 'month' ? 'd-flex' : 'd-none'; ?> align-items-center gap-2 flex-wrap" id="panelMonth">
                                <label class="filter-tray-label"><i class="fas fa-calendar-alt text-primary"></i> Select Month:</label>
                                <input type="month" name="month" id="inputMonth" value="<?php echo htmlspecialchars($selectedMonth); ?>" onchange="submitFilterForm()" class="filter-tray-input">
                                <?php if ($selectedMonth !== $currentMonthStr): ?>
                                    <button type="button" class="btn-tray-reset" onclick="resetToCurrentMonth()">
                                        <i class="fas fa-undo"></i> Reset to This Month
                                    </button>
                                <?php endif; ?>
                                <span class="filter-tray-hint">Showing full calendar month collections</span>
                            </div>

                            <!-- Control 3: Year Filter -->
                            <div class="period-control-panel <?php echo $activePeriod === 'year' ? 'd-flex' : 'd-none'; ?> align-items-center gap-2 flex-wrap" id="panelYear">
                                <label class="filter-tray-label"><i class="fas fa-calendar text-primary"></i> Select Year:</label>
                                <select name="year" id="selectYear" onchange="submitFilterForm()" class="filter-tray-select">
                                    <?php 
                                    $startYear = (int)date('Y') - 3;
                                    $endYear = (int)date('Y') + 2;
                                    for ($y = $endYear; $y >= $startYear; $y--): 
                                    ?>
                                        <option value="<?php echo $y; ?>" <?php echo $y === (int)$selectedYear ? 'selected' : ''; ?>>
                                            Year <?php echo $y; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                                <?php if ((int)$selectedYear !== (int)$currentYearStr): ?>
                                    <button type="button" class="btn-tray-reset" onclick="resetToCurrentYear()">
                                        <i class="fas fa-undo"></i> Reset to <?php echo $currentYearStr; ?>
                                    </button>
                                <?php endif; ?>
                                <span class="filter-tray-hint">Showing entire calendar year collections</span>
                            </div>

                            <!-- Control 4: Custom Date Range -->
                            <div class="period-control-panel <?php echo $activePeriod === 'custom' ? 'd-flex' : 'd-none'; ?> align-items-center gap-2 flex-wrap" id="panelCustom">
                                <label class="filter-tray-label"><i class="fas fa-calendar-week text-primary"></i> From:</label>
                                <input type="date" name="from_date" id="inputFromDate" value="<?php echo htmlspecialchars($fromDate); ?>" class="filter-tray-input">
                                <label class="filter-tray-label">To:</label>
                                <input type="date" name="to_date" id="inputToDate" value="<?php echo htmlspecialchars($toDate); ?>" class="filter-tray-input">
                                <button type="submit" class="btn-tray-apply">
                                    <i class="fas fa-filter"></i> Apply Date Range
                                </button>
                            </div>

                            <!-- Control 5: Overall -->
                            <div class="period-control-panel <?php echo $activePeriod === 'overall' ? 'd-flex' : 'd-none'; ?> align-items-center gap-2 flex-wrap" id="panelOverall">
                                <span class="badge bg-light text-dark border p-2">
                                    <i class="fas fa-vault text-warning"></i> Viewing cumulative collections across all time
                                </span>
                            </div>

                            <!-- Real-time info -->
                            <div class="text-muted small d-none d-md-block">
                                <i class="fas fa-clock text-secondary"></i> IST (+05:30)
                            </div>
                        </form>
                    </div>

                    <!-- 4 Clean Executive KPI Chips for Active Filter -->
                    <div class="kpi-chips-strip">
                        <div class="kpi-chip">
                            <div class="kpi-chip-icon chip-icon-verified">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <div class="kpi-chip-title">Verified Collections</div>
                                <div class="kpi-chip-value">₹<?php echo number_format($filteredPaymentsSummary['verified_amount'], 2); ?></div>
                                <div class="kpi-chip-sub"><?php echo number_format($filteredPaymentsSummary['verified_count']); ?> approved payments</div>
                            </div>
                        </div>

                        <div class="kpi-chip">
                            <div class="kpi-chip-icon chip-icon-total">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div>
                                <div class="kpi-chip-title">Total Submissions</div>
                                <div class="kpi-chip-value"><?php echo number_format($filteredPaymentsSummary['total_count']); ?></div>
                                <div class="kpi-chip-sub">Gross ₹<?php echo number_format($filteredPaymentsSummary['total_amount'], 2); ?></div>
                            </div>
                        </div>

                        <div class="kpi-chip">
                            <div class="kpi-chip-icon chip-icon-pending">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div>
                                <div class="kpi-chip-title">Pending Verification</div>
                                <div class="kpi-chip-value"><?php echo number_format($filteredPaymentsSummary['pending_count']); ?></div>
                                <div class="kpi-chip-sub">₹<?php echo number_format($filteredPaymentsSummary['pending_amount'], 2); ?> under review</div>
                            </div>
                        </div>

                        <div class="kpi-chip">
                            <div class="kpi-chip-icon chip-icon-range">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <div class="kpi-chip-title">Contributing Schemes</div>
                                <div class="kpi-chip-value"><?php echo count($filteredSchemeBreakdown); ?> active</div>
                                <div class="kpi-chip-sub"><?php echo count($filteredPayments); ?> transaction records</div>
                            </div>
                        </div>
                    </div>

                    <!-- Scheme Collections Distribution (Visual Summary Bars) -->
                    <div class="scheme-contribution-card">
                        <div class="scheme-contribution-title">
                            <span><i class="fas fa-chart-pie text-primary me-2"></i>Scheme Collections Distribution (<?php echo htmlspecialchars($periodLabel); ?>)</span>
                            <span class="badge bg-white text-dark border">Total: ₹<?php echo number_format($filteredPaymentsSummary['verified_amount'], 2); ?></span>
                        </div>
                        <?php if (count($filteredSchemeBreakdown) > 0): ?>
                            <div class="row g-3">
                                <?php 
                                $grandTotalFiltered = (float)$filteredPaymentsSummary['verified_amount'];
                                foreach ($filteredSchemeBreakdown as $sch): 
                                    $sTotal = (float)$sch['scheme_total'];
                                    $pct = $grandTotalFiltered > 0 ? round(($sTotal / $grandTotalFiltered) * 100, 1) : 0;
                                ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="scheme-pill-item">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <strong style="font-size: 13.5px; color: #0f172a;">
                                                    <i class="fas fa-gift text-primary me-1"></i><?php echo htmlspecialchars($sch['SchemeName']); ?>
                                                </strong>
                                                <span style="font-weight: 800; color: #9B0090; font-size: 14px;">₹<?php echo number_format($sTotal, 2); ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between text-muted" style="font-size: 11.5px; margin-bottom: 4px;">
                                                <span><?php echo $sch['payment_count']; ?> payments (<?php echo $sch['paying_customers']; ?> subscribers)</span>
                                                <span><strong><?php echo $pct; ?>%</strong></span>
                                            </div>
                                            <div class="share-bar">
                                                <div class="share-fill" style="width: <?php echo $pct; ?>%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-3 text-muted" style="font-size: 13px;">
                                <i class="fas fa-info-circle me-1"></i> No verified scheme collections recorded for <strong><?php echo htmlspecialchars($periodLabel); ?></strong>.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Collapsible Payment Transaction Details (Keeps dashboard clean & clutter-free) -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-3 border-top">
                        <button type="button" class="btn-toggle-details" onclick="toggleDetails()" id="btnToggleDetails">
                            <i class="fas fa-list-ul"></i>
                            <span id="toggleText">View Transaction Details (<?php echo count($filteredPayments); ?>)</span>
                            <i class="fas fa-chevron-down ms-1" id="toggleIcon"></i>
                        </button>
                        <a href="../payments/index.php" class="view-all-link">
                            <i class="fas fa-external-link-alt"></i> Payments Management Hub
                        </a>
                    </div>

                    <!-- Collapsed Container for Transaction Table -->
                    <div id="detailsContainer" style="display: none; margin-top: 15px;">
                        <div class="table-responsive">
                            <table class="payments-table">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Scheme</th>
                                        <th>Amount</th>
                                        <th>Transaction / UTR</th>
                                        <th>Status</th>
                                        <th>Submission Time</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($filteredPayments) > 0): ?>
                                        <?php foreach ($filteredPayments as $payment): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="customer-avatar-badge">
                                                            <?php echo getInitials($payment['CustomerName']); ?>
                                                        </div>
                                                        <div>
                                                            <div style="font-weight: 600; color: #1e293b;">
                                                                <?php echo htmlspecialchars($payment['CustomerName']); ?>
                                                            </div>
                                                            <div class="customer-uid-tag">
                                                                <?php echo htmlspecialchars($payment['CustomerUniqueID']); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="scheme-name-tag">
                                                        <i class="fas fa-gift text-primary"></i>
                                                        <?php echo htmlspecialchars($payment['SchemeName']); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="payment-amount-text">
                                                        ₹<?php echo number_format($payment['Amount'], 2); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if (!empty($payment['UTRNumber'])): ?>
                                                        <code class="utr-code-badge" title="UTR Reference">
                                                            <?php echo htmlspecialchars($payment['UTRNumber']); ?>
                                                        </code>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Cash / Direct</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="status-badge status-<?php echo strtolower($payment['Status']); ?>">
                                                        <span class="status-dot"></span>
                                                        <?php echo $payment['Status']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div style="font-size: 13px; font-weight: 500; color: #334155;">
                                                        <?php echo date('d M Y', strtotime($payment['SubmittedAt'])); ?>
                                                    </div>
                                                    <div class="text-muted small">
                                                        <?php echo date('h:i A', strtotime($payment['SubmittedAt'])); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($payment['Status'] === 'Verified'): ?>
                                                        <a href="../payments/receipt.php?id=<?php echo $payment['PaymentID']; ?>" target="_blank" class="receipt-action-btn" title="View Official Receipt">
                                                            <i class="fas fa-file-invoice"></i> Receipt
                                                        </a>
                                                    <?php elseif ($payment['Status'] === 'Pending'): ?>
                                                        <a href="../payments/index.php" class="review-action-btn" title="Review Payment in Payments Hub">
                                                            <i class="fas fa-search"></i> Review
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted small">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7">
                                                <div class="payments-empty-state">
                                                    <div class="empty-icon-circle">
                                                        <i class="fas fa-receipt"></i>
                                                    </div>
                                                    <h4 class="empty-title">No Payments Found</h4>
                                                    <p class="empty-desc">
                                                        There are no payment records matching the filter: <strong><?php echo htmlspecialchars($periodLabel); ?></strong>.
                                                    </p>
                                                    <div class="empty-btns">
                                                        <button type="button" class="btn-empty-reset" onclick="selectPeriod('month')">
                                                            <i class="fas fa-redo"></i> View Current Month
                                                        </button>
                                                        <a href="../payments/index.php" class="btn-empty-all">
                                                            <i class="fas fa-external-link-alt"></i> All Payments Hub
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Section -->
            <div class="recent-activity">
                <h3 class="section-title">Recent Activity</h3>
                <div class="activity-list">
                    <?php foreach ($recentActivity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">
                                    <?php echo formatActivity($activity['Action'], $activity['UserName']); ?>
                                </div>
                                <div class="activity-time">
                                    <?php echo timeAgo($activity['CreatedAt']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    function selectPeriod(period) {
        var periodInput = document.getElementById('periodInput');
        if (periodInput) periodInput.value = period;

        // Hide all control panels
        ['Today', 'Month', 'Year', 'Custom', 'Overall'].forEach(function(p) {
            var el = document.getElementById('panel' + p);
            if (el) {
                el.classList.remove('d-flex');
                el.classList.add('d-none');
            }
        });

        // Update active filter pill style
        document.querySelectorAll('.filter-pill-btn').forEach(function(btn) {
            btn.classList.remove('active');
        });

        if (period === 'today') {
            var p = document.getElementById('panelToday');
            if (p) { p.classList.remove('d-none'); p.classList.add('d-flex'); }
            var inp = document.getElementById('inputDay');
            if (inp) inp.value = '<?php echo $todayStr; ?>';
            submitFilterForm();
        } else if (period === 'month') {
            var p = document.getElementById('panelMonth');
            if (p) { p.classList.remove('d-none'); p.classList.add('d-flex'); }
            var inp = document.getElementById('inputMonth');
            if (inp) inp.value = '<?php echo $currentMonthStr; ?>';
            submitFilterForm();
        } else if (period === 'year') {
            var p = document.getElementById('panelYear');
            if (p) { p.classList.remove('d-none'); p.classList.add('d-flex'); }
            var sel = document.getElementById('selectYear');
            if (sel) sel.value = '<?php echo $currentYearStr; ?>';
            submitFilterForm();
        } else if (period === 'overall') {
            var p = document.getElementById('panelOverall');
            if (p) { p.classList.remove('d-none'); p.classList.add('d-flex'); }
            submitFilterForm();
        } else if (period === 'custom') {
            var p = document.getElementById('panelCustom');
            if (p) { p.classList.remove('d-none'); p.classList.add('d-flex'); }
            if (window.event && window.event.target) {
                var btn = window.event.target.closest('.filter-pill-btn');
                if (btn) btn.classList.add('active');
            }
        }
    }

    function submitFilterForm() {
        var form = document.getElementById('collectionsFilterForm');
        if (form) {
            form.submit();
        }
    }

    function resetToToday() {
        var periodInput = document.getElementById('periodInput');
        if (periodInput) periodInput.value = 'today';
        var inp = document.getElementById('inputDay');
        if (inp) inp.value = '<?php echo $todayStr; ?>';
        submitFilterForm();
    }

    function resetToCurrentMonth() {
        var periodInput = document.getElementById('periodInput');
        if (periodInput) periodInput.value = 'month';
        var inp = document.getElementById('inputMonth');
        if (inp) inp.value = '<?php echo $currentMonthStr; ?>';
        submitFilterForm();
    }

    function resetToCurrentYear() {
        var periodInput = document.getElementById('periodInput');
        if (periodInput) periodInput.value = 'year';
        var sel = document.getElementById('selectYear');
        if (sel) sel.value = '<?php echo $currentYearStr; ?>';
        submitFilterForm();
    }

    function toggleDetails() {
        var container = document.getElementById('detailsContainer');
        var btn = document.getElementById('btnToggleDetails');
        var textSpan = document.getElementById('toggleText');
        var icon = document.getElementById('toggleIcon');
        var totalCount = <?php echo count($filteredPayments); ?>;

        if (!container) return;

        if (container.style.display === 'none' || container.style.display === '') {
            container.style.display = 'block';
            if (btn) btn.classList.add('active');
            if (textSpan) textSpan.textContent = 'Hide Transaction Details (' + totalCount + ')';
            if (icon) {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        } else {
            container.style.display = 'none';
            if (btn) btn.classList.remove('active');
            if (textSpan) textSpan.textContent = 'View Transaction Details (' + totalCount + ')';
            if (icon) {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }
    }
    </script>
</body>

</html>