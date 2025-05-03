<?php




$page_title = $page_title ?? 'Order Details';

$order = $order ?? [];

$csrfToken = $csrfToken ?? '';

$page_title_safe = htmlspecialchars($page_title);

$csrfToken_safe = htmlspecialchars($csrfToken);

$session = App\Core\Registry::get('session');



if (empty($order) || !isset($order['order_id'])) {
    echo "<div class='container'><div class='alert alert-error'>Error: Order data is missing or invalid. Cannot display details.</div></div>";
    
    return;
}



$order_id_safe = htmlspecialchars($order['order_id']);
$status_class_safe = htmlspecialchars($order['status_class'] ?? 'status-unknown');
$status_text_safe = htmlspecialchars($order['status_text'] ?? 'Unknown');
$order_date_formatted_safe = htmlspecialchars($order['order_date_formatted'] ?? 'N/A');
$total_amount_formatted_safe = htmlspecialchars($order['total_amount_formatted'] ?? '$0.00');


$user_name_safe = htmlspecialchars($order['user_name'] ?? 'N/A');
$user_email_safe = htmlspecialchars($order['user_email'] ?? 'N/A');
$user_phone_safe = htmlspecialchars($order['user_phone'] ?? 'N/A'); 


$notes_safe = isset($order['notes']) ? nl2br(htmlspecialchars($order['notes'])) : '';
$shipping_address_safe = isset($order['shipping_address']) ? nl2br(htmlspecialchars($order['shipping_address'])) : '';


$items = $order['items'] ?? [];
?>
<!
<main class="full-width-main">
    <!
    <div class="container">
        <!
        <h1 class="page-title"><?= $page_title_safe ?></h1>
        <!
        <p class="page-subtitle">Order #<?= $order_id_safe ?></p>

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
        <div class="details-container">
            <!
            <div class="details-header">
                <!
                <div class="order-status">
                    Status: <span class="badge <?= $status_class_safe ?>"><?= $status_text_safe ?></span>
                </div>
                <!
                <div class="order-actions">
                    <!
                    <a href="<?= BASE_URL ?>orders" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Orders
                    </a>
                    <!
                    <?php if (isset($order['status']) && $order['status'] === 'pending'): ?>
                        <!
                        <form action="<?= BASE_URL ?>order/cancel/<?= $order_id_safe ?>" method="post"
                            class="d-inline cancel-form" id="cancelOrderForm">
                            <!
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken_safe ?>">
                            <!
                            <button type="button" name="cancel_order" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                data-bs-target="#cancelOrderModal" data-cancel-url="/order/cancel/<?= $order_id_safe ?>">
                                <i class="fas fa-times"></i> Cancel Order
                            </button>
                            <!
                        </form>
                    <?php endif; ?>
                    <!
                    <button class="btn btn-secondary btn-sm" onclick="window.print(); return false;">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div> <!

            <!
            <div class="order-summary-grid">
                <!
                <div class="summary-block">
                    <h3>Order Information</h3>
                    <div class="info-group">
                        <div class="info-item">
                            <span class="info-label">Order Number:</span>
                            <span class="info-value">#<?= $order_id_safe ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Order Date:</span>
                            <span class="info-value"><?= $order_date_formatted_safe ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Status:</span>
                            <!
                            <span
                                class="info-value status-text-<?= strtolower($order['status'] ?? 'unknown') ?>"><?= $status_text_safe ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Total Amount:</span>
                            <span class="info-value order-total"><?= $total_amount_formatted_safe ?></span>
                        </div>
                    </div>
                </div> <!

                <!
                <div class="summary-block">
                    <h3>Customer Information</h3>
                    <div class="info-group">
                        <div class="info-item">
                            <span class="info-label">Name:</span>
                            <span class="info-value"><?= $user_name_safe ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?= $user_email_safe ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phone:</span>
                            <span class="info-value"><?= $user_phone_safe ?></span>
                        </div>
                    </div>
                </div> <!
            </div> <!

            <!
            <?php if (!empty($shipping_address_safe)): ?>
                <div class="summary-block">
                    <h3>Shipping Address</h3>
                    <div class="shipping-address">
                        <?= $shipping_address_safe 
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <!
            <div class="order-items-section">
                <h3>Order Items</h3>
                <!
                <?php if (!empty($items)): ?>
                    <!
                    <div class="table-responsive">
                        <!
                        <table class="order-table">
                            <!
                            <thead>
                                <tr>
                                    <th scope="col" colspan="2">Product</th> <!
                                    <th scope="col" class="text-end">Unit Price</th>
                                    <th scope="col" class="text-center">Quantity</th>
                                    <th scope="col" class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <!
                            <tbody>
                                <!
                                <?php foreach ($items as $item):
                                    
                                    if (!is_array($item) || !isset($item['image_url'], $item['product_name'], $item['price_formatted'], $item['quantity'], $item['subtotal_formatted'], $item['product_id']))
                                        continue; 

                                    
                                    $item_image_url_safe = htmlspecialchars($item['image_url']);
                                    $item_name_safe = htmlspecialchars($item['product_name']);
                                    $item_id_safe = htmlspecialchars($item['product_id']);
                                    $item_price_safe = htmlspecialchars($item['price_formatted']);
                                    $item_quantity_safe = htmlspecialchars($item['quantity']);
                                    $item_subtotal_safe = htmlspecialchars($item['subtotal_formatted']);
                                ?>
                                    <!
                                    <tr>
                                        <!
                                        <td style="width: 80px;">
                                            <img src="<?= $item_image_url_safe ?>" alt="<?= $item_name_safe ?>"
                                                class="product-thumbnail">
                                        </td>
                                        <!
                                        <td>
                                            <?= $item_name_safe ?>
                                            <small class="product-id">ID: <?= $item_id_safe ?></small>
                                        </td>
                                        <!
                                        <td class="text-end"><?= $item_price_safe ?></td>
                                        <!
                                        <td class="text-center"><?= $item_quantity_safe ?></td>
                                        <!
                                        <td class="text-end"><?= $item_subtotal_safe ?></td>
                                    </tr>
                                <?php endforeach; 
                                ?>
                            </tbody>
                            <!
                            <tfoot>
                                <tr>
                                    <!
                                    <td colspan="4" class="text-end fw-bold">Total:</td>
                                    <td class="text-end fw-bold"><?= $total_amount_formatted_safe ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div> <!
                <?php else: 
                ?>
                    <p>No items found for this order.</p>
                <?php endif; 
                ?>
            </div> <!

            <!
            <?php if (!empty($notes_safe)): ?>
                <div class="summary-block">
                    <h3>Order Notes</h3>
                    <div class="notes-content">
                        <?= $notes_safe 
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <!
            <div class="order-timeline">
                <h3>Order Timeline</h3>
                <div class="timeline">
                    <!
                    <div class="timeline-item active">
                        <div class="timeline-icon"><i class="fas fa-check"></i></div>
                        <div class="timeline-content">
                            <h4>Order Placed</h4>
                            <p class="timeline-date"><?= $order_date_formatted_safe ?></p>
                            <p class="timeline-description">Your order has been received and is being processed.</p>
                        </div>
                    </div>

                    <!
                    <?php if (isset($order['status']) && ($order['status'] === 'processing' || $order['status'] === 'completed')): ?>
                        <div class="timeline-item active">
                            <div class="timeline-icon"><i class="fas fa-cog"></i></div>
                            <div class="timeline-content">
                                <h4>Processing</h4>
                                <p class="timeline-description">
                                    <?= $order['status'] === 'processing' ? 'Your order is being prepared for shipping.' : 'Your order was prepared for shipping.' ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!
                    <?php if (isset($order['status']) && $order['status'] === 'completed'): ?>
                        <div class="timeline-item active terminal-status">
                            <div class="timeline-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="timeline-content">
                                <h4>Completed</h4>
                                <p class="timeline-description">Your order has been delivered successfully.</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!
                    <?php if (isset($order['status']) && $order['status'] === 'cancelled'): ?>
                        <div class="timeline-item active cancelled terminal-status">
                            <div class="timeline-icon"><i class="fas fa-times"></i></div>
                            <div class="timeline-content">
                                <h4>Cancelled</h4>
                                <p class="timeline-description">This order has been cancelled.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div> <!
            </div> <!
        </div> <!
    </div> <!
</main> <!
<!
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
    <!
    <div class="modal-backdrop"></div>
    <!
    <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="cancelOrderModalLabel">
        <!
        <h4 id="cancelOrderModalLabel">Confirm Cancellation</h4>
        <!
        <p>Are you sure you want to cancel this order? This action cannot be undone.</p>
        <!
        <div class="modal-buttons">
            <!
            <button id="modalCloseBtn" class="modal-btn cancel-btn" aria-label="Keep Order">Keep Order</button>
            <!
            <button id="confirmCancelBtn" class="modal-btn confirm-btn" aria-label="Confirm Cancellation">Confirm
                Cancellation</button>
        </div>
    </div>
</div>
<!
<!
<style>
    
    .btn-sm {
        padding: 6px 12px;
        font-size: 14px;
    }

    
    .btn-danger {
        background-color: var(--tomato-red);
        
        color: white;
        border: none;
        
    }

    .btn-danger:hover {
        background-color: var(--dark-tomato-red);
        
    }

    
    .product-id {
        display: block;
        font-size: 0.8em;
        color: #666;
    }

    
    @media print {

        
        header,
        footer,
        .order-actions,
        
        .mobile-menu-toggle,
        
        .details-actions,
        
        .modal,
        
        .modal-backdrop {
            
            display: none !important;
        }

        
        .container {
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            max-width: none !important;
        }

        
        .details-container {
            box-shadow: none !important;
            border: 1px solid #ccc !important;
            
            padding: 10px !important;
            margin-top: 0 !important;
        }

        
        body {
            font-size: 10pt !important;
            color: #000 !important;
            background-color: #fff !important;
        }

        
        .order-table {
            font-size: 9pt !important;
        }

        .order-table th,
        .order-table td {
            border: 1px solid #ccc !important;
            padding: 4px 6px !important;
            
        }

        
        .product-thumbnail {
            max-width: 40px !important;
            height: auto !important;
            vertical-align: middle;
        }

        
        .timeline::before,
        
        .timeline-icon {
            
            display: none !important;
        }

        .timeline-item {
            padding-left: 0 !important;
            
            margin-bottom: 10px !important;
            page-break-inside: avoid !important;
            
        }

        .timeline-content {
            padding-left: 0 !important;
        }

        .timeline-date {
            font-size: 0.9em;
            color: #555;
        }

        
        .summary-block {
            border: 1px solid #eee !important;
            padding: 10px !important;
            margin-bottom: 15px !important;
            page-break-inside: avoid !important;
        }

        .order-summary-grid {
            display: block !important;
            
        }

        
        .badge {
            background-color: transparent !important;
            color: #000 !important;
            border: 1px solid #ccc !important;
            padding: 2px 4px !important;
        }
    }
</style>