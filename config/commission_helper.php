<?php
// config/commission_helper.php

if (!function_exists('convertCommissionToInt')) {
    function convertCommissionToInt($commission)
    {
        return intval(preg_replace('/[^0-9]/', '', (string)$commission));
    }
}

/**
 * Process promoter commission upon payment verification.
 * 
 * Rules:
 * - Month 1 (Initial payment / Installment 1): Direct promoter receives initial commission (₹200).
 *   Parent promoters receive differential if configured.
 * - Month 2 onwards (Recurring monthly payments): Direct promoter receives ₹30 monthly commission.
 *
 * @param string $customerUniqueID
 * @param PDO $conn
 * @param int|null $paymentId
 * @return array
 */
function processPromoterCommission($customerUniqueID, $conn, $paymentId = null)
{
    if (empty($customerUniqueID) && empty($paymentId)) {
        return ['success' => false, 'credited' => []];
    }

    $creditedSummary = [];

    try {
        // Fetch specific payment if paymentId is given, otherwise fetch latest payment for customer
        if (!empty($paymentId)) {
            $stmt = $conn->prepare("
                SELECT p.*, c.CustomerUniqueID, c.Name as CustomerName, c.PromoterID, s.SchemeName, i.InstallmentNumber
                FROM Payments p
                JOIN Customers c ON p.CustomerID = c.CustomerID
                LEFT JOIN Schemes s ON p.SchemeID = s.SchemeID
                LEFT JOIN Installments i ON p.InstallmentID = i.InstallmentID
                WHERE p.PaymentID = ?
            ");
            $stmt->execute([$paymentId]);
            $paymentData = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $stmt = $conn->prepare("
                SELECT p.*, c.CustomerUniqueID, c.Name as CustomerName, c.PromoterID, s.SchemeName, i.InstallmentNumber
                FROM Customers c
                JOIN Payments p ON c.CustomerID = p.CustomerID
                LEFT JOIN Schemes s ON p.SchemeID = s.SchemeID
                LEFT JOIN Installments i ON p.InstallmentID = i.InstallmentID
                WHERE c.CustomerUniqueID = ?
                ORDER BY p.SubmittedAt DESC, p.PaymentID DESC LIMIT 1
            ");
            $stmt->execute([$customerUniqueID]);
            $paymentData = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$paymentData || empty($paymentData['PromoterID'])) {
            return ['success' => false, 'credited' => []];
        }

        $currentPaymentId = $paymentData['PaymentID'];
        $customerUniqueID = $paymentData['CustomerUniqueID'];
        $customerId = $paymentData['CustomerID'];
        $schemeId = $paymentData['SchemeID'];
        $directPromoterRef = trim($paymentData['PromoterID']);
        $schemeName = !empty($paymentData['SchemeName']) ? $paymentData['SchemeName'] : 'Gold Savings Plan';
        $custName = $paymentData['CustomerName'];
        $installmentNum = intval($paymentData['InstallmentNumber'] ?? 0);

        // Determine if this payment is Month 1 (Initial) or Month 2+ (Monthly Recurring)
        // Count verified payments prior to this payment
        $priorStmt = $conn->prepare("
            SELECT COUNT(*) as prior_count 
            FROM Payments 
            WHERE CustomerID = ? AND SchemeID = ? AND Status = 'Verified' AND PaymentID < ?
        ");
        $priorStmt->execute([$customerId, $schemeId, $currentPaymentId]);
        $priorCount = intval($priorStmt->fetch(PDO::FETCH_ASSOC)['prior_count'] ?? 0);

        $isInitialPayment = ($installmentNum === 1 || ($installmentNum === 0 && $priorCount === 0));

        // Pre-load all promoters to build hierarchy chain
        $pStmt = $conn->prepare("SELECT PromoterID, PromoterUniqueID, ParentPromoterID, Commission, ParentCommission, Name FROM Promoters");
        $pStmt->execute();
        $allPromoters = $pStmt->fetchAll(PDO::FETCH_ASSOC);

        $promoterByRef = [];
        foreach ($allPromoters as $p) {
            $pID = (string)$p['PromoterID'];
            $uID = trim($p['PromoterUniqueID']);
            if (!empty($uID)) $promoterByRef[$uID] = $p;
            if (!empty($pID)) $promoterByRef[$pID] = $p;
        }

        // Build hierarchy chain: Direct (index 0) -> Parent (index 1) -> Grandparent (index 2) ...
        $hierarchy = [];
        $currRef = $directPromoterRef;
        $visited = [];

        while (!empty($currRef) && !isset($visited[$currRef])) {
            $visited[$currRef] = true;
            if (!isset($promoterByRef[$currRef])) {
                break;
            }
            $pData = $promoterByRef[$currRef];
            $hierarchy[] = $pData;
            $currRef = !empty($pData['ParentPromoterID']) ? trim($pData['ParentPromoterID']) : null;
        }

        if (empty($hierarchy)) {
            return ['success' => false, 'credited' => []];
        }

        $directPromoter = $hierarchy[0];
        $directID = trim($directPromoter['PromoterUniqueID']);
        $directNumID = (string)$directPromoter['PromoterID'];

        if ($isInitialPayment) {
            // ==========================================
            // 1. INITIAL PAYMENT (Month 1): ₹200 commission
            // ==========================================
            $directCommission = convertCommissionToInt($directPromoter['Commission']);
            if ($directCommission <= 0) {
                $directCommission = 200; // Default to ₹200
            }

            // Check if already credited for Payment #ID or Month 1
            $checkStmt = $conn->prepare("
                SELECT COUNT(*) as already_credited 
                FROM WalletLogs 
                WHERE (TRIM(PromoterUniqueID) = ? OR TRIM(PromoterUniqueID) = ?) 
                  AND (Message LIKE ? OR (Message LIKE ? AND Message LIKE '%Initial%'))
                  AND (TransactionType = 'Credit' OR TransactionType IS NULL OR TransactionType = '')
            ");
            $checkStmt->execute([
                $directID,
                $directNumID,
                "%Payment #{$currentPaymentId}%",
                "%{$customerUniqueID}%"
            ]);

            if ($checkStmt->fetch(PDO::FETCH_ASSOC)['already_credited'] == 0) {
                // Update / create wallet
                $wStmt = $conn->prepare("SELECT BalanceID FROM PromoterWallet WHERE TRIM(PromoterUniqueID) = ? OR UserID = ?");
                $wStmt->execute([$directID, $directNumID]);
                $wRecord = $wStmt->fetch(PDO::FETCH_ASSOC);

                if ($wRecord) {
                    $uStmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = BalanceAmount + ?, LastUpdated = CURRENT_TIMESTAMP WHERE TRIM(PromoterUniqueID) = ? OR UserID = ?");
                    $uStmt->execute([$directCommission, $directID, $directNumID]);
                } else {
                    $inStmt = $conn->prepare("INSERT INTO PromoterWallet (UserID, PromoterUniqueID, BalanceAmount, Message) VALUES (?, ?, ?, 'Commission from payment')");
                    $inStmt->execute([$directPromoter['PromoterID'], $directID, $directCommission]);
                }

                $logMsg = "Initial commission earned from customer " . $custName . " (" . $customerUniqueID . ") for " . $schemeName . " scheme (Payment #" . $currentPaymentId . " / Month 1)";
                $lStmt = $conn->prepare("INSERT INTO WalletLogs (PromoterUniqueID, Amount, Message, TransactionType) VALUES (?, ?, ?, 'Credit')");
                $lStmt->execute([$directID, $directCommission, $logMsg]);

                $creditedSummary[] = [
                    'role' => 'Direct Promoter (Initial)',
                    'name' => $directPromoter['Name'],
                    'id' => $directID,
                    'amount' => $directCommission
                ];
            }

            // Process Parent Promoters differential for Initial Payment if any
            for ($i = 0; $i < count($hierarchy) - 1; $i++) {
                $childPromoter = $hierarchy[$i];
                $parentPromoter = $hierarchy[$i + 1];

                $parentID = trim($parentPromoter['PromoterUniqueID']);
                $parentNumID = (string)$parentPromoter['PromoterID'];

                $childCommission = convertCommissionToInt($childPromoter['Commission']) ?: 200;
                $parentCommission = convertCommissionToInt($parentPromoter['Commission']) ?: 200;

                $gapAmount = 0;
                if (!empty($childPromoter['ParentCommission']) && convertCommissionToInt($childPromoter['ParentCommission']) > 0) {
                    $gapAmount = convertCommissionToInt($childPromoter['ParentCommission']);
                } else if ($parentCommission > $childCommission) {
                    $gapAmount = $parentCommission - $childCommission;
                }

                if ($gapAmount > 0) {
                    $pCheckStmt = $conn->prepare("
                        SELECT COUNT(*) as parent_already_credited 
                        FROM WalletLogs 
                        WHERE (TRIM(PromoterUniqueID) = ? OR TRIM(PromoterUniqueID) = ?) 
                          AND (Message LIKE ? OR (Message LIKE ? AND Message LIKE '%Initial%'))
                          AND (TransactionType = 'Credit' OR TransactionType IS NULL OR TransactionType = '')
                    ");
                    $pCheckStmt->execute([
                        $parentID,
                        $parentNumID,
                        "%Payment #{$currentPaymentId}%",
                        "%{$customerUniqueID}%"
                    ]);

                    if ($pCheckStmt->fetch(PDO::FETCH_ASSOC)['parent_already_credited'] == 0) {
                        $pwStmt = $conn->prepare("SELECT BalanceID FROM PromoterWallet WHERE TRIM(PromoterUniqueID) = ? OR UserID = ?");
                        $pwStmt->execute([$parentID, $parentNumID]);
                        $pwRecord = $pwStmt->fetch(PDO::FETCH_ASSOC);

                        if ($pwRecord) {
                            $puStmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = BalanceAmount + ?, LastUpdated = CURRENT_TIMESTAMP WHERE TRIM(PromoterUniqueID) = ? OR UserID = ?");
                            $puStmt->execute([$gapAmount, $parentID, $parentNumID]);
                        } else {
                            $pinStmt = $conn->prepare("INSERT INTO PromoterWallet (UserID, PromoterUniqueID, BalanceAmount, Message) VALUES (?, ?, ?, 'Parent commission from payment')");
                            $pinStmt->execute([$parentPromoter['PromoterID'], $parentID, $gapAmount]);
                        }

                        $pLogMsg = "Parent initial commission earned from customer " . $custName . " (" . $customerUniqueID . ") for " . $schemeName . " scheme (Payment #" . $currentPaymentId . " / Month 1)";
                        $plStmt = $conn->prepare("INSERT INTO WalletLogs (PromoterUniqueID, Amount, Message, TransactionType) VALUES (?, ?, ?, 'Credit')");
                        $plStmt->execute([$parentID, $gapAmount, $pLogMsg]);

                        $roleLabel = ($i === 0) ? 'Parent Promoter (Initial)' : 'Grandparent Promoter (Initial)';
                        $creditedSummary[] = [
                            'role' => $roleLabel,
                            'name' => $parentPromoter['Name'],
                            'id' => $parentID,
                            'amount' => $gapAmount
                        ];
                    }
                }
            }
        } else {
            // ========================================================
            // 2. RECURRING MONTHLY PAYMENT (Month 2+): ₹30 commission
            // ========================================================
            $monthlyCommission = 30; // ₹30 for each monthly payment
            $monthNum = $installmentNum > 1 ? $installmentNum : ($priorCount + 1);

            $mCheckStmt = $conn->prepare("
                SELECT COUNT(*) as already_credited 
                FROM WalletLogs 
                WHERE (TRIM(PromoterUniqueID) = ? OR TRIM(PromoterUniqueID) = ?) 
                  AND Message LIKE ?
                  AND (TransactionType = 'Credit' OR TransactionType IS NULL OR TransactionType = '')
            ");
            $mCheckStmt->execute([
                $directID,
                $directNumID,
                "%Payment #{$currentPaymentId}%"
            ]);

            if ($mCheckStmt->fetch(PDO::FETCH_ASSOC)['already_credited'] == 0) {
                // Update / create wallet
                $wStmt = $conn->prepare("SELECT BalanceID FROM PromoterWallet WHERE TRIM(PromoterUniqueID) = ? OR UserID = ?");
                $wStmt->execute([$directID, $directNumID]);
                $wRecord = $wStmt->fetch(PDO::FETCH_ASSOC);

                if ($wRecord) {
                    $uStmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = BalanceAmount + ?, LastUpdated = CURRENT_TIMESTAMP WHERE TRIM(PromoterUniqueID) = ? OR UserID = ?");
                    $uStmt->execute([$monthlyCommission, $directID, $directNumID]);
                } else {
                    $inStmt = $conn->prepare("INSERT INTO PromoterWallet (UserID, PromoterUniqueID, BalanceAmount, Message) VALUES (?, ?, ?, 'Monthly commission from payment')");
                    $inStmt->execute([$directPromoter['PromoterID'], $directID, $monthlyCommission]);
                }

                $logMsg = "Monthly commission earned from customer " . $custName . " (" . $customerUniqueID . ") for " . $schemeName . " scheme (Payment #" . $currentPaymentId . " / Month " . $monthNum . ")";
                $lStmt = $conn->prepare("INSERT INTO WalletLogs (PromoterUniqueID, Amount, Message, TransactionType) VALUES (?, ?, ?, 'Credit')");
                $lStmt->execute([$directID, $monthlyCommission, $logMsg]);

                $creditedSummary[] = [
                    'role' => 'Direct Promoter (Monthly)',
                    'name' => $directPromoter['Name'],
                    'id' => $directID,
                    'amount' => $monthlyCommission
                ];
            }
        }

        return ['success' => true, 'credited' => $creditedSummary];
    } catch (Exception $e) {
        error_log("Error in processPromoterCommission: " . $e->getMessage());
        return ['success' => false, 'credited' => []];
    }
}