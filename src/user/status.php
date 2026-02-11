<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION["user_id"])) {
  header("Location: ../../public/index.php");
  exit;
}

$userId = $_SESSION["user_id"];

$appStmt = $conn->prepare("SELECT status, created_at, serve_sub FROM applications WHERE user_id = ?");
$appStmt->bind_param("i", $userId);
$appStmt->execute();
$appResult = $appStmt->get_result();
$application = $appResult->fetch_assoc();
$appStmt->close();

$payStmt = $conn->prepare("SELECT status, uploaded_at FROM payments WHERE user_id = ? ORDER BY uploaded_at DESC LIMIT 1");
$payStmt->bind_param("i", $userId);
$payStmt->execute();
$payResult = $payStmt->get_result();
$payment = $payResult->fetch_assoc();
$payStmt->close();

$appStatus = $application["status"] ?? "not-submitted";
$paymentStatus = $payment["status"] ?? "not-submitted";
$paymentTime = $payment["uploaded_at"] ?? null;
$serveSub = $application["serve_sub"] ?? null;

// Blocked user overlay logic
$stmt = $conn->prepare("SELECT blocked_start, blocked_end, blocked_reason FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$blocked = $stmt->get_result()->fetch_assoc();
$stmt->close();
$blockedActive = false;
if ($blocked && $blocked["blocked_start"] && $blocked["blocked_end"]) {
  $today = date("Y-m-d");
  if ($today >= $blocked["blocked_start"] && $today <= $blocked["blocked_end"]) {
    $blockedActive = true;
  }
}

if ($appStatus === "approved" && $paymentStatus === "approved" && $serveSub) {
  $roleMap = [
    "Langkay Leader/Assistant" => "../roles/role_langkay.php",
    "Kawan Leader/Assistant" => "../roles/role_kawan.php",
    "Troop Leader/Assistant" => "../roles/role_troop.php",
    "Outfit Leader/Assistant" => "../roles/role_outfit.php",
    "Circle Manager/Assistant" => "../roles/role_circle.php",
    "Institutional Scouting Representative/ISCOM/ISC" => "../roles/role_iscom.php",
    "District/Municipal Commissioner/Coordinator/Member-at-Large" => "../roles/role_district.php",
    "Local Council" => "../roles/role_local_council.php"
  ];
  if (isset($roleMap[$serveSub])) {
    header("Location: " . $roleMap[$serveSub]);
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Application Status • BSP</title>
    <link rel="stylesheet" href="../../public/css/styles.css" />
    <style>
      .blocked-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0,0,0,0.4);
        backdrop-filter: blur(6px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .blocked-modal {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 16px #0003;
        padding: 40px 32px;
        max-width: 420px;
        text-align: center;
      }
      .blocked-modal h2 {
        color: #b71c1c;
        margin-bottom: 16px;
      }
      .blocked-modal p {
        color: #333;
        margin-bottom: 12px;
      }
      body.blocked {
        pointer-events: none;
        user-select: none;
      }
    </style>
  </head>
  <body>
    <?php if ($blockedActive): ?>
      <script>document.body.classList.add('blocked');</script>
      <div class="blocked-overlay">
        <div class="blocked-modal">
          <h2>Account Blocked</h2>
          <p>Your account has been blocked by the administrator.</p>
          <p><strong>Reason:</strong> <?php echo htmlspecialchars($blocked["blocked_reason"] ?? "Blocked by administrator."); ?></p>
          <p><strong>Blocked Period:</strong><br><?php echo htmlspecialchars($blocked["blocked_start"]); ?> to <?php echo htmlspecialchars($blocked["blocked_end"]); ?></p>
        </div>
      </div>
    <?php endif; ?>
    <div class="container">
      <section class="hero">
        <h1>Application Status</h1>
        <p>Check your Adult Registration and payment verification status.</p>
      </section>

      <section class="card">
        <div class="form-title">
          <h2>Status Overview</h2>
          <span class="badge">Dashboard</span>
        </div>
        <div class="grid">
          <div class="field">
            <label>Adult Registration Application</label>
            <div class="notice">
              <strong><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $appStatus))); ?></strong>
              <?php if ($appStatus === "pending") : ?>
                <p>Your AAR form is pending review.</p>
              <?php elseif ($appStatus === "approved") : ?>
                <p>Your AAR form is approved.</p>
              <?php elseif ($appStatus === "rejected") : ?>
                <p>Your AAR form needs correction. Please contact the council.</p>
              <?php else : ?>
                <p>Please complete the application form.</p>
              <?php endif; ?>
            </div>
          </div>
          <div class="field">
            <label>Payment Verification</label>
            <div class="notice">
              <strong><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $paymentStatus))); ?></strong>
              <?php if ($paymentStatus === "pending") : ?>
                <p>Payment proof submitted. Verification pending.</p>
                <?php if ($paymentTime) : ?>
                  <p>Submitted: <?php echo htmlspecialchars($paymentTime); ?></p>
                <?php endif; ?>
              <?php elseif ($paymentStatus === "approved") : ?>
                <p>Payment verified.</p>
              <?php elseif ($paymentStatus === "rejected") : ?>
                <p>Payment rejected. Please upload again.</p>
              <?php else : ?>
                <p>No payment submitted yet.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="button-row">
          <?php if ($appStatus === "not-submitted") : ?>
            <a class="btn" href="step1.php">Start Application</a>
          <?php else : ?>
            <a class="btn" href="step1.php">View / Update Application</a>
          <?php endif; ?>

          <?php if ($paymentStatus === "not-submitted") : ?>
            <a class="btn" href="payment.php">Submit Payment</a>
          <?php elseif ($paymentStatus === "rejected") : ?>
            <a class="btn" href="payment.php">View / Update Payment</a>
          <?php else : ?>
            <a class="btn" href="wait.php">View Waiting Timer</a>
          <?php endif; ?>

          <a class="btn ghost" href="logout.php">Logout</a>
        </div>
      </section>
    </div>
  </body>
</html>
