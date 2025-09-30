import { viewOrderDetails } from "./orders.js";

export async function initSales() {
  const tbody = document.querySelector("#sales-table tbody");
  const exportBtn = document.getElementById("export-btn");

  if (!tbody) {
    console.warn("initSales: sales table not found — aborting.");
    return;
  }

  let currentFilter = "all";
  let currentSales = [];

  async function loadSalesReport() {
    try {
      const response = await fetch(apiUrl("orders"));
      const result = await response.json();

      if (result.status !== "success") throw new Error(result.message || "Failed to fetch orders");

      // completed only
      let sales = (Array.isArray(result.data) ? result.data : [])
        .filter(o => o.order_status === "completed");

      const now = new Date();

      if (currentFilter === "daily") {
        const today = new Date();
        sales = sales.filter(o => {
          const d = new Date(o.created_at);
          return d.getDate() === today.getDate() &&
                 d.getMonth() === today.getMonth() &&
                 d.getFullYear() === today.getFullYear();
        });
      } else if (currentFilter === "weekly") {
        const startOfWeek = new Date(now);
        startOfWeek.setHours(0, 0, 0, 0);
        startOfWeek.setDate(now.getDate() - now.getDay());
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 7);
        sales = sales.filter(o => {
          const d = new Date(o.created_at);
          return d >= startOfWeek && d < endOfWeek;
        });
      } else if (currentFilter === "monthly") {
        sales = sales.filter(o => {
          const d = new Date(o.created_at);
          return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
        });
      }

      currentSales = sales;

      // render rows
      tbody.innerHTML = sales.map(order => {
        const created = order.created_at ? new Date(order.created_at).toLocaleDateString() : "";
        const amt = Number(order.total_amount || 0).toFixed(2);
        return `
          <tr>
            <td>${order.order_id}</td>
            <td>${order.customer_id}</td>
            <td>₱${amt}</td>
            <td style="text-transform:capitalize">${order.order_status}</td>
            <td>${created}</td>
            <td>
              <button class="details-btn" data-id="${order.order_id}">View Details</button>
            </td>
          </tr>
        `;
      }).join("");

      // attach listeners for details buttons
      tbody.querySelectorAll(".details-btn").forEach(btn => {
        btn.addEventListener("click", () => {
          const id = btn.dataset.id;
          viewOrderDetails(id);
        });
      });

      // summary rows
      const totalRevenue = sales.reduce((s, o) => s + (parseFloat(o.total_amount) || 0), 0);
      const totalDiscounts = sales.reduce((s, o) => s + (parseFloat(o.discount_amount) || 0), 0);
      const totalItemsSold = sales.reduce((sum, o) => {
        if (o.details && Array.isArray(o.details)) {
          return sum + o.details.reduce((is, it) => is + (parseInt(it.quantity) || 0), 0);
        }
        return sum;
      }, 0);

      const summaryHTML = `
        <tr class="summary"><td colspan="6">Total Revenue: ₱${totalRevenue.toFixed(2)}</td></tr>
        <tr class="summary"><td colspan="6">Total Discounts: ₱${totalDiscounts.toFixed(2)}</td></tr>
        <tr class="summary"><td colspan="6">Total Items Sold: ${totalItemsSold}</td></tr>
      `;
      tbody.insertAdjacentHTML("beforeend", summaryHTML);

    } catch (err) {
      console.error("Failed to load sales report:", err);
      tbody.innerHTML = `<tr><td colspan="6" class="error">Failed to load sales report.</td></tr>`;
    }
  }

  // export CSV
  function exportCSV(sales) {
    if (!Array.isArray(sales) || sales.length === 0) {
      alert("No sales data to export.");
      return;
    }

    const headers = [
      "Order ID","Customer ID","Subtotal","Shipping Fee","Discount",
      "Total Amount","Status","Created At"
    ];

    const currency = (val) =>
      "₱" + Number(val || 0).toLocaleString("en-PH", { minimumFractionDigits: 2 });

    const rows = sales.map(o => {
      const date = o.created_at ? new Date(o.created_at).toLocaleDateString("en-PH") : "";
      const status = o.order_status
        ? o.order_status.charAt(0).toUpperCase() + o.order_status.slice(1)
        : "";
      return [
        o.order_id,
        o.customer_id,
        currency(o.subtotal),
        currency(o.shipping_fee),
        currency(o.discount_amount),
        currency(o.total_amount),
        status,
        date,
      ];
    });

    const totalRevenue = sales.reduce((s, o) => s + (parseFloat(o.total_amount) || 0), 0);
    const totalDiscounts = sales.reduce((s, o) => s + (parseFloat(o.discount_amount) || 0), 0);
    const totalItemsSold = sales.reduce((sum, o) => {
      if (o.details && Array.isArray(o.details)) {
        return sum + o.details.reduce((is, it) => is + (parseInt(it.quantity) || 0), 0);
      }
      return sum;
    }, 0);

    const summary = [
      ["", "", "", "", "", "", "", ""],
      ["Total Revenue", currency(totalRevenue)],
      ["Total Discounts", currency(totalDiscounts)],
      ["Total Items Sold", totalItemsSold],
    ];

    let csvContent = [headers, ...rows, ...summary]
      .map(r => r.map(cell => `"${String(cell ?? "").replace(/"/g, '""')}"`).join(","))
      .join("\n");

    const blob = new Blob(["\uFEFF" + csvContent], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);

    const a = document.createElement("a");
    a.href = url;
    a.download = `sales_report_${new Date().toISOString().slice(0, 10)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
  }

  // filter buttons
  document.querySelectorAll(".filter-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      currentFilter = btn.dataset.filter;
      loadSalesReport();
    });
  });

  if (exportBtn) exportBtn.addEventListener("click", () => exportCSV(currentSales));

  await loadSalesReport();
}
