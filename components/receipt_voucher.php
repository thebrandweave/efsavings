<?php
/**
 * Official Payment Receipt Voucher Component
 * Liya's Furniture & Electronics (A Unit of Pro Gee Dee Ventures Pvt. Ltd.)
 * Matches reference voucher format with brand logo and colors
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

// Format receipt number: RCP-LF1000-0042
$receiptNumber = 'RCP-' . str_pad($payment['PaymentID'], 6, '0', STR_PAD_LEFT);
$receiptDate = !empty($payment['VerifiedAt']) ? date('d / m / Y', strtotime($payment['VerifiedAt'])) : date('d / m / Y', strtotime($payment['SubmittedAt']));
$amountInWords = amountToWords($payment['Amount']);
$backLink = !empty($backUrl) ? $backUrl : 'javascript:window.close();';

// Logo path resolution
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
    <title>Payment Receipt - <?php echo htmlspecialchars($receiptNumber); ?> | Liya's Furniture & Electronics</title>
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($logoBase64); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-magenta: #9B0090;
            --brand-blue: #0B5CAD;
            --brand-dark: #1E293B;
            --brand-gray: #64748B;
            --receipt-bg: #FFFFFF;
            --line-color: #334155;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #0d1117;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 15px 40px;
        }

        /* Top Action Bar (hidden on print) */
        .receipt-action-bar {
            width: 100%;
            max-width: 900px;
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
            color: #38bdf8;
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

        /* The Main Receipt Paper Container */
        .receipt-card {
            width: 100%;
            max-width: 900px;
            background: var(--receipt-bg);
            border-radius: 6px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
            position: relative;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            padding: 36px 42px 30px;
        }

        /* Guilloche Wave Background Pattern */
        .guilloche-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 220px;
            pointer-events: none;
            z-index: 1;
            overflow: hidden;
        }

        .guilloche-bg svg {
            width: 100%;
            height: 100%;
            opacity: 0.85;
        }

        /* Subtle Watermark in center */
        .receipt-watermark {
            position: absolute;
            top: 54%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-18deg);
            font-size: 75px;
            font-weight: 900;
            color: rgba(155, 0, 144, 0.035);
            letter-spacing: 12px;
            text-transform: uppercase;
            pointer-events: none;
            user-select: none;
            z-index: 1;
            white-space: nowrap;
            font-family: 'Playfair Display', serif;
        }

        /* Content layer */
        .receipt-content {
            position: relative;
            z-index: 2;
        }

        /* Header Section */
        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 26px;
            padding-bottom: 12px;
        }

        .header-left {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .receipt-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #9B0090;
            line-height: 1.1;
        }

        .receipt-number-row {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin-top: 6px;
            font-size: 15px;
            color: #334155;
            font-weight: 600;
        }

        .receipt-number-row .num-val {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 800;
            font-size: 17px;
            color: #0f172a;
            letter-spacing: 0.5px;
        }

        .header-right {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
        }

        .company-brand-row {
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: flex-end;
            margin-bottom: 4px;
        }

        .company-logo {
            height: 48px;
            max-width: 170px;
            object-fit: contain;
        }

        .company-name {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #0f172a;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .company-sub {
            font-size: 11.5px;
            color: #64748b;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .receipt-date-row {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            font-size: 14.5px;
            font-weight: 700;
            color: #1e293b;
        }

        .receipt-date-row .date-val {
            border-bottom: 1px solid #475569;
            padding: 0 10px;
            min-width: 110px;
            text-align: center;
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
        }

        /* Voucher Body Fill-in Rows */
        .receipt-body {
            display: flex;
            flex-direction: column;
            gap: 22px;
            margin-top: 15px;
            margin-bottom: 35px;
        }

        .fill-row {
            display: flex;
            align-items: flex-end;
            font-size: 15px;
            line-height: 1.4;
            color: #1e293b;
            width: 100%;
        }

        .fill-label {
            font-weight: 700;
            color: #1e293b;
            white-space: nowrap;
            margin-right: 10px;
            font-size: 15px;
        }

        .fill-line {
            flex: 1;
            border-bottom: 1.2px solid #64748b;
            padding-bottom: 3px;
            padding-left: 8px;
            padding-right: 8px;
            color: #090d16;
            font-size: 15px;
            display: flex;
            align-items: baseline;
            min-height: 24px;
        }

        .fill-line strong {
            font-weight: 700;
            color: #0f172a;
        }

        .split-row {
            display: flex;
            gap: 30px;
            width: 100%;
        }

        .split-half {
            flex: 1;
            display: flex;
            align-items: flex-end;
        }

        .amount-highlight {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            font-family: 'Plus Jakarta Sans', sans-serif;
            letter-spacing: 0.5px;
        }

        .utr-highlight {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            font-size: 15px;
            color: #0f172a;
        }

        .words-italic {
            font-family: 'Libre Baskerville', Georgia, serif;
            font-style: italic;
            font-size: 14.5px;
            color: #334155;
            font-weight: 600;
        }

        /* Bottom Verification & Signatures */
        .receipt-footer-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-top: 25px;
            margin-top: 10px;
            position: relative;
        }

        .signature-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 250px;
        }

        .sig-line {
            width: 100%;
            height: 1px;
            background: #475569;
            margin-bottom: 8px;
        }

        .sig-label {
            font-size: 13.5px;
            font-weight: 700;
            color: #334155;
            text-transform: capitalize;
        }

        .sig-name {
            font-size: 12.5px;
            color: #64748b;
            margin-top: 3px;
            font-weight: 500;
        }

        .stamp-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            margin-right: 40px;
        }

        .stamp-label {
            font-size: 13.5px;
            font-weight: 700;
            color: #334155;
            margin-top: 8px;
        }

        /* Realistic Official Ink Stamp */
        .official-stamp {
            width: 118px;
            height: 118px;
            border: 2.5px dashed #9B0090;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
            transform: rotate(-6deg);
            opacity: 0.88;
            box-shadow: inset 0 0 0 2px #9B0090;
            user-select: none;
            background: rgba(155, 0, 144, 0.03);
        }

        .stamp-inner {
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

        .stamp-stars {
            font-size: 7px;
            letter-spacing: 2px;
            margin-bottom: 2px;
        }

        .stamp-title {
            font-size: 7.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1;
        }

        .stamp-center-badge {
            margin: 3px 0;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 1px;
            border-top: 1px solid #9B0090;
            border-bottom: 1px solid #9B0090;
            padding: 1px 6px;
            text-transform: uppercase;
        }

        .stamp-date {
            font-size: 8px;
            font-weight: 700;
            font-family: 'Courier New', Courier, monospace;
        }

        .stamp-footer-text {
            font-size: 6.5px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* Legal / Compliance Disclaimer at very bottom */
        .receipt-disclaimer {
            margin-top: 28px;
            padding-top: 14px;
            border-top: 1px dotted #cbd5e1;
            font-size: 10.5px;
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

            .receipt-action-bar {
                display: none !important;
            }

            .receipt-card {
                border: 1.5px solid #64748b !important;
                box-shadow: none !important;
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 auto !important;
                padding: 30px 35px 25px !important;
                border-radius: 0 !important;
                page-break-inside: avoid;
            }

            @page {
                size: A4 landscape;
                margin: 12mm;
            }
        }

        @media (max-width: 768px) {
            .receipt-card {
                padding: 24px 20px 20px;
            }
            .receipt-header {
                flex-direction: column;
                gap: 16px;
            }
            .header-right {
                text-align: left;
                align-items: flex-start;
            }
            .split-row {
                flex-direction: column;
                gap: 20px;
            }
            .receipt-footer-row {
                flex-direction: column;
                align-items: center;
                gap: 25px;
            }
            .stamp-box {
                margin-right: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Top Toolbar (hidden on print) -->
    <div class="receipt-action-bar">
        <div class="action-bar-left">
            <i class="fas fa-check-circle"></i>
            <span>Verified Official Receipt &bull; #<?php echo htmlspecialchars($receiptNumber); ?></span>
        </div>
        <div class="action-bar-right">
            <button class="btn-action btn-print" onclick="window.print();">
                <i class="fas fa-print"></i> Print Receipt
            </button>
            <a href="<?php echo htmlspecialchars($backLink); ?>" class="btn-action btn-back">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- The Payment Receipt Voucher -->
    <div class="receipt-card" id="printableReceipt">
        
        <!-- Guilloche Wave Background Art -->
        <div class="guilloche-bg">
            <svg viewBox="0 0 900 220" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="guillocheGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#9B0090" stop-opacity="0.18"/>
                        <stop offset="50%" stop-color="#0B5CAD" stop-opacity="0.12"/>
                        <stop offset="100%" stop-color="#9B0090" stop-opacity="0.04"/>
                    </linearGradient>
                </defs>
                <path d="M0,25 Q220,85 450,25 T900,35" fill="none" stroke="url(#guillocheGrad)" stroke-width="1.3"/>
                <path d="M0,35 Q220,95 450,35 T900,45" fill="none" stroke="url(#guillocheGrad)" stroke-width="1.3"/>
                <path d="M0,45 Q220,105 450,45 T900,55" fill="none" stroke="url(#guillocheGrad)" stroke-width="1.3"/>
                <path d="M0,55 Q220,115 450,55 T900,65" fill="none" stroke="url(#guillocheGrad)" stroke-width="1.3"/>
                <path d="M0,65 Q220,125 450,65 T900,75" fill="none" stroke="url(#guillocheGrad)" stroke-width="1.3"/>
                <path d="M0,75 Q220,135 450,75 T900,85" fill="none" stroke="url(#guillocheGrad)" stroke-width="1.3"/>
                <path d="M0,85 Q220,145 450,85 T900,95" fill="none" stroke="url(#guillocheGrad)" stroke-width="1.3"/>
                <path d="M0,95 Q220,155 450,95 T900,105" fill="none" stroke="url(#guillocheGrad)" stroke-width="1.3"/>
                <path d="M0,105 Q220,165 450,105 T900,115" fill="none" stroke="url(#guillocheGrad)" stroke-width="1.3"/>
                <path d="M0,115 Q220,175 450,115 T900,125" fill="none" stroke="url(#guillocheGrad)" stroke-width="1.3"/>
            </svg>
        </div>

        <!-- Center Watermark -->
        <div class="receipt-watermark">LIYA'S</div>

        <div class="receipt-content">
            <!-- Header Section -->
            <div class="receipt-header">
                <div class="header-left">
                    <h1 class="receipt-title">Payment Receipt</h1>
                    <div class="receipt-number-row">
                        <span>No.</span>
                        <span class="num-val"><?php echo htmlspecialchars($receiptNumber); ?></span>
                    </div>
                </div>

                <div class="header-right">
                    <div class="company-brand-row">
                        <?php if (!empty($logoBase64)): ?>
                            <img src="<?php echo $logoBase64; ?>" alt="Liya's Logo" class="company-logo">
                        <?php endif; ?>
                        <div>
                            <div class="company-name">Liya's Furniture & Electronics</div>
                            <div class="company-sub">A Unit of Pro Gee Dee Ventures Pvt. Ltd.</div>
                        </div>
                    </div>
                    <div class="receipt-date-row">
                        <span>Date:</span>
                        <span class="date-val"><?php echo htmlspecialchars($receiptDate); ?></span>
                    </div>
                </div>
            </div>

            <!-- Receipt Form Fill-in Lines -->
            <div class="receipt-body">
                <!-- Received From -->
                <div class="fill-row">
                    <span class="fill-label">Received From:</span>
                    <span class="fill-line">
                        <strong><?php echo htmlspecialchars($payment['CustomerName'] ?? 'Valued Customer'); ?></strong>
                        <?php if (!empty($payment['CustomerUniqueID'])): ?>
                            &nbsp;(Customer ID: <strong><?php echo htmlspecialchars($payment['CustomerUniqueID']); ?></strong>)
                        <?php endif; ?>
                        <?php if (!empty($payment['CustomerContact'])): ?>
                            &nbsp;&bull;&nbsp;Mob: <?php echo htmlspecialchars($payment['CustomerContact']); ?>
                        <?php endif; ?>
                    </span>
                </div>

                <!-- Amount and Cash/Check/UTR -->
                <div class="split-row">
                    <div class="split-half">
                        <span class="fill-label">Amount:</span>
                        <span class="fill-line amount-highlight">
                            ₹ <?php echo number_format($payment['Amount'], 2); ?>
                        </span>
                    </div>
                    <div class="split-half">
                        <span class="fill-label">Cash/Check/UTR No.:</span>
                        <span class="fill-line utr-highlight">
                            <?php echo !empty($payment['UTRNumber']) ? htmlspecialchars($payment['UTRNumber']) : 'Cash / Direct'; ?>
                        </span>
                    </div>
                </div>

                <!-- For -->
                <div class="fill-row">
                    <span class="fill-label">For:</span>
                    <span class="fill-line">
                        <strong><?php echo htmlspecialchars($payment['SchemeName'] ?? 'Gold Savings Scheme'); ?></strong>
                        <?php if (!empty($payment['InstallmentNumber'])): ?>
                            &nbsp;&mdash;&nbsp;Installment #<strong><?php echo htmlspecialchars($payment['InstallmentNumber']); ?></strong>
                        <?php endif; ?>
                        <?php if (!empty($payment['PaymentID'])): ?>
                            &nbsp;(Ref: Pay #<?php echo $payment['PaymentID']; ?>)
                        <?php endif; ?>
                        <?php if (!empty($payment['StaffName'])): ?>
                            &nbsp;&bull;&nbsp;Staff: <?php echo htmlspecialchars($payment['StaffName']); ?>
                        <?php endif; ?>
                    </span>
                </div>

                <!-- Amount in Words -->
                <div class="fill-row">
                    <span class="fill-label">Amount in Words:</span>
                    <span class="fill-line words-italic">
                        <?php echo htmlspecialchars($amountInWords); ?>
                    </span>
                </div>
            </div>

            <!-- Bottom Row: Signatures and Official Stamp -->
            <div class="receipt-footer-row">
                <!-- Received By -->
                <div class="signature-box">
                    <div class="sig-line"></div>
                    <span class="sig-label">Received by</span>
                    <span class="sig-name"><?php echo htmlspecialchars(!empty($payment['StaffName']) ? $payment['StaffName'] : (!empty($payment['VerifierName']) ? $payment['VerifierName'] : 'Authorized Signatory')); ?></span>
                </div>

                <!-- Stamp -->
                <div class="stamp-box">
                    <div class="official-stamp">
                        <div class="stamp-inner">
                            <div class="stamp-stars">&#9733; &#9733; &#9733;</div>
                            <div class="stamp-title">PRO GEE DEE VENTURES</div>
                            <div class="stamp-center-badge">APPROVED</div>
                            <div class="stamp-date"><?php echo date('d-m-Y', strtotime($payment['VerifiedAt'] ?? 'now')); ?></div>
                            <div class="stamp-footer-text">LIYA'S OFFICIAL</div>
                        </div>
                    </div>
                    <span class="stamp-label">Stamp</span>
                </div>
            </div>

            <!-- Disclaimer -->
            <div class="receipt-disclaimer">
                Sheshashayi Complex, Seebinakere, Sagara Road, Thirthahalli, Karnataka | Ph: +91 88678 44051 | Email: liyasfurnitureandelectronics@gmail.com<br>
                This document is a computer-verified payment receipt issued towards Liya's Furniture & Electronics Monthly Savings Scheme. Valid upon official seal.
            </div>
        </div>
    </div>

</body>
</html>
