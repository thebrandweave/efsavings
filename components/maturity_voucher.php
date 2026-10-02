<?php
/**
 * Official Savings Scheme Maturity Certificate & Showroom Redemption Voucher
 * Liya's Furniture & Electronics (A Unit of Pro Gee Dee Ventures Pvt. Ltd.)
 */

if (!function_exists('amountToWords')) {
    function amountToWords($amount) {
        $amount = round((float)$amount, 2);
        $whole = (int)floor($amount);
        $fraction = (int)round(($amount - $whole) * 100);

        $ones = array(
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen'
        );
        $tens = array(
            2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
            6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
        );

        $convertGroup = function($n) use ($ones, $tens) {
            $str = '';
            if ($n >= 100) {
                $str .= $ones[(int)floor($n / 100)] . ' Hundred ';
                $n %= 100;
            }
            if ($n >= 20) {
                $str .= $tens[(int)floor($n / 10)] . ' ';
                $n %= 10;
            }
            if ($n > 0) {
                $str .= $ones[$n] . ' ';
            }
            return trim($str);
        };

        if ($whole == 0) {
            $words = 'Zero';
        } else {
            $parts = [];
            if ($whole >= 10000000) {
                $crores = (int)floor($whole / 10000000);
                $parts[] = $convertGroup($crores) . ' Crore';
                $whole %= 10000000;
            }
            if ($whole >= 100000) {
                $lakhs = (int)floor($whole / 100000);
                $parts[] = $convertGroup($lakhs) . ' Lakh';
                $whole %= 100000;
            }
            if ($whole >= 1000) {
                $thousands = (int)floor($whole / 1000);
                $parts[] = $convertGroup($thousands) . ' Thousand';
                $whole %= 1000;
            }
            if ($whole > 0) {
                $parts[] = $convertGroup($whole);
            }
            $words = implode(' ', $parts);
        }

        $words = trim($words) . ' Rupees';
        if ($fraction > 0) {
            $words .= ' and ' . $convertGroup($fraction) . ' Paise';
        }
        return $words . ' Only';
    }
}

// Certificate reference
$certNumber = 'MAT-' . str_pad($subscription['SubscriptionID'], 6, '0', STR_PAD_LEFT);
$startDateStr = !empty($subscription['StartDate']) ? date('d / m / Y', strtotime($subscription['StartDate'])) : '-';
$maturityDateStr = !empty($subscription['EndDate']) ? date('d / m / Y', strtotime($subscription['EndDate'])) : date('d / m / Y');
$totalSaved = (float)($subscription['TotalSaved'] ?? ($subscription['paid_installments'] * $subscription['MonthlyPayment']));
$amountInWords = amountToWords($totalSaved);
$backLink = !empty($backUrl) ? $backUrl : 'javascript:window.close();';

// Logo resolution
$logoPath = dirname(__DIR__) . '/components/liyas_logo.png';
$logoBase64 = '';
if (file_exists($logoPath)) {
    $logoData = file_get_contents($logoPath);
    $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan Maturity Certificate - <?php echo htmlspecialchars($certNumber); ?> | Liya's Furniture & Electronics</title>
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($logoBase64); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600;1,700&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-magenta: #9B0090;
            --brand-blue: #0B5CAD;
            --brand-gold: #d97706;
            --receipt-bg: #FFFFFF;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0b0f19;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 15px 40px;
        }

        /* Top Action Bar (hidden on print) */
        .cert-action-bar {
            width: 100%;
            max-width: 920px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            background: #161f30;
            padding: 12px 20px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
        }

        .action-bar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #f1f5f9;
            font-size: 14px;
            font-weight: 600;
        }

        .action-bar-left i {
            color: #fbbf24;
            font-size: 18px;
        }

        .action-bar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-print {
            background: linear-gradient(135deg, #9B0090 0%, #0B5CAD 100%);
            color: #ffffff;
            box-shadow: 0 2px 10px rgba(155, 0, 144, 0.4);
        }

        .btn-print:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(155, 0, 144, 0.6);
            color: #ffffff;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.16);
            color: #ffffff;
        }

        /* Certificate Container */
        .cert-card {
            width: 100%;
            max-width: 920px;
            background: var(--receipt-bg);
            border-radius: 8px;
            box-shadow: 0 12px 45px rgba(0, 0, 0, 0.7);
            position: relative;
            overflow: hidden;
            border: 4px double #d97706;
            padding: 38px 46px 32px;
        }

        /* Golden Corner Accents */
        .cert-corner {
            position: absolute;
            width: 32px;
            height: 32px;
            border-color: #d97706;
            pointer-events: none;
        }
        .cert-corner.top-left { top: 10px; left: 10px; border-top: 3px solid; border-left: 3px solid; }
        .cert-corner.top-right { top: 10px; right: 10px; border-top: 3px solid; border-right: 3px solid; }
        .cert-corner.bottom-left { bottom: 10px; left: 10px; border-bottom: 3px solid; border-left: 3px solid; }
        .cert-corner.bottom-right { bottom: 10px; right: 10px; border-bottom: 3px solid; border-right: 3px solid; }

        /* Security Watermark */
        .cert-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            font-size: 90px;
            font-weight: 900;
            color: rgba(155, 0, 144, 0.03);
            letter-spacing: 16px;
            text-transform: uppercase;
            pointer-events: none;
            user-select: none;
            z-index: 1;
            white-space: nowrap;
            font-family: 'Playfair Display', serif;
        }

        .cert-content {
            position: relative;
            z-index: 2;
        }

        /* Header */
        .cert-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 16px;
            border-bottom: 2px solid #f1f5f9;
            margin-bottom: 24px;
        }

        .cert-title-group h1 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: #9B0090;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .cert-title-group .sub-title {
            font-size: 13px;
            font-weight: 700;
            color: #0B5CAD;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-top: 4px;
        }

        .cert-meta-row {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 8px;
            font-size: 13.5px;
            color: #475569;
        }

        .cert-meta-row .val {
            font-family: 'Courier New', monospace;
            font-weight: 800;
            color: #0f172a;
        }

        .cert-brand {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
        }

        .company-logo {
            height: 46px;
            max-width: 170px;
            object-fit: contain;
            margin-bottom: 4px;
        }

        .company-name {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
        }

        .company-sub {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
        }

        /* Ribbon Badge */
        .maturity-ribbon {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, rgba(155, 0, 144, 0.08) 0%, rgba(11, 92, 173, 0.08) 100%);
            border: 1px dashed #d97706;
            border-radius: 8px;
            padding: 14px 20px;
            margin-bottom: 26px;
        }

        .ribbon-text {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 700;
            color: #92400e;
        }

        .ribbon-text i {
            color: #d97706;
            font-size: 22px;
        }

        .ribbon-tag {
            background: #d97706;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        /* Certificate Body Form */
        .cert-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 26px;
        }

        .cert-info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px 18px;
        }

        .card-label {
            font-size: 11.5px;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .card-val-lg {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        .card-val-sub {
            font-size: 13px;
            color: #475569;
            margin-top: 4px;
        }

        /* Maturity Values Highlight Box */
        .highlight-banner {
            background: linear-gradient(135deg, #9B0090 0%, #0B5CAD 100%);
            border-radius: 10px;
            padding: 20px 24px;
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 26px;
            box-shadow: 0 6px 18px rgba(155, 0, 144, 0.25);
        }

        .highlight-left .hl-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
            font-weight: 700;
        }

        .highlight-left .hl-amount {
            font-size: 32px;
            font-weight: 900;
            letter-spacing: -0.5px;
            margin-top: 2px;
        }

        .highlight-left .hl-words {
            font-family: 'Libre Baskerville', serif;
            font-style: italic;
            font-size: 13.5px;
            opacity: 0.95;
            margin-top: 4px;
        }

        .highlight-right {
            text-align: right;
            border-left: 1px solid rgba(255, 255, 255, 0.25);
            padding-left: 24px;
        }

        .hl-status-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Footer & Official Seal */
        .cert-footer-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-top: 20px;
            border-top: 1px dashed #cbd5e1;
            margin-top: 10px;
        }

        .signature-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 220px;
        }

        .sig-line {
            width: 100%;
            height: 1px;
            background: #475569;
            margin-bottom: 6px;
        }

        .sig-label {
            font-size: 13px;
            font-weight: 700;
            color: #334155;
        }

        .sig-sub {
            font-size: 11.5px;
            color: #64748b;
        }

        .official-seal {
            width: 120px;
            height: 120px;
            border: 2.5px dashed #9B0090;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
            transform: rotate(-6deg);
            box-shadow: inset 0 0 0 2px #9B0090;
            background: rgba(155, 0, 144, 0.02);
        }

        .seal-inner {
            width: 100%;
            height: 100%;
            border: 1px solid #9B0090;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 4px;
            color: #9B0090;
        }

        .seal-stars { font-size: 7px; letter-spacing: 2px; }
        .seal-title { font-size: 7.5px; font-weight: 800; text-transform: uppercase; }
        .seal-badge { margin: 3px 0; font-size: 10px; font-weight: 900; border-top: 1px solid #9B0090; border-bottom: 1px solid #9B0090; padding: 1px 6px; text-transform: uppercase; }
        .seal-date { font-size: 7.5px; font-weight: 700; font-family: 'Courier New', monospace; }
        .seal-foot { font-size: 6.5px; font-weight: 700; text-transform: uppercase; margin-top: 2px; }

        .cert-terms {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px dotted #cbd5e1;
            font-size: 11px;
            color: #64748b;
            text-align: center;
            line-height: 1.5;
        }

        /* PRINT STYLES */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .cert-action-bar { display: none !important; }
            .cert-card {
                box-shadow: none !important;
                border: 3px solid #d97706 !important;
                max-width: 100% !important;
                width: 100% !important;
                padding: 25px 30px !important;
                page-break-inside: avoid;
            }
            @page {
                size: A4 landscape;
                margin: 10mm;
            }
        }

        @media (max-width: 768px) {
            .cert-card { padding: 24px 20px; }
            .cert-header { flex-direction: column; gap: 15px; }
            .cert-brand { text-align: left; align-items: flex-start; }
            .cert-grid { grid-template-columns: 1fr; }
            .highlight-banner { flex-direction: column; gap: 16px; text-align: center; }
            .highlight-right { border-left: none; padding-left: 0; text-align: center; }
            .cert-footer-row { flex-direction: column; gap: 20px; align-items: center; }
        }
    </style>
</head>
<body>

    <!-- Floating Top Toolbar -->
    <div class="cert-action-bar">
        <div class="action-bar-left">
            <i class="fas fa-award"></i>
            <span>Verified Savings Plan Maturity Voucher &bull; #<?php echo htmlspecialchars($certNumber); ?></span>
        </div>
        <div class="action-bar-right">
            <button class="btn-action btn-print" onclick="window.print();">
                <i class="fas fa-print"></i> Print Voucher
            </button>
            <a href="<?php echo htmlspecialchars($backLink); ?>" class="btn-action btn-back">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- The Certificate Voucher -->
    <div class="cert-card">
        <div class="cert-corner top-left"></div>
        <div class="cert-corner top-right"></div>
        <div class="cert-corner bottom-left"></div>
        <div class="cert-corner bottom-right"></div>

        <div class="cert-watermark">MATURITY</div>

        <div class="cert-content">
            <!-- Header -->
            <div class="cert-header">
                <div class="cert-title-group">
                    <h1>Plan Maturity Certificate</h1>
                    <div class="sub-title">Liya's Furniture & Electronics Savings Scheme</div>
                    <div class="cert-meta-row">
                        <span>Voucher No: <span class="val"><?php echo htmlspecialchars($certNumber); ?></span></span>
                        <span>Maturity Date: <span class="val"><?php echo htmlspecialchars($maturityDateStr); ?></span></span>
                    </div>
                </div>

                <div class="cert-brand">
                    <?php if (!empty($logoBase64)): ?>
                        <img src="<?php echo $logoBase64; ?>" alt="Liya's Logo" class="company-logo">
                    <?php endif; ?>
                    <div class="company-name">Liya's Furniture & Electronics</div>
                    <div class="company-sub">A Unit of Pro Gee Dee Ventures Pvt. Ltd.</div>
                </div>
            </div>

            <!-- Ribbon -->
            <div class="maturity-ribbon">
                <div class="ribbon-text">
                    <i class="fas fa-check-circle"></i>
                    <span>Congratulations! All scheduled installments have been successfully completed & verified.</span>
                </div>
                <span class="ribbon-tag">100% Completed</span>
            </div>

            <!-- Highlights Banner -->
            <div class="highlight-banner">
                <div class="highlight-left">
                    <div class="hl-title">Total Redeemable Showroom Value</div>
                    <div class="hl-amount">₹ <?php echo number_format($totalSaved, 2); ?></div>
                    <div class="hl-words"><?php echo htmlspecialchars($amountInWords); ?></div>
                </div>
                <div class="highlight-right">
                    <div class="hl-status-badge">
                        <i class="fas fa-gem me-1"></i> Ready for Showroom Redemption
                    </div>
                    <div style="font-size: 11.5px; opacity: 0.85; margin-top: 8px;">
                        Valid across all furniture & appliances
                    </div>
                </div>
            </div>

            <!-- Grid Details -->
            <div class="cert-grid">
                <div class="cert-info-card">
                    <div class="card-label">Subscriber Details</div>
                    <div class="card-val-lg"><?php echo htmlspecialchars($subscription['CustomerName'] ?? 'Valued Customer'); ?></div>
                    <div class="card-val-sub">
                        ID: <strong><?php echo htmlspecialchars($subscription['CustomerUniqueID'] ?? 'N/A'); ?></strong> &bull; 
                        Mobile: <?php echo htmlspecialchars($subscription['Contact'] ?? $subscription['CustomerContact'] ?? 'N/A'); ?>
                    </div>
                    <?php if (!empty($subscription['Email'])): ?>
                        <div class="card-val-sub" style="font-size: 12px; color: #64748b;">
                            <?php echo htmlspecialchars($subscription['Email']); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="cert-info-card">
                    <div class="card-label">Scheme & Installment Summary</div>
                    <div class="card-val-lg"><?php echo htmlspecialchars($subscription['SchemeName'] ?? 'Savings Scheme'); ?></div>
                    <div class="card-val-sub">
                        Installments: <strong><?php echo $subscription['paid_installments'] ?? $subscription['TotalPayments']; ?> of <?php echo $subscription['TotalPayments'] ?? 12; ?> Paid</strong> &bull;
                        Monthly: ₹<?php echo number_format($subscription['MonthlyPayment'] ?? 0, 2); ?>
                    </div>
                    <div class="card-val-sub" style="font-size: 12px; color: #64748b;">
                        Tenure: <?php echo $startDateStr; ?> to <?php echo $maturityDateStr; ?>
                    </div>
                </div>
            </div>

            <!-- Footer Signatures & Official Stamp -->
            <div class="cert-footer-row">
                <div class="signature-box">
                    <div class="sig-line"></div>
                    <span class="sig-label">Authorized Signatory</span>
                    <span class="sig-sub">Liya's Accounts Department</span>
                </div>

                <div class="official-seal">
                    <div class="seal-inner">
                        <div class="seal-stars">&#9733; &#9733; &#9733;</div>
                        <div class="seal-title">PRO GEE DEE VENTURES</div>
                        <div class="seal-badge">MATURED</div>
                        <div class="seal-date"><?php echo htmlspecialchars($maturityDateStr); ?></div>
                        <div class="seal-foot">LIYA'S OFFICIAL</div>
                    </div>
                </div>

                <div class="signature-box">
                    <div class="sig-line"></div>
                    <span class="sig-label">Customer Signature</span>
                    <span class="sig-sub">Upon In-Store Redemption</span>
                </div>
            </div>

            <!-- Terms -->
            <div class="cert-terms">
                Ground Floor, Sri Mantame Complex, Mudipu Road, Kurnadu, Bantwal - 574153, Karnataka | Ph: +91 99951 94472 | Email: info@efsavings.in<br>
                Present this official voucher along with valid ID at Liya's Showroom to redeem your accumulated savings towards furniture and electronics.
            </div>
        </div>
    </div>

</body>
</html>
