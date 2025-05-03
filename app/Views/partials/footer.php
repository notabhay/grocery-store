<?php
$logged_in = isset($_SESSION['user_id']); 
?>
<!
<!
<div class="footer-toggle">
    <!
    <i class="fas fa-chevron-down" id="footer-toggle-icon"></i>
</div>
<!
<!
<footer id="site-footer" class="hidden">
    <!
    <div class="container">
        <!
        <div class="footer-content">
            <!
            <div class="footer-section">
                <h3>GhibliGroceries</h3>
                <p>Your one-stop shop for fresh vegetables and quality meat products.</p>
            </div>
            <!
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="<?= BASE_URL ?>">Home</a></li>
                    <li><a href="<?= BASE_URL ?>categories">Categories</a></li>
                    <li><a href="<?= BASE_URL ?>about">About</a></li>
                    <li><a href="<?= BASE_URL ?>contact">Contact</a></li>
                    <?php
                    if ($logged_in): ?>
                        <!
                        <li><a href="<?= BASE_URL ?>my-orders">My Orders</a></li>
                    <?php else: ?>
                        <!
                        <li><a href="<?= BASE_URL ?>register">Register</a></li>
                        <li><a href="<?= BASE_URL ?>login">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <!
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p><i class="fas fa-envelope"></i> contact@ghibligroceries.com</p>
                <p><i class="fas fa-phone"></i> (123) 456-7890</p>
            </div>
        </div> <!
        <!
        <div class="copyright">
            <p>&copy; <?php echo date('Y'); 
                        ?> GhibliGroceries. All rights reserved.</p>
        </div>
    </div> <!
</footer> <!