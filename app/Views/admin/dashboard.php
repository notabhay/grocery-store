
<div class="dashboard-container">
    <!
    <div class="dashboard-welcome">
        <h2>Welcome to the Admin Panel</h2>
        <p>This is the administration area for GhibliGroceries. From here, you can manage users, orders, products, and
            categories.</p>
    </div>
    <!

    <!
    <div class="dashboard-stats">
        <div class="stats-heading">
            <h3>Quick Statistics</h3>
            <p>Overview of your store's performance</p>
        </div>
        <div class="stats-grid">
            <!
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h4>Total Users</h4>
                    <p class="stat-value"><?= $stats['total_users'] 
                                            ?></p>
                    <p class="stat-description">Registered users</p>
                </div>
            </div>
            <!

            <!
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <h4>Total Orders</h4>
                    <p class="stat-value"><?= $stats['total_orders'] 
                                            ?></p>
                    <p class="stat-description">All orders</p>
                </div>
            </div>
            <!

            <!
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-content">
                    <h4>Products</h4>
                    <p class="stat-value"><?= $stats['total_products'] 
                                            ?></p>
                    <p class="stat-description">Available products</p>
                </div>
            </div>
            <!

            <!
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-content">
                    <h4>Categories</h4>
                    <p class="stat-value"><?= $stats['total_categories'] 
                                            ?></p>
                    <p class="stat-description">Product categories</p>
                </div>
            </div>
            <!

            <!
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(255, 193, 7, 0.1); color: #ffc107;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h4>Pending Orders</h4>
                    <p class="stat-value"><?= $stats['pending_orders'] 
                                            ?></p>
                    <p class="stat-description">Awaiting processing</p>
                </div>
            </div>
            <!

            <!
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(0, 123, 255, 0.1); color: #007bff;">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="stat-content">
                    <h4>Processing Orders</h4>
                    <p class="stat-value"><?= $stats['processing_orders'] 
                                            ?></p>
                    <p class="stat-description">Currently processing</p>
                </div>
            </div>
            <!

            <!
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(40, 167, 69, 0.1); color: #28a745;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h4>Completed Orders</h4>
                    <p class="stat-value"><?= $stats['completed_orders'] 
                                            ?></p>
                    <p class="stat-description">Successfully delivered</p>
                </div>
            </div>
            <!

            <!
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(220, 53, 69, 0.1); color: #dc3545;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h4>Low Stock</h4>
                    <p class="stat-value"><?= $stats['low_stock_products'] 
                                            ?></p>
                    <p class="stat-description">Products need restocking</p>
                </div>
            </div>
            <!
        </div>
    </div>
    <!

    <!
    <div class="dashboard-quick-actions">
        <div class="quick-actions-heading">
            <h3>Quick Actions</h3>
            <p>Common administrative tasks</p>
        </div>
        <div class="quick-actions-grid">
            <!
            <a href="<?= BASE_URL ?>admin/users" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="action-content">
                    <h4>Manage Users</h4>
                    <p>View, edit, and manage user accounts</p>
                </div>
            </a>
            <!

            <!
            <a href="<?= BASE_URL ?>admin/orders" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="action-content">
                    <h4>View Orders</h4>
                    <p>Process and manage customer orders</p>
                </div>
            </a>
            <!

            <!
            <a href="<?= BASE_URL ?>admin/products" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="action-content">
                    <h4>Manage Products</h4>
                    <p>Add, edit, or remove products</p>
                </div>
            </a>
            <!

            <!
            <a href="<?= BASE_URL ?>admin/categories" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-sitemap"></i>
                </div>
                <div class="action-content">
                    <h4>Manage Categories</h4>
                    <p>Organize your product categories</p>
                </div>
            </a>
            <!
        </div>
    </div>
    <!

    <!
    <div class="dashboard-recent-orders">
        <div class="recent-orders-heading">
            <h3>Recent Orders</h3>
            <p>Latest customer orders</p>
        </div>
        <div class="recent-orders-table-container">
            <table class="recent-orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    ?>
                    <?php if (empty($recent_orders)): ?>
                        <!
                        <tr>
                            <td colspan="6" class="no-orders">No recent orders found</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        ?>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>#<?= $order['order_id'] 
                                        ?></td>
                                <td><?= htmlspecialchars($order['user_name']) 
                                    ?></td>
                                <td><?= date('M d, Y', strtotime($order['order_date'])) 
                                    ?></td>
                                <td>$<?= number_format($order['total_amount'], 2) 
                                        ?></td>
                                <td>
                                    <!
                                    <span class="order-status status-<?= strtolower($order['status']) ?>">
                                        <?= ucfirst($order['status']) 
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <!
                                    <a href="<?= BASE_URL ?>admin/orders/<?= $order['order_id'] ?>" class="view-order-btn">
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <!
            <div class="view-all-orders">
                <a href="<?= BASE_URL ?>admin/orders" class="view-all-link">View All Orders <i
                        class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <!
</div>

<!
<!
<!
<style>
    
    body {
        margin: 0;
        padding: 0;
    }

    
    .dashboard-container {
        display: flex;
        flex-direction: column;
        gap: 2rem;
        
    }

    
    .dashboard-welcome {
        background-color: #f8f9fa;
        border-left: 4px solid var(--admin-primary);
        
        padding: 1.5rem;
        border-radius: 4px;
    }

    .dashboard-welcome h2 {
        margin-top: 0;
        color: var(--admin-secondary);
    }

    
    .stats-heading,
    .quick-actions-heading {
        margin-bottom: 1rem;
    }

    .stats-heading h3,
    .quick-actions-heading h3 {
        margin-bottom: 0.25rem;
        color: var(--admin-secondary);
    }

    .stats-heading p,
    .quick-actions-heading p {
        margin-top: 0;
        color: #6c757d;
        
    }

    
    .stats-grid,
    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        
        gap: 1.5rem;
    }

    
    .stat-card,
    .action-card {
        display: flex;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        
        padding: 1.5rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        
    }

    
    .action-card {
        text-decoration: none;
        color: inherit;
        
    }

    
    .stat-card:hover,
    .action-card:hover {
        transform: translateY(-5px);
        
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        
    }

    
    .stat-icon,
    .action-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        background-color: rgba(52, 152, 219, 0.1);
        
        color: var(--admin-primary);
        border-radius: 8px;
        margin-right: 1rem;
        font-size: 1.5rem;
    }

    
    .stat-content,
    .action-content {
        flex: 1;
        
    }

    .stat-content h4,
    .action-content h4 {
        margin-top: 0;
        margin-bottom: 0.5rem;
        color: var(--admin-secondary);
    }

    
    .stat-value {
        font-size: 1.5rem;
        font-weight: bold;
        margin: 0.5rem 0;
        color: var(--admin-primary);
    }

    
    .stat-description,
    .action-content p {
        margin: 0;
        color: #6c757d;
        font-size: 0.9rem;
    }

    
    @media (max-width: 768px) {

        .stats-grid,
        .quick-actions-grid {
            grid-template-columns: 1fr;
            
        }
    }

    
    .dashboard-recent-orders {
        margin-top: 2rem;
    }

    .recent-orders-heading h3 {
        margin-bottom: 0.25rem;
        color: var(--admin-secondary);
    }

    .recent-orders-heading p {
        margin-top: 0;
        color: #6c757d;
        margin-bottom: 1rem;
    }

    
    .recent-orders-table-container {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        
    }

    
    .recent-orders-table {
        width: 100%;
        border-collapse: collapse;
    }

    .recent-orders-table th,
    .recent-orders-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #f0f0f0;
        
    }

    .recent-orders-table th {
        background-color: #f8f9fa;
        
        font-weight: 600;
        color: var(--admin-secondary);
    }

    .recent-orders-table tr:last-child td {
        border-bottom: none;
        
    }

    
    .order-status {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        
        font-size: 0.8rem;
        font-weight: 500;
    }

    
    .status-pending {
        background-color: rgba(255, 193, 7, 0.1);
        
        color: #ffc107;
    }

    .status-processing {
        background-color: rgba(0, 123, 255, 0.1);
        
        color: #007bff;
    }

    .status-completed {
        background-color: rgba(40, 167, 69, 0.1);
        
        color: #28a745;
    }

    .status-cancelled {
        background-color: rgba(220, 53, 69, 0.1);
        
        color: #dc3545;
    }

    
    .view-order-btn {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background-color: var(--admin-primary);
        color: white;
        border-radius: 4px;
        text-decoration: none;
        font-size: 0.8rem;
        transition: background-color 0.2s ease;
    }

    .view-order-btn:hover {
        background-color: var(--admin-secondary);
    }

    
    .no-orders {
        text-align: center;
        color: #6c757d;
        padding: 2rem !important;
        
    }

    
    .view-all-orders {
        padding: 1rem;
        text-align: center;
        border-top: 1px solid #f0f0f0;
        
    }

    .view-all-link {
        color: var(--admin-primary);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s ease;
    }

    .view-all-link:hover {
        color: var(--admin-secondary);
    }

    .view-all-link i {
        margin-left: 0.5rem;
        font-size: 0.8rem;
    }
</style>