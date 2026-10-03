<?php
require_once '../config/config.php';
require_once '../config/session_check.php';

$c_path = "../";
$current_page = "support";

// Validate customer session
$userData = checkSession();
$customerId = $userData['customer_id'];

$successMsg = '';
$errorMsg = '';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Fetch customer details
    $stmt = $db->prepare("SELECT * FROM Customers WHERE CustomerID = ?");
    $stmt->execute([$customerId]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    // Process support query submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_query'])) {
        $subject = trim($_POST['subject'] ?? '');
        $category = trim($_POST['category'] ?? 'General');
        $message = trim($_POST['message'] ?? '');

        if (empty($subject) || empty($message)) {
            $errorMsg = "Please fill in both the subject and your message.";
        } else {
            // Insert into CustomerQueries table
            $stmt = $db->prepare("
                INSERT INTO CustomerQueries (CustomerID, Subject, Category, Message, Status)
                VALUES (?, ?, ?, ?, 'Open')
            ");
            $stmt->execute([$customerId, $subject, $category, $message]);
            $queryId = $db->lastInsertId();

            // Create admin notification
            $adminNotif = "Customer " . $customer['Name'] . " (" . ($customer['CustomerUniqueID'] ?? 'ID:'.$customerId) . ") submitted support ticket #TKT-" . $queryId . ": " . $subject;
            $stmt = $db->prepare("
                INSERT INTO Notifications (UserID, UserType, Message)
                VALUES (1, 'Admin', ?)
            ");
            $stmt->execute([$adminNotif]);

            // Log activity
            $stmt = $db->prepare("
                INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress)
                VALUES (?, 'Customer', ?, ?)
            ");
            $stmt->execute([$customerId, "Submitted support ticket #TKT-$queryId ($subject)", $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);

            $successMsg = "Your support request #TKT-$queryId has been submitted successfully! Our customer care desk will look into it promptly.";
        }
    }

    // Fetch customer's existing queries
    $stmt = $db->prepare("
        SELECT * FROM CustomerQueries 
        WHERE CustomerID = ? 
        ORDER BY CreatedAt DESC
    ");
    $stmt->execute([$customerId]);
    $existingQueries = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $errorMsg = "An error occurred: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact & Support | Liya's Furniture & Electronics</title>
    <link rel="icon" type="image/png" href="../uploads/liyas_logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --dark-bg: #071220;
            --card-bg: #0c1e34;
            --card-border: rgba(2, 132, 199, 0.22);
            --accent-brand: #0284c7;
            --accent-cyan: #38bdf8;
            --accent-green: #10b981;
            --text-primary: rgba(255, 255, 255, 0.95);
            --text-secondary: rgba(255, 255, 255, 0.7);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #5b70a3 0%, #000000 100%) fixed;
            background-attachment: fixed;
            color: var(--text-primary);
            min-height: 100vh;
        }

        .main-content {
            margin-left: 250px;
            padding: 95px 25px 40px;
            transition: all 0.3s ease;
            min-height: 100vh;
            background: transparent;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 85px 15px 30px;
            }
        }

        .page-header {
            margin-bottom: 25px;
            margin-top: 70px;
        }

        .page-header h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-header h2 i {
            color: #38bdf8;
        }

        .page-header p {
            color: var(--text-secondary);
            font-size: 14px;
            margin: 0;
        }

        /* Contact Channels Grid */
        .channels-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }

        .channel-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 22px 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.35);
            transition: transform 0.2s ease, border-color 0.2s ease;
            text-decoration: none;
            color: var(--text-primary);
        }

        .channel-card:hover {
            transform: translateY(-2px);
            border-color: #0284c7;
            color: var(--text-primary);
        }

        .channel-icon-wrap {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .channel-icon-wrap.phone {
            background: rgba(2, 132, 199, 0.15);
            color: #38bdf8;
            border: 1px solid rgba(2, 132, 199, 0.35);
        }

        .channel-icon-wrap.whatsapp {
            background: rgba(37, 211, 102, 0.15);
            color: #25d366;
            border: 1px solid rgba(37, 211, 102, 0.35);
        }

        .channel-icon-wrap.email {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.35);
        }

        .channel-icon-wrap.address {
            background: rgba(168, 85, 247, 0.15);
            color: #c084fc;
            border: 1px solid rgba(168, 85, 247, 0.35);
        }

        .channel-title {
            font-size: 15px;
            font-weight: 700;
            color: #fff;
        }

        .channel-val {
            font-size: 13.5px;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .channel-badge {
            font-size: 11.5px;
            font-weight: 600;
            margin-top: 4px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .channel-badge.btn-wa {
            color: #25d366;
        }

        .channel-badge.btn-call {
            color: #38bdf8;
        }

        /* 2-Column Section Layout */
        .support-columns {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        @media (max-width: 992px) {
            .support-columns {
                grid-template-columns: 1fr;
            }
        }

        .card-custom {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        }

        .card-custom-header {
            padding: 18px 22px;
            border-bottom: 1px solid rgba(2, 132, 199, 0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            font-weight: 700;
            color: #fff;
        }

        .card-custom-body {
            padding: 22px;
        }

        /* Form elements */
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 6px;
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.25);
            color: #fff;
        }

        .form-select option {
            background: #0c1e34;
            color: #ffffff;
        }

        .btn-submit-query {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            border: none;
            padding: 11px 22px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-submit-query:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(2, 132, 199, 0.55);
            color: #fff;
        }

        /* Queries List */
        .query-item {
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 10px;
            margin-bottom: 12px;
        }

        .query-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .query-ref {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            color: #38bdf8;
            font-size: 12.5px;
        }

        .query-status {
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 12px;
            text-transform: uppercase;
        }

        .status-open {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.35);
        }

        .status-in-progress {
            background: rgba(2, 132, 199, 0.15);
            color: #38bdf8;
            border: 1px solid rgba(2, 132, 199, 0.35);
        }

        .status-resolved {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.35);
        }

        .query-subject {
            font-weight: 600;
            color: #fff;
            font-size: 13.5px;
            margin-bottom: 4px;
        }

        .query-snippet {
            font-size: 12.5px;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        .query-date {
            font-size: 11px;
            color: #64748b;
            margin-top: 6px;
        }

        /* FAQ Accordion */
        .faq-item {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 14px 0;
        }

        .faq-item:last-child {
            border-bottom: none;
        }

        .faq-question {
            font-weight: 600;
            font-size: 14px;
            color: #ffffff;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-question i {
            color: #38bdf8;
            transition: transform 0.2s ease;
        }

        .faq-item.active .faq-question i {
            transform: rotate(180deg);
        }

        .faq-answer {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.6;
            margin-top: 8px;
            display: none;
        }

        .faq-item.active .faq-answer {
            display: block;
        }
    </style>
</head>
<body>

    <!-- Sidebar Component -->
    <?php include '../c_includes/sidebar.php'; ?>

    <!-- Topbar Component -->
    <?php include '../c_includes/topbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h2><i class="fas fa-headset"></i> Customer Support & Help Desk</h2>
            <p>We are here to assist you with your scheme contributions, payment verification, receipts, or account queries.</p>
        </div>

        <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 mb-4" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #34d399; border-radius: 10px;">
                <i class="fas fa-check-circle fs-5"></i>
                <div><?php echo htmlspecialchars($successMsg); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35); color: #f87171; border-radius: 10px;">
                <i class="fas fa-exclamation-circle fs-5"></i>
                <div><?php echo htmlspecialchars($errorMsg); ?></div>
            </div>
        <?php endif; ?>

        <!-- Direct Contact Channels -->
        <div class="channels-grid">
            <!-- WhatsApp -->
            <a href="https://wa.me/918867844051?text=Hello%20Liyas%20Customer%20Support,%20I%20need%20help%20with%20my%20account" target="_blank" class="channel-card">
                <div class="channel-icon-wrap whatsapp">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <div class="channel-title">WhatsApp Support</div>
                <div class="channel-val">Instant assistance for receipts & quick queries</div>
                <div class="channel-badge btn-wa">
                    <i class="fas fa-paper-plane"></i> Chat on WhatsApp &rarr;
                </div>
            </a>

            <!-- Phone Helpline -->
            <a href="tel:+918867844051" class="channel-card">
                <div class="channel-icon-wrap phone">
                    <i class="fas fa-phone"></i>
                </div>
                <div class="channel-title">Phone Helpline</div>
                <div class="channel-val">+91 88678 44051</div>
                <div class="channel-badge btn-call">
                    <i class="fas fa-headset"></i> Call Support &rarr;
                </div>
            </a>

            <!-- Email Support -->
            <a href="mailto:liyasfurnitureandelectronics@gmail.com" class="channel-card">
                <div class="channel-icon-wrap email">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="channel-title">Email Desk</div>
                <div class="channel-val">liyasfurnitureandelectronics@gmail.com</div>
                <div class="channel-badge" style="color: #fbbf24;">
                    <i class="fas fa-reply"></i> Send Email &rarr;
                </div>
            </a>

            <!-- Showroom / Head Office -->
            <div class="channel-card">
                <div class="channel-icon-wrap address">
                    <i class="fas fa-store"></i>
                </div>
                <div class="channel-title">Showroom & Desk</div>
                <div class="channel-val">
                    Sheshashayi Complex, Seebinakere, Sagara Road, Thirthahalli, Karnataka
                </div>
                <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">
                    <i class="fas fa-clock"></i> Mon - Sun: 9:30 AM - 7:00 PM
                </div>
            </div>
        </div>

        <!-- 2-Column Section: Submit Request & My Tickets / FAQs -->
        <div class="support-columns">
            <!-- Column 1: Submit Support Request Form -->
            <div class="card-custom">
                <div class="card-custom-header">
                    <i class="fas fa-pen-to-square" style="color: #38bdf8;"></i> Submit a Help Request / Query
                </div>
                <div class="card-custom-body">
                    <form method="POST" action="index.php">
                        <div class="mb-3">
                            <label class="form-label" for="categorySelect">Inquiry Category</label>
                            <select class="form-select" id="categorySelect" name="category" required>
                                <option value="Payment Verification">Payment / UTR Verification</option>
                                <option value="Receipt Issue">Payment Receipt / Voucher</option>
                                <option value="Scheme Details">Savings Scheme & Installments</option>
                                <option value="Lucky Draw Rewards">Monthly Lucky Draw & Prizes</option>
                                <option value="Showroom Redemption">Showroom Redemption Inquiry</option>
                                <option value="General">Other / General Question</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="subjectInput">Subject / Summary</label>
                            <input type="text" class="form-control" id="subjectInput" name="subject" placeholder="e.g. Inquiring about verification of payment #1042" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="messageTextarea">Detailed Message</label>
                            <textarea class="form-control" id="messageTextarea" name="message" rows="5" placeholder="Please describe your query or problem in detail. Mention any relevant UTR or scheme details." required></textarea>
                        </div>

                        <button type="submit" name="submit_query" class="btn-submit-query">
                            <i class="fas fa-paper-plane"></i> Submit Support Request
                        </button>
                    </form>
                </div>
            </div>

            <!-- Column 2: My Tickets & Frequently Asked Questions -->
            <div>
                <!-- My Tickets -->
                <div class="card-custom mb-4">
                    <div class="card-custom-header">
                        <i class="fas fa-clock-rotate-left" style="color: #38bdf8;"></i> My Recent Queries
                    </div>
                    <div class="card-custom-body">
                        <?php if (empty($existingQueries)): ?>
                            <div style="text-align: center; padding: 25px 10px; color: var(--text-secondary); font-size: 13.5px;">
                                <i class="fas fa-inbox fs-3 mb-2 d-block" style="opacity: 0.4;"></i>
                                You haven't submitted any queries yet. Use the form on the left if you need assistance!
                            </div>
                        <?php else: ?>
                            <div style="max-height: 280px; overflow-y: auto;">
                                <?php foreach ($existingQueries as $q): ?>
                                    <?php 
                                        $statusClass = 'status-' . strtolower(str_replace(' ', '-', $q['Status'])); 
                                    ?>
                                    <div class="query-item">
                                        <div class="query-header">
                                            <span class="query-ref">#TKT-<?php echo $q['QueryID']; ?> &bull; <?php echo htmlspecialchars($q['Category']); ?></span>
                                            <span class="query-status <?php echo $statusClass; ?>"><?php echo htmlspecialchars($q['Status']); ?></span>
                                        </div>
                                        <div class="query-subject"><?php echo htmlspecialchars($q['Subject']); ?></div>
                                        <div class="query-snippet"><?php echo htmlspecialchars(mb_strimwidth($q['Message'], 0, 100, '...')); ?></div>
                                        <div class="query-date"><i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y h:i A', strtotime($q['CreatedAt'])); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- FAQs -->
                <!-- <div class="card-custom">
                    <div class="card-custom-header">
                        <i class="fas fa-circle-question" style="color: #fbbf24;"></i> Frequently Asked Questions
                    </div>
                    <div class="card-custom-body">
                        <div class="faq-item active">
                            <div class="faq-question">
                                <span>How do I access and print my payment receipts?</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                Click on the <strong>Receipts</strong> tab in the sidebar. Every approved payment automatically generates a verified voucher that you can view, download, or print at any time.
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>How long does payment verification take?</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                Once you upload your payment screenshot with UTR number, our accounts team verifies it within 2 to 24 hours. You receive an instant SMS/notification upon approval.
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>When can I redeem my savings scheme?</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                100% of your accumulated scheme contributions can be redeemed at our showroom for luxury furniture, modular interiors, or smart home electronics upon completion or winning monthly draws.
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>Can I pay my installments via UPI or QR Code?</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                Yes! Navigate to the <strong>Payment QR</strong> section in the sidebar to scan the official company QR code, make the payment through Google Pay, PhonePe, or Paytm, and submit the UTR reference.
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle FAQ Accordion
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', function() {
                const parent = this.parentElement;
                parent.classList.toggle('active');
            });
        });
    </script>
</body>
</html>
