<?php

use App\Core\Registry;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'GhibliGroceries'; ?></title>
    <!
    <meta name="description"
        content="<?php echo isset($meta_description) ? htmlspecialchars($meta_description) : 'Your one-stop shop for fresh groceries inspired by Studio Ghibli.'; ?>">
    <!
    <meta name="keywords"
        content="<?php echo isset($meta_keywords) ? htmlspecialchars($meta_keywords) : 'grocery, food, online shopping, delivery, ghibli'; ?>">
    <meta name="author" content="GhibliGroceries Team">

    <!
    <!
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/styles.css">

    <?php
    
    if (!empty($additional_css_files) && is_array($additional_css_files)):
        foreach ($additional_css_files as $css_file): ?>
    <link rel="stylesheet" href="<?= BASE_URL ?><?php echo htmlspecialchars($css_file); ?>">
    <?php endforeach;
    endif;
    ?>
</head>
<!

<body data-logged-in="<?php echo isset($logged_in) && $logged_in ? 'true' : 'false'; ?>">
    <!
    <div class="app-container">

        <?php
        
        
        
        $navPath = BASE_PATH . '/app/Views/partials/navigation.php';
        if (file_exists($navPath)) {
            require $navPath;
        } else {
            
            echo "<!
        }
        ?>

        <?php
        
        $headerPath = BASE_PATH . '/app/Views/partials/header.php';
        if (file_exists($headerPath)) {
            require $headerPath;
        } else {
            
            echo "<!
        }
        ?>

        <!
        <main class="main-content">
            <?php
            
            
            echo $content ?? '<p>Error: Page content not loaded.</p>';
            ?>
        </main> <!

        <?php
        
        $footerPath = BASE_PATH . '/app/Views/partials/footer.php';
        if (file_exists($footerPath)) {
            require $footerPath;
        } else {
            
            echo "<!
        }
        ?>

        <!
        <div id="toast-container"></div>

    </div> <!

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

    <!
    <!
</body>

</html>