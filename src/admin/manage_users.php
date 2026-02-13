<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION["admin_id"])) {
  header("Location: ../../public/admin_login.php");
  exit;
}

$paymentJoin = "LEFT JOIN (SELECT p1.* FROM payments p1 INNER JOIN (SELECT user_id, MAX(uploaded_at) AS max_uploaded_at FROM payments GROUP BY user_id) p2 ON p1.user_id = p2.user_id AND p1.uploaded_at = p2.max_uploaded_at) p ON p.user_id = u.id";
$users = $conn->query("SELECT u.id, u.email, u.username, u.membership_expires_at, u.blocked_start, u.blocked_end, u.blocked_reason, a.status AS app_status, p.status AS payment_status FROM users u LEFT JOIN applications a ON a.user_id = u.id $paymentJoin ORDER BY u.created_at DESC");
$today = date("Y-m-d");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Users • BSP</title>
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
    .inline-form { display: none; }
    .m-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 0.85rem 1rem; margin-bottom: 0.5rem; }
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
          <h1 class="text-xl md:text-2xl font-bold text-gray-900 tracking-tight">Manage Users</h1>
          <p class="text-gray-500 text-sm mt-0.5">Review and manage user access and status</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="border-b border-gray-200 px-4 py-3 md:px-6 md:py-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
              <div class="flex flex-wrap items-center gap-2.5">
                <div class="relative">
                  <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                  <input id="tableSearch" type="text" class="w-56 md:w-72 h-9 pl-9 pr-3 text-sm rounded-lg border border-gray-300 bg-white placeholder-gray-400 focus:border-[#1F7D53] focus:ring-2 focus:ring-[#1F7D53]/20 outline-none transition" placeholder="Search user, email..." />
                </div>
                <div class="flex items-center gap-1.5 h-9 px-3 rounded-lg border border-gray-300 bg-white">
                  <span class="text-xs text-gray-500 font-medium">Show</span>
                  <select id="rowsPerPage" class="bg-transparent border-0 text-sm font-semibold outline-none cursor-pointer pr-1">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                  </select>
                </div>
                <span class="hidden md:inline-flex items-center gap-1 text-xs text-gray-500 font-medium ml-1"><span id="totalCount" class="font-bold text-[#1F7D53]">0</span> entries</span>
              </div>
            </div>
          </div>

          <div class="table-wrap overflow-x-auto min-w-0">
            <table id="regTable" class="hidden md:table w-full">
              <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                  <th class="sticky top-0 z-10 text-left px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap">User</th>
                  <th class="sticky top-0 z-10 text-left px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap">Application Status</th>
                  <th class="sticky top-0 z-10 text-left px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap">Membership</th>
                  <th class="sticky top-0 z-10 text-left px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap">Expiration</th>
                  <th class="sticky top-0 z-10 text-left px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap">Block Status</th>
                  <th class="sticky top-0 z-10 text-center px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap w-28">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <?php $i = 0; $mobileCards = ''; while ($users && $row = $users->fetch_assoc()): $idx = $i++;
                  $appStatus = $row['app_status'] ?? 'not-submitted';
                  $paymentStatus = $row['payment_status'] ?? 'not-submitted';
                  $appLabel = $appStatus === 'not-submitted' ? 'Not Submitted' : ucfirst($appStatus);
                  $appClass = $appStatus === 'not-submitted' ? 'bg-gray-100 text-gray-600' : ($appStatus === 'approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700');
                  $payLabel = $paymentStatus === 'not-submitted' ? 'Not Submitted' : ucfirst($paymentStatus);
                  $payClass = $paymentStatus === 'not-submitted' ? 'bg-gray-100 text-gray-600' : ($paymentStatus === 'approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700');
                  $membershipStatus = ($appStatus === 'approved' && $paymentStatus === 'approved') ? 'NEW!' : 'Pending';
                  $expiresAt = $row['membership_expires_at'] ?? '-';
                  $blockedActive = $row['blocked_start'] && $row['blocked_end'] && $today >= $row['blocked_start'] && $today <= $row['blocked_end'];
                ?>
                  <tr data-index="<?= $idx ?>" class="table-row-item transition-colors duration-100">
                    <td class="px-4 py-3 whitespace-nowrap">
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-[#1F7D53] flex items-center justify-center text-white text-xs font-bold shrink-0"><?= strtoupper(substr($row['username'], 0, 1)) ?></div>
                        <div class="min-w-0">
                          <div class="font-semibold text-gray-900 text-sm truncate"><?= htmlspecialchars($row['username']) ?></div>
                          <div class="text-gray-400 text-xs truncate"><?= htmlspecialchars($row['email']) ?></div>
                        </div>
                      </div>
                    </td>
                    <td class="px-4 py-3">
                      <div class="flex flex-col gap-1">
                        <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold <?= $appClass ?>">AAR <?= $appLabel ?></span>
                        <span class="inline-block px-2 py-0.5 rounded text-[11px] font-medium <?= $payClass ?>">Payment <?= $payLabel ?></span>
                      </div>
                    </td>
                    <td class="px-4 py-3"><span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold <?= $membershipStatus === 'NEW!' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' ?>"><?= $membershipStatus ?></span></td>
                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap text-sm"><?= htmlspecialchars($expiresAt) ?></td>
                    <td class="px-4 py-3">
                      <?php if ($blockedActive): ?>
                        <div class="flex flex-col gap-1">
                          <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-700">Blocked</span>
                          <small class="text-xs text-gray-500"><?= htmlspecialchars($row['blocked_start'] . ' to ' . $row['blocked_end']) ?></small>
                          <small class="text-xs text-gray-500"><?= htmlspecialchars($row['blocked_reason'] ?? '') ?></small>
                        </div>
                      <?php else: ?>
                        <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700">Active</span>
                      <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                      <div class="flex items-center justify-center gap-1">
                        <button class="btn btn-ghost btn-sm" data-toggle="block-<?= (int)$row['id'] ?>" title="Block"><i class="fas fa-ban text-orange-500"></i></button>
                        <?php if ($blockedActive): ?>
                          <form method="post" action="admin_action.php" style="display:inline;">
                            <input type="hidden" name="action" value="unblock_user" />
                            <input type="hidden" name="user_id" value="<?= (int)$row['id'] ?>" />
                            <input type="hidden" name="redirect" value="manage_users" />
                            <button class="btn btn-ghost btn-sm text-emerald-600" type="submit" title="Unblock"><i class="fas fa-lock-open"></i></button>
                          </form>
                        <?php endif; ?>
                        <button class="btn btn-ghost btn-sm" data-edit="<?= (int)$row['id'] ?>" data-username="<?= htmlspecialchars($row['username']) ?>" data-email="<?= htmlspecialchars($row['email']) ?>" title="Edit"><i class="fas fa-pen"></i></button>
                      </div>
                      <form method="post" action="admin_action.php" class="inline-form mt-2" id="block-<?= (int)$row['id'] ?>">
                        <input type="hidden" name="action" value="block_user" />
                        <input type="hidden" name="user_id" value="<?= (int)$row['id'] ?>" />
                        <input type="hidden" name="redirect" value="manage_users" />
                        <div class="flex items-center gap-2 mt-2">
                          <input type="date" name="blocked_start" required class="h-8 px-2 border rounded" />
                          <input type="date" name="blocked_end" required class="h-8 px-2 border rounded" />
                          <input type="text" name="blocked_reason" placeholder="Reason (optional)" class="h-8 px-2 border rounded" />
                          <button class="btn btn-sm bg-[#1F7D53] text-white" type="submit">Save</button>
                          <button class="btn btn-sm btn-ghost" type="button" data-cancel="block-<?= (int)$row['id'] ?>">Cancel</button>
                        </div>
                      </form>
                    </td>
                  </tr>
                  <?php ob_start(); ?>
                  <div data-index="<?= $idx ?>" class="m-card block md:hidden">
                    <div class="flex items-start gap-3">
                      <div class="w-9 h-9 rounded-full bg-[#1F7D53] flex items-center justify-center text-white text-xs font-bold shrink-0 mt-0.5"><?= strtoupper(substr($row['username'], 0, 1)) ?></div>
                      <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                          <div class="min-w-0">
                            <div class="font-semibold text-gray-900 text-sm truncate"><?= htmlspecialchars($row['username']) ?></div>
                            <div class="text-gray-400 text-xs truncate"><?= htmlspecialchars($row['email']) ?></div>
                          </div>
                          <div class="flex flex-col items-end gap-1 shrink-0">
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold <?= $blockedActive ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700' ?>"><?= $blockedActive ? 'Blocked' : 'Active' ?></span>
                          </div>
                        </div>
                        <div class="mt-1.5 text-xs text-gray-500">
                          <span>AAR: <?= $appLabel ?></span>
                          <span class="mx-1">&middot;</span>
                          <span>Pay: <?= $payLabel ?></span>
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5 truncate">Expires: <?= htmlspecialchars($expiresAt) ?></div>
                        <div class="flex items-center gap-2 mt-2 pt-2 border-t border-gray-100">
                          <button class="btn btn-ghost btn-xs" data-toggle="block-<?= (int)$row['id'] ?>" title="Block"><i class="fas fa-ban text-orange-500"></i></button>
                          <?php if ($blockedActive): ?>
                            <form method="post" action="admin_action.php" class="inline">
                              <input type="hidden" name="action" value="unblock_user" />
                              <input type="hidden" name="user_id" value="<?= (int)$row['id'] ?>" />
                              <input type="hidden" name="redirect" value="manage_users" />
                              <button class="btn btn-ghost btn-xs" type="submit" title="Unblock"><i class="fas fa-lock-open text-emerald-600"></i></button>
                            </form>
                          <?php endif; ?>
                          <button class="btn btn-ghost btn-xs" data-edit="<?= (int)$row['id'] ?>" data-username="<?= htmlspecialchars($row['username']) ?>" data-email="<?= htmlspecialchars($row['email']) ?>" title="Edit"><i class="fas fa-pen"></i></button>
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php $mobileCards .= ob_get_clean(); ?>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
          <?php if (!empty($mobileCards)): ?>
            <div class="md:hidden px-4 py-3"><?= $mobileCards ?></div>
          <?php endif; ?>
          <div class="border-t border-gray-200 px-4 py-3 md:px-6 flex items-center justify-between text-xs text-gray-500">
            <span id="tableInfoFooter">Showing 0 to 0 of 0 entries</span>
            <div class="flex items-center gap-1.5">
              <button id="prevPageFooter" class="h-8 px-2.5 rounded border border-gray-300 bg-white text-gray-600 hover:bg-[#1F7D53] hover:text-white hover:border-[#1F7D53] disabled:opacity-40 transition text-xs"><i class="fas fa-chevron-left text-[9px]"></i> Prev</button>
              <button id="nextPageFooter" class="h-8 px-2.5 rounded border border-gray-300 bg-white text-gray-600 hover:bg-[#1F7D53] hover:text-white hover:border-[#1F7D53] disabled:opacity-40 transition text-xs">Next <i class="fas fa-chevron-right text-[9px]"></i></button>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <div id="editUserModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:#0008;z-index:1200;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:24px;border-radius:12px;max-width:460px;width:92vw;position:relative;">
      <button id="closeEditUserModal" style="position:absolute;top:12px;right:16px;font-size:1.5em;background:none;border:none;cursor:pointer;">&times;</button>
      <h3>Edit User</h3>
      <form method="post" action="admin_action.php" id="editUserForm">
        <input type="hidden" name="action" value="edit_user" />
        <input type="hidden" name="user_id" id="editUserId" value="" />
        <input type="hidden" name="redirect" value="manage_users" />
        <div style="display:grid;gap:12px;">
          <input type="text" name="username" id="editUsername" placeholder="Username" required />
          <input type="email" name="email" id="editEmail" placeholder="Email" required />
          <input type="password" name="password" id="editPassword" placeholder="New password (optional)" />
        </div>
        <div style="margin-top:16px;display:flex;gap:8px;">
          <button class="btn Edit" type="submit">Save</button>
          <button class="btn Cancel" type="button" id="cancelEditUser">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('tableSearch');
      const rowsPerPageSelect = document.getElementById('rowsPerPage');
      const prevBtnF = document.getElementById('prevPageFooter');
      const nextBtnF = document.getElementById('nextPageFooter');
      const infoBot = document.getElementById('tableInfoFooter');
      const totalCountEl = document.getElementById('totalCount');

      const indexedEls = Array.from(document.querySelectorAll('[data-index]')).map(el => el.getAttribute('data-index'));
      const uniqueIndices = [...new Set(indexedEls)].sort((a, b) => +a - +b);
      const items = uniqueIndices.map(idx => {
        const tableEl = document.querySelector('tr[data-index="' + idx + '"]');
        const cardEl = document.querySelector('.m-card[data-index="' + idx + '"]');
        const text = ((tableEl && tableEl.textContent) || '') + ' ' + ((cardEl && cardEl.textContent) || '');
        return { idx, tableEl, cardEl, text };
      });

      let filtered = items.slice();
      let currentPage = 1;
      let rowsPerPage = parseInt(rowsPerPageSelect.value, 10);
      totalCountEl.textContent = items.length;

      function render() {
        const total = filtered.length;
        const totalPages = Math.max(1, Math.ceil(total / rowsPerPage));
        if (currentPage > totalPages) currentPage = totalPages;
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        items.forEach(it => {
          if (it.tableEl) it.tableEl.classList.add('hidden');
          if (it.cardEl) it.cardEl.classList.add('hidden');
        });
        filtered.slice(start, end).forEach(it => {
          if (it.tableEl) it.tableEl.classList.remove('hidden');
          if (it.cardEl) it.cardEl.classList.remove('hidden');
        });

        const from = total === 0 ? 0 : start + 1;
        const to = Math.min(end, total);
        infoBot.textContent = `Showing ${from} to ${to} of ${total} entries`;
        totalCountEl.textContent = items.length;

        if (prevBtnF) prevBtnF.disabled = currentPage === 1;
        if (nextBtnF) nextBtnF.disabled = currentPage >= totalPages || total === 0;
      }

      function applySearch() {
        const term = (searchInput.value || '').trim().toLowerCase();
        filtered = !term ? items.slice() : items.filter(it => it.text.toLowerCase().includes(term));
        currentPage = 1;
        render();
      }

      searchInput.addEventListener('input', applySearch);
      rowsPerPageSelect.addEventListener('change', function() {
        rowsPerPage = +this.value;
        currentPage = 1;
        render();
      });
      if (prevBtnF) prevBtnF.addEventListener('click', () => { if (currentPage > 1) { currentPage--; render(); } });
      if (nextBtnF) nextBtnF.addEventListener('click', () => { const tp = Math.max(1, Math.ceil(filtered.length / rowsPerPage)); if (currentPage < tp) { currentPage++; render(); } });

      render();
    });
  </script>

  <script>
    document.querySelectorAll('[data-toggle]').forEach(btn => {
      btn.addEventListener('click', () => {
        const target = document.getElementById(btn.getAttribute('data-toggle'));
        if (target) {
          target.style.display = target.style.display === 'flex' ? 'none' : 'flex';
        }
      });
    });
    document.querySelectorAll('[data-cancel]').forEach(btn => {
      btn.addEventListener('click', () => {
        const target = document.getElementById(btn.getAttribute('data-cancel'));
        if (target) {
          target.style.display = 'none';
        }
      });
    });

    const editModal = document.getElementById('editUserModal');
    const editUserId = document.getElementById('editUserId');
    const editUsername = document.getElementById('editUsername');
    const editEmail = document.getElementById('editEmail');
    const editPassword = document.getElementById('editPassword');

    document.querySelectorAll('[data-edit]').forEach(btn => {
      btn.addEventListener('click', () => {
        editUserId.value = btn.getAttribute('data-edit');
        editUsername.value = btn.getAttribute('data-username') || '';
        editEmail.value = btn.getAttribute('data-email') || '';
        editPassword.value = '';
        editModal.style.display = 'flex';
      });
    });

    document.getElementById('closeEditUserModal').onclick = function() {
      editModal.style.display = 'none';
    };
    document.getElementById('cancelEditUser').onclick = function() {
      editModal.style.display = 'none';
    };
    window.addEventListener('click', function(event) {
      if (event.target === editModal) {
        editModal.style.display = 'none';
      }
    });
  </script>
</body>
</html>
