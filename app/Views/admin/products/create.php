<! <div class="admin-content-header">
    <h2>Add New Product</h2>
    <! <div class="admin-content-actions">
        <a href="<?= BASE_URL ?>admin/products" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
        </div>
        </div>
        <?php
        ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($_SESSION['flash_error']);
                ?>
        </div>
        <?php
            ?>
        <?php endif; ?>
        <?php
        ?>
        <?php if (isset($_SESSION['flash_errors']) && is_array($_SESSION['flash_errors'])): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($_SESSION['flash_errors'] as $error): ?>
                <li><?php echo htmlspecialchars($error);
                            ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
            ?>
        <?php endif; ?>
        <! <div class="card">
            <div class="card-body">
                <! <form action="<?= BASE_URL ?>admin/products" method="POST" enctype="multipart/form-data">
                    <! <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <! <div class="form-group">
                            <label for="name">Product Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" maxlength="100" required
                                value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
                                                                                                                            ?>">
                            <small class="form-text text-muted">Maximum 100 characters</small>
            </div>
            <! <div class="form-group">
                <label for="description">Description <span class="text-danger">*</span></label>
                <textarea id="description" name="description" class="form-control" rows="5" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';
                                                                                                        ?></textarea>
                </div>
                <! <div class="form-row">
                    <! <div class="form-group col-md-6">
                        <label for="price">Price ($) <span class="text-danger">*</span></label>
                        <input type="number" id="price" name="price" class="form-control" min="0.01" step="0.01"
                            required value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '';
                                    ?>">
                        </div>
                        <! <div class="form-group col-md-6">
                            <label for="stock_quantity">Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" min="0"
                                required value="<?php echo isset($_POST['stock_quantity']) ? htmlspecialchars($_POST['stock_quantity']) : '100';
                                        ?>">
                            </div>
                            </div>
                            <! <div class="form-group">
                                <label for="category_id">Category <span class="text-danger">*</span></label>
                                <select id="category_id" name="category_id" class="form-control" required>
                                    <option value="">Select a category</option>
                                    <?php
                                    ?>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>" <?php
                                                                                                ?>
                                        <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['category_name']);
                                            ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                </div>
                                <! <div class="form-group">
                                    <label for="image">Product Image <span class="text-danger">*</span></label>
                                    <div class="custom-file">
                                        <?php
                                        ?>
                                        <input type="file" class="custom-file-input" id="image" name="image"
                                            accept="image/jpeg,image/png,image/gif,image/webp" required>
                                        <label class="custom-file-label" for="image">Choose file</label>
                                    </div>
                                    <small class="form-text text-muted">Accepted formats: JPG, PNG, GIF, WEBP. Maximum
                                        size: 5MB.</small>
                                    </div>
                                    <! <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="is_active"
                                                name="is_active" value="1" checked <?php
                                                        ?>>
                                            <label class="custom-control-label" for="is_active">Active (available for
                                                purchase)</label>
                                        </div>
                                        </div>
                                        <! <div class="form-group mt-4">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Save Product
                                            </button>
                                            <a href="<?= BASE_URL ?>admin/products" class="btn btn-secondary">Cancel</a>
                                            </div>
                                            </form>
                                            </div>
                                            <! </div>
                                                <! <! <style>
                                                    body {
                                                    margin: 0;
                                                    padding: 0;
                                                    }
                                                    .admin-content-header {
                                                    display: flex;
                                                    justify-content: space-between;
                                                    align-items: center;
                                                    margin-bottom: 1.5rem;
                                                    }
                                                    .form-group {
                                                    margin-bottom: 1.5rem;
                                                    }
                                                    .form-row {
                                                    display: flex;
                                                    flex-wrap: wrap;
                                                    margin-right: -15px;
                                                    margin-left: -15px;
                                                    }
                                                    .form-row>.col,
                                                    .form-row>[class*="col-"] {
                                                    padding-right: 15px;
                                                    padding-left: 15px;
                                                    }
                                                    .col-md-6 {
                                                    flex: 0 0 50%;
                                                    max-width: 50%;
                                                    }
                                                    .custom-file {
                                                    position: relative;
                                                    display: inline-block;
                                                    width: 100%;
                                                    height: calc(1.5em + 0.75rem + 2px);
                                                    margin-bottom: 0;
                                                    }
                                                    .custom-file-input {
                                                    position: relative;
                                                    z-index: 2;
                                                    width: 100%;
                                                    height: calc(1.5em + 0.75rem + 2px);
                                                    margin: 0;
                                                    opacity: 0;
                                                    }
                                                    .custom-file-label {
                                                    position: absolute;
                                                    top: 0;
                                                    right: 0;
                                                    left: 0;
                                                    z-index: 1;
                                                    height: calc(1.5em + 0.75rem + 2px);
                                                    padding: 0.375rem 0.75rem;
                                                    font-weight: 400;
                                                    line-height: 1.5;
                                                    color: #495057;
                                                    background-color: #fff;
                                                    border: 1px solid #ced4da;
                                                    border-radius: 0.25rem;
                                                    overflow: hidden;
                                                    white-space: nowrap;
                                                    }
                                                    .custom-file-label::after {
                                                    position: absolute;
                                                    top: 0;
                                                    right: 0;
                                                    bottom: 0;
                                                    z-index: 3;
                                                    display: block;
                                                    height: calc(1.5em + 0.75rem);
                                                    padding: 0.375rem 0.75rem;
                                                    line-height: 1.5;
                                                    color: #495057;
                                                    content: "Browse";
                                                    background-color: #e9ecef;
                                                    border-left: inherit;
                                                    border-radius: 0 0.25rem 0.25rem 0;
                                                    }
                                                    .custom-switch {
                                                    padding-left: 2.25rem;
                                                    }
                                                    .custom-control {
                                                    position: relative;
                                                    display: block;
                                                    min-height: 1.5rem;
                                                    padding-left: 1.5rem;
                                                    }
                                                    .custom-control-input {
                                                    position: absolute;
                                                    z-index: -1;
                                                    opacity: 0;
                                                    }
                                                    .custom-control-label {
                                                    position: relative;
                                                    margin-bottom: 0;
                                                    vertical-align: top;
                                                    }
                                                    .custom-control-label::before {
                                                    position: absolute;
                                                    top: 0.25rem;
                                                    left: -1.5rem;
                                                    display: block;
                                                    width: 1rem;
                                                    height: 1rem;
                                                    pointer-events: none;
                                                    content: "";
                                                    background-color: #fff;
                                                    border: 1px solid #adb5bd;
                                                    }
                                                    .custom-switch .custom-control-label::before {
                                                    left: -2.25rem;
                                                    width: 1.75rem;
                                                    border-radius: 0.5rem;
                                                    }
                                                    .custom-switch
                                                    .custom-control-input:checked~.custom-control-label::after {
                                                    background-color: #fff;
                                                    transform: translateX(0.75rem);
                                                    }
                                                    .custom-switch .custom-control-label::after {
                                                    top: calc(0.25rem + 2px);
                                                    left: calc(-2.25rem + 2px);
                                                    width: calc(1rem - 4px);
                                                    height: calc(1rem - 4px);
                                                    background-color: #adb5bd;
                                                    border-radius: 0.5rem;
                                                    transition: transform 0.15s ease-in-out, background-color 0.15s
                                                    ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s
                                                    ease-in-out;
                                                    }
                                                    .custom-control-label::after {
                                                    position: absolute;
                                                    top: 0.25rem;
                                                    left: -1.5rem;
                                                    display: block;
                                                    width: 1rem;
                                                    height: 1rem;
                                                    content: "";
                                                    background: no-repeat 50% / 50% 50%;
                                                    }
                                                    </style>
                                                    <! <script>
                                                        const fileInput = document.querySelector('.custom-file-input');
                                                        if (fileInput) {
                                                        fileInput.addEventListener('change', function(e) {
                                                        const fileName = e.target.files[0] ? e.target.files[0].name :
                                                        'Choose file';
                                                        const label = e.target.nextElementSibling;
                                                        if (label) {
                                                        label.textContent = fileName;
                                                        }
                                                        });
                                                        }
                                                        </script>