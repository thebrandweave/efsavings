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
    $_SESSION['error_message'] = "No installment ID specified.";
    header("Location: ../index.php");
    exit();
}

$installmentId = intval($_GET['id']);

// Fetch installment
$stmt = $conn->prepare("
    SELECT i.*, s.SchemeName 
    FROM Installments i 
    JOIN Schemes s ON i.SchemeID = s.SchemeID 
    WHERE i.InstallmentID = ?
");
$stmt->execute([$installmentId]);
$installment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$installment) {
    $_SESSION['error_message'] = "Installment not found.";
    header("Location: ../index.php");
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $installmentName = trim($_POST['installment_name'] ?? '');
    $installmentNumber = intval($_POST['installment_number'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);
    $drawDate = !empty($_POST['draw_date']) ? $_POST['draw_date'] : null;
    $benefits = trim($_POST['benefits'] ?? '');
    $status = in_array($_POST['status'] ?? '', ['Active', 'Inactive']) ? $_POST['status'] : 'Active';
    $isReplayable = isset($_POST['is_replayable']) ? 1 : 0;
    $replaymentPercentage = floatval($_POST['replayment_percentage'] ?? 0);

    if (empty($installmentName)) {
        $errors[] = "Installment name is required.";
    }
    if ($installmentNumber <= 0) {
        $errors[] = "Installment number must be greater than 0.";
    }
    if ($amount < 0) {
        $errors[] = "Amount cannot be negative.";
    }

    if (empty($errors)) {
        try {
            $conn->beginTransaction();

            $stmt = $conn->prepare("
                UPDATE Installments 
                SET InstallmentName = ?, 
                    InstallmentNumber = ?, 
                    Amount = ?, 
                    DrawDate = ?, 
                    Benefits = ?, 
                    Status = ?, 
                    IsReplayable = ?, 
                    ReplaymentPercentage = ? 
                WHERE InstallmentID = ?
            ");
            $stmt->execute([
                $installmentName,
                $installmentNumber,
                $amount,
                $drawDate,
                $benefits,
                $status,
                $isReplayable,
                $replaymentPercentage,
                $installmentId
            ]);

            // Log activity
            $action = "Updated installment #{$installmentNumber} for Scheme ID: {$installment['SchemeID']}";
            $stmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Admin', ?, ?)");
            $stmt->execute([$_SESSION['admin_id'], $action, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);

            $conn->commit();
            $_SESSION['success_message'] = "Installment updated successfully.";
            header("Location: index.php?id=" . $installmentId);
            exit();

        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

include("../../components/sidebar.php");
include("../../components/topbar.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Installment - <?php echo htmlspecialchars($installment['InstallmentName']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-primary: #9B0090;
            --brand-secondary: #0B5CAD;
            --brand-obsidian: #0B0F19;
            --border-light: #e2e8f0;
        }
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        .edit-container { max-width: 800px; margin: 30px auto; padding: 0 20px; }
        .card { background: white; border-radius: 12px; border: 1px solid var(--border-light); padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid var(--border-light); padding-bottom: 15px; }
        .card-header h2 { margin: 0; font-size: 20px; color: var(--brand-obsidian); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 500; font-size: 13.5px; margin-bottom: 6px; color: #334155; }
        .form-control { width: 100%; padding: 10px 14px; border: 1px solid var(--border-light); border-radius: 8px; font-size: 14px; outline: none; }
        .form-control:focus { border-color: var(--brand-primary); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .btn-submit { background: linear-gradient(135deg, #9B0090, #0B5CAD); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .btn-cancel { background: #f1f5f9; color: #475569; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 500; margin-left: 10px; display: inline-block; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="edit-container">
            <div class="card">
                <div class="card-header">
                    <h2>Edit Installment - <?php echo htmlspecialchars($installment['SchemeName']); ?></h2>
                    <a href="index.php?id=<?php echo $installmentId; ?>" class="btn-cancel">Back</a>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert-error">
                        <?php foreach ($errors as $err): ?>
                            <div><i class="fas fa-circle-exclamation"></i> <?php echo htmlspecialchars($err); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Installment Number *</label>
                            <input type="number" name="installment_number" class="form-control" value="<?php echo htmlspecialchars($installment['InstallmentNumber']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Installment Name *</label>
                            <input type="text" name="installment_name" class="form-control" value="<?php echo htmlspecialchars($installment['InstallmentName']); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Amount (₹) *</label>
                            <input type="number" step="0.01" name="amount" class="form-control" value="<?php echo htmlspecialchars($installment['Amount']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Draw Date</label>
                            <input type="date" name="draw_date" class="form-control" value="<?php echo htmlspecialchars($installment['DrawDate'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="Active" <?php echo ($installment['Status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?php echo ($installment['Status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Replayment Percentage (%)</label>
                            <input type="number" step="0.01" name="replayment_percentage" class="form-control" value="<?php echo htmlspecialchars($installment['ReplaymentPercentage'] ?? 0); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Benefits / Prize Details</label>
                        <textarea name="benefits" class="form-control" rows="4"><?php echo htmlspecialchars($installment['Benefits'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_replayable" value="1" <?php echo !empty($installment['IsReplayable']) ? 'checked' : ''; ?>>
                            Is Replayable (Eligible for repayment/reclaim)
                        </label>
                    </div>

                    <div style="margin-top: 25px;">
                        <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Save Changes</button>
                        <a href="index.php?id=<?php echo $installmentId; ?>" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
