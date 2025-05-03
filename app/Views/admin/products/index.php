<! <div class="admin-content-header">
    <h2>Product Management</h2>
    <! <div class="admin-content-actions">
        <a href="<?= BASE_URL ?>admin/products/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Product
        </a>
        </div>
        </div>
        <?php
        ?>
        <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['flash_success']);
                ?>
        </div>
        <?php
            ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($_SESSION['flash_error']);
                ?>
        </div>
        <?php
            ?>
        <?php endif; ?>
        <! <div class="filter-container">
            <! <form action="<?= BASE_URL ?>admin/products" method="GET" class="filter-form">
                <! <div class="filter-group">
                    <label for="category_id">Category:</label>
                    <select name="category_id" id="category_id" class="form-control">
                        <option value="">All Categories</option>
                        <?php
                        ?>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>" <?php
                                                                                    ?>
                            <?php echo (isset($filters['category_id']) && $filters['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['category_name']);
                                ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    </div>
                    <! <div class="filter-group">
                        <label for="is_active">Status:</label>
                        <select name="is_active" id="is_active" class="form-control">
                            <option value="">All</option>
                            <option value="1" <?php
                                                ?>
                                <?php echo (isset($filters['is_active']) && $filters['is_active'] === 1) ? 'selected' : ''; ?>>
                                Active</option>
                            <option value="0" <?php
                                                ?>
                                <?php echo (isset($filters['is_active']) && $filters['is_active'] === 0) ? 'selected' : ''; ?>>
                                Inactive</option>
                        </select>
                        </div>
                        <! <div class="filter-group">
                            <button type="submit" class="btn btn-secondary">Filter</button>
                            <a href="<?= BASE_URL ?>admin/products" class="btn btn-outline-secondary">Reset</a>
                            </div>
                            </form>
                            </div>
                            <! <div class="table-responsive">
                                <table class="table table-striped">
                                    <! <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Image</th>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Stock</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                        </thead>
                                        <! <tbody>
                                            <?php
                                            ?>
                                            <?php if (empty($products)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center">No products found matching your
                                                    criteria.</td>
                                            </tr>
                                            <?php else: ?>
                                            <?php
                                                ?>
                                            <?php foreach ($products as $product): ?>
                                            <tr>
                                                <! <td><?php echo $product['product_id']; ?></td>
                                                    <! <td>
                                                        <img src="<?= BASE_URL ?><?php echo htmlspecialchars($product['image_path']);
                                                                                            ?>" alt="<?php echo htmlspecialchars($product['name']);
                                                                                                        ?>"
                                                            class="product-thumbnail" width="50" height="50">
                                                        </td>
                                                        <! <td><?php echo htmlspecialchars($product['name']);
                                                                        ?></td>
                                                            <! <td><?php echo htmlspecialchars($product['category_name']);
                                                                            ?></td>
                                                                <! <td>
                                                                    $<?php echo number_format($product['price'], 2); ?>
                                                                    </td>
                                                                    <! <td><?php echo $product['stock_quantity']; ?>
                                                                        </td>
                                                                        <! <td>
                                                                            <span class="badge <?php echo $product['is_active'] ? 'badge-success' : 'badge-danger';
                                                                                                        ?>">
                                                                                <?php echo $product['is_active'] ? 'Active' : 'Inactive';
                                                                                        ?>
                                                                            </span>
                                                                            </td>
                                                                            <! <td>
                                                                                <div class="btn-group">
                                                                                    <! <a
                                                                                        href="<?= BASE_URL ?>admin/products/<?php echo $product['product_id']; ?>/edit"
                                                                                        class="btn btn-sm btn-primary">
                                                                                        <i class="fas fa-edit"></i> Edit
                                                                                        </a>
                                                                                        <! <form
                                                                                            action="<?= BASE_URL ?>admin/products/<?php echo $product['product_id']; ?>/toggle-active"
                                                                                            method="POST"
                                                                                            class="d-inline">
                                                                                            <?php
                                                                                                    ?>
                                                                                            <input type="hidden"
                                                                                                name="csrf_token"
                                                                                                value="<?php echo $csrf_token; ?>">
                                                                                            <button type="submit"
                                                                                                class="btn btn-sm <?php echo $product['is_active'] ? 'btn-warning' : 'btn-success';
                                                                                                                            ?>">
                                                                                                <i
                                                                                                    class="fas <?php echo $product['is_active'] ? 'fa-ban' : 'fa-check';
                                                                                                                        ?>"></i>
                                                                                                <?php echo $product['is_active'] ? 'Deactivate' : 'Activate';
                                                                                                        ?>
                                                                                            </button>
                                                                                            </form>
                                                                                </div>
                                                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php endif;
                                            ?>
                                            </tbody>
                                </table>
                                </div>
                                <?php
                                ?>
                                <?php if (!empty($pagination) && $pagination['total_pages'] > 1): ?>
                                <! <div class="pagination-container">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination">
                                            <?php
                                                ?>
                                            <?php if ($pagination['has_previous']): ?>
                                            <li class="page-item">
                                                <?php
                                                        ?>
                                                <a class="page-link"
                                                    href="<?= BASE_URL ?>admin/products?page=<?php echo $pagination['current_page'] - 1; ?><?php echo isset($filters['category_id']) ? '&category_id=' . urlencode($filters['category_id']) : ''; ?><?php echo isset($filters['is_active']) ? '&is_active=' . urlencode($filters['is_active']) : ''; ?>"
                                                    aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            <?php
                                                ?>
                                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                            <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : '';
                                                                            ?>">
                                                <?php
                                                        ?>
                                                <a class="page-link"
                                                    href="<?= BASE_URL ?>admin/products?page=<?php echo $i; ?><?php echo isset($filters['category_id']) ? '&category_id=' . urlencode($filters['category_id']) : ''; ?><?php echo isset($filters['is_active']) ? '&is_active=' . urlencode($filters['is_active']) : ''; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                            <?php endfor; ?>
                                            <?php
                                                ?>
                                            <?php if ($pagination['has_next']): ?>
                                            <li class="page-item">
                                                <?php
                                                        ?>
                                                <a class="page-link"
                                                    href="<?= BASE_URL ?>admin/products?page=<?php echo $pagination['current_page'] + 1; ?><?php echo isset($filters['category_id']) ? '&category_id=' . urlencode($filters['category_id']) : ''; ?><?php echo isset($filters['is_active']) ? '&is_active=' . urlencode($filters['is_active']) : ''; ?>"
                                                    aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                    </div>
                                    <?php endif;
                                    ?>
                                    <! <style>
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
                                        .filter-container {
                                        background-color: #f8f9fa;
                                        padding: 1rem;
                                        border-radius: 4px;
                                        margin-bottom: 1.5rem;
                                        }
                                        .filter-form {
                                        display: flex;
                                        flex-wrap: wrap;
                                        gap: 1rem;
                                        align-items: flex-end;
                                        }
                                        .filter-group {
                                        display: flex;
                                        flex-direction: column;
                                        min-width: 200px;
                                        }
                                        .product-thumbnail {
                                        object-fit: cover;
                                        border-radius: 4px;
                                        }
                                        .badge {
                                        padding: 0.5em 0.75em;
                                        border-radius: 4px;
                                        font-weight: normal;
                                        font-size: 0.85em;
                                        }
                                        .badge-success {
                                        background-color: #28a745;
                                        color: white;
                                        }
                                        .badge-danger {
                                        background-color: #dc3545;
                                        color: white;
                                        }
                                        .pagination-container {
                                        display: flex;
                                        justify-content: center;
                                        margin-top: 1.5rem;
                                        }
                                        .btn-group {
                                        display: flex;
                                        gap: 0.5rem;
                                        }
                                        </style>