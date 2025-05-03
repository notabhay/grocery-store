<?php




use App\Core\Session;
use App\Core\Registry;






if (!isset($product) || !is_array($product)) {
    
    
    
    
}


if (!isset($csrfToken)) {
    
    
    
    $csrfToken = ''; 
}
?>
<!
<main class="full-width-main">
    <!
    <div class="container">
        <!
        <h1 class="page-title">Place Your Order</h1>
        <!
        <p class="page-subtitle">Review and confirm your order details for
            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
        </p>

        <!
        <?php
        
        $session = Registry::get('session');
        
        if ($session->hasFlash('error')) {
            echo '<div class="alert alert-error">' . htmlspecialchars($session->getFlash('error')) . '</div>';
        }
        
        if ($session->hasFlash('success')) {
            echo '<div class="alert alert-success">' . htmlspecialchars($session->getFlash('success')) . '</div>';
        }
        
        if ($session->hasFlash('info')) {
            echo '<div class="alert alert-info">' . htmlspecialchars($session->getFlash('info')) . '</div>';
        }
        ?>

        <!
        <div class="order-content">
            <!
            <section class="order-summary-section">
                <h2>Product Details</h2>
                <!
                <div class="product-details">
                    <!
                    <img src="<?= BASE_URL ?>assets/images/products/<?php echo htmlspecialchars(basename($product['image_path'] ?? 'default.png')); 
                                                                    ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-thumbnail">
                    <!
                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="product-price">$<?php echo number_format($product['price'], 2); ?> per unit</div>
                        <div class="product-stock">
                            <span class="stock-label">In Stock:</span>
                            <span class="stock-value"><?php echo htmlspecialchars($product['stock_quantity']); ?>
                                units</span>
                        </div>
                    </div>
                </div>
                <!
                <div class="order-summary">
                    <h3>Order Summary</h3>
                    <div class="summary-row">
                        <span>Product:</span>
                        <span><?php echo htmlspecialchars($product['name']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Price:</span>
                        <span>$<?php echo number_format($product['price'], 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Quantity:</span>
                        <!
                        <span id="quantity-value">1</span>
                    </div>
                    <div class="summary-row total">
                        <span id="total-price-label">Total:</span>
                        <!
                        <span id="total-price">$<?php echo number_format($product['price'], 2); 
                                                ?></span>
                    </div>
                </div>
            </section> <!

            <!
            <section class="shipping-payment-section">
                <h2>Order Details</h2>
                <!
                <form action="<?= BASE_URL ?>order/product/<?php echo htmlspecialchars($product['product_id']); 
                                                            ?>" method="post" class="order-form">
                    <!
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <!
                    <input type="hidden" name="product_id"
                        value="<?php echo htmlspecialchars($product['product_id']); ?>">

                    <!
                    <div class="form-group">
                        <label for="quantity">Quantity:</label>
                        <div class="quantity-controls">
                            <!
                            <button type="button" class="quantity-btn decrease-btn" aria-label="Decrease quantity">
                                <i class="fas fa-minus"></i>
                            </button>
                            <!
                            <input type="number" id="quantity" name="quantity" class="quantity-input" value="1" min="1"
                                max="<?php echo htmlspecialchars($product['stock_quantity']); 
                                        ?>" required data-price="<?php echo htmlspecialchars($product['price']); 
                                                                    ?>" aria-describedby="total-price-label">
                            <!
                            <button type="button" class="quantity-btn increase-btn" aria-label="Increase quantity">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!
                    <div class="form-group">
                        <label for="shipping_address" class="form-label">Shipping Address</label>
                        <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3"
                            placeholder="Enter your shipping address" required></textarea>
                        <!
                    </div>

                    <!
                    <div class="form-group">
                        <label for="notes">Order Notes (Optional):</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3"
                            placeholder="Add any special instructions for your order"></textarea>
                    </div>

                    <!
                    <div class="payment-methods">
                        <h3>Payment Method</h3>
                        <!
                        <div class="payment-option">
                            <input type="radio" id="payment_cash" name="payment_method" value="cash" checked>
                            <label for="payment_cash">Cash on Delivery</label>
                        </div>
                    </div>

                    <!
                    <div class="form-actions">
                        <!
                        <a href="<?= BASE_URL ?>categories" class="btn btn-secondary">Continue Shopping</a>
                        <!
                        <button type="submit" name="place_order" class="btn btn-primary">Place Order</button>
                    </div>
                </form> <!
            </section> <!
        </div> <!
    </div> <!
</main> <!
<!
<script>
    
    document.addEventListener('DOMContentLoaded', function() {
        
        const quantityInput = document.getElementById('quantity');
        const decreaseBtn = document.querySelector('.quantity-btn.decrease-btn');
        const increaseBtn = document.querySelector('.quantity-btn.increase-btn');
        const quantityValueSpan = document.getElementById('quantity-value'); 
        const totalPriceSpan = document.getElementById('total-price'); 
        const productPrice = parseFloat(quantityInput.getAttribute('data-price')); 
        const maxQuantity = parseInt(quantityInput.max, 10); 

        
        function updateTotal() {
            let quantity = parseInt(quantityInput.value, 10);

            
            if (isNaN(quantity) || quantity < 1) {
                quantity = 1; 
                quantityInput.value = 1;
            }
            if (quantity > maxQuantity) {
                quantity = maxQuantity; 
                quantityInput.value = maxQuantity;
            }

            
            const total = (productPrice * quantity).toFixed(2); 

            
            quantityValueSpan.textContent = quantity; 
            totalPriceSpan.textContent = '$' + total; 
        }

        
        decreaseBtn.addEventListener('click', function() {
            let currentQuantity = parseInt(quantityInput.value, 10);
            if (currentQuantity > 1) { 
                quantityInput.value = currentQuantity - 1;
                updateTotal(); 
            }
        });

        
        increaseBtn.addEventListener('click', function() {
            let currentQuantity = parseInt(quantityInput.value, 10);
            if (currentQuantity < maxQuantity) { 
                quantityInput.value = currentQuantity + 1;
                updateTotal(); 
            }
        });

        
        quantityInput.addEventListener('input', updateTotal);

        
        updateTotal();
    });
</script>