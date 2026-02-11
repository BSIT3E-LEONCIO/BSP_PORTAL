<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION["user_id"])) {
  header("Location: ../../public/index.php");
  exit;
}

$userId = $_SESSION["user_id"];
$expectedRole = "Kawan Leader/Assistant";

$appStmt = $conn->prepare("SELECT status, serve_sub FROM applications WHERE user_id = ?");
$appStmt->bind_param("i", $userId);
$appStmt->execute();
$app = $appStmt->get_result()->fetch_assoc();
$appStmt->close();

$payStmt = $conn->prepare("SELECT status FROM payments WHERE user_id = ? ORDER BY uploaded_at DESC LIMIT 1");
$payStmt->bind_param("i", $userId);
$payStmt->execute();
$payment = $payStmt->get_result()->fetch_assoc();
$payStmt->close();

if (!$app || $app["status"] !== "approved" || !$payment || $payment["status"] !== "approved" || $app["serve_sub"] !== $expectedRole) {
  header("Location: ../user/status.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kawan Leader Home • BSP</title>
    <link rel="stylesheet" href="../../public/css/styles.css" />
  </head>
  <body>
    <div class="container">
      <section class="hero">
        <h1>Kawan Leader/Assistant</h1>
        <p>Welcome to your role home page.</p>
      </section>

      <section class="card">
        <div class="form-title">
          <h2>Home</h2>
          <span class="badge">Approved</span>
        </div>
        <p>You are approved for the Kawan Leader/Assistant role.</p>
        <div class="button-row">
          <a class="btn" href="../user/status.php">View Status</a>
          <a class="btn ghost" href="../user/logout.php">Logout</a>
        </div>
      </section>
    </div>
  </body>
</html>
