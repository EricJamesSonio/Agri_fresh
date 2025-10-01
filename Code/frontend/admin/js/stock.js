export async function initStock() {
  // Default thresholds
  let thresholds = {
    kg: 5,
    bunch: 10
  };

  // UI elements
  const kgThresholdInput = document.getElementById("kg-threshold-input");
  const bunchThresholdInput = document.getElementById("bunch-threshold-input");
  const kgThresholdDisplay = document.getElementById("kg-threshold");
  const bunchThresholdDisplay = document.getElementById("bunch-threshold");
  const updateBtn = document.getElementById("update-threshold-btn");

  // Set default values in UI
  kgThresholdInput.value = thresholds.kg;
  bunchThresholdInput.value = thresholds.bunch;
  kgThresholdDisplay.textContent = thresholds.kg;
  bunchThresholdDisplay.textContent = thresholds.bunch;

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
          // Build sizes & prices
          let sizePriceDisplay = "";
          let stockUnit = "";
          if (p.size1_value) {
            sizePriceDisplay += `${p.size1_value} ${p.size1_unit} - ₱${p.price1.toFixed(2)}`;
            stockUnit = p.size1_unit?.toLowerCase();
          }
          if (p.size2_value) {
            sizePriceDisplay += `<br>${p.size2_value} ${p.size2_unit} - ₱${p.price2.toFixed(2)}`;
            stockUnit = p.size2_unit?.toLowerCase();
          }

          // Pick threshold based on unit
          let unitThreshold = thresholds[stockUnit] ?? thresholds.kg; // default fallback
          const lowStockClass = p.stock_quantity <= unitThreshold ? "low-stock" : "";

          return `
            <tr class="${lowStockClass}">
              <td>${p.id}</td>
              <td>${p.name}</td>
              <td>${p.category || "Uncategorized"}</td>
              <td>${sizePriceDisplay || "-"}</td>
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
  // Update thresholds handler
  // -------------------------
  if (updateBtn) {
    updateBtn.addEventListener("click", () => {
      const newKg = parseFloat(kgThresholdInput.value);
      const newBunch = parseFloat(bunchThresholdInput.value);

      if (isNaN(newKg) || newKg < 0) return alert("Enter a valid Kg threshold");
      if (isNaN(newBunch) || newBunch < 0) return alert("Enter a valid Bunch threshold");

      thresholds.kg = newKg;
      thresholds.bunch = newBunch;

      kgThresholdDisplay.textContent = thresholds.kg;
      bunchThresholdDisplay.textContent = thresholds.bunch;

      loadProducts();
    });
  }

  // -------------------------
  // Initialize
  // -------------------------
  await loadProducts();
}
