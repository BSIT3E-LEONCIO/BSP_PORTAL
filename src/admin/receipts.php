<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION["admin_id"])) {
  header("Location: ../../public/admin_login.php");
  exit;
}

$paymentJoin = "LEFT JOIN (SELECT p1.* FROM payments p1 INNER JOIN (SELECT user_id, MAX(uploaded_at) AS max_uploaded_at FROM payments GROUP BY user_id) p2 ON p1.user_id = p2.user_id AND p1.uploaded_at = p2.max_uploaded_at) p ON p.user_id = u.id";
$receipts = $conn->query("SELECT u.id AS user_id, u.email, u.username, u.membership_expires_at, a.surname, a.firstname, a.council, a.serve_sub, a.status AS app_status, p.status AS payment_status, r.id AS receipt_id, r.receipt_no, r.aar_no, r.paid_for, r.amount, r.payment_method, r.released_at, r.note FROM users u LEFT JOIN applications a ON a.user_id = u.id $paymentJoin LEFT JOIN receipts r ON r.user_id = u.id WHERE a.status = 'approved' AND p.status = 'approved' ORDER BY r.released_at DESC, a.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Receipts • BSP</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../public/assets/css/output.css" />
  <style>
    body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    #regTable th { font-size: 0.7rem; letter-spacing: 0.06em; }
    #regTable td { font-size: 0.85rem; }
    #regTable tbody tr:nth-child(even):not(.hidden) { background-color: #f9fafb; }
    #regTable tbody tr:hover { background-color: #f0fdf4 !important; }
  </style>
</head>
<body class="min-h-screen bg-gray-50 overflow-x-hidden">
  <div id="adminLayout" class="flex min-h-screen w-full transition-all duration-300">
    <?php include __DIR__ . '/admin_sidebar.php'; ?>
    <div class="flex flex-col w-full min-w-0">
      <div class="flex items-start mt-4">
        <button id="sidebarToggle" onclick="toggleSidebar()" style="margin-left:14px" class="z-50 bg-[#1F7D53] text-white p-3 rounded-full shadow-lg hover:bg-[#166540] transition-all duration-300 focus:outline-none">
          <i class="fas fa-bars text-lg"></i>
        </button>
      </div>
      <main id="adminMain" class="main flex-1 min-w-0 p-4 md:p-8 transition-all duration-300">
        <div class="mb-6">
          <h1 class="text-xl md:text-2xl font-bold text-gray-900 tracking-tight">Receipts</h1>
          <p class="text-gray-500 text-sm mt-0.5">View issued receipts and payment details</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="table-wrap overflow-x-auto min-w-0">
            <table id="regTable" class="w-full">
              <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                  <th class="sticky top-0 z-10 text-left px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap">Applicant</th>
                  <th class="sticky top-0 z-10 text-left px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap">Receipt No</th>
                  <th class="sticky top-0 z-10 text-left px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap">AAR No</th>
                  <th class="sticky top-0 z-10 text-left px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap">Paid For</th>
                  <th class="sticky top-0 z-10 text-left px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap">Amount</th>
                  <th class="sticky top-0 z-10 text-left px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap">Payment Method</th>
                  <th class="sticky top-0 z-10 text-left px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap">Released</th>
                  <th class="sticky top-0 z-10 text-left px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap">Membership Expiration</th>
                  <th class="sticky top-0 z-10 text-center px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
              <?php while ($receipts && $row = $receipts->fetch_assoc()): ?>
                <tr class="transition-colors duration-100">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-full bg-[#1F7D53] flex items-center justify-center text-white text-xs font-bold shrink-0">
                        <?= strtoupper(substr(($row['surname'] ?? 'U'), 0, 1)) ?>
                      </div>
                      <div class="min-w-0">
                        <div class="font-semibold text-gray-900 text-sm truncate"><?= htmlspecialchars(($row['surname'] ?? '') . ' ' . ($row['firstname'] ?? '')) ?></div>
                        <div class="text-gray-400 text-xs truncate"><?= htmlspecialchars($row['email'] ?? '') ?></div>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-gray-700 text-sm whitespace-nowrap"><?= htmlspecialchars($row['receipt_no'] ?? '-') ?></td>
                  <td class="px-4 py-3 text-gray-700 text-sm whitespace-nowrap"><?= htmlspecialchars($row['aar_no'] ?? '-') ?></td>
                  <td class="px-4 py-3 text-gray-700 text-sm whitespace-nowrap"><?= htmlspecialchars($row['paid_for'] ?? 'AAR Form') ?></td>
                  <td class="px-4 py-3 text-gray-700 text-sm whitespace-nowrap"><?= isset($row['amount']) ? 'PHP ' . number_format((float)$row['amount'], 2) : 'PHP 0.00' ?></td>
                  <td class="px-4 py-3 text-gray-700 text-sm whitespace-nowrap"><?= htmlspecialchars($row['payment_method'] ?? 'N/A') ?></td>
                  <td class="px-4 py-3 text-gray-700 text-sm whitespace-nowrap"><?= htmlspecialchars($row['released_at'] ?? '-') ?></td>
                  <td class="px-4 py-3 text-gray-700 text-sm whitespace-nowrap"><?= htmlspecialchars($row['membership_expires_at'] ?? '-') ?></td>
                  <td class="px-4 py-3 text-center">
                    <?php if (!empty($row['receipt_id'])): ?>
                      <div class="inline-flex items-center justify-center gap-1">
                        <a class="btn btn-ghost btn-sm" href="receipt_view.php?receipt_id=<?= (int)$row['receipt_id'] ?>" target="_blank" title="View"><i class="fas fa-eye text-sky-600"></i></a>
                        <a class="btn btn-ghost btn-sm" href="receipt_view.php?receipt_id=<?= (int)$row['receipt_id'] ?>&print=1" target="_blank" title="Save PDF"><i class="fas fa-file-pdf text-purple-600"></i></a>
                        <a class="btn btn-ghost btn-sm" href="receipt_view.php?receipt_id=<?= (int)$row['receipt_id'] ?>&print=1" target="_blank" title="Print"><i class="fas fa-print text-emerald-600"></i></a>
                      </div>
                    <?php else: ?>
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700">Pending auto-generation</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
  </div>
</body>
</html>
