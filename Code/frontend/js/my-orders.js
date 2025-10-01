async function fetchOrders() {
    const container = document.getElementById('orders-container');
    container.innerHTML = '<p>Loading orders...</p>';

    try {
        const res = await fetch(`../../backend/api/index.php?request=orders&customer_id=${window.customer_id}`);
        const data = await res.json();

        if (data.status !== 'success') {
            container.innerHTML = `<p>${data.message}</p>`;
            return;
        }

        const orders = data.data;
        if (!orders.length) {
            container.innerHTML = '<p>No orders found.</p>';
            return;
        }

        container.innerHTML = '';
        orders.forEach(order => {
            const orderCard = document.createElement('div');
            orderCard.className = 'order-card';

            const statusClass = 'status-' + order.order_status.toLowerCase();

orderCard.innerHTML = `
    <div class="order-header">
        <div>
            <strong>Order #${order.order_id}</strong> 
            - <span class="${statusClass}">${order.order_status}</span>
        </div>
        <div>Total: ${parseFloat(order.total_amount).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })}</div>
    </div>

    <div><small>Ordered on: ${new Date(order.created_at).toLocaleString()}</small></div>
    <div><strong>Payment:</strong> ${order.payment_method}</div>

    <div><strong>Customer:</strong> ${order.first_name} ${order.last_name} (${order.email})</div>

    <div><strong>Shipping Address:</strong> 
        ${order.street}, ${order.city}, ${order.state || ''}, ${order.country}
    </div>

    <div><strong>Subtotal:</strong> ₱${parseFloat(order.subtotal).toFixed(2)}</div>
    <div><strong>Shipping Fee:</strong> ₱${parseFloat(order.shipping_fee).toFixed(2)}</div>
    <div><strong>Discount:</strong> -₱${parseFloat(order.discount_amount).toFixed(2)}</div>
    ${order.voucher_code ? `<div><strong>Voucher Applied:</strong> ${order.voucher_code}</div>` : ""}
    <div><strong>Final Total:</strong> ₱${parseFloat(order.total_amount).toFixed(2)}</div>

    <div class="order-details">
        <h4>Items:</h4>
        ${order.details.map(item => `
            <div class="order-item">
                ${item.product_name} 
                (${item.size_value} ${item.size_unit}) 
                - Qty: ${item.quantity} 
                - Price: ₱${parseFloat(item.price_each).toFixed(2)}
            </div>
        `).join('')}
    </div>
`;


            container.appendChild(orderCard);
        });
    } catch (error) {
        container.innerHTML = `<p>Error fetching orders: ${error.message}</p>`;
    }
}

// ✅ Fetch orders on page load
document.addEventListener("DOMContentLoaded", fetchOrders);
