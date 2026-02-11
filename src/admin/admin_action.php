<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION["admin_id"])) {
  header("Location: ../../public/admin_login.php");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  header("Location: admin.php");
  exit;
}

$action = $_POST["action"] ?? "";
$userId = (int)($_POST["user_id"] ?? 0);
$paymentId = (int)($_POST["payment_id"] ?? 0);

function ensureReceiptForUser(mysqli $conn, int $userId): void {
  if ($userId <= 0) {
    return;
  }

  $stmt = $conn->prepare("SELECT serve_main, status FROM applications WHERE user_id = ? LIMIT 1");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $app = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  $stmt = $conn->prepare("SELECT status FROM payments WHERE user_id = ? ORDER BY uploaded_at DESC LIMIT 1");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $payment = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$app || $app["status"] !== "approved" || !$payment || $payment["status"] !== "approved") {
    return;
  }

  $stmt = $conn->prepare("SELECT id FROM receipts WHERE user_id = ? LIMIT 1");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $existing = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if ($existing) {
    return;
  }

  $serveMain = $app["serve_main"] ?? "";
  $amount = 0.00;
  if ($serveMain === "unit") {
    $amount = 60.00;
  } elseif ($serveMain === "lay") {
    $amount = 100.00;
  }

  $paidFor = "AAR Form";
  $paymentMethod = "QR Code";
  $releasedAt = date("Y-m-d");
  $note = "Do not lose this receipt.";

  $stmt = $conn->prepare("INSERT INTO receipts (user_id, paid_for, amount, payment_method, released_at, note) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("isdsss", $userId, $paidFor, $amount, $paymentMethod, $releasedAt, $note);
  $stmt->execute();
  $receiptId = $stmt->insert_id;
  $stmt->close();

  if ($receiptId > 0) {
    $year = date("Y", strtotime($releasedAt));
    $seq = str_pad((string)$receiptId, 6, "0", STR_PAD_LEFT);
    $receiptNo = "R-" . $year . "-" . $seq;
    $aarNo = $year . "-" . $seq;
    $stmt = $conn->prepare("UPDATE receipts SET receipt_no = ?, aar_no = ? WHERE id = ?");
    $stmt->bind_param("ssi", $receiptNo, $aarNo, $receiptId);
    $stmt->execute();
    $stmt->close();

    $membershipExpires = date("Y-m-d", strtotime($releasedAt . " +1 year"));
    $stmt = $conn->prepare("UPDATE users SET membership_expires_at = ? WHERE id = ?");
    $stmt->bind_param("si", $membershipExpires, $userId);
    $stmt->execute();
    $stmt->close();
  }
}

switch ($action) {
  case "approve_app":
    $stmt = $conn->prepare("UPDATE applications SET status = 'approved' WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
    ensureReceiptForUser($conn, $userId);
    break;
  case "reject_app":
    $stmt = $conn->prepare("UPDATE applications SET status = 'rejected' WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
    break;
  case "approve_payment":
    if ($paymentId > 0) {
      $stmt = $conn->prepare("UPDATE payments SET status = 'approved' WHERE id = ?");
      $stmt->bind_param("i", $paymentId);
      $stmt->execute();
      $stmt->close();
    }
    if ($userId > 0) {
      ensureReceiptForUser($conn, $userId);
    }
    break;
  case "reject_payment":
    if ($paymentId > 0) {
      $stmt = $conn->prepare("UPDATE payments SET status = 'rejected' WHERE id = ?");
      $stmt->bind_param("i", $paymentId);
      $stmt->execute();
      $stmt->close();
    }
    break;
  case "block_user":
    $blockedStart = $_POST["blocked_start"] ?? "";
    $blockedEnd = $_POST["blocked_end"] ?? "";
    $blockedReason = trim($_POST["blocked_reason"] ?? "");
    if ($blockedReason === "") {
      $blockedReason = "Blocked by administrator.";
    }
    if ($userId > 0 && $blockedStart !== "" && $blockedEnd !== "") {
      $stmt = $conn->prepare("UPDATE users SET blocked_start = ?, blocked_end = ?, blocked_reason = ? WHERE id = ?");
      $stmt->bind_param("sssi", $blockedStart, $blockedEnd, $blockedReason, $userId);
      $stmt->execute();
      $stmt->close();
    }
    break;
  case "unblock_user":
    if ($userId > 0) {
      $stmt = $conn->prepare("UPDATE users SET blocked_start = NULL, blocked_end = NULL, blocked_reason = NULL WHERE id = ?");
      $stmt->bind_param("i", $userId);
      $stmt->execute();
      $stmt->close();
    }
    break;
  case "edit_user":
    $newEmail = trim($_POST["email"] ?? "");
    $newUsername = trim($_POST["username"] ?? "");
    $newPassword = $_POST["password"] ?? "";
    if ($userId > 0 && $newEmail !== "" && $newUsername !== "") {
      if ($newPassword !== "") {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET email = ?, username = ?, password_hash = ? WHERE id = ?");
        $stmt->bind_param("sssi", $newEmail, $newUsername, $passwordHash, $userId);
      } else {
        $stmt = $conn->prepare("UPDATE users SET email = ?, username = ? WHERE id = ?");
        $stmt->bind_param("ssi", $newEmail, $newUsername, $userId);
      }
      $stmt->execute();
      $stmt->close();
    }
    break;
}

// Redirect to the correct view if provided, else dashboard
$redirect = 'admin.php';
if (isset($_POST['redirect']) && in_array($_POST['redirect'], ['dashboard','pending','approved','rejected','manage_users','receipts'])) {
  if ($_POST['redirect'] === 'manage_users') {
    $redirect = 'manage_users.php';
  } elseif ($_POST['redirect'] === 'receipts') {
    $redirect = 'receipts.php';
  } else {
    $redirect .= '?view=' . $_POST['redirect'];
  }
}
header("Location: $redirect");
exit;
?>
