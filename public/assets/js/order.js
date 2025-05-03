document.addEventListener('DOMContentLoaded', function () {
    initMobileMenu();
    initQuantityInputs();
    initOrderForm();
    initOrderCancellation();
    initPrintButtons();
    initOrderStatusFilter();
});
function initMobileMenu() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.mobile-menu');
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function () {
            navMenu.classList.toggle('show');
            const icon = menuToggle.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-bars'); 
                icon.classList.toggle('fa-times'); 
            }
        });
    }
}
function initQuantityInputs() {
    const quantityInput = document.getElementById('quantity');
    if (!quantityInput) return;
    const quantityValue = document.getElementById('quantity-value'); 
    const totalPrice = document.getElementById('total-price'); 
    const productPrice = parseFloat(quantityInput.getAttribute('data-price') || 0);
    const maxQuantity = parseInt(quantityInput.getAttribute('max') || 1); 
    const decreaseBtn = document.querySelector('.quantity-btn.decrease-btn');
    if (decreaseBtn) {
        decreaseBtn.addEventListener('click', function () {
            let value = parseInt(quantityInput.value);
            if (value > 1) {
                value--;
                quantityInput.value = value; 
                updateOrderSummary(value, productPrice); 
            }
        });
    }
    const increaseBtn = document.querySelector('.quantity-btn.increase-btn');
    if (increaseBtn) {
        increaseBtn.addEventListener('click', function () {
            let value = parseInt(quantityInput.value);
            if (value < maxQuantity) {
                value++;
                quantityInput.value = value; 
                updateOrderSummary(value, productPrice); 
            }
        });
    }
    if (quantityInput) {
        quantityInput.addEventListener('input', function () {
            let value = parseInt(this.value);
            if (isNaN(value) || value < 1) {
                value = 1; 
            } else if (value > maxQuantity) {
                value = maxQuantity; 
            }
            this.value = value; 
            updateOrderSummary(value, productPrice); 
        });
    }
}
function updateOrderSummary(quantity, unitPrice) {
    const quantityValue = document.getElementById('quantity-value');
    const totalPrice = document.getElementById('total-price');
    if (quantityValue && totalPrice) {
        const total = unitPrice * quantity;
        quantityValue.textContent = quantity;
        totalPrice.textContent = '$' + total.toFixed(2); 
    }
}
function initOrderForm() {
    const orderForm = document.querySelector('.order-form'); 
    if (orderForm) {
        orderForm.addEventListener('submit', function (e) {
            const quantityInput = document.getElementById('quantity');
            if (quantityInput) {
                const quantity = parseInt(quantityInput.value);
                if (isNaN(quantity) || quantity < 1) {
                    e.preventDefault(); 
                    showMessage('error', 'Please enter a valid quantity.'); 
                    return; 
                }
            }
            if (orderForm.getAttribute('data-ajax') === 'true') {
                e.preventDefault(); 
                const submitBtn = orderForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML; 
                submitBtn.disabled = true; 
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...'; 
                const formData = new FormData(orderForm);
                const orderData = { 
                    product_id: formData.get('product_id'),
                    quantity: parseInt(formData.get('quantity')),
                    notes: formData.get('notes') || null
                };
                fetch(window.baseUrl + 'order/product/' + orderData.product_id, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams(formData)
                })
                    .then(response => response.json()) 
                    .then(data => {
                        if (data.success) {
                            window.location.href = data.redirect || (window.baseUrl + 'order/confirmation/' + data.order_id);
                        } else {
                            showMessage('error', data.message || 'An error occurred while processing your order.');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    })
                    .catch(error => {
                        console.error('Error processing order:', error);
                        showMessage('error', 'An error occurred while processing your order. Please try again.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            }
        });
    }
    const checkoutForm = document.getElementById('order-form'); 
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function (e) {
            const shippingAddress = document.getElementById('shipping_address');
            if (shippingAddress && shippingAddress.value.trim() === '') {
                e.preventDefault(); 
                showMessage('error', 'Please enter your shipping address.');
                shippingAddress.focus(); 
                return;
            }
        });
    }
}
function initOrderCancellation() {
    const cancelForms = document.querySelectorAll('.cancel-form');
    cancelForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            if (form.getAttribute('data-ajax') === 'true') {
                e.preventDefault(); 
                if (!confirm('Are you sure you want to cancel this order?')) {
                    return; 
                }
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';
                const formData = new FormData(form); 
                const orderId = form.getAttribute('action').split('/').pop();
                fetch(window.baseUrl + 'order/cancel/' + orderId, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest' 
                    },
                    body: formData 
                })
                    .then(response => response.json()) 
                    .then(data => {
                        if (data.success) {
                            showMessage('success', data.message || 'Order cancelled successfully.');
                            updateOrderStatusUI('cancelled'); 
                            form.style.display = 'none'; 
                        } else {
                            showMessage('error', data.message || 'Failed to cancel order.');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    })
                    .catch(error => {
                        console.error('Error cancelling order:', error);
                        showMessage('error', 'An error occurred while cancelling your order. Please try again.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            }
        });
    });
}
function updateOrderStatusUI(status) {
    const statusBadge = document.querySelector('.badge'); 
    if (statusBadge) {
        statusBadge.className = 'badge status-' + status; 
        statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1); 
    }
    const statusText = document.querySelector('.status-text'); 
    if (statusText) {
        statusText.className = 'info-value status-text-' + status; 
        statusText.textContent = status.charAt(0).toUpperCase() + status.slice(1); 
    }
    if (status === 'cancelled') {
        const timeline = document.querySelector('.timeline'); 
        if (timeline) {
            if (!timeline.querySelector('.timeline-item.cancelled')) {
                const cancelledStep = document.createElement('div');
                cancelledStep.className = 'timeline-item active cancelled'; 
                cancelledStep.innerHTML = `
                    <div class="timeline-icon"><i class="fas fa-times"></i></div>
                    <div class="timeline-content">
                        <h4>Cancelled</h4>
                        <p class="timeline-date">${new Date().toLocaleString()}</p>
                        <p class="timeline-description">This order has been cancelled.</p>
                    </div>
                `;
                timeline.appendChild(cancelledStep);
            }
        }
    }
}
function initPrintButtons() {
    const printButtons = document.querySelectorAll('.print-confirmation, [onclick*="window.print"]');
    printButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault(); 
            window.print(); 
        });
    });
}
function initOrderStatusFilter() {
    const statusFilter = document.getElementById('order-status-filter');
    if (!statusFilter) return;
    const orderRows = document.querySelectorAll('.order-row');
    statusFilter.addEventListener('change', function () {
        const selectedStatus = this.value; 
        orderRows.forEach(row => {
            const rowStatus = row.getAttribute('data-status'); 
            if (selectedStatus === 'all' || selectedStatus === rowStatus) {
                row.style.display = ''; 
            } else {
                row.style.display = 'none'; 
            }
        });
    });
}
function showMessage(type, message) {
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    const alertElement = document.createElement('div');
    alertElement.className = `alert alert-${type}`; 
    alertElement.innerHTML = message; 
    const container = document.querySelector('.container'); 
    if (container) {
        const firstChild = container.firstChild;
        container.insertBefore(alertElement, firstChild);
    } else {
        console.warn('Container element not found for showing message. Appending to body.');
        document.body.insertBefore(alertElement, document.body.firstChild);
    }
    alertElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
    if (type === 'success') {
        setTimeout(() => {
            if (alertElement.parentNode) {
                alertElement.remove();
            }
        }, 5000); 
    }
}
