<?php
$categories = $categories ?? [];
$products = $products ?? [];
$logged_in = $logged_in ?? false;
$activeFilter = $activeFilter ?? null; 
?>
<!
<section class="page-header fixed-page-header">
    <div class="container">
        <h1>Product Categories</h1>
        <p>Browse our wide selection of fresh groceries by category</p>
    </div>
</section>
<!
<div class="products-wrapper">
    <!
    <aside class="filter-sidebar">
        <h3>Filter Products</h3>
        <!
        <div class="filter-group">
            <label for="main-category">Main Category</label>
            <select id="main-category" name="main_category">
                <option value="all">All Categories</option>
                <?php 
                ?>
                <?php if (!empty($categories)): ?>
                    <?php 
                    ?>
                    <?php foreach ($categories as $cat): ?>
                        <?php 
                        ?>
                        <?php $isSelected = ($activeFilter !== null && $cat['category_name'] === $activeFilter); ?>
                        <option value="<?php echo htmlspecialchars($cat['category_id']); ?>" <?php echo $isSelected ? 'selected' : ''; 
                                                                                                ?>>
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <!
        <div class="filter-group">
            <label for="sub-category">Sub Category / Products</label>
            <select id="sub-category" name="sub_category" disabled>
                <option value="all">Select Main Category First</option>
                <?php 
                ?>
            </select>
            <small>Selecting a main category will load products here.</small>
        </div>
    </aside>
    <!
    <section id="product-display-area" class="products-grid">
        <?php 
        ?>
        <?php if (!empty($products)): ?>
            <?php
            $total_products = count($products);
            $rows = ceil($total_products / 4); 
            for ($i = 0; $i < $rows; $i++):
                $start_index = $i * 4; 
                $end_index = min($start_index + 4, $total_products); 
            ?>
                <!
                <div class="products-row">
                    <?php 
                    ?>
                    <?php for ($j = $start_index; $j < $end_index; $j++):
                        $prod = $products[$j];
                        if (!isset($prod['product_id'], $prod['name'], $prod['price'], $prod['image_path']))
                            continue; 
                    ?>
                        <!
                        <article class="product-card">
                            <!
                            <a href="<?= BASE_URL ?>product/<?php echo htmlspecialchars($prod['product_id']); ?>"
                                class="product-link">
                                <!
                                <img src="<?= BASE_URL ?><?php echo htmlspecialchars($prod['image_path']); ?>"
                                    alt="<?php echo htmlspecialchars($prod['name']); ?>" class="product-image">
                                <!
                                <h4 class="product-name"><?php echo htmlspecialchars($prod['name']); ?></h4>
                                <!
                                <p class="product-price">$<?php echo number_format($prod['price'], 2); ?></p>
                            </a>
                            <?php 
                            ?>
                            <?php if ($logged_in): ?>
                                <!
                                <button class="add-to-cart-btn" data-product-id="<?php echo htmlspecialchars($prod['product_id']); ?>">
                                    Add to Cart
                                </button>
                            <?php else: ?>
                                <!
                                <a href="<?= BASE_URL ?>login" class="login-to-purchase-btn">
                                    Login to Purchase
                                </a>
                            <?php endif; ?>
                        </article>
                    <?php endfor; 
                    ?>
                </div> <!
            <?php endfor; 
            ?>
        <?php else: ?>
            <!
            <p>No products found.</p>
        <?php endif; 
        ?>
    </section> <!
</div> <!