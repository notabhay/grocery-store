<! <div class="admin-content-header">
    <h2>Category Management</h2>
    <! <div class="admin-content-actions">
        <a href="<?= BASE_URL ?>admin/categories/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Category
        </a>
        </div>
        </div>
        <! <! <?php
                ?> <?php if (isset($_SESSION['flash_success'])): ?> <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['flash_success']);
            ?>
            </div>
            <?php endif; ?>
            <?php
        ?>
            <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['flash_error']);
                ?>
            </div>
            <?php endif; ?>
            <! <! <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Parent Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                    ?>
                        <?php if (empty($categories)): ?>
                        <! <tr>
                            <td colspan="4" class="text-center">No categories found.</td>
                            </tr>
                            <?php else: ?>
                            <?php
                            ?>
                            <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo $category['id'];
                                        ?></td>
                                <td><?php echo htmlspecialchars($category['category_name']);
                                        ?></td>
                                <td>
                                    <?php
                                        ?>
                                    <?php if (!empty($category['parent_name'])): ?>
                                    <?php echo htmlspecialchars($category['parent_name']);
                                            ?>
                                    <?php else: ?>
                                    <span class="text-muted">None</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <! <div class="btn-group">
                                        <! <a href="<?= BASE_URL ?>admin/categories/<?php echo $category['id']; ?>/edit"
                                            class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <! </div>
                                                <! </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                    </tbody>
                </table>
                </div>
                <! <! <?php
                    ?> <?php if (!empty($pagination) && $pagination['total_pages'] > 1): ?> <div
                    class="pagination-container">
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <?php
                        ?>
                            <?php if ($pagination['has_previous']): ?>
                            <li class="page-item">
                                <a class="page-link"
                                    href="<?= BASE_URL ?>admin/categories?page=<?php echo $pagination['current_page'] - 1; ?>"
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
                                <a class="page-link" href="<?= BASE_URL ?>admin/categories?page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            <?php
                        ?>
                            <?php if ($pagination['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link"
                                    href="<?= BASE_URL ?>admin/categories?page=<?php echo $pagination['current_page'] + 1; ?>"
                                    aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    </div>
                    <?php endif; ?>
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
                        .pagination-container {
                        display: flex;
                        justify-content: center;
                        margin-top: 1.5rem;
                        }
                        .btn-group {
                        display: flex;
                        gap: 0.5rem;
                        }
                        .text-muted {
                        color: #6c757d;
                        }
                        </style>