<!
<div class="user-details-container">
    <!
    <div class="user-details-header">
        <h2>User Details</h2>
        <!
        <div class="user-actions">
            <a href="<?= BASE_URL ?>admin/users" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
            <a href="<?= BASE_URL ?>admin/users/<?php echo $user['user_id']; ?>/edit" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit User
            </a>
        </div>
    </div>
    <?php 
    ?>
    <?php if (isset($_SESSION['_flash']['success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['_flash']['success']); 
            ?>
        </div>
        <?php 
        ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['_flash']['error'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($_SESSION['_flash']['error']); 
            ?>
        </div>
        <?php 
        ?>
    <?php endif; ?>
    <!
    <div class="user-details-card">
        <!
        <div class="user-details-section">
            <h3>Basic Information</h3>
            <!
            <div class="user-details-grid">
                <!
                <div class="detail-item">
                    <span class="detail-label">User ID:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user['user_id']); ?></span>
                </div>
                <!
                <div class="detail-item">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user['name']); ?></span>
                </div>
                <!
                <div class="detail-item">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <!
                <div class="detail-item">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user['phone']); ?></span>
                </div>
                <!
                <div class="detail-item">
                    <span class="detail-label">Role:</span>
                    <span class="detail-value">
                        <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-admin' : 'badge-customer'; 
                                            ?>">
                            <?php echo htmlspecialchars(ucfirst($user['role'])); 
                            ?>
                        </span>
                    </span>
                </div>
                <!
                <div class="detail-item">
                    <span class="detail-label">Account Status:</span>
                    <span class="detail-value">
                        <span class="badge <?php echo isset($user['account_status']) && $user['account_status'] === 'active' ? 'badge-active' : 'badge-inactive'; 
                                            ?>">
                            <?php echo htmlspecialchars(ucfirst($user['account_status'] ?? 'active')); 
                            ?>
                        </span>
                    </span>
                </div>
                <!
                <div class="detail-item">
                    <span class="detail-label">Registration Date:</span>
                    <span class="detail-value"><?php echo htmlspecialchars(date('F d, Y', strtotime($user['registration_date']))); 
                                                ?></span>
                </div>
            </div> <!
        </div> <!
        <!
        <div class="user-details-section">
            <h3>Password Management</h3>
            <p>Use this option to send a password reset email to the user.</p>
            <!
            <form action="<?= BASE_URL ?>admin/users/<?php echo $user['user_id']; ?>/reset-password" method="post"
                class="reset-password-form">
                <!
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <!
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-key"></i> Send Password Reset Email
                </button>
            </form>
        </div> <!
    </div> <!
</div> <!
<!
<style>
    body {
        margin: 0;
        padding: 0;
    }
    .user-details-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .user-details-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    .user-details-header h2 {
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
    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }
    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }
    .user-details-card {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }
    .user-details-section {
        padding: 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }
    .user-details-section:last-child {
        border-bottom: none;
    }
    .user-details-section h3 {
        margin-top: 0;
        margin-bottom: 1rem;
        color: var(--admin-secondary, #495057);
        font-size: 1.25rem;
    }
    .user-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1rem;
    }
    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .detail-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.875rem;
    }
    .detail-value {
        font-size: 1rem;
    }
    .badge {
        display: inline-block;
        padding: 0.25em 0.6em;
        font-size: 75%;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }
    .badge-admin {
        color: white;
        background-color: var(--admin-primary, #007bff);
    }
    .badge-customer {
        color: white;
        background-color: #6c757d;
    }
    .badge-active {
        color: white;
        background-color: #28a745;
    }
    .badge-inactive {
        color: white;
        background-color: #dc3545;
    }
    .reset-password-form {
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
        transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
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
    .btn-warning {
        color: #212529;
        background-color: #ffc107;
        border-color: #ffc107;
    }
    .btn-warning:hover {
        background-color: #e0a800;
        border-color: #e0a800;
    }
    @media (max-width: 768px) {
        .user-details-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        .user-details-grid {
            grid-template-columns: 1fr;
        }
    }
</style>