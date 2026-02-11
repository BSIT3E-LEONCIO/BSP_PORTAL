<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION["admin_id"])) {
  header("Location: ../../public/admin_login.php");
  exit;
}

$receiptId = (int)($_GET["receipt_id"] ?? 0);
if ($receiptId <= 0) {
  echo "Invalid receipt.";
  exit;
}

$stmt = $conn->prepare("SELECT r.*, u.email, u.username, a.surname, a.firstname, a.council, a.serve_sub FROM receipts r JOIN users u ON r.user_id = u.id LEFT JOIN applications a ON a.user_id = u.id WHERE r.id = ? LIMIT 1");
$stmt->bind_param("i", $receiptId);
$stmt->execute();
$receipt = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$receipt) {
  echo "Receipt not found.";
  exit;
}

$autoPrint = isset($_GET["print"]) && $_GET["print"] === "1";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Receipt • BSP</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 0; padding: 24px; background: #f7f8fa; }
    .receipt { max-width: 720px; margin: 0 auto; background: #fff; padding: 28px; border-radius: 12px; box-shadow: 0 2px 8px #0001; }
    .receipt h1 { margin: 0 0 8px 0; font-size: 1.6em; }
    .meta { color: #666; margin-bottom: 16px; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 16px; }
    .row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #eee; }
    .label { font-weight: 600; color: #333; }
    .actions { margin-top: 16px; display: flex; gap: 8px; }
    .btn { border: none; border-radius: 6px; padding: 8px 14px; font-weight: 600; cursor: pointer; }
    .btn.Print { background: #43a047; color: #fff; }
    @media print {
      body { background: #fff; padding: 0; }
      .receipt { box-shadow: none; border-radius: 0; }
      .actions { display: none; }
    }
  </style>
</head>
<body>
  <div class="receipt">
    <h1>Boy Scouts of the Philippines</h1>
    <div class="meta">Registration Receipt</div>

    <div class="row"><span class="label">Receipt No</span><span><?= htmlspecialchars($receipt['receipt_no'] ?? '-') ?></span></div>
    <div class="row"><span class="label">AAR No</span><span><?= htmlspecialchars($receipt['aar_no'] ?? '-') ?></span></div>
    <div class="row"><span class="label">Date Released</span><span><?= htmlspecialchars($receipt['released_at'] ?? '-') ?></span></div>

    <div class="grid">
      <div>
        <div class="row"><span class="label">Name</span><span><?= htmlspecialchars(($receipt['surname'] ?? '') . ' ' . ($receipt['firstname'] ?? '')) ?></span></div>
        <div class="row"><span class="label">Email</span><span><?= htmlspecialchars($receipt['email'] ?? '') ?></span></div>
        <div class="row"><span class="label">Username</span><span><?= htmlspecialchars($receipt['username'] ?? '') ?></span></div>
      </div>
      <div>
        <div class="row"><span class="label">Council</span><span><?= htmlspecialchars($receipt['council'] ?? '') ?></span></div>
        <div class="row"><span class="label">Role</span><span><?= htmlspecialchars($receipt['serve_sub'] ?? '') ?></span></div>
      </div>
    </div>

    <div class="row"><span class="label">Paid For</span><span><?= htmlspecialchars($receipt['paid_for'] ?? 'AAR Form') ?></span></div>
    <div class="row"><span class="label">Amount</span><span>PHP <?= number_format((float)($receipt['amount'] ?? 0), 2) ?></span></div>
    <div class="row"><span class="label">Payment Method</span><span><?= htmlspecialchars($receipt['payment_method'] ?? 'N/A') ?></span></div>
    <div class="row"><span class="label">Note</span><span><?= htmlspecialchars($receipt['note'] ?? 'Do not lose the receipt.') ?></span></div>

    <div class="actions">
      <button class="btn Print" onclick="window.print()">Print / Save PDF</button>
    </div>
  </div>

  <?php if ($autoPrint): ?>
    <script>
      window.onload = function() {
        window.print();
      };
    </script>
  <?php endif; ?>
</body>
</html>
