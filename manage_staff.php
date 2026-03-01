<?php
include "database/database.php";
$database->login_session();
$database->admin_session();
$database->register();
$database->update_staff();
$database->deactivate_staff();
$database->activate_staff();

// Pagination and Search
$perPage = 5;
$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$totalStaffs = $database->getTotalStaffsCount($search);
$totalPages  = max(1, ceil($totalStaffs / $perPage));
$staffs      = $database->select_staffs_paginated($offset, $perPage, $search);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS & Inventory - Staff Accounts</title>
    <link rel="stylesheet" href="assets/tailwind.min.css">
    <link href="assets/fonts.css" rel="stylesheet">

    <style>
        :root {
            --bg: #0f1117;
            --surface: #1a1d27;
            --surface2: #22263a;
            --border: #2e3347;
            --accent: #f5a623;
            --text: #e8eaf0;
            --text-muted: #7b82a0;
            --success: #43d392;
            --danger: #ff5c5c;
            --warning: #f59e0b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }

        main {
            flex: 1;
            padding: 24px;
            transition: margin-left .3s ease;
        }

        .page-header {
            margin-bottom: 24px;
        }

        .page-header h2 {
            font-size: 22px;
            font-weight: 700;
        }

        .card {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 16px;
            padding: 24px;
        }

        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .search-wrap {
            position: relative;
            flex: 1 1 300px;
            min-width: 260px;
        }

        .search-wrap input {
            width: 100%;
            background: var(--bg);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 9px 36px 9px 38px;
            color: var(--text);
            font-size: 13px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .search-wrap input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(245, 166, 35, .1);
        }

        .search-wrap input::placeholder {
            color: var(--text-muted);
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        }

        .search-clear {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 16px;
            line-height: 1;
            text-decoration: none;
            transition: color .2s;
        }

        .search-clear:hover {
            color: var(--danger);
        }

        .btn-primary {
            background: var(--accent);
            color: #111;
            border: none;
            border-radius: 10px;
            padding: 9px 18px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .2s;
            white-space: nowrap;
        }

        .btn-primary:hover {
            opacity: .88;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: rgba(67, 211, 146, .1);
            border: 1px solid rgba(67, 211, 146, .3);
            color: var(--success);
        }

        .alert-error {
            background: rgba(255, 92, 92, .1);
            border: 1px solid rgba(255, 92, 92, .3);
            color: var(--danger);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .data-table thead tr {
            border-bottom: 1.5px solid var(--border);
        }

        .data-table thead th {
            padding: 10px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: var(--text-muted);
            white-space: nowrap;
        }

        .data-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background .15s;
        }

        .data-table tbody tr:hover {
            background: rgba(255, 255, 255, .02);
        }

        .data-table tbody td {
            padding: 13px 16px;
        }

        .status-pill {
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 999px;
            white-space: nowrap;
        }

        .status-active {
            background: rgba(67, 211, 146, .18);
            color: var(--success);
        }

        .status-inactive {
            background: rgba(255, 92, 92, .18);
            color: var(--danger);
        }

        .btn-action {
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 600;
            transition: all .15s;
        }

        .btn-action.update {
            color: var(--success);
        }

        .btn-action.deactivate {
            color: var(--danger);
        }

        .btn-action.activate {
            color: #60a5fa;
        }

        .btn-action:hover.update {
            background: rgba(67, 211, 146, .12);
        }

        .btn-action:hover.deactivate {
            background: rgba(255, 92, 92, .12);
        }

        .btn-action:hover.activate {
            background: rgba(96, 165, 250, .12);
        }

        .actions-wrap {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 8px;
            flex-wrap: nowrap;
        }

        .pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 12px;
        }

        .page-btn {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            border: 1.5px solid var(--border);
            background: var(--surface);
            color: var(--text);
            text-decoration: none;
            transition: all .2s;
        }

        .page-btn:hover {
            border-color: var(--accent);
            background: rgba(245, 166, 35, .08);
            color: var(--accent);
        }

        .page-btn.active {
            background: var(--accent);
            color: #111;
            border-color: var(--accent);
        }

        .page-btn.disabled {
            opacity: .5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .7);
            backdrop-filter: blur(4px);
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s;
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: all;
        }

        .modal-box {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 18px;
            padding: 28px;
            width: 460px;
            max-width: 92%;
            box-shadow: 0 25px 60px rgba(0, 0, 0, .4);
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: .8px;
        }

        .form-input {
            width: 100%;
            background: var(--bg);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 10px 14px;
            color: var(--text);
            font-size: 13px;
            outline: none;
            transition: border-color .2s;
        }

        .form-input:focus {
            border-color: var(--accent);
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
        }

        .btn-cancel {
            background: var(--surface2);
            border: 1.5px solid var(--border);
            color: var(--text-muted);
            border-radius: 10px;
            padding: 9px 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-cancel:hover {
            color: var(--text);
            border-color: #555;
        }

        .btn-confirm-ok {
            background: var(--accent);
            color: #111;
            border: none;
            border-radius: 10px;
            padding: 9px 20px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-confirm-ok:hover {
            opacity: .88;
        }

        .btn-confirm-danger {
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 9px 20px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-confirm-danger:hover {
            opacity: .88;
        }

        ::-webkit-scrollbar {
            width: 5px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 5px;
        }
    </style>
</head>

<body>

    <button class="mobile-toggle" id="sidebar-toggle">☰</button>
    <?php include "sidebar.php"; ?>

    <main style="margin-left:240px;">

        <div class="page-header">
            <h2>Activate and Deactivate your staff</h2>
        </div>

        <div class="card">

            <!-- Toolbar: Search + Add Staff -->
            <div class="toolbar">
                <div class="search-wrap">
                    <svg class="search-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                    <form method="GET" id="staffFilterForm" style="margin:0;">
                        <input type="text" name="search" id="searchInput" value="<?= htmlspecialchars($search) ?>" placeholder="Search username..." class="form-input">
                        <?php if ($search !== ''): ?>
                            <a href="?" class="search-clear">×</a>
                        <?php endif; ?>
                    </form>
                </div>

                <button id="openAddStaffModal" class="btn-primary">+ Add Staff</button>
            </div>

            <!-- Alerts -->
            <?php if (isset($_SESSION['register-success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['register-success'] ?></div>
                <?php unset($_SESSION['register-success']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['register-error'])): ?>
                <div class="alert alert-error"><?= $_SESSION['register-error'] ?></div>
                <?php unset($_SESSION['register-error']); ?>
            <?php endif; ?>

            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($staffs)): ?>
                            <?php foreach ($staffs as $staff): ?>
                                <tr>
                                    <td style="font-weight:600;"><?= htmlspecialchars($staff['username'] ?? '—') ?></td>
                                    <td class="muted"><?= htmlspecialchars(ucfirst($staff['role'] ?? '—')) ?></td>
                                    <td>
                                        <span class="status-pill <?= ($staff['is_active'] ?? 0) == 1 ? 'status-active' : 'status-inactive' ?>">
                                            <?= ($staff['is_active'] ?? 0) == 1 ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions-wrap">
                                            <button class="btn-action update openEditStaffModal"
                                                data-id="<?= $staff['user_id'] ?>"
                                                data-username="<?= htmlspecialchars($staff['username']) ?>"
                                                title="Edit">
                                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>

                                            <?php if (($staff['is_active'] ?? 0) == 1): ?>
                                                <button class="btn-action deactivate openDeactivateStaffModal"
                                                    data-id="<?= $staff['user_id'] ?>"
                                                    data-username="<?= htmlspecialchars($staff['username']) ?>"
                                                    title="Deactivate">
                                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn-action activate openActivateStaffModal"
                                                    data-id="<?= $staff['user_id'] ?>"
                                                    data-username="<?= htmlspecialchars($staff['username']) ?>"
                                                    title="Activate">
                                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center; padding:60px 20px; color:var(--text-muted); font-size:15px;">
                                    <?= $search ? 'No matching staff found.' : 'No staff accounts yet.' ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1):
                $q = $search ? '&search=' . urlencode($search) : '';
            ?>
                <div class="pagination">
                    <div style="font-size:12px; color:var(--text-muted);">
                        Page <strong style="color:var(--text)"><?= $page ?></strong> of <strong style="color:var(--text)"><?= $totalPages ?></strong>
                    </div>
                    <div style="display:flex; gap:4px; flex-wrap:wrap;">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?><?= $q ?>" class="page-btn">‹ Prev</a>
                        <?php else: ?>
                            <span class="page-btn disabled">‹ Prev</span>
                        <?php endif; ?>

                        <?php
                        $range = 2;
                        $start = max(1, $page - $range);
                        $end   = min($totalPages, $page + $range);
                        if ($start > 1): ?>
                            <a href="?page=1<?= $q ?>" class="page-btn">1</a>
                            <?php if ($start > 2): ?><span style="padding:6px 8px; color:var(--text-muted);">…</span><?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start; $i <= $end; $i++): ?>
                            <?php if ($i === $page): ?>
                                <span class="page-btn active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?><?= $q ?>" class="page-btn"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($end < $totalPages): ?>
                            <?php if ($end < $totalPages - 1): ?><span style="padding:6px 8px; color:var(--text-muted);">…</span><?php endif; ?>
                            <a href="?page=<?= $totalPages ?><?= $q ?>" class="page-btn"><?= $totalPages ?></a>
                        <?php endif; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?><?= $q ?>" class="page-btn">Next ›</a>
                        <?php else: ?>
                            <span class="page-btn disabled">Next ›</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <!-- Add Staff Modal -->
    <div id="addStaffModal" class="modal-overlay">
        <div class="modal-box">
            <h3>Add New Staff</h3>

            <form method="POST">
                <input type="hidden" name="role" value="staff">

                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" required class="form-input" placeholder="staff_username">
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required class="form-input">
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelAddStaff">Cancel</button>
                    <button type="submit" name="register" class="btn-confirm-ok">Add Staff</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Staff Modal -->
    <div id="editStaffModal" class="modal-overlay">
        <div class="modal-box">
            <h3>Edit Staff Account</h3>

            <form method="POST">
                <input type="hidden" name="user_id" id="edit_staff_id">

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="edit_staff_username" class="form-input">
                </div>
                <div class="form-group">
                    <label>New Password (leave blank to keep current)</label>
                    <input type="password" name="password" id="edit_staff_password" class="form-input" placeholder="••••••••">
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelEditStaff">Cancel</button>
                    <button type="submit" name="update_staff" class="btn-confirm-ok">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Deactivate Confirmation Modal -->
    <div id="deactivateStaffModal" class="modal-overlay">
        <div class="modal-box" style="max-width:420px;">
            <h3>Deactivate Staff</h3>
            <p style="margin:20px 0;">
                Deactivate <strong id="deactivate_staff_name" style="color:var(--text)"></strong>?<br>
                They will no longer be able to log in.
            </p>
            <form method="POST">
                <input type="hidden" name="user_id" id="deactivate_staff_id">
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelDeactivate">Cancel</button>
                    <button type="submit" name="deactivate_staff" class="btn-confirm-danger">Deactivate</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Activate Confirmation Modal -->
    <div id="activateStaffModal" class="modal-overlay">
        <div class="modal-box" style="max-width:420px;">
            <h3>Activate Staff</h3>
            <p style="margin:20px 0;">
                Reactivate <strong id="activate_staff_name" style="color:var(--text)"></strong>?<br>
                They will regain access to the system.
            </p>
            <form method="POST">
                <input type="hidden" name="user_id" id="activate_staff_id">
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelActivate">Cancel</button>
                    <button type="submit" name="activate_staff" class="btn-confirm-ok">Activate</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal helpers
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        document.querySelectorAll('.modal-overlay').forEach(m => {
            m.addEventListener('click', e => {
                if (e.target === m) closeModal(m.id);
            });
        });

        // Add Staff
        document.getElementById('openAddStaffModal')?.addEventListener('click', () => openModal('addStaffModal'));
        document.getElementById('cancelAddStaff')?.addEventListener('click', () => closeModal('addStaffModal'));

        // Edit Staff
        document.querySelectorAll('.openEditStaffModal').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('edit_staff_id').value = btn.dataset.id;
                document.getElementById('edit_staff_username').value = btn.dataset.username;
                document.getElementById('edit_staff_password').value = '';
                openModal('editStaffModal');
            });
        });
        document.getElementById('cancelEditStaff')?.addEventListener('click', () => closeModal('editStaffModal'));

        // Deactivate
        document.querySelectorAll('.openDeactivateStaffModal').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('deactivate_staff_id').value = btn.dataset.id;
                document.getElementById('deactivate_staff_name').textContent = btn.dataset.username;
                openModal('deactivateStaffModal');
            });
        });
        document.getElementById('cancelDeactivate')?.addEventListener('click', () => closeModal('deactivateStaffModal'));

        // Activate
        document.querySelectorAll('.openActivateStaffModal').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('activate_staff_id').value = btn.dataset.id;
                document.getElementById('activate_staff_name').textContent = btn.dataset.username;
                openModal('activateStaffModal');
            });
        });
        document.getElementById('cancelActivate')?.addEventListener('click', () => closeModal('activateStaffModal'));

        // Live search
        let timeout;
        document.getElementById('searchInput')?.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => this.closest('form').submit(), 450);
        });

        // Sidebar sync
        function syncMargin() {
            const collapsed = document.getElementById('main-sidebar')?.classList.contains('collapsed');
            document.querySelector('main').style.marginLeft = collapsed ? '64px' : '240px';
        }
        document.getElementById('sidebarCollapseBtn')?.addEventListener('click', () => setTimeout(syncMargin, 80));
        syncMargin();

        // Auto-dismiss alerts
        document.querySelectorAll('.alert').forEach(el => {
            setTimeout(() => el.style.opacity = '0', 3200);
            setTimeout(() => el.remove(), 4000);
        });
    </script>

</body>

</html>