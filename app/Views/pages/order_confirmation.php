<?php




$page_title = $page_title ?? 'Order Confirmation';

$order = $order ?? [];

$page_title_safe = htmlspecialchars($page_title);

$session = App\Core\Registry::get('session');



if (empty($order) || !isset($order['order_id'])) {
    echo "<div class='container'><div class='alert alert-error'>Error: Order data is missing or invalid. Cannot display confirmation.</div></div>";
    
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
        <p class="page-subtitle">Thank you for your purchase!</p>

        <!
        <div class="confirmation-success">
            <i class="fas fa-check-circle"></i> <!
            <p class="lead">Your order has been successfully placed!</p>
        </div>

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
        <div class="order-summary-grid">
            <!
            <div class="summary-block">
                <h3>Order Information</h3>
                <div class="info-group">
                    <!
                    <div class="info-item">
                        <span class="info-label">Order Number:</span>
                        <span class="info-value">#<?= $order_id_safe ?></span>
                    </div>
                    <!
                    <div class="info-item">
                        <span class="info-label">Order Date:</span>
                        <span class="info-value"><?= $order_date_formatted_safe ?></span>
                    </div>
                    <!
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="badge <?= $status_class_safe ?>"><?= $status_text_safe ?></span>
                    </div>
                    <!
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
                    <!
                    <div class="info-item">
                        <span class="info-label">Name:</span>
                        <span class="info-value"><?= $user_name_safe ?></span>
                    </div>
                    <!
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?= $user_email_safe ?></span>
                    </div>
                    <!
                    <?php if (!empty($user_phone_safe) && $user_phone_safe !== 'N/A'): ?>
                        <div class="info-item">
                            <span class="info-label">Phone:</span>
                            <span class="info-value"><?= $user_phone_safe ?></span>
                        </div>
                    <?php endif; ?>
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
        <?php if (!empty($items)): ?>
            <div class="order-summary-section">
                <h2>Items Ordered</h2>
                <!
                <div class="table-responsive">
                    <!
                    <table class="order-table">
                        <!
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-end">Price</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <!
                        <tbody>
                            <!
                            <?php foreach ($items as $item):
                                
                                if (!is_array($item) || !isset($item['image_url'], $item['product_name'], $item['quantity'], $item['price_formatted'], $item['subtotal_formatted']))
                                    continue; 

                                
                                $item_image_url_safe = htmlspecialchars($item['image_url']);
                                $item_name_safe = htmlspecialchars($item['product_name']);
                                $item_quantity_safe = htmlspecialchars($item['quantity']);
                                $item_price_safe = htmlspecialchars($item['price_formatted']);
                                $item_subtotal_safe = htmlspecialchars($item['subtotal_formatted']);
                            ?>
                                <!
                                <tr>
                                    <!
                                    <td class="product-details">
                                        <img src="<?= $item_image_url_safe ?>" alt="<?= $item_name_safe ?>"
                                            class="product-thumbnail">
                                        <span class="product-name"><?= $item_name_safe ?></span>
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
                                <td colspan="3" class="text-end">Total:</td>
                                <td class="text-end order-total"><?= $total_amount_formatted_safe ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div> <!
            </div> <!
        <?php endif; 
        ?>

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
        <div class="confirmation-actions">
            <!
            <a href="<?= BASE_URL ?>categories" class="btn btn-primary">Continue Shopping</a>
            <!
            <a href="<?= BASE_URL ?>orders" class="btn btn-secondary">View My Orders</a>
            <!
            <button class="btn btn-secondary print-confirmation" onclick="window.print(); return false;">
                <i class="fas fa-print"></i> Print Confirmation
            </button>
        </div>
    </div> <!
</main> <!
<!
<style type="text/css" media="print">
    
    header,
    footer,
    .confirmation-actions,
    
    .mobile-menu-toggle {
        
        display: none !important;
    }

    
    .container {
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        max-width: none !important;
        box-shadow: none !important;
        
        border: none !important;
        
    }

    
    .page-title,
    .page-subtitle {
        text-align: center !important;
        margin: 20px 0 !important;
    }

    
    .confirmation-success {
        margin: 20px 0 !important;
        padding: 10px 0 !important;
        text-align: center;
        
    }

    .confirmation-success i {
        font-size: 2em !important;
        
    }

    
    body {
        font-size: 10pt !important;
        color: #000 !important;
        
        background-color: #fff !important;
        
    }

    
    .order-table th,
    .order-table td {
        padding: 5px 8px !important;
        border: 1px solid #ccc !important;
        
    }

    
    .product-thumbnail {
        max-width: 40px !important;
        height: auto !important;
        vertical-align: middle;
        
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
</style>