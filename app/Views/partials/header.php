<?php
$logged_in = isset($_SESSION['user_id']);
?>
<! <! <header class="fixed-header">
    <! <div class="container">
        <! <div class="logo">
            <a href="<?= BASE_URL ?>">
                <! <img src="<?= BASE_URL ?>assets/images/Logo.png" alt="GhibliGroceries Logo" class="logo-image">
                    <span class="logo-text">GhibliGroceries</span>
            </a>
            </div>
            <! <nav>
                <ul>
                    <! <li><a href="<?= BASE_URL ?>"
                            class="<?php echo (isset($currentPath) && $currentPath === '/') ? 'active' : ''; ?>">Home</a>
                        </li>
                        <li><a href="<?= BASE_URL ?>categories"
                                class="<?php echo (isset($currentPath) && $currentPath === '/categories') ? 'active' : ''; ?>">Categories</a>
                        </li>
                        <li><a href="<?= BASE_URL ?>about"
                                class="<?php echo (isset($currentPath) && $currentPath === '/about') ? 'active' : ''; ?>">About</a>
                        </li>
                        <li><a href="<?= BASE_URL ?>contact"
                                class="<?php echo (isset($currentPath) && $currentPath === '/contact') ? 'active' : ''; ?>">Contact</a>
                        </li>
                </ul>
                </nav>
                <! <div class="header-actions">
                    <?php
                    $isAdmin = false;
                    if ($logged_in) {
                        try {
                            $db = \App\Core\Registry::get('database');
                            if ($db) {
                                $userModel = new \App\Models\User($db);
                                $user = $userModel->findById($_SESSION['user_id']);
                                if ($user && isset($user['role']) && $user['role'] === 'admin') {
                                    $isAdmin = true;
                                }
                            } else {
                                error_log("Database not found in Registry in header.php (actions)");
                            }
                        } catch (Exception $e) {
                            error_log("Error checking admin status in header.php (actions): " . $e->getMessage());
                        }
                    }
                    if ($isAdmin) {
                        echo '<a href="' . BASE_URL . 'admin/dashboard" class="sign-in-btn">Admin Panel</a>';
                    }
                    ?>
                    <! <div class="cart-icon">
                        <! <a href="<?php echo $logged_in ? BASE_URL . '/cart' : BASE_URL . '/login'; ?>"
                            class="nav-button sign-in-btn" id="cart-icon-link">
                            <span>Cart</span>
                            <?php
                            if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                            <img src="<?= BASE_URL ?>assets/images/cart/filled_shopping_cart.png" alt="Shopping Cart"
                                class="cart-image">
                            <?php else: ?>
                            <img src="<?= BASE_URL ?>assets/images/cart/empty_shopping_cart.png"
                                alt="Empty Shopping Cart" class="cart-image">
                            <?php endif; ?>
                            <! <span id="cart-count-badge"></span>
                                </a>
                                </div>
                                <?php
                                if ($logged_in): ?>
                                <! <a href="<?= BASE_URL ?>my-orders" class="sign-in-btn">My Orders</a>
                                    <a href="<?= BASE_URL ?>logout" class="sign-up-btn">Logout</a>
                                    <?php else: ?>
                                    <! <a href="<?= BASE_URL ?>login" class="sign-in-btn">Sign In</a>
                                        <a href="<?= BASE_URL ?>register" class="sign-up-btn">Sign Up</a>
                                        <?php endif; ?>
                                        </div>
                                        <! <! <! <! <div class="mobile-menu-toggle">
                                            <i class="fas fa-bars"></i>
                                            <! </div>
                                                </div>
                                                <! </header>
                                                    <! <! <! <div class="mobile-menu">
                                                        <nav>
                                                            <ul>
                                                                <! <li><a href="<?= BASE_URL ?>"
                                                                        class="<?php echo (isset($currentPath) && $currentPath === '/') ? 'active' : ''; ?>">Home</a>
                                                                    </li>
                                                                    <li><a href="<?= BASE_URL ?>categories"
                                                                            class="<?php echo (isset($currentPath) && $currentPath === '/categories') ? 'active' : ''; ?>">Categories</a>
                                                                    </li>
                                                                    <li><a href="<?= BASE_URL ?>about"
                                                                            class="<?php echo (isset($currentPath) && $currentPath === '/about') ? 'active' : ''; ?>">About</a>
                                                                    </li>
                                                                    <li><a href="<?= BASE_URL ?>contact"
                                                                            class="<?php echo (isset($currentPath) && $currentPath === '/contact') ? 'active' : ''; ?>">Contact</a>
                                                                    </li>
                                                                    <?php
                                                                    if ($logged_in): ?>
                                                                    <! <li>
                                                                        <a href="<?= BASE_URL ?>cart"
                                                                            class="mobile-cart-button">
                                                                            <?php
                                                                                if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                                                                            <img src="<?= BASE_URL ?>assets/images/cart/filled_shopping_cart.png"
                                                                                alt="Shopping Cart" class="cart-image">
                                                                            <?php else: ?>
                                                                            <img src="<?= BASE_URL ?>assets/images/cart/empty_shopping_cart.png"
                                                                                alt="Empty Shopping Cart"
                                                                                class="cart-image">
                                                                            <?php endif; ?>
                                                                            <span>Cart</span>
                                                                            <! </a>
                                                                                </li>
                                                                                <! <li><a
                                                                                        href="<?= BASE_URL ?>my-orders"
                                                                                        class="sign-in-btn">My
                                                                                        Orders</a></li>
                                                                                    <li><a
                                                                                            href="<?= BASE_URL ?>logout">Logout</a>
                                                                                    </li>
                                                                                    <?php else: ?>
                                                                                    <! <li><a
                                                                                            href="<?= BASE_URL ?>login">Sign
                                                                                            In</a></li>
                                                                                        <li><a
                                                                                                href="<?= BASE_URL ?>register">Sign
                                                                                                Up</a></li>
                                                                                        <?php endif; ?>
                                                            </ul>
                                                        </nav>
                                                        </div>
                                                        <!