<?php
session_start();
$menuPath = "../";
$currentPage = "extras";
require_once($menuPath . "../config/config.php");
require_once($menuPath . "../vendor/autoload.php");
$database = new Database();
$conn = $database->getConnection();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Fetch all schemes
$schemes = $conn->query("SELECT SchemeID, SchemeName FROM Schemes WHERE Status = 'Active' ORDER BY SchemeName")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all installments for all schemes
$allInstallments = $conn->query("
    SELECT i.InstallmentID, i.SchemeID, i.InstallmentName, i.InstallmentNumber, i.Amount, i.DrawDate
    FROM Installments i 
    JOIN Schemes s ON i.SchemeID = s.SchemeID
    WHERE i.Status = 'Active' AND s.Status = 'Active'
    ORDER BY i.SchemeID, i.InstallmentNumber
")->fetchAll(PDO::FETCH_ASSOC);

// Group installments by scheme for easy access
$installmentsByScheme = [];
foreach ($allInstallments as $inst) {
    $installmentsByScheme[$inst['SchemeID']][] = $inst;
}

// Fetch all promoters with their customer count
$promoters = $conn->query("
    SELECT p.PromoterUniqueID, p.Name, COUNT(c.CustomerID) as customer_count
    FROM Promoters p
    LEFT JOIN Customers c ON c.PromoterID = p.PromoterUniqueID
    WHERE p.Status = 'Active'
    GROUP BY p.PromoterUniqueID, p.Name
    ORDER BY customer_count DESC, p.Name ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Helper to convert commission to int
function convertCommissionToInt($commission) {
    return intval(preg_replace('/[^0-9]/', '', $commission));
}

// Core Function: Fetch and calculate actual commissions with optional filters
function getFilteredCommissionsData($promoterId, $schemeId, $installmentId, $conn) {
    // 1. Get default commission
    $stmt = $conn->prepare("SELECT Commission FROM Promoters WHERE PromoterUniqueID = ?");
    $stmt->execute([$promoterId]);
    $promoter = $stmt->fetch(PDO::FETCH_ASSOC);
    $defaultCommission = $promoter ? convertCommissionToInt($promoter['Commission']) : 0;

    // 2. Build dynamic query based on applied filters
    $query = "
        SELECT pay.PaymentID, pay.Amount as PaymentAmount, pay.VerifiedAt, c.CustomerUniqueID as FromCustomerID, s.SchemeName, i.InstallmentName
        FROM Payments pay
        JOIN Customers c ON pay.CustomerID = c.CustomerID
        JOIN Schemes s ON pay.SchemeID = s.SchemeID
        JOIN Installments i ON pay.InstallmentID = i.InstallmentID
        WHERE c.PromoterID = ? AND pay.Status = 'Verified'
    ";
    
    $params = [$promoterId];

    if (!empty($schemeId)) {
        $query .= " AND pay.SchemeID = ?";
        $params[] = $schemeId;
    }
    // Monthly/Installment Filtration applied here to the Payments table
    if (!empty($installmentId)) {
        $query .= " AND pay.InstallmentID = ?";
        $params[] = $installmentId;
    }
    
    // Order ascending so older payments match with older wallet logs
    $query .= " ORDER BY pay.PaymentID ASC";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Fetch WalletLogs to cross-reference actual commission payouts
    $stmtLogs = $conn->prepare("
        SELECT Amount, Message, CreatedAt 
        FROM WalletLogs 
        WHERE PromoterUniqueID = ? AND TransactionType = 'Credit' 
        ORDER BY CreatedAt ASC
    ");
    $stmtLogs->execute([$promoterId]);
    $walletLogs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

    $rows = [];
    $matchedLogIndexes = [];

    // 4. Match payments to wallet logs
    foreach ($payments as $payment) {
        $commissionAmount = $defaultCommission; // Fallback

        foreach ($walletLogs as $idx => $log) {
            if (in_array($idx, $matchedLogIndexes)) continue;
            
            $msg = $log['Message'];
            
            // Relaxed Match: If the log mentions BOTH the Customer ID and the Scheme Name, 
            // it is guaranteed to be the commission for this transaction.
            if (strpos($msg, $payment['FromCustomerID']) !== false && strpos($msg, $payment['SchemeName']) !== false) {
                $matchedLogIndexes[] = $idx;
                $commissionAmount = (float)$log['Amount'];
                break; // Move to the next payment once matched
            }
        }

        $rows[] = [
            'PromoterUniqueID' => $promoterId,
            'FromCustomerID' => $payment['FromCustomerID'],
            'CommissionAmount' => $commissionAmount,
            'SchemeName' => $payment['SchemeName'],
            'InstallmentName' => $payment['InstallmentName']
        ];
    }
    
    // Reverse the array so the newest records show up at the top of the Excel sheet
    return array_reverse($rows);
}

// Handle form selection and preview
$selectedPromoter = $_GET['promoter_id'] ?? '';
$selectedScheme = $_GET['scheme_id'] ?? '';
$selectedInstallment = $_GET['installment_id'] ?? '';
$previewRows = [];

if ($selectedPromoter) {
    $previewRows = getFilteredCommissionsData($selectedPromoter, $selectedScheme, $selectedInstallment, $conn);
}

// Handle Excel download
if (isset($_GET['download']) && isset($_GET['promoter_id'])) {
    $rows = getFilteredCommissionsData($_GET['promoter_id'], $_GET['scheme_id'] ?? '', $_GET['installment_id'] ?? '', $conn);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'PromoterUniqueID');
    $sheet->setCellValue('B1', 'FromCustomerID');
    $sheet->setCellValue('C1', 'Commission Amount');
    $sheet->setCellValue('D1', 'Scheme Name');
    $sheet->setCellValue('E1', 'Installment Name');
    
    $rowNum = 2;
    foreach ($rows as $row) {
        $sheet->setCellValue('A' . $rowNum, $row['PromoterUniqueID']);
        $sheet->setCellValue('B' . $rowNum, $row['FromCustomerID']);
        $sheet->setCellValue('C' . $rowNum, $row['CommissionAmount']);
        $sheet->setCellValue('D' . $rowNum, $row['SchemeName']);
        $sheet->setCellValue('E' . $rowNum, $row['InstallmentName']);
        $rowNum++;
    }
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="monthly_commissions_report.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

include($menuPath . "components/sidebar.php");
include($menuPath . "components/topbar.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Monthly Commissions Excel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        :root {
            --primary-color: #9B0090;
            --secondary-color: #0B5CAD;
            --hover-color: #b800aa;
        }
        .extras-container { padding: 20px; max-width: 1100px; margin: 0 auto; }
        .extras-title { font-size: 24px; font-weight: 700; color: var(--secondary-color); margin-bottom: 25px; }
        .extras-form { background: #fff; border-radius: 10px; padding: 20px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06); }
        .extras-form label { font-weight: 500; color: var(--secondary-color); margin-bottom: 8px; display: block; }
        .extras-form select, .extras-form button { padding: 10px; border-radius: 6px; border: 1px solid #ddd; font-size: 15px; margin-bottom: 15px; width: 100%; }
        .extras-form button { background: var(--primary-color); color: #fff; border: none; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .extras-form button:hover { background: var(--hover-color); }
        .extras-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04); }
        .extras-table th, .extras-table td { padding: 12px 16px; text-align: left; border-bottom: 1px solid #e0e0e0; font-size: 14px; }
        .extras-table th { background: #f4f8fb; color: #34495e; font-weight: 600; }
        .extras-table tr:last-child td { border-bottom: none; }
        .download-btn { margin-top: 20px; display: inline-block; background: linear-gradient(135deg, #9B0090 0%, #0B5CAD 100%); color: #fff; padding: 10px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; transition: all 0.2s; box-shadow: 0 2px 6px rgba(155, 0, 144, 0.25); }
        .download-btn:hover { background: linear-gradient(135deg, #b800aa 0%, #0d6ed0 100%); transform: translateY(-1px); }
        /* Searchable Dropdown Styles */
        .searchable-dropdown { position: relative; display: block; width: 100%; }
        .searchable-dropdown .select-wrapper { position: relative; }
        .searchable-dropdown input[type="text"] { width: 100%; padding: 10px 30px 10px 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 15px; box-sizing: border-box; }
        .searchable-dropdown input[type="text"]:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 2px rgba(58, 123, 213, 0.1); }
        .searchable-dropdown .dropdown-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #666; }
        .searchable-dropdown .dropdown-list { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-top: none; border-radius: 0 0 6px 6px; max-height: 300px; overflow-y: auto; z-index: 1000; display: none; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); margin-top: -1px; }
        .searchable-dropdown .dropdown-list.show { display: block; }
        .searchable-dropdown .dropdown-item { padding: 10px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; transition: background-color 0.2s; }
        .searchable-dropdown .dropdown-item:last-child { border-bottom: none; }
        .searchable-dropdown .dropdown-item:hover { background-color: #f5f5f5; }
        .searchable-dropdown .dropdown-item.selected { background-color: #e3f2fd; color: var(--primary-color); font-weight: 500; }
        .searchable-dropdown .dropdown-item.hidden { display: none; }
        .searchable-dropdown .no-results { padding: 10px 12px; color: #999; text-align: center; font-style: italic; display: none; }
        .searchable-dropdown .no-results.show { display: block; }
    </style>
</head>

<body>
    <div class="content-wrapper">
        <div class="extras-container">
            <div class="extras-title"><i class="fas fa-file-excel"></i> Get Commission Details</div>
            <form class="extras-form" method="GET" id="filterForm">
                <label for="promoter-search">Select Promoter (Required):</label>
                <div class="searchable-dropdown" id="promoterDropdown">
                    <input type="hidden" name="promoter_id" id="promoter_id" value="<?php echo htmlspecialchars($selectedPromoter); ?>">
                    <div class="select-wrapper">
                        <input type="text" id="promoter-search" placeholder="Search promoters..." autocomplete="off"
                            value="<?php
                                    if ($selectedPromoter) {
                                        foreach ($promoters as $promoter) {
                                            if ($promoter['PromoterUniqueID'] === $selectedPromoter) {
                                                echo htmlspecialchars($promoter['PromoterUniqueID'] . ' - ' . $promoter['Name'] . ' (' . $promoter['customer_count'] . ' customers)');
                                                break;
                                            }
                                        }
                                    }
                                    ?>">
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </div>
                    <div class="dropdown-list" id="promoterList">
                        <?php foreach ($promoters as $promoter): ?>
                            <div class="dropdown-item <?php echo ($selectedPromoter === $promoter['PromoterUniqueID']) ? 'selected' : ''; ?>"
                                data-value="<?php echo htmlspecialchars($promoter['PromoterUniqueID']); ?>"
                                data-text="<?php echo htmlspecialchars($promoter['PromoterUniqueID'] . ' - ' . $promoter['Name'] . ' (' . $promoter['customer_count'] . ' customers)'); ?>">
                                <?php echo htmlspecialchars($promoter['PromoterUniqueID'] . ' - ' . $promoter['Name'] . ' (' . $promoter['customer_count'] . ' customers)'); ?>
                            </div>
                        <?php endforeach; ?>
                        <div class="no-results">No promoters found</div>
                    </div>
                </div>
                
                <label for="scheme_id">Select Scheme (Optional Filter):</label>
                <select name="scheme_id" id="scheme_id" onchange="filterInstallments()">
                    <option value="">All Schemes</option>
                    <?php foreach ($schemes as $scheme): ?>
                        <option value="<?php echo $scheme['SchemeID']; ?>" <?php if ($selectedScheme == $scheme['SchemeID']) echo 'selected'; ?>><?php echo htmlspecialchars($scheme['SchemeName']); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <label for="installment_id">Select Installment (Optional Filter):</label>
                <select name="installment_id" id="installment_id">
                    <option value="">All Installments</option>
                </select>
                
                <button type="submit">Preview Data</button>
            </form>
            
            <?php if ($selectedPromoter): ?>
                <a class="download-btn" href="?download=1&promoter_id=<?php echo urlencode($selectedPromoter); ?>&scheme_id=<?php echo urlencode($selectedScheme); ?>&installment_id=<?php echo urlencode($selectedInstallment); ?>">Download Excel</a>
                <div style="margin-top:20px;"></div>
                <table class="extras-table">
                    <thead>
                        <tr>
                            <th>PromoterUniqueID</th>
                            <th>FromCustomerID</th>
                            <th>Commission Amount</th>
                            <th>Scheme Name</th>
                            <th>Installment Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previewRows as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['PromoterUniqueID']); ?></td>
                                <td><?php echo htmlspecialchars($row['FromCustomerID']); ?></td>
                                <td><?php echo htmlspecialchars($row['CommissionAmount']); ?></td>
                                <td><?php echo htmlspecialchars($row['SchemeName']); ?></td>
                                <td><?php echo htmlspecialchars($row['InstallmentName']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($previewRows)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center; color:#888;">No data found for this selection.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Searchable Dropdown Functionality
        (function() {
            const dropdown = document.getElementById('promoterDropdown');
            const searchInput = document.getElementById('promoter-search');
            const hiddenInput = document.getElementById('promoter_id');
            const dropdownList = document.getElementById('promoterList');
            const dropdownItems = dropdownList.querySelectorAll('.dropdown-item:not(.no-results)');
            const noResults = dropdownList.querySelector('.no-results');

            searchInput.addEventListener('focus', function() {
                dropdownList.classList.add('show');
                this.select();
                dropdownItems.forEach(item => {
                    item.classList.remove('hidden');
                    if (item.getAttribute('data-value') === hiddenInput.value) {
                        item.classList.add('selected');
                    } else {
                        item.classList.remove('selected');
                    }
                });
                noResults.classList.remove('show');
            });

            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target)) {
                    dropdownList.classList.remove('show');
                }
            });

            function filterItems() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                let visibleCount = 0;

                dropdownItems.forEach(item => {
                    const text = item.getAttribute('data-text').toLowerCase();
                    if (text.includes(searchTerm)) {
                        item.classList.remove('hidden');
                        visibleCount++;
                        if (item.getAttribute('data-value') === hiddenInput.value) {
                            item.classList.add('selected');
                        } else {
                            item.classList.remove('selected');
                        }
                    } else {
                        item.classList.add('hidden');
                    }
                });

                if (visibleCount === 0) {
                    noResults.classList.add('show');
                } else {
                    noResults.classList.remove('show');
                }
            }

            searchInput.addEventListener('input', function() {
                dropdownList.classList.add('show');
                filterItems();
            });

            dropdownItems.forEach(item => {
                item.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    const text = this.getAttribute('data-text');

                    hiddenInput.value = value;
                    searchInput.value = text;

                    dropdownItems.forEach(i => i.classList.remove('selected'));
                    this.classList.add('selected');
                    dropdownList.classList.remove('show');
                });
            });

            let selectedIndex = -1;
            searchInput.addEventListener('keydown', function(e) {
                const visibleItems = Array.from(dropdownItems).filter(item => !item.classList.contains('hidden'));

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedIndex = Math.min(selectedIndex + 1, visibleItems.length - 1);
                    updateHighlight(visibleItems);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedIndex = Math.max(selectedIndex - 1, -1);
                    updateHighlight(visibleItems);
                } else if (e.key === 'Enter' && selectedIndex >= 0) {
                    e.preventDefault();
                    visibleItems[selectedIndex].click();
                } else if (e.key === 'Escape') {
                    dropdownList.classList.remove('show');
                }
            });

            function updateHighlight(visibleItems) {
                visibleItems.forEach((item, index) => {
                    if (index === selectedIndex) {
                        item.style.backgroundColor = '#e3f2fd';
                    } else {
                        item.style.backgroundColor = '';
                    }
                });
            }

            searchInput.addEventListener('input', function() {
                selectedIndex = -1;
            });
        })();

        // Scheme / Installment Dependency logic
        const installmentsByScheme = <?php echo json_encode($installmentsByScheme); ?>;
        const selectedInstallment = '<?php echo $selectedInstallment; ?>';

        function filterInstallments() {
            const schemeId = document.getElementById('scheme_id').value;
            const installmentSelect = document.getElementById('installment_id');

            installmentSelect.innerHTML = '<option value="">All Installments</option>';

            if (schemeId && installmentsByScheme[schemeId]) {
                const installments = installmentsByScheme[schemeId];
                installments.forEach(inst => {
                    const option = document.createElement('option');
                    option.value = inst.InstallmentID;
                    option.textContent = inst.InstallmentName;
                    if (selectedInstallment == inst.InstallmentID) {
                        option.selected = true;
                    }
                    installmentSelect.appendChild(option);
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const schemeSelect = document.getElementById('scheme_id');
            if (schemeSelect.value) {
                filterInstallments();
            }
        });

        document.getElementById('filterForm').addEventListener('submit', function(e) {
            const promoterId = document.getElementById('promoter_id').value;
            if (!promoterId) {
                e.preventDefault();
                alert('Please select a promoter.');
                document.getElementById('promoter-search').focus();
                return false;
            }
        });
    </script>
</body>
</html>