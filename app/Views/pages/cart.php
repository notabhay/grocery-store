<div class="cart-container">
    <h1>Your Shopping Cart</h1>
    <?php 
    ?>
    <?php if ($is_empty): ?>
        <!
        <div class="empty-state-container">
            <div class="empty-state-illustration">
                <!
                <img src="<?= BASE_URL ?>assets/images/cart/empty_shopping_cart.png" alt="Empty State Illustration">
            </div>
            <h2 class="empty-state-heading">Your cart is looking empty</h2>
            <p class="empty-state-text">Looks like you haven't added anything to your cart yet. Discover our fresh selection
                of products and start filling your basket!</p>
            <!
            <a href="<?= BASE_URL ?>categories" class="btn btn-primary empty-state-cta">Browse Products</a>
        </div>
    <?php else: ?>
        <!
        <div class="cart-items">
            <!
            <table class="cart-table" id="cart-items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Actions</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    ?>
                    <?php foreach ($cart_items as $item): ?>
                        <?php 
                        ?>
                        <tr class="cart-item" data-product-id="<?php echo htmlspecialchars($item['product_id']); ?>">
                            <!
                            <td class="product-info">
                                <!
                                <img src="<?= BASE_URL ?>assets/images/products/<?php echo basename(htmlspecialchars($item['image'])); ?>"
                                    alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-thumbnail">
                                <!
                                <div class="product-details">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                </div>
                            </td>
                            <!
                            <td class="product-price">$<?php echo number_format($item['price'], 2); ?></td>
                            <!
                            <td class="product-quantity">
                                <div class="quantity-controls">
                                    <!
                                    <button class="quantity-btn decrease-btn"
                                        data-product-id="<?php echo htmlspecialchars($item['product_id']); ?>">-</button>
                                    <!
                                    <input type="number" class="quantity-input"
                                        value="<?php echo htmlspecialchars($item['quantity']); ?>" min="1" max="99"
                                        data-product-id="<?php echo htmlspecialchars($item['product_id']); ?>"
                                        aria-label="Item quantity">
                                    <!
                                    <button class="quantity-btn increase-btn"
                                        data-product-id="<?php echo htmlspecialchars($item['product_id']); ?>">+</button>
                                </div>
                            </td>
                            <!
                            <td class="product-actions">
                                <!
                                <button class="remove-item-btn"
                                    data-product-id="<?php echo htmlspecialchars($item['product_id']); ?>">
                                    <i class="fas fa-trash"></i> <!
                                </button>
                            </td>
                            <!
                            <td class="product-total">$<?php echo number_format($item['total_price'], 2); ?></td>
                        </tr>
                    <?php endforeach; 
                    ?>
                </tbody>
                <tfoot>
                    <!
                    <tr class="cart-total">
                        <td colspan="4">Total</td>
                        <!
                        <td id="cart-total-price">$<?php echo number_format($total_price, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
            <!
            <div class="cart-actions">
                <!
                <a href="<?= BASE_URL ?>categories" class="continue-shopping-btn">Continue Shopping</a>
                <!
                <div class="cart-actions-right">
                    <!
                    <button id="clear-cart-btn" class="clear-cart-btn">Clear Cart</button>
                    <!
                    <a href="<?= BASE_URL ?>order" class="checkout-btn">Proceed to Checkout</a>
                </div>
            </div>
        </div> <!
    <?php endif; 
    ?>
</div> <!