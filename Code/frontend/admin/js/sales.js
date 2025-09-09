export async function initSales() {
  async function loadSalesReport() {
    try {
      const response = await fetch(apiUrl("orders"));
      const result = await response.json();

      if (result.status !== "success") throw new Error(result.message);

      const tbody = document.querySelector("#sales-table tbody");
      const sales = result.data.filter(order => order.order_status === "completed");

      tbody.innerHTML = sales.map(order => `
        <tr>
          <td>${order.order_id}</td>
          <td>${order.customer_id}</td>
          <td>₱${order.total_amount}</td>
          <td>${order.order_status}</td>
          <td>${new Date(order.created_at).toLocaleDateString()}</td>
        </tr>
      `).join('');

      // ---- Totals ----
      const totalRevenue = sales.reduce((sum, o) => sum + parseFloat(o.total_amount), 0);
      const totalDiscounts = sales.reduce((sum, o) => sum + (parseFloat(o.discount_amount) || 0), 0);
      const totalItemsSold = sales.reduce((sum, o) => {
  if (o.details && Array.isArray(o.details)) {
    return sum + o.details.reduce((s, item) => s + (parseInt(item.quantity) || 0), 0);
  }
  return sum;
}, 0);


      // ---- Summary Rows ----
      const revenueRow = document.createElement("tr");
      revenueRow.innerHTML = `<td colspan="5" style="text-align:right;font-weight:bold;">
        Total Revenue: ₱${totalRevenue.toFixed(2)}
      </td>`;
      tbody.appendChild(revenueRow);

      const discountRow = document.createElement("tr");
      discountRow.innerHTML = `<td colspan="5" style="text-align:right;font-weight:bold;">
        Total Discounts: ₱${totalDiscounts.toFixed(2)}
      </td>`;
      tbody.appendChild(discountRow);

      const itemsRow = document.createElement("tr");
      itemsRow.innerHTML = `<td colspan="5" style="text-align:right;font-weight:bold;">
        Total Items Sold: ${totalItemsSold}
      </td>`;
      tbody.appendChild(itemsRow);

    } catch (error) {
      console.error("Failed to load sales report:", error);
    }
  }

  await loadSalesReport();
}

