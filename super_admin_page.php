<?php
include "database/database.php";
$database->login_session();
$database->superadmin_session();
$database->register_admin();
$database->update_admin();
$database->deactivate_admin();
$database->activate_admin();
$database->delete_admin();

// Pagination and Search
$perPage = 5;
$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$totalAdmins = $database->getTotalAdminsCount($search);
$totalPages  = max(1, ceil($totalAdmins / $perPage));
$admins      = $database->select_admins_paginated($offset, $perPage, $search);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin - Admin Accounts</title>
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
            font-family: 'DM Sans', sans-serif;
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

        .page-header p {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 4px;
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

        .role-pill {
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 999px;
            background: rgba(245, 166, 35, .15);
            color: var(--accent);
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

        .modal-box h3 {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 20px;
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
            font-family: 'DM Sans', sans-serif;
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

        /* Logout button */
        .btn-logout {
            background: rgba(255, 92, 92, .1);
            border: 1.5px solid rgba(255, 92, 92, .25);
            color: var(--danger);
            border-radius: 10px;
            padding: 9px 18px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all .2s;
            white-space: nowrap;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .btn-logout:hover {
            background: rgba(255, 92, 92, .2);
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

    <main style="margin-left:0; max-width:1100px; margin:0 auto;">

        <!-- Top bar -->
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; padding-top:8px; flex-wrap:wrap; gap:12px;">
            <div class="page-header" style="margin-bottom:0;">
                <h2>Admin Accounts</h2>
                <p>Manage administrator access to the POS system</p>
            </div>
            <a href="logout.php" class="btn-logout">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout
            </a>
        </div>

        <div class="card">

            <!-- Toolbar -->
            <div class="toolbar">
                <div class="search-wrap">
                    <svg class="search-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                    <form method="GET" id="adminFilterForm" style="margin:0;">
                        <input type="text" name="search" id="searchInput" value="<?= htmlspecialchars($search) ?>" placeholder="Search admin username..." class="form-input">
                        <?php if ($search !== ''): ?>
                            <a href="?" class="search-clear">×</a>
                        <?php endif; ?>
                    </form>
                </div>
                <button id="openAddAdminModal" class="btn-primary">+ Add Admin</button>
            </div>

            <!-- Alerts -->
            <?php if (isset($_SESSION['register-admin-success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['register-admin-success'] ?></div>
                <?php unset($_SESSION['register-admin-success']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['register-admin-error'])): ?>
                <div class="alert alert-error"><?= $_SESSION['register-admin-error'] ?></div>
                <?php unset($_SESSION['register-admin-error']); ?>
            <?php endif; ?>

            <!-- Table -->
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
                        <?php if (!empty($admins)): ?>
                            <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td style="font-weight:600;"><?= htmlspecialchars($admin['username'] ?? '—') ?></td>
                                    <td><span class="role-pill"><?= htmlspecialchars(ucfirst($admin['role'] ?? '—')) ?></span></td>
                                    <td>
                                        <span class="status-pill <?= ($admin['is_active'] ?? 0) == 1 ? 'status-active' : 'status-inactive' ?>">
                                            <?= ($admin['is_active'] ?? 0) == 1 ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions-wrap">
                                            <button class="btn-action update openEditAdminModal"
                                                data-id="<?= $admin['user_id'] ?>"
                                                data-username="<?= htmlspecialchars($admin['username']) ?>"
                                                title="Edit">
                                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>

                                            <?php if (($admin['is_active'] ?? 0) == 1): ?>
                                                <button class="btn-action deactivate openDeactivateAdminModal"
                                                    data-id="<?= $admin['user_id'] ?>"
                                                    data-username="<?= htmlspecialchars($admin['username']) ?>"
                                                    title="Deactivate">
                                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn-action activate openActivateAdminModal"
                                                    data-id="<?= $admin['user_id'] ?>"
                                                    data-username="<?= htmlspecialchars($admin['username']) ?>"
                                                    title="Activate">
                                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </button>
                                            <?php endif; ?>

                                            <!-- Delete -->
                                            <button class="btn-action deactivate openDeleteAdminModal"
                                                data-id="<?= $admin['user_id'] ?>"
                                                data-username="<?= htmlspecialchars($admin['username']) ?>"
                                                title="Permanently Delete">
                                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center; padding:60px 20px; color:var(--text-muted); font-size:15px;">
                                    <?= $search ? 'No matching admins found.' : 'No admin accounts yet.' ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
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

    <!-- Add Admin Modal -->
    <div id="addAdminModal" class="modal-overlay">
        <div class="modal-box">
            <h3>Add New Admin</h3>
            <form method="POST">
                <input type="hidden" name="role" value="admin">
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" required class="form-input" placeholder="admin_username">
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required class="form-input">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelAddAdmin">Cancel</button>
                    <button type="submit" name="register_admin" class="btn-confirm-ok">Add Admin</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Admin Modal -->
    <div id="editAdminModal" class="modal-overlay">
        <div class="modal-box">
            <h3>Edit Admin Account</h3>
            <form method="POST">
                <input type="hidden" name="user_id" id="edit_admin_id">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="edit_admin_username" class="form-input">
                </div>
                <div class="form-group">
                    <label>New Password (leave blank to keep current)</label>
                    <input type="password" name="password" id="edit_admin_password" class="form-input" placeholder="••••••••">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelEditAdmin">Cancel</button>
                    <button type="submit" name="update_admin" class="btn-confirm-ok">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Deactivate Admin Modal -->
    <div id="deactivateAdminModal" class="modal-overlay">
        <div class="modal-box" style="max-width:420px;">
            <h3>Deactivate Admin</h3>
            <p style="margin:16px 0 0; font-size:13px; color:var(--text-muted); line-height:1.6;">
                Deactivate <strong id="deactivate_admin_name" style="color:var(--text);"></strong>?<br>
                They will no longer be able to log in.
            </p>
            <form method="POST">
                <input type="hidden" name="user_id" id="deactivate_admin_id">
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelDeactivateAdmin">Cancel</button>
                    <button type="submit" name="deactivate_admin" class="btn-confirm-danger">Deactivate</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Activate Admin Modal -->
    <div id="activateAdminModal" class="modal-overlay">
        <div class="modal-box" style="max-width:420px;">
            <h3>Activate Admin</h3>
            <p style="margin:16px 0 0; font-size:13px; color:var(--text-muted); line-height:1.6;">
                Reactivate <strong id="activate_admin_name" style="color:var(--text);"></strong>?<br>
                They will regain access to the system.
            </p>
            <form method="POST">
                <input type="hidden" name="user_id" id="activate_admin_id">
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelActivateAdmin">Cancel</button>
                    <button type="submit" name="activate_admin" class="btn-confirm-ok">Activate</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Admin Modal -->
    <div id="deleteAdminModal" class="modal-overlay">
        <div class="modal-box" style="max-width:420px;">
            <h3 style="color:var(--danger);">Permanently Delete Admin</h3>
            <p style="margin:16px 0 0; font-size:13px; color:var(--text-muted); line-height:1.6;">
                You are about to permanently delete <strong id="delete_admin_name" style="color:var(--text);"></strong>.<br><br>
                <span style="color:var(--danger);">This action cannot be undone.</span>
            </p>
            <form method="POST">
                <input type="hidden" name="user_id" id="delete_admin_id">
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelDeleteAdmin">Cancel</button>
                    <button type="submit" name="delete_admin" class="btn-confirm-danger">Yes, Delete Permanently</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        // Close on backdrop click
        document.querySelectorAll('.modal-overlay').forEach(m => {
            m.addEventListener('click', e => {
                if (e.target === m) closeModal(m.id);
            });
        });

        // Add Admin
        document.getElementById('openAddAdminModal')?.addEventListener('click', () => openModal('addAdminModal'));
        document.getElementById('cancelAddAdmin')?.addEventListener('click', () => closeModal('addAdminModal'));

        // Edit Admin
        document.querySelectorAll('.openEditAdminModal').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('edit_admin_id').value = btn.dataset.id;
                document.getElementById('edit_admin_username').value = btn.dataset.username;
                document.getElementById('edit_admin_password').value = '';
                openModal('editAdminModal');
            });
        });
        document.getElementById('cancelEditAdmin')?.addEventListener('click', () => closeModal('editAdminModal'));

        // Deactivate Admin
        document.querySelectorAll('.openDeactivateAdminModal').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('deactivate_admin_id').value = btn.dataset.id;
                document.getElementById('deactivate_admin_name').textContent = btn.dataset.username;
                openModal('deactivateAdminModal');
            });
        });
        document.getElementById('cancelDeactivateAdmin')?.addEventListener('click', () => closeModal('deactivateAdminModal'));

        // Activate Admin
        document.querySelectorAll('.openActivateAdminModal').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('activate_admin_id').value = btn.dataset.id;
                document.getElementById('activate_admin_name').textContent = btn.dataset.username;
                openModal('activateAdminModal');
            });
        });
        document.getElementById('cancelActivateAdmin')?.addEventListener('click', () => closeModal('activateAdminModal'));

        // Delete Admin
        document.querySelectorAll('.openDeleteAdminModal').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('delete_admin_id').value = btn.dataset.id;
                document.getElementById('delete_admin_name').textContent = btn.dataset.username;
                openModal('deleteAdminModal');
            });
        });
        document.getElementById('cancelDeleteAdmin')?.addEventListener('click', () => closeModal('deleteAdminModal'));

        // Live search
        let timeout;
        document.getElementById('searchInput')?.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => this.closest('form').submit(), 450);
        });

        // Auto-dismiss alerts
        document.querySelectorAll('.alert').forEach(el => {
            setTimeout(() => {
                el.style.opacity = '0';
                el.style.transition = 'opacity .5s';
            }, 3200);
            setTimeout(() => el.remove(), 4000);
        });
    </script>

</body>

</html>