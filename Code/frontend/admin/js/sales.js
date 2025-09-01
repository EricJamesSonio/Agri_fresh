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

      const totalRevenue = sales.reduce((sum, o) => sum + parseFloat(o.total_amount), 0);
      const revenueRow = document.createElement("tr");
      revenueRow.innerHTML = `<td colspan="5" style="text-align:right;font-weight:bold;">Total Revenue: ₱${totalRevenue.toFixed(2)}</td>`;
      tbody.appendChild(revenueRow);

    } catch (error) {
      console.error("Failed to load sales report:", error);
    }
  }

  await loadSalesReport();
}
