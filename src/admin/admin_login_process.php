<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  header("Location: ../../public/admin_login.php");
  exit;
}

$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

if ($username === "" || $password === "") {
  header("Location: ../../public/admin_login.php");
  exit;
}

$stmt = $conn->prepare("SELECT id, password_hash FROM admins WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if (!$admin) {
  header("Location: ../../public/admin_login.php?error=notfound");
  exit;
}

if (password_verify($password, $admin["password_hash"])) {
  $_SESSION["admin_id"] = $admin["id"];
  header("Location: admin.php");
  exit;
}

header("Location: ../../public/admin_login.php?error=invalid");
exit;
?>
