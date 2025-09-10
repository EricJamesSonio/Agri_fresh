export async function initStock() {
  let threshold = 5; // Default threshold
  const thresholdDisplay = document.getElementById("stock-threshold");
  const thresholdInput = document.getElementById("stock-threshold-input");
  const updateBtn = document.getElementById("update-threshold-btn");

  thresholdDisplay.textContent = threshold;
  if(thresholdInput) thresholdInput.value = threshold;

  let categories = [];

  // -------------------------
  // Load categories
  // -------------------------
  async function loadCategories() {
    try {
      const res = await fetch(apiUrl("category"));
      categories = await res.json();
    } catch {
      categories = [];
    }
  }

  // -------------------------
  // Load products
  // -------------------------
  async function loadProducts() {
    try {
      const res = await fetch(apiUrl("products"));
      const products = await res.json();
      const tbody = document.querySelector("#stock-table tbody");
      if (!tbody) return;

      tbody.innerHTML = products.map(p => {
        const categoryName = categories.find(c => c.id === p.category_id)?.name || '-';
        const lowStockClass = p.stock_quantity <= threshold ? 'low-stock' : '';
        return `
          <tr class="${lowStockClass}">
            <td>${p.id}</td>
            <td>${p.name}</td>
            <td>${categoryName}</td>
            <td>${p.size_value} ${p.size_unit}</td>
            <td>₱${p.price.toFixed(2)}</td>
            <td>${p.stock_quantity}</td>
          </tr>
        `;
      }).join('');
    } catch (err) {
      console.error("Failed to load stock:", err);
    }
  }

  // -------------------------
  // Update threshold handler
  // -------------------------
  if(updateBtn && thresholdInput) {
    updateBtn.addEventListener("click", () => {
      const newThreshold = parseInt(thresholdInput.value);
      if(isNaN(newThreshold) || newThreshold < 0) return alert("Enter a valid threshold");
      threshold = newThreshold;
      thresholdDisplay.textContent = threshold;
      loadProducts();
    });
  }

  // -------------------------
  // Initialize
  // -------------------------
  await loadCategories();
  await loadProducts();
}
