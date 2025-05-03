<?php
$page_title = $page_title ?? 'GhibliGroceries';
$meta_description = $meta_description ?? 'Fresh groceries delivered.';
$meta_keywords = $meta_keywords ?? 'grocery, online shopping';
$random_products = $random_products ?? [];
$logged_in = $logged_in ?? false;
?>
<! <section class="hero">
    <! <div class="hero-copy">
        <h1>Let Your <span>Groceries</span> Come To You</h1>
        <p>Get fresh groceries online without stepping out to make delicious food with the freshest ingredients.</p>
        <! <search class="search-bar">
            <input type="text" class="search-input" placeholder="Search products...">
            <button class="search-button" aria-label="Search">
                <i class="fas fa-search"></i>
                <! </button>
                    </search>
                    <! <div class="feature-list">
                        <div class="feature-item">
                            <i class="fas fa-carrot feature-icon"></i> Fresh Vegetables
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle feature-icon"></i> 100% Guarantee
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-money-bill-wave feature-icon"></i> Cash on Delivery
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-shipping-fast feature-icon"></i> Fast Delivery
                        </div>
                        </div>
                        </div>
                        <! <div class="hero-visual">
                            <img src="<?= BASE_URL ?>assets/images/hero_image.png" alt="Delivery person with groceries"
                                class="hero-image">
                            </div>
                            <! <section class="product-showcase">
                                <h2 class="featured-items-title">Today's Featured Items</h2>
                                <?php
                                ?>
                                <?php if (!empty($random_products)): ?>
                                <?php
                                    ?>
                                <?php foreach ($random_products as $product): ?>
                                <! <article class="product-card">
                                    <! <img
                                        src="<?= BASE_URL ?><?php echo htmlspecialchars($product['image_path'] ?? 'assets/images/placeholder.png'); ?>"
                                        alt="<?php echo htmlspecialchars($product['name'] ?? 'Product'); ?>">
                                        <! <div class="product-name">
                                            <?php echo htmlspecialchars($product['name'] ?? 'N/A'); ?></div>
                                            <! <div class="product-price">
                                                $<?php echo number_format($product['price'] ?? 0, 2); ?></div>
                                                <?php
                                                        ?>
                                                <?php if ($logged_in): ?>
                                                <! <button class="add-to-cart-btn" data-product-id="<?php echo htmlspecialchars($product['product_id'] ?? '');
                                                                                    ?>">
                                                    Add to Cart
            </button>
            <?php else: ?>
            <! <a href="<?= BASE_URL ?>login" class="login-to-purchase-btn">
                Login to Purchase
                </a>
                <?php endif; ?>
                </article>
                <?php endforeach;
        ?>
                <?php else: ?>
                <! <p>No products to display currently.</p>
                    <?php endif;
        ?>
                    </section>
                    <! </section>
                        <! <! <section class="category-section">
                            <! <! <a href="<?= BASE_URL ?>categories?filter=Dairy%20Products" class="category-link">
                                <article class="category-item">
                                    <div class="category-icon">
                                        <img src="<?= BASE_URL ?>assets/images/categories/dairy_products_icon.png"
                                            alt="Dairy Products">
                                    </div>
                                    <div class="category-title">Dairy Products</div>
                                    <div class="category-description">Fresh milk, cheese, yogurt, and more.</div>
                                </article>
                                </a>
                                <! <a href="<?= BASE_URL ?>categories?filter=Fruits%20%26%20Veggies"
                                    class="category-link">
                                    <article class="category-item">
                                        <div class="category-icon">
                                            <img src="<?= BASE_URL ?>assets/images/categories/fruits_and_veggies_icon.png"
                                                alt="Fruits & Veggies">
                                        </div>
                                        <div class="category-title">Fruits & Veggies</div>
                                        <div class="category-description">Farm-fresh seasonal produce.</div>
                                    </article>
                                    </a>
                                    <! <a href="<?= BASE_URL ?>categories?filter=Spices%20%26%20Seasonings"
                                        class="category-link">
                                        <article class="category-item">
                                            <div class="category-icon">
                                                <img src="<?= BASE_URL ?>assets/images/categories/spices_and_seasonings_icon.png"
                                                    alt="Spices & Seasonings">
                                            </div>
                                            <div class="category-title">Spices & Seasonings</div>
                                            <div class="category-description">Flavorful additions for your cooking.
                                            </div>
                                        </article>
                                        </a>
                                        <! <a href="<?= BASE_URL ?>categories?filter=Meat" class="category-link">
                                            <article class="category-item">
                                                <div class="category-icon">
                                                    <img src="<?= BASE_URL ?>assets/images/categories/meat_icon.png"
                                                        alt="Meat">
                                                </div>
                                                <div class="category-title">Meat</div>
                                                <div class="category-description">Quality cuts of chicken, beef, and
                                                    pork.</div>
                                            </article>
                                            </a>
                                            <! <a href="<?= BASE_URL ?>categories?filter=Baked%20Goods"
                                                class="category-link">
                                                <article class="category-item">
                                                    <div class="category-icon">
                                                        <img src="<?= BASE_URL ?>assets/images/categories/baked_goods_icon.png"
                                                            alt="Baked Goods">
                                                    </div>
                                                    <div class="category-title">Baked Goods</div>
                                                    <div class="category-description">Delicious bread, pastries, and
                                                        cookies.</div>
                                                </article>
                                                </a>
                                                </section>
                                                <!