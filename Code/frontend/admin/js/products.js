export async function initPage() {
  let categories = [];
  let originalProduct = null; // Store original product data when editing

  // -------------------------
  // Load categories
  // -------------------------
  async function loadCategories() {
    try {
      const res = await fetch(apiUrl("category"));
      categories = await res.json();
      const select = document.getElementById("product-category");
      if (!select) return;
      select.innerHTML = categories.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
    } catch {
      categories = [];
    }
  }

  // -------------------------
  // Load products
  // -------------------------
  // -------------------------
// Load products
// -------------------------
async function loadProducts() {
  try {
    const res = await fetch(apiUrl("products"));
    const products = await res.json();
    const tbody = document.querySelector("#products-table tbody");
    if (!tbody) return;
    tbody.innerHTML = products.map(p => `
      <tr ${p.stock_quantity === 0 ? 'style="opacity:0.5;"' : ''}>
        <td>${p.id}</td>
        <td>${p.name}</td>
        <td>₱${p.price.toFixed(2)}</td>
        <td>${p.stock_quantity === 0 ? 'Out of Stock' : p.stock_quantity}</td>
        <td>${p.category || "Uncategorized"}</td>
        <td>${p.tags.join(', ')}</td>
        <td>
          <button type="button" onclick="editProduct(${p.id})">Edit</button>
        </td>
      </tr>
    `).join('');
  } catch {}
}


  // -------------------------
  // Edit product
  // -------------------------
  async function editProduct(id) {
    try {
      const res = await fetch(apiUrl("products") + `&id=${id}`);
      const product = await res.json();
      if (!product) return;

      // Store original product data
      originalProduct = { ...product };

      document.getElementById("product-id").value = product.id;
      document.getElementById("product-name").value = product.name;
      document.getElementById("product-price").value = product.price;
      document.getElementById("product-stock").value = product.stock_quantity || 0;
      document.getElementById("product-image").value = product.img || "";

      const select = document.getElementById("product-category");
      if (select && categories.length > 0) {
        select.value = product.category_id != null ? String(product.category_id) : "";
      }

      document.getElementById("product-organic").checked = product.tags.includes("organic");
      document.getElementById("product-seasonal").checked = product.tags.includes("seasonal");
    } catch {}
  }

  // -------------------------
  // Live validation
  // -------------------------
  function setupLiveValidation() {
    const inputs = [
      { el: document.getElementById("product-name"), validator: val => {
        if (!val) return "Product name cannot be empty.";
        if (!/^[A-Za-z\s]+$/.test(val)) return "Only letters and spaces allowed.";
        return "";
      }},
      { el: document.getElementById("product-image"), validator: val => {
        const isEditing = document.getElementById("product-id").value;
        // Only validate image URL if it's not empty, or if we're adding a new product
        if (val && !/\.(jpg|jpeg|png|gif|webp)$/i.test(val)) {
          return "Invalid image URL (.jpg, .png, .gif, .webp).";
        }
        // For new products, require image URL
        if (!isEditing && !val) {
          return "Image URL is required for new products.";
        }
        return "";
      }},
      { el: document.getElementById("product-stock"), validator: val => {
        if (parseInt(val) < 0) return "Stock cannot be negative.";
        return "";
      }}
    ];

    inputs.forEach(inputObj => {
      const { el, validator } = inputObj;

      // Create error message element above input
      let errorElem = el.parentNode.querySelector(".error-text");
      if (!errorElem) {
        errorElem = document.createElement("div");
        errorElem.className = "error-text";
        errorElem.style.color = "red";
        errorElem.style.fontSize = "0.85em";
        errorElem.style.position = "absolute";
        errorElem.style.top = "-18px"; // above input
        errorElem.style.left = "0";
        el.parentNode.style.position = "relative"; // for absolute positioning
        el.parentNode.insertBefore(errorElem, el);
      }

      let timeout;
      el.addEventListener("input", () => {
        const msg = validator(el.value.trim());
        if (msg) {
          errorElem.textContent = msg;
          el.setCustomValidity("invalid");

          // Auto-hide after 3 seconds
          clearTimeout(timeout);
          timeout = setTimeout(() => {
            errorElem.textContent = "";
            el.setCustomValidity("");
          }, 3000);
        } else {
          errorElem.textContent = "";
          el.setCustomValidity("");
        }
      });
    });
  }

  // -------------------------
  // Form submit
  // -------------------------
  const form = document.getElementById("product-form");
  if (form) {
    setupLiveValidation();

   // -------------------------
// Form submit
// -------------------------
form.addEventListener("submit", async e => {
  e.preventDefault();

  const id = document.getElementById("product-id").value;
  const name = document.getElementById("product-name").value.trim();
  const price = parseFloat(document.getElementById("product-price").value);
  const stock_quantity = parseInt(document.getElementById("product-stock").value) || 0;
  const image_url = document.getElementById("product-image").value.trim();
  const category = parseInt(document.getElementById("product-category").value) || null;
  const is_organic = document.getElementById("product-organic").checked ? 1 : 0;
  const is_seasonal = document.getElementById("product-seasonal").checked ? 1 : 0;

  // Submit validation
  if (!name || !/^[A-Za-z\s]+$/.test(name) || stock_quantity < 0) return;

  // Handle image URL validation
  let finalImageUrl = image_url;
  if (id) {
    if (!image_url && originalProduct) finalImageUrl = originalProduct.img || "";
    else if (image_url && !/\.(jpg|jpeg|png|gif|webp)$/i.test(image_url)) {
      alert("Please enter a valid image URL (.jpg, .png, .gif, .webp).");
      return;
    }
  } else {
    if (!image_url) {
      alert("Image URL is required for new products.");
      return;
    }
    if (!/\.(jpg|jpeg|png|gif|webp)$/i.test(image_url)) {
      alert("Please enter a valid image URL (.jpg, .png, .gif, .webp).");
      return;
    }
  }

  // Remove deletion prompt; allow stock 0
  // if (!id && stock_quantity === 0) { ... } // remove this
  // if (id && stock_quantity === 0) { ... } // remove this

  const payload = { 
    id: id || null, 
    name, 
    price, 
    stock_quantity, 
    image_url: finalImageUrl, 
    category, 
    is_organic, 
    is_seasonal 
  };

  try {
    const response = await fetch(apiUrl("products"), {
      method: id ? "PUT" : "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });
    const result = await response.json();
    alert(result.message);
    form.reset();
    originalProduct = null;
    await loadProducts();
  } catch {
    alert("Failed to save product.");
  }
});

  }

  // Clear original product data when starting to add a new product
  const addNewButton = document.querySelector("[onclick*='reset']") || document.querySelector("button[type='reset']");
  if (addNewButton) {
    addNewButton.addEventListener("click", () => {
      originalProduct = null;
    });
  }

  window.editProduct = editProduct;

  // -------------------------
  // Initialize page
  // -------------------------
  await loadCategories();
  await loadProducts();
}