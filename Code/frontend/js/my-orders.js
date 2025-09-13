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
                <div><strong>Shipping Address:</strong> ${order.street}, ${order.city}, ${order.state || ''}, ${order.country}</div>
                <div class="order-details">
                    ${order.details.map(item => `
                        <div class="order-item">
                            ${item.product_name} - Qty: ${item.quantity} - ${parseFloat(item.price_each).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })}
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
