<! <div class="user-edit-container">
    <! <div class="user-edit-header">
        <h2>Edit User</h2>
        <! <div class="user-actions">
            <a href="<?= BASE_URL ?>admin/users" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
            <a href="<?= BASE_URL ?>admin/users/<?php echo $user['user_id'];
                                                ?>" class="btn btn-info">
                <i class="fas fa-eye"></i> View User
            </a>
            </div>
            </div>
            <?php
            ?>
            <?php if (isset($_SESSION['_flash']['error'])): ?>
            <div class="alert alert-danger">
                <?php
                    if (is_array($_SESSION['_flash']['error'])) {
                        echo '<ul class="error-list">';
                        foreach ($_SESSION['_flash']['error'] as $error) {
                            echo '<li>' . htmlspecialchars($error) . '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo htmlspecialchars($_SESSION['_flash']['error']);
                    }
                    ?>
            </div>
            <?php endif; ?>
            <! <div class="user-edit-card">
                <?php
                ?>
                <form action="<?= BASE_URL ?>admin/users/<?php echo $user['user_id']; ?>" method="post"
                    class="user-edit-form">
                    <! <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <! <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']);
                                                                                                    ?>" required>
                            </div>
                            <! <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']);
                                                                                                        ?>" readonly>
                                <small class="form-text text-muted">Email cannot be changed.</small>
                                </div>
                                <! <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']);
                                                                                                            ?>"
                                        required>
                                    </div>
                                    <! <div class="form-group">
                                        <label for="role">Role</label>
                                        <select id="role" name="role" class="form-control" required>
                                            <option value="customer" <?php echo $user['role'] === 'customer' ? 'selected' : '';
                                                                        ?>>Customer
                                            </option>
                                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : '';
                                                                    ?>>Admin</option>
                                        </select>
                                        </div>
                                        <! <div class="form-group">
                                            <label for="account_status">Account Status</label>
                                            <select id="account_status" name="account_status" class="form-control"
                                                required>
                                                <?php
                                                ?>
                                                <option value="active"
                                                    <?php echo (!isset($user['account_status']) || $user['account_status'] === 'active') ? 'selected' : ''; ?>>
                                                    Active</option>
                                                <option value="inactive"
                                                    <?php echo (isset($user['account_status']) && $user['account_status'] === 'inactive') ? 'selected' : ''; ?>>
                                                    Inactive</option>
                                            </select>
                                            </div>
                                            <! <div class="form-group">
                                                <label>Registration Date</label>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars(date('F d, Y', strtotime($user['registration_date'])));
                                                                                                ?>" readonly>
                                                <small class="form-text text-muted">Registration date cannot be
                                                    changed.</small>
                                                </div>
                                                <! <div class="form-actions">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-save"></i> Save Changes
                                                    </button>
                                                    <a href="<?= BASE_URL ?>admin/users/<?php echo $user['user_id'];
                                                                                        ?>" class="btn btn-secondary">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </a>
                                                    </div>
                </form>
                </div>
                <! </div>
                    <! <! <style>
                        body {
                        margin: 0;
                        padding: 0;
                        }
                        .user-edit-container {
                        display: flex;
                        flex-direction: column;
                        gap: 1.5rem;
                        }
                        .user-edit-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 1rem;
                        }
                        .user-edit-header h2 {
                        margin: 0;
                        color: var(--admin-secondary, #343a40);
                        }
                        .user-actions {
                        display: flex;
                        gap: 0.5rem;
                        }
                        .alert {
                        padding: 0.75rem 1.25rem;
                        margin-bottom: 1rem;
                        border: 1px solid transparent;
                        border-radius: 0.25rem;
                        }
                        .alert-danger {
                        color: #721c24;
                        background-color: #f8d7da;
                        border-color: #f5c6cb;
                        }
                        .error-list {
                        margin: 0.5rem 0 0.5rem 1.5rem;
                        padding: 0;
                        list-style: disc;
                        }
                        .error-list li {
                        margin-bottom: 0.25rem;
                        }
                        .user-edit-card {
                        background-color: white;
                        border-radius: 8px;
                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                        padding: 1.5rem;
                        }
                        .user-edit-form {
                        display: flex;
                        flex-direction: column;
                        gap: 1.25rem;
                        }
                        .form-group {
                        display: flex;
                        flex-direction: column;
                        gap: 0.5rem;
                        }
                        .form-group label {
                        font-weight: 600;
                        color: var(--admin-secondary, #495057);
                        }
                        .form-control {
                        display: block;
                        width: 100%;
                        padding: 0.375rem 0.75rem;
                        font-size: 1rem;
                        line-height: 1.5;
                        color: #495057;
                        background-color: #fff;
                        background-clip: padding-box;
                        border: 1px solid #ced4da;
                        border-radius: 0.25rem;
                        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
                        }
                        .form-control:focus {
                        color: #495057;
                        background-color: #fff;
                        border-color: #80bdff;
                        outline: 0;
                        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
                        }
                        .form-control:disabled,
                        .form-control[readonly] {
                        background-color: #e9ecef;
                        opacity: 1;
                        }
                        .form-text {
                        display: block;
                        margin-top: 0.25rem;
                        font-size: 0.875rem;
                        }
                        .text-muted {
                        color: #6c757d;
                        }
                        .form-actions {
                        display: flex;
                        gap: 0.5rem;
                        margin-top: 1rem;
                        }
                        .btn {
                        display: inline-block;
                        font-weight: 400;
                        text-align: center;
                        white-space: nowrap;
                        vertical-align: middle;
                        user-select: none;
                        border: 1px solid transparent;
                        padding: 0.375rem 0.75rem;
                        font-size: 1rem;
                        line-height: 1.5;
                        border-radius: 0.25rem;
                        transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s
                        ease-in-out, box-shadow 0.15s ease-in-out;
                        text-decoration: none;
                        cursor: pointer;
                        }
                        .btn-primary {
                        color: white;
                        background-color: var(--admin-primary, #007bff);
                        border-color: var(--admin-primary, #007bff);
                        }
                        .btn-primary:hover {
                        background-color: #0056b3;
                        border-color: #0056b3;
                        }
                        .btn-secondary {
                        color: white;
                        background-color: #6c757d;
                        border-color: #6c757d;
                        }
                        .btn-secondary:hover {
                        background-color: #5a6268;
                        border-color: #5a6268;
                        }
                        .btn-info {
                        color: white;
                        background-color: #17a2b8;
                        border-color: #17a2b8;
                        }
                        .btn-info:hover {
                        background-color: #138496;
                        border-color: #138496;
                        }
                        @media (max-width: 768px) {
                        .user-edit-header {
                        flex-direction: column;
                        align-items: flex-start;
                        gap: 1rem;
                        }
                        }
                        </style>