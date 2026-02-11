<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION["user_id"])) {
  header("Location: ../../public/index.php");
  exit;
}

$userId = $_SESSION["user_id"];
$stmt = $conn->prepare("SELECT UNIX_TIMESTAMP(uploaded_at) AS uploaded_ts FROM payments WHERE user_id = ? ORDER BY uploaded_at DESC LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result->fetch_assoc();
$stmt->close();

if (!$payment) {
  header("Location: payment.php");
  exit;
}

$startMs = ((int) $payment["uploaded_ts"]) * 1000;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verification in Progress • BSP</title>
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
    <?php
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
    if ($blockedActive): ?>
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
        <h1>Verification in Progress</h1>
        <p>Please wait 24 hours for payment approval.</p>
      </section>

      <section class="card">
        <div class="form-title">
          <h2>Waiting Timer</h2>
          <span class="badge">Step 4</span>
        </div>
        <p class="timer" id="timer" data-start-ms="<?php echo htmlspecialchars((string) $startMs); ?>">24:00:00</p>
        <p id="timer-message">We will notify you once verification is complete.</p>
        <div class="button-row">
          <button id="timer-login" class="btn" type="button">Back to Login</button>
          <a class="btn ghost" href="status.php">Check Status</a>
        </div>
      </section>
    </div>
    <script src="../../public/js/app.js"></script>
  </body>
</html>
