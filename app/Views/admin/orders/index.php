<! <div class="container-fluid">
    <! <div class="row mb-4">
        <! <div class="col-md-6">
            <h1>Manage Orders</h1>
            </div>
            <! <div class="col-md-6 text-end">
                <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-secondary">Back to Dashboard</a>
                </div>
                </div>
                <! <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Filter Orders</h5>
                    </div>
                    <div class="card-body">
                        <! <form action="<?= BASE_URL ?>admin/orders" method="get" class="row g-3">
                            <! <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <?php
                                    ?>
                                    <option value="pending"
                                        <?= isset($filters['status']) && $filters['status'] === 'pending' ? 'selected' : '' ?>>
                                        Pending</option>
                                    <option value="processing"
                                        <?= isset($filters['status']) && $filters['status'] === 'processing' ? 'selected' : '' ?>>
                                        Processing</option>
                                    <option value="completed"
                                        <?= isset($filters['status']) && $filters['status'] === 'completed' ? 'selected' : '' ?>>
                                        Completed</option>
                                    <option value="cancelled"
                                        <?= isset($filters['status']) && $filters['status'] === 'cancelled' ? 'selected' : '' ?>>
                                        Cancelled</option>
                                </select>
                    </div>
                    <! <div class="col-md-3">
                        <label for="start_date" class="form-label">From Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="<?= $filters['start_date'] ?? ''
                                                                                                            ?>">
                        </div>
                        <! <div class="col-md-3">
                            <label for="end_date" class="form-label">To Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="<?= $filters['end_date'] ?? ''
                                                                                                            ?>">
                            </div>
                            <! <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                                <a href="<?= BASE_URL ?>admin/orders" class="btn btn-outline-secondary">Reset</a>
                                </div>
                                </form>
                                </div>
                                </div>
                                <! <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Orders</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        ?>
                                        <?php if (empty($orders)): ?>
                                        <! <div class="alert alert-info">No orders found matching your criteria.
                                    </div>
                                    <?php else: ?>
                                    <! <div class="table-responsive">
                                        <! <table class="table table-striped table-hover">
                                            <! <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Customer</th>
                                                    <th>Order Date</th>
                                                    <th>Total Amount</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                                </thead>
                                                <! <tbody>
                                                    <?php
                                                    ?>
                                                    <?php foreach ($orders as $order): ?>
                                                    <tr>
                                                        <! <td>#<?= htmlspecialchars($order['order_id'])
                                                                    ?></td>
                                                            <! <td>
                                                                <?= htmlspecialchars($order['user_name'])
                                                                    ?><br>
                                                                <small class="text-muted"><?= htmlspecialchars($order['user_email'])
                                                                                                ?></small>
                                                                </td>
                                                                <! <td><?= date('M d, Y H:i', strtotime($order['order_date']))
                                                                            ?></td>
                                                                    <! <td>$<?= number_format($order['total_amount'], 2)
                                                                                ?></td>
                                                                        <! <td>
                                                                            <?php
                                                                                ?>
                                                                            <span
                                                                                class="badge bg-<?= getStatusBadgeClass($order['status']) ?>">
                                                                                <?= ucfirst(htmlspecialchars($order['status']))
                                                                                    ?>
                                                                            </span>
                                                                            </td>
                                                                            <! <td>
                                                                                <! <a
                                                                                    href="<?= BASE_URL ?>admin/orders/<?= $order['order_id']
                                                                                                                            ?>"
                                                                                    class="btn btn-sm btn-primary">
                                                                                    View</a>
                                                                                    </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                    </tbody>
                                                    </table>
                                                    </div>
                                                    <?php
                                                    ?>
                                                    <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                                                    <! <nav aria-label="Page navigation" class="mt-4">
                                                        <ul class="pagination justify-content-center">
                                                            <?php
                                                                ?>
                                                            <?php if ($pagination['current_page'] > 1): ?>
                                                            <li class="page-item">
                                                                <?php
                                                                        ?>
                                                                <a class="page-link"
                                                                    href="?page=<?= $pagination['current_page'] - 1 ?><?= buildFilterQueryString($filters) ?>"
                                                                    aria-label="Previous">
                                                                    <span aria-hidden="true">&laquo;</span>
                                                                </a>
                                                            </li>
                                                            <?php else: ?>
                                                            <! <li class="page-item disabled">
                                                                <span class="page-link">&laquo;</span>
                                                                </li>
                                                                <?php endif; ?>
                                                                <?php
                                                                    $startPage = max(1, $pagination['current_page'] - 2);
                                                                    $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                                                                    if ($startPage > 1) {
                                                                        echo '<li class="page-item"><a class="page-link" href="?page=1' . buildFilterQueryString($filters) . '">1</a></li>';
                                                                        if ($startPage > 2) {
                                                                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                                                        }
                                                                    }
                                                                    for ($i = $startPage; $i <= $endPage; $i++) {
                                                                        if ($i == $pagination['current_page']) {
                                                                            echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
                                                                        } else {
                                                                            echo '<li class="page-item"><a class="page-link" href="?page=' . $i . buildFilterQueryString($filters) . '">' . $i . '</a></li>';
                                                                        }
                                                                    }
                                                                    if ($endPage < $pagination['total_pages']) {
                                                                        if ($endPage < $pagination['total_pages'] - 1) {
                                                                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                                                        }
                                                                        echo '<li class="page-item"><a class="page-link" href="?page=' . $pagination['total_pages'] . buildFilterQueryString($filters) . '">' . $pagination['total_pages'] . '</a></li>';
                                                                    }
                                                                    ?>
                                                                <?php
                                                                    ?>
                                                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                                                <li class="page-item">
                                                                    <?php
                                                                            ?>
                                                                    <a class="page-link"
                                                                        href="?page=<?= $pagination['current_page'] + 1 ?><?= buildFilterQueryString($filters) ?>"
                                                                        aria-label="Next">
                                                                        <span aria-hidden="true">&raquo;</span>
                                                                    </a>
                                                                </li>
                                                                <?php else: ?>
                                                                <! <li class="page-item disabled">
                                                                    <span class="page-link">&raquo;</span>
                                                                    </li>
                                                                    <?php endif; ?>
                                                        </ul>
                                                        </nav>
                                                        <?php endif;
                                                        ?>
                                                        <?php endif;
                                                    ?>
                                                        </div>
                                                        <! </div>
                                                            <! </div>
                                                                <! <?php
                                                                function getStatusBadgeClass($status)
                                                                {
                                                                    switch ($status) {
                                                                        case 'pending':
                                                                            return 'warning';
                                                                        case 'processing':
                                                                            return 'info';
                                                                        case 'completed':
                                                                            return 'success';
                                                                        case 'cancelled':
                                                                            return 'danger';
                                                                        default:
                                                                            return 'secondary';
                                                                    }
                                                                }
                                                                function buildFilterQueryString($filters)
                                                                {
                                                                    $queryString = '';
                                                                    if (!empty($filters)) {
                                                                        foreach ($filters as $key => $value) {
                                                                            if (!empty($value)) {
                                                                                $queryString .= "&{$key}=" . urlencode($value);
                                                                            }
                                                                        }
                                                                    }
                                                                    return $queryString;
                                                                }
                                                                ?>