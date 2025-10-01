export async function initStock() {
  // Default thresholds
  let thresholds = {
    kg: 5,
    bunch: 10
  };

  // UI elements
  const thresholdInput = document.getElementById("threshold-input");
  const thresholdUnit = document.getElementById("threshold-unit");
  const thresholdDisplay = document.getElementById("threshold-display");
  const stockThreshold = document.getElementById("stock-threshold");
  const updateBtn = document.getElementById("update-threshold-btn");

  // Set default display
  function updateDisplay() {
    const unit = thresholdUnit.value;
    thresholdDisplay.textContent = `${thresholds[unit]} ${unit}`;
    stockThreshold.textContent = thresholds[unit];
    thresholdInput.value = thresholds[unit];
  }

  thresholdUnit.addEventListener("change", updateDisplay);
  thresholdInput.addEventListener("input", () => {
    const unit = thresholdUnit.value;
    thresholds[unit] = parseFloat(thresholdInput.value) || 0;
    updateDisplay();
  });

  updateBtn.addEventListener("click", () => {
    const unit = thresholdUnit.value;
    const value = parseFloat(thresholdInput.value);
    if (isNaN(value) || value < 0) return alert(`Enter a valid ${unit} threshold`);

    thresholds[unit] = value;
    updateDisplay();
    loadProducts();
  });

  // -------------------------
  // Load products
  // -------------------------
  async function loadProducts() {
    try {
      const res = await fetch(apiUrl("products"));
      const products = await res.json();
      const tbody = document.querySelector("#stock-table tbody");
      if (!tbody) return;

      tbody.innerHTML = products
        .map(p => {
          const sizePriceDisplay = p.size_value
            ? `${p.size_value} ${p.size_unit} - ₱${p.price.toFixed(2)}`
            : "-";

          const stockUnit = p.size_unit?.toLowerCase();
          const unitThreshold = thresholds[stockUnit] ?? thresholds.kg;
          const lowStockClass = p.stock_quantity <= unitThreshold ? "low-stock" : "";

          return `
            <tr class="${lowStockClass}">
              <td>${p.id}</td>
              <td>${p.name}</td>
              <td>${p.category || "Uncategorized"}</td>
              <td>${sizePriceDisplay}</td>
              <td>${p.stock_quantity === 0 ? "Out of Stock" : p.stock_quantity}</td>
            </tr>
          `;
        })
        .join("");
    } catch (err) {
      console.error("Failed to load stock:", err);
    }
  }

  // -------------------------
  // Initialize
  // -------------------------
  updateDisplay();
  await loadProducts();
}
