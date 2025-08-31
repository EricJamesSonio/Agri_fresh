async function loadOrders() {
  try {
    const response = await fetch(apiUrl("orders")); // apiUrl is defined in config.js
    const result = await response.json();

    if (result.status !== "success") throw new Error(result.message);

    const tbody = document.querySelector("#orders-table tbody");
    tbody.innerHTML = result.data.map(order => `
      <tr>
        <td>${order.order_id}</td>
        <td>${order.customer_id}</td>
        <td>₱${order.total_amount}</td>
        <td>${order.order_status}</td>
        <td>
          <select onchange="updateOrder(${order.order_id}, this.value)">
            <option value="">-- Select --</option>
            <option value="completed">Completed</option>
            <option value="refunded">Refunded</option>
            <option value="returned">Returned</option>
          </select>
        </td>
      </tr>
    `).join('');
  } catch (error) {
    console.error("Failed to load orders:", error);
  }
}

async function updateOrder(orderId, newStatus) {
  if (!newStatus) return;

  try {
    const response = await fetch(apiUrl("orderUpdate"), {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ order_id: orderId, status: newStatus })
    });

    const result = await response.json();
    alert(result.message);
    loadOrders();
  } catch (error) {
    console.error("Failed to update order:", error);
  }
}

// call after section HTML is loaded
loadOrders();
