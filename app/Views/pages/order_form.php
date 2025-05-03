<?php
$page_title = $page_title ?? 'Place Your Order';
$cartItems = $cartItems ?? [];
$totalAmount = $totalAmount ?? 0;
$csrfToken = $csrfToken ?? '';
$page_title_safe = htmlspecialchars($page_title);
$csrfToken_safe = htmlspecialchars($csrfToken);
$session = App\Core\Registry::get('session');
?>
<! <main class="full-width-main">
    <! <div class="container">
        <! <h1 class="page-title"><?= $page_title_safe ?></h1>
            <! <p class="page-subtitle">Review your cart and complete your purchase</p>
                <! <?php if ($session->hasFlash('warning')): ?> <div class="alert alert-warning" role="alert">
                    <?= htmlspecialchars($session->getFlash('warning')); ?>
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
                    <! <?php if (empty($cartItems)): ?> <! <div class="empty-cart">
                        <img src="<?= BASE_URL ?>assets/images/cart/empty_shopping_cart.png" alt="Empty Shopping Cart"
                            class="empty-cart-image">
                        <p>Your cart is currently empty.</p>
                        <! <a href="<?= BASE_URL ?>categories" class="btn btn-primary">Browse Products</a>
                            </div>
                            <?php else:
                    ?>
                            <! <div class="order-content">
                                <! <section class="order-summary-section">
                                    <h2>Order Summary</h2>
                                    <! <div class="table-responsive">
                                        <! <table class="order-table">
                                            <! <thead>
                                                <tr>
                                                    <th scope="col">Product</th>
                                                    <th scope="col" class="text-end">Price</th>
                                                    <th scope="col" class="text-center">Quantity</th>
                                                    <th scope="col" class="text-end">Subtotal</th>
                                                </tr>
                                                </thead>
                                                <! <tbody>
                                                    <! <?php foreach ($cartItems as $item):
                                                        if (!is_array($item) || !isset($item['image_url'], $item['name'], $item['price'], $item['quantity'], $item['subtotal']))
                                                            continue;
                                                        $item_image_url_safe = htmlspecialchars($item['image_url']);
                                                        $item_name_safe = htmlspecialchars($item['name']);
                                                        $item_price_formatted = '$' . number_format($item['price'], 2);
                                                        $item_quantity_safe = htmlspecialchars($item['quantity']);
                                                        $item_subtotal_formatted = '$' . number_format($item['subtotal'], 2);
                                                    ?> <! <tr>
                                                        <! <td class="product-details">
                                                            <img src="<?= $item_image_url_safe ?>"
                                                                alt="<?= $item_name_safe ?>" class="product-thumbnail">
                                                            <span class="product-name"><?= $item_name_safe ?></span>
                                                            </td>
                                                            <! <td class="text-end"><?= $item_price_formatted ?></td>
                                                                <! <td class="text-center"><?= $item_quantity_safe ?>
                                                                    </td>
                                                                    <! <td class="text-end">
                                                                        <?= $item_subtotal_formatted ?></td>
                                                                        </tr>
                                                                        <?php endforeach;
                                                                ?>
                                                                        </tbody>
                                                                        <! <tfoot>
                                                                            <tr>
                                                                                <! <td colspan="3"
                                                                                    class="text-end fw-bold">Total:</td>
                                                                                    <! <td class="text-end fw-bold">
                                                                                        $<?= number_format($totalAmount, 2) ?>
                                                                                        </td>
                                                                            </tr>
                                                                            </tfoot>
                                                                            </table>
                                                                            </div>
                                                                            <! </section>
                                                                                <! <! <section
                                                                                    class="shipping-payment-section">
                                                                                    <h2>Shipping & Payment Details</h2>
                                                                                    <! <form
                                                                                        action="<?= BASE_URL ?>order/process"
                                                                                        method="POST" id="order-form">
                                                                                        <! <input type="hidden"
                                                                                            name="csrf_token"
                                                                                            value="<?= $csrfToken_safe ?>">
                                                                                            <! <div class="form-group">
                                                                                                <label
                                                                                                    for="shipping_address"
                                                                                                    class="form-label">Shipping
                                                                                                    Address</label>
                                                                                                <textarea
                                                                                                    class="form-control"
                                                                                                    id="shipping_address"
                                                                                                    name="shipping_address"
                                                                                                    rows="3"
                                                                                                    placeholder="Enter your shipping address"
                                                                                                    required></textarea>
                                                                                                <! </div>
                                                                                                    <! <div
                                                                                                        class="form-group">
                                                                                                        <label
                                                                                                            for="order_notes"
                                                                                                            class="form-label">Order
                                                                                                            Notes
                                                                                                            (Optional)</label>
                                                                                                        <textarea
                                                                                                            class="form-control"
                                                                                                            id="order_notes"
                                                                                                            name="order_notes"
                                                                                                            rows="3"
                                                                                                            placeholder="Add any special instructions here..."></textarea>
                                                                                                        </div>
                                                                                                        <! <div
                                                                                                            class="payment-methods">
                                                                                                            <h3>Payment
                                                                                                                Method
                                                                                                            </h3>
                                                                                                            <! <div
                                                                                                                class="payment-option">
                                                                                                                <input
                                                                                                                    type="radio"
                                                                                                                    id="payment_cash"
                                                                                                                    name="payment_method"
                                                                                                                    value="cash"
                                                                                                                    checked>
                                                                                                                <label
                                                                                                                    for="payment_cash">Cash
                                                                                                                    on
                                                                                                                    Delivery</label>
                                                                                                                <!
                                                                                                                    </div>
                                                                                                                    </div>
                                                                                                                    <! <div
                                                                                                                        class="form-actions">
                                                                                                                        <! <a
                                                                                                                            href="<?= BASE_URL ?>cart"
                                                                                                                            class="btn btn-secondary">
                                                                                                                            Back
                                                                                                                            to
                                                                                                                            Cart</a>
                                                                                                                            <! <button
                                                                                                                                type="submit"
                                                                                                                                class="btn btn-primary">
                                                                                                                                Place
                                                                                                                                Order</button>
                                                                                                                                </div>
                                                                                                                                </form>
                                                                                                                                <!
                                                                                                                                    </section>
                                                                                                                                    <!
                                                                                                                                        </div>
                                                                                                                                        <! <?php endif;
                                                                                                                                    ?> </div>
                                                                                                                                            <!
                                                                                                                                                </main>
                                                                                                                                                <!