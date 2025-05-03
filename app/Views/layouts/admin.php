<?php
use App\Core\Registry;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php
        echo isset($page_title) ? htmlspecialchars($page_title) . ' - Admin Panel' : 'GhibliGroceries Admin Panel';
        ?>
    </title>
    <!
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="GhibliGroceries Admin Panel">
    <!
    <!
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/styles.css">
    <!
    <style>
    :root {
        --admin-primary: #3498db;
        --admin-secondary: #2c3e50;
        --admin-accent: #e74c3c;
        --admin-bg: #f8f9fa;
        --admin-text: #333;
        --admin-sidebar-width: 250px;
    }
    body {
        background-color: var(--admin-bg);
        color: var(--admin-text);
        display: flex;
        min-height: 100vh;
        margin: 0;
        padding: 0;
    }
    .admin-container {
        display: flex;
        width: 100%;
        min-height: 100vh;
    }
    .admin-sidebar {
        width: var(--admin-sidebar-width);
        background-color: var(--admin-secondary);
        color: white;
        padding: 1rem 0;
        flex-shrink: 0;
    }
    .admin-sidebar-header {
        padding: 0 1rem 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 1rem;
    }
    .admin-sidebar-header h1 {
        font-size: 1.5rem;
        margin: 0;
    }
    .admin-sidebar-nav {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .admin-sidebar-nav li {
        padding: 0;
    }
    .admin-sidebar-nav a {
        display: block;
        padding: 0.75rem 1rem;
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .admin-sidebar-nav a:hover,
    .admin-sidebar-nav a.active {
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
    }
    .admin-sidebar-nav i {
        width: 20px;
        margin-right: 8px;
        text-align: center;
    }
    .admin-content {
        flex-grow: 1;
        padding: 1rem;
        overflow-y: auto;
    }
    .admin-header {
        background-color: white;
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .admin-header-title h1 {
        margin: 0;
        font-size: 1.5rem;
    }
    .admin-user-info {
        display: flex;
        align-items: center;
    }
    .admin-user-info span {
        margin-right: 1rem;
    }
    .admin-main {
        background-color: white;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
    }
    #toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }
    </style>
    <?php
    if (!empty($additional_css_files) && is_array($additional_css_files)):
        foreach ($additional_css_files as $css_file): ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($css_file); ?>">
    <?php endforeach;
    endif;
    ?>
</head>
<body>
    <!
    <div class="admin-container">
        <!
        <aside class="admin-sidebar">
            <!
            <div class="admin-sidebar-header">
                <h1>GhibliGroceries</h1>
                <p>Admin Panel</p>
            </div>
            <!
            <ul class="admin-sidebar-nav">
                <li>
                    <!
                    <a href="<?= BASE_URL ?>admin/dashboard"
                        class="<?php echo (isset($currentPath) && $currentPath === '/admin/dashboard') ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <!
                    <a href="<?= BASE_URL ?>admin/users"
                        class="<?php echo (isset($currentPath) && strpos($currentPath, '/admin/users') === 0) ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                <li>
                    <!
                    <a href="<?= BASE_URL ?>admin/orders"
                        class="<?php echo (isset($currentPath) && strpos($currentPath, '/admin/orders') === 0) ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-cart"></i> Orders
                    </a>
                </li>
                <li>
                    <!
                    <a href="<?= BASE_URL ?>admin/products"
                        class="<?php echo (isset($currentPath) && strpos($currentPath, '/admin/products') === 0) ? 'active' : ''; ?>">
                        <i class="fas fa-box"></i> Products
                    </a>
                </li>
                <li>
                    <!
                    <a href="<?= BASE_URL ?>admin/categories"
                        class="<?php echo (isset($currentPath) && strpos($currentPath, '/admin/categories') === 0) ? 'active' : ''; ?>">
                        <i class="fas fa-tags"></i> Categories
                    </a>
                </li>
                <li>
                    <!
                    <a href="<?= BASE_URL ?>" target="_blank">
                        <i class="fas fa-home"></i> View Store
                    </a>
                </li>
                <li>
                    <!
                    <a href="<?= BASE_URL ?>logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </aside> <!
        <!
        <div class="admin-content">
            <!
            <header class="admin-header">
                <!
                <div class="admin-header-title">
                    <!
                    <h1><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?></h1>
                </div>
                <!
                <div class="admin-user-info">
                    <?php
                    if (isset($admin_user) && is_array($admin_user)): ?>
                    <span>Welcome, <?php echo htmlspecialchars($admin_user['name']); ?></span>
                    <?php endif; ?>
                </div>
            </header> <!
            <!
            <main class="admin-main">
                <?php
                echo $content ?? '<p>Error: Page content not loaded.</p>';
                ?>
            </main> <!
        </div> <!
    </div> <!
    <!
    <div id="toast-container"></div>
    <!
    <div id="confirmation-modal" class="modal">
        <div class="modal-backdrop"></div>
        <div class="modal-content">
            <p id="modal-message"></p> <!
            <div class="modal-buttons">
                <button id="modal-confirm-button" class="modal-btn confirm-btn">Confirm</button>
                <button id="modal-cancel-button" class="modal-btn cancel-btn">Cancel</button>
            </div>
        </div>
    </div>
    <!
    <script>
    document.addEventListener('DOMContentLoaded', function() {
    });
    </script>
    <!
    <script>
    <?php
        $jsBaseUrl = BASE_URL; 
        $publicSuffix = '/public/';
        if (substr($jsBaseUrl, -strlen($publicSuffix)) === $publicSuffix) {
            $jsBaseUrl = substr($jsBaseUrl, 0, -strlen($publicSuffix)); 
        }
        $jsBaseUrl = rtrim($jsBaseUrl, '/') . '/';
        ?>
    window.baseUrl = '<?= $jsBaseUrl ?>';
    </script>
</body>
</html>