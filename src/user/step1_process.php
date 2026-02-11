<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION["user_id"])) {
  header("Location: ../../public/index.php");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  header("Location: step1.php"); // stays in src, so no change
  exit;
}

$userId = $_SESSION["user_id"];

$fields = [
  "surname" => trim($_POST["surname"] ?? ""),
  "firstname" => trim($_POST["firstname"] ?? ""),
  "mi" => trim($_POST["mi"] ?? ""),
  "sex" => trim($_POST["sex"] ?? ""),
  "civil_status" => trim($_POST["civil_status"] ?? ""),
  "tenure" => trim($_POST["tenure"] ?? ""),
  "serve_main" => trim($_POST["serve_main"] ?? ""),
  "serve_sub" => trim($_POST["serve_sub"] ?? ""),
  "sponsoring" => trim($_POST["sponsoring"] ?? ""),
  "council" => trim($_POST["council"] ?? "Navotas City Council"),
  "dob" => trim($_POST["dob"] ?? ""),
  "pob" => trim($_POST["pob"] ?? ""),
  "religion" => trim($_POST["religion"] ?? ""),
  "profession" => trim($_POST["profession"] ?? ""),
  "position" => trim($_POST["position"] ?? "")
];

if ($fields["surname"] === "" || $fields["firstname"] === "" || $fields["mi"] === "" || $fields["sex"] === "" || $fields["civil_status"] === "" || $fields["tenure"] === "" || $fields["sponsoring"] === "" || $fields["council"] === "" || $fields["dob"] === "" || $fields["pob"] === "" || $fields["religion"] === "" || $fields["profession"] === "" || $fields["position"] === "") {
  header("Location: step1.php"); // stays in src, so no change
  exit;
}

if ($fields["serve_main"] === "" || $fields["serve_sub"] === "") {
  header("Location: step1.php"); // stays in src, so no change
  exit;
}

$stmt = $conn->prepare(
  "INSERT INTO applications (user_id, surname, firstname, mi, sex, civil_status, tenure, serve_main, serve_sub, sponsoring_institutions, council, dob, pob, religion, profession, position_title, status)
   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
   ON DUPLICATE KEY UPDATE surname=VALUES(surname), firstname=VALUES(firstname), mi=VALUES(mi), sex=VALUES(sex), civil_status=VALUES(civil_status), tenure=VALUES(tenure), serve_main=VALUES(serve_main), serve_sub=VALUES(serve_sub), sponsoring_institutions=VALUES(sponsoring_institutions), council=VALUES(council), dob=VALUES(dob), pob=VALUES(pob), religion=VALUES(religion), profession=VALUES(profession), position_title=VALUES(position_title), status='pending'"
);

$dob = $fields["dob"] !== "" ? $fields["dob"] : null;

$stmt->bind_param(
  "isssssssssssssss",
  $userId,
  $fields["surname"],
  $fields["firstname"],
  $fields["mi"],
  $fields["sex"],
  $fields["civil_status"],
  $fields["tenure"],
  $fields["serve_main"],
  $fields["serve_sub"],
  $fields["sponsoring"],
  $fields["council"],
  $dob,
  $fields["pob"],
  $fields["religion"],
  $fields["profession"],
  $fields["position"]
);

$stmt->execute();
$stmt->close();

$payCheck = $conn->prepare("SELECT id FROM payments WHERE user_id = ? LIMIT 1");
$payCheck->bind_param("i", $userId);
$payCheck->execute();
$hasPayment = $payCheck->get_result()->fetch_assoc();
$payCheck->close();

if ($hasPayment) {
  header("Location: status.php"); // stays in src, so no change
  exit;
}

header("Location: safe.php"); // stays in src, so no change
exit;
?>
