<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION["user_id"])) {
  header("Location: ../../public/index.php");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  header("Location: payment.php");
  exit;
}

if (!isset($_FILES["payment_proof"]) || $_FILES["payment_proof"]["error"] !== UPLOAD_ERR_OK) {
  header("Location: payment.php");
  exit;
}

$uploadDir = __DIR__ . "/../../public/uploads/";
if (!is_dir($uploadDir)) {
  mkdir($uploadDir, 0755, true);
}

$filename = basename($_FILES["payment_proof"]["name"]);
$ext = pathinfo($filename, PATHINFO_EXTENSION);
$targetName = uniqid("payment_", true) . "." . $ext;
$targetPath = $uploadDir . $targetName;

if (!move_uploaded_file($_FILES["payment_proof"]["tmp_name"], $targetPath)) {
  header("Location: payment.php");
  exit;
}

$relativePath = "public/uploads/" . $targetName;
$userId = $_SESSION["user_id"];

$stmt = $conn->prepare("INSERT INTO payments (user_id, proof_path) VALUES (?, ?)");
$stmt->bind_param("is", $userId, $relativePath);
$stmt->execute();
$stmt->close();

header("Location: wait.php");
exit;
?>
