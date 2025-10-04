export async function initPage() {
  let categories = [];
  let originalProduct = null;

  const form = document.getElementById("product-form");
  const formTitle = document.getElementById("form-title");
  const addNewBtn = document.getElementById("add-new-product");

  // -------------------------
  // Load categories
  // -------------------------
  async function loadCategories() {
    try {
      const res = await fetch(apiUrl("category"));
      categories = await res.json();
      const select = document.getElementById("product-category");
      if (!select) return;
      select.innerHTML = categories
        .map(c => `<option value="${c.id}">${c.name}</option>`)
        .join("");
    } catch {
      categories = [];
    }
  }

  // -------------------------
  // Check if product is expired
  // -------------------------
  function isExpired(dateStr) {
    if (!dateStr) return false;
    const expirationDate = new Date(dateStr);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return expirationDate < today;
  }

  // -------------------------
  // Calculate days expired
  // -------------------------
  function getDaysExpired(dateStr) {
    if (!dateStr) return 0;
    const expirationDate = new Date(dateStr);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const diffTime = today - expirationDate;
    return Math.floor(diffTime / (1000 * 60 * 60 * 24));
  }

  // -------------------------
  // Format expiration date for display
  // -------------------------
  function formatExpirationDate(dateStr) {
    if (!dateStr) return "-";
    const date = new Date(dateStr);
    const now = new Date();
    const diffTime = date - now;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    const formatted = date.toLocaleDateString('en-US', { 
      year: 'numeric', 
      month: 'short', 
      day: 'numeric' 
    });
    
    if (diffDays < 0) {
      return `<span style="color: red; font-weight: bold;">${formatted} (Expired)</span>`;
    } else if (diffDays <= 7) {
      return `<span style="color: orange; font-weight: bold;">${formatted} (${diffDays}d)</span>`;
    } else if (diffDays <= 30) {
      return `<span style="color: #ff9800;">${formatted} (${diffDays}d)</span>`;
    } else {
      return formatted;
    }
  }

  // -------------------------
  // Load products
  // -------------------------
  async function loadProducts() {
    try {
      const res = await fetch(apiUrl("products"));
      const allProducts = await res.json();
  
      
      // Separate active and expired products
      const activeProducts = allProducts.filter(p => !isExpired(p.expiration_date));
      const expiredProducts = allProducts.filter(p => isExpired(p.expiration_date));

      // Load active products
      const tbody = document.querySelector("#products-table tbody");
      if (tbody) {
        tbody.innerHTML = activeProducts
          .map(
            p => `
              <tr ${p.stock_quantity === 0 ? 'style="opacity:0.5;"' : ""}>
                <td>${p.id}</td>
                <td>${p.name}</td>
                <td>${p.description || ""}</td>
                <td>
                  ${p.size_value ? p.size_value + " " + (p.size_unit || "") : "-"} 
                  - ₱${p.price.toFixed(2)}
                </td>
                <td>${p.stock_quantity === 0 ? "Out of Stock" : p.stock_quantity}</td>
                <td>${p.category || "Uncategorized"}</td>
                <td>${formatExpirationDate(p.expiration_date)}</td>
                <td>
                  ${[
                    p.is_organic ? "Organic" : null,
                    p.is_seasonal ? "Seasonal" : null
                  ].filter(Boolean).join(", ")}
                </td>
                <td>
                  <button type="button" onclick="editProduct(${p.id})">Edit</button>
                  <button type="button" onclick="deleteProduct(${p.id})" 
                    style="margin-left:5px; background-color:red; color:white; border:none; padding:5px 10px; border-radius:4px;">
                    Remove
                  </button>
                </td>
              </tr>
            `
          )
          .join("");
      }

      // Load expired products
      const expiredTbody = document.querySelector("#expired-products-table tbody");
      const expiredCount = document.getElementById("expired-count");
      const noExpiredMsg = document.getElementById("no-expired");
      const expiredTable = document.getElementById("expired-products-table");

      if (expiredTbody && expiredCount) {
        expiredCount.textContent = expiredProducts.length;

        if (expiredProducts.length === 0) {
          if (noExpiredMsg) noExpiredMsg.style.display = "block";
          if (expiredTable) expiredTable.style.display = "none";
        } else {
          if (noExpiredMsg) noExpiredMsg.style.display = "none";
          if (expiredTable) expiredTable.style.display = "table";

        expiredTbody.innerHTML = expiredProducts
          .map(p => {
            const daysExpired = getDaysExpired(p.expiration_date);
            return `
              <tr style="background-color: #ffebee;">
                <td>${p.id}</td>
                <td>${p.name}</td>
                <td>${p.description || ""}</td>
                <td>
                  ${p.size_value ? p.size_value + " " + (p.size_unit || "") : "-"} 
                  - ₱${p.price.toFixed(2)}
                </td>
                <td>${p.stock_quantity}</td> <!-- Quantity column -->
                <td>${p.category || "Uncategorized"}</td>
                <td style="color: red; font-weight: bold;">
                  ${new Date(p.expiration_date).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                  })}
                </td>
                <td style="color: red; font-weight: bold;">
                  ${daysExpired} day${daysExpired !== 1 ? 's' : ''} ago
                </td>
                <td>
                  ${[
                    p.is_organic ? "Organic" : null,
                    p.is_seasonal ? "Seasonal" : null
                  ].filter(Boolean).join(", ")}
                </td>
                <td>
                  <button type="button" onclick="deleteProduct(${p.id})" 
                    style="background-color:red; color:white; border:none; padding:5px 10px; border-radius:4px;">
                    Remove
                  </button>
                </td>
              </tr>
            `;
          })
          .join("");

        }
      }


    } catch {
      console.error("Failed to load products.");
    }
  }

  // -------------------------
  // Delete product
  // -------------------------
  async function deleteProduct(id) {
    try {
      const res = await fetch(apiUrl("products") + `&id=${id}`);
      const product = await res.json();
      if (!product) return alert("Product not found.");

      if (
        !confirm(
          `Remove all items with name "${product.name}" (including all sizes)?`
        )
      )
        return;

      const delRes = await fetch(
        apiUrl("products") + `&name=${encodeURIComponent(product.name)}`,
        {
          method: "DELETE",
        }
      );
      const result = await delRes.json();
      alert(result.message);
      await loadProducts();
    } catch {
      alert("Failed to delete product(s).");
    }
  }
  window.deleteProduct = deleteProduct;

  // -------------------------
  // Edit product
  // -------------------------
  async function editProduct(id) {
    try {
      const res = await fetch(apiUrl("products") + `&id=${id}`);
      const product = await res.json();
      if (!product) return;

      originalProduct = { ...product };
      if (formTitle) formTitle.textContent = "Update Product";

      if (form) {
        form.querySelector("#product-id").value = product.id;
        form.querySelector("#product-name").value = product.name;
        form.querySelector("#product-price").value = product.price;
        form.querySelector("#product-stock").value = product.stock_quantity || 0;
        form.querySelector("#product-image").value = product.img || "";
        form.querySelector("#product-size-value").value = product.size_value || 0;
        form.querySelector("#product-size-unit").value = product.size_unit || "";
        form.querySelector("#product-description").value = product.description || "";
        form.querySelector("#product-expiration").value = product.expiration_date || "";

        const select = form.querySelector("#product-category");
        if (select && categories.length > 0)
          select.value = product.category_id || "";

        form.querySelector("#product-organic").checked =
          product.tags.includes("organic");
        form.querySelector("#product-seasonal").checked =
          product.tags.includes("seasonal");

        form.scrollIntoView({ behavior: "smooth", block: "start" });
        form.querySelector("#product-name")?.focus();
      }
    } catch (err) {
      console.error(err);
    }
  }
  window.editProduct = editProduct;

  // -------------------------
  // Live validation
  // -------------------------
  function setupLiveValidation() {
    if (!form) return;

    const inputs = [
      {
        el: form.querySelector("#product-name"),
        validator: val => {
          if (!val) return "Product name cannot be empty.";
          if (!/^[A-Za-z\s]+$/.test(val))
            return "Only letters and spaces allowed.";
          return "";
        },
      },
      {
        el: form.querySelector("#product-image"),
        validator: val => {
          const isEditing = form.querySelector("#product-id").value;
          if (
            val &&
            !/\.(jpg|jpeg|png|gif|webp)$/i.test(val)
          )
            return "Invalid image URL (.jpg, .png, .gif, .webp).";
          if (!isEditing && !val)
            return "Image URL is required for new products.";
          return "";
        },
      },
      {
        el: form.querySelector("#product-stock"),
        validator: val => {
          if (parseInt(val) < 0) return "Stock cannot be negative.";
          return "";
        },
      },
      {
        el: form.querySelector("#product-expiration"),
        validator: val => {
          if (val) {
            const selectedDate = new Date(val);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
              return "Expiration date cannot be in the past.";
            }
          }
          return "";
        },
      },
    ];

    inputs.forEach(({ el, validator }) => {
      if (!el) return;
      let errorElem = el.parentNode.querySelector(".error-text");
      if (!errorElem) {
        errorElem = document.createElement("div");
        errorElem.className = "error-text";
        errorElem.style.color = "red";
        errorElem.style.fontSize = "0.85em";
        errorElem.style.position = "absolute";
        errorElem.style.top = "-18px";
        errorElem.style.left = "0";
        el.parentNode.style.position = "relative";
        el.parentNode.insertBefore(errorElem, el);
      }
      let timeout;
      el.addEventListener("input", () => {
        const msg = validator(el.value.trim());
        if (msg) {
          errorElem.textContent = msg;
          el.setCustomValidity("invalid");
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
  if (form) {
    setupLiveValidation();
    form.addEventListener("submit", async e => {
      e.preventDefault();

      const id = form.querySelector("#product-id").value;
      const name = form.querySelector("#product-name").value.trim();
      const price = parseFloat(form.querySelector("#product-price").value);
      const stock_quantity =
        parseFloat(form.querySelector("#product-stock").value) || 0;
      const image_url = form.querySelector("#product-image").value.trim();
      const description = form.querySelector("#product-description").value.trim();
      const category =
        parseInt(form.querySelector("#product-category").value) || null;
      const size_value =
        parseFloat(form.querySelector("#product-size-value").value) || 0;
      const size_unit = form.querySelector("#product-size-unit").value.trim();
      const expiration_date = form.querySelector("#product-expiration").value || null;
      const is_organic = form.querySelector("#product-organic").checked ? 1 : 0;
      const is_seasonal = form.querySelector("#product-seasonal").checked ? 1 : 0;

      if (!name || !/^[A-Za-z\s]+$/.test(name) || stock_quantity < 0) return;
      if (!id && (!image_url || !/\.(jpg|jpeg|png|gif|webp)$/i.test(image_url))) {
        alert("Please enter a valid image URL for new product (.jpg, .png, .gif, .webp).");
        return;
      }
      if (!size_unit) {
        alert("Please select a size unit.");
        return;
      }

      if (expiration_date) {
        const selectedDate = new Date(expiration_date);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
          alert("Expiration date cannot be in the past.");
          return;
        }
      }

      let payload;
      if (!id) {
        payload = {
          id: null,
          name,
          description,
          price,
          stock_quantity,
          image_url,
          category,
          size_value,
          size_unit,
          expiration_date,
          is_organic,
          is_seasonal,
        };
      } else {
        payload = {
          id,
          name,
          description,
          price,
          stock_quantity,
          image_url,
          category,
          size_value,
          size_unit,
          expiration_date,
          is_organic,
          is_seasonal,
        };
      }

      try {
        const response = await fetch(apiUrl("products"), {
          method: id ? "PUT" : "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        });
        const result = await response.json();
        alert(result.message);
        form.reset();
        if (formTitle) formTitle.textContent = "Add New Product";
        originalProduct = null;
        await loadProducts();
      } catch {
        alert("Failed to save product.");
      }
    });
  }

  // -------------------------
  // Add New Product button
  // -------------------------
  if (addNewBtn && form) {
    addNewBtn.addEventListener("click", () => {
      originalProduct = null;
      form.reset();
      if (formTitle) formTitle.textContent = "Add New Product";
      form.querySelector("#product-id").value = "";
      form.querySelector("#product-name")?.focus();
    });
  }

  // -------------------------
  // Initialize page
  // -------------------------
  await loadCategories();
  await loadProducts();
}