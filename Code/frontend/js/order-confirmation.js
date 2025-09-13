let orderId = null;
let customerId = parseInt(localStorage.getItem("customer_id"), 10);

document.addEventListener('DOMContentLoaded', function() {
    // Get order_id from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    orderId = urlParams.get('order_id');
    
    if (!orderId) {
        document.getElementById('order-details').innerHTML = 
            '<p class="error">Order ID not found.</p>';
        return;
    }
    
    if (!customerId) {
        alert('Please login to view order details');
        window.location.href = 'login.php';
        return;
    }
    
    loadOrderDetails();
});

async function loadOrderDetails() {
    try {
        const response = await fetch(apiUrl(`orders?order_id=${orderId}&customer_id=${customerId}`));
        const result = await response.json();
        
        if (result.status === 'success') {
            displayOrderDetails(result.data);
        } else {
            document.getElementById('order-details').innerHTML = 
                `<p class="error">${result.message}</p>`;
        }
    } catch (error) {
        console.error('Failed to load order details:', error);
        document.getElementById('order-details').innerHTML = 
            '<p class="error">Failed to load order details.</p>';
    }
}

function displayOrderDetails(data) {
    const order = data.order;
    const details = data.details;

    const orderDate = new Date(order.created_at).toLocaleDateString();
    const subtotal = parseFloat(order.subtotal);
    const shipping = parseFloat(order.shipping_fee);
    const discount = parseFloat(order.discount_amount);
    const total = parseFloat(order.total_amount);

    document.getElementById('order-details').innerHTML = `
        <h3>Order Confirmation</h3>
        
        <div class="detail-row">
            <span>Order ID:</span>
            <span>#${order.order_id}</span>
        </div>
        
        <div class="detail-row">
            <span>Order Date:</span>
            <span>${orderDate}</span>
        </div>
        
        <div class="detail-row">
            <span>Status:</span>
            <span style="text-transform: capitalize; color: #4CAF50;">${order.order_status}</span>
        </div>
        
        <div class="detail-row">
            <span>Payment Method:</span>
            <span>${order.payment_method}</span>
        </div>

        ${order.voucher_code ? `
        <div class="detail-row">
            <span>Voucher Used:</span>
            <span>${order.voucher_code}</span>
        </div>` : ""}
        
        <div class="address-info">
            <strong>Delivery Address:</strong><br>
            ${order.street}<br>
            ${order.city}${order.state ? ', ' + order.state : ''} ${order.postal_code || ''}<br>
            ${order.country}
        </div>

        <h4>Items Ordered:</h4>
        <div class="item-list">
            ${details.map(item => {
                const priceEach = parseFloat(item.price_each);
                const itemTotal = priceEach * item.quantity;
                return `
                    <div class="item">
                        <div>
                            <strong>${item.product_name}</strong><br>
                            <small>${priceEach.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })} each</small>
                        </div>
                        <div>
                            ${item.quantity} × ${priceEach.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })} = 
                            ${itemTotal.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })}
                        </div>
                    </div>
                `;
            }).join('')}
        </div>
        
        <div class="detail-row">
            <span>Subtotal:</span>
            <span>${subtotal.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })}</span>
        </div>
        <div class="detail-row">
            <span>Shipping Fee:</span>
            <span>${shipping.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })}</span>
        </div>
        <div class="detail-row">
            <span>Discount:</span>
            <span>- ${discount.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })}</span>
        </div>
        <div class="detail-row">
            <span>Grand Total:</span>
            <span>${total.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })}</span>
        </div>
    `;
}

function viewOrders() {
    window.location.href = './my-orders.php';
}
