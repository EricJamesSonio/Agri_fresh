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

        // check if within 8 hours
        const createdAt = new Date(order.created_at);
        const now = new Date();
        const hoursDiff = (now - createdAt) / (1000 * 60 * 60); // ms → hrs

        let returnBtn = "";

if (order.return_request == 1) {
    if (order.order_status.toLowerCase() === "returned" || order.order_status.toLowerCase() === "refunded") {
        returnBtn = `<span class="notif success">Request Approved!</span>`;
    } else {
        returnBtn = `<span class="notif">Return/Refund already requested</span>`;
    }
        } else if (order.order_status.toLowerCase() === "completed") {
            if (hoursDiff <= 8) {
                returnBtn = `
                <button class="return-btn" onclick="requestReturn(${order.order_id})">
                    Request Return/Refund
                </button>`;
            } else {
                returnBtn = `
                <button class="return-btn expired" disabled>
                    Return/Refund expired (${hoursDiff.toFixed(1)} hrs ago)
                </button>`;
            }
        } else {
            returnBtn = "";
        }


        orderCard.innerHTML = `
            <div class="order-header">
                <div>
                    <strong>Order #${order.order_id}</strong> 
                    - <span class="${statusClass}">${order.order_status}</span>
                </div>
                <div>Total: ${parseFloat(order.total_amount).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })}</div>
            </div>

            <div><small>Ordered on: ${createdAt.toLocaleString()}</small></div>
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

            <div class="order-actions">
                ${returnBtn}
            </div>
        `;

        container.appendChild(orderCard);
    });

window.requestReturn = async function(orderId) {
    if (!confirm("Are you sure you want to request a return/refund?")) return;

    try {
        const res = await fetch(`../../backend/api/index.php?request=orderReturnRequest`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ order_id: orderId })
        });

        const result = await res.json();
        alert(result.message);

        // ✅ Just reload orders, it will display "already requested" correctly
        fetchOrders();

    } catch (err) {
        alert("Error requesting return/refund");
        console.error(err);
    }
};


    } catch (error) {
        container.innerHTML = `<p>Error fetching orders: ${error.message}</p>`;
    }
}

// ✅ Fetch orders on page load
document.addEventListener("DOMContentLoaded", fetchOrders);
