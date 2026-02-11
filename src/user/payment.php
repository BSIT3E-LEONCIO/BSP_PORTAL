<?php
session_start();
if (!isset($_SESSION["user_id"])) {
  header("Location: ../../public/index.php");
  exit;
}

require_once __DIR__ . '/../../includes/config.php';

$userId = $_SESSION["user_id"];
$stmt = $conn->prepare("SELECT serve_main FROM applications WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$app = $stmt->get_result()->fetch_assoc();
$stmt->close();

$serveMain = $app["serve_main"] ?? "";
$amount = 0;
$qrImage = null;
if ($serveMain === "unit") {
  $amount = 60;
  $qrImage = "../../public/assets/qr_60.png";
} elseif ($serveMain === "lay") {
  $amount = 100;
  $qrImage = "../../public/assets/qr_100.png";
}
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
$qrImagePath = $qrImage ? __DIR__ . "/../../public/assets/" . basename($qrImage) : null;
$qrExists = $qrImagePath && file_exists($qrImagePath);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mode of Payment • BSP</title>
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
        <h1>Mode of Payment</h1>
        <p>Upload your payment proof to begin verification.</p>
      </section>

      <section class="card">
        <div class="form-title">
          <h2>Payment Details</h2>
          <span class="badge">Step 3</span>
        </div>

        <div class="notice">
          <strong>QR Code:</strong>
          <?php if ($serveMain === "unit" || $serveMain === "lay") : ?>
            <p>Amount Due: PHP <?php echo number_format($amount, 2); ?></p>
            <?php if ($qrExists) : ?>
              <img src="<?php echo htmlspecialchars($qrImage); ?>" alt="Payment QR Code" style="max-width:220px;max-height:220px;border:1px solid #ccc;" />
            <?php else : ?>
              <p>QR code image is not available.</p>
            <?php endif; ?>
          <?php else : ?>
            <p>Please complete the application to see your payment QR code.</p>
          <?php endif; ?>
        </div>

        <form id="payment-form" method="post" action="payment_upload.php" enctype="multipart/form-data">
          <div class="field">
            <label for="payment-proof">Upload of Payment (image)</label>
            <input id="payment-proof" name="payment_proof" type="file" accept="image/*" required />
          </div>

          <div id="upload-preview" class="file-preview">Upload a payment proof image to continue.</div>

          <div class="button-row">
            <button id="payment-next" class="btn" type="submit" disabled>Submit Payment</button>
            <a class="btn ghost" href="safe.php">Back</a>
          </div>
        </form>
      </section>
    </div>
    <script src="../../public/js/app.js"></script>
  </body>
</html>
