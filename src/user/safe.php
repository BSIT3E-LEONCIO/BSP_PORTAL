<?php
session_start();
if (!isset($_SESSION["user_id"])) {
  header("Location: ../../public/index.php");
  exit;
}
require_once __DIR__ . '/../../includes/config.php';
$userId = $_SESSION["user_id"];
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
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Safe From Harm • BSP</title>
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
        <h1>Safe From Harm Policy</h1>
        <p>Review and agree before proceeding to payment.</p>
      </section>

      <section class="card">
        <div class="form-title">
          <h2>Policy Statement</h2>
          <span class="badge">Step 2</span>
        </div>

        <div class="notice">
          <p>
            World Scouting emphasizes that the achievement of Scouting’s Mission makes it essential for the Movement to provide young people
            with a “safe passage” based on respect for their integrity and their right to develop in a non-constraining environment. The Boy
            Scouts of the Philippines implements “Safe from Harm” on the conviction that all adults and children have a right NOT to be abused.
            This is a fundamental human right. Abuse can take the form of bullying, physical abuse, emotional abuse, neglect, sexual abuse and
            exploitation. It is important to note that young people can suffer from one or a combination of these forms of abuse. Abuse can take
            place at home, at school or anywhere young people spend time. In the great majority of cases, the abuser is someone the young person
            knows, such as a parent, teacher, relative, leader or friend. The main objective is to ensure that no one will be exposed to abuse.
            Good child protection practice means making sure that everyone is aware of signs of potential abuse. It is based upon the
            Declaration on the Rights of the Child and Human Rights.
          </p>
          <p>
            I hereby commit and fully subscribe to the existing Safe From Harm Policy of the Boy Scouts of the Philippines, and that I hereby
            absolve and free the BSP from any liability arising from any of my acts contrary to the policy. I hereby accept that the BSP may
            immediately revoke my registration as an adult leader upon violation of such policy.
          </p>
        </div>

        <div class="field" style="margin-top: 16px;">
          <label>
            <input id="safe-agree" type="checkbox" />
            I agree to the Safe From Harm Policy.
          </label>
        </div>

        <div class="button-row">
          <a class="btn ghost" href="step1.php">Back</a>
          <button id="safe-next" class="btn" type="button" data-href="payment.php" disabled>Proceed to Payment</button>
        </div>
      </section>
    </div>
    <script src="../../public/js/app.js"></script>
  </body>
</html>
