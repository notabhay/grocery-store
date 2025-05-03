<?php
?>
<! the `<body>` of `default.php` as it appeared in the read content.
    Assuming this might be included differently or the structure in default.php needs review.
    For commenting purposes, we'll treat this as setting up the head content. -->
    <!DOCTYPE html>
    <! <html lang="en">
        <! <head>
            <! <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <?php
                if (isset($meta_description)): ?>
                <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
                <?php else: ?>
                <meta name="description"
                    content="Fresh groceries delivered to your door. Shop vegetables, meat, and more at our online grocery store.">
                <?php endif; ?>
                <?php
                if (isset($meta_keywords)): ?>
                <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>">
                <?php else: ?>
                <meta name="keywords" content="grocery, online shopping, vegetables, meat, fresh produce">
                <?php endif; ?>
                <! <title>
                    <?php echo isset($page_title) ? htmlspecialchars($page_title) : 'GhibliGroceries - Fresh Food Delivered'; ?>
                    </title>
                    <! <! <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/styles.css">
                        <! <link rel="preconnect" href="https://fonts.googleapis.com">
                            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap"
                                rel="stylesheet">
                            <! <link rel="stylesheet"
                                href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
                                <?php
                                if (isset($additional_styles)): ?>
                                <style>
                                <?php echo $additional_styles;
                                ?>
                                </style>
                                <?php endif; ?>
                                <! <! <script src="<?= BASE_URL ?>assets/js/script.js" defer>
                                    </script>
                                    <?php
                                    if (isset($additional_js_files) && is_array($additional_js_files)):
                                        foreach ($additional_js_files as $js_file): ?>
                                    <! <script src="<?= BASE_URL ?>assets/js/<?php echo htmlspecialchars($js_file); ?>"
                                        defer>
                                        </script>
                                        <?php endforeach;
                                    endif; ?>
                                        </head>
                                        <! <body>
                                            <! <! It might be part of an older structure or intended for a different
                                                inclusion method. -->
                                                <div class="main-panel">
                                                    <! but this partial seems focused on the <head> section. -->