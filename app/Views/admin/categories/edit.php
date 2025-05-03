<! <div class="admin-content-header">
    <h2>Edit Category</h2>
    <! <div class="admin-content-actions">
        <a href="<?= BASE_URL ?>admin/categories" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Categories
        </a>
        </div>
        </div>
        <! <! <?php
                ?> <?php if (isset($_SESSION['flash_error'])): ?> <div class="alert alert-danger">
            <?php echo htmlspecialchars($_SESSION['flash_error']);
            ?>
            </div>
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
            <?php endif; ?>
            <! <! <div class="card">
                <div class="card-body">
                    <! <form action="<?= BASE_URL ?>admin/categories/<?php echo $category['id']; ?>" method="POST">
                        <! <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <! <div class="form-group mb-3">
                                <label for="name" class="form-label">Category Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($category['category_name']);
                                                                                                            ?>">
                                <small class="form-text text-muted">Enter a unique and descriptive name for the
                                    category.</small>
                </div>
                <! <! <div class="form-group mb-3">
                    <label for="parent_id" class="form-label">Parent Category</label>
                    <select class="form-control" id="parent_id" name="parent_id">
                        <! <option value="0">None (Top Level Category)</option>
                            <?php
                        ?>
                            <?php foreach ($categories as $parentCategory): ?>
                            <?php
                            ?>
                            <?php if ($parentCategory['id'] != $category['id']): ?>
                            <option value="<?php echo $parentCategory['id']; ?>" <?php
                                    echo ($category['parent_id'] == $parentCategory['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($parentCategory['category_name']);
                                    ?>
                            </option>
                            <?php endif; ?>
                            <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Select a parent category if this is a subcategory, or leave as
                        "None" for a top-level category.</small>
                    </div>
                    <! <! <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Category
                        </button>
                        <! <a href="<?= BASE_URL ?>admin/categories" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                            <! </form>
                                </div>
                                </div>
                                <! <! <?php
                                    ?> <?php if (!$has_products): ?> <! <div class="card mt-4">
                                    <div class="card-header bg-danger text-white">
                                        <h3 class="card-title h5 mb-0">Delete Category</h3>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">Warning: This action cannot be undone. Only categories with
                                            no associated products can be
                                            deleted.</p>
                                        <! <form
                                            action="<?= BASE_URL ?>admin/categories/<?php echo $category['id']; ?>/delete"
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.');">
                                            <! <input type="hidden" name="csrf_token"
                                                value="<?php echo $csrf_token; ?>">
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-trash"></i> Delete Category
                                                </button>
                                                </form>
                                    </div>
                                    </div>
                                    <! <?php else: ?> <! <div class="card mt-4">
                                        <div class="card-header bg-secondary text-white">
                                            <h3 class="card-title h5 mb-0">Delete Category</h3>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text">This category cannot be deleted because it has
                                                associated products. You must reassign or
                                                delete those products first.</p>
                                            <! <button type="button" class="btn btn-secondary" disabled>
                                                <i class="fas fa-trash"></i> Delete Category
                                                </button>
                                        </div>
                                        </div>
                                        <! <?php endif; ?> <! <! <style>
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
                                            .card {
                                            border-radius: 4px;
                                            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                                            }
                                            .card-body {
                                            padding: 1.5rem;
                                            }
                                            .card-header {
                                            padding: 1rem 1.5rem;
                                            }
                                            .form-label {
                                            font-weight: 500;
                                            }
                                            .text-danger {
                                            color: #dc3545;
                                            }
                                            .form-text {
                                            color: #6c757d;
                                            font-size: 0.875rem;
                                            }
                                            .mt-4 {
                                            margin-top: 1.5rem !important;
                                            }
                                            .bg-danger {
                                            background-color: #dc3545 !important;
                                            }
                                            .bg-secondary {
                                            background-color: #6c757d !important;
                                            }
                                            .text-white {
                                            color: white !important;
                                            }
                                            .h5 {
                                            font-size: 1.25rem;
                                            }
                                            .mb-0 {
                                            margin-bottom: 0 !important;
                                            }
                                            </style>