// Load orders into the table
export async function loadOrders() {
  try {
    const response = await fetch(apiUrl("orders"));
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
            <option value="approved">Approved</option>
            <option value="completed">Completed</option>
            <option value="refunded">Refunded</option>
            <option value="returned">Returned</option>
          </select>
        </td>
        <td>
          <button onclick="viewOrderDetails(${order.order_id})">View Details</button>
        </td>
      </tr>
    `).join('');
  } catch (error) {
    console.error("Failed to load orders:", error);
  }
}

// Update an order’s status
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

    // Reload orders to reflect changes
    await loadOrders();
  } catch (error) {
    console.error("Failed to update order:", error);
    alert("Failed to update order");
  }
}

export async function viewOrderDetails(orderId) {
  try {
    const response = await fetch(apiUrl(`orders?order_id=${orderId}`));
    const result = await response.json();
      console.log("🔍 Order details API response:", result);
    
    if (result.status !== "success") throw new Error(result.message);

    let order, details;

    // Case 1: API returns { order, details }
    if (result.data.order && result.data.details) {
      order = result.data.order;
      details = result.data.details;
    }
    // Case 2: API returns a flat array/object with details embedded
    else if (Array.isArray(result.data) && result.data.length > 0) {
      order = result.data[0];
      details = order.details || [];
    } else if (result.data && result.data.order_id) {
      order = result.data;
      details = order.details || [];
    } else {
      throw new Error("Unexpected order format from API");
    }

    // Build details HTML
    const detailsHtml = `
      <p><strong>Order ID:</strong> ${order.order_id}</p>
      <p><strong>Customer:</strong> ${order.first_name ?? ""} ${order.last_name ?? ""} (${order.email ?? ""})</p>
      <p><strong>Address:</strong> ${order.street ?? ""}, ${order.city ?? ""}, ${order.state ?? ""}, ${order.postal_code ?? ""}, ${order.country ?? ""}</p>
      <p><strong>Total:</strong> ₱${order.total_amount}</p>
      <p><strong>Status:</strong> ${order.order_status}</p>
      <h3>Items:</h3>
      <ul>
<ul>
  ${details.map(item => {
    // Determine which size to show
    let sizeLabel = "";
    if (item.size1_value && item.price_each == item.price1) {
      sizeLabel = `${item.size1_value} ${item.size1_unit}`;
    } else if (item.size2_value && item.price_each == item.price2) {
      sizeLabel = `${item.size2_value} ${item.size2_unit}`;
    }

    return `
      <li>
        ${item.product_name} (${sizeLabel}) - Qty: ${item.quantity} - ₱${item.price_each}
      </li>
    `;
  }).join("")}
</ul>

    `;

    document.getElementById("order-details").innerHTML = detailsHtml;

    // Show modal
    const modal = document.getElementById("order-modal");
    modal.style.display = "block";

    modal.querySelector(".close-btn").onclick = () => modal.style.display = "none";
    window.onclick = (e) => { if (e.target === modal) modal.style.display = "none"; };

  } catch (error) {
    console.error("Failed to fetch order details:", error);
    alert("Failed to fetch order details");
  }
}


// Expose to global so inline HTML calls work
window.updateOrder = updateOrder;
window.viewOrderDetails = viewOrderDetails;

// For your loader in index.js
export async function initOrders() {
  await loadOrders();
}
