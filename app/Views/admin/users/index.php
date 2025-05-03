

<!
<div class="users-container">
    <!
    <div class="users-header">
        <h2>User Management</h2>
        <p>View and manage all registered users.</p>
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
    <div class="users-table-container">
        <!
        <table class="users-table">
            <!
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Registration Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <!
            <tbody>
                <?php 
                ?>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" class="text-center">No users found.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <!
                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                            <!
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <!
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <!
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <!
                            <td>
                                <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-admin' : 'badge-customer'; 
                                                    ?>">
                                    <?php echo htmlspecialchars(ucfirst($user['role'])); 
                                    ?>
                                </span>
                            </td>
                            <!
                            <td>
                                <span class="badge <?php echo isset($user['account_status']) && $user['account_status'] === 'active' ? 'badge-active' : 'badge-inactive'; 
                                                    ?>">
                                    <?php echo htmlspecialchars(ucfirst($user['account_status'] ?? 'active')); 
                                    ?>
                                </span>
                            </td>
                            <!
                            <td><?php echo htmlspecialchars(date('M d, Y', strtotime($user['registration_date']))); 
                                ?></td>
                            <!
                            <td class="actions">
                                <!
                                <a href="<?= BASE_URL ?>admin/users/<?php echo $user['user_id']; 
                                                                    ?>" class="btn btn-sm btn-view" title="View User">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <!
                                <a href="<?= BASE_URL ?>admin/users/<?php echo $user['user_id']; ?>/edit"
                                    class="btn btn-sm btn-edit" title="Edit User">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; 
                ?>
            </tbody>
        </table>
    </div> <!

    <?php 
    ?>
    <?php if ($pagination['total_pages'] > 1): ?>
        <!
        <div class="pagination">
            <?php 
            ?>
            <?php if ($pagination['current_page'] > 1): ?>
                <a href="<?= BASE_URL ?>admin/users?page=1" class="pagination-link">&laquo; First</a>
                <a href="<?= BASE_URL ?>admin/users?page=<?php echo $pagination['current_page'] - 1; ?>"
                    class="pagination-link">&lsaquo;
                    Previous</a>
            <?php endif; ?>

            <?php
            
            $startPage = max(1, $pagination['current_page'] - 2);
            $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
            
            for ($i = $startPage; $i <= $endPage; $i++):
            ?>
                <a href="<?= BASE_URL ?>admin/users?page=<?php echo $i; ?>" class="pagination-link <?php echo $i === $pagination['current_page'] ? 'active' : ''; 
                                                                                                    ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php 
            ?>
            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <a href="<?= BASE_URL ?>admin/users?page=<?php echo $pagination['current_page'] + 1; ?>"
                    class="pagination-link">Next
                    &rsaquo;</a>
                <a href="<?= BASE_URL ?>admin/users?page=<?php echo $pagination['total_pages']; ?>" class="pagination-link">Last
                    &raquo;</a>
            <?php endif; ?>
        </div> <!
    <?php endif; 
    ?>
</div> <!

<!
<style>
    
    body {
        margin: 0;
        padding: 0;
    }

    
    .users-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        
    }

    
    .users-header {
        margin-bottom: 1rem;
    }

    .users-header h2 {
        margin-top: 0;
        margin-bottom: 0.5rem;
        color: var(--admin-secondary, #343a40);
        
    }

    .users-header p {
        margin: 0;
        color: #6c757d;
        
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

    
    .users-table-container {
        overflow-x: auto;
    }

    
    .users-table {
        width: 100%;
        border-collapse: collapse;
        
    }

    
    .users-table th,
    .users-table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid #e9ecef;
        
        vertical-align: middle;
        
    }

    
    .users-table th {
        background-color: #f8f9fa;
        
        font-weight: 600;
        color: var(--admin-secondary, #495057);
    }

    
    .users-table tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
        
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

    
    .actions {
        white-space: nowrap;
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
        margin: 0 0.1rem;
        
    }

    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }

    
    .btn-view {
        color: var(--admin-primary, #007bff);
        background-color: transparent;
        border: 1px solid var(--admin-primary, #007bff);
    }

    .btn-view:hover {
        color: white;
        background-color: var(--admin-primary, #007bff);
    }

    
    .btn-edit {
        color: #ffc107;
        
        background-color: transparent;
        border: 1px solid #ffc107;
    }

    .btn-edit:hover {
        color: #212529;
        
        background-color: #ffc107;
    }

    
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 1.5rem;
        flex-wrap: wrap;
        
    }

    
    .pagination-link {
        display: inline-block;
        padding: 0.5rem 0.75rem;
        margin: 0 0.25rem 0.5rem 0.25rem;
        
        color: var(--admin-primary, #007bff);
        background-color: white;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    
    .pagination-link:hover {
        background-color: #e9ecef;
        border-color: #dee2e6;
    }

    
    .pagination-link.active {
        color: white;
        background-color: var(--admin-primary, #007bff);
        border-color: var(--admin-primary, #007bff);
    }

    
    @media (max-width: 992px) {

        
        .users-table th:nth-child(4),
        .users-table td:nth-child(4),
        .users-table th:nth-child(7),
        .users-table td:nth-child(7) {
            display: none;
        }
    }

    @media (max-width: 768px) {

        
        .users-table th:nth-child(3),
        .users-table td:nth-child(3) {
            display: none;
        }
    }

    @media (max-width: 576px) {

        
        .pagination-link {
            padding: 0.4rem 0.6rem;
            margin: 0 0.1rem 0.4rem 0.1rem;
        }
    }
</style>