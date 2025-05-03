<! <div class="container-fluid">
    <! <div class="row mb-4">
        <! <div class="col-md-6">
            <h1>Order #<?= htmlspecialchars($order['order_id'])
                        ?></h1>
            </div>
            <! <div class="col-md-6 text-end">
                <a href="<?= BASE_URL ?>admin/orders" class="btn btn-secondary">Back to Orders</a>
                </div>
                </div>
                <?php
                ?>
                <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['flash_success']
                        ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php
                    ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['flash_error']
                        ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php
                    ?>
                <?php endif; ?>
                <! <div class="row">
                    <! <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <! <div class="mb-3">
                                    <strong>Order Date:</strong>
                                    <div><?= date('F d, Y H:i:s', strtotime($order['order_date']))
                                            ?></div>
                            </div>
                            <! <div class="mb-3">
                                <strong>Current Status:</strong>
                                <div>
                                    <?php
                                    ?>
                                    <span class="badge bg-<?= getStatusBadgeClass($order['status']) ?> fs-6">
                                        <?= ucfirst(htmlspecialchars($order['status']))
                                        ?>
                                    </span>
                                </div>
                        </div>
                        <! <div class="mb-3">
                            <strong>Total Amount:</strong>
                            <div class="fs-5">$<?= number_format($order['total_amount'], 2)
                                                ?></div>
                            </div>
                            <! <form action="<?= BASE_URL ?>admin/orders/<?= $order['order_id'] ?>/status" method="post"
                                class="mt-4">
                                <?php
                                ?>
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <div class="mb-3">
                                    <label for="status" class="form-label"><strong>Update Status:</strong></label>
                                    <select name="status" id="status" class="form-select">
                                        <?php
                                        ?>
                                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>
                                            Pending
                                        </option>
                                        <option value="processing"
                                            <?= $order['status'] === 'processing' ? 'selected' : '' ?>>
                                            Processing</option>
                                        <option value="completed"
                                            <?= $order['status'] === 'completed' ? 'selected' : '' ?>>
                                            Completed</option>
                                        <option value="cancelled"
                                            <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>
                                            Cancelled</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Status</button>
                                </form>
                                </div>
                                </div>
                                </div>
                                <! <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h5 class="mb-0">Customer Information</h5>
                                        </div>
                                        <div class="card-body">
                                            <! <div class="mb-3">
                                                <strong>Name:</strong>
                                                <div><?= htmlspecialchars($order['user_name'])
                                                        ?></div>
                                        </div>
                                        <! <div class="mb-3">
                                            <strong>Email:</strong>
                                            <div><?= htmlspecialchars($order['user_email'])
                                                    ?></div>
                                    </div>
                                    <! <div class="mb-3">
                                        <strong>Phone:</strong>
                                        <div><?= htmlspecialchars($order['user_phone'])
                                                ?></div>
                                        </div>
                                        <! <div class="mb-3">
                                            <strong>Shipping Address:</strong>
                                            <?php
                                            ?>
                                            <div class="text-wrap">
                                                <?= nl2br(htmlspecialchars($order['shipping_address'])) ?></div>
                                            </div>
                                            </div>
                                            </div>
                                            </div>
                                            <! <div class="col-md-4 mb-4">
                                                <div class="card h-100">
                                                    <div class="card-header">
                                                        <h5 class="mb-0">Order Notes</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php
                                                        ?>
                                                        <?php if (!empty($order['notes'])): ?>
                                                        <?php
                                                            ?>
                                                        <div class="text-wrap">
                                                            <?= nl2br(htmlspecialchars($order['notes'])) ?></div>
                                                        <?php else: ?>
                                                        <! <p class="text-muted">No notes for this order.</p>
                                                            <?php endif; ?>
                                                    </div>
                                                </div>
                                                </div>
                                                </div>
                                                <! <! <div class="card mt-4">
                                                    <div class="card-header">
                                                        <h5 class="mb-0">Order Items</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php
                                                        ?>
                                                        <?php if (empty($order['items'])): ?>
                                                        <! <div class="alert alert-info">No items found for this order.
                                                    </div>
                                                    <?php else: ?>
                                                    <! <div class="table-responsive">
                                                        <! <table class="table table-striped">
                                                            <! <thead>
                                                                <tr>
                                                                    <th>Product</th>
                                                                    <th>Price</th>
                                                                    <th>Quantity</th>
                                                                    <th class="text-end">Line Total</th>
                                                                </tr>
                                                                </thead>
                                                                <! <tbody>
                                                                    <?php
                                                                    $calculatedTotal = 0;
                                                                    foreach ($order['items'] as $item):
                                                                        $lineTotal = $item['price'] * $item['quantity'];
                                                                        $calculatedTotal += $lineTotal;
                                                                    ?>
                                                                    <tr>
                                                                        <! <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <?php
                                                                                    ?>
                                                                                <?php if (!empty($item['product_image'])): ?>
                                                                                <img src="<?= htmlspecialchars($item['product_image'])
                                                                                                    ?>" alt="<?= htmlspecialchars($item['product_name'])
                                                                                                                ?>"
                                                                                    class="me-3"
                                                                                    style="width: 50px; height: 50px; object-fit: cover;">
                                                                                <?php endif; ?>
                                                                                <div>
                                                                                    <div><?= htmlspecialchars($item['product_name'])
                                                                                                ?></div>
                                                                                    <div class="text-muted small">
                                                                                        Product ID:
                                                                                        <?= htmlspecialchars($item['product_id'])
                                                                                            ?></div>
                                                                                </div>
                                                                            </div>
                                                                            </td>
                                                                            <! <td>$<?= number_format($item['price'], 2)
                                                                                        ?></td>
                                                                                <! <td><?= htmlspecialchars($item['quantity'])
                                                                                            ?></td>
                                                                                    <! <td class="text-end">$<?= number_format($lineTotal, 2)
                                                                                                                    ?>
                                                                                        </td>
                                                                    </tr>
                                                                    <?php endforeach; ?>
                                                                    </tbody>
                                                                    <! <tfoot>
                                                                        <! <tr>
                                                                            <th colspan="3" class="text-end">Subtotal:
                                                                            </th>
                                                                            <th class="text-end">$<?= number_format($calculatedTotal, 2)
                                                                                                    ?></th>
                                                                            </tr>
                                                                            <! <tr>
                                                                                <th colspan="3" class="text-end">Total:
                                                                                </th>
                                                                                <th class="text-end">$<?= number_format($order['total_amount'], 2)
                                                                                                        ?></th>
                                                                                </tr>
                                                                                </tfoot>
                                                                                </table>
                                                                                </div>
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
                                                                                        ?>