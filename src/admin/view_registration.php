<?php
require_once __DIR__ . '/../../includes/config.php';
header('Content-Type: application/json');

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if (!$user_id) {
    echo json_encode(['error' => 'Invalid user id']);
    exit;
}

// Fetch application summary
$stmt = $conn->prepare("SELECT a.*, u.email, u.username FROM applications a JOIN users u ON a.user_id = u.id WHERE a.user_id = ? LIMIT 1");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$app = $stmt->get_result()->fetch_assoc();
$stmt->close();

$summary = [];
if ($app) {
    $summary = [
        'Name' => ($app['surname'] ?? '') . ' ' . ($app['firstname'] ?? ''),
        'Email' => $app['email'] ?? '',
        'Username' => $app['username'] ?? '',
        'Council' => $app['council'] ?? '',
        'Role' => $app['serve_sub'] ?? '',
        'Status' => isset($app['status']) ? ucfirst($app['status']) : '',
        'Submitted' => $app['created_at'] ?? '',
        'Middle Name' => $app['mi'] ?? '',
        'Sex' => $app['sex'] ?? '',
        'Civil Status' => $app['civil_status'] ?? '',
        'Tenure' => $app['tenure'] ?? '',
        'Serve Main' => $app['serve_main'] ?? '',
        'Sponsoring Institutions' => $app['sponsoring_institutions'] ?? '',
        'DOB' => $app['dob'] ?? '',
        'POB' => $app['pob'] ?? '',
        'Religion' => $app['religion'] ?? '',
        'Profession' => $app['profession'] ?? '',
        'Position Title' => $app['position_title'] ?? '',
    ];
} else {
    echo json_encode(['error' => 'No application found for this user.']);
    exit;
}

// Fetch payment images (assuming proof_path is front, and proof_path_back is back)
$pay = $conn->query("SELECT proof_path, proof_path_back FROM payments WHERE user_id = $user_id ORDER BY uploaded_at DESC LIMIT 1")->fetch_assoc();

$resolvePaymentPath = function ($path) {
    if (!$path) {
        return null;
    }
    $clean = ltrim($path, '/');
    if (strpos($clean, 'public/') === 0 || strpos($clean, 'src/') === 0) {
        return $clean;
    }
    $publicCandidate = __DIR__ . '/../../public/' . $clean;
    if (file_exists($publicCandidate)) {
        return 'public/' . $clean;
    }
    $srcCandidate = __DIR__ . '/../' . $clean;
    if (file_exists($srcCandidate)) {
        return 'src/' . $clean;
    }
    return 'public/' . $clean;
};

$payment_front = $resolvePaymentPath($pay['proof_path'] ?? null);
$payment_back = $resolvePaymentPath($pay['proof_path_back'] ?? null);

echo json_encode([
    'summary' => $summary,
    'payment_front' => $payment_front,
    'payment_back' => $payment_back
]);
