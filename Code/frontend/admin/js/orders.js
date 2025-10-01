// ================================
// Load orders into the table
// ================================
export async function loadOrders() {
  try {
    const response = await fetch(apiUrl("orders"));
    const result = await response.json();

    if (result.status !== "success") throw new Error(result.message);

    const tbody = document.querySelector("#orders-table tbody");
    tbody.innerHTML = result.data.map(order => `
      <tr data-id="${order.order_id}" class="${order.return_request == 1 ? 'highlight-return' : ''}">
        <td>${order.order_id}</td>
        <td>${order.customer_id}</td>
        <td>₱${parseFloat(order.total_amount).toFixed(2)}</td>
        <td>${order.voucher_code ? order.voucher_code : "—"}</td>
        <td class="status-cell">
          ${order.return_request == 1
            ? '<span class="notif">Return/Refund Requested</span>'
            : order.order_status}
        </td>
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

// ================================
// Update an order’s status (FAST update only row)
// ================================
function updateOrder(orderId, newStatus) {
  if (!newStatus) return;

  // Run async work after handler exits
  (async () => {
    try {
      const response = await fetch(apiUrl("orderUpdate"), {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ order_id: orderId, status: newStatus })
      });

      const result = await response.json();
      alert(result.message);

      // ✅ Instead of reload, update only that row
      const row = document.querySelector(`#orders-table tr[data-id="${orderId}"]`);
      if (row) {
        const statusCell = row.querySelector(".status-cell");
        statusCell.innerHTML = (newStatus === "return_requested")
          ? '<span class="notif">Return/Refund Requested</span>'
          : newStatus;
      }
    } catch (error) {
      console.error("Failed to update order:", error);
      alert("Failed to update order");
    }
  })();
}

// ================================
// View order details (modal)
// ================================
export async function viewOrderDetails(orderId) {
  try {
    const response = await fetch(apiUrl(`orders?order_id=${orderId}`));
    const result = await response.json();
    console.log("🔍 Order details API response:", result);
    
    if (result.status !== "success") throw new Error(result.message);

    let order, details;

    if (result.data.order && result.data.details) {
      order = result.data.order;
      details = result.data.details;
    } else if (Array.isArray(result.data) && result.data.length > 0) {
      order = result.data[0];
      details = order.details || [];
    } else if (result.data && result.data.order_id) {
      order = result.data;
      details = order.details || [];
    } else {
      throw new Error("Unexpected order format from API");
    }

    const voucherHtml = order.voucher_code
      ? `
        <p><strong>Voucher Code:</strong> ${order.voucher_code}</p>
        <p><strong>Discount Applied:</strong> -₱${parseFloat(order.discount_amount).toFixed(2)}</p>
      `
      : `<p><strong>Voucher:</strong> None</p>`;

    const detailsHtml = `
      <p><strong>Order ID:</strong> ${order.order_id}</p>
      <p><strong>Customer:</strong> ${order.first_name ?? ""} ${order.last_name ?? ""} (${order.email ?? ""})</p>
      <p><strong>Address:</strong> ${order.street ?? ""}, ${order.city ?? ""}, ${order.state ?? ""}, ${order.postal_code ?? ""}, ${order.country ?? ""}</p>
      
      <p><strong>Subtotal:</strong> ₱${parseFloat(order.subtotal).toFixed(2)}</p>
      <p><strong>Shipping Fee:</strong> ₱${parseFloat(order.shipping_fee).toFixed(2)}</p>
      ${voucherHtml}
      <p><strong>Total:</strong> ₱${parseFloat(order.total_amount).toFixed(2)}</p>

      <p><strong>Status:</strong> ${order.order_status}</p>

      <h3>Items:</h3>
      <ul>
        ${details.map(item => {
          let sizeLabel = "";
          if (item.size1_value && item.price_each == item.price1) {
            sizeLabel = `${item.size1_value} ${item.size1_unit}`;
          } else if (item.size2_value && item.price_each == item.price2) {
            sizeLabel = `${item.size2_value} ${item.size2_unit}`;
          } else if (item.size_value) {
            sizeLabel = `${item.size_value} ${item.size_unit}`;
          }

          return `
            <li>
              ${item.product_name} (${sizeLabel}) - Qty: ${item.quantity} - ₱${parseFloat(item.price_each).toFixed(2)}
            </li>
          `;
        }).join("")}
      </ul>
    `;

    document.getElementById("order-details").innerHTML = detailsHtml;

    const modal = document.getElementById("order-modal");
    modal.style.display = "block";

    modal.querySelector(".close-btn").onclick = () => modal.style.display = "none";
    window.onclick = (e) => { if (e.target === modal) modal.style.display = "none"; };

  } catch (error) {
    console.error("Failed to fetch order details:", error);
    alert("Failed to fetch order details");
  }
}

// ================================
// Expose to global so inline calls work
// ================================
window.updateOrder = updateOrder;
window.viewOrderDetails = viewOrderDetails;

// ================================
// Init loader
// ================================
export async function initOrders() {
  await loadOrders();
}
