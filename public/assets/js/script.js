document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.mobile-menu');
    if (menuToggle && navMenu) { 
        menuToggle.addEventListener('click', function () {
            navMenu.classList.toggle('show');
        });
    } else {
        if (!menuToggle) console.warn("Mobile menu toggle button not found.");
        if (!navMenu) console.warn("Mobile navigation menu element not found.");
    }
    const footerToggle = document.querySelector('.footer-toggle');
    const footerToggleIcon = document.getElementById('footer-toggle-icon');
    const footer = document.getElementById('site-footer');
    if (footerToggle && footer && footerToggleIcon) { 
        footerToggle.addEventListener('click', function () {
            footer.classList.toggle('hidden');
            footerToggleIcon.classList.toggle('rotate');
            if (!footer.classList.contains('hidden')) {
                setTimeout(function () {
                    try {
                        window.scrollTo({
                            top: document.body.scrollHeight,
                            behavior: 'smooth'
                        });
                    } catch (e) {
                        window.scrollTo(0, document.body.scrollHeight);
                    }
                }, 500); 
            }
        });
    } else {
        if (!footerToggle) console.warn("Footer toggle button not found.");
        if (!footerToggleIcon) console.warn("Footer toggle icon not found.");
        if (!footer) console.warn("Site footer element not found.");
    }
    let currentModalConfirmCallback = null; 
    const modalConfirmButton = document.getElementById('modal-confirm-button');
    const modalCancelButton = document.getElementById('modal-cancel-button');
    const confirmationModal = document.getElementById('confirmation-modal');
    const modalMessage = document.getElementById('modal-message'); 
    if (modalConfirmButton && confirmationModal) {
        modalConfirmButton.addEventListener('click', () => {
            console.log('Persistent Confirm Listener Fired.');
            if (typeof currentModalConfirmCallback === 'function') {
                console.log('Executing stored callback.');
                try {
                    currentModalConfirmCallback();
                } catch (error) {
                    console.error("Error executing modal confirm callback:", error);
                    showToast("An error occurred during the confirmation action.", "error");
                }
            } else {
                console.log('No callback stored or callback is not a function.');
            }
            currentModalConfirmCallback = null;
            confirmationModal.classList.remove('modal-visible');
        });
    } else {
        if (!modalConfirmButton) console.warn("Modal confirm button (#modal-confirm-button) not found for persistent listener setup.");
        if (!confirmationModal) console.warn("Confirmation modal (#confirmation-modal) not found for persistent listener setup.");
    }
    if (modalCancelButton && confirmationModal) {
        modalCancelButton.addEventListener('click', () => {
            console.log('Persistent Cancel Listener Fired.');
            currentModalConfirmCallback = null;
            confirmationModal.classList.remove('modal-visible');
        });
    } else {
        if (!modalCancelButton) console.warn("Modal cancel button (#modal-cancel-button) not found for persistent listener setup.");
        if (!confirmationModal) console.warn("Confirmation modal (#confirmation-modal) not found for persistent listener setup.");
    }
    function showConfirmationModal(message, confirmCallback) {
        console.log('Showing confirmation modal. Message:', message);
        if (confirmationModal && modalMessage) {
            modalMessage.textContent = message; 
            currentModalConfirmCallback = confirmCallback; 
            console.log('Callback stored. Type:', typeof currentModalConfirmCallback);
            confirmationModal.classList.add('modal-visible'); 
            console.log('Added "modal-visible" class. Modal classList:', confirmationModal.classList);
        } else {
            console.error('Modal (#confirmation-modal) or message element (#modal-message) not found in showConfirmationModal.');
            if (!confirmationModal) console.error("Confirmation modal element is missing.");
            if (!modalMessage) console.error("Modal message element is missing.");
        }
    }
    const mainCategorySelect = document.getElementById('main-category');
    const subCategorySelect = document.getElementById('sub-category');
    const productDisplayArea = document.getElementById('product-display-area');
    function resetSubCategoryDropdown() {
        if (subCategorySelect) {
            subCategorySelect.innerHTML = '<option value="">
            subCategorySelect.disabled = true;
        }
    }
    resetSubCategoryDropdown();
    if (mainCategorySelect) {
        mainCategorySelect.addEventListener('change', function () {
            const categoryId = this.value; 
            resetSubCategoryDropdown(); 
            if (productDisplayArea) {
                productDisplayArea.innerHTML = '<p>Loading products...</p>'; 
                if (categoryId === 'all' || !categoryId) {
                    productDisplayArea.innerHTML = '<p>Select a specific category to view products.</p>';
                    return; 
                }
            } else {
                if (categoryId === 'all' || !categoryId) {
                    return; 
                }
            }
            fetchAndPopulateSubcategories(categoryId);
            if (productDisplayArea) {
                fetch(`${window.baseUrl}/ajax/products-by-category?categoryId=${encodeURIComponent(categoryId)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Product fetch HTTP error! status: ${response.status}`);
                        }
                        const contentType = response.headers.get("content-type");
                        if (contentType && contentType.indexOf("application/json") !== -1) {
                            return response.json();
                        } else {
                            return response.text().then(text => { throw new Error("Expected JSON for products, got: " + text); });
                        }
                    })
                    .then(data => {
                        if (data.error) {
                            console.error('Server error loading products:', data.error);
                            productDisplayArea.innerHTML = `<p>Error loading products: ${escapeHTML(data.error)}</p>`;
                        } else if (data.products && Array.isArray(data.products)) {
                            renderProducts(data.products);
                        } else {
                            console.error('Unexpected product data format:', data);
                            productDisplayArea.innerHTML = '<p>Could not load products. Unexpected data format received.</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Fetch products error:', error);
                        productDisplayArea.innerHTML = `<p>Error loading products. Please check connection. ${escapeHTML(error.message)}</p>`;
                    });
            }
        });
    }
    if (subCategorySelect) {
        subCategorySelect.addEventListener('change', function () {
            const subCategoryId = this.value; 
            if (productDisplayArea) {
                productDisplayArea.innerHTML = '<p>Loading products...</p>'; 
                if (subCategoryId && subCategoryId !== "") {
                    fetch(`${window.baseUrl}/ajax/products-by-category?categoryId=${encodeURIComponent(subCategoryId)}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`Sub-category Product fetch HTTP error! status: ${response.status}`);
                            }
                            const contentType = response.headers.get("content-type");
                            if (contentType && contentType.indexOf("application/json") !== -1) {
                                return response.json();
                            } else {
                                return response.text().then(text => { throw new Error("Expected JSON for sub-category products, got: " + text); });
                            }
                        })
                        .then(data => {
                            if (data.error) {
                                console.error('Server error loading sub-category products:', data.error);
                                productDisplayArea.innerHTML = `<p>Error loading products: ${escapeHTML(data.error)}</p>`;
                            } else if (data.products && Array.isArray(data.products)) {
                                renderProducts(data.products);
                            } else {
                                console.error('Unexpected sub-category product data format:', data);
                                productDisplayArea.innerHTML = '<p>Could not load products. Unexpected data format received.</p>';
                            }
                        })
                        .catch(error => {
                            console.error('Fetch sub-category products error:', error);
                            productDisplayArea.innerHTML = `<p>Error loading products. Please check connection. ${escapeHTML(error.message)}</p>`;
                        });
                } else {
                    productDisplayArea.innerHTML = '<p>Select a sub-category to view products.</p>';
                }
            }
        });
    }
    function fetchAndPopulateSubcategories(categoryId) {
        if (!subCategorySelect) {
            return;
        }
        resetSubCategoryDropdown();
        if (!categoryId || categoryId === 'all') {
            return;
        }
        fetch(`${window.baseUrl}/ajax/subcategories?parentId=${encodeURIComponent(categoryId)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Subcategory fetch HTTP error! status: ${response.status}`);
                }
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    return response.json();
                } else {
                    return response.text().then(text => { throw new Error("Expected JSON for subcategories, got: " + text); });
                }
            })
            .then(data => {
                if (data.error) {
                    console.error('Server error fetching subcategories:', data.error);
                    subCategorySelect.innerHTML = '<option value="">
                } else if (data.subcategories && Array.isArray(data.subcategories)) {
                    if (data.subcategories.length > 0) {
                        data.subcategories.forEach(subCat => {
                            if (subCat && subCat.category_id && subCat.category_name) {
                                const option = document.createElement('option');
                                option.value = subCat.category_id;
                                option.textContent = subCat.category_name;
                                subCategorySelect.appendChild(option);
                            } else {
                                console.warn('Skipping invalid subcategory data:', subCat);
                            }
                        });
                        subCategorySelect.disabled = false; 
                    } else {
                        subCategorySelect.innerHTML = '<option value="">
                    }
                } else {
                    console.error('Unexpected data format for subcategories:', data);
                    subCategorySelect.innerHTML = '<option value="">
                }
            })
            .catch(error => {
                console.error('Fetch subcategories error:', error);
                subCategorySelect.innerHTML = `<option value="">
            });
    }
    function renderProducts(products) {
        if (!productDisplayArea) {
            console.warn("Attempted to render products, but #product-display-area not found.");
            return;
        }
        if (!products || products.length === 0) {
            productDisplayArea.innerHTML = '<p>No products found in this category.</p>';
            return;
        }
        let html = '';
        const productsPerRow = 4; 
        for (let i = 0; i < products.length; i += productsPerRow) {
            html += '<div class="products-row">'; 
            const rowProducts = products.slice(i, i + productsPerRow); 
            rowProducts.forEach(prod => {
                if (!prod || typeof prod !== 'object' || !prod.product_id || typeof prod.name === 'undefined' || typeof prod.price === 'undefined' || typeof prod.image_path === 'undefined') {
                    console.warn('Skipping invalid product data:', prod);
                    return; 
                }
                const isLoggedIn = document.body.getAttribute('data-logged-in') === 'true';
                const price = parseFloat(prod.price);
                const formattedPrice = !isNaN(price) ? price.toFixed(2) : 'N/A'; 
                html += `
                    <div class="product-card">
                        <a href="${window.baseUrl}product/${escapeHTML(prod.product_id)}" class="product-link">
                            <img src="${window.baseUrl}/${escapeHTML(prod.image_path)}" alt="${escapeHTML(prod.name)}" class="product-image">
                            <h4 class="product-name">${escapeHTML(prod.name)}</h4>
                            <p class="product-price">$${formattedPrice}</p>
                        </a>
                        ${isLoggedIn
                        ? `<button class="add-to-cart-btn" data-product-id="${escapeHTML(prod.product_id)}">Add to Cart</button>` 
                        : `<a href="${window.baseUrl}/login" class="login-to-purchase-btn">Login to Purchase</a>` 
                    }
                    </div>
                `;
            });
            html += '</div>'; 
        }
        productDisplayArea.innerHTML = html;
    }
    function escapeHTML(str) {
        if (str === null || str === undefined) return '';
        if (typeof str !== 'string' && typeof str !== 'number' && typeof str !== 'boolean') {
            console.warn("Attempted to escape non-primitive value:", str);
            str = String(str);
        }
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(String(str))); 
        return div.innerHTML;
    }
    function showToast(message, type = 'success', duration = 3000) {
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            console.error('Toast container (#toast-container) not found');
            return;
        }
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`; 
        let iconClass = 'fa-check-circle'; 
        if (type === 'error') {
            iconClass = 'fa-exclamation-circle';
        } else if (type === 'info') {
            iconClass = 'fa-info-circle';
        }
        toast.innerHTML = `
            <div class="toast-icon">
                <i class="fas ${iconClass}"></i>
            </div>
            <div class="toast-content">
                ${escapeHTML(message)}
            </div>
        `;
        toastContainer.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        }, 10);
        setTimeout(() => {
            toast.classList.add('toast-exit'); 
            setTimeout(() => {
                if (toast.parentNode) { 
                    toast.parentNode.removeChild(toast);
                }
            }, 300); 
        }, duration);
    }
    document.addEventListener('click', function (event) {
        if (event.target.matches('.add-to-cart-btn')) {
            event.preventDefault(); 
            const button = event.target;
            const productId = button.getAttribute('data-product-id');
            if (!productId) {
                console.error('Product ID not found on button.');
                showToast('Could not add item: Product ID missing.', 'error');
                return;
            }
            button.textContent = 'Adding...';
            button.disabled = true;
            const data = {
                product_id: productId,
                quantity: 1 
            };
            fetch(`${window.baseUrl}/api/cart/add`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json' 
                },
                body: JSON.stringify(data)
            })
                .then(response => {
                    const contentType = response.headers.get("content-type");
                    if (response.ok && contentType && contentType.indexOf("application/json") !== -1) {
                        return response.json(); 
                    } else if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`Add to cart failed: ${response.status} ${response.statusText}. ${text}`);
                        });
                    } else {
                        return response.text().then(text => { throw new Error("Expected JSON response from add to cart, got non-JSON: " + text); });
                    }
                })
                .then(data => {
                    button.textContent = 'Add to Cart';
                    button.disabled = false;
                    if (data.success) {
                        showToast(data.message || 'Item added to cart successfully!', 'success');
                        updateCartIcon(true); 
                        updateCartBadge(); 
                    } else {
                        showToast('Error: ' + (data.message || 'Could not add item to cart.'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Add to Cart Fetch error:', error);
                    button.textContent = 'Add to Cart';
                    button.disabled = false;
                    showToast(`An error occurred: ${error.message}. Please try again.`, 'error');
                });
        }
    });
    const cartContainer = document.querySelector('.cart-container');
    if (cartContainer) {
        cartContainer.addEventListener('change', function (event) {
            if (event.target.matches('.quantity-input')) {
                const input = event.target;
                const productId = input.getAttribute('data-product-id');
                let newQuantity = parseInt(input.value, 10);
                if (isNaN(newQuantity)) {
                    newQuantity = 1; 
                    input.value = 1;
                    showToast('Quantity must be a valid number', 'info');
                } else if (newQuantity > 99) {
                    newQuantity = 99; 
                    input.value = 99;
                    showToast('Maximum quantity is 99', 'info');
                }
                const previousValue = parseInt(input.getAttribute('data-previous-value') || input.defaultValue, 10);
                if (newQuantity !== previousValue) {
                    if (newQuantity <= 0) {
                        showConfirmationModal(
                            'Are you sure you want to remove this item from your cart?',
                            function () {
                                removeCartItem(productId); 
                            }
                        );
                        input.value = previousValue;
                    } else {
                        updateCartItemQuantity(productId, newQuantity);
                        input.setAttribute('data-previous-value', newQuantity.toString());
                    }
                }
            }
        });
        cartContainer.addEventListener('click', function (event) {
            if (event.target.matches('.increase-btn')) {
                event.preventDefault();
                const productId = event.target.getAttribute('data-product-id');
                const quantityInput = event.target.parentElement.querySelector('.quantity-input');
                let currentQuantity = parseInt(quantityInput.value, 10);
                if (isNaN(currentQuantity)) currentQuantity = 1; 
                const newQuantity = Math.min(99, currentQuantity + 1); 
                quantityInput.value = newQuantity; 
                updateCartItemQuantity(productId, newQuantity); 
            }
            if (event.target.matches('.decrease-btn')) {
                event.preventDefault();
                const productId = event.target.getAttribute('data-product-id');
                const quantityInput = event.target.parentElement.querySelector('.quantity-input');
                let currentQuantity = parseInt(quantityInput.value, 10);
                if (isNaN(currentQuantity)) currentQuantity = 1; 
                const newQuantity = Math.max(0, currentQuantity - 1); 
                if (newQuantity > 0) {
                    quantityInput.value = newQuantity;
                    updateCartItemQuantity(productId, newQuantity);
                } else {
                    console.log('Quantity reached zero, showing confirmation modal.');
                    showConfirmationModal(
                        'Are you sure you want to remove this item from your cart?',
                        function () {
                            console.log('*** Confirm Callback Entered for product ID (decrease):', productId, '***');
                            const allButtons = document.querySelectorAll('button');
                            allButtons.forEach(btn => { btn.disabled = true; });
                            const deleteUrl = `${window.baseUrl}/api/cart/item/${productId}`;
                            console.log('Making DELETE request from decrease-to-zero callback to:', deleteUrl);
                            fetch(deleteUrl, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({}) 
                            })
                                .then(response => {
                                    console.log('Received response from DELETE request. Status:', response.status);
                                    const contentType = response.headers.get("content-type");
                                    if (response.ok && contentType && contentType.indexOf("application/json") !== -1) {
                                        return response.json();
                                    } else if (!response.ok) {
                                        return response.text().then(text => {
                                            throw new Error(`Remove item failed: ${response.status} ${response.statusText}. ${text}`);
                                        });
                                    } else {
                                        return response.text().then(text => {
                                            throw new Error("Expected JSON response from remove item, got non-JSON: " + text);
                                        });
                                    }
                                })
                                .then(data => {
                                    console.log('Parsed response data:', data);
                                    if (data.success) {
                                        console.log('Attempting to update UI after deletion...');
                                        const itemRow = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
                                        if (itemRow) itemRow.remove();
                                        const cartTotalElement = document.getElementById('cart-total-price');
                                        if (cartTotalElement && data.total_price !== undefined) {
                                            const totalPrice = parseFloat(data.total_price);
                                            cartTotalElement.textContent = !isNaN(totalPrice) ? `$${totalPrice.toFixed(2)}` : '$--.--';
                                        } else if (cartTotalElement) {
                                            cartTotalElement.textContent = '$0.00'; 
                                        }
                                        if (data.is_empty) {
                                            updateCartUI({ is_empty: true, total_items: 0, total_price: 0 });
                                        } else {
                                            updateCartIcon(!data.is_empty);
                                            updateCartBadge();
                                        }
                                        showToast('Item removed from cart.', 'success');
                                    } else {
                                        showToast('Error: ' + (data.message || 'Could not remove item from cart.'), 'error');
                                    }
                                    allButtons.forEach(btn => { btn.disabled = false; });
                                })
                                .catch(error => {
                                    console.error('Error details during delete fetch operation (decrease-to-zero):', error);
                                    showToast(`An error occurred: ${error.message}. Please try again.`, 'error');
                                    allButtons.forEach(btn => { btn.disabled = false; });
                                });
                        } 
                    ); 
                } 
            } 
            if (event.target.matches('.remove-item-btn') || event.target.closest('.remove-item-btn')) {
                event.preventDefault();
                const button = event.target.matches('.remove-item-btn') ? event.target : event.target.closest('.remove-item-btn');
                const productId = button.getAttribute('data-product-id');
                console.log('Delete button clicked for product ID:', productId);
                if (!productId) {
                    console.error('Product ID not found on remove button.');
                    showToast('Could not remove item: Product ID missing.', 'error');
                    return;
                }
                showConfirmationModal(
                    'Are you sure you want to remove this item from your cart?',
                    function () {
                        console.log('*** Confirm Callback Entered for product ID:', productId, '***');
                        removeCartItem(productId); 
                    }
                );
            }
            if (event.target.matches('#clear-cart-btn')) {
                event.preventDefault();
                console.log('Clear Cart button clicked.');
                const confirmCallback = () => {
                    console.log('*** Clear Cart Confirm Callback Entered ***');
                    console.log('Making POST request to clear cart...');
                    const allButtons = document.querySelectorAll('button');
                    allButtons.forEach(btn => { btn.disabled = true; });
                    fetch(`${window.baseUrl}/api/cart/clear`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({}) 
                    })
                        .then(response => {
                            console.log('Received response from clear cart request. Status:', response.status);
                            const contentType = response.headers.get("content-type");
                            if (response.ok && contentType && contentType.indexOf("application/json") !== -1) {
                                return response.json();
                            } else if (!response.ok) {
                                return response.text().then(text => {
                                    throw new Error(`Clear cart failed: ${response.status} ${response.statusText}. ${text}`);
                                });
                            } else {
                                return response.text().then(text => {
                                    throw new Error("Expected JSON response from clear cart, got non-JSON: " + text);
                                });
                            }
                        })
                        .then(data => {
                            console.log('Parsed clear cart response data:', data);
                            if (data.success) {
                                console.log('Attempting to update UI after clearing cart...');
                                updateCartUI({
                                    is_empty: true,
                                    total_items: 0,
                                    total_price: 0
                                });
                                showToast('Cart cleared.', 'success');
                                updateCartIcon(false); 
                                updateCartBadge(); 
                            } else {
                                showToast('Error: ' + (data.message || 'Could not clear cart.'), 'error');
                                allButtons.forEach(btn => { btn.disabled = false; });
                            }
                        })
                        .catch(error => {
                            console.error('Error during clear cart fetch operation:', error);
                            showToast(`An error occurred: ${error.message}. Please try again.`, 'error');
                            allButtons.forEach(btn => { btn.disabled = false; });
                        });
                }; 
                showConfirmationModal('Are you sure you want to clear your entire cart?', confirmCallback);
            } 
        }); 
    } 
    function updateCartItemQuantity(productId, newQuantity) {
        if (!productId) {
            console.error('Product ID not found for quantity update.');
            return;
        }
        const quantityButtons = document.querySelectorAll('.quantity-btn');
        const quantityInputs = document.querySelectorAll('.quantity-input');
        quantityButtons.forEach(btn => { btn.disabled = true; });
        quantityInputs.forEach(input => { input.disabled = true; });
        const data = {
            product_id: productId,
            quantity: newQuantity
        };
        fetch(window.baseUrl + 'api/cart/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
            .then(response => {
                const contentType = response.headers.get("content-type");
                if (response.ok && contentType && contentType.indexOf("application/json") !== -1) {
                    return response.json();
                } else if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Update cart failed: ${response.status} ${response.statusText}. ${text}`);
                    });
                } else {
                    return response.text().then(text => {
                        throw new Error("Expected JSON response from update cart, got non-JSON: " + text);
                    });
                }
            })
            .then(data => {
                if (data.success) {
                    updateCartUI(data);
                    if (data.updated_product) {
                        if (data.updated_product.new_quantity <= 0) {
                            showToast('Item removed from cart.', 'success');
                        } else {
                            showToast('Cart updated successfully.', 'success');
                        }
                    }
                } else {
                    showToast('Error: ' + (data.message || 'Could not update cart.'), 'error');
                    quantityButtons.forEach(btn => { btn.disabled = false; });
                    quantityInputs.forEach(input => { input.disabled = false; });
                }
            })
            .catch(error => {
                console.error('Update Cart Fetch error:', error);
                showToast(`An error occurred: ${error.message}. Please try again.`, 'error');
                quantityButtons.forEach(btn => { btn.disabled = false; });
                quantityInputs.forEach(input => { input.disabled = false; });
            });
    }
    function updateCartUI(data) {
        updateCartIcon(data.total_items > 0);
        updateCartBadge(); 
        if (data.is_empty) {
            const cartContainer = document.querySelector('.cart-container');
            if (cartContainer) {
                cartContainer.innerHTML = `
                    <h1>Your Shopping Cart</h1>
                    <div class="empty-cart">
                        <img src="${window.baseUrl}/assets/images/cart/empty_shopping_cart.png" alt="Empty Shopping Cart" class="empty-cart-image">
                        <p>Your shopping cart is empty.</p>
                        <a href="${window.baseUrl}/categories" class="continue-shopping-btn">Continue Shopping</a>
                    </div>
                `;
            }
            return; 
        }
        const updatedProduct = data.updated_product;
        if (updatedProduct) {
            const productId = updatedProduct.product_id;
            const itemRow = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
            if (updatedProduct.new_quantity <= 0 && itemRow) {
                itemRow.remove();
            }
            else if (itemRow) {
                const quantityInput = itemRow.querySelector('.quantity-input');
                const totalElement = itemRow.querySelector('.product-total');
                if (quantityInput) {
                    quantityInput.value = updatedProduct.new_quantity;
                    quantityInput.setAttribute('data-previous-value', updatedProduct.new_quantity.toString());
                }
                if (totalElement && updatedProduct.new_total !== undefined) {
                    totalElement.textContent = `$${updatedProduct.new_total.toFixed(2)}`;
                }
            }
        }
        const cartTotalElement = document.getElementById('cart-total-price');
        if (cartTotalElement && data.total_price !== undefined) {
            cartTotalElement.textContent = `$${data.total_price.toFixed(2)}`;
        }
        const quantityButtons = document.querySelectorAll('.quantity-btn');
        const quantityInputs = document.querySelectorAll('.quantity-input');
        quantityButtons.forEach(btn => { btn.disabled = false; });
        quantityInputs.forEach(input => { input.disabled = false; });
    }
    function updateCartIcon(filled) {
        const cartImage = document.querySelector('.cart-image');
        if (cartImage) {
            if (filled) {
                cartImage.src = `${window.baseUrl}/assets/images/cart/filled_shopping_cart.png`;
                cartImage.alt = 'Shopping Cart';
            } else {
                cartImage.src = `${window.baseUrl}/assets/images/cart/empty_shopping_cart.png`;
                cartImage.alt = 'Empty Shopping Cart';
            }
        }
    }
    function removeCartItem(productId) {
        if (!productId) {
            console.error('Product ID not found for removal.');
            return;
        }
        const allButtons = document.querySelectorAll('button');
        allButtons.forEach(btn => { btn.disabled = true; });
        const deleteUrl = `${window.baseUrl}/api/cart/item/${productId}`;
        console.log('Making DELETE request to:', deleteUrl);
        fetch(deleteUrl, {
            method: 'POST', 
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({}) 
        })
            .then(response => {
                console.log('Received response from DELETE request. Status:', response.status);
                const contentType = response.headers.get("content-type");
                if (response.ok && contentType && contentType.indexOf("application/json") !== -1) {
                    return response.json();
                } else if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Remove item failed: ${response.status} ${response.statusText}. ${text}`);
                    });
                } else {
                    return response.text().then(text => {
                        throw new Error("Expected JSON response from remove item, got non-JSON: " + text);
                    });
                }
            })
            .then(data => {
                console.log('Parsed response data:', data);
                if (data.success) {
                    console.log('Attempting to update UI after deletion...');
                    const itemRow = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
                    if (itemRow) itemRow.remove();
                    const cartTotalElement = document.getElementById('cart-total-price');
                    if (cartTotalElement && data.total_price !== undefined) {
                        const totalPrice = parseFloat(data.total_price);
                        cartTotalElement.textContent = !isNaN(totalPrice) ? `$${totalPrice.toFixed(2)}` : '$--.--'; 
                    }
                    if (data.is_empty) {
                        updateCartUI({
                            is_empty: true,
                            total_items: 0,
                            total_price: 0
                        });
                    } else {
                        updateCartIcon(true); 
                        updateCartBadge();
                    }
                    showToast('Item removed from cart.', 'success');
                } else {
                    showToast('Error: ' + (data.message || 'Could not remove item from cart.'), 'error');
                }
                allButtons.forEach(btn => { btn.disabled = false; });
            })
            .catch(error => {
                console.error('Error details during delete fetch operation:', error);
                console.error('Remove Cart Item Fetch error:', error);
                showToast(`An error occurred: ${error.message}. Please try again.`, 'error');
                allButtons.forEach(btn => { btn.disabled = false; });
            });
    }
    const cartCountBadge = document.getElementById('cart-count-badge');
    async function fetchCartCount() {
        if (!cartCountBadge) {
            return 0;
        }
        try {
            console.log('Attempting to fetch cart count from URL:', `${window.baseUrl}api/cart/count`);
            const response = await fetch(`${window.baseUrl}api/cart/count`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });
            if (!response.ok) {
                const errorText = await response.text().catch(() => 'Could not read error response body');
                throw new Error(`HTTP error! status: ${response.status}. ${errorText}`);
            }
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.indexOf("application/json") !== -1) {
                const data = await response.json();
                if (data && typeof data.count === 'number' && data.count >= 0) {
                    return data.count;
                } else {
                    console.error('Invalid cart count data format or negative count received:', data);
                    return 0; 
                }
            } else {
                const text = await response.text();
                throw new Error("Expected JSON response for cart count, got non-JSON: " + text);
            }
        } catch (error) {
            console.error('Error fetching cart count:', error); 
            console.error(`Error message: ${error.message}. Status: ${error.response ? error.response.status : 'N/A'}`);
            return 0; 
        }
    }
    async function updateCartBadge() {
        if (!cartCountBadge) {
            return; 
        }
        const count = await fetchCartCount(); 
        if (count > 0) {
            cartCountBadge.textContent = count;
            cartCountBadge.classList.add('visible');
        } else {
            cartCountBadge.textContent = '';
            cartCountBadge.classList.remove('visible');
        }
    }
    updateCartBadge();
    if (mainCategorySelect && mainCategorySelect.value && mainCategorySelect.value !== 'all') {
        const initialCategoryId = mainCategorySelect.value;
        console.log(`Initial main category detected: ${initialCategoryId}. Fetching sub-categories...`);
        fetchAndPopulateSubcategories(initialCategoryId);
    } else if (mainCategorySelect) {
        console.log("No initial main category selected or 'all' selected.");
    }
    const cancelOrderModal = document.getElementById('cancelOrderModal');
    if (cancelOrderModal) {
        const confirmCancelBtn = document.getElementById('confirmCancelBtn');
        const modalCloseBtn = document.getElementById('modalCloseBtn'); 
        const cancelOrderForm = document.getElementById('cancelOrderForm'); 
        let previouslyFocusedElement = null; 
        if (confirmCancelBtn && modalCloseBtn && cancelOrderForm) {
            document.addEventListener('click', function (event) {
                if (event.target.matches('[data-bs-toggle="modal"][data-bs-target="#cancelOrderModal"]')) {
                    const cancelUrl = event.target.getAttribute('data-cancel-url'); 
                    if (cancelUrl) {
                        cancelOrderForm.action = cancelUrl; 
                    }
                    previouslyFocusedElement = event.target; 
                    openModal(); 
                }
            });
            confirmCancelBtn.addEventListener('click', function () {
                cancelOrderForm.submit(); 
            });
            modalCloseBtn.addEventListener('click', function () {
                closeModal();
            });
            cancelOrderModal.addEventListener('click', function (event) {
                if (event.target === cancelOrderModal) { 
                    closeModal();
                }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && cancelOrderModal.classList.contains('modal-visible')) {
                    closeModal();
                }
            });
            cancelOrderModal.addEventListener('keydown', function (event) {
                if (event.key === 'Tab' && cancelOrderModal.classList.contains('modal-visible')) {
                    const focusableElements = cancelOrderModal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
                    const firstElement = focusableElements[0];
                    const lastElement = focusableElements[focusableElements.length - 1];
                    if (event.shiftKey && document.activeElement === firstElement) {
                        event.preventDefault();
                        lastElement.focus();
                    }
                    else if (!event.shiftKey && document.activeElement === lastElement) {
                        event.preventDefault();
                        firstElement.focus();
                    }
                }
            });
            function openModal() {
                cancelOrderModal.inert = false; 
                cancelOrderModal.removeAttribute('aria-hidden');
                cancelOrderModal.classList.add('modal-visible');
                cancelOrderModal.setAttribute('aria-modal', 'true');
                cancelOrderModal.setAttribute('role', 'dialog');
                setTimeout(() => {
                    modalCloseBtn.focus();
                }, 50);
            }
            function closeModal() {
                const elementToFocus = previouslyFocusedElement || document.body;
                try {
                    elementToFocus.focus();
                } catch (e) {
                    console.error("Error focusing element:", e);
                    document.body.focus(); 
                }
                cancelOrderModal.inert = true; 
                cancelOrderModal.classList.remove('modal-visible');
                cancelOrderModal.removeAttribute('aria-modal');
                cancelOrderModal.setAttribute('aria-hidden', 'true'); 
                previouslyFocusedElement = null; 
            }
        } else {
            if (!confirmCancelBtn) console.warn("Confirm cancel button (#confirmCancelBtn) not found inside existing #cancelOrderModal.");
            if (!modalCloseBtn) console.warn("Modal close button (#modalCloseBtn) not found inside existing #cancelOrderModal.");
            if (!cancelOrderForm) console.warn("Cancel order form (#cancelOrderForm) not found inside existing #cancelOrderModal.");
        }
    } 
}); 
