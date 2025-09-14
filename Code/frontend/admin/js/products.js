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
  // Load products
  // -------------------------
  async function loadProducts() {
    try {
      const res = await fetch(apiUrl("products"));
      const products = await res.json();
      const tbody = document.querySelector("#products-table tbody");
      if (!tbody) return;

      tbody.innerHTML = products
        .map(
          p => `
        <tr ${p.stock_quantity === 0 ? 'style="opacity:0.5;"' : ""}>
          <td>${p.id}</td>
          <td>${p.name}</td>
          <td>${p.description || ""}</td>
          <td>
            ${p.size1_value ? `${p.size1_value} ${p.size1_unit} - ₱${p.price1.toFixed(2)}` : ""}
            ${p.size2_value ? `<br>${p.size2_value} ${p.size2_unit} - ₱${p.price2.toFixed(2)}` : ""}
          </td>
          <td>${p.stock_quantity === 0 ? "Out of Stock" : p.stock_quantity}</td>
          <td>${p.category || "Uncategorized"}</td>
          <td>${p.tags?.join(", ") || ""}</td>
          <td>
            <button type="button" class="action-btn edit-btn" onclick="editProduct(${p.id})">Edit</button>
            <button type="button" class="action-btn remove-btn" onclick="deleteProduct(${p.id})">Remove</button>
          </td>
        </tr>
      `
        )
        .join("");
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
          `Remove Item "${product.name}" ?`
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
        form.querySelector("#product-description").value = product.description || "";
        form.querySelector("#product-stock").value = product.stock_quantity || 0;
        form.querySelector("#product-image").value = product.image_url || "";

        // size 1
        form.querySelector("#size1-value").value = product.size1_value || "";
        form.querySelector("#size1-unit").value = product.size1_unit || "";
        form.querySelector("#price1").value = product.price1 || "";

        // size 2
        form.querySelector("#size2-value").value = product.size2_value || "";
        form.querySelector("#size2-unit").value = product.size2_unit || "";
        form.querySelector("#price2").value = product.price2 || "";

        const select = form.querySelector("#product-category");
        if (select && categories.length > 0)
          select.value = product.category_id || "";

        form.querySelector("#product-organic").checked =
          product.is_organic === 1;
        form.querySelector("#product-seasonal").checked =
          product.is_seasonal === 1;
      }

      // ✅ Scroll to form smoothly
      form.scrollIntoView({ behavior: "smooth", block: "start" });
      form.querySelector("#product-name")?.focus();

    } catch {}
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
    form.addEventListener("submit", async e => {
      e.preventDefault();

      const id = form.querySelector("#product-id").value;
      const name = form.querySelector("#product-name").value.trim();
      const description = form.querySelector("#product-description").value.trim();
      const stock_quantity =
        parseInt(form.querySelector("#product-stock").value) || 0;
      const image_url = form.querySelector("#product-image").value.trim();
      const category =
        parseInt(form.querySelector("#product-category").value) || null;

      // size 1
      const size1_value =
        parseFloat(form.querySelector("#size1-value").value) || null;
      const size1_unit = form.querySelector("#size1-unit").value.trim() || null;
      const price1 =
        parseFloat(form.querySelector("#price1").value) || null;

      // size 2
      const size2_value =
        parseFloat(form.querySelector("#size2-value").value) || null;
      const size2_unit = form.querySelector("#size2-unit").value.trim() || null;
      const price2 =
        parseFloat(form.querySelector("#price2").value) || null;

      const is_organic = form.querySelector("#product-organic").checked ? 1 : 0;
      const is_seasonal = form.querySelector("#product-seasonal").checked ? 1 : 0;

      if (!name) return alert("Product name required.");

      const payload = {
        id: id || null,
        name,
        description,
        stock_quantity,
        image_url,
        category,
        size1_value,
        size1_unit,
        price1,
        size2_value,
        size2_unit,
        price2,
        is_organic,
        is_seasonal,
      };

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
