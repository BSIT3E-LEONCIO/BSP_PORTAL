<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  header("Location: signup.php");
  exit;
}

$email = trim($_POST["email"] ?? "");
$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

if ($email === "" || $username === "" || $password === "") {
  header("Location: signup.php");
  exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$check = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
$check->bind_param("ss", $email, $username);
$check->execute();
$exists = $check->get_result()->fetch_assoc();
$check->close();

if ($exists) {
  header("Location: signup.php?error=exists");
  exit;
}

$stmt = $conn->prepare("INSERT INTO users (email, username, password_hash) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $email, $username, $hash);

if ($stmt->execute()) {
  $_SESSION["user_id"] = $stmt->insert_id;
  $stmt->close();
  header("Location: step1.php");
  exit;
}

$stmt->close();
header("Location: signup.php");
exit;
?>
