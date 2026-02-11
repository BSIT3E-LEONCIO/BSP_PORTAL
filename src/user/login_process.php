<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  header("Location: ../../public/index.php");
  exit;
}

$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

if ($username === "" || $password === "") {
  header("Location: ../../public/index.php");
  exit;
}

$stmt = $conn->prepare("SELECT id, password_hash, blocked_start, blocked_end, blocked_reason FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
  header("Location: ../../public/index.php?error=notfound");
  exit;
}

if (password_verify($password, $user["password_hash"])) {
  $blockedStart = $user["blocked_start"] ?? null;
  $blockedEnd = $user["blocked_end"] ?? null;
  if ($blockedStart && $blockedEnd) {
    $today = date("Y-m-d");
    if ($today >= $blockedStart && $today <= $blockedEnd) {
      $reason = urlencode($user["blocked_reason"] ?? "Blocked by administrator.");
      header("Location: ../../public/index.php?error=blocked&reason=$reason&start=$blockedStart&end=$blockedEnd");
      exit;
    }
  }
  $_SESSION["user_id"] = $user["id"];
  header("Location: status.php");
  exit;
}

header("Location: ../../public/index.php?error=invalid");
exit;
?>
