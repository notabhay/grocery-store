<?php




$page_title = $page_title ?? 'My Orders';

$orders = $orders ?? [];

$page_title_safe = htmlspecialchars($page_title);

$session = App\Core\Registry::get('session');
?>
<!
<main class="full-width-main">
    <!
    <div class="container">
        <!
        <h1 class="page-title"><?= $page_title_safe ?></h1>
        <!
        <p class="page-subtitle">View and track your order history</p>

        <!
        <?php if ($session->hasFlash('success')): ?>
            <div class="alert alert-success" role="alert">
                <?= htmlspecialchars($session->getFlash('success')); ?>
            </div>
        <?php endif; ?>
        <?php if ($session->hasFlash('error')): ?>
            <div class="alert alert-error" role="alert">
                <?= htmlspecialchars($session->getFlash('error')); ?>
            </div>
        <?php endif; ?>
        <?php if ($session->hasFlash('info')): ?>
            <div class="alert alert-info" role="alert">
                <?= htmlspecialchars($session->getFlash('info')); ?>
            </div>
        <?php endif; ?>

        <!
        <?php if (!empty($orders)): ?>
            <!
            <div class="orders-container">
                <!
                <div class="orders-filter">
                    <div class="filter-group">
                        <label for="order-status-filter">Filter by Status:</label>
                        <!
                        <select id="order-status-filter" class="form-control">
                            <option value="all">All Orders</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <!
                <div class="orders-list">
                    <!
                    <div class="table-responsive">
                        <!
                        <table class="order-table">
                            <!
                            <thead>
                                <tr>
                                    <th scope="col">Order #</th>
                                    <th scope="col">Date</th>
                                    <th scope="col" class="text-end">Total</th>
                                    <th scope="col" class="text-center">Status</th>
                                    <th scope="col" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <!
                            <tbody>
                                <!
                                <?php foreach ($orders as $order):
                                    
                                    if (!is_array($order) || !isset($order['order_id'], $order['order_date_formatted'], $order['total_amount_formatted'], $order['status_class'], $order['status_text']))
                                        continue; 

                                    
                                    $order_id_safe = htmlspecialchars($order['order_id']);
                                    $order_date_safe = htmlspecialchars($order['order_date_formatted']);
                                    $total_amount_safe = htmlspecialchars($order['total_amount_formatted']);
                                    $status_class_safe = htmlspecialchars($order['status_class']);
                                    $status_text_safe = htmlspecialchars($order['status_text']);
                                    
                                    $status_value = strtolower($order['status'] ?? 'unknown');
                                ?>
                                    <!
                                    <tr class="order-row" data-status="<?= $status_value ?>">
                                        <!
                                        <td>#<?= $order_id_safe ?></td>
                                        <!
                                        <td><?= $order_date_safe ?></td>
                                        <!
                                        <td class="text-end"><?= $total_amount_safe ?></td>
                                        <!
                                        <td class="text-center">
                                            <span class="badge <?= $status_class_safe ?>">
                                                <?= $status_text_safe ?>
                                            </span>
                                        </td>
                                        <!
                                        <td class="text-center">
                                            <!
                                            <a href="<?= BASE_URL ?>order/details/<?= $order_id_safe ?>"
                                                class="btn btn-secondary btn-sm">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; 
                                ?>
                            </tbody>
                        </table>
                    </div> <!
                </div> <!
            </div> <!
        <?php else: 
        ?>
            <!
            <div class="empty-state-container">
                <div class="empty-state-illustration">
                    <img src="<?= BASE_URL ?>assets/images/cart/empty_shopping_cart.png" alt="Empty State Illustration">
                </div>
                <h2 class="empty-state-heading">Your order history is empty</h2>
                <p class="empty-state-text">You haven't placed any orders with us yet. Explore our fresh selection and
                    enjoy convenient delivery to your doorstep!</p>
                <!
                <a href="<?= BASE_URL ?>categories" class="btn btn-primary empty-state-cta">Browse Products</a>
            </div>
        <?php endif; 
        ?>
    </div> <!
</main> <!
<!
<script>
    
    document.addEventListener('DOMContentLoaded', function() {
        
        const statusFilter = document.getElementById('order-status-filter');
        const orderRows = document.querySelectorAll('.order-row');

        
        if (statusFilter) {
            
            statusFilter.addEventListener('change', function() {
                
                const selectedStatus = this.value;

                
                orderRows.forEach(row => {
                    
                    const rowStatus = row.getAttribute('data-status');

                    
                    if (selectedStatus === 'all' || selectedStatus === rowStatus) {
                        
                        row.style.display = ''; 
                    } else {
                        
                        row.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
<!
<style>
    
    .btn-sm {
        padding: 6px 12px;
        
        font-size: 14px;
        
    }

    
</style>