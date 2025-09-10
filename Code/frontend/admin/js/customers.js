export async function initCustomers() {
  const tableBody = document.querySelector("#customer-table tbody");

  // -------------------------
  // Load all customers
  // -------------------------
  async function loadCustomers() {
  try {
    const res = await fetch(apiUrl("customer"));
    const customers = await res.json();

    // Filter out deactivated customers (email AND password are null)
    const activeCustomers = customers.filter(c => c.email !== null && c.password !== null);

    tableBody.innerHTML = activeCustomers.map(c => `
      <tr>
        <td>${c.customer_id}</td>
        <td>${c.first_name} ${c.last_name}</td>
        <td>${c.email}</td>
        <td>${c.contact || '-'}</td>
        <td>${c.created_at}</td>
        <td>
          <button class="delete-btn" onclick="deleteCustomer(${c.customer_id})">Deactivate</button>
        </td>
      </tr>
    `).join('');
  } catch (err) {
    console.error("Failed to load customers:", err);
  }
}


  // -------------------------
  // Delete customer
  // -------------------------
  window.deleteCustomer = async (id) => {
    if (!confirm("Are you sure you want to delete this customer?")) return;

    try {
      const res = await fetch(apiUrl("customer"), {
        method: "DELETE",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({ customer_id: id })
      });
      const result = await res.json();
      alert(result.message);
      loadCustomers();
    } catch (err) {
      console.error(err);
    }
  };

  // -------------------------
  // Initialize
  // -------------------------
  await loadCustomers();
}
